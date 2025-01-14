<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

//payment_check('audiochat');
// checkAccessFeatureByPayment('audiochat');

if (get_param('audio')) {
    Chat::talk();
}

$cmd = get_param('cmd');
if($cmd == 'lang') {
    header('Content-Type: text/xml; charset=UTF-8');
    header('Cache-Control: no-cache, must-revalidate');

    echo '<lang>
	<connect>' . l('Call Now!') . '</connect>
	<disconnect>' . l('End Call') . '</disconnect>
	<stop>' . l('Your IP is not in our database') . '</stop>
    </lang>';
    die();
}

class CAc extends CHtmlBlock
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
        $groupInfo = false;
		if ($row = DB::fetch_row()){
            $name = $row['name'];
            $age = User::getInfoBasic($row['user_id'], 'age');
            $userTitle = $name . ', ' . $age;

            if ($groupId) {
                $groupInfo = Groups::getInfoBasic($groupId);
                if ($groupInfo) {
                    if ($callUid == $groupInfo['user_id']) {
                        $name = $groupInfo['title'];
                        $age = '';
                        $userTitle = $name;
                    }
                } else {
                    $groupId = 0;
                }
            }
            $html->setvar('name', $name);
            $html->setvar('age', $age);
            $html->setvar('user_title', $userTitle);


            if ($html->varExists('page_title')) {
                $html->setvar('page_title', lSetVars('page_title', array('name' => $name)));
            }

            Chat::setType();
            $sql = "DELETE FROM `audio_reject`
                     WHERE `to_user` = " . to_sql($g_user['user_id'])
                   . " AND `from_user` = " . to_sql($row['user_id'])
                   . ' AND `group_id` = ' . to_sql($groupId);
            DB::execute($sql);

			if (User::isOnline($callUid, $row)) {
				#foreach ($row as $k => $v) $html->setvar($k, $v);
				$html->setvar('enemy_name', $row['name']);
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

            if ($groupInfo && $clientId == $groupInfo['user_id']) {
                $clientUserPhoto = GroupsPhoto::getPhotoDefault($clientId, $groupId, 'm');
            } else {
                $clientUserPhoto = User::getPhotoDefault($clientId, 'm');
            }
            $html->setvar('client_user_photo', $g['path']['url_files'] . $clientUserPhoto);

            if ($groupInfo && $callUid == $groupInfo['user_id']) {
                $userPhotoUrl = GroupsPhoto::getPhotoDefault($callUid, $groupId, 'b');
                $userPhotoId = GroupsPhoto::getPhotoDefault($callUid, $groupId, 'b', true);
                $userGender = '';
                $isPlugPrivate = GroupsPhoto::isVisiblePlugPrivatePhotoFromId($callUid, $userPhotoId, $groupId);
            } else {
                $userPhotoUrl = User::getPhotoDefault($callUid, 'b');
                $userPhotoId = User::getPhotoDefault($callUid, 'b', true);
                $userGender = User::getInfoBasic($callUid, 'gender');
                $isPlugPrivate = User::isVisiblePlugPrivatePhotoFromId($callUid, $userPhotoId);
            }

            if ($isPlugPrivate || !$userPhotoId) {
                $html->parse('plug_photos', false);
                $userPhotoUrl = User::photoFileCheck(array('user_id' => 0, 'photo_id' => 0, 'gif' => false), 'b', $userGender, true, false, 'audiochat');
            }

            $html->setvar('call_user_url', User::url($callUid));
            $html->setvar('call_user_photo', $g['path']['url_files'] . $userPhotoUrl);


            $html->setvar('client_id', Chat::getIdByChat($callUid, true, 'audio'));
            $html->setvar('call_to_id', Chat::getIdByChat($callUid, false, 'audio'));
        }
        if ($isParseChat && $typeChat == 'webrtc' && $callUid != $clientId) {
            $html->setvar('media_server', $g['webrtc_app']);
            $html->parse('audio_chat_webrtc_js');
        }
        $html->parse("audio_chat_{$typeChat}");

        TemplateEdge::parseColumn($html);

		parent::parseBlock($html);
	}
}


$tmpl = getPageCustomTemplate('audiochat.html', 'audiochat_template');

$page = new CAc("", $tmpl);
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

if (Common::isParseModule('profile_colum_narrow')){
    $columnNarrow = new CProfileNarowBox('profile_column_narrow', $g['tmpl']['dir_tmpl_main'] . '_profile_column_narrow.html');
    $page->add($columnNarrow);
}
if (Common::isParseModule('profile_head')){
    $profileHead = new ProfileHead('profile_head', $g['tmpl']['dir_tmpl_main'] . '_profile_head.html');
    $profileHead::setUserId(get_param('id', 0));
    $page->add($profileHead);
}
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");