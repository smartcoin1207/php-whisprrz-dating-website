<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

$area = 'login';
include("./_include/core/main_start.php");

$isOptionTmplSet = Common::getOption('set', 'template_options');

if ($isOptionTmplSet != 'urban') {
   include("./_include/current/menu_section.class.php");
}

if (!Common::isOptionActive('wink')) {
    redirect(Common::toHomePage());
}

class CHtmlUsersListAction extends CHtmlUsersListInt {

    private $maxId = 0;
    private $maxDate = '';

    function action() {
        global $g_user;

        $cmd = get_param("cmd", "");
        if ($cmd == "delete") {
            $id = get_param_array("id");
            foreach ($id as $k => $v) {
                DB::execute("
					DELETE FROM users_interest
					WHERE user_to=" . $g_user['user_id'] . " AND user_from=" . to_sql($v, "Number") . "
					ORDER BY id LIMIT 1
				");
            }
        }

        if (Common::getOption('set', 'template_options') != 'urban') {
            $fans = DB::count('users_interest', '`user_to` = ' . to_sql(guid(), 'Number'));
            if (!$fans) {
                redirect('mail.php');
            }
        } else {
            //Fix for old template
            /*$lastWink = DB::select('users_interest', '`user_to` = ' . to_sql(guid(), 'Number'), '`id` DESC', 1);
            if ($lastWink && isset($lastWink[0])) {
                $this->maxId = $lastWink[0]['id'];
                if ($lastWink[0]['date'] != '0000-00-00 00:00:00') {
                    $this->maxDate = $lastWink[0]['date'];
                }
            }*/
        }
    }

    function onItem(&$html, $row, $i, $last) {
        global $g_user;
        if ($html->varExists('wink_id')) {
            $html->setvar('wink_id', $row['wink_id']);
        }
        if ($html->varExists('wink_time_ago')) {
            //Fix for old template
            /*if ($this->maxId && $row['wink_date'] == '0000-00-00 00:00:00') {
                $d = new DateTime($this->maxDate);
                $d->modify('-' . ($this->maxId - $row['wink_id']) . ' day');
                $row['wink_date'] = $d->format('Y-m-d H:i:s');
                DB::update('users_interest', array('date' => $row['wink_date']), '`id` = ' . to_sql($row['wink_id']));
            }*/
            $html->setvar('wink_time_ago', timeAgo($row['wink_date'], 'now', 'string', 60, 'second'));
        }
        $fieldStatus = 'wink_status';
        if ($row[$fieldStatus] == 'Y') {
            DB::update('users_interest', array('new' => 'N'), '`id` = ' . to_sql($row['wink_id']));
            if ($g_user['new_interests']) {
                $countNewWink = $g_user['new_interests'] - 1;
                User::update(array('new_interests' => $countNewWink));
                $g_user['new_interests'] = $countNewWink;
            }
        }
        if ($html->blockexists($fieldStatus)) {
            $html->subcond($row[$fieldStatus] == 'Y', $fieldStatus);
        }
        if ($html->blockexists('wink_old')) {
            $html->subcond($row[$fieldStatus] == 'N', 'wink_old');
        }
        parent::onItem($html, $row, $i, $last);
    }

    function parseBlock(&$html) {
        if ($html->varExists('wink_is_new')) {
            $html->setvar('wink_is_new', DB::count('users_interest', "`new` = 'Y' AND `user_to` = " . to_sql(guid())));
        }
        if ($html->varExists('wink_count')) {
            $html->setvar('wink_count', DB::count('users_interest', "`user_to` = " . to_sql(guid())));
        }
        $this->m_folder_name = l('menu_mail_whos_interest');
        parent::parseBlock($html);
    }
}

class CMailWhosInterest extends CHtmlBlock {

    function parseBlock(&$html) {
        $guid = guid();
        if (Common::getOption('set', 'template_options') != 'urban') {
            $type = get_param("display", "list");
            if ($type == "photo") {
                $html->parse('photo_on');
            } else {
                $html->parse('photo_off');
            }
            $uid = get_param('uid');
            if ($type == 'profile' && $uid != '') {
                $sql = 'UPDATE `users_interest`
                           SET `new` = "N"
                         WHERE `user_to` = ' . to_sql($guid, 'Number')
                       . ' AND `user_from` = ' . to_sql($uid, 'Number');
                DB::execute($sql);
            }
        } else {
            if (!DB::count('users_interest', '`user_to` = ' . to_sql($guid, 'Number'))) {
                $html->parse('wink_noitems');
            }
            $html->setvar('on_page', Common::getOption('user_custom_per_page', 'template_options'));
        }
        parent::parseBlock($html);
    }
}

$isAjaxRequest = get_param('ajax');
$tmpl = $g['tmpl']['dir_tmpl_main'] . 'mail_whos_interest.html';
if ($isAjaxRequest) {
    $tmpl = $g['tmpl']['dir_tmpl_main'] . 'search_results_ajax.html';
}

$page = new CMailWhosInterest("", $tmpl);

if (!$isAjaxRequest) {
    $header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
    $page->add($header);
    $footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
    $page->add($footer);
}

$type = get_param("display", "list");
if ($type == "list" || $isOptionTmplSet == 'urban') {
    $list = new CHtmlUsersListAction('users_list', $g['tmpl']['dir_tmpl_main'] . "_mail_interest.html");
} elseif ($type == "profile" || $type == "profile_info")
    $list = new CUsersProfile("users_list", $g['tmpl']['dir_tmpl_main'] . "_profile.html");
elseif ($type == "photo")
    $list = new CHtmlUsersPhoto("users_list", $g['tmpl']['dir_tmpl_main'] . "_photo.html");
else {
    redirect("mail.php");
}

$list->m_view = 0;
$list->m_sql_where = "1";
$onPage = Common::getOption('user_custom_per_page', 'template_options');

if($type == "profile" || $type == "profile_info") {
    $onPage = 1;
}

$list->m_on_page = $onPage ? $onPage : 20;
if($isAjaxRequest) {
    $list->m_on_page = get_param('on_page');
    $list->m_sql_where .= ' AND i.id < ' . to_sql(get_param('id'));
}
$list->m_sql_order = "i.id DESC ";
$list->m_sql_from_add = " JOIN users_interest AS i ON (u.user_id=i.user_from AND i.user_to=" . $g_user['user_id'] . ")";
$list->fieldsFromAdd = ', i.new AS wink_status, i.id AS wink_id, i.date AS wink_date';
$page->add($list);

if ($isOptionTmplSet == 'urban') {
    $column_narrow = new CProfileNarowBox('profile_column_narrow', $g['tmpl']['dir_tmpl_main'] . '_profile_column_narrow.html');
    $page->add($column_narrow);
} else {
    if(Common::isOptionActive('mail')) {
        $folders = new CFolders("folders", $g['tmpl']['dir_tmpl_main'] . "_folders.html");
        $page->add($folders);
    } else {
        $search = new CSearch("search", $g['tmpl']['dir_tmpl_main'] . "_search.html");
        $page->add($search);
    }
    $mailMenu = new CMenuSection('mail_menu', $g['tmpl']['dir_tmpl_main'] . "_mail_menu.html");
    $mailMenu->setActive('whos_interest');
    $page->add($mailMenu);
}

include("./_include/core/main_close.php");