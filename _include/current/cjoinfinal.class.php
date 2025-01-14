<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CJoinFinal extends UserFields//CHtmlBlock
{
    function init()
    {
        global $g;
        global $g_user;


        $facebook = false;
        $cmds = array('photo', 'skip');
        $cmd = get_param('cmd', '');
        $this->message = '';
        if (in_array($cmd, $cmds)) {
            $this->validate();
            if ($this->message == '') {
                $uid = User::add();
                if (!$uid) {
                    redirect('join.php?cmd=exists_email');
                } else {
                    $g_user['user_id'] = $uid;
                    if ($cmd == 'photo') {
                        $photo_id = uploadphoto($g_user['user_id'], get_param('photo_name', ''), get_param('description', ''), ($g['options']['photo_approval'] == 'Y' ? 0 : 1));
                        
                        //popcorn modified s3 bucket photo upload 2024-05-07
                        if(getFileDirectoryType('photo')) {
                            $photo = DB::one('photo', '`photo_id` = ' . to_sql($photo_id, 'Number'));

                            $file_sizes = CProfilePhoto::getSizes();
                            if($photo) {
                                $ext = '.jpg';
                                if(file_exists($g['path']['dir_files'] . 'temp/' . $photo_id . '_' . $photo['hash'] .'_src.gif')) {
                                    $ext = '.gif';
                                }
                                foreach ($file_sizes as $key => $size) {
                                    $file_path = $g['path']['dir_files'] . 'temp/' . $photo_id . '_' . $photo['hash'] . '_' . $size . $ext;
                                    custom_file_upload($file_path, 'photo/' . $photo_id . '_' . $photo['hash'] . '_' . $size . $ext);
                                }
                            }
                        }
                    }
                    //Common::toHomePage();
                    $this->goToHomePage();
                }
            }
        } elseif (get_session('social_id') && get_session('social_photo')) {
            set_session('j_captcha', true);
            $this->validate();
            set_session('j_captcha', false);
            if ($this->message == '') {
                $uid = User::add();
                if (!$uid) {
                    redirect('join.php?cmd=exists_email');
                } else {
                    uploadphoto($g_user['user_id'], get_param('photo_name', ''), get_param('description', ''), ($g['options']['photo_approval'] == 'Y' ? 0 : 1), '', get_session('social_photo'));
                    set_session('social_photo', false);

                    //Common::toHomePage();
                    $this->goToHomePage();
                }
            }
        }
        set_session('j_captcha', false);
    }

    function parseBlock(&$html)
    {
        if (isset($this->message))
            $html->setvar("join_message", $this->message);
        if (!Common::isOptionActive('join_with_photo_only')){
            $html->parse('skip_button');
        }
        Common::parseCaptcha($html);

        $html->setvar('profile_photo_description_length', Common::getOption('profile_photo_description_length'));

        $field = 'about_me';
        if ($html->blockExists('about_me')) {
            self::parseAboutMe($html);
        }

        parent::parseBlock($html);
    }

    function validate()
    {
        global $l;

        if (get_session("j_name") == '' or
                get_session("j_password") == '' or
                get_session("j_mail") == '' or
                get_session("j_orientation") == ''
        ) {
            $this->message .= "Error in session. Start new registration.<br>";
        }

        $this->message .= User::validateName(get_session("j_name"));
        $this->message .= User::validateEmail(get_session("j_mail"));

        // if (!get_session('j_captcha')) {
        //     $this->message .= l('incorrect_captcha') . '<br>';
        // }
    }

    function goToHomePage()
    {
        $isUserApproval = Common::isOptionActive('manual_user_approval');
        if ($isUserApproval) {
            redirect('index.php?cmd=wait_approval');
        } else {
            Common::toHomePage();
        }
    }

}
