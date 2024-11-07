<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

$area = "login";

include("./_include/core/main_start.php");
require_once("./_include/current/vids/includes.php");

Moderator::checkAccess();

class CHon extends CHtmlBlock
{
    var $message_send = '';

    function action()
    {
        global $p;
        global $g;
        global $g_user;

        $redirect = false;
        $cmd = get_param('section', Moderator::checkAccess());

        if ($cmd == 'profiles') {
            $approvalUserId = get_param('user_approval', 0);
            $delUserId = get_param('user_delete', 0);
            if ($delUserId) {
                if (Common::isEnabledAutoMail('admin_delete')) {
                    DB::query('SELECT * FROM user WHERE user_id = ' . to_sql($delUserId, 'Number'));
                    $row = DB::fetch_row();
                    $vars = array(
                        'title' => $g['main']['title'],
                    );
                    Common::sendAutomail($row['lang'], $row['mail'], 'admin_delete', $vars);
                }
                delete_user($delUserId);
                redirect($p . '?section=' . $cmd);
            } elseif ($approvalUserId) {
                $data = array('active' => 1, 'hide_time' => 0);
                DB::update('user', $data, '`user_id` = ' . to_sql($approvalUserId, 'Number'));
                if (Common::isEnabledAutoMail('profile_approved')) {
                    DB::query('SELECT * FROM `user` WHERE `user_id` = ' . to_sql($approvalUserId, 'Number'));
                    $row = DB::fetch_row();
                    $vars = array(
                        'title' => $g['main']['title'],
                        'name' => $row['name'],
                        'password' => $row['password'],
                    );
                    Common::sendAutomail($row['lang'], $row['mail'], 'profile_approved', $vars);
                }
                redirect($p . '?section=' . $cmd);
            }
        } else if ($cmd == "users_reports") {
            $action = get_param('action');
            if ($action == "delete_report") {
                $reportsId = get_param('id');

                $listUsersReport = explode(',', $reportsId);
                foreach ($listUsersReport as $rid) {
                    $where = 'id = ' . to_sql($rid);
                    $report = DB::select('users_reports', $where);
                    $usersToReport = array();
                    if ($report && isset($report[0])) {
                        $report = $report[0];
                        $usersToReport = User::getInfoBasic($report['user_to'], 'users_reports');
                        DB::delete('users_reports', $where);
                    }
                    if ($usersToReport) {
                        $usersToReport = explode(',', $usersToReport);
                        unset_from_array($report['user_from'], $usersToReport);
                        User::update(array('users_reports' => implode(',', $usersToReport)), $report['user_to']);
                    }
                }
                $cmd = "users_reports";
                $redirect = true;
            } else if ($action == "ban_user") {
                $uid = get_param('ban_user');

                $sql = 'UPDATE `user`
                           SET `ban_global` = 1 - `ban_global`
                         WHERE `user_id` = ' . to_sql($uid, 'Number');
                DB::execute($sql);

                $cmd = "users_reports";
                $redirect = true;
            }
        } else if ($cmd == "support_tickets") { /* Added by divyesh 13-10-2023 */
            $action = get_param('action');
            if ($action == "delete_ticket") {
                $ticketId = get_param('id');
                $where = 'id = ' . to_sql($ticketId);
                $ticket = DB::one('support_tickets', $where);

                if ($ticket && !empty($ticket['attachment'])) {
                    $patch = Common::getOption('url_files', 'path');
                    $patch . "support_ticket/" . $ticket['attachment'];
                    unlink($patch . "support_ticket/" . $ticket['attachment']);
                }

                $ticket_replies = DB::select('ticket_replies', 'ticket_id =' . to_sql($ticketId));
                foreach ($ticket_replies as $ticket_replie) {
                    if ($ticket_replie && !empty($ticket_replie['attachment'])) {
                        $patch = Common::getOption('url_files', 'path');
                        $patch . "support_ticket/" . $ticket_replie['attachment'];
                        unlink($patch . "support_ticket/" . $ticket_replie['attachment']);
                    }
                    DB::delete('ticket_replies', 'id =' . to_sql($ticket_replie['id']));
                }
                DB::delete('support_tickets', $where);
                $cmd = "support_tickets";
                $redirect = true;
            } else if ($action == "upper_tier") {
                $tier = get_param('level');
                $assign_to = get_tier_user($tier);
                $id = get_param('id');
                DB::update('support_tickets', array("assign_to" => $assign_to), "id=" . $id);
                set_session("saved", "yes");
                $cmd = "support_tickets";
                $redirect = true;
            } else if ($action == "lower_tier") {
                $tier = get_param('level');
                $assign_to = get_tier_user($tier);
                $id = get_param('id');
                DB::update('support_tickets', array("assign_to" => $assign_to), "id=" . $id);
                set_session("saved", "yes");
                $cmd = "support_tickets";
                $redirect = true;
            }
        }

        $do = get_param_array('do');
        $data_texts = array();
        DB::query("SHOW COLUMNS FROM texts");
        while ($row = DB::fetch_row()) {
            if (($row[0] != 'id') or ($row[0] != 'user_id')) {
                $data_texts[$row[0]] = get_param_array($row[0]);
            }
        }


        foreach ($do as $k => $v) {
            $k = ((int) $k);

            if ($v == "del") {
                if ($cmd == 'texts') {
                    Moderator::setNotificationTypeText();
                    $sql = 'SELECT user_id FROM texts WHERE id = ' . to_sql($k);
                    $row = DB::row($sql);
                    if (isset($row['user_id'])) {
                        Moderator::prepareNotificationInfo($row['user_id']);
                    }

                    $sql = 'DELETE FROM texts
                        WHERE id = ' . $k;
                    DB::execute($sql);
                }

                if ($cmd == 'vids_video') {

                    Moderator::setNotificationTypeVideo();

                    $sql = 'SELECT * FROM vids_video WHERE id = ' . $k;
                    DB::query($sql, 2);
                    if ($row = DB::fetch_row(2)) {
                        CVidsTools::delVideoById($row['id'], true);
                        Moderator::prepareNotificationInfo($row['user_id'], $row);
                    }
                }

                if ($cmd == 'photo') {
                    Moderator::setNotificationTypePhoto();

                    $sql = 'SELECT * FROM photo WHERE photo_id = ' . $k;
                    DB::query($sql, 2);
                    if ($row = DB::fetch_row(2)) {
                        Moderator::prepareNotificationInfo($row['user_id'], $row);
                        deletephoto($row['user_id'], $row['photo_id']);
                    }
                    $sql = 'DELETE FROM photo WHERE photo_id = ' . $k;
                    DB::execute($sql);
                }

                Moderator::sendNotificationDeclined();

                $redirect = true;
            } else { /* Divyesh - 17042024 */
                if ($cmd == 'photo') {

                    Moderator::setNotificationTypePhoto();

                    /*$sql = 'UPDATE photo SET visible="Y"
                        WHERE photo_id = ' . $k;
                    DB::execute($sql);

                    $sql = 'SELECT * FROM photo
                        WHERE photo_id = ' . $k;
                    DB::query($sql, 2);

                    if ($row = DB::fetch_row(2)) {
                        $g_user['user_id'] = $row['user_id'];
                        $sql = 'UPDATE user
                            SET last_visit = last_visit,
                            is_photo = "Y"
                            WHERE user_id = ' . $row['user_id'];
                        DB::execute($sql);
                    }
                    if ($v == 'access') {
                        CProfilePhoto::setPhotoPrivate($k);
                    }*/
                    User::photoApproval($k, $v);
                }

                if ($cmd == 'vids_video') {

                    Moderator::setNotificationTypeVideo();
                    /* Divyesh - 17042024 */
                    $sql = 'UPDATE vids_video SET active=1';
                    if ($v == 'private') {
                        $sql .= ',private="Y" ';
                    }
                    $sql .= ' WHERE id = ' . $k;
                    /* Divyesh - 17042024 */
                    DB::execute($sql);
                    /*
                    $sql = 'SELECT * FROM vids_video
                        WHERE id = ' . $k;
                    DB::query($sql, 2);
                    if ($row = DB::fetch_row(2)) {
                        $sql = 'UPDATE user
                            SET last_visit = last_visit
                            WHERE user_id = ' . $row['user_id'];
                        DB::execute($sql);
                    }
                    *
                    */
                    DB::update('wall', array('params' => ""), '`item_id` = ' . to_sql((int) $k) . ' AND section="vids"');

                    $sql = 'SELECT * FROM vids_video WHERE id = ' . to_sql($k);
                    $videoInfo = DB::row($sql);

                    if (isset($videoInfo['user_id'])) {
                        Moderator::prepareNotificationInfo($videoInfo['user_id'], $videoInfo);
                    }
                }

                if ($cmd == 'texts') {

                    Moderator::setNotificationTypeText();

                    DB::query("SELECT * FROM texts WHERE id=" . ((int) $k) . "");
                    if ($row = DB::fetch_row()) {

                        $sql = "";
                        foreach ($row as $k2 => $v2) {
                            if (isset($g['user_var'][$k2]) and ($k2 != "id" and $k2 != "user_id" and !is_int($k2) and $g['user_var'][$k2]['status'] == 'active')) {
                                if (isset($data_texts[$k2][$k])) {
                                    $sql .= " " . $k2 . "=" . to_sql($data_texts[$k2][$k], "Text") . ", ";
                                } else {
                                    $sql .= " " . $k2 . "=" . to_sql($v2, "Text") . ", ";
                                }
                            }
                        }
                        if ($sql != '') {
                            $sql = substr($sql, 0, (strlen($sql) - 2));
                            DB::execute("UPDATE userinfo SET " . $sql . " WHERE user_id=" . $row['user_id'] . "");

                            Moderator::prepareNotificationInfo($row['user_id']);
                        }
                    }
                    DB::execute("DELETE FROM texts WHERE id=" . ((int) $k) . "");
                }

                Moderator::sendNotificationApproved();

                $redirect = true;
            }/* Divyesh - 17042024 */
        }

        if ($redirect) {
            redirect($p . '?section=' . $cmd);
        }
    }

