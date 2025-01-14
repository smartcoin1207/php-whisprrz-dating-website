<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

Common::authRequiredExit();

require_once("./_include/current/partyhouz/tools.php");

function do_action()
{
	global $g_user;

	$partyhou_id = intval(get_param('partyhou_id'));
	$comment_text = get_param('comment_text');

	if($partyhou_id && $comment_text)
	{
        $partyhou = DB::row("SELECT * FROM partyhouz_partyhou WHERE partyhou_id=" . to_sql($partyhou_id, 'Number') . " LIMIT 1");
        if($partyhou)
        {
            DB::execute("INSERT INTO partyhouz_partyhou_comment SET partyhou_id=".$partyhou['partyhou_id'].
                                ", user_id=".$g_user['user_id'].
                                ", comment_text=".to_sql(CpartyhouzTools::filter_text_to_db($comment_text)).
                                ", created_at = NOW()");
            $id = DB::insert_id();
            Wall::setSiteSection('partyhou');
            Wall::setSiteSectionItemId($partyhou_id);
            Wall::add('partyhou_comment', $id);

            CpartyhouzTools::update_partyhou($partyhou['partyhou_id']);

            echo 'ok';
            die();
        }
	}
}

do_action();

include("./_include/core/main_close.php");

?>