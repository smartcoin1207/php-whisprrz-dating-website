<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class AdminReportsContent extends CHtmlList
{

    public function action() {

		global $p;
        global $g_user;

        $cmd = get_param('cmd');

        if ($cmd) {
            $del = get_param('delete');
            $rid = get_param('report_id');
            $pid = get_param('photo_id');
            $id = get_param('id');
            $commentId = get_param_int('comment_id');
            $uid = get_param('user_id');
            $groupId = get_param('group_id');

            if ($cmd == 'delete_report' && $rid){
                deleteReport($rid);
                redirect("{$p}?action=delete");
            }elseif ($cmd == 'delete_photo' && $pid && $uid){
                CProfilePhoto::deletePhoto($pid, false, $uid, true);
                $reports = DB::field('users_reports', 'id', '`photo_id` = ' . to_sql($pid));
                if ($reports) {
                    deleteReport(implode(',', $reports));
                }
                redirect("{$p}?action=delete");
            }elseif (($cmd == 'delete_video' || $cmd == 'delete_stream') && $pid && $uid){
                require_once("../_include/current/vids/includes.php");

				if ($cmd == 'delete_stream') {
					$video = CVidsTools::getVideoById($pid);
					if ($video && $video['live_id']) {
						LiveStreaming::deleteLive($video['live_id']);
					}
				}

                CVidsTools::delVideoById($pid, true);
                $reports = DB::field('users_reports', 'id', 'video = 1 AND `photo_id` = ' . to_sql($pid));
                if ($reports) {
                    deleteReport(implode(',', $reports));
                }
                redirect("{$p}?action=delete");
            }elseif ($cmd == 'ban' && $groupId) {
                Groups::ban($groupId);
                redirect();
            }elseif ($cmd == 'ban' && $uid) {
                $sql = 'UPDATE `user`
                           SET `ban_global` = 1 - `ban_global`
                         WHERE `user_id` = '. to_sql($uid, 'Number');
                DB::execute($sql);
                redirect();
            } elseif ($cmd == 'delete_comment' && $commentId){
				$commentType = get_param('comment_type');
				if ($commentType == 'video') {
					$table = 'vids_comment';
				} else {
					$table = "{$commentType}_comments";
				}
				$where = '`id` = ' . to_sql($commentId) . ' OR `parent_id` = ' . to_sql($commentId);
				$comments = DB::select($table, $where);
				if ($comments) {
					$commentsId = array();
					foreach ($comments as $key => $item) {
						$commentsId[] = $item['id'];
					}

					$where = '`comment_type` = ' . to_sql($commentType) . ' AND `comment_id` IN(' . implode(',', $commentsId) . ')';
					DB::delete('users_reports', $where);
				}

				if ($commentType == 'wall') {
					Wall::removeComment($commentId, false);
				} elseif ($commentType == 'photo') {
					$photo = DB::one('photo_comments', '`id` = ' . to_sql($commentId));
					if ($photo) {
						$g_user['user_id'] = $photo['photo_user_id'];
						CProfilePhoto::deleteComment($commentId, $photo['photo_id']);
					}
                } elseif ($commentType == 'video') {
                    include_once('../_include/current/vids/tools.php');
					$comment = CVidsTools::getCommentById($commentId);
					if ($comment) {
						$g_user['user_id'] = $comment['user_id'];
						CVidsTools::deleteCommentVideoByAjax($commentId);
					}
                }

                redirect("{$p}?action=delete");
            } elseif ($cmd == 'delete_post' && $id){
				Wall::removeById($id);
                $reports = DB::field('users_reports', 'id', '`wall_id` = ' . to_sql($id));
                if ($reports) {
                    deleteReport(implode(',', $reports));
                }
                redirect("{$p}?action=delete");
            }
        }
	}

	function onItem(&$html, $row, $i, $last)
	{
		global $g;

        $banKeys = array('ban_to', 'group_ban_to');
        foreach($banKeys as $banKey) {
            if(isset($row[$banKey])) {
                if($row[$banKey]){
                    $this->m_field[$banKey][1] = ($banKey == 'ban_to' ? l('unban_user') : l('unban'));
                } else {
                    $this->m_field[$banKey][1] = ($banKey == 'ban_to' ? l('ban_user') : l('ban'));
                }
            }
        }

        $this->m_field['msg'][1] = nl2br($row['msg']);
        $dateParts = explode(' ', $row['date']);
        $this->m_field['date'][1] = "<span class='nw'>{$dateParts[0]}</span> {$dateParts[1]}";

		$html->clean('processing');

		$lDeleteContent = l('delete_content');
        $type = '';
        $cmd = '';

        $commentId = intval($row['comment_id']);
        $html->setvar('comm_id', $commentId);
        $html->setvar('comm_type', $row['comment_type']);
        $html->setvar('rep_id', $row['id']);

        // Special parser for wall(group_id = 0 && wall_id > 0) and group wall(group_id > 0 && wall_id > 0)
        if($row['wall_id']) {
            Wall::setAdmin(true);

            $wallItem = DB::row('SELECT * FROM `wall` WHERE id = ' . to_sql($row['wall_id']), DB_MAX_INDEX);
            if ($wallItem) {
                $groupId = $wallItem['group_id'];
                if($groupId) {
                    $groupInfo = Groups::getInfoBasic($groupId);
                }
                $html->clean('wall_items');
                $html->clean('wall_no_content');

                $commentId = intval($row['comment_id']);
                $html->setvar('comm_id', $commentId);
                $html->setvar('comm_type', $row['comment_type']);
                $html->setvar('rep_id', $row['id']);
                if ($commentId) {
                    $html->setvar('type', l('comment'));
                    $html->parse('report_comment', false);
                    $html->parse('report_comment_delete', false);
                    $html->parse('wall_item_post_show', false);

                    Wall::parseComments($html, $row['wall_id'], 2, 0, 1, $row['comment_id']);
                    $html->parse('wall_item_comments', false);
                } else {
                    $html->setvar('type', l('post'));
                    $html->clean('report_comment');
                    $html->clean('report_comment_delete');
                    $html->clean('wall_item_post_show');
                    $html->clean('wall_item_comments', false);
                }

                if($groupId && $groupInfo) {
                    $html->setvar('group_id', $groupId);
                    $title = $groupInfo['page'] ? l('page') : l('group');
                    $vars = array();
                    $vars['link_start'] = Common::getLinkHtml(Groups::url($groupId, $groupInfo), true);
                    $vars['link_end'] = '</a>';
                    $vars['title'] = $groupInfo['title'];
                    $html->setvar('group_title_wall', Common::replaceByVars($title, $vars));
                }

                Common::setOptionRuntime('', 'wall_play_video', Common::getTmplName() . '_wall_settings');
                $parse = Wall::parseItems($html, $row['wall_id'], $row['wall_id'], false, false, $groupId, true);
                if ($parse) {
                    if (Common::getTmplName() == 'urban') {
                        $html->parse('wall_item_title_urban', false);
                    }
                } else {
                    $html->parse('wall_no_content', false);
                }
            }
        } elseif($row['video']) {
			$type = l('video');
			$cmd = 'delete_video';
            $video = DB::row('SELECT * FROM vids_video WHERE id = ' . to_sql($row['photo_id']), DB_MAX_INDEX);
            if($video) {
                $html->setvar('video_id', $row['photo_id']);
				$videoLink = 'vids_video_edit.php?id=' . $row['photo_id'];
				if ($video['live_id']) {
					$type = l('past_stream');

					$liveInfo = LiveStreaming::getInfoLive($video['live_id']);

					if ($liveInfo) {
						if ($video['active'] == 2 && $liveInfo['status']) {
							$type = l('live_now');
							$videoLink = $g['path']['url_main'] . Common::pageUrl('live_id', $video['user_id'], $video['live_id']);
							$lDeleteContent = l('delete_stream');
							$cmd = 'delete_stream';
						} elseif(!$liveInfo['is_upload_video']) {
							$html->parse('processing', false);
						}
					}
				}
				$html->setvar('video_link', $videoLink);
				$html->setvar('video_url', User::getVideoFile($video, 'b', ''));
                $html->setvar('content_user_id', $video['user_id']);
                $html->parse('video_url', false);
                $html->clean('photo_url');
                $html->clean('comment');

                if($row['comment_type'] == 'video') {

                    $type = l('comment');

                    $comment = DB::row("SELECT * FROM `vids_comment` WHERE `id` = " . to_sql($row['comment_id'], 'Number'));

                    if($comment) {
                        $user = User::getInfoBasic($comment['user_id'], false, 2);

                        $commentInfo['id'] = $comment['id'];
                        $commentInfo['user_id'] = $comment['user_id'];
                        $commentInfo['photo_user_id'] = $video['user_id'];
                        $commentInfo['send'] = $comment['send'];

                        $commentInfo['comment'] = $comment['text'];
                        $commentInfo['date'] = $comment['dt'];
                        $commentInfo['display_profile'] = User::displayProfile();
                        $commentInfo['user_name'] = $user['name'];
                        $commentInfo['user_photo'] = User::getPhotoDefault($comment['user_id'], "r", false, $user['gender']);
                        $commentInfo['user_photo_id'] = User::getPhotoDefault($comment['user_id'], "r", true);
                        $commentInfo['audio_message_id'] = $comment['audio_message_id'];

                        $commentInfo['content_item_id'] = isset($comment['photo_id']) ? $comment['photo_id'] : $comment['video_id'];
                        $commentInfo['users_reports_comment'] = isset($comment['users_reports_comment']) ? $comment['users_reports_comment'] : '';

                        CProfilePhoto::parseComment($html, $commentInfo, 'comment', 'video');
                    }

                    $html->parse('report_comment', false);
                    $html->parse('report_comment_delete', false);
                }

            }

        } else {
			$type = l('photo');
            $photo = DB::select('photo', 'photo_id = ' . to_sql($row['photo_id'], 'Number'));
            $photoUrl = l('no_photo');
            if (isset($photo[0])) {
                $photoUrl = User::photoFileCheck($photo[0], 'r', isset($row['gender_to']) ? $row['gender_to'] : '');
                $html->setvar('photo_url', $photoUrl);
                $html->clean('no_photo_url');
                $html->parse('photo_url', false);
                $html->setvar('content_user_id', $photo[0]['user_id']);
            } else {
                $html->clean('photo_url');
                $html->parse('no_photo_url', false);
            }

            $html->clean('video_url');
            $cmd = 'delete_photo';

            $html->setvar('comm_id', intval($row['comment_id']));
            $html->setvar('comm_type', $row['comment_type']);

            $html->clean('comment');

            if($row['comment_type'] == 'photo') {
                $type = l('comment');
                $comment = DB::select('photo_comments', 'id = ' . to_sql($row['comment_id'], 'Number'));
                if($comment[0]) {
                    $commentInfo = $comment[0];
                    $commentInfo['item_group_id'] = $commentInfo['group_id'];
                    $commentInfo['photo_user_id'] = $commentInfo['user_id'];
                    $commentInfo = CProfilePhoto::prepareDataComment($commentInfo, 'photo', false);
                    CProfilePhoto::parseComment($html, $commentInfo);
                }

                $html->parse('report_comment', false);
				$html->parse('report_comment_delete', false);
            }

        }

        if($row['comment_id'] == 0) {
            $html->clean('report_comment_delete');
        }

		$html->setvar('delete_content', $lDeleteContent);
		$html->setvar('type', $type);


        $html->setvar('cmd', $cmd);

		parent::onItem($html, $row, $i, $last);
	}

    function onPostParse(&$html)
	{
        if ($this->m_total != 0) {
            $html->parse('no_delete');
        }
	}

}