    function parseBlock(&$html)
    {
        Moderator::buttonsParse($html);

        $html->parse('members', false);

        parent::parseBlock($html);
    }
}

class Cgroups extends CHtmlList
{
    var $dir = 'groups';

    function init()
    {
        global $g_user; /* Added by divyesh - 13-10-2023 */
        parent::init();

        $cmd = to_sql(get_param('section', Moderator::checkAccess()), 'Plain');

        if ($cmd == "photo") {
            $this->m_field['description'] = array("description", null);
            $this->m_field['user_id'] = array("user_id", null);
            $this->m_field['photo_id'] = array("photo_id", null);
            $this->m_field['photo_name'] = array("photo_name", null);

            $this->m_sql_where = CProfilePhoto::moderatorVisibleFilter();

            $this->m_sql_order = "photo_id desc";
        } elseif ($cmd == "vids_video") {
            $this->m_field['subject'] = array("subject", null);
            $this->m_field['user_id'] = array("user_id", null);
            $this->m_field['id'] = array("id", null);
            $this->m_field['text'] = array("text", null);

            $this->m_sql_where = "c.active=3";
            $this->m_sql_order = "id desc";
        } elseif ($cmd == "texts") {
            $this->m_field['id'] = array("id", null);
            $this->m_field['user_id'] = array("user_id", null);

            $this->m_sql_order = "id desc";
        }
        $this->m_on_page = 10;

        if ($cmd == "profiles") {
            $this->m_on_page = 20;

            $this->m_sql_count = "SELECT COUNT(u.user_id) FROM user AS u ";
            $this->m_sql = "
            SELECT u.user_id, u.mail, u.type, u.orientation, u.password, u.gold_days, u.name, (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
            ) AS age, u.last_visit,
            u.is_photo,
            u.city_id, u.state_id, u.country_id, u.last_ip, u.register
            FROM user AS u";

            $this->m_field['user_id'] = array("user_id", null);
            $this->m_field['name'] = array("name", null);
            $this->m_field['age'] = array("age", null);
            $this->m_field['last_visit'] = array("last_visit", null);
            $this->m_field['mail'] = array("mail", null);
            $this->m_field['type'] = array("type", null);
            $this->m_field['gold_days'] = array("gold_days", null);
            $this->m_field['password'] = array("password", null);
            $this->m_field['orientation'] = array("orientation", null);
            $this->m_field['last_ip'] = array("last_ip", null);
            $this->m_field['register'] = array("register", null);
            $this->m_sql_where = " `active` = 0";
            $this->m_sql_order = "user_id";
        } elseif ($cmd == "events") {


            $this->m_field['user_id'] = array("user_id", null);
            $this->m_field['event_title'] = array("event_title", null);

            $this->m_field['event_description'] = array("event_description", null);

            $this->m_sql_order = "event_id desc";

            $sql = "SELECT c.* FROM `events_event` AS c LEFT JOIN user AS u ON u.user_id = c.user_id";
            $sqlCount = "SELECT count(*) FROM `events_event` AS c LEFT JOIN user AS u ON u.user_id = c.user_id";

            $this->m_sql = $sql;
            $this->m_sql_count = $sqlCount;
        } elseif ($cmd == "hotdates") {


            $this->m_field['user_id'] = array("user_id", null);
            $this->m_field['hotdate_title'] = array("hotdate_title", null);

            $this->m_field['hotdate_description'] = array("hotdate_description", null);

            $this->m_sql_order = "hotdate_id desc";

            $sql = "SELECT c.* FROM `hotdates_hotdate` AS c LEFT JOIN user AS u ON u.user_id = c.user_id";
            $sqlCount = "SELECT count(*) FROM `hotdates_hotdate` AS c LEFT JOIN user AS u ON u.user_id = c.user_id";

            $this->m_sql = $sql;
            $this->m_sql_count = $sqlCount;
        } elseif ($cmd == "partyhouz") {


            $this->m_field['user_id'] = array("user_id", null);
            $this->m_field['partyhou_title'] = array("partyhou_title", null);

            $this->m_field['partyhou_description'] = array("partyhou_description", null);

            $this->m_sql_order = "partyhou_id desc";

            $sql = "SELECT c.* FROM `partyhouz_partyhou` AS c LEFT JOIN user AS u ON u.user_id = c.user_id";
            $sqlCount = "SELECT count(*) FROM `partyhouz_partyhou` AS c LEFT JOIN user AS u ON u.user_id = c.user_id";

            $this->m_sql = $sql;
            $this->m_sql_count = $sqlCount;
        } elseif ($cmd == "craigs") {
            $this->m_field['user_id'] = array("user_id", null);
            $this->m_field['partyhou_title'] = array("partyhou_title", null);

            $this->m_field['partyhou_description'] = array("partyhou_description", null);

            $this->m_sql_order = "partyhou_id desc";

            $sql = "SELECT c.* FROM `partyhouz_partyhou` AS c LEFT JOIN user AS u ON u.user_id = c.user_id";
            $sqlCount = "SELECT count(*) FROM `partyhouz_partyhou` AS c LEFT JOIN user AS u ON u.user_id = c.user_id";

            $this->m_sql = $sql;
            $this->m_sql_count = $sqlCount;
        } elseif ($cmd == "wowslider") {
            $this->m_field['user_id'] = array("user_id", null);
            $this->m_field['title'] = array("title", null);

            $this->m_field['description'] = array("description", null);

            $this->m_sql_order = "event_id desc";

            $sql = "SELECT c.* FROM `wowslider` AS c LEFT JOIN user AS u ON u.user_id = c.user_id";
            $sqlCount = "SELECT count(*) FROM `wowslider` AS c LEFT JOIN user AS u ON u.user_id = c.user_id";

            $this->m_sql = $sql;
            $this->m_sql_count = $sqlCount;
        } elseif ($cmd == "users_reports") {
            $this->m_field['id'] = array("id", null);
            $this->m_field['user_from'] = array("user_from", null);
            $this->m_field['user_to'] = array("user_to", null);
            $this->m_field['priority'] = array("priority", null);
            $this->m_field['title'] = array("title", null);
            $this->m_field['msg'] = array("msg", null);

            $this->m_field['date'] = array("date", null);

            $this->m_sql_order = "id desc";

            $sql = "SELECT * FROM `users_reports`";
            $sqlCount = "SELECT count(*) FROM `users_reports`";

            $this->m_sql = $sql;
            $this->m_sql_count = $sqlCount;
        } elseif ($cmd == "support_tickets") {  /* Added by Divyesh - 13-10-2023 */
            $this->m_field['id'] = array("id", null);
            $this->m_field['user_from'] = array("user_from", null);
            $this->m_field['assign_to'] = array("assign_to", null);
            $this->m_field['priority'] = array("priority", null);
            $this->m_field['title'] = array("title", null);
            $this->m_field['msg'] = array("msg", null);
            $this->m_field['attachment'] = array("attachment", null);
            $this->m_field['status'] = array("status", null);

            $this->m_field['date'] = array("date", null);

            $this->m_sql_order = "id desc";

            $sql = "SELECT * FROM `support_tickets`";
            $sqlCount = "SELECT count(*) FROM `support_tickets`";

            $this->m_sql = $sql;
            $this->m_sql_where = "assign_to = " . $g_user['user_id'] . " AND `status`='1'";
            $this->m_sql_count = $sqlCount;
        } else {
            $sql = "SELECT c.* FROM $cmd AS c
                      LEFT JOIN user AS u ON u.user_id = c.user_id";
            $sqlCount = "SELECT count(*) FROM $cmd AS c
                           LEFT JOIN user AS u ON u.user_id = c.user_id";

            $this->m_sql = $sql;
            $this->m_sql_count = $sqlCount;
        }

        //$this->m_debug = "Y";
    }

