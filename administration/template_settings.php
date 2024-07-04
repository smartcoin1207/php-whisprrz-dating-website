<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
include("../_include/core/administration_start.php");

if (Common::isMultisite()) {
    redirect('home.php');
}


$part = get_param("part", "main");
$tmpl = get_param("tmpl");
$toListTemplate = true;
if ($part && $tmpl) {
    if (isOptionActiveLoadTemplateSettings('template_edit_settings', null, $part, $tmpl)){
        $toListTemplate = false;
    }
}
if ($toListTemplate) {
    redirect('template.php');
}

class CAdminTemplateEditSettings extends CHtmlBlock
{
	var $message_template = "";

	function action()
	{
		global $g;
		$cmd = get_param("cmd", "");
		$part = get_param("part");
		$tmpl = get_param("tmpl");

        parent::action();
	}

    function excludeNotAvailableTmpl($part, $tmpl)
	{
        if ($part == 'mobile') {
            $tmplOptionSet = loadTemplateSettings('mobile', $tmpl, 'set');
            return $tmplOptionSet != $this->tmplOptionSet;
        } else {
            return false;
        }
    }

    static function getSettings($tmpl){
        $menuSettings = array();
        $defaultArea = '';
        if ($tmpl == 'edge') {
            $defaultArea = 'general_settings';
            $menuSettings = array(
                // 'general_settings' => array('type' => 'config', 'class' => 'm_general'),

                'color_scheme_general' => array('type' => 'config', 'class' => 'm_general'),
                'color_scheme_visitor' => array('type' => 'config'),
                'main_page_settings' => array('type' => 'config', 'page' => 'blogs_list.php'),
                'main_page_block_order' => array('type' => 'order', 'page' => 'index.php'),
                'join_page_settings' => array('type' => 'config', 'page' => ''),

                'color_scheme_member' => array('type' => 'config'),

                'member_settings' => array('type' => 'config'),
                'member_home_page' => array('type' => 'order', 'page' => ''),
                'member_profile_tabs' => array('type' => 'order', 'page' => ''),
                'member_profile_inner_tabs' => array('type' => 'order', 'page' => ''),

                'member_column_left_order' => array('type' => 'order', 'page' => ''),
                'member_column_right_order' => array('type' => 'order', 'page' => ''),

                'member_header_menu' => array('type' => 'order', 'page' => ''),
                'member_header_menu_short' => array('type' => 'order', 'page' => ''),
                'member_user_additional_menu' => array('type' => 'order', 'page' => ''),

                'member_visited_additional_menu' => array('type' => 'order', 'page' => ''),
                'member_visited_additional_menu_inner' => array('type' => 'order', 'page' => ''),
                'member_visited_right_column_menu' => array('type' => 'order', 'page' => ''),

                'gallery_settings' => array('type' => 'config', 'class' => 'm_gallery'),
                'wall_settings' => array('type' => 'config', 'class' => 'm_wall'),

                'groups_settings' => array('type' => 'config', 'class' => 'm_group'),
                'member_groups_tabs' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),
                'member_groups_inner_tabs' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),
                'member_groups_column_left_order' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),
                'member_groups_column_right_order' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),
                'member_groups_header_menu_short' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),
                'member_groups_additional_menu' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),

                'member_groups_visited_additional_menu' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),
                'member_groups_visited_additional_menu_inner' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),
                'member_groups_visited_right_column_menu' => array('type' => 'order', 'page' => '', 'class' => 'm_group'),

                'events_settings' => array('type' => 'config', 'class' => 'm_events'),

                'blogs_settings' => array('type' => 'config', 'class' => 'm_blogs'),
                'blogs_column_right_order' => array('type' => 'order', 'page' => '', 'class' => 'm_blogs'),
                'blogs_visited_right_column_menu' => array('type' => 'order', 'page' => '', 'class' => 'm_blogs'),

                'live_settings' => array('type' => 'config', 'class' => 'm_live'),
            );
        }
        return array('settings' => $menuSettings,
                     'default' => $defaultArea);
    }

	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $p;
		global $area;

        $error = get_param('error' , '');
        $errorMsg = '';
        if ($error != '') {
            $errors = explode('_', $error);
            foreach ($errors as $value) {
                $errorMsg .= l('error_upload_file_' . $value) . '\r\n';
            }
            if ($errorMsg != '') {
                $html->setvar('error', $errorMsg);
                $html->parse('upload_error');
            }
        }

        /* Menu */
        $part = get_param("part", "main");
        $html->setvar("part", $part);

        $tmpl = get_param("tmpl");
		$settingsType = getTemplateSettingsType($part, $tmpl);
