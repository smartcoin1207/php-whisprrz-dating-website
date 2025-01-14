<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

function do_action()
{
	global $g_user;
	global $g;
	global $l;

	$id = get_param('id');
	DB::query("SELECT * FROM places_place WHERE id=" . to_sql($id, 'Number') . " LIMIT 1");
	if($place = DB::fetch_row())
	{
		$rating = intval(get_param('rating'));

		if($rating and $rating > 0 and $rating <=10)
		{
			DB::query("SELECT * FROM places_place_vote WHERE user_id=" . $g_user['user_id'] . " AND place_id = " . $place['id'] . " LIMIT 1");
			if($place_vote = DB::fetch_row())
			{
				DB::execute("UPDATE places_place_vote SET rating = " . to_sql($rating, 'Number') . ", updated_at = NOW() ".
					"WHERE user_id=" . $g_user['user_id'] . " AND place_id = " . $place['id'] .
					" LIMIT 1");
			}
			else
			{
				DB::execute("INSERT INTO places_place_vote SET user_id=" . $g_user['user_id'] . ", place_id = " . $place['id'] .
					", rating = " . to_sql($rating, 'Number') . ", created_at = NOW(), updated_at = NOW()");
			}

			DB::query("SELECT SUM(rating), COUNT(id) FROM places_place_vote WHERE place_id = " . $place['id']);
			if($row = DB::fetch_row())
			{
				$n_votes = $row['COUNT(id)'];
				$overal_rating = floor($row['SUM(rating)'] / $n_votes);

				DB::execute("UPDATE places_place SET rating = " . $overal_rating . ", n_votes = " . $n_votes . " WHERE id="  . $place['id'] . " LIMIT 1");
			}

            Wall::addItemForUser($place['id'], 'places', guid());
		}

		die('ok');
	}
	else
		die('error');
}

do_action();

include("./_include/core/main_close.php");

?>