    function onItem(&$html, $row, $i, $last)
    {
        global $g;

        $cmd = get_param('section', Moderator::checkAccess());
        if ($cmd == "profiles") {
            $this->m_field['orientation'][1] = DB::result("SELECT title FROM const_orientation WHERE id=" . $row['orientation'] . "", 0, 2);
            if ($this->m_field['orientation'][1] == "") {
                $this->m_field['orientation'][1] = l("Invalid orientation");
            } else {
                $this->m_field['orientation'][1] = l($this->m_field['orientation'][1]);
            }
            if (Common::getOption('set', 'template_options') != 'urban1') {
                if ($row['type'] == 'membership') {
                    $this->m_field['type'][1] = l('platinum');
                } else {
                    $this->m_field['type'][1] = l($row['type']);
                }
            } else {
                if ($row['type'] != 'none') {
                    if ($row['gold_days'] > 0) {
                        $this->m_field['type'][1] = l('Super Powers!');
                    } else {
                        $this->m_field['type'][1] = l('none');
                    }
                } else {
                    $this->m_field['type'][1] = l($row['type']);
                }
            }
            $this->m_field['name'][1] = hard_trim($row['name'], 10);
            if ($i % 2 == 0) {
                $html->setvar("class", 'color');
            } else {
                $html->setvar("class", '');
            }

            $html->setvar('profile_url', User::url($row['user_id'], null, array('moderator_view_profile' => 1)));
        }
        #$html->parse($cmd, false);
        $html->parse("file", false);
    }