// 		var_dump($settingsType); die();

        $this->tmplOptionSet = Common::getOption('set', 'template_options');
        $this->tmplOptionSettigs = isOptionActiveLoadTemplateSettings('template_edit_settings', null, $part, $tmpl);

        $tmplDir = $g['path']['dir_tmpl'];
        $tmplPart = $part;

        /* Templates */
		$dir = $tmplDir . $tmplPart . '/';
		$templates = array();
		if (is_dir($dir)) {
	   		if ($dh = opendir($dir)) {
		        while (($file = readdir($dh)) !== false) {
					if (is_dir($dir . $file) and substr($file, 0, 1) != ".") {
						$templates[$file] = array($file, ucfirst($file));
					}
		        }
		        closedir($dh);
    		}
		}

		sort($templates);

		$html->setvar("tmpl_this", $tmpl);
		$html->setvar("tmpl_title_this", ucfirst($tmpl));

		foreach ($templates as $k => $v) {
            if ($this->excludeNotAvailableTmpl($part, $v[0])){
                continue;
            }
            $html->setvar("tmpl_link", $v[0]);
            $html->setvar("title", $v[1]);

			if ($tmpl == $v[0]) {
				$html->parse("mtmpl_on", false);
			} else {
				$html->setblockvar("mtmpl_on", "");
			}
			$html->parse("mtmpl", true);
        }
        /* Templates */

		$dir = $tmplDir . $tmplPart . "/" . $tmpl . "/./";
		if (is_dir($dir)){
    		if ($dh = opendir($dir)){
                while (($file = readdir($dh)) !== false){
                    if (!is_dir($dir . $file) and substr($file, 0, 1) != "."){
                            $ltitle = substr($file, 0, strlen($file) - 5);
                            $ltitle = trim(str_replace("_", " ", $ltitle));
                            $ltitle = ucfirst($ltitle);
                            $html->setvar("title", $ltitle);
                            $html->setvar("tmpl_page", basename($file));

							$html->parse("tmpl_off", false);
							$html->setblockvar("tmpl_on", "");

							$html->parse("tmpl", true);
                    }
                }
                closedir($dh);
            }
            $html->parse("tmpl_edit", true);
        }

        $html->parse("menu_settings_on", false);
        $html->parse('menu_settings', false);

        $html->parse("pages_off", false);
        $html->parse("css_off", false);
        $html->parse("js_off", false);
        $html->parse("images_off", false);

        $html->setvar("title", l('main_area'));
        $html->parse("main_area_off", false);
        $html->parse("area_on", false);
        $html->parse("area", true);
        /* Menu */

        /* Menu settings */
        $menuSettingsTemplate = self::getSettings($settingsType);
        $menuSettings = $menuSettingsTemplate['settings'];
        $area = get_param('area', $menuSettingsTemplate['default']);
        $html->setvar('area', $area);

        $descriptionModule = "{$area}_description";
        if ($descriptionModule != l($descriptionModule)) {
            $html->setvar('module_description', l($descriptionModule));
        }

        // var_dump($menuSettings); die();

        foreach ($menuSettings as $key => $item) {
            $html->setvar('settings_item_class', isset($item['class']) ? $item['class'] : '');
            $html->setvar('settings_item_area', $key);
            $html->setvar('settings_item_title', l($key));
            if ($key == $area) {
                $html->parse('settings_item_on', false);
                $html->clean('settings_item_off');
            } else {
                $html->parse('settings_item_off', false);
                $html->clean('settings_item_on');
            }
            $html->parse('settings_item', true);

        }
        $html->parse('settings', false);
        /* Menu settings */
		$html->setvar("message_template", $this->message_template);

		parent::parseBlock($html);
	}
}

$settingsType = getTemplateSettingsType($part, $tmpl);

$menuSettingsTemplate = CAdminTemplateEditSettings::getSettings($settingsType);
$menuSettings = $menuSettingsTemplate['settings'];
$area = get_param('area', $menuSettingsTemplate['default']);
$urlParams = '&part=main&tmpl=' . $tmpl . '&area=' . $area;
$content = false;

if (isset($menuSettings[$area])) {
    if ($menuSettings[$area]['type'] == 'order') {
        $content = new ListBlocksOrder('page_content', $g['tmpl']['dir_tmpl_administration'] . "_block_order.html");
        $params = array('tmpl' => $settingsType,
                        'module' => $area,
                        'urlParams' => $urlParams);
        if ($menuSettings[$area]['page']) {
            $params['langsPage'] = $menuSettings[$area]['page'];
        }
        $content::setParam($params);

    } elseif ($menuSettings[$area]['type'] == 'config') {
        $content = new CAdminConfig('page_content', $g['tmpl']['dir_tmpl_administration'] . '_config.html');
        $content->setModule("{$settingsType}_{$area}");
        $content->setSort('position');
        $content->setUrlParams($urlParams);
    }
}

$tmpls = array('main' => $g['tmpl']['dir_tmpl_administration'] . 'template_settings.html',
               'template_edit_menu' => $g['tmpl']['dir_tmpl_administration'] . '_template_menu.html');
$page = new CAdminTemplateEditSettings("", $tmpls);
if ($content) {
    $page->add($content);
}

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");