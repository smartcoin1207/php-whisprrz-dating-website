<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */
// Rade 2023-09-23
include("../_include/core/administration_start.php");

class CCustomPage extends CHtmlBlock {

    private $sections = array('top_menu', 'narrow', 'bottom', 'not_in_menu');
    private $editSystem = array('menu_bottom_about_us');

    function init() {
        $sections = array('top_menu');
        //$sectionsTemplate = Common::getOptionTemplate('custom_pages_sections');
        //if ($sectionsTemplate) {
        //    $sections = $sectionsTemplate;
        //}
        $sections[] = 'narrow';
		$sections[] = 'bottom';
        $sections[] = 'not_in_menu';
        $this->sections = $sections;
    }

    function action() {
        global $p;

        $cmd = get_param('cmd');
        if($cmd == 'update') {
            $pages = get_param('pages');
            $status = get_param('status');
            $hide = get_param('hide');
            foreach ($this->sections as $section) {
                if (!isset($pages[$section])) {
                    continue;
                }
                $i = 1;
                foreach ($pages[$section] as $id) {
                    if ($id) {
                        $statusValue = intval(isset($status[$id])&&$status[$id]);
                        $sql = "SELECT `menu_title`
                                  FROM `pages`
                                 WHERE `id` = " . to_sql($id);
                        $pageTitle = DB::result($sql);
                        if ($section == 'bottom' && $pageTitle == 'menu_bottom_contact_us') {
                            Config::update('options', 'contact', $statusValue ? 'Y' : 'N');
                        }
                        $data = array('position' => $i,
                                      'section' => $section,
                                      'status' => $statusValue,
                                      'hide_from_guests' => intval(isset($hide[$id])&&$hide[$id]));
                        DB::update('pages', $data, '`id` = ' . to_sql($id));
                        //$data = array('section' => $section);
                        unset($data['position']);
                        DB::update('pages', $data, '`parent` = ' . to_sql($id));
                        $i++;
                    }
                }
            }
            die();
        } elseif ($cmd == 'delete') {
            $page = get_param('page');
            DB::delete('pages', '`id` = ' . to_sql($page) . ' OR `parent` = ' . to_sql($page));
            redirect("{$p}?action=delete");
        }
    }

    function parseSection(&$html, $section = 'narrow') {

        $optionTmplName = Common::getOption('name', 'template_options');
        $html->setvar('template_name', $optionTmplName);
        $html->setvar('section', $section);
        $html->setvar('title_section', lCascade($section, array($section . '_' . $optionTmplName, $section)));

        $allowedPage = array('column_narrow_3dcity' => CustomPage::isParseCity(),
                             'column_narrow_friends' => Common::isOptionActive('friends_enabled'),
                             'column_narrow_rated_your_photos' => Common::isOptionActive('photo_rating_enabled'),
                             //'menu_bottom_contact_us' => Common::isOptionActive('contact'),
                             'menu_bottom_affiliates' => Common::isOptionActive('partner'));
        //if ($optionTmplName != 'impact') {
            //$allowedPage['menu_about_us'] = false;
        //}
        $lang = loadLanguageAdmin();
        DB::query("SELECT * FROM `pages` WHERE `section` = " . to_sql($section) . " AND `lang` = 'default' ORDER BY position");
        while ($row = DB::fetch_row()) {
            $allowTmpls = explode(',', $row['set']);
            if ($row['system'] && !in_array($optionTmplName, $allowTmpls)) {
                continue;
            }
            if (isset($allowedPage[$row['menu_title']]) && !$allowedPage[$row['menu_title']]) {
                continue;
            }
            $alias = $row['menu_title'];
            $html->setvar('id', $row['id']);
            $html->setvar('system', $row['system']);
            $html->setvar('alias', $alias);
            if ($row['system']) {
                $row['menu_title'] = l($row['menu_title'], $lang);
            }
            $html->setvar('menu_title', $row['menu_title']);
            $html->setvar('checked', $row['status'] ? 'checked' : '');
            if ($row['system']) {
                $html->parse('item_no_edit', false);
                $html->parse('item_no_hide', false);
                $html->clean('item_hide');
                $html->clean('item_edit');
            } else {
                $html->setvar('hide_checked', $row['hide_from_guests'] ? 'checked' : '');
                $html->parse('item_edit', false);
                $html->parse('item_hide', false);
                $html->clean('item_no_edit');
                $html->clean('item_no_hide');
            }
            $html->parse('item', true);
        }
        $html->parse('items', true);
        $html->parse('items_sortable_js', true);
        $html->clean('item');
    }

    function parseBlock(&$html) {

        foreach ($this->sections as $section) {
            $this->parseSection($html, $section);
        }

        parent::parseBlock($html);
    }

}

$page = new CCustomPage("", $g['tmpl']['dir_tmpl_administration'] . "pages_club.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuCustomPagesClub());

include("../_include/core/administration_close.php");