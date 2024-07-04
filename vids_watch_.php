<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('./_include/core/main_start.php');
if(Common::isOptionActive('video_autoplay'))
    VideoHosts::setAutoplay(true);

include('./_include/current/vids/start.php');

CStatsTools::count('videos_viewed');

class CPage extends CHtmlBlock
{
    public $video = null;
    public $user = null;
    function init()
    {
        if (intval(param('id')) > 0) {
            $this->video = CVidsTools::getVideoById(ipar('id'), true);
            if (!is_array($this->video)) {
                redirect('vids.php');
            } else {
                $this->user = user($this->video['user_id']);
                CVidsTools::viewVideoByIdAndUserId($this->video['id'], $this->video['user_id']);

                /* For compatibility with new templates */
                CProfilePhoto::markReadCommentsAndLikes($this->video['id'], $this->video['user_id'], 'video');
                /*For compatibility with new templates */
            }
        } else {
            redirect('vids.php');
        }

        global $g;
        $g['main']['title'] = $this->video['subject'];
        $g['main']['description'] = $this->video['text'];


    }
	function parseBlock(&$html)
	{

		if(guid()==0) $html->parse("guest_comment");
		if(guid()==0) $html->parse("guest_subscribe");
		if(guid()==0) $html->parse("guest_rating");

        if (Common::getOption('video_player_type') == 'player_custom' && intval($this->video['is_uploaded'])) {
            $html->parse('player_custom', false);
        }

        $total_comments = CVidsTools::countCommentsByVideoId($this->video['id']);
        $html->assign('video', $this->video);
        $rateAlready = CVidsTools::rateAlready($this->video['id']);
        $html->assign('rate_already', $rateAlready);
        if($rateAlready == '')
        {
            $html->assign('rated_class_oryx','displayhide');
            $html->assign('rated_class','vishide');
            $html->assign('readonly','false');
        } else {
            $html->assign('readonly','true');
            $html->assign('video_rating_check',$this->video['rating_check']);
        }
        $html->assign('on_page_comments', 5);
        $html->assign('total_comments', $total_comments);
        $html->assign('my_user_id', guid());
        $html->assign('my_user_photo', urphoto(guid()));


		$html->setvar("user_id",$this->video['user_id']);

        $more = CVidsTools::getVideosByUserExcept($this->video['user_id'], $this->video['id'], '0,2');

        if (count($more) > 0) {
            $html->items('more_item', $more);
			$members_vids_count = CVidsTools::countVideosByUser($this->video['user_id']) - 1;
			if($members_vids_count>2) $html->parse("pager_next");
			else $html->parse("pager_next_inactive");

			$html->setvar("last_video_n",count($more));
			$html->setvar("members_vids_count",$members_vids_count);

            $html->parse('more');
        }
        /*$rel = CVidsTools::getVideosByVideoRel($this->video['id'], '0,3');
        if (count($rel) > 0) {
            $html->items('rel_item', $rel);
            $html->parse('rel');
        }*/
        $i = 0;
        if (!CVidsTools::isSubscrided(guid(), $this->video['user_id'])) {
            $html->parse('subscribe');
            $i++;
        }
        if (Common::isOptionActive('blogs')) {
            $html->parse('blog');
            $i++;
        }

        if ($i == 2) {
            $html->parse('separator');
        }

        if (guid() == $this->video['user_id']) {
            $html->parse('video_edit');
        }

        $state = User::isNarrowBox('vids');
        if  ($state) {
           $html->setvar('display', 'table-cell');
           $html->setvar('hide_narrow_box', 'block');
           $html->setvar('show_narrow_box', 'none');
        } else {
           $html->setvar('display', 'none');
           $html->setvar('hide_narrow_box', 'none');
           $html->setvar('show_narrow_box', 'block');
        }

        parent::parseBlock($html);
    }
}

vids_render_page();
include('./_include/core/main_close.php');
