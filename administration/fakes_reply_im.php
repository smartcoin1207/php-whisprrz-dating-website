<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class FakesReplyIm extends CHtmlList
{
    var $message = '';

	function action()
	{
		global $g;
		global $g_user;

		$cmd = get_param('cmd');
		$userTo = intval(get_param('user_to'));
		$userFrom = intval(get_param('user_from'));
		$text = trim(get_param('text'));

        if ($cmd == 'writing') {
            $writing = json_decode(get_param('writing'));
            foreach ($writing as $fromUser => $toUser) {
                $where = '`from_user` = ' . to_sql($fromUser, 'Number') .
                         ' AND `to_user` = ' . to_sql($toUser->user_to, 'Number');
                DB::update('im_open', array('last_writing' => $toUser->time), $where);
            }
            die();
        }elseif($cmd == 'reply') {
            // reply - check block list!!!
			// check open im
			if ($userTo && $userFrom && $text) {
                $id = $userTo;
                $old_g_user=$g_user;
                $g_user=User::getInfoBasic($userFrom);

				$sql = "SELECT id FROM user_block_list WHERE im = 1 AND user_from=" . $id . " AND user_to=" . $g_user['user_id'];
				$block = DB::result($sql);

				if ($block == 0) {

                    // Free messages for replier
                    Common::setOptionRuntime('Y', 'free_site');
                    Common::setOptionRuntime('Y', 'credits_enabled');

                    CIm::addMessage($block, $userTo, $text, false);

                    redirect();

				} else {
					$toName = DB::result('SELECT name FROM user WHERE user_id = ' . $id);
					$this->message = $g_user['name'] . " in Block List of " . $toName . "<br>";
				}
                $g_user=$old_g_user;

			}
		}

	}

	function init()
	{
		parent::init();
		global $g;
		global $g_user;
		$user_id = get_param('user_id');

		$this->m_on_page = 20;
		$this->m_sql_count = "select count(M.id) from im_msg as M
		join user as U on U.user_id = M.from_user
		join user as U2 on U2.user_id = M.to_user
		";

		$this->m_sql = "select M.*, U.name as user_from,
		U.use_as_online AS u_from_fake, U.register AS u_from_register,
		U2.name as user_to,
		U2.use_as_online AS u_to_fake, U2.register AS u_to_register
		FROM im_msg as M
		JOIN user as U on U.user_id = M.from_user
		JOIN user as U2 on U2.user_id = M.to_user
		";

		$this->m_sql_where = " (U.use_as_online = 1
		OR U2.use_as_online = 1) ";
		$this->m_sql_order = " id DESC ";
		$this->m_field['id'] = array("id", null);
		$this->m_field['user_from'] = array("user_from", null);
		$this->m_field['user_to'] = array("user_to", null);
		$this->m_field['from_user'] = array("from_user", null);
		$this->m_field['to_user'] = array("to_user", null);
		$this->m_field['msg'] = array("msg", null);
		$this->m_field['born'] = array("born", null);

	}
	function onItem(&$html, $row, $i, $last)
	{
		global $g;
		$user_id = get_param('user_id');
		$html->setvar("id", $row['id']);

		$html->setvar("from_user", $row['from_user']);
		$html->setvar("to_user", $row['to_user']);

        CIm::parseImOneMsg($html, $row);

        //$msg = Common::parseLinksTag(to_html($row['msg']), 'a', '&lt;', 'parseLinksSmile', '_blank', '', true);
        /*
        $msg = $row['msg'];
        if ($row['system'] && $row['system_type'] == 2) {
            $msg = CIm::grabsRequest($msg, $row['from_user'], $row['to_user'], true);
        } else {
            $msg = CIm::prepareMediaFromComment($msg, $row['from_user'], true);
        }

        if($row['msg_translation']!=''){
            $msg =$msg. ' ('. Common::parseLinksTag(to_html($row['msg_translation']), 'a', '&lt;', 'parseLinksSmile', '_blank', '', true).') ';
        }

        if(isset($row['audio_message_id']) && $row['audio_message_id']) {
			$msg = '<span class="im_audio_message">' .
						'<span class="im_audio_message_loader" data-audio-message-file="' . ImAudioMessage::getUrl($row['audio_message_id']) . '">' .
							'<i class="fa fa-play" aria-hidden="true"></i>' .
						'</span>' .
						'<span class="im_audio_message_process"></span>' .
						'<span class="im_audio_message_process_play"></span>' .
					'</span><div class="cl"></div>' . $msg;
            //$msg = '<span class="icon_fa im_audio_message" data-audio-message-file="' . ImAudioMessage::getUrl($row['audio_message_id']) . '"><i class="fa fa-play-circle" aria-hidden="true" style=""></i></span> ' . $msg;
        }

        $html->setvar('msg_im', $msg);
         */
		// parse reply for fakes

		if ($row['u_from_fake'] == 1) {
            if (!$row['is_new']) {
               $html->parse('is_read', false);
            }
			$html->setblockvar('reply', '');
		} else {
            $sql = 'UPDATE `im_msg`
                       SET `is_new` = 0
                     WHERE `is_new` > 0
                       AND `to_user` = ' . to_sql($row['to_user'], 'Number');
            DB::execute($sql);
            $html->setblockvar('is_read', '');
			$html->parse('reply', false);
		}

	}


	function parseBlock(&$html)
	{
		$html->setvar('error_message', $this->message);

		parent::parseBlock($html);
	}
}


$page = new FakesReplyIm("mail_list", $g['tmpl']['dir_tmpl_administration'] . "fakes_reply_im.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuFakes());

include("../_include/core/administration_close.php");