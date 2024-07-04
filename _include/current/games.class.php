<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class Games
{

    static public function action()
    {
        $url = explode('.', Common::page());
        switch ($url[0]) {
            case 'record_hod':
                $method = 'recordHod';
                break;

            case 'check_hod':
                $method = 'checkHod';
                break;

            default:
                $method = $url[0];
                break;
        }

        if (method_exists('Games', $method)){
            self::$method();
        }
    }

    static public function isParamActive($parameter)
    {
        return (get_param($parameter, '') == 'yes') ? true : false;
    }

    static public function set()
    {
        $status = (self::isParamActive('yesNo')) ? 'yes' : 'no';
        $sql = "UPDATE `users`
                   SET `active` = '" . $status . "'
                 WHERE `login` = " . to_sql(get_param('activeLogin', ''), 'Text');
        DB::execute($sql);

        echo "isOk='ok'";
    }

    static public function setEmpty()
    {
        $sql = "UPDATE `users`
                   SET `enemy` = NULL, `x0` = NULL, `nowX` = NULL, `angle` = NULL, `zernoX` = NULL, `sila` = NULL, `popal` = NULL,
                       `upal` = NULL, `active` = 'no', `ingame` = 'no', `time_in` = NULL
                 WHERE `login` = " . to_sql(get_param('myLoginIN', ''), 'Text');
        DB::execute($sql);
    }

    static public function check()
    {
        $response = '';
        $enemyLogin = get_param('enemyLogin', '');

        $sql = "SELECT *
                  FROM `users`
                 WHERE `login` = " . to_sql($enemyLogin, 'Text');
        $row = DB::row($sql);
        $inGame = $row['ingame'];
        $response = 'timeEnemy=' . $row['time_in'] . '&ingame=' . $inGame;

        if ($inGame == 'yes') {
            $sql = "UPDATE `users`
                       SET `ingame` = 'no'
                     WHERE `login` = " . to_sql($enemyLogin, 'Text');
            DB::execute($sql);
        }

        $sql = "SELECT *
                  FROM `users`
                 WHERE `login` = " . to_sql(get_param('myLogin', ''), 'Text');
        $row = DB::row($sql);
        echo $response . '&timeMy=' . $row['time_in'];
    }

    static public function checkHod()
    {
        $sql = "SELECT  *
                  FROM `users`
                 WHERE `login` = " . to_sql(get_param('myEnemy', ''), 'Text');
        $row = DB::row($sql);

        $timeIn = ($row['time_in'] == NULL) ? 'fuck' : $row['time_in'];
        echo  "enemyActive=" . $row['active']
            . "&x0Hod=" . $row['x0']
            . "&nowXHod=" . $row['nowX']
            . "&angleHod=" . $row['angle']
            . "&silaHod=" . $row['sila']
            . "&popalHod=" . $row['popal']
            . "&upalHod=" . $row['upal']
            . "&zernoX=" . $row['zernoX']
            . "&isOver=" . $timeIn;
    }

    static function record()
    {
        $nowTime = mktime();
        $myLogin = to_sql(get_param('myLogin', ''), 'Text');
        $enemyLogin = to_sql(get_param('enemyLogin', ''), 'Text');

        $sql = "UPDATE `users`
                   SET `time_in` = " . $nowTime . ",
                       `enemy` = " . $enemyLogin . ",
                       `ingame` = 'yes'
                 WHERE `login` = " . $myLogin;
        DB::execute($sql);

        // дубли, перенести в отдельный метод получение пола, возможно через DB::result()
        $sql = "SELECT `gender` FROM `users` WHERE `login` = " . $myLogin;
        $row = DB::row($sql, 1);
        $myGender = $row['gender'];

        $sql = "SELECT * FROM `users` WHERE `login` = " . $enemyLogin;
        $row = DB::row($sql, 2);
        $enemyGender = $row['gender'];

        echo 'mygender=' . $myGender . '&enemygender=' . $enemyGender;


    }

    static function recordHod()
    {
        $sql = "UPDATE `users`
                   SET `x0` = " . to_sql(get_param('x0Hod', ''), 'Number') . ",
                       `nowX` = " . to_sql(get_param('nowXHod', ''), 'Number') . ",
                       `angle` = " . to_sql(get_param('angleHod', ''), 'Number') . ",
                       `zernoX` = " . to_sql(get_param('nowZernoX', ''), 'Text') . ",
                       `sila` = " . to_sql(get_param('silaHod', ''), 'Number') . ",
                       `popal` = " . to_sql(get_param('popalHod', ''), 'Text') . ",
                       `upal` = " . to_sql(get_param('upalHod', ''), 'Text') . ",
                       `active` = 'no'
                 WHERE `login` = " . to_sql(get_param('loginHod', ''), 'Text');
        DB::execute($sql);

        $sql = "UPDATE `users` SET `active` = 'yes' WHERE `login` = " . to_sql(get_param('enemyHod', ''), 'Text');
        DB::execute($sql);

        echo "myOk=ok";
    }

    static function parseGamesUrban($html)
    {
        $where = '`status` = 1 AND `game` = 1 AND `hide` = 0';
        $where .= ' AND `id` NOT IN(' . to_sql(implode(',', City::getLocationGameData()), 'Plain') . ')';

        $games = DB::column('SELECT `name` FROM ' . City::getTable('city_rooms') . ' WHERE ' . $where . ' ORDER BY `position`');
        foreach ($games as $game) {
            $key = City::prepareSeoAlias($game);
            $html->setvar('game', $key);
            $html->setvar('game_title', lCascade(l($game), array($key . '_city')));
            $html->setvar('game_url', City::url($game, false, true, true));
            $html->parse('game', true);
        }
    }
}