<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class SettingsField extends CHtmlBlock
{

    public $settings;
    public $num = 0;
    private $setOptionTmpl;
    private $optionEnableDisable;

    public function allowShowOption($name)
    {

        $hideOption = User::isSettingEnabled($name);

        return $hideOption;
    }

    public function parseBlock(&$html)
    {

        AudioGreeting::parseProfileSettings($html);

        $this->fieldsList();
        if (is_array($this->settings)) {

            $hideOption = Common::getOption('hide_profile_settings', 'template_options');
            $notificationSettingsUrban = false;
            foreach ($this->settings as $key => $item) {
                if (!self::allowShowOption($key)) {

                    continue;
                }

                if (isset($item['active']) && ($item['active'] == "Y" || $item['active'] == true)) {

                    if (!$notificationSettingsUrban && isset($item['group']) && $item['group'] == 2) {
                        $notificationSettingsUrban = true;
                    }
                    $group = isset($item['group']) ? '_' . $item['group'] : '';

                    if ($item['type'] == 'radio') {
                        $this->prepareRadio($html, $item['name'], $item['value'], $item['default'], $item['label'], $group);
                    } elseif ($item['type'] == 'select') {
                        $this->prepareSelect($html, $item['value'], $item['default'], $item['label'], $item['name'], $item['sort'], $group);
                    } elseif ($item['type'] == 'selectLangs') {
                        $this->prepareSelectLangs($html, $item['value'], $item['default'], $item['label'], $item['name'], $item['sort'], $group);
                    } elseif ($item['type'] == 'text') { //nsc-eric-cuigao-20201201
                        $this->prepareText($html, $item['value'], $item['default'], $item['label'], $item['name'], '', $group);
                    } elseif ($item['type'] == 'dropdown') {
                        $this->prepareDropdown($html, $item['name'], $item['value'], $item['title'], $group);
                    } elseif ($item['type'] == 'dropdown_see_calendar') {
                        $this->prepareDropdownCalendar($html, $item['name'], $item['value'], $item['title'], $group);
                    } elseif ($item['type'] == 'slider') {
                        $this->prepareSlider($html, $item['name'], $item['default'], $item['label'], $group);
                    } elseif ($item['type'] == 'multidropdown') {
                        $this->prepareMultiDropdown($html, $item['name'], $item['value'], $item['title'], $group);
                    }
                }
                $html->setblockvar('field_text_item', ''); //nsc-eric-cuigao-20201201
                $html->setblockvar('field_radio_item', '');
                $html->setblockvar('field_select_item', '');
                $html->setblockvar('field_dropdown_item', '');
                $html->setblockvar('field_dropdown_item_calendar', '');
                $html->setblockvar('field_slider', '');
                $html->setblockvar('field_multi_dropdown_item', '');
            }

            if ($notificationSettingsUrban && $html->blockExists('notification_settings')) {
                $html->parse('notification_settings');
            }

            $isActiveInvisibleMode = Common::isActiveFeatureSuperPowers('invisible_mode');
            if ($html->blockExists('invisible_mode_settings')) {
                if (Common::isOptionActive('free_site') || !$isActiveInvisibleMode || ($isActiveInvisibleMode && User::isSuperPowers())) {
                    $html->parse('invisible_mode_settings');
                    if ($html->blockExists('invisible_mode_settings_btn')) {
                        $html->parse('invisible_mode_settings_btn');
                    }
                } elseif ($html->blockExists('invisible_mode_settings_upgrade')) {
                    $html->parse('invisible_mode_settings_upgrade');
                }
            }
            if ($html->blockExists('invisible_mode')) {
                $html->parse('invisible_mode');
            }

            $display = get_param('display');
            $block = "settings_{$display}";
            if ($display && $html->blockExists($block)) {
                $html->parse($block);
            }
            if ($display == '' && $this->setOptionTmpl == 'urban' && Common::isMobile()) {
                $html->parse('settings_menu');
            }
            if (Common::isOptionActive('autotranslator_enabled') && $html->blockExists('autotranslator_settings')) {
                $html->parse('autotranslator_settings');
            }
            parent::parseBlock($html);
        }
    }

    public function fieldsList()
    {
        global $g;
        global $g_user;
        global $sitePart;
        global $sitePartParam;

        $this->setOptionTmpl = Common::getTmplSet();
        $this->setOptionTmpl = 'old';

        $this->optionTmplName = Common::getTmplName();
        $this->optionTmplSettingsGroup = Common::getOptionTemplate('group_profile_settings');
        $isMobile = Common::isMobile();
        if ($g['options']['select_language'] == "Y") {
            $langs = Common::listLangs($sitePart);
            if ($langs) { // && !Common::isMobile()
                $this->settings['lang'] = array(
                    'value' => $langs,
                    'sort' => true,
                    'name' => 'set_language' . $sitePartParam,
                    'label' => l('select_language'),
                    'default' => Common::getOption('lang_loaded', 'main'),
                    'type' => 'selectLangs',
                    'active' => $g['options']['select_language'],
                );
                if ($this->setOptionTmpl == 'urban') {
                    $this->settings['lang']['label'] = l('interface_language');
                    $this->settings['lang']['group'] = 1;
                }
            }
        }

        if (Common::isOptionActive('color_scheme_settings', 'template_options')) {
            // $scheme = Common::getOption('color_scheme', 'template_options');
            // var_dump($scheme);
            // die();
            //unset($scheme['custom']); //nnsscc-diamond-20200211
            //unset($scheme['default']); //nnsscc-diamond-20200211

            $scheme = DB::rows('SELECT * FROM color_scheme');

            $colorScheme = array();
            //$colorScheme[''] = l('please_choose'); //nnsscc-diamond-20200211
            foreach ($scheme as $key => $value) {
                $colorScheme[$value['color']] = $value['title'];
            }
            $this->settings['color_scheme'] = array(
                'value' => $colorScheme,
                'sort' => false,
                'name' => 'color_scheme',
                'label' => l('color_scheme_settings'),
                'default' => $g_user['color_scheme'],
                'type' => 'select',
                'active' => Common::isOptionActive('allow_users_color_scheme'),
            );
        }

        if (!$isMobile) {
            $optionEnableDisable = array(1 => l('on'), 2 => l('off'));
        } else {
            $optionEnableDisable = array(1 => l('enabled'), 2 => l('disabled'));
        }
        $this->optionEnableDisable = $optionEnableDisable;

        $this->settings['set_email_mail'] = array(
            'label' => l('new_mail_alert'),
            // 'value' => 1,
            'name' => 'new_mail_alert',
            'default' => $g_user['set_email_mail'],
            'type' => 'slider',
            'active' => Common::isOptionActive('mail') && Common::isEnabledAutoMail('mail_message'),
        ); //popcorn 7/12/2023

        $this->settings['set_events_banner_activity'] = array(
            'value' => $optionEnableDisable,
            'label' => l('events_banner_activity'),
            'name' => 'events_banner_activity',
            'default' => $g_user['set_events_banner_activity'],
            'type' => 'slider',
            'active' => 1,
        );

        $this->settings['set_nsc_banner_activity'] = array(
            'value' => $optionEnableDisable,
            'label' => l('nsc_banner_activity'),
            'name' => 'nsc_banner_activity',
            'default' => $g_user['set_nsc_banner_activity'],
            'type' => 'slider',
            'active' => 1,
        );

        $this->settings['set_partyhouz_banner_activity'] = array(
            'value' => $optionEnableDisable,
            'label' => l('partyhouz_banner_activity'),
            'name' => 'partyhouz_banner_activity',
            'default' => $g_user['set_partyhouz_banner_activity'],
            'type' => 'slider',
            'active' => 1,
        );

        //nnsscc-diamond-20200328-end
        if (!$isMobile) {

            $setEmailInterest = array(
                'value' => $optionEnableDisable,
                'name' => 'interest_alert_options',
                'label' => l('show_interest_alert'),
                'default' => $g_user['set_email_interest'],
                'type' => 'slider',
                'active' => Common::isOptionActive('wink') && Common::isEnabledAutoMail('interest'),
            );

            if ($this->setOptionTmpl != 'urban') {
                $this->settings['set_email_interest'] = $setEmailInterest;
            }
        }

        $wallLikeCommentAlert = array(
            'value' => $optionEnableDisable,
            'default' => $g_user['wall_like_comment_alert'],
            'type' => 'slider',
            'label' => l('wall_like_comment_alert'),
            'name' => 'wall_like_comment_alert',
            'active' => Wall::isActive() && Common::isOptionActive('wall_like_comment_alert')
            && (Common::isEnabledAutoMail('wall_alert_message')
                || Common::isEnabledAutoMail('wall_alert_like')
                || Common::isEnabledAutoMail('wall_alert_comment')),
        );

        if ($this->setOptionTmpl != 'urban') {
            $this->settings['wall_like_comment_alert'] = $wallLikeCommentAlert;
        }

        $set_albums_to_see = array(
            'users' => l("All users' albums"),
            'friends' => l("My friends' albums"),
        );
        if (!$isMobile) {
            $this->settings['albums_to_see'] = array(
                'value' => $set_albums_to_see,
                'default' => $g_user['albums_to_see'],
                'type' => 'select',
                'name' => 'albums_to_see',
                'label' => l('albums_to_see'),
                'sort' => false,
                'active' => Common::isOptionActive('gallery'),
            );
        }
        $set_default_online_view = array(
            'B' => l('Single and Couple'),
            'C' => l('Couple only'),
            'M' => l('Men only'),
            'F' => l('Women only'),
        );
        $sql = 'SELECT gender FROM const_orientation GROUP BY gender';
        DB::query($sql);
        if (DB::num_rows() > 1) {
            $genders = true;
        } else {
            $genders = false;
        }
        $this->settings['default_online_view'] = array(
            'value' => $set_default_online_view,
            'default' => $g_user['default_online_view'],
            'type' => 'select',
            'name' => 'default_online_view',
            'label' => l('default_online_view'),
            'sort' => false,
            'active' => $genders && Common::isOptionActive('user_choose_default_profile_view'),
        );
        if (DB::result("SELECT id FROM email WHERE mail = " . to_sql(guser('mail'), 'Text')) == 0) {
            $newsletter = '2';
            // echo "N"; die();

        } else {
            $newsletter = '1';
            // echo "Y"; die();
        }
        if (!$isMobile) {
            $optionEnableDisableYN = array('1' => l('on'), '2' => l('off'));
        } else {
            $optionEnableDisableYN = array('1' => l('enabled'), '2' => l('disabled'));
        }

        $newsletterOption = array(
            'value' => $optionEnableDisableYN,
            'default' => $newsletter,
            'type' => 'slider',
            'name' => 'newsletter',
            'active' => Common::isOptionActive('newsletter'),
            'label' => l('date_newsletter'),
        );
        if ($this->setOptionTmpl != 'urban') {
            $this->settings['newsletter'] = $newsletterOption;
        }

        if (get_cookie("c_user") == $g_user['name'] and get_cookie("c_password") == $g_user['password']) {

            $autologin = '1';
        } else {
            $autologin = '2';
        }

        $this->settings['autologin'] = array(
            'value' => $optionEnableDisableYN,
            'default' => $autologin,
            'type' => 'slider',
            'name' => 'auto_login',
            'label' => l('auto_login'),
            'active' => true,
        );

        $matchMailOption = array(
            'value' => $optionEnableDisable,
            'default' => $g_user['match_mail'],
            'type' => 'slider',
            'name' => 'match_mail',
            'label' => l('match_mail_settings'),
            'active' => Common::isOptionActive('active', 'match_mail') && Common::isEnabledAutoMail('match_mail'),
        );
        if ($this->setOptionTmpl != 'urban') {
            $this->settings['match_mail'] = $matchMailOption;
        }

        if (!$isMobile) {
            // var_dump('smart'); die();
            $this->settings['smart_profile'] = array(
                'default' => $g_user['smart_profile'],
                'type' => 'slider',
                'name' => 'smart_profile',
                'label' => l('smart_profile_settings'),
                'active' => Common::isOptionActive('allow_users_profile_mode'),
            );
        }

        $wallOnlyPost = array(
            'value' => $optionEnableDisable,
            'default' => $g_user['wall_only_post'],
            'type' => 'slider',
            'name' => 'wall_only_post',
            'label' => l('wall_only_post_settings'),
            'active' => !Common::isOptionActive('only_friends_wall_posts'),
        );

        if ($this->setOptionTmpl != 'urban') {
            $this->settings['wall_only_post'] = $wallOnlyPost;
        }

        if (!$isMobile) {

            $this->settings['sound'] = array(
                'default' => $g_user['sound'],
                'type' => 'slider',
                'name' => 'sound',
                'label' => lCascade(l('im_sound_settings'), array('im_sound_settings_' . $this->optionTmplName)),
                'active' => Common::isOptionActive('im'),
            );
            if (isset($this->optionTmplSettingsGroup['sound'])) {
                $this->settings['sound']['group'] = $this->optionTmplSettingsGroup['sound'];
            }
        }
        if (get_cookie('frameworks_version') == '1') {
            $Mversion = '1';
            $Dversion = '1';
        } else {
            $Mversion = '2';
            $Dversion = '2';
        }
        if (countFrameworks('mobile') && countFrameworks('main')) {
            if (!$isMobile) {

                $this->settings['framework_version'] = array(
                    'value' => $optionEnableDisable,
                    'default' => $Mversion,
                    'type' => 'slider',
                    'name' => 'framework_version',
                    'label' => l('mobile_version'),
                    'active' => Common::isOptionActive('frameworks_version'),
                );
            } else {

                $this->settings['framework_version'] = array(
                    'value' => $optionEnableDisableYN,
                    'default' => $Dversion,
                    'type' => 'slider',
                    'name' => 'framework_version',
                    'label' => l('desktop_version'),
                    'active' => Common::isOptionActive('frameworks_version'),
                );
            }
        }

        $zonesArr = DateTimeZone::listIdentifiers();
        $zones = array();
        foreach ($zonesArr as $v) {
            $zones[$v] = $v;
        }

        if ($zones) { // && !Common::isMobile()
            $this->settings['timezone'] = array(
                'value' => $zones,
                'sort' => false,
                'name' => 'timezone',
                'label' => l('users_time_zone'),
                'default' => $g_user['timezone'],
                'type' => 'select',
                'active' => true,
                'group' => -1,
            );
        }
        //nnsscc-diamond-20201102-start

        //gregory mann 7/11/2023-start
        $presence_items = [];

        $presence_items[] = array(
            'label' => l('set_my_presence_couples'),
            'name' => 'set_my_presence_couples',
            'default' => $g_user['set_my_presence_couples'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $presence_items[] = array(
            'label' => l('set_my_presence_males'),
            'name' => 'set_my_presence_males',
            'default' => $g_user['set_my_presence_males'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $presence_items[] = array(
            'label' => l('set_my_presence_females'),
            'name' => 'set_my_presence_females',
            'default' => $g_user['set_my_presence_females'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $presence_items[] = array(
            'label' => l('set_my_presence_transgender'),
            'name' => 'set_my_presence_transgender',
            'default' => $g_user['set_my_presence_transgender'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $presence_items[] = array(
            'label' => l('set_my_presence_nonbinary'),
            'name' => 'set_my_presence_nonbinary',
            'default' => $g_user['set_my_presence_nonbinary'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $this->settings['set_my_presence'] = array(
            'value' => $presence_items,
            'title' => l('set_my_presence'),
            'name' => 'set_my_presence',
            'type' => 'dropdown',
            'active' => 1,
        );

        //popcorn added start 2024-01-31
        $map_show_items = [];

        $map_show_items[] = array(
            'label' => l('set_my_map_couples'),
            'name' => 'set_my_map_couples',
            'default' => $g_user['set_my_map_couples'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $map_show_items[] = array(
            'label' => l('set_my_map_males'),
            'name' => 'set_my_map_males',
            'default' => $g_user['set_my_map_males'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $map_show_items[] = array(
            'label' => l('set_my_map_females'),
            'name' => 'set_my_map_females',
            'default' => $g_user['set_my_map_females'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $map_show_items[] = array(
            'label' => l('set_my_map_transgender'),
            'name' => 'set_my_map_transgender',
            'default' => $g_user['set_my_map_transgender'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $map_show_items[] = array(
            'label' => l('set_my_map_nonbinary'),
            'name' => 'set_my_map_nonbinary',
            'default' => $g_user['set_my_map_nonbinary'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $map_show_items[] = array(
            'label' => l('set_show_me_map'),
            'name' => 'set_show_me_map',
            'default' => $g_user['set_show_me_map'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $map_show_items[] = array(
            'label' => l('set_show_only_friends_map'),
            'name' => 'set_show_only_friends_map',
            'default' => $g_user['set_show_only_friends_map'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $this->settings['set_my_map'] = array(
            'value' => $map_show_items,
            'title' => l('set_my_map'),
            'name' => 'set_my_map',
            'type' => 'dropdown',
            'active' => 1,
        );

        //popcorn added start 2024-01-31

        //popcorn added for calendar settings start 2024-02-27
        $calendar_see_items[] = array(
            'label' => l('set_friends_see_my_calendar'),
            'name' => 'set_friends_see_my_calendar',
            'default' => $g_user['set_friends_see_my_calendar'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        // $calendar_see_items[] = array(
        //     'label' => l('set_group_see_my_calendar'),
        //     'name' => 'set_group_see_my_calendar',
        //     'default' => $g_user['set_group_see_my_calendar'],
        //     'type' => 'dropdownItem',
        //     'active' => 1,
        // );

        $calendar_see_items[] = array(
            'label' => l('set_male_see_my_calendar'),
            'name' => 'set_male_see_my_calendar',
            'default' => $g_user['set_male_see_my_calendar'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $calendar_see_items[] = array(
            'label' => l('set_female_see_my_calendar'),
            'name' => 'set_female_see_my_calendar',
            'default' => $g_user['set_female_see_my_calendar'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $calendar_see_items[] = array(
            'label' => l('set_couple_see_my_calendar'),
            'name' => 'set_couple_see_my_calendar',
            'default' => $g_user['set_couple_see_my_calendar'],
            'type' => 'dropdownItem',
            'active' => 1,
        );
        
        $calendar_see_items[] = array(
            'label' => l('set_transgender_see_my_calendar'),
            'name' => 'set_transgender_see_my_calendar',
            'default' => $g_user['set_transgender_see_my_calendar'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $calendar_see_items[] = array(
            'label' => l('set_nonbinary_see_my_calendar'),
            'name' => 'set_nonbinary_see_my_calendar',
            'default' => $g_user['set_nonbinary_see_my_calendar'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $this->settings['set_see_my_calendar'] = array(
            'value' => $calendar_see_items,
            'title' => l('set_see_my_calendar'),
            'name' => 'set_see_my_calendar',
            'type' => 'dropdown',
            'active' => 1,
        );
        //popcorn added for calendar settings end 2024-02-27

        //popcorn added for calendar post settings start 2024-02-27
        $calendar_post_items[] = array(
            'label' => l('set_friends_post_my_calendar'),
            'name' => 'set_friends_post_my_calendar',
            'default' => $g_user['set_friends_post_my_calendar'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        // $calendar_post_items[] = array(
        //     'label' => l('set_group_post_my_calendar'),
        //     'name' => 'set_group_post_my_calendar',
        //     'default' => $g_user['set_group_post_my_calendar'],
        //     'type' => 'dropdownItem',
        //     'active' => 1,
        // );

        $calendar_post_items[] = array(
            'label' => l('set_male_post_my_calendar'),
            'name' => 'set_male_post_my_calendar',
            'default' => $g_user['set_male_post_my_calendar'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $calendar_post_items[] = array(
            'label' => l('set_female_post_my_calendar'),
            'name' => 'set_female_post_my_calendar',
            'default' => $g_user['set_female_post_my_calendar'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $calendar_post_items[] = array(
            'label' => l('set_couple_post_my_calendar'),
            'name' => 'set_couple_post_my_calendar',
            'default' => $g_user['set_couple_post_my_calendar'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $calendar_post_items[] = array(
            'label' => l('set_transgender_post_my_calendar'),
            'name' => 'set_transgender_post_my_calendar',
            'default' => $g_user['set_transgender_post_my_calendar'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $calendar_post_items[] = array(
            'label' => l('set_nonbinary_post_my_calendar'),
            'name' => 'set_nonbinary_post_my_calendar',
            'default' => $g_user['set_nonbinary_post_my_calendar'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $this->settings['set_post_my_calendar'] = array(
            'value' => $calendar_post_items,
            'title' => l('set_post_my_calendar'),
            'name' => 'set_post_my_calendar',
            'type' => 'dropdown_see_calendar',
            'active' => 1,
        );
        //popcorn added for calendar post settings end 2024-02-27

        $profile_visitor_items = [];
        $profile_visitor_items[] = array(
            'label' => l('set_profile_visitor_couples'),
            'name' => 'set_profile_visitor_couples',
            'default' => $g_user['set_profile_visitor_couples'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $profile_visitor_items[] = array(
            'label' => l('set_profile_visitor_males'),
            'name' => 'set_profile_visitor_males',
            'default' => $g_user['set_profile_visitor_males'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $profile_visitor_items[] = array(
            'label' => l('set_profile_visitor_females'),
            'name' => 'set_profile_visitor_females',
            'default' => $g_user['set_profile_visitor_females'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $profile_visitor_items[] = array(
            'label' => l('set_profile_visitor_transgender'),
            'name' => 'set_profile_visitor_transgender',
            'default' => $g_user['set_profile_visitor_transgender'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $profile_visitor_items[] = array(
            'label' => l('set_profile_visitor_nonbinary'),
            'name' => 'set_profile_visitor_nonbinary',
            'default' => $g_user['set_profile_visitor_nonbinary'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $this->settings['set_my_profile'] = array(
            'value' => $profile_visitor_items,
            'title' => l('set_profile_visitor'),
            'name' => 'set_my_profile',
            'type' => 'dropdown',
            'active' => 1,
        );

        $private_pav_items = [];

        $private_pav_items[] = array(
            'label' => l('set_album_video_couples'),
            'name' => 'set_album_video_couples',
            'default' => $g_user['set_album_video_couples'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $private_pav_items[] = array(
            'label' => l('set_album_video_males'),
            'name' => 'set_album_video_males',
            'default' => $g_user['set_album_video_males'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $private_pav_items[] = array(
            'label' => l('set_album_video_females'),
            'name' => 'set_album_video_females',
            'default' => $g_user['set_album_video_females'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $private_pav_items[] = array(
            'label' => l('set_album_video_transgender'),
            'name' => 'set_album_video_transgender',
            'default' => $g_user['set_album_video_transgender'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $private_pav_items[] = array(
            'label' => l('set_album_video_nonbinary'),
            'name' => 'set_album_video_nonbinary',
            'default' => $g_user['set_album_video_nonbinary'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        //photo private

        $private_pav_items[] = array(
            'label' => l('set_photo_couples'),
            'name' => 'set_photo_couples',
            'default' => $g_user['set_photo_couples'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $private_pav_items[] = array(
            'label' => l('set_photo_males'),
            'name' => 'set_photo_males',
            'default' => $g_user['set_photo_males'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $private_pav_items[] = array(
            'label' => l('set_photo_females'),
            'name' => 'set_photo_females',
            'default' => $g_user['set_photo_females'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $private_pav_items[] = array(
            'label' => l('set_photo_transgender'),
            'name' => 'set_photo_transgender',
            'default' => $g_user['set_photo_transgender'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $private_pav_items[] = array(
            'label' => l('set_photo_nonbinary'),
            'name' => 'set_photo_nonbinary',
            'default' => $g_user['set_photo_nonbinary'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        //album
        $private_pav_items[] = array(
            'label' => l('set_album_couples'),
            'name' => 'set_album_couples',
            'default' => $g_user['set_album_couples'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $private_pav_items[] = array(
            'label' => l('set_album_males'),
            'name' => 'set_album_males',
            'default' => $g_user['set_album_males'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $private_pav_items[] = array(
            'label' => l('set_album_females'),
            'name' => 'set_album_females',
            'default' => $g_user['set_album_females'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $private_pav_items[] = array(
            'label' => l('set_album_transgender'),
            'name' => 'set_album_transgender',
            'default' => $g_user['set_album_transgender'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $private_pav_items[] = array(
            'label' => l('set_album_nonbinary'),
            'name' => 'set_album_nonbinary',
            'default' => $g_user['set_album_nonbinary'],
            'type' => 'dropdownItem',
            'active' => 1,
        );

        $this->settings['set_my_pav'] = array(
            'value' => $private_pav_items,
            'title' => l('set_album_video'),
            'name' => 'set_my_pav',
            'type' => 'multidropdown',
            'active' => 1,
        );

        //gregory mann 7/11/2023-start

        //nnsscc-diamond-20200328-end

        /* URBAN */
        $setValue = array(
            'anyone' => l('anyone'),
            'members' => l('only_members'),
        );
        if ($this->allowShowOption('set_who_view_profile')) {
            $this->settings['set_who_view_profile'] = array(
                'value' => $setValue,
                'default' => $g_user['set_who_view_profile'],
                'type' => 'select',
                'name' => 'set_who_view_profile',
                'label' => l('who_can_view_your_profile'),
                'sort' => false,
                'active' => true,
                'group' => 1,
            );
        }
        if ($this->allowShowOption('set_can_comment_photos')) {
            $this->settings['set_can_comment_photos'] = array(
                'value' => $setValue,
                'default' => $g_user['set_can_comment_photos'],
                'type' => 'select',
                'name' => 'set_can_comment_photos',
                'label' => l('who_can_comment_on_your_photos'),
                'sort' => false,
                'active' => true,
                'group' => 1,
            );
        }

        /* Translation */
        if (Common::isOptionActive('autotranslator_enabled')) {
            $langs = Common::listLangs($sitePart);

            if ($langs) {
                $langsOff = explode(',', $g_user['translation_off']);

                foreach ($langs as $v => $k) {
                    if ($g_user['lang'] != $v) {
                        $enabled = 1;
                        if (in_array($v, $langsOff)) {
                            $enabled = 2;
                        }

                        $this->settings['set_translation[' . $v . ']'] = array(
                            'value' => $optionEnableDisable,
                            'default' => $enabled,
                            'type' => 'slider',
                            'name' => 'set_translation[' . $v . ']',
                            'label' => $k,
                            'active' => 1,
                            'group' => 8,
                        );
                    }
                }
                $this->settings['translation_off'] = array(
                    'value' => $g_user['translation_off'],
                    'default' => '',
                    'type' => 'text',
                    'name' => 'translation_off',
                    'label' => 'translation_off',
                    'active' => 0,
                    'group' => 8,
                );
            }

        }

        /* Translation */

        if ($this->allowShowOption('set_notif_new_msg') && isset($g_user['set_notif_new_msg'])) {

            $this->settings['set_notif_new_msg'] = array(
                'value' => $optionEnableDisable,
                'default' => $g_user['set_notif_new_msg'],
                'type' => 'slider',
                'name' => 'set_notif_new_msg',
                'label' => l('set_notif_new_msg'),
                'active' => Common::isEnabledAutoMail('new_message'),
                'group' => 2,
            );
        }

        if (!$isMobile && $this->setOptionTmpl == 'urban') {
            $this->settings['wall_like_comment_alert'] = $wallLikeCommentAlert;
            $this->settings['wall_like_comment_alert']['label'] = l('wall_like_comment_alert_urban');
            $this->settings['wall_like_comment_alert']['group'] = 2;
        }

        if ($this->allowShowOption('set_notif_new_comments') && isset($g_user['set_notif_new_comments'])) {

            $this->settings['set_notif_new_comments'] = array(
                'value' => $optionEnableDisable,
                'default' => $g_user['set_notif_new_comments'],
                'type' => 'slider',
                'name' => 'set_notif_new_comments',
                'label' => l('set_notif_new_comments'),
                'active' => Common::isEnabledAutoMail('new_comment_photo'),
                'group' => 2,
            );
        }

        if ($this->allowShowOption('set_notif_profile_visitors')) {

            $this->settings['set_notif_profile_visitors'] = array(
                'value' => $optionEnableDisable,
                'default' => $g_user['set_notif_profile_visitors'],
                'type' => 'slider',
                'name' => 'set_notif_profile_visitors',
                'label' => l('profile_visitors'),
                'active' => Common::isEnabledAutoMail('profile_visitors'),
                'group' => 2,
            );
        }
        
        if ($this->allowShowOption('set_notif_want_to_meet_you') && isset($g_user['set_notif_want_to_meet_you'])) {

            $this->settings['set_notif_want_to_meet_you'] = array(
                'value' => $optionEnableDisable,
                'default' => $g_user['set_notif_want_to_meet_you'],
                'type' => 'slider',
                'name' => 'set_notif_want_to_meet_you',
                'label' => l('set_notif_want_to_meet_you'),
                'active' => Common::isEnabledAutoMail('want_to_meet_you'),
                'group' => 2,
            );
        }

        if ($this->allowShowOption('set_notif_mutual_attraction') && isset($g_user['set_notif_mutual_attraction'])) {

            $this->settings['set_notif_mutual_attraction'] = array(
                'value' => $optionEnableDisable,
                'default' => $g_user['set_notif_mutual_attraction'],
                'type' => 'slider',
                'name' => 'set_notif_mutual_attraction',
                'label' => l('set_notif_mutual_attraction'),
                'active' => Common::isEnabledAutoMail('mutual_attraction'),
                'group' => 2,
            );
        }

        if ($this->allowShowOption('set_notif_gifts')) {

            $this->settings['set_notif_gifts'] = array(
                'value' => $optionEnableDisable,
                'default' => $g_user['set_notif_gifts'],
                'type' => 'slider',
                'name' => 'set_notif_gifts',
                'label' => l('gifts'),
                'active' => Common::isEnabledAutoMail('gift'),
                'group' => 2,
            );
        }
        if ($this->allowShowOption('set_notif_voted_photos')) {

            $this->settings['set_notif_voted_photos'] = array(
                'value' => $optionEnableDisable,
                'default' => $g_user['set_notif_voted_photos'],
                'type' => 'slider',
                'name' => 'set_notif_voted_photos',
                'label' => l('who_voted_on_your_photos'),
                'active' => Common::isEnabledAutoMail('voted_photo') && Common::isOptionActive('photo_rating_enabled'),
                'group' => 2,
            );
        }

        if ($this->setOptionTmpl == 'urban') {
            $this->settings['wall_only_post'] = $wallOnlyPost;
            $this->settings['wall_only_post']['label'] = l('wall_only_post_settings_urban');
            $this->settings['wall_only_post']['group'] = 1;

            if (!$isMobile) {
                $this->settings['set_email_interest'] = $setEmailInterest;
                $this->settings['set_email_interest']['label'] = l('show_interest_alert_urban');
                $this->settings['set_email_interest']['group'] = 2;
            }

            $this->settings['match_mail'] = $matchMailOption;
            $this->settings['match_mail']['label'] = l('match_mail_settings_urban');
            $this->settings['match_mail']['group'] = 2;

            $this->settings['newsletter'] = $newsletterOption;
            $this->settings['newsletter']['label'] = l('date_newsletter_urban');
            $this->settings['newsletter']['group'] = 2;
        }

        $notFree = !Common::isOptionActive('free_site');
        if ($this->allowShowOption('set_hide_my_presence')) {

            $this->settings['set_hide_my_presence'] = array(
                'value' => $optionEnableDisable,
                'default' => $g_user['set_hide_my_presence'],
                'type' => 'slider',
                'name' => 'set_hide_my_presence',
                'label' => l('hide_my_presence_from_other_users'),
                'active' => true,
                'group' => 3,
            );
        }

        if ($this->allowShowOption('set_do_not_show_me_visitors')) {

            $this->settings['set_do_not_show_me_visitors'] = array(
                'value' => $optionEnableDisable,
                'default' => $g_user['set_do_not_show_me_visitors'],
                'type' => 'slider',
                'name' => 'set_do_not_show_me_visitors',
                'label' => l('dont_show_me_as_a_profile_visitor'),
                'active' => true,
                'group' => 3,
            );
        }
        /* URBAN */

        if ($this->allowShowOption('set_notif_show_my_age') && Common::isOptionActive('show_age_profile', 'edge_member_settings')) {

            $this->settings['set_notif_show_my_age'] = array(
                'value' => $optionEnableDisable,
                'default' => $g_user['set_notif_show_my_age'],
                'type' => 'slider',
                'name' => 'set_notif_show_my_age',
                'label' => l('show_my_age_in_the_profile'),
                'active' => true,
                'group' => 1,
            );
        }

        $optionName = 'set_notif_push_notifications';
        if ($this->allowShowOption($optionName)) {
            $isActive = Common::isAppIos() || Common::isAppAndroid();
            $this->setOption($optionName, 'radio', $isActive, 2);
        }
    }

    public function setOption($optionName, $type = 'radio', $active = true, $group = 0)
    {
        $this->settings[$optionName] = array(
            'value' => $this->optionEnableDisable,
            'default' => intval(guser($optionName)),
            'type' => $type,
            'name' => $optionName,
            'label' => l($optionName),
            'active' => $active,
        );
        if ($group) {
            $this->settings[$optionName]['group'] = $group;
        }
    }

    public function setOptionCheckIsAllowed($optionName, $type = 'radio', $active = true, $group = 0)
    {
        if ($this->allowShowOption($optionName)) {
            self::setOption($optionName, $type, $active, $group);
        }
    }

    public function save($value)
    {

        global $g_user;
        $this->fieldsList();
        $sql = array();
        foreach ($this->settings as $key => $item) {
            if (isset($value[$item['name']]) && isset($item['active']) && ($item['active'] == "Y" || $item['active'] == true)) {
                if ($item['name'] == 'newsletter') {
                    if ($value['newsletter'] == '1') {
                        User::emailAdd(guser('mail'));
                    } else {
                        User::emailRemove(guser('mail'));
                    }
                } elseif ($item['name'] == 'framework_version') {
                    if ($value['framework_version'] == '1') {
                        set_cookie("frameworks_version", "1");
                    } elseif ($value['framework_version'] == '2') {
                        set_cookie('frameworks_version', "");
                    }
                } elseif ($item['name'] == 'auto_login') {
                    if ($value['auto_login'] == "1") {
                        set_cookie("c_user", $g_user['name']);
                        set_cookie("c_password", $g_user['password']);
                    } else {
                        set_cookie("c_user", "");
                        set_cookie("c_password", "");
                    }
                } elseif ($item['name'] == 'sound') {
                    if ($g_user['sound'] != $value['sound']) {
                        User::saveImSound($value['sound']);
                    }

                } else {

                    if ($item['name'] == 'set_language') { //eric-cuigao-20201124-end
                        $lang = guser('lang');
                        if (empty($lang)) {
                            $lang = 'default';
                        }
                        if ($lang != $value['set_language']) {
                            set_session('alert_after_page_loaded', 'changes_saved');
                        }
                    } elseif ($item['name'] == 'timezone') {
                        $value['timezone'] = trim($value['timezone']);
                    }

                    $sql[$key] = $value[$item['name']];

                }
            }
        }

        foreach ($this->settings['set_my_presence']['value'] as $item1) {
            //BEGIN GREGORY MANN 7/11/2023
            if ($item1['name'] == 'set_my_presence_couples') {
                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {

                    DB::execute("
                            UPDATE user SET `set_my_presence_couples` = " . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_my_presence_males') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_my_presence_males=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_my_presence_females') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_my_presence_females=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_my_presence_transgender') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_my_presence_transgender=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_my_presence_nonbinary') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_my_presence_nonbinary=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            }

            if (isset($value[$item1['name']])) {
                $sql[$item1['name']] = $value[$item1['name']];
            }

            //END GREGORY MANN 7/11/2023

        }

        foreach ($this->settings['set_my_map']['value'] as $item1) {
            //BEGIN GREGORY MANN 01/31/2024
            if ($item1['name'] == 'set_my_map_couples') {
                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {

                    DB::execute("
                            UPDATE user SET `set_my_map_couples` = " . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_my_map_males') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_my_map_males =" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_my_map_females') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_my_map_females =" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_my_map_transgender') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_my_map_transgender =" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_my_map_nonbinary') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_my_map_nonbinary =" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            }
            if (isset($value[$item1['name']])) {
                $sql[$item1['name']] = $value[$item1['name']];
            }
            //END GREGORY MANN 7/11/2023
        }

        foreach ($this->settings['set_see_my_calendar']['value'] as $item1) {
            //BEGIN GREGORY MANN 01/31/2024
            if ($item1['name'] == 'set_couple_see_my_calendar') {
                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET `set_couple_see_my_calendar` = " . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_male_see_my_calendar') {
                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            `set_male_see_my_calendar` =" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_female_see_my_calendar') {
                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            `set_female_see_my_calendar` =" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_transgender_see_my_calendar') {
                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            `set_transgender_see_my_calendar` =" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_nonbinary_see_my_calendar') {
                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            `set_nonbinary_see_my_calendar` =" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } 
            if (isset($value[$item1['name']])) {
                $sql[$item1['name']] = $value[$item1['name']];
            }
            //END GREGORY MANN 7/11/2023
        }

        foreach ($this->settings['set_post_my_calendar']['value'] as $item1) {
            //BEGIN GREGORY MANN 01/31/2024
            if ($item1['name'] == 'set_couple_post_my_calendar') {
                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET `set_couple_post_my_calendar` = " . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_male_post_my_calendar') {
                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            `set_male_post_my_calendar` =" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_female_post_my_calendar') {
                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            `set_female_post_my_calendar` =" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_transgender_post_my_calendar') {
                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            `set_transgender_post_my_calendar` =" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_nonbinary_post_my_calendar') {
                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            `set_nonbinary_post_my_calendar` =" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            }
            if (isset($value[$item1['name']])) {
                $sql[$item1['name']] = $value[$item1['name']];
            }
            //END GREGORY MANN 7/11/2023
        }

        foreach ($this->settings['set_my_profile']['value'] as $item1) {
            //BEGIN GREGORY MANN 7/11/2023
            if ($item1['name'] == 'set_profile_visitor_couples') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_profile_visitor_couples=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_profile_visitor_males') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_profile_visitor_males=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_profile_visitor_females') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_profile_visitor_females=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_profile_visitor_transgender') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_profile_visitor_transgender=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_profile_visitor_nonbinary') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_profile_visitor_nonbinary=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            }

            if (isset($value[$item1['name']])) {
                $sql[$item1['name']] = $value[$item1['name']];

            }

            //END GREGORY MANN 7/11/2023

        }

        foreach ($this->settings['set_my_pav']['value'] as $item1) {
            //BEGIN GREGORY MANN 7/11/2023
            if ($item1['name'] == 'set_album_video_couples') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_album_video_couples=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_album_video_males') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_album_video_males=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_album_video_females') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_album_video_females=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }

            } else if ($item1['name'] == 'set_album_video_transgender') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_album_video_transgender=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }

            } else if ($item1['name'] == 'set_album_video_nonbinary') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_album_video_nonbinary=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }

            } else if ($item1['name'] == 'set_photo_couples') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_photo_couples=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_photo_males') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_photo_males=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_photo_females') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_photo_females=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }

            } else if ($item1['name'] == 'set_photo_transgender') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_photo_transgender=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }

            } else if ($item1['name'] == 'set_photo_nonbinary') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_photo_nonbinary=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }

            } else if ($item1['name'] == 'set_album_couples') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_album_couples=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_album_males') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_album_males=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_album_females') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_album_females=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_album_transgender') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_album_transgender=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            } else if ($item1['name'] == 'set_album_nonbinary') {

                if ($g_user["orientation"] == 5 && $g_user['nsc_couple_id'] > 0) {
                    DB::execute("
                            UPDATE user SET
                            set_album_nonbinary=" . $value[$item1['name']] . "
                            WHERE user_id=" . $g_user['nsc_couple_id'] . "
                        ");
                }
            }

            if (isset($value[$item1['name']])) {
                $sql[$item1['name']] = $value[$item1['name']];
            }

            //END GREGORY MANN 7/11/2023
        }

        if (Common::isOptionActive('autotranslator_enabled')) {
            $langsOff = array();
            $setTranslation = get_param_array('set_translation');
            foreach ($setTranslation as $k => $v) {
                if ($v == 2) {
                    $langsOff[] = $k;
                }
            }
            $sql['translation_off'] = implode(',', $langsOff);
        }

        $invite_user_ids_calendar = get_param_array("invited_user_ids");
        $calendar_user_ids = implode(",", $invite_user_ids_calendar);

        $sql['set_post_my_calendar_users'] = $calendar_user_ids;
        DB::update('user', $sql, 'user_id = ' . guid());
    }

    public function prepareSelectLangs(&$html, $value, $default, $label, $name, $sort, $group)
    {

        foreach ($value as $title => $item) {
            if ($sort == true) {
                if ($title == $default) {
                    $html->setvar('selected', 'selected="selected"');
                } else {
                    $html->setvar('selected', '');
                }
                $html->setvar('value', $title);
                $html->setvar('title', $item);
            } else {
                if ($item == $default) {
                    $html->setvar('selected', 'selected="selected"');
                } else {
                    $html->setvar('selected', '');
                }
                $html->setvar('value', $item);
                $html->setvar('title', $title);
            }
            $html->parse('field_select_item' . $group);
            $html->setvar('name_select', $name);
            $html->setvar('label', $label);
            # $html->setblockvar('field_radio_item','');
        }

        $html->parse('field_select' . $group, false);
        $html->clean('field_select_item' . $group);
        $html->clean('field_dropdown_see_calendar' . $group);
        $html->clean('field_radio' . $group);
        $html->clean('field_text' . $group);
        $html->clean('field_slider', $group);
        $html->clean('field_dropdown' . $group); //gregory mann 7/11/2023
        $html->parse('field' . $group);
    }
    //nsc-eric-cuigao-20201201-start
    public function prepareText(&$html, $value, $default, $label, $name, $sort, $group)
    {

        foreach ($value as $item => $title) {
            $html->parse('field_text_item' . $group);
            $html->setvar('name_text', $name);
            $html->setvar('label', $label);
            # $html->setblockvar('field_radio_item','');
        }

        $html->parse('field_text' . $group, false);
        $html->clean('field_dropdown_see_calendar' . $group);
        $html->clean('field_select' . $group);
        $html->clean('field_select_item' . $group);
        $html->clean('field_radio' . $group);
        $html->clean('field_slider' . $group);
        $html->clean('field_dropdown' . $group); //gregory mann 7/11/2023
        $html->parse('field' . $group);
    }
    //nsc-eric-cuigao-20201201-end
    public function prepareSelect(&$html, $value, $default, $label, $name, $sort, $group)
    {

        foreach ($value as $item => $title) {
            if ($sort == true) {
                if ($title == $default) {
                    $html->setvar('selected', 'selected="selected"');
                } else {
                    $html->setvar('selected', '');
                }
                $html->setvar('value', $title);
                $html->setvar('title', $item);
            } else {
                if ($item == $default) {
                    $html->setvar('selected', 'selected="selected"');
                } else {
                    $html->setvar('selected', '');
                }
                $html->setvar('value', $item);
                $html->setvar('title', $title);
            }
            $html->parse('field_select_item' . $group);
            $html->setvar('name_select', $name);
            $html->setvar('label', $label);
            # $html->setblockvar('field_radio_item','');
        }

        $html->parse('field_select' . $group, false);
        $html->clean('field_select_item' . $group);
        $html->clean('field_dropdown_see_calendar' . $group);
        $html->clean('field_radio' . $group);
        $html->clean('field_slider' . $group);
        $html->clean('field_text' . $group); //nsc-eric-cuigao-20201201
        $html->clean('field_dropdown' . $group); //gregory mann 7/11/2023

        $html->parse('field' . $group);
    }

    public function prepareRadio(&$html, $name, $value, $default, $label, $group)
    {
        foreach ($value as $item => $title) {

            if (empty($default)) {
                $html->setvar('checked', 'checked');
                $default = $item;
            } elseif ($item == $default) {
                $html->setvar('checked', 'checked');
            } else {
                $html->setvar('checked', '');
            }
            $html->setvar('value', $item);
            $html->setvar('label', $label);
            $html->setvar('title', $title);
            $html->setvar('name_radio', $name);
            $html->parse('field_radio_item' . $group);
        }
        $html->parse('field_radio' . $group, false);
        $html->clean('field_radio_item' . $group);
        $html->clean('field_dropdown_see_calendar' . $group);
        $html->clean('field_select' . $group);
        $html->clean('field_slider' . $group);
        $html->clean('field_text' . $group); //nsc-eric-cuigao-20201201
        $html->clean('field_dropdown' . $group); //gregory mann 7/11/2023

        $html->parse('field' . $group);
    }

    public function prepareSlider(&$html, $name, $default, $label, $group)
    {

        if (empty($default)) {
            $html->setvar('checked', '');
        } elseif ($default == '1') {
            $html->setvar('checked', 'checked');
        } elseif ($default == '2') {
            $html->setvar('checked', '');
        }

        $html->setvar('label', $label);
        $html->setvar('name', $name);
        $html->parse('field_slider' . $group);

        $html->clean('field_dropdown_see_calendar' . $group);
        $html->clean('field_select' . $group);
        $html->clean('field_radio' . $group);
        $html->clean('field_text' . $group); //nsc-eric-cuigao-20201201
        $html->clean('field_dropdown' . $group); //gregory mann 7/11/2023

        $html->parse('field' . $group);

    }

    public function prepareDropdown(&$html, $name, $value, $title, $group)
    {

        foreach ($value as $dropitem) {

            if ($name == "set_my_presence" || $name == "set_my_profile") {
                if ($dropitem['default'] == '1') {
                    $html->setvar('checked', '');
                } elseif ($dropitem['default'] == '2') {
                    $html->setvar('checked', 'checked');
                } else {
                    $html->setvar('checked', '');
                }
            } else {
                if (empty($dropitem['default'])) {
                    $html->setvar('checked', '');
                } elseif ($dropitem['default'] == '1') {
                    $html->setvar('checked', 'checked');
                } elseif ($dropitem['default'] == '2') {
                    $html->setvar('checked', '');
                }
            }

            $html->setvar('title', $title);
            $html->setvar('name', $dropitem['name']);
            $html->setvar('label', $dropitem['label']);
            $html->setvar('value', $dropitem['name']);
            $html->parse('field_dropdown_item' . $group);
        }

        $html->setvar('anchor_id', $name . '_anchor');
        // die();

        $html->parse('field_dropdown' . $group, false);
        $html->clean('field_dropdown_item' . $group);

        $html->clean('field_dropdown_see_calendar' . $group);
        $html->clean('field_select' . $group);
        $html->clean('field_slider' . $group);
        $html->clean('field_text' . $group); //nsc-eric-cuigao-20201201
        $html->clean('field_radio' . $group); //gregory mann 7/11/2023

        $html->parse('field' . $group);
    }

    public function prepareDropdownCalendar(&$html, $name, $value, $title, $group)
    {
        global $g_user;
        foreach ($value as $dropitem) {
            if (empty($dropitem['default'])) {
                $html->setvar('checked', '');
            } elseif ($dropitem['default'] == '1') {
                $html->setvar('checked', 'checked');
            } elseif ($dropitem['default'] == '2') {
                $html->setvar('checked', '');
            }

            $html->setvar('title', $title);
            $html->setvar('name', $dropitem['name']);
            $html->setvar('label', $dropitem['label']);
            $html->setvar('value', $dropitem['name']);
            $html->parse('field_dropdown_item_calendar' . $group);
        }

        $can_post_users_ids = $g_user['set_post_my_calendar_users'];
        if ($can_post_users_ids) {
            $can_post_users_sql = "SELECT * FROM user WHERE user_id IN (" . ($can_post_users_ids) . ")";
            $can_post_users = DB::rows($can_post_users_sql);
            $can_users = [];
            foreach ($can_post_users as $key => $row) {
                $user_photo = User::getPhotoDefault($row["user_id"], "m");
                $u = array("user_name" => $row["name"], "user_id" => $row["user_id"], "user_photo" => $user_photo);
                array_push($can_users, $u);
            }
            $html->setvar('invitees', json_encode($can_users));
        }

        $html->setvar('anchor_id', $name . '_anchor');

        $html->parse('field_dropdown_see_calendar' . $group, false);
        $html->clean('field_dropdown_item_calendar' . $group);
        $html->clean('field_dropdown' . $group);
        $html->clean('field_select' . $group);
        $html->clean('field_slider' . $group);
        $html->clean('field_text' . $group); //nsc-eric-cuigao-20201201
        $html->clean('field_radio' . $group); //gregory mann 7/11/2023

        $html->parse('field' . $group);
    }

    public function prepareMultiDropdown(&$html, $name, $value, $title, $group)
    {
        global $g_user;

        $pav_titles = array("VIDEO", "PROFILE PHOTO", "ALBUM");

        for ($i = 0; $i < 3; $i++) {

            $slice = array_slice($value, $i * 5, 5, true);

            foreach ($slice as $dropitem) {
                if (empty($dropitem['default'])) {
                    $html->setvar('checked', '');
                } elseif ($dropitem['default'] == '1') {
                    $html->setvar('checked', 'checked');
                } elseif ($dropitem['default'] == '2') {
                    $html->setvar('checked', '');
                }

                /* Divyesh - 24042024 */
                $items_title = $pav_titles[$i];
                $url_main = Common::getOption('url_main', 'path');
                if ($pav_titles[$i] == "ALBUM"){
                   $items_title = "<span onclick=\"window.location.href='{$url_main}photos'\" style='cursor: pointer;'>{$pav_titles[$i]}</span>";
                }else if ($pav_titles[$i] == "PROFILE PHOTO"){
                    $items_title = "<span onclick=\"window.location.href='{$url_main}{$g_user['name_seo']}/photos'\" style='cursor: pointer;'>{$pav_titles[$i]}</span>";
                }
                
                $html->setvar('items_title', $items_title);
                /* Divyesh - 24042024 */
                $html->setvar('title', $title);
                $html->setvar('name', $dropitem['name']);
                $html->setvar('label', $dropitem['label']);
                $html->setvar('value', $dropitem['name']);
                $html->parse('field_single_dropdown_item' . $group);
            }

            $html->parse('field_multi_dropdown_item' . $group, true);
            $html->clean('field_single_dropdown_item' . $group);
            $html->parse('field_multi_dropdown' . $group);

        }

        $html->parse('field_multi_dropdown' . $group, false);
        $html->clean('field_multi_dropdown_item' . $group);
        $html->clean('field_dropdown_see_calendar' . $group);

        $html->clean('field_select' . $group);
        $html->clean('field_dropdown' . $group);

        $html->clean('field_slider' . $group);
        $html->clean('field_text' . $group); //nsc-eric-cuigao-20201201
        $html->clean('field_radio' . $group); //gregory mann 7/11/2023

        $html->parse('field' . $group);

    }
}
