<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
include('./_include/current/vids/start.php');

class CVidsList extends CHtmlBlock
{


	function parseBlock(&$html)
	{


		$start = intval(get_param("page",0));
		$user_id = get_param("user_id",0);
		$video_id = get_param("video_id",0);

        $more = CVidsTools::getVideosByUserExcept($user_id, $video_id, "$start,2");

        if (count($more) > 0) {
            $html->items('more_item', $more);
			$members_vids_count = CVidsTools::countVideosByUser($user_id) - 1;

			$html->setvar("page_n",$start+2);

			if($members_vids_count>$start+2) $html->parse("pager_next");
			else $html->parse("pager_next_inactive");

			$html->setvar("page_n",$start-2);

			if($start>1) $html->parse("pager_prev");
			else $html->parse("pager_prev_inactive");

			$html->setvar("first_video_n",$start+1);
			$html->setvar("last_video_n",$start+count($more));
			$html->setvar("members_vids_count",$members_vids_count);

            $html->parse('more');
        }


        parent::parseBlock($html);
    }
}

$page = new CVidsList("", $g['tmpl']['dir_tmpl_main'] . "vids_more_list_ajax.html");

include("./_include/core/main_close.php");