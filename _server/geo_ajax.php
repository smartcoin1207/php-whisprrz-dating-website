<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['no_headers'] = true;
$g['to_root'] = "../";
$area = "test";
include($g['to_root'] . "_include/core/main_start.php");

function do_action()
{
    $cmd = get_param('cmd');

    if($cmd == 'countries')
        echo(DB::db_options("SELECT country_id, country_title FROM geo_country;"));
    else if($cmd == 'states')
        echo(DB::db_options("SELECT state_id, state_title FROM geo_state WHERE country_id=" . to_sql(get_param('country_id')) . " ORDER BY state_title;"));
    else if($cmd == 'cities')
        echo(DB::db_options("SELECT city_id, city_title FROM geo_city WHERE state_id=" . to_sql(get_param('state_id')) . " ORDER BY city_title;"));
}

do_action();