<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include "../_include/core/administration_start.php";

$cmd = get_param('cmd', '');

if ($cmd != 'location') {
    $uid = get_param_int('id', 0);
    if (User::isExistsByUid($uid)) {
        $g_user = User::getInfoFull($uid);
    } else {
        redirect('users_results.php');
    }
}

class CForm extends UserFields//CHtmlBlock
{
    public $message = "";
    public $login = "";
    public function action()
    {
        global $g;
        global $g_user;
        global $p;

        $cmd = get_param('cmd', '');
        $optionsSet = Common::getOption('set', 'template_options');
        $optionsTmplName = Common::getOption('name', 'template_options');

        if ($cmd == 'update') {
            $this->message = "";
            $orientation = get_param('orientation', $g_user['orientation']);

            $name = trim(get_param('username'));
            $this->message .= User::validateName($name);

            $password = trim(get_param('password'));
            $mail = get_param('email', '');

            $month = (int) get_param('month', 1);
            $day = (int) get_param('day', 1);
            $year = (int) get_param('year', 1980);

            $country = get_param('country', '');
            $state = get_param('state', '');
            $city = get_param('city', '');

            $this->message .= User::validatePassword($password, 4, 100);
            $this->message .= User::validate('email,birthday,country');
            $this->verification('admin');

            if ($this->message == '') {
                if ($optionsTmplName != 'edge_OFF') {
                    $selectionFileds = 'join';
                    if ($optionsSet == 'urban') {
                        $this->updateLookingFor($g_user['user_id']);
                        $selectionFileds = 'update_admin_urban';
                    } elseif (self::isActive('orientation') && !Common::isOptionActive('your_orientation')) {
                        User::update(array('p_orientation' => get_checks_param('p_orientation')));
                    }

                    $this->updatePartner(get_param('id', ''));
                } else {
                    $selectionFileds = 'update_admin_urban';
                }
                $this->updateInfo(get_param('id', ''), $selectionFileds);

                $h = zodiac($year . '-' . $month . '-' . $day);

                $setSql = '';

                $goldDays = get_param('gold_days', 0);
                // $trialDays = get_param('trial_days', 0);

                $type = get_param('type', ''); //silver, gold, paltinum...
                $trial_plan_type = get_param('trial_plan_type', ''); //silver, gold, paltinum...

                $site_access_type = get_param('site_access_type', ''); //trial, free, payment

                $support_tier = get_param('support_tier', ''); // Added by divyesh - 13-10-2023

                if (($g_user['gold_days'] != $goldDays && $g_user['type'] == $type)
                    || ($g_user['gold_days'] == $goldDays && $g_user['type'] != $type)
                    || ($g_user['gold_days'] != $goldDays && $g_user['type'] != $type)
                ) {

                    if ($g_user['gold_days'] != $goldDays) {
                        $timeStamp = time() + 3600; //+60 minutes
                        $date = date('Y-m-d', $timeStamp);
                        $hour = intval(date('H', $timeStamp));

                        $setSql .= ', payment_day=' . to_sql($date) . ', payment_hour=' . to_sql($hour) . ' ';
                    }
                    User::upgradeCouple($g_user['user_id'], $goldDays, $type);
                }

                if ($goldDays == 0 && $site_access_type == 'payment') {
                    $type = '0';
                }

                //popcorn delete start 2023-12-23
                // if ($optionsSet == 'urban') {
                //     $setSql .= ', credits = ' . to_sql(get_param('credits'), 'Number');
                //     if ($goldDays > 0) {
                //         $type = 'membership';
                //         $setSql .= ", sp_sending_messages_per_day = 0";
                //     } else {
                //         if (!User::isAllowedInvisibleMode(false)) {
                //             $setSql .= ", set_hide_my_presence = '2', set_do_not_show_me_visitors = '2'";
                //         }
                //     }
                // }

                // if($optionsTmplName == 'edge' && $goldDays) {
                //     $type = 'membership';
                // }
                //popcorn delete end 2023-12-23

                if ($password != $g_user['password']) {
                    $password = User::preparePasswordForDatabase($password);
                }

                $isAdmin = get_param('user_admin');
                //Rade 2023-10-12 add start
                $setSql .= ', moderator_photo = ' . to_sql(get_param('moderator_photo'), 'Number') . ',
                                 moderator_texts = ' . to_sql(get_param('moderator_texts'), 'Number') . ',
                                 moderator_vids_video = ' . to_sql(get_param('moderator_vids_video'), 'Number') . ',
                                 moderator_profiles = ' . to_sql(get_param('moderator_profiles'), 'Number') . ',
                                 moderator_events = ' . to_sql(get_param('moderator_events'), 'Number') . ',
                                 moderator_hotdates = ' . to_sql(get_param('moderator_hotdates'), 'Number') . ',
                                 moderator_partyhouz = ' . to_sql(get_param('moderator_partyhouz'), 'Number') . ',
                                 moderator_craigs = ' . to_sql(get_param('moderator_craigs'), 'Number') . ',
                                 moderator_wowslider = ' . to_sql(get_param('moderator_wowslider'), 'Number') . ',
                                 moderator_users_reports = ' . to_sql(get_param('moderator_users_reports'), 'Number') . ',
                                 moderator_support_tickets = ' . to_sql(get_param('moderator_support_tickets'), 'Number') . ',
                                 admin = ' . to_sql($isAdmin, 'Number') . ',
                                 support_tier = ' . to_sql($support_tier);

                //Rade 2023-10-12 add end
                if ($name != $g_user['name']) {
                    $setSql .= ', `name_seo` = ' . to_sql(Router::getNameSeo($name, $g_user['user_id'], 'user'));
                }

                if ($isAdmin) {
                    DB::update('user', array('admin' => 0));
                    $adminUserId = $g_user['user_id'];
                } else {
                    $adminUserId = DB::result('SELECT `user_id` FROM `user` WHERE `admin` = 1');
                }
                Config::update('options', 'admin_user_id', $adminUserId);
                DB::execute("UPDATE user SET
                                    name = " . to_sql($name, 'Text') . ",
                                    password = " . to_sql($password, 'Text') . ",
                                    gold_days=" . to_sql($goldDays, "Number") . ",
                                    type=" . to_sql($type, "Number") . ",
                                    trial_plan_type=" . to_sql($trial_plan_type, "Number") . ",
                                    site_access_type = " . to_sql($site_access_type, "Text") . ",
                                    mail=" . to_sql($mail, "Text") . ",
                                    country_id=" . to_sql($country, "Number") . ",
                                    state_id=" . to_sql($state, "Number") . ",
                                    city_id=" . to_sql($city, "Number") . ",
                                    country=" . to_sql(Common::getLocationTitle('country', $country), 'Text') . ",
                                    state=" . to_sql(Common::getLocationTitle('state', $state), 'Text') . ",
                                    city=" . to_sql(Common::getLocationTitle('city', $city), 'Text') . ",
                                    birth='" . $year . "-" . $month . "-" . $day . "',
                                    horoscope='" . $h . "',
                                    relation='" . ((int) get_param("relation", $g_user["relation"]) . "'") . ",
                                    use_as_online = " . to_sql(get_param('use_as_online'), 'Number') .
                    $setSql . "
                                    WHERE user_id=" . $g_user['user_id'] . ";
                    ");
                // Rade 2023-10-12 update start
                $nsc_new_couple_row = DB::row('SELECT * FROM user WHERE user_id = ' . $g_user['user_id'], 1);
                if ($nsc_new_couple_row['orientation'] == "5") {
                    if ($nsc_new_couple_row['nsc_couple_id'] > 0) {
                        DB::execute("UPDATE user SET
                                    gold_days=" . to_sql($goldDays, "Number") . ",
                                    type=" . to_sql($type, "Text") . "
                                WHERE user_id=" . $g_user['nsc_couple_id'] . ";
                            ");
                    }
                }
                if (guser('city_id') != $city) {
                    User::updateGeoPosition($city);
                }

                User::setOrientation($g_user['user_id'], $orientation);

                $status = get_param('profile_status', false);
                if ($status !== false) {
                    $status = htmlentities($status, ENT_QUOTES, 'UTF-8');
                    User::updateProfileStatus($status);
                }

                redirect("$p?id=" . get_param("id") . "&action=saved");
            }
        } elseif ($cmd == "sms_alert") {
            $nsc_phone = get_param('nsc_phone', '');
            $carrier = get_param('carrier', '');
            $queryUpdate = "UPDATE user SET set_sms_alert= " . to_sql(get_param('set_sms_alert')) . ",
            set_sms_alert_mi= " . to_sql(get_param('set_sms_alert_mi')) . ",
            set_sms_alert_hd= " . to_sql(get_param('set_sms_alert_hd')) . ",
            set_sms_alert_pi= " . to_sql(get_param('set_sms_alert_pi')) . ",
            set_sms_alert_pa= " . to_sql(get_param('set_sms_alert_pa')) . ",
            set_sms_alert_rm= " . to_sql(get_param('set_sms_alert_rm')) . ",
            set_sms_alert_wm= " . to_sql(get_param('set_sms_alert_wm')) . ",
            nsc_phone= " . to_sql($nsc_phone, "Text") . ",
            carrier_provider= " . to_sql($carrier);

            if ($nsc_phone != "" && $carrier != "" && ($nsc_phone != $g_user['nsc_phone'] || $carrier != $g_user['carrier_provider'])) {
                $verifycode = Common::generateVerifyCode();
                $queryUpdate .= ", verify_code=" . to_sql($verifycode);
                $queryUpdate .= ", verify_code_date_time=" . to_sql(date("Y-m-d H:i:s"));
                $queryUpdate .= ", is_verified_c_provider='0'";

                $carriernumber = str_replace("number", $nsc_phone, $carrier);
                $smsAuto = Common::autosmsInfo('verify_code', $g_user['lang'], 2);

                $subject = $smsAuto['subject'];
                $subject = str_replace("{title}", $g['main']['title'], $subject);
                $subject = str_replace("{name}", $g_user['name'], $subject);

                $message = strip_tags($smsAuto['text']);
                $message = str_replace("{name}", $g_user['name'], $message);
                $message = str_replace("{title}", $g['main']['title'], $message);
                $message = str_replace("{code}", $verifycode, $message);

                send_sms("{$carriernumber}", $g['main']['info_mail'], $subject, $message);
            }

            $queryUpdate .= " WHERE user_id=" . $g_user['user_id'];
            DB::execute($queryUpdate);
            redirect("$p?id=" . get_param("id") . "&action=saved");
        } elseif ($cmd == "insert_photo") {
            // Rade 2023-10-12 update end
            $description = get_param("description", "");

            $this->message = User::validatePhoto("photo_file");

            if ($this->message == "") {
                $g['options']['photo_approval'] = 'N';
                $g['options']['nudity_filter_enabled'] = 'N';
                $photo_id = uploadphoto($g_user['user_id'], '', $description, 1, '../', false, 'photo_file', get_param('access')); // Divyesh - 17042024
                
                //popcorn modified s3 bucket photo 2024-05-06 start
                if(getFileDirectoryType('photo') == 2) {
                    $photo = DB::one('photo', '`photo_id` = ' . to_sql($photo_id));

                    $fileTypes = CProfilePhoto::getSizes();
                    $ext = '.jpg';
                    if(file_exists($g['path']['dir_files'] . 'temp/' . $photo['photo_id'] . '_' . $photo['hash'] . '_bm.gif')) {
                        $ext = '.gif';
                    }

                    foreach ($fileTypes as $fileType) {
                        $file_path = $g['path']['dir_files'] . 'temp/' . $photo['photo_id'] . '_' . $photo['hash'] . '_' . $fileType . $ext;
                        if(file_exists($file_path)) {
                            custom_file_upload($file_path, 'photo/' . $photo['photo_id'] . '_' . $photo['hash'] . '_' . $fileType . $ext);
                        }
                    }
                }
                //popcorn modified s3 bucket photo 2024-05-06 end
                
                redirect("$p?id=" . get_param("id") . "&action=saved");
            }
        } elseif ($cmd == "delete_photo") {

            $photo_id = get_param("photo_id", 0);
            if ($photo_id == 0) {
                return;
            }

            deletephoto($g_user['user_id'], $photo_id);

            redirect("$p?id=" . get_param("id") . "&action=delete");
        } elseif ($cmd == "approve_photo") {
            $photo_id = intval(get_param('photo_id'));
            Moderator::setNotificationTypePhoto();
            User::photoApproval($photo_id, 'add');
            Moderator::sendNotificationApproved();
            redirect("$p?id=" . get_param("id") . "&action=saved");
        } elseif ($cmd == 'location') {
            $param = get_param('param');
            $method = 'list' . get_param('method');
            echo Common::$method($param, -1);
            die();
        } elseif ($cmd == 'add_spotlight') {
            $uid = get_param('id');
            Spotlight::addItem($uid);
            redirect("{$p}?id={$uid}&action=saved");
        } elseif ($cmd == 'remove_spotlight') {
            $uid = get_param('id');
            Spotlight::removeItem($uid);
            redirect("{$p}?id={$uid}&action=saved");
        } elseif ($cmd == 'set_photo_personal_access') {
            $uid = get_param('id');
            CProfilePhoto::setPhotoPersonal(get_param('photo_id'), true);
            redirect("{$p}?id={$uid}&action=saved");
        } elseif ($cmd == 'set_photo_folder_access') {
            $uid = get_param('id');
            $folder_id = get_param('folder_id');
            CProfilePhoto::setPhotoCustomFolder(get_param('photo_id'), $folder_id, true);
            redirect("{$p}?id={$uid}&action=saved");
        } elseif($cmd == 'remove_photo_folder_access') {
            $uid = get_param('id');
            CProfilePhoto::setPhotoCustomFolder(get_param('photo_id'), 0, true);
            redirect("{$p}?id={$uid}&action=saved");
        } elseif($cmd == 'set_photo_access') {
            $uid = get_param('id');
            CProfilePhoto::setPhotoPrivate(get_param('photo_id'), true);
            redirect("{$p}?id={$uid}&action=saved");
        }
    }

    public function parseBlock(&$html)
    {
        global $g;
        global $g_user;
        global $l;

        $optionsSet = Common::getOption('set', 'template_options');
        $optionsTmplName = Common::getOption('name', 'template_options');
        $html->setvar('message', $this->message);

        $checked = 'checked';

        $html->setvar('field_physical_datails', l('physical_datails'));
        if ($g_user['moderator_photo']) {
            $html->setvar("moderator_photo", $checked);
        }
        if ($g_user['moderator_texts']) {
            $html->setvar("moderator_texts", $checked);
        }
        if ($g_user['moderator_vids_video']) {
            $html->setvar("moderator_vids_video", $checked);
        }
        if ($g_user['moderator_profiles']) {
            $html->setvar("moderator_profiles", $checked);
        }
        // Rade 2023-10-12 add start
        if ($g_user['moderator_events']) {
            $html->setvar("moderator_events", $checked);
        }
        if ($g_user['moderator_hotdates']) {
            $html->setvar("moderator_hotdates", $checked);
        }
        if ($g_user['moderator_partyhouz']) {
            $html->setvar("moderator_partyhouz", $checked);
        }
        if ($g_user['moderator_craigs']) {
            $html->setvar("moderator_craigs", $checked);
        }
        if ($g_user['moderator_wowslider']) {
            $html->setvar("moderator_wowslider", $checked);
        }
        if ($g_user['moderator_users_reports']) {
            $html->setvar("moderator_users_reports", $checked);
        }
        // Rade 2023-10-12 add end

        // Added by Divyesh - 11-10-2023
        if ($g_user['moderator_support_tickets']) {
            $html->setvar("moderator_support_tickets", $checked);
        }
        $html->parse('moderator');

        if ($g_user['admin']) {
            $html->setvar('checked_user_admin', $checked);
        }
        // Added by Divyesh - 11-10-2023
        $html->setvar("moderator_support_tier_{$g_user['support_tier']}", "selected");

        $l = loadLanguageAdmin();

        //$g_user = User::getInfoFull(get_param("id", ""));

        $html->setvar('url_profile', User::url($g_user['user_id']));
        $html->setvar("user_id", $g_user['user_id']);
        $html->setvar('username_length', $g['options']['username_length']);
        $html->setvar('paid_days_length', Common::getOption('paid_days_length'));
        $html->setvar("gold_days", $g_user['gold_days']);
        // $html->setvar('trial_days', $g_user['trial_days']);
        $html->setvar("user_name", $g_user['name']);
        $html->setvar("password", $g_user['password']);
        /* Divyesh - Add on 17042024 */
        $custom_folder = User::getInfoBasic($g_user['user_id'], 'custom_folder');
        $html->setvar("custom_folder", $custom_folder);  

        /* Divyesh - Add on 17042024 */

        /** Popcorn modified 2024-11-05 customfolders start */
        $folders_sql = "SELECT * FROM custom_folders WHERE user_id = " . to_sql($g_user['user_id'], 'Number');
        $custom_folders = DB::rows($folders_sql);

        foreach ($custom_folders as $folder) {
            $html->setvar('folder_id', $folder['id']);
            $html->setvar('custom_folder_access', $folder['name'] . " Folder");
            $html->parse('add_folder_item', true);
        }
        $html->parse('add_folder_access', false);
        $html->clean('add_folder_item');
        /** Popcorn modified 2024-11-05 customfolders end */

        if ($html->varExists('user_photo')) {
            $html->setvar('user_photo', User::getPhotoDefault($g_user['user_id'], 'm'));
        }
        if ($html->varExists('country_title')) {
            $html->setvar('country_title', l($g_user['country']));
        }
        if ($html->varExists('state_title')) {
            $html->setvar('state_title', l($g_user['state']));
        }
        if ($html->varExists('city_title')) {
            $html->setvar('city_title', l($g_user['city']));
        }

        $optionsSet = Common::getOption('set', 'template_options');
        if ($optionsSet == 'urban') {
            // $html->parse('menu_im'); // Rade 2023-10-02
            // Rade 2023-10-02 add start
            $html->parse('menu_editblog');
            $html->parse('menu_im');
            $html->parse('menu_chat');
            $html->parse('menu_mail');
            // Rade 2023-10-02 add end
            $html->setvar('field_physical_datails', l('appearance'));
            if ($optionsTmplName != 'edge') {
                $html->setvar('credits', $g_user['credits']);
                $html->parse('user_credits');
            }
            if (Common::isParseModule('people_nearby_spotlight')) {
                if ($g_user['is_photo_public'] != 'N') {
                    $html->parse('menu_add_spotlight');
                }
                if (Spotlight::isThere()) {
                    $html->parse('menu_remove_spotlight');
                }
            }
        } else {
            $html->parse('menu_editblog');
            $html->parse('menu_im');
            $html->parse('menu_chat');
            $html->parse('menu_mail');
        }

        if (Common::isOptionActive('videogallery')) {
            $html->parse('menu_user_video');
        }

        $html->parse('user_gold_days');
        $html->parse('user_trial_days');

        // if ($optionsTmplName != 'urban' && $optionsTmplName != 'edge') { // Rade 2023-10-11 delete
        $profileStatus = DB::one('profile_status', '`user_id` = ' . to_sql($g_user['user_id']));
        if ($profileStatus) {
            $html->setvar('profile_status', $profileStatus['status']);
        }

        $profileStatusMaxLength = Common::getOptionTemplateInt('profile_status_max_length');
        if (!$profileStatusMaxLength) {
            $profileStatusMaxLength = 25; //OLD template
        }
        $html->setvar('profile_status_max_length', $profileStatusMaxLength);
        $html->parse('profile_status');
        // } // Rade 2023-10-11 delete

        if ($g_user['use_as_online']) {
            $html->setvar("use_as_online", $checked);
        }

        if (IS_DEMO) {
            $html->setvar("mail", get_param("mail", 'disabled@ondemoadmin.cp'));
        } else {
            $html->setvar("mail", get_param("mail", $g_user['mail']));
        }

        $html->setvar("nsc_phone", get_param("nsc_phone", $g_user['nsc_phone']));

        //$this->parseFieldsAll($html, 'admin');

        $whereNoPrivatePhoto = '';
        $noPrivatePhoto = CProfilePhoto::isHidePrivatePhoto();
        if ($noPrivatePhoto) {
            $whereNoPrivatePhoto = ' AND `private` = "N" ';
        }
        $num_photos = DB::result("SELECT COUNT(photo_id) FROM photo WHERE user_id=" . $g_user['user_id'] . "  AND `visible` != 'P' AND `group_id` = 0 " . $whereNoPrivatePhoto);

        if ($num_photos < 4) {
            $html->parse("photo_upload", true);
        }

        $html->setvar("num_photos", $num_photos);
        
        $sql = "SELECT *, `private` AS access FROM photo WHERE user_id=" . $g_user['user_id'] . " AND `visible` != 'P' AND `group_id` = 0 " . $whereNoPrivatePhoto . " ORDER BY access, photo_id DESC;";
        $photoRows = DB::rows($sql);
        $noPrivatePhoto = Common::isOptionActiveTemplate('no_private_photos');

        $publicPhotos = [];
        $privatePhotos = [];
        $personalPhotos = [];
        $customFoldersPhotos = [];
        $folderNames = [];
        
        foreach ($custom_folders as $folder) {
            $customFoldersPhotos[$folder['id']] = [];
            $folderNames[$folder['id']] = $folder['name'];
        }

        foreach ($photoRows as $photo) {
            if ($photo['private'] == 'Y') {
                $privatePhotos[] = $photo;
            } elseif ($photo['personal'] == 'Y') {
                $personalPhotos[] = $photo;
            } elseif ($photo['in_custom_folder'] == 'Y' && (isset($photo['custom_folder_id']) && $photo['custom_folder_id'] > 0)) {
                $folderId = $photo['custom_folder_id'];
                $customFoldersPhotos[$folderId][] = $photo;
            } else {
                $publicPhotos[] = $photo;
            }
        }

        $groupedPhotos = [];

        $groupedPhotos[] = [
            "header" => "public",
            "photos" => $publicPhotos
        ];

        $groupedPhotos[] = [
            "header" => "private",
            "photos" => $privatePhotos
        ];
        $groupedPhotos[] = [
            "header" => "personal",
            "photos" => $personalPhotos
        ];

        foreach ($customFoldersPhotos as $key => $folderPhotos) {
            $groupedPhotos[] = [
                "header" => $folderNames[$key] . " Folder",
                "photos" => $folderPhotos
            ];
        }

        // var_dump($groupedPhotos); die();

        foreach ($groupedPhotos as $key => $folderPhotos) {
            $html->setvar('grouped_photo_header', $folderPhotos['header']);
            $photos = $folderPhotos['photos'];

            foreach ($photos as $i => $row) {
                $html->setvar("numer", $i);

                if ($row) {
                    $html->setvar('photo', User::getPhotoFile($row, 's', $g_user['gender']));
                    $html->setvar("photo_id", $row['photo_id']);
                    
                    if (!$noPrivatePhoto) {
                        $html->setvar("photo_access", l($row['private'] == 'N' ? 'make_private' : 'make_public'));
                        $html->parse('photo_access', false);
                    }

                    /* Divyesh - added on - 17042024 */
                    $html->setvar("photo_personal_access", l($row['personal'] == 'N' ? 'make_personal' : 'remove_personal')); 
                    /* Divyesh - added on - 17042024 */

                    /** Popcorn modified added 2024-11-05 custom folders start */
                    if($row['custom_folder_id']) {
                        $html->setvar('photo_folder_remove', 'Remove from Folder');
                        $html->parse('remove_customfolder', false);
                    }

                    foreach ($custom_folders as $folder) {
                        $html->setvar('folder_id', $folder['id']);
                        $html->setvar('folder_name', "Folder". " " . $folder['name']);
                        $html->parse('make_customfolder_item', true);
                    }
                    $html->parse('make_customfolder', false);
                    $html->clean('make_customfolder_item');
                    /** Popcorn modified added 2024-11-05 custom folders end */

                    $html->setvar("photo_name", $row['photo_name']);
                    $html->setvar("description", nl2br($row['description']));

                    $html->setvar("visible", $row['visible'] == "Y" ? "" : "(pending audit)");
                    if ($row['visible'] == "Y") {
                        $html->clean('photo_approve');
                    } else {
                        $html->parse('photo_approve', false);
                    }

                    if ($i == 1 or $i == 3) {
                        $html->parse("photo_odd", true);
                    } else {
                        $html->setblockvar("photo_odd", "");
                    }

                    if ($i == 2) {
                        $html->parse("photo_even", true);
                    } else {
                        $html->setblockvar("photo_even", "");
                    }

                    if ($i % 4 == 0) {
                        $html->parse('photo_delimiter');
                    } else {
                        $html->setblockvar('photo_delimiter', '');
                    }

                    $html->subcond(!$row['gif'], 'photo_edit_image');
                    $html->subcond(!$row['gif'], 'photo_rotate');

                    $html->parse("photo_item", true);

                    $html->parse("photo", false);
                }
            }

            $html->parse("photo_grouped_container", true);
            $html->clean('photo_item');
            $html->clean('nophoto_item');
            $html->clean('photo');
        }

        if (!Common::isOptionActive('personal_settings')) {
            $html->parse('btn_update', false);
        }
        $html->setvar('paid_days_length', Common::getOption('paid_days_length'));

        if (!$noPrivatePhoto) {
            $html->parse('photo_add_access', false);
        }

        //Rade 2023-10-12 add start
        if ($g_user['set_sms_alert'] == "on") {
            $html->setvar('sms_alert_checked', 'checked');
        } else {
            $html->setvar('sms_alert_checked', '');
        }
        if ($g_user['set_sms_alert_mi'] == "on") {
            $html->setvar('sms_alert_mi_checked', 'checked');
        } else {
            $html->setvar('sms_alert_mi_checked', '');
        }
        if ($g_user['set_sms_alert_pi'] == "on") {
            $html->setvar('sms_alert_pi_checked', 'checked');
        } else {
            $html->setvar('sms_alert_pi_checked', '');
        }
        if ($g_user['set_sms_alert_pa'] == "on") {
            $html->setvar('sms_alert_pa_checked', 'checked');
        } else {
            $html->setvar('sms_alert_pa_checked', '');
        }
        if ($g_user['set_sms_alert_hd'] == "on") {
            $html->setvar('sms_alert_hd_checked', 'checked');
        } else {
            $html->setvar('sms_alert_hd_checked', '');
        }
        if ($g_user['set_sms_alert_rm'] == "on") {
            $html->setvar('sms_alert_rm_checked', 'checked');
        } else {
            $html->setvar('sms_alert_rm_checked', '');
        }
        if ($g_user['set_sms_alert_wm'] == "on") {
            $html->setvar('sms_alert_wm_checked', 'checked');
        } else {
            $html->setvar('sms_alert_wm_checked', '');
        }
        $carrierselected = $g_user['carrier_provider'];

        //$where = " WHERE country_id={$g_user['country_id']} AND state_id={$g_user['state_id']} ";
        $where = " WHERE country_id={$g_user['country_id']} ";

        $carriers_options = Common::getCarrierOptionsSelect($where, $carrierselected);
        $html->setvar('carriers_options', $carriers_options);

        parent::parseBlock($html);
    }
    //Rade 2023-10-12 add end
}

$page = new CForm('', $g['tmpl']['dir_tmpl_administration'] . 'users_edit.html', false, false, false, 'admin', get_param('id'));
$page->formatValue = 'entities';

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuUsers());

include "../_include/core/administration_close.php";
