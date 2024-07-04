<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('./_include/core/main_start.php');

checkByAuth();

$cmd = get_param('cmd');
$display = get_param('display');
$uid = get_param('user_id');
$userTo = get_param('user_to');
$isAjaxRequest = get_param('ajax', 0);

if($display == 'one_chat') {
    if ($isAjaxRequest && $cmd == 'send_message' && $userTo == guid()){
        die(getResponseDataAjaxByAuth('redirect'));
    }
    if ($uid == guid()){
        redirect('messages.php');
    }else if (!$isAjaxRequest) {
        CIm::setMessageAsRead();
    }
}

CustomPage::setSelectedMenuItemByTitle('column_narrow_messages');

class CMessages extends CIm
{
	function action()
	{
		global $g;
		global $g_user;
        $cmd = get_param('cmd');
        $isAjaxRequest = get_param('ajax', 0);

        if ($isAjaxRequest && $cmd == 'clear_history_messages') {
            $responseData = CIm::clearHistoryMessages();
            die(getResponseDataAjaxByAuth($responseData));
        }
	}

	function parseBlock(&$html)
	{
        $uid = get_param('user_id');
        $display = get_param('display');
        $cmd = get_param('cmd');
        if ($cmd != 'send_message') {
            if ($html->varExists('you_have_no_messages_yet')) {
                $vars = array('url' => 'search_results.php');
                $html->setvar('you_have_no_messages_yet', Common::lSetLink('you_have_no_messages_yet', $vars, false));
            }
            self::parseImMobile($html);
        }

        if ($display == 'one_chat') {
            $html->setvar('url_profile', User::url($uid));
        }

		parent::parseBlock($html);
	}
}

$dirTmpl = $g['tmpl']['dir_tmpl_main'];
$tmpls = array();
if($isAjaxRequest) {
    if ($display == 'one_chat') {
        $tmpls['main'] = "{$dirTmpl}_messages_user_msg.html";
        if ($cmd != 'send_message') {
            $tmpls = array('main' => "{$dirTmpl}_messages_user.html",
                           'msg_list' => "{$dirTmpl}_messages_user_msg.html",
            );
        }
    } else {
        $tmpls = array('main' => "{$dirTmpl}_messages_list_users.html",
                       'msg_list' => "{$dirTmpl}_messages_list_msg.html",
        );
        if ($display == 'open_list_chats') {
            $tmpls = "{$dirTmpl}_pp_list_chats_open_item.html";
            if ($cmd == 'send_message') {
                $tmpls = "{$dirTmpl}_pp_list_chats_open_im_msg.html";
            }
        }
    }
} else {
    if ($display == 'one_chat') {
        $tmpls = array('main' => "{$dirTmpl}messages_one.html",
					   'users_list' => "{$dirTmpl}_messages_user.html",
					   'msg_list' => "{$dirTmpl}_messages_user_msg.html",
		);
    } else {
		$tmpls = array('main' => "{$dirTmpl}messages.html",
					   'users_list' => "{$dirTmpl}_messages_list_users.html",
                       'msg_list' => "{$dirTmpl}_messages_list_msg.html",
		);
	}
}

$page = new CMessages('', $tmpls);


if($isAjaxRequest) {
    if ($display == 'open_list_chats' && $cmd == 'send_message') {
        $responseData = getParsePageAjax($page);

        $_GET['cmd'] = 'update_im';
        $_POST['user_id'] = 0;
        $_POST['last_id'] = get_param('last_id_temp');
        $page = new CIm('', "{$dirTmpl}_pp_list_chats_open_item.html");
		$responseData .= '<div class="update_built_im">' . getParsePageAjax($page) . '</div>';
        die(getResponseDataAjaxByAuth($responseData));
    }

    die(getResponsePageAjaxAuth($page));
}

$header = new CHeader('header', "{$dirTmpl}_header.html");
$page->add($header);
$footer = new CFooter('footer', "{$dirTmpl}_footer.html");
$page->add($footer);

include('./_include/core/main_close.php');