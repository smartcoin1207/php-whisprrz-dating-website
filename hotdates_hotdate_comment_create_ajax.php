<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

Common::authRequiredExit();

require_once("./_include/current/hotdates/tools.php");

function do_action()
{
	global $g_user;

	$hotdate_id = intval(get_param('hotdate_id'));
	$comment_text = get_param('comment_text');

	if($hotdate_id && $comment_text)
	{
        $hotdate = DB::row("SELECT * FROM hotdates_hotdate WHERE hotdate_id=" . to_sql($hotdate_id, 'Number') . " LIMIT 1");
        if($hotdate)
        {
            DB::execute("INSERT INTO hotdates_hotdate_comment SET hotdate_id=".$hotdate['hotdate_id'].
                                ", user_id=".$g_user['user_id'].
                                ", comment_text=".to_sql(CHotdatesTools::filter_text_to_db($comment_text)).
                                ", created_at = NOW()");
            $id = DB::insert_id();
            Wall::setSiteSection('hotdate');
            Wall::setSiteSectionItemId($hotdate_id);
            Wall::add('hotdate_comment', $id);

            CHotdatesTools::update_hotdate($hotdate['hotdate_id']);

            echo 'ok';
            die();
        }
	}
}

do_action();

include("./_include/core/main_close.php");

?>