<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

Common::authRequiredExit();

function do_action()
{
	global $g_user;
	global $g;
	global $l;

	$review_id = get_param('review_id');
	DB::query("SELECT * FROM places_review WHERE id=" . to_sql($review_id, 'Number') . " LIMIT 1");
	if($review = DB::fetch_row())
	{
		DB::query("SELECT * FROM places_review_vote WHERE user_id=" . $g_user['user_id'] . " AND review_id = " . $review['id'] . " LIMIT 1");
		if(!($place_vote = DB::fetch_row()))
		{
			DB::execute("INSERT INTO places_review_vote SET user_id=" . $g_user['user_id'] . ", review_id = " . $review['id'] . ", created_at = NOW()");
		}

		$n_votes = DB::result("SELECT COUNT(id) FROM places_review_vote WHERE review_id = " . $review['id']);
		DB::execute("UPDATE places_review SET n_votes = " . $n_votes . " WHERE id="  . $review['id'] . " LIMIT 1");



		echo $n_votes;
		die();
	}
	else
		die('error');
}

do_action();

include("./_include/core/main_close.php");

?>