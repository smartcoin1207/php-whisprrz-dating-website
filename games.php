<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/games/custom_head.php");

$optionTemplateSet = Common::getOption('set', 'template_options') ;
if ($optionTemplateSet == 'urban') {
    if (!City::isActiveGames()) {
        Common::toHomePage();
    }
    // City::accessCheck();
    payment_check('games');

} else {
    payment_check('games');
}

CustomPage::setSelectedMenuItemByTitle('column_narrow_game_choose');

class CGames extends CHtmlBlock
{
    function action()
    {
        $cmd = get_param('cmd');

        if($cmd == 'lang') {
            $game = get_param('game',false);

            header('Content-Type: text/xml; charset=UTF-8');
            header('Cache-Control: no-cache, must-revalidate');

            $words = array(
                'Start_Game',
                'Waiting',
                'Play_again',
                'Scorched_Earth',
                'You_won',
                'You_lost',
                'Your_move',
                'Waiting_for',
                'Click_here_to_begin_you_move',
                'vs',
                'Start',
                'Your_name',
                'Partners_name',
                'Game_Over',
                'move',
                'Waiting_for_the_partner',
                'Your_partner_is_out_of_game',
                'Throwing',
                'Walking',
                'Aiming',
                'You_Win',
                'You_Lose',
                'Wait_please',
                'Your_shoot',
                'again',
                'waiter',
                'start',
                'Clear',
                'Begin_the_game',
                'Battle_Sea',
                'Wait',
                'Draughts',
                'You_win',
                'You_lose',
                'Again',
                'chess',
                'Waiting_for_the_opponent',
                'CHECKMATE',
                'CHECK_Your_move',
                'Choose_the_figure_for_the_changing',
                'Your_move',
                'Waiting_for_the_opponent',
                'Prepare_for_your_next_move',
                'pool',
                's',
            );
            $lang = '<lang>';
            foreach($words as $wordKey) {
                $lang .= "<word name='$wordKey'>" . l($wordKey,false,$game) . '</word>';
            }
            $lang .= '</lang>';

            echo $lang;
            die();
        }
    }

	function on_new_user_slot(&$html, &$row, &$col, $empty)
	{
		$block_name = "row_" . ($row < 3 ? $row : ($row % 2 + 3));
		$user_postfix = $empty ? "_no_user" : "_user";

		if(($row == 1 && $col == 2) || ($row == 2 && $col == 4) || ($row > 2 && $col == 7))
        {
            $html->setvar('games_last', 'games_last');

            $html->parse($block_name . $user_postfix);
            $html->parse($block_name, false);
            if($row > 2 && (($row % 2) || $empty))
            {
            	$html->parse('full_rows');
                $html->clean('row_3_user');
                $html->clean('row_3');
                $html->clean('row_4_user');
                $html->clean('row_4');
            }
            ++$row;
            $col = 1;
        }
        else
        {
            $html->setvar('games_last', '');

            $html->parse($block_name . $user_postfix);

            ++$col;
        }
	}

    function on_new_game_slot(&$html, &$row, &$col, $empty)
	{
		$block_name = "row_" . ($row < 3 ? $row : ($row % 2 + 3));
		$user_postfix = $empty ? "_no_user" : "_user";

		if(($row == 1 && $col == 2) || ($row == 2 && $col == 4) || ($row > 2 && $col == 7))
        {
            $html->setvar('games_last', 'games_last');

            $html->parse($block_name . $user_postfix);
            $html->parse($block_name, false);
            if($row > 2 && (($row % 2) || $empty))
            {
            	$html->parse('full_rows');
                $html->clean('row_3_user');
                $html->clean('row_3');
                $html->clean('row_4_user');
                $html->clean('row_4');
            }
            ++$row;
            $col = 1;
        }
        else
        {
            $html->setvar('games_last', '');

            $html->parse($block_name . $user_postfix);

            ++$col;
        }
	}

