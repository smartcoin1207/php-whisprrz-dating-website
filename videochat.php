<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
$area = "login";
include("./_include/core/main_start.php");

//payment_check('videochat');
// checkAccessFeatureByPayment('videochat');

if (get_param('type')) {
    Chat::talk();
}

$cmd = get_param('cmd');
if($cmd == 'lang') {
    header('Content-Type: text/xml; charset=UTF-8');
    header('Cache-Control: no-cache, must-revalidate');

    echo '<lang>
	<speed>' . l('Speed') . '</speed>
	<high>' . l('High') . '</high>
	<medium>' . l('Medium') . '</medium>
	<low>' . l('Low') . '</low>
	<connect>' . l('Connect') . '</connect>
	<disconnect>' . l('Disconnect') . '</disconnect>
	<stop>' . l('Your IP is not in our database') . '</stop>
    </lang>';
    die();
}

class CVc extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g;
		global $g_user;

        $callUid = intval(get_param('id', 0));
        $clientId = $g_user['user_id'];
        $groupId = Groups::getParamId();

        $sql = "SELECT *
                  FROM `user`
                 WHERE `user_id` = " . to_sql($callUid);

        DB::query($sql);
        $isParseChat = true;
		if ($row = DB::fetch_row()){
            $sql = "DELETE FROM `video_reject`
                     WHERE `to_user` = " . to_sql($g_user['user_id'])
                   . " AND `from_user` = " . to_sql($row['user_id'])
                   . ' AND `group_id` = ' . to_sql($groupId);
            DB::execute($sql);

            $name = $row['name'];

            if ($groupId) {
                $groupInfo = Groups::getInfoBasic($groupId);
                if ($groupInfo) {
                    if ($callUid == $groupInfo['user_id']) {
                        $name = $groupInfo['title'];
                    }
                } else {
                    $groupId = 0;
                }
            }

            if ($html->varExists('page_title')) {
                $html->setvar('page_title', lSetVars('page_title', array('name' => $name)));
            }

			if (User::isOnline($callUid, $row)) {
				#foreach ($row as $k => $v) $html->setvar($k, $v);
				$html->setvar('enemy_name', $name);
				$html->setvar('my_name', $g_user['name']);
			} else {
                $isParseChat = false;
                $html->parse('alert_js');
            }
		} else {
            Common::toHomePage();
        }

        if (Common::isOptionActiveTemplate('only_webrtc_mediachat')) {
            $typeChat = 'webrtc';
        } else {
            $typeChat = Common::getOption('type_media_chat');
        }

    	$html->setvar('type_chat', $typeChat);

        if ($typeChat == 'webrtc') {
            if (IS_DEMO && get_param('demo')) {
                $html->setvar('demo_url', Common::urlSiteSubfolders());
                $html->setvar('demo_user_gender', mb_strtolower(User::getInfoBasic($callUid, 'gender'), 'UTF-8'));
                $html->setvar('demo', 1);
            }
            $clientId = Chat::getIdByChat($callUid, true, 'video');
            $callUid = Chat::getIdByChat($callUid, false, 'video');
        }
        $html->setvar('client_id', $clientId);
        $html->setvar('call_to_id', $callUid);
        if ($isParseChat && $typeChat == 'webrtc') {
            $html->setvar('is_mobile', Common::isMobile(false, true, true));
            $html->setvar('media_server', $g['webrtc_app']);

            $html->parse('video_chat_webrtc_js');
        }
        $html->parse("video_chat_{$typeChat}");

        TemplateEdge::parseColumn($html);

		parent::parseBlock($html);
	}
}

$tmpl = getPageCustomTemplate('videochat.html', 'videochat_template');

$page = new CVc("", $tmpl);
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

if (Common::isParseModule('profile_colum_narrow')){
    $column_narrow = new CProfileNarowBox('profile_column_narrow', $g['tmpl']['dir_tmpl_main'] . '_profile_column_narrow.html');
    $page->add($column_narrow);
}
if (Common::isParseModule('profile_head')){
    $profileHead = new ProfileHead('profile_head', $g['tmpl']['dir_tmpl_main'] . '_profile_head.html');
    $profileHead::setUserId(get_param('id', 0));
    $page->add($profileHead);
}

$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");