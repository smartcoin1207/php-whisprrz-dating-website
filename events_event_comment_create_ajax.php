<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

Common::authRequiredExit();

require_once("./_include/current/events/tools.php");

function do_action()
{
	global $g_user;

	$event_id = intval(get_param('event_id'));
	$comment_text = get_param('comment_text');

	if($event_id && $comment_text)
	{
        $event = DB::row("SELECT * FROM events_event WHERE event_id=" . to_sql($event_id, 'Number') . " LIMIT 1");
        if($event)
        {
            DB::execute("INSERT INTO events_event_comment SET event_id=".$event['event_id'].
                                ", user_id=".$g_user['user_id'].
                                ", comment_text=".to_sql(CEventsTools::filter_text_to_db($comment_text)).
                                ", created_at = NOW()");
            $id = DB::insert_id();
            Wall::setSiteSection('event');
            Wall::setSiteSectionItemId($event_id);
            Wall::add('event_comment', $id);

            CEventsTools::update_event($event['event_id']);

            echo 'ok';
            die();
        }
	}
}

do_action();

include("./_include/core/main_close.php");

?>