	function parseBlock(&$html)
	{
		global $g;
		global $g_user;
        global $swf;

        $optionTmplSet = Common::getOption('set', 'template_options') ;
        $parseTemplateMethod = 'parseGames' . $optionTmplSet;
        if (method_exists('Games', $parseTemplateMethod)) {
            Games::$parseTemplateMethod($html);

            TemplateEdge::parseColumn($html);

            parent::parseBlock($html);
            return;
        }

        if (Common::isMultisite()) {
            unset($g['games']['tanks']);
        }

        $selected_user_id = get_param('id', 0);
        $game = get_param('game');
        if (!$game) {
            $users = DB::rows("
	           (SELECT u.* FROM user as u WHERE u.user_id = " . to_sql($selected_user_id, 'NUMBER') . ") UNION DISTINCT
	           (SELECT u.* FROM friends_requests as f, user as u WHERE ((f.user_id='" . $g_user['user_id'] . "' AND u.user_id = f.friend_id) OR (f.friend_id='" . $g_user['user_id'] . "' AND u.user_id = f.user_id)) AND f.accepted=1 AND (u.last_visit>'" . (date("Y-m-d H:i:s", time() - $g['options']['online_time'] * 60)) . "'" . " OR u.use_as_online=1)" . " )");

            $row = 1;
            $col = 1;

            foreach ($users as $user) {
                $html->setvar('user_id', $user['user_id']);
                $html->setvar('user_name', User::nameShort(to_html($user['name'])));
                $html->setvar('user_name_full', to_html($user['name']));
                $html->setvar('user_photo', $g['path']['url_files'] . User::getPhotoDefault($user['user_id'], "r"));
                $html->setvar('user_selected', $user['user_id'] == $selected_user_id ? "" : 'style="display:none;"');

                $this->on_new_user_slot($html, $row, $col, false);
            }

            while ($row < 3 || $col != 1) {
                $this->on_new_user_slot($html, $row, $col, true);
            }

            $html->setvar('selected_user_id', $selected_user_id);

            $games_img = array(
                'pool' => 'foto_games05.png',
                'chess' => 'foto_games04.png',
                'shashki' => 'foto_games03.png',
                'tanks' => 'foto_games06.png',
                'morboy' => 'foto_games02.png',
                'lovetree' => 'foto_games01.png',
            );
            $i = 0;
            foreach ($g['games'] as $key => $value) {
                if ($value){
                    $i++;
                    $html->setvar('key', $key);
                    $html->setvar('name', l($key));
                    $html->setvar('description', l($key . '_description'));
                    $html->setvar('img', $games_img[$key]);
                    if ($i%2) {
                        $html->setvar('band_class', 'games_orange');
                    } else {
                        $html->setvar('band_class', '');
                    }
                    $html->parse('game', true);
                }
            }

            $html->parse('select');
        } else {
            $user = DB::row("SELECT u.* FROM user as u WHERE u.user_id = " . to_sql($selected_user_id, 'NUMBER'));

            if ($user) {
                DB::execute("DELETE FROM game_reject WHERE to_user=" . to_sql($g_user['user_id'], "Number") . " AND from_user=" . to_sql($user['user_id'], "Number"));
                $user['last_visit'] = time_mysql_dt2u($user['last_visit']);
                if (((time() - $user['last_visit']) / 60) < $g['options']['online_time']) {
                    foreach ($user as $k => $v)
                        $html->setvar($k, $v);
                    if ($game == 'lovetree') {
                        //$html->setvar('height', 426);
                        DB::query("SELECT id FROM users WHERE login='" . $g_user['name'] . "'");
                        $id = DB::fetch_row();
                        if ($id['id'] > 0) {
                            DB::execute("UPDATE users SET enemy=NULL, x0=NULL, nowX=NULL, angle=NULL, zernoX=NULL, sila=NULL, popal=NULL, upal=NULL, active='no', ingame='no', time_in=NULL WHERE login='" . $g_user['name'] . "'");
                        } else {
                            DB::execute("INSERT INTO users SET gender=" . to_sql(strtolower($g_user['gender']), "Text") . ",enemy=NULL, x0=NULL, nowX=NULL, angle=NULL, zernoX=NULL, sila=NULL, popal=NULL, upal=NULL, active='no', ingame='no', time_in=NULL, login='" . $g_user['name'] . "'");
                        }
                        /*$html->setvar("emeny_name", $user['name']);
                        $html->setvar("my_name", $g_user['name']);
                        $html->setvar("kolvoAll", 6);
                        $html->setvar("kolvoPopitok", 10);
                        $html->parse("lovetree", true);*/
                    } elseif ($game == "test") {
                        //$html->setvar('flash_game', User::flashGames('test', $user));
                        //$html->setvar('game_id', $swf['test']['attributes']['id']);
                        //$html->setvar('height', 426);
                        DB::query("SELECT id FROM users WHERE login='" . $g_user['name'] . "'");
                        $id = DB::fetch_row();
                        if ($id['id'] > 0) {
                            DB::execute("UPDATE users SET enemy=NULL, x0=NULL, nowX=NULL, angle=NULL, zernoX=NULL, sila=NULL, popal=NULL, upal=NULL, active='no', ingame='no', time_in=NULL WHERE login='" . $g_user['name'] . "'");
                        } else {
                            DB::execute("INSERT INTO users SET gender=" . to_sql(strtolower($g_user['gender']), "Text") . ",enemy=NULL, x0=NULL, nowX=NULL, angle=NULL, zernoX=NULL, sila=NULL, popal=NULL, upal=NULL, active='no', ingame='no', time_in=NULL, login='" . $g_user['name'] . "'");
                        }

                        /*$html->setvar("emeny_name", $user['name']);
                        $html->setvar("my_name", $g_user['name']);
                        $html->setvar("kolvoAll", 6);
                        $html->setvar("kolvoPopitok", 10);
                        $html->parse("test", true);*/
                    } elseif ($game == "morboy") {
                        //$html->setvar('flash_morboy', User::flashGames('morboy', $user));
                        //$html->setvar('height', 426);
                        DB::query("SELECT id FROM game_morboy WHERE login='" . $g_user['name'] . "'");
                        $id = DB::fetch_row();
                        if ($id['id'])
                            DB::execute("UPDATE game_morboy SET enemy=NULL, nowY=NULL, nowX=NULL, active='no', ingame='no', time_in=NULL, massiv=NULL, popal=NULL, shodil='n', pokazal='n' WHERE login='" . $g_user['name'] . "'");
                        else
                            DB::execute("INSERT INTO game_morboy SET enemy=NULL, nowY=NULL, nowX=NULL, active='no', ingame='no', time_in=NULL, massiv=NULL, popal=NULL, shodil='n', pokazal='n', login='" . $g_user['name'] . "'");

                        //$html->setvar("emeny_name", $user['name']);
                        //$html->setvar("my_name", $g_user['name']);
                        //$html->parse("morboy", true);
                    }
                    elseif ($game == "shashki") {
                        //$html->setvar('flash_shashki', User::flashGames('shashki', $user));
                        DB::query("SELECT id FROM game_shashki WHERE login='" . $g_user['name'] . "'");
                        $id = DB::fetch_row();
                        if ($id['id'])
                            DB::execute("UPDATE game_shashki SET damka='false', num_shashka=NULL, enemy=NULL, nowY=NULL, nowX=NULL, active='no', ingame='no', time_in=NULL, srubil=NULL WHERE login='" . $g_user['name'] . "'");
                        else
                            DB::execute("INSERT INTO game_shashki (login , enemy , nowX , nowY , ingame , time_in , active , srubil , num_shashka , damka ) VALUES ('" . $g_user['name'] . "', NULL , NULL , NULL , 'no', NULL , 'no', NULL , NULL , 'false');");

                        //$html->setvar("emeny_name", $user['name']);
                        //$html->setvar("my_name", $g_user['name']);
                        //$html->parse("shashki", true);
                    }
                    elseif ($game == "chess") {
                        //$html->setvar('flash_chess', User::flashGames('chess', $user));
                        DB::query("SELECT id FROM game_shashki WHERE login='" . $g_user['name'] . "'");
                        $id = DB::fetch_row();
                        //!!!не я if ($id['id']) DB::execute("UPDATE game_chess SET damka='false', num_shashka=NULL, enemy=NULL, nowY=NULL, nowX=NULL, active='no', ingame='no', time_in=NULL, srubil=NULL WHERE login='" . $g_user['name'] . "'");
                        //!!!не я else DB::execute("INSERT INTO game_chess (login , enemy , nowX , nowY , ingame , time_in , active , srubil , num_shashka , damka ) VALUES ('" . $g_user['name'] . "', NULL , NULL , NULL , 'no', NULL , 'no', NULL , NULL , 'false');");

                        //$html->setvar("emeny_name", $user['name']);
                        //$html->setvar("my_name", $g_user['name']);
                        //$html->parse("chess", true);
                    } elseif ($game == "pool") {
                        //$html->setvar('flash_pool', User::flashGames('pool', $user));
                        DB::query("SELECT id FROM game_shashki WHERE login='" . $g_user['name'] . "'");
                        $id = DB::fetch_row();
                        //!!!не я if ($id['id']) DB::execute("UPDATE game_chess SET damka='false', num_shashka=NULL, enemy=NULL, nowY=NULL, nowX=NULL, active='no', ingame='no', time_in=NULL, srubil=NULL WHERE login='" . $g_user['name'] . "'");
                        //!!!не я else DB::execute("INSERT INTO game_chess (login , enemy , nowX , nowY , ingame , time_in , active , srubil , num_shashka , damka ) VALUES ('" . $g_user['name'] . "', NULL , NULL , NULL , 'no', NULL , 'no', NULL , NULL , 'false');");

                        //$html->setvar("emeny_name", $user['name']);
                        //$html->setvar("my_name", $g_user['name']);
                        //$html->parse("pool", true);
                    } elseif ($game == "tanks") {
                        //$html->setvar('flash_tanks', User::flashGames('tanks', $user));
                        //DB::query("SELECT id FROM game_shashki WHERE login='" . $g_user['name'] . "'");
                        //$id = DB::fetch_row();
                        //!!!не я if ($id['id']) DB::execute("UPDATE game_chess SET damka='false', num_shashka=NULL, enemy=NULL, nowY=NULL, nowX=NULL, active='no', ingame='no', time_in=NULL, srubil=NULL WHERE login='" . $g_user['name'] . "'");
                        //!!!не я else DB::execute("INSERT INTO game_chess (login , enemy , nowX , nowY , ingame , time_in , active , srubil , num_shashka , damka ) VALUES ('" . $g_user['name'] . "', NULL , NULL , NULL , 'no', NULL , 'no', NULL , NULL , 'false');");
                        DB::execute('DELETE FROM `game_chess` WHERE login = ' . to_sql($g_user['name'], 'Text'));
                        //$html->setvar("emeny_name", $user['name']);
                        //$html->setvar("my_name", $g_user['name']);
                        //$html->parse("tanks", true);
                    }
                    $html->setvar('flash_game', User::flashGames($game, $user));
                    $html->setvar('game_id', $swf[$game]['attributes']['id']);
                }
                else
                    redirect('games.php');
            }
            else
                redirect('games.php');
        }

        parent::parseBlock($html);
    }
}

$page = new CGames("", getPageCustomTemplate('games.html', 'games_template'));

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

if (Common::isParseModule('custom_head')){
    $games_custom_head = new CGamesCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_games_custom_head.html");
    $header->add($games_custom_head);
}

if (Common::isParseModule('complite')){
    $complite = new CComplite("complite", $g['tmpl']['dir_tmpl_main'] . "_complite.html");
    $page->add($complite);
}

if (Common::isParseModule('profile_colum_narrow')){
    $column_narrow = new CProfileNarowBox('profile_column_narrow', $g['tmpl']['dir_tmpl_main'] . '_profile_column_narrow.html');
    $page->add($column_narrow);
}

$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");