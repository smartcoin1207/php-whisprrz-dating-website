<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/friends.php");
if(!Common::isOptionActive('contact_blocking')) {
    Common::toHomePage();
}

$isAjaxRequest = get_param('ajax');
$optionTmplName = Common::getTmplName();
$optionTmplName = "oryx";
if ($optionTmplName == 'edge') {

    if (!guid() && !$isAjaxRequest) {
        Common::toLoginPage();
    }

    class CPage extends CHtmlBlock {
        function parseBlock(&$html) {
            $ajax = get_param('ajax');
            if (!$ajax) {
                TemplateEdge::parseColumn($html);
            }

            $html->setvar('url_pages', Common::pageUrl('user_block_list'));

            parent::parseBlock($html);
        }
    }

    class CUserListBlocked extends CUsers {
        function init()	{
            parent::init();
            global $g;
            global $g_user;

            $this->m_sql_count = "SELECT COUNT(u.user_id) FROM user AS u " . $this->m_sql_from_add;
            $this->m_sql = "
                SELECT u.user_id, u.gender, u.orientation, u.rating, u.name, u.gold_days, u.type, (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
                ) AS age, u.last_visit, u.is_photo,	u.city, u.state, u.country, u.relation,
                IF(u.city_id=" . $g_user['city_id'] . ", 1, 0) +
                IF(u.state_id=" . $g_user['state_id'] . ", 1, 0) +
                IF(u.country_id=" . $g_user['country_id'] . ", 1, 0) AS near
                FROM user AS u " . $this->m_sql_from_add ;

            $this->m_field['user_id'] = array("user_id", null);
            $this->m_field['photo_id'] = array("photo", null);
            $this->m_field['name'] = array("name", null);
            $this->m_field['age'] = array("age", null);
            $this->m_field['last_visit'] = array("last_visit", null);
            $this->m_field_default = $this->m_field;

            $this->m_params = set_param("display", "list");
            $this->m_params = del_param("cmd", $this->m_params);
            $this->m_params = del_param("id", $this->m_params);
            $this->m_params = del_param("display", $this->m_params);
            $this->m_params = del_param("offset", $this->m_params);
            $this->m_params_pages = set_param("display", "list", $this->m_params);

            $this->m_on_page = Common::getOptionUsersInfoPerPage();
        }
    }


    $dirTmpl = $g['tmpl']['dir_tmpl_main'];
    $tmplList = getPageCustomTemplate(null, 'search_results_list');
    if ($isAjaxRequest) {
        $tmplList['main'] = $dirTmpl . 'search_results_ajax.html';
        unset($tmplList['users_filter']);
    }

    $page = new CPage("", $dirTmpl . 'search_results.html');

    $list = new CUserListBlocked('users_list', $tmplList);

    $list->m_sql_from_add = "JOIN user_block_list AS B ON (u.user_id=B.user_to AND B.user_from=" . guid() . ")";
    $list->m_sql_order = ' B.id DESC';

    $page->add($list);
} else {

    CustomPage::setSelectedMenuItemByTitle('column_narrow_blocked');

    class CBlock extends CHtmlList
    {
        static $first = true;

        function action()
        {
            $cmd = get_param('cmd');
            $isAjaxRequest = get_param('ajax');

            if($isAjaxRequest) {
                $userTo = get_param('user_to', 0);
                $uid = guid();
                if ($uid && $cmd == 'user_unblock' && $userTo) {
                    User::blockRemoveAll($uid, $userTo);
                }
            }
        }

        function init()
        {
            parent::init();
            global $g;

            $isAjaxRequest = get_param('ajax');

            $mOnPage = Common::getOption('user_custom_per_page', 'template_options');
            $this->m_on_page = ($mOnPage) ? $mOnPage : 10;
            if($isAjaxRequest) {
                $this->m_on_page = get_param('on_page');
            }
            //$this->m_debug = 'Y';

            $this->m_sql_count = 'SELECT COUNT(*)
                FROM user_block_list as B ' . $this->m_sql_from_add;
            $this->m_sql = 'SELECT B.*, U.*, U.mail AS umail, B.mail, B.id as id, B.id as last_id
                , DATE_FORMAT(NOW(), "%Y") - DATE_FORMAT(birth, "%Y") - (DATE_FORMAT(NOW(), "00-%m-%d") < DATE_FORMAT(birth, "00-%m-%d")) AS age
                FROM user_block_list AS B ' . $this->m_sql_from_add;

            // show only block by active options
            $blockOptions = User::getBlockOptionsActiveSections();
            $blockCheck = array();

            if(count($blockOptions)) {
                foreach($blockOptions as $blockOption) {
                    $blockCheck[] = to_sql('B.' . $blockOption, 'Plain') . ' = 1';
                }
            }

            $blockCheckWhere = ' AND B.user_from = 0 ';
            if(count($blockCheck)) {
                $blockCheckWhere = ' AND (' . implode(' OR ', $blockCheck) . ')';
            }

            $whereCustom = '';
            if($isAjaxRequest) {
                $id = get_param('id');
                if ($id) {
                    $whereCustom = ' AND B.id < ' . to_sql($id, 'Number');
                }
            }

            $this->m_sql_where = ' B.user_from = ' . to_sql(guid(), 'Number') . $blockCheckWhere . $whereCustom;
            $this->m_sql_order = ' B.id DESC';
            //$this->m_debug = "Y";
        }

        function onItem(&$html, $row, $i, $last)
        {
            global $g;
            global $l;

            if (self::$first && !get_param('ajax', 0)) {
                $html->parse('border_none', false);
                $html->clean('border_top');
                self::$first = false;
            } else {
                $html->parse('border_top', false);
                $html->clean('border_none');
            }

            $html->setvar('row', ($i+1)%2+1);

            $html->setvar('last_id', $row['last_id']);

            $html->setvar('user_id', $row['user_id']);
            $html->setvar('user_name', $row['name']);
            $html->setvar('user_age', $row['age']);
            $html->setvar('user_city', $row['city']);
            $html->setvar('user_photo', User::getPhotoDefault($row['user_to'], 'r')	);

            User::parseItemBasicList($html, $row);
            if ($html->blockExists('users_list_item_block')) {
                $html->parse('users_list_item_block', false);
            }

            $blocked = l('blocked');
            $available = l('available');

            $blockOptions = User::getBlockOptionsActive();
            $parsed = false;

            foreach($blockOptions as $blockOption => $title) {
                if($blockOption == 'wall' && Common::isOptionActive('block_list_hide_wall', 'template_options')) {
                    continue;
                }

                if($row[$blockOption] == 1) {
                    $blockStatus = $blocked;
                    $status = 'blocked';
                } else {
                    $blockStatus = $available;
                    $status = 'available';
                }

                $html->setvar('option_block', $blockStatus);
                $html->setvar('option_status', $status);

                if($parsed) {
                    $html->parse('delimiter', false);
                }

                $html->setvar('option', $blockOption);

                $html->setvar('option_title', l($title));

                $html->parse('option');
                $parsed = true;
            }
            $html->parse('options', false);
            $html->setblockvar('option', '');
            $html->setblockvar('delimiter', '');
        }

        function parseBlock(&$html)
        {
            parent::parseBlock($html);

            if ($this->m_total == 0 && $html->blockexists('no_one_here_yet')) {
                $html->parse('no_one_here_yet');
            }

            if ($html->varExists('page_class')) {
                $html->setvar('page_class', 'blocked_users');
            }
        }
    }

    $listTmpl = $g['tmpl']['dir_tmpl_main'] . 'user_block_list.html';

    $nameBlock = 'block_list';
    $fromAddCustom = '';
    if (Common::isOptionActive('list_users_block_list_tmpl_parts', 'template_options')) {
        $listTmpl = array(
            'main' => $g['tmpl']['dir_tmpl_main'] . 'user_block_list.html',
            'items' => $g['tmpl']['dir_tmpl_main'] . '_list_users_block_list_items.html',
        );

        if($isAjaxRequest) {
            $listTmpl['main'] = $g['tmpl']['dir_tmpl_main'] . 'search_results_ajax.html';
        }
    } elseif (Common::isOptionActive('list_users_info_tmpl_base_parts', 'template_options')) {
        $listTmpl = array(
            'main' => $g['tmpl']['dir_tmpl_main'] . '_list_users_base.html',
            'items' => $g['tmpl']['dir_tmpl_main'] . '_list_users_base_items.html',
            'pages' => $g['tmpl']['dir_tmpl_main'] . '_list_users_base_pages.html',
        );
        if($isAjaxRequest) {
            $listTmpl['main'] = $g['tmpl']['dir_tmpl_main'] . 'search_results_ajax.html';
        }
        $nameBlock = 'users_list';
    }

    $page = new CHtmlBlock("", $listTmpl);

    $block_list = new CBlock($nameBlock, null);
    $block_list->m_sql_from_add = 'JOIN user AS U ON B.user_to = U.user_id';
    $page->add($block_list);

    if($isAjaxRequest) {
        die(getResponsePageAjaxByAuth(guid(), $page));
    }
}

if (!$isAjaxRequest) {
    $header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
    $page->add($header);

    $footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
    $page->add($footer);

    if (Common::isParseModule('friends_menu')){
        $friends_menu = new CFriendsMenu("friends_menu", $g['tmpl']['dir_tmpl_main'] . "_friends_menu.html");
        $friends_menu->active_button = "blocked";
        $page->add($friends_menu);
    }

    if (Common::isParseModule('profile_colum_narrow')){
        $column_narrow = new CProfileNarowBox('profile_column_narrow', $g['tmpl']['dir_tmpl_main'] . '_profile_column_narrow.html');
        $page->add($column_narrow);
    }
}

include("./_include/core/main_close.php");