<?php

$area = "login";
include("./_include/core/pony_start.php");

if (Common::getOption('set', 'template_options') != 'urban') {
	redirect('view_friend_requests.php');
}
$cmd = get_param('cmd');
if ($cmd == 'send_request_private_access') {
    CIm::sendRequestPrivateAccess();
    $responseData = User::getNumberFriends();
    die(getResponseDataAjaxByAuth($responseData));
} elseif($cmd == 'reguest_friend') {
	$action = User::friendAction();
    $uid = get_param('uid');
	$responseData = array('action' => $action,
                          'photos' => array(),
                          'is_friends' => User::isFriend($uid, guid()),
                          'photos' => CProfilePhoto::getPhotoListMobile($uid));

    die(getResponseDataAjaxByAuth($responseData));
}

class CFriends extends CUsers
{
    function onItem(&$html, $row, $i, $last)
	{
        if ($html->varExists('from_page')) {
            $html->setvar('from_page', 'users_viewed_me');
        }

		parent::onItem($html, $row, $i, $last);
        $html->parse('users_list_item_url', false);
	}

	function parseBlock(&$html)
	{
        $isAjaxRequest = get_param('ajax', 0);

        if ($html->varExists('offset_real')) {
            $html->setvar('offset_real', max(1, intval($this->m_offset)));
        }

        if (Common::isOptionActive('free_site') && $html->blockExists('class_indent')) {
            $html->parse('class_indent');
        }

        if (!$isAjaxRequest) {
            $optionTmplName = Common::getOption('name', 'template_options');
            /*if ($html->blockExists('users_list_loader')) {
                $html->parse('users_list_loader', false);
            }
            if ($html->blockExists('users_list_scroll') && get_param('back_offset_profile_view')) {
                $html->parse('users_list_scroll', false);
            }*/
            if ($html->varExists('on_page')) {
                $html->setvar('on_page', $this->m_on_page);
                $html->parse('users_list_on_page', false);
            }
            if ($html->varExists('found_title')) {
                $html->setvar('found_title', l('found'));
            }
            if ($html->varExists('found_info')) {
                $keyTitle = 'found_info';
                $html->setvar('found_info', lCascade(l($keyTitle), array($keyTitle . '_' . $optionTmplName)));
            }
            if ($html->varExists('found_no_one')) {
                $html->setvar('found_no_one', l('found_no_one'));
            }
        }

		parent::parseBlock($html);
	}
}
$optionTmplSet = Common::getOption('set', 'template_options');
$isAjaxRequest = get_param('ajax', 0);
$tmpl = 'search_results.html';
if ($isAjaxRequest) {
    $tmpl = 'search_results_ajax.html';
}


class CPage extends CHtmlBlock
{
	function parseBlock(&$html)
	{
        if ($html->varExists('url_page_history')) {
            $html->setvar('url_page_history', Common::pageUrl('private_photo_access'));
        }
        if ($html->blockExists('block_target')) {
            $html->parse('block_target_main', false);
            $html->parse('block_target', false);
        }

		parent::parseBlock($html);
	}
}

$page = new CPage("", $g['tmpl']['dir_tmpl_mobile'] . $tmpl);

if (!$isAjaxRequest) {
    $header = new CHeader("header", $g['tmpl']['dir_tmpl_mobile'] . "_header.html");
    $page->add($header);
	if (Common::isParseModule('people_nearby_spotlight')) {
        $spotlight = new Spotlight('spotlight', $g['tmpl']['dir_tmpl_mobile'] . '_spotlight.html');
        $spotlight->update = false;
        $page->add($spotlight);
    }
    if (Common::isParseModule('user_menu')) {
        $user_menu = new CUserMenu("user_menu", $g['tmpl']['dir_tmpl_mobile'] . "_user_menu.html");
        $header->add($user_menu);
    }
    $tmplFooter = $g['tmpl']['dir_tmpl_mobile'] . "_footer.html";
    if (Common::isOptionActive('is_allow_empty_footer', 'template_options')) {
        $tmplFooter = $g['tmpl']['dir_tmpl_mobile'] . "_footer_empty.html";
    }
    $footer = new CFooter("footer", $tmplFooter);
    $page->add($footer);
}


$list = new CFriends('users_list', $g['tmpl']['dir_tmpl_mobile'] . '_list_users_info.html');
$list->m_sql_where = "u.user_id=F.fid";
$list->m_sql_order = "F.faccept ASC, F.fcreated_at DESC, F.factivity DESC";
$list->m_sql_select_add = ", F.*";
//$list->m_last_visit_only_online = true;


$onPage = getMobileOnPageSearch();
$list->m_on_page = get_param('on_page', $onPage);
$list->m_offset = get_param('offset', (int)get_cookie('back_offset_my_friends', 1));
$list->m_chk = $onPage;
$list->m_offset_real = true;

$list->row_breaks = true;
//$list->m_debug = "Y";
$guidSql = to_sql(guid());

$pendingSql = " UNION (SELECT * FROM `friends_requests` WHERE `friend_id` = {$guidSql} AND `accepted` = 0)";
$templateName = Common::getOption('name', 'template_options');
if ($templateName == 'impact_mobile') {
    $pendingSql = '';
}
$sql = "(SELECT IF(user_id = {$guidSql}, friend_id, user_id) AS fid, accepted AS faccept, created_at AS fcreated_at, activity AS factivity  FROM
(SELECT FR.* FROM
	(
    (SELECT * FROM `friends_requests` WHERE user_id = {$guidSql} AND `accepted` = 1)
    UNION
	(SELECT * FROM `friends_requests` WHERE friend_id = {$guidSql} AND `accepted` = 1)
    " . $pendingSql . "
	) FR
	) AS AFR
) AS F";

$list->m_sql_from_add = " JOIN {$sql}";


$page->add($list);

loadPageContentAjax($page);

include("./_include/core/main_close.php");