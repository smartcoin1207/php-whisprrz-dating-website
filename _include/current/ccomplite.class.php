<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

class CComplite extends UserFields {

    function parseBlock(&$html)
    {
        global $g;
        global $p;
        global $g_user;
		CBanner::getBlock($html, 'right_column');
		//nnsscc-diamond 20200227 start-nnsscc_diamond
			if($html->varExists('user_allowed_feature')) {
				$html->setvar('user_allowed_feature', User::accessCheckFeatureSuperPowersGetList());
			}

			$profileBgVideoPlayDisabled = 0;

			//if($isJoinStep1) {
				$profileBgVideoPlayDisabled = intval(Common::isOptionActive('main_page_video_stop_on_join_page'));
			//}
			$html->setvar('profile_bg_video_play_disabled', $profileBgVideoPlayDisabled);
			
			$isCustom = true;
            $userId = get_param('uid', $g_user['user_id'] ?? '');
            $userName = get_param('name', $g_user['name'] ?? '');
            if (($userId == $g_user['user_id'] && $userName == ($g_user['name'] ?? '')) || get_param('display') == 'encounters') {
                /*
				if (Common::isOptionActive('youtube_video_background_users_urban')
                    && (get_param('display') == 'profile' || Common::isOptionActive('youtube_video_background_users_all_pages_urban'))) {
					$html->parse('profile_customization_video_js');
                    $html->parse('profile_customization_video');
                    $html->parse('profile_customization_video_add_tip');
                }
				*/
				$html->parse('profile_customization_video_js');
                $html->parse('profile_customization_video');
                $html->parse('profile_customization_video_add_tip');
                $dir = Common::getOption('dir_tmpl_main', 'tmpl') . 'images/patterns';
				$patterns = array_flip(readAllFileArrayOfDir($dir, '', SORT_NUMERIC, '', '', 'png'));
                foreach ($patterns as $key => $pattern) {
                    $html->setvar('pattern', $pattern);
                    $html->setvar('num', trim($key));
                    if ($key > 0) {
                        $html->parse('profile_customization_item');
                    }
                }
                if(Common::getOption('mode_profile') == 'smart') {
                    $html->parse('background_custom', false);

                }
                $html->parse('customization');
            }
			// CProfileNarowBox::Customization($html);	
		//nnsscc-diamond end        
        $complite =   User::profileComplite();
        $html->setvar('profile_complite_percent',$complite['completed']);
        $html->setvar('profile_empty_percent',100 - $complite['completed']);
        $html->setvar('basic',$complite['basic']);
        $html->setvar('personalc',$complite['personalc']);
        $html->setvar('partnerc',$complite['partnerc']);
        $html->setvar('profile_complite_percent',$complite['completed']);
        $html->setvar('your_profile_is_level_completed', lSetVars('your_profile_is_level_completed', array('level' => $complite['completed'])));
        if (Common::isOptionActive('partner_settings', 'options') || Common::isOptionActive('personal_settings', 'options')) {
            if (Common::isOptionActive('partner_settings', 'options')) {
                $html->parse('yes_partner');
            }
            if (Common::isOptionActive('personal_settings', 'options')) {
                $html->parse('yes_personal');
            }

            $html->parse('yes_settings');
        }
        if($p == 'home.php') {
            $html->parse('profile_completion_on');
        } else {
            $html->parse('profile_completion_off');
        }
        parent::parseBlock($html);
    }

}