    function onPostParse(&$html)
    {
        $cmd = get_param('section', Moderator::checkAccess());
        if ($cmd == 'profiles') {
            $html->parse('profiles', false);
        }
        parent::onPostParse($html);
    }

    function parseBlock(&$html)
    {
        global $g;
        global $g_user; /* Added by Divyesh - 13-10-2023 */
        global $guid;

        $tmplName = Common::getTmplName();
        $cmd = get_param('section', Moderator::checkAccess());

        $html->setvar('cmd', $cmd);
        $html->setvar('page_title', l('top_moderator'));

        if ($cmd == 'texts') {
            DB::query("SELECT * FROM texts ORDER BY id DESC LIMIT 20", 2);
            $num = DB::num_rows(2);
            while ($row = DB::fetch_row(2)) {
                $html->setvar('id', $row['id']);
                $html->setvar('user_id', $row['user_id']);
                $html->setvar('user_name', User::getInfoBasic($row['user_id'], 'name'));
                $html->setvar('user_profile_link', User::url($row['user_id']));
                foreach ($row as $k => $v) {
                    if ($k != "id" and $k != "user_id" and !is_int($k) && !empty($v)) {
                        $html->setvar("field", $k);
                        $html->setvar("field_title", ucfirst($k));
                        $html->setvar("value", he($v));
                        if (!isset($g['user_var'][$k][0])) {
                        } elseif ($g['user_var'][$k][0] == "text") {
                            $html->setvar("name_input", $k);
                            $html->setvar("field_title", $g['user_var'][$k][2]);
                            $html->parse("text");
                        } elseif ($g['user_var'][$k][0] == "textarea") {
                            $html->setvar("name_input", $k);
                            $html->setvar("field_title", $g['user_var'][$k][2]);
                            $html->parse("textarea");
                        }
                    }
                }
                $html->parse('texts', true);
                $html->clean('textarea');
                $html->clean('text');
            }
            if ($num) {
                $html->parse('text_section');
            } else {
                $html->parse('text_section_noitems');
            }
            $html->parse('sections');
            $html->parse('approve_button_submit', true);
        } elseif ($cmd == 'vids_video') {
            DB::query('SELECT * FROM `vids_video` WHERE `active` = 3 ORDER BY id LIMIT 20', 2);
            $num = DB::num_rows(2);
            VideoHosts::setAutoplay(false);
            if ($tmplName == 'edge') {
                $g['options']['video_player_type'] = 'player_native';
            }
            while ($row = DB::fetch_row(2)) {
                $row['user_name'] = User::getInfoBasic($row['user_id'], 'name');
                $row['user_profile_link'] = User::url($row['user_id']);
                foreach ($row as $k => $v) {
                    $html->setvar($k, $v);
                }

                /* Divyesh - 17042024 */
                if ($row['private'] == 'Y') {
                    $html->setvar('status', l('Private'));
                    $html->setvar('private_check', 'checked="checked"');
                } else {
                    $html->setvar('status', l('Public'));
                    $html->setvar('public_check', 'checked="checked"');
                }
                /* Divyesh - 17042024 */

                $video = CVidsTools::getVideoById($row['id'], true);
                if (!isset($video) or !is_array($video)) {
                    continue;
                }
                $html->setvar('video_html_code', $video['html_code']);
                $html->setvar('user_name', $row['user_name']);
                $html->setvar('video_id', $row['id']);
                $html->setvar('description', $row['subject']);

                $html->parse("video", true);
            }
            if ($num) {
                $html->parse('vids_video');
            } else {
                $html->parse('vids_video_noitems');
            }
            $html->parse('sections');
            $html->parse('approve_button_submit', true);
        } elseif ($cmd == "photo") {
            $noPrivatePhoto = Common::isOptionActiveTemplate('no_private_photos');

            // var_dump("SELECT * FROM photo WHERE " . CProfilePhoto::moderatorVisibleFilter() . " ORDER BY photo_id LIMIT 20"); die();
            DB::query("SELECT * FROM photo WHERE " . CProfilePhoto::moderatorVisibleFilter() . " ORDER BY photo_id LIMIT 20", 2);
            $num = DB::num_rows(2);
            while ($row = DB::fetch_row(2)) {
                $row['user_name'] = User::getInfoBasic($row['user_id'], 'name');
                $row['user_profile_link'] = User::url($row['user_id']);
                foreach ($row as $k => $v) {
                    $html->setvar($k, $v);
                }

                if (!$noPrivatePhoto) {
                    if ($row['private'] == 'Y') {
                        $html->setvar('status', l('Private'));
                        $html->setvar('private_check', 'checked="checked"');
                    } else if ($row['personal'] == 'Y') {
                        $html->setvar('status', l('personal'));
                        $html->setvar('personal_check', 'checked="checked"');
                    } else if ($row['in_custom_folder'] == 'Y' && !empty($row['custom_folder_id'])) {
                    } else {
                        $html->setvar('status', l('Public'));
                        $html->setvar('public_check', 'checked="checked"');
                    }

                    $sql = "SELECT * FROM custom_folders WHERE user_id=" . to_sql($row['user_id'], 'Number');
                    $folders = DB::rows($sql);

                    foreach ($folders as $key => $folder) {
                        $folder_id = $folder['id'];
                        if($folder_id == $row['custom_folder_id']) {
                            $folder_check = 'checked=checked';
                        } else {
                            $folder_check = '';
                        }

                        $html->setvar('folder_name', l('move_to') . ' ' . $folder['name']);
                        $html->setvar('folder_id', $folder['id']);
                        $html->setvar('folder_check', $folder_check);
                        $html->parse('folder_item', true);
                    }

                    $html->parse('folders_move', false);
                    $html->clean('folder_item');

                    /* Divyesh - 17042024 */
                    // if (!empty($custom_folder)) {
                    //     $html->setvar("folder_access", l('move_to') . " " . $custom_folder);
                    //     $html->parse("show_folder_access", false);
                    // } else {
                    //     $html->setblockvar("show_folder_access", '');
                    // }
                    /* Divyesh - 17042024 */

                    //$html->parse('photo_access', false);
                }
                $html->setvar("photo_file", $row['photo_id'] . "_" . $row['hash']);
                $html->parse("photo", true);
            }
            if ($num) {
                $html->parse('photos');
            } else {
                $html->parse('photos_noitems');
            }
            $html->parse('sections');
            $html->parse('approve_button_submit', true);
        } elseif ($cmd == "events") {
            DB::query("SELECT * FROM events_event WHERE approved = 0 ORDER BY event_id", 2);
            $num = DB::num_rows(2);
            while ($row = DB::fetch_row(2)) {
                $row['user_name'] = User::getInfoBasic($row['user_id'], 'name');
                $row['user_profile_link'] = User::url($row['user_id']);
                foreach ($row as $k => $v) {
                    $html->setvar($k, $v);
                }
                $html->setvar('description', $row['event_description']);
                $html->setvar('field_title', $row['event_title']);

                $img_sql = "SELECT * FROM events_event_image WHERE event_id = '" . $row['event_id'] . "' ";
                $img_rows = DB::rows($img_sql);

                foreach ($img_rows as $key => $img_row) {
                    $html->setvar("image_thumbnail", $g['path']['url_files'] . "events_event_images/" . $img_row['image_id'] . "_th.jpg");
                    $html->setvar("image_file", $g['path']['url_files'] . "events_event_images/" . $img_row['image_id'] . "_b.jpg");
                    $html->setvar('image_id', $img_row['image_id']);
                    $html->parse('moderator_img_item', true);
                }
                if ($img_rows) {
                    $html->parse('image', true);
                }

                $html->setvar('obj_id_type', 'event_id');
                $html->setvar('obj_id', $row['event_id']);


                $html->parse('moderator_item', true);
                $html->clean('image');
                $html->clean('moderator_img_item');
            }

            $html->setvar('delete_moderator_object_url', 'events_event_delete.php');
            $html->setvar('approve_moderator_object_url', 'moderator_approve_ajax.php');
            $html->setvar('delete_img_ajax_php', 'events_event_image_delete_ajax.php');
            $html->setvar('redirect_url', 'moderator.php?section=events');
            $html->setvar('confirm_delete_action', l('confirm_delete_events'));
            $html->setvar('confirm_approve_action', l('confirm_approve_events'));
            $html->setvar('confirm_approve_all_action', l('confirm_approve_all_events'));

            $html->parse('moderator_section', true);
            $html->parse('sections', true);
            $html->parse('approve_button_not_submit', true);
        } elseif ($cmd == "hotdates") {
            DB::query("SELECT * FROM hotdates_hotdate WHERE approved = 0 ORDER BY hotdate_id", 2);
            $num = DB::num_rows(2);
            while ($row = DB::fetch_row(2)) {
                $row['user_name'] = User::getInfoBasic($row['user_id'], 'name');
                $row['user_profile_link'] = User::url($row['user_id']);
                foreach ($row as $k => $v) {
                    $html->setvar($k, $v);
                }
                $html->setvar('description', $row['hotdate_description']);
                $html->setvar('field_title', $row['hotdate_title']);

                $img_sql = "SELECT * FROM hotdates_hotdate_image WHERE hotdate_id = '" . $row['hotdate_id'] . "' ";
                $img_rows = DB::rows($img_sql);

                foreach ($img_rows as $key => $img_row) {
                    $html->setvar("image_thumbnail", $g['path']['url_files'] . "hotdates_hotdate_images/" . $img_row['image_id'] . "_th.jpg");
                    $html->setvar("image_file", $g['path']['url_files'] . "hotdates_hotdate_images/" . $img_row['image_id'] . "_b.jpg");
                    $html->setvar('image_id', $img_row['image_id']);
                    $html->parse('moderator_img_item', true);
                }
                if ($img_rows) {
                    $html->parse('image', true);
                }

                $html->setvar('obj_id_type', 'hotdate_id');
                $html->setvar('obj_id', $row['hotdate_id']);

                $html->parse('moderator_item', true);
                $html->clean('image');
                $html->clean('moderator_img_item');
            }

            $html->setvar('delete_moderator_object_url', 'hotdates_hotdate_delete.php');
            $html->setvar('approve_moderator_object_url', 'moderator_approve_ajax.php');
            $html->setvar('delete_img_ajax_php', 'hotdates_hotdate_image_delete_ajax.php');
            $html->setvar('redirect_url', 'moderator.php?section=hotdates');
            $html->setvar('confirm_delete_action', l('confirm_delete_hotdates'));
            $html->setvar('confirm_approve_action', l('confirm_approve_hotdates'));
            $html->setvar('confirm_approve_all_action', l('confirm_approve_all_hotdates'));
            $html->parse('moderator_section', true);
            $html->parse('sections', true);
            $html->parse('approve_button_not_submit', true);
        } elseif ($cmd == "partyhouz") {
            DB::query("SELECT * FROM partyhouz_partyhou WHERE approved = 0 ORDER BY partyhou_id", 2);
            $num = DB::num_rows(2);
            while ($row = DB::fetch_row(2)) {
                $row['user_name'] = User::getInfoBasic($row['user_id'], 'name');
                $row['user_profile_link'] = User::url($row['user_id']);
                foreach ($row as $k => $v) {
                    $html->setvar($k, $v);
                }
                $html->setvar('description', $row['partyhou_description']);
                $html->setvar('field_title', $row['partyhou_title']);

                $img_sql = "SELECT * FROM partyhouz_partyhou_image WHERE partyhou_id = '" . $row['partyhou_id'] . "' ";
                $img_rows = DB::rows($img_sql);

                foreach ($img_rows as $key => $img_row) {
                    $html->setvar("image_thumbnail", $g['path']['url_files'] . "partyhouz_partyhou_images/" . $img_row['image_id'] . "_th.jpg");
                    $html->setvar("image_file", $g['path']['url_files'] . "partyhouz_partyhou_images/" . $img_row['image_id'] . "_b.jpg");
                    $html->setvar('image_id', $img_row['image_id']);
                    $html->parse('moderator_img_item', true);
                }
                if ($img_rows) {
                    $html->parse('image', true);
                }

                $html->setvar('obj_type', 'partyhouz');
                $html->setvar('obj_id_type', 'partyhou_id');
                $html->setvar('obj_id', $row['partyhou_id']);

                $html->parse('moderator_item', true);
                $html->clean('image');
                $html->clean('moderator_img_item');
            }
            $html->setvar('delete_moderator_object_url', 'partyhouz_partyhou_delete.php');
            $html->setvar('approve_moderator_object_url', 'moderator_approve_ajax.php');
            $html->setvar('delete_img_ajax_php', 'partyhouz_partyhou_image_delete_ajax.php');
            $html->setvar('redirect_url', 'moderator.php?section=partyhouz');
            $html->setvar('confirm_delete_action', l('confirm_delete_partyhouz'));
            $html->setvar('confirm_approve_action', l('confirm_approve_partyhouz'));
            $html->setvar('confirm_approve_all_action', l('confirm_approve_all_partyhouz'));
            $html->parse('moderator_section', true);
            $html->parse('sections', true);
            $html->parse('approve_button_not_submit', true);
        } elseif ($cmd == "craigs") {
            for ($i = 1; $i <= 11; $i++) {
                DB::query("select * from adv_cats where id=" . $i);
                if ($cat = DB::fetch_row()) {

                    $adv_table = "adv_" . $cat['eng'];
                    $rows = DB::rows("SELECT * FROM " . $adv_table . " WHERE approved = 0");
                    if ($rows) {
                        foreach ($rows as $key => $row) {

                            $row['user_name'] = User::getInfoBasic($row['user_id'], 'name');
                            $row['user_profile_link'] = User::url($row['user_id']);
                            foreach ($row as $k => $v) {
                                $html->setvar($k, $v);
                            }

                            $html->setvar('field_title', $row['body']);
                            $html->setvar('description', $row['subject']);

                            $html->setvar('obj_type', 'craigs');
                            $html->setvar('obj_id_type', 'craigs_id');
                            $html->setvar('obj_id', $row['id']);

                            $html->setvar("var_adv_cat_id", $cat['id']);
                            $html->setvar("var_adv_cat_name", l($cat['name']));
                            $html->setvar("var_adv_cat_name_eng", $cat['eng']);


                            DB::query("select * from adv_razd where id=" . to_sql($row['razd_id']) . "");
                            if (($adv_razd = DB::fetch_row())) {
                                $html->setvar("var_adv_razd_id", $adv_razd['id']);
                                $html->setvar("var_adv_razd_name", l($adv_razd['name']));
                            }
                            $html->parse('category', true);

                            if (isset($row['price'])) {
                                $html->setvar('price', $row['price']);
                                $html->parse('price', true);
                            }

                            $img_rows = DB::rows("SELECT * FROM adv_images WHERE adv_cat_id = '" . $row['cat_id'] . "' and adv_id = '" . $row['id'] . "' ");
                            foreach ($img_rows as $key => $img) {
                                $html->setvar("image_thumbnail", $g['path']['url_files'] . "adv_images/" . $img['id'] . "_th.jpg");
                                $html->setvar("image_file", $g['path']['url_files'] . "adv_images/" . $img['id'] . "_b.jpg");
                                $html->setvar('image_id', $img['id']);
                                $html->parse('moderator_img_item', true);
                            }
                            if ($img_rows) {
                                $html->parse('image', true);
                            }

                            $html->parse('moderator_item', true);
                            $html->clean('price');
                            $html->clean('category');

                            $html->clean('image');
                            $html->clean('moderator_img_item');
                        }
                    }
                }
            }

            $html->setvar('delete_moderator_object_url', 'adv_delete.php');
            $html->setvar('approve_moderator_object_url', 'moderator_approve_ajax.php');
            $html->setvar('delete_img_ajax_php', 'events_event_image_delete_ajax.php');
            $html->setvar('redirect_url', 'moderator.php?section=craigs');
            $html->setvar('confirm_delete_action', l('confirm_delete_craigs'));
            $html->setvar('confirm_approve_action', l('confirm_approve_craigs'));
            $html->setvar('confirm_approve_all_action', l('confirm_approve_all_craigs'));
            $html->parse('moderator_section', true);
            $html->parse('sections', true);
            $html->parse('approve_button_not_submit', true);
        } elseif ($cmd == "wowslider") {
            DB::query("SELECT * FROM wowslider WHERE approved = 0 ORDER BY event_id", 2);
            $num = DB::num_rows(2);
            while ($row = DB::fetch_row(2)) {
                $row['user_name'] = User::getInfoBasic($row['user_id'], 'name');
                $row['user_profile_link'] = User::url($row['user_id']);
                foreach ($row as $k => $v) {
                    $html->setvar($k, $v);
                }
                $html->setvar('description', $row['description']);
                $html->setvar('field_title', $row['title']);

                $html->setvar("image_thumbnail", $g['path']['url_files'] . "wowslider/" . $row['img_path']);
                $html->setvar("image_file", $g['path']['url_files'] . "wowslider/" . $row['img_path']);

                $html->setvar('image_id', substr($row['img_path'], 0, strpos($row['img_path'], ".")));
                $html->parse('moderator_img_item', true);
                if ($row['img_path'])
                    $html->parse('image', true);

                $html->setvar('obj_id_type', 'wowslider_id');
                $html->setvar('obj_id', $row['event_id']);


                $html->parse('moderator_item', true);
                $html->clean('image');
                $html->clean('moderator_img_item');
            }

            $html->setvar('delete_moderator_object_url', 'events_event_delete.php');
            $html->setvar('approve_moderator_object_url', 'moderator_approve_ajax.php');
            $html->setvar('delete_img_ajax_php', 'events_event_image_delete_ajax.php');
            $html->setvar('redirect_url', 'moderator.php?section=wowslider');
            $html->setvar('confirm_delete_action', l('confirm_delete_wowslider'));
            $html->setvar('confirm_approve_action', l('confirm_approve_wowslider'));
            $html->setvar('confirm_approve_all_action', l('confirm_approve_all_wowslider'));

            $html->parse('moderator_section', true);
            $html->parse('sections', true);
            $html->parse('approve_button_not_submit', true);
        } elseif ($cmd == "users_reports") {
            DB::query("SELECT * FROM users_reports ORDER BY priority", 2);
            $num = DB::num_rows(2);
            while ($row = DB::fetch_row(2)) {
                $ban_to = User::getInfoBasic($row['user_to'], "ban_global");
                if ($ban_to) {
                    $row['ban_title'] = ucfirst(l('unban_user'));
                } else {
                    $row['ban_title'] = ucfirst(l('ban_user'));
                }
                $row['from_user_name'] = User::getInfoBasic($row['user_from'], 'name');
                $row['to_user_name'] = User::getInfoBasic($row['user_to'], 'name');
                $row['user_profile_link'] = User::url($row['user_to']);
                $row['msg'] = nl2br($row['msg']);
                $row['priority'] = ucfirst($row['priority']);
                $row['date'] = date("d M, Y", strtotime($row['date']));
                foreach ($row as $k => $v) {
                    $html->setvar($k, $v);
                }

                $html->parse('moderator_report', true);
            }

            $html->setvar('redirect_url', 'moderator.php?section=users_reports');
            $html->parse('moderator_users_reports', true);
            $html->parse('sections', true);
        } elseif ($cmd == "support_tickets") { /* Added by Divyesh - 13-10-2023 */
            DB::query("SELECT * FROM support_tickets WHERE assign_to = " . $g_user['user_id'] . " ORDER BY priority", 2);
            $num = DB::num_rows(2);
            while ($row = DB::fetch_row(2)) {
                $row['from_user_name'] = User::getInfoBasic($row['user_from'], 'name');
                $row['user_profile_link'] = User::url($row['user_from']);
                $row['msg'] = nl2br($row['msg']);
                $row['priority'] = ucfirst($row['priority']);
                $row['date'] = date("d M, Y", strtotime($row['date']));

                $row['status_dropdown'] = "<select class='ticket_status' data-id='{$row['id']}'>
                <option value='1' " . ($row['status'] == '1' ? 'selected' : '') . ">" . ucfirst(l('open')) . "</option>
                <option value='0' " . ($row['status'] == '0' ? 'selected' : '') . ">" . ucfirst(l('close')) . "</option>
                </select>";

                if ($row['attachment'] != '') {
                    $patch = Common::getOption('url_files', 'path');
                    $attached_file = $patch . "support_ticket/" . $row['attachment'];
                    $row['attachment'] = '<a href="' . $attached_file . '" data-lightbox="attachment_' . $row['id'] . '">' . l('click_to_open') . '</a>';
                }
                foreach ($row as $k => $v) {
                    $html->setvar($k, $v);
                }

                $html->parse('support_tickets', true);
            }

            $saved = get_session("saved");
            $html->setvar("saved", $saved);
            delses("saved");

            $html->setvar('redirect_url', 'moderator.php?section=support_tickets');
            $html->parse('moderator_support_tickets', true);
            $html->parse('sections', true);
        }


        // TemplateEdge::parseColumn($html);

        parent::parseBlock($html);
    }
}

$page = new CHon("", $g['tmpl']['dir_tmpl_main'] . "moderator.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

if (Common::getOption('set', 'template_options') !== 'urban1') {
    $search = new CSearch("search", $g['tmpl']['dir_tmpl_main'] . "_search.html");
    $page->add($search);
} else {
    if (Common::isParseModule('profile_colum_narrow')) {
        if (guid()) {
            $column_narrow = new CProfileNarowBox('profile_column_narrow', $g['tmpl']['dir_tmpl_main'] . '_profile_column_narrow.html');
            $page->add($column_narrow);
        } else {
            $loginForm = new CLoginForm('login_form', $g['tmpl']['dir_tmpl_main'] . '_login_form.html');
            $page->add($loginForm);
        }
    }
}
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$group_list = new Cgroups("group_list", null);
$page->add($group_list);

include("./_include/core/main_close.php");
