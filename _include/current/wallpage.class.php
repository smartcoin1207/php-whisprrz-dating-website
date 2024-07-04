<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class Wall_Page {

    static function show()
    {
        global $g;
        global $g_user;

        $guid = guid();

        $distance = get_param('radius', '');
        if($distance == 'all') {
            $distance = '';
        }
        $sql = "UPDATE userinfo SET wall_distance_filter = " . to_sql($distance) . " WHERE user_id=" . to_sql($guid);
        DB::execute($sql);

        if (!Wall::isActive()) {
            redirect(Common::getHomePage());
        }
        if (Wall::getUid() == 0 && get_param('uid', guid()) == 0) {
            redirect(Common::getLoginPage());
        }

        //$tmplList = array('main' => $g['tmpl']['dir_tmpl_main'] . 'wall.html');
        $tmplList = getPageCustomTemplate(array('main' => 'wall.html'), 'wall_template');
        if (Common::isParseModule('wall_custom_content')) {
            $tmplList['gallery_init'] = $g['tmpl']['dir_tmpl_main'] . '_profile_photos_init.html';
            $tmplList['wall_content'] = $g['tmpl']['dir_tmpl_main'] . '_wall_content.html';
        }

        $page = new CWallPage("", $tmplList);
        if (Common::isParseModule('profile_colum_narrow')) {
            $column_narrow = new CProfileNarowBox('profile_column_narrow', $g['tmpl']['dir_tmpl_main'] . '_profile_column_narrow.html');
            $page->add($column_narrow);
        }

        $tmpl = getPageCustomTemplate('_wall_items.html', 'wall_items');
        $wallItems = new CWallItems("wall_items", $tmpl);


        $wall_custom_head = false;
        if (Common::isParseModule('wall_custom_head')) {
            $wall_custom_head = new CHtmlBlock("custom_head", $g['tmpl']['dir_tmpl_main'] . "_wall_custom_head.html");
        }

        Page::addParts($page, $wallItems, $wall_custom_head);

        return $page;
    }

}