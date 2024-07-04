<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");

class CProfile extends UserFields  {

    function action() {
        global $g;
        global $g_user;
        $cmd = get_param('cmd', '');

        if ($cmd == 'update') {
            $this->message = '';

            $mail = trim(get_param('email', ''));
            $orientation = (int) get_param('orientation', $g_user['orientation']);
            $relation = (int) get_param('relation', $g_user['relation']);
            $name = trim(get_param('user_name', $g_user['name']));

            if (Common::isOptionActive('allow_users_to_change_their_logins')) {
                $this->message .= User::validateName($name);
            } else {
                $name = $g_user['name'];
            }

            $month = (int)get_param('month', 1);
            $day   = (int)get_param('day', 1);
            $year  = (int)get_param('year', 1980);

            $country = get_param('country', '');
            $state   = get_param('state', '');
            $city    = get_param('city', '');

            $this->message .= User::validate('email,birthday,location');
            $this->verification('pr_check');

            if ($this->message == '') {
                $h = zodiac($year . '-' . $month . '-' . $day);

                DB::execute("
						UPDATE user SET
                        name = " . to_sql($name, 'Text') . ",
                        relation = " . to_sql($relation, 'Number') . ",
						mail=" . to_sql($mail, 'Text') . ",
						country_id=" . to_sql($country, 'Number') . ",
						state_id=" . to_sql($state, 'Number') . ",
						city_id=" . to_sql($city, 'Number') . ",
						country=" . to_sql(Common::getLocationTitle('country', $country), 'Text') . ",
						state=" . to_sql(Common::getLocationTitle('state', $state), 'Text') . ",
						city=" . to_sql(Common::getLocationTitle('city', $city), 'Text') . ",
						birth='" . $year . "-" . $month . "-" . $day . "',
						horoscope='" . $h . "'
						WHERE user_id=" . guid() . ";
				");

                User::setOrientation(guid());
                if (self::isActive('orientation') && !Common::isOptionActive('your_orientation')) {
                    User::update(array('p_orientation' => get_checks_param('p_orientation')));
                }

                $this->updateTextsApproval();

                DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id=" . guid() . "");
                $g_user = DB::fetch_row();
                //g_user_full();

                /* if($mail!=$g_user['mail'])
                  {
                  user_change_email($g_user['user_id'], $mail);
                  redirect("email_not_confirmed.php");
                  } */

                redirect();
            }
        } elseif ($cmd == "couple_request") {
            $this->message = '';

            $couple_mail = get_param("couple_mail", "");
            $couple_id = DB::result("SELECT user_id FROM user WHERE name LIKE " . to_sql($couple_mail) . "");
            if ($couple_id == $g_user['user_id']) {
                $this->message = l('cannot_self');
                User::$error['couple'] = true;
            }elseif ($couple_id > 0) {
                $request_to = DB::result("SELECT user_id FROM user WHERE couple_to = '" . $g_user['user_id'] . "' AND  user_id = " . $couple_id);
                if ($request_to > 0) {
                    /*Old couples*/
                    DB::execute("UPDATE `user` SET `couple` = 'N', `couple_id` = 0  WHERE `couple_id` = " . to_sql($g_user['user_id'], 'Number'));
                    $coupleToOld = User::getInfoBasic($request_to, 'couple_id');
                    if ($coupleToOld > 0) {
                        DB::execute("UPDATE `user` SET `couple` = 'N', `couple_id` = 0  WHERE `user_id` = " . to_sql($coupleToOld, 'Number'));
                    }
                    DB::execute("UPDATE user SET couple='Y',couple_id='" . $g_user['couple_from'] . "',couple_from = 0 WHERE user_id=" . $g_user['user_id'] . "");
                    DB::execute("UPDATE user SET couple='Y',couple_id='" . $g_user['user_id'] . "',couple_to = 0 WHERE user_id=" . $g_user['couple_from'] . "");
                    DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id=" . get_session("user_id") . "");
                    $g_user = DB::fetch_row();
                    g_user_full();
                } else {
                    /*Old couples from*/
                    $coupleToOld = User::getInfoBasic($couple_id, 'couple_from');
                    if ($coupleToOld > 0)
                        DB::execute("UPDATE `user` SET `couple_to` = 0 WHERE `user_id` = " . to_sql($coupleToOld, 'Number'));
                    DB::execute("UPDATE `user` SET `couple_from` = " . to_sql($g_user['user_id'], 'Number') . " WHERE `user_id` = " . to_sql($couple_id, 'Number'));
                    DB::execute("UPDATE `user` SET `couple_to` = " . to_sql($couple_id, 'Number') . " WHERE `user_id` = " . to_sql($g_user['user_id'], 'Number'));
                }
            }
            else {
                $this->message = l('user_not_exists');
                User::$error['couple'] = true;
            }
            DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id=" . get_session("user_id") . "");
            $g_user = DB::fetch_row();
            g_user_full();
        } elseif ($cmd == "couple_request_cancel") {

            DB::execute("UPDATE user SET couple_from = 0 WHERE user_id=" . $g_user['couple_to'] . "");
            DB::execute("UPDATE user SET  couple_to = 0  WHERE user_id=" . $g_user['user_id'] . "");
            DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id=" . get_session("user_id") . "");
            $g_user = DB::fetch_row();
            g_user_full();
        } elseif ($cmd == "couple_query_cancel") {
            DB::execute("UPDATE user SET  couple_to = 0  WHERE user_id=" . $g_user['couple_from'] . "");
            DB::execute("UPDATE user SET couple_from = 0 WHERE user_id=" . $g_user['user_id'] . "");
            DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id=" . get_session("user_id") . "");
            $g_user = DB::fetch_row();
            g_user_full();
        } elseif ($cmd == "couple_cancel") {

            DB::execute("UPDATE user SET couple = 'N',couple_id =0  WHERE user_id=" . $g_user['couple_id'] . "");
            DB::execute("UPDATE user SET couple = 'N',couple_id=0  WHERE user_id=" . $g_user['user_id'] . "");
            DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id=" . get_session("user_id") . "");
            $g_user = DB::fetch_row();
            g_user_full();
        } elseif ($cmd == "couple_approve") {
            /*Old couples request*/
            if ($g_user['couple_to']) {
                DB::execute('UPDATE `user` SET `couple_from` = 0 WHERE `user_id` = ' . to_sql($g_user['couple_to'], 'Number'));
                DB::execute('UPDATE `user` SET `couple_to` = 0  WHERE `user_id` = ' . to_sql($g_user['user_id'], 'Number'));
            }
            /*Old couples request*/
            /*Old couples*/
            DB::execute("UPDATE `user` SET `couple` = 'N', `couple_id` =0  WHERE `couple_id` = " . to_sql($g_user['user_id'], 'Number'));
            $coupleToOld = User::getInfoBasic($g_user['couple_from'], 'couple_id');
            if ($coupleToOld > 0) {
                DB::execute("UPDATE `user` SET `couple` = 'N', `couple_id` = 0  WHERE `user_id` = " . to_sql($coupleToOld, 'Number'));
            }
            DB::execute("UPDATE user SET couple='Y',couple_id='" . $g_user['couple_from'] . "',couple_from = 0 WHERE user_id=" . $g_user['user_id'] . "");
            DB::execute("UPDATE user SET couple='Y',couple_id='" . $g_user['user_id'] . "',couple_to = 0 WHERE user_id=" . $g_user['couple_from'] . "");
            DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id=" . get_session("user_id") . "");
            $g_user = DB::fetch_row();
            g_user_full();
        }
        //elseif ($cmd == "confirmed")
        //{
        //$this->message = l("Email confirmed")."<br><br>";
        //}
    }

    function parseBlock(&$html) {
        global $g;
        global $l;
        global $g_user;

        $cmd = get_param('cmd', '');
        if ($cmd == "confirmed"){
            $html->parse('alert_email_confirmed');
        }

        if (isset($this->message))
            $html->setvar('update_message', $this->message);

        //$this->setValueTexts();
        if (!Common::isOptionActive('allow_users_to_change_profile_type')) {
            $this->setBanCustomFields(array('orientation'));
            $orientation = DB::result("SELECT title FROM const_orientation WHERE id = " . to_sql($g_user['orientation']));
            $html->setvar('orientation_value', l($orientation));
            $html->parse('orientation_edit_off');
        }
        $this->formatValue = 'entities';
        $this->parseFieldsAll($html, 'profile', false);

        $html->setvar("name", $g_user['name']);
        $html->setvar("mail", get_param("mail", $g_user['mail']));


        foreach (User::$error as $key => $item) {
            $html->parse($key . '_error', false);
        }
        if ($g_user['couple_from'] > 0) {
            $html->setvar("couple_name", DB::result("SELECT name FROM user WHERE user_id=" . $g_user['couple_from'] . ""));
            $html->parse("couple_approve", true);
        }
        if ($g_user['couple_to'] > 0) {
            $html->setvar("couple_name", DB::result("SELECT name FROM user WHERE user_id=" . $g_user['couple_to'] . ""));
            $html->parse("couple_request_cancel", true);
        }

        if ($g_user['couple'] == 'Y' and $g_user['couple_id'] > 0) {
            $html->setvar("couple_name", DB::result("SELECT name FROM user WHERE user_id=" . $g_user['couple_id'] . ""));
            $html->parse("couple_cancel", true);
        }
        if ($g_user['couple_to'] == 0
            //&& $g_user['couple_from'] == 0
            && $g_user['couple_id'] == 0
            && $g_user['couple'] != 'Y')
            $html->parse("couple_request", true);


        if (Common::isOptionActive('couples')) {
            $html->parse('couple');
        }
        if (Common::isOptionActive('allow_users_to_change_their_logins')) {
            $html->parse('name_edit_on');
            $html->setvar('username_length', $g['options']['username_length']);
            $html->setvar('username_length_min', $g['options']['username_length_min']);
            $html->parse('name_edit_on_js');
        } else {
            $html->parse('name_edit_off');
        }
        if (Common::isOptionActive('partner_settings', 'options') || Common::isOptionActive('personal_settings', 'options')) {
            if (Common::isOptionActive('partner_settings', 'options')) {
                $html->parse('yes_partner');
            }
            if (Common::isOptionActive('personal_settings', 'options')) {
                $html->parse('yes_personal');
            }
            $html->parse('yes_settings');
        }

        $html->setvar('mail_length_max', Common::getOption('mail_length_max'));

// PAYMENTS
        if (!Common::isOptionActive('free_site')) {
            if ($g_user['gold_days'] > 0 && $g_user['type'] != 'none') {
                $lType = l($g_user['type']);
                $paidDays = $g_user['gold_days'];
                $vars = array(
                    'paid_type' => $lType,
                    'payment_type' => $lType,
                    'paid_days' => $paidDays,
                    'gold_days' => $paidDays,
                    'days_left' => l('days_left')
                );
                $html->setvar('payment_paid', lSetVars(l('payment_module'), $vars));
                $html->parse('payment_paid');
            } elseif ($g_user['free_access']) {
                $type = DB::result('SELECT free FROM const_orientation WHERE id=' . $g_user['orientation']);
                $html->setvar('payment_free_access', l($type));
                $html->parse('payment_free_access');
            } else {
                $vars = array('url' => $g['path']['url_main'] . 'upgrade.php');
                $html->setvar('payment_free', lSetVars(l('free_upgrade_module'), $vars));
                $html->parse('payment_free');
            }

            $html->parse('paid_site');
        }
// PAYMENTS
        if (self::isActive('orientation')) {
            if (Common::isOptionActive('your_orientation')) {
                if (guser('p_orientation')) {
                    $name = User::getTitleOrientationLookingFor(array('p_orientation' => guser('p_orientation')));
                    $html->setvar('name', $name);
                    $html->parse('looking_your_orientation');
                }
            } else {
                $this->parseChecks($html, 'p_orientation', $g['user_var']['orientation'], 2, 0, false, 'p_orientation', true);
            }
        }

        parent::parseBlock($html);
    }

}

g_user_full();

$page = new CProfile("", $g['tmpl']['dir_tmpl_main'] . "profile.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$profile_menu = new CMenuSection("profile_menu", $g['tmpl']['dir_tmpl_main'] . "_profile_menu.html");
$profile_menu->setActive('profile');
$page->add($profile_menu);

$complite = new CComplite("complite", $g['tmpl']['dir_tmpl_main'] . "_complite.html");
$page->add($complite);

include("./_include/core/main_close.php");
?>
