<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class Match_Mail {

    static $module = 'match_mail';
    static $profiles = null;
    static $receiver = null;
    static $lastReceiverId = 0;
    static $debug = false;

    static function setDebug($debug)
    {
        self::$debug = $debug;
    }

    static function getDebug()
    {
        return self::$debug;
    }

    static function debugger($msg = '', $value = '')
    {
        if(self::getDebug()) {
            $msg = "<b>$msg</b>";
            if($value) {
                $msg .= ": $value";
            }
            echo $msg . '<br>';

            global $g;

            $filename = $g['path']['dir_logs'] . 'match_mail.html';
            file_put_contents($filename, date('Y-m-d H:i:s') . ' > ' . $msg . '<br>', FILE_APPEND);
        }
    }

    static function setReceiver($receiver)
    {
        self::$receiver = $receiver;
    }

    static function getReceiver()
    {
        return self::$receiver;
    }

    static function setLastReceiverId($lastReceiverId)
    {
        self::$lastReceiverId = $lastReceiverId;
    }

    static function getLastReceiverId()
    {
        return self::$lastReceiverId;
    }

    static function getModule()
    {
        return self::$module;
    }

    static function setProfiles($profiles)
    {
        self::$profiles = $profiles;
    }

    static function getProfiles()
    {
        return self::$profiles;
    }

    static function getOption($option)
    {
        return Common::getOption($option, self::getModule());
    }

    static function isActive()
    {
        if (!Common::isEnabledAutoMail('match_mail')) {
            return;
        }
        return Common::isOptionActive('active', self::getModule());
    }

    static function lastReceiverId()
    {
        $sql = 'SELECT user_id FROM user
            WHERE ' . self::getBaseWhereForListOfReceivers() . '
            ORDER BY user_id DESC LIMIT 1';
        self::debugger('lastReceiverId', $sql);
        return DB::result($sql);
    }

    static function stop($time)
    {
        $options = array(
            'last_receiver_id' => 0,
            'status' => 2,
            'date' => $time,
        );
        Config::updateAll(self::getModule(), $options);
    }

    static function isTodayStart()
    {
        return (self::getOption('day') == date('w'));
    }


    static function run($debug = false)
    {
        self::setDebug($debug);

        $time = time();
        $timer = microtime(true);
        if (!self::isActive()) {
            self::debugger('NOT ACTIVE');
            return;
        }

        $todayStart = self::isTodayStart();
        $todayDate = date('m-d-Y', $time);
        $status = self::getOption('status');

        if(!$todayStart && !$status) {
            self::debugger('TODAY NOT START and STATUS 0');
            return;
        }

        $lastActionDate = date('m-d-Y', self::getOption('date'));

        if($todayStart && $lastActionDate == $todayDate && $status == '2') {
            self::debugger('TODAY START and STATUS STOP');
            return;
        }

        if (!$todayStart && $status == '2') {
            self::debugger('TODAY NOT START and STATUS STOP');
            return;
        }

        self::setLastReceiverId(self::lastReceiverId());

        if ($status == '1' && self::getLastReceiverId() <= self::getOption('last_receiver_id')) {
            self::debugger('STOP AT START');
            self::stop($time);
            return;
        }

        if ($todayStart && $lastActionDate != $todayDate) {
            // start today send new mails queue
            $options = array(
                'status' => 1,
                'last_receiver_id' => 0,
                'date' => $time,
            );
            Config::updateAll(self::getModule(), $options);
            self::debugger('TODAY START');
        }

        self::sendMatchMail($time);

        $timer = microtime(true) - $timer;
        self::debugger('TIMER ALL', $timer);
    }

    static function sendMatchMail($time)
    {
        global $g;

        $sql = "SELECT *,
            DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d')) AS age
            FROM user
            WHERE " . self::getBaseWhereForListOfReceivers() . "
                AND user_id > " . to_sql(self::getOption('last_receiver_id'), 'Number') . "
            ORDER BY user_id ASC
            LIMIT " . to_sql(self::getOption('limit_mails'), 'Number');

        self::debugger('SEND TO', $sql);

        DB::query($sql);
        if (DB::num_rows() == 0) {
            return;
        }

        self::debugger('SENDER');

        $urlParts = explode('/', Common::urlSite());
        array_pop($urlParts);
        array_pop($urlParts);
        $url = implode('/', $urlParts) . '/';
        $g['path']['url_main'] = $url;

        while ($receiver = DB::fetch_row()) {

            $emailAuto = Common::automailInfo('match_mail', $receiver['lang'], 2);

            if (!$emailAuto) {
                continue;
            }

            $parser = new Match_Mail_Parser('', '', true, $emailAuto['text']);
            $profiles = self::findProfiles($receiver);
            if (count($profiles)) {
                $text = self::parseMail($receiver, $profiles, $parser);

                $subject = $emailAuto['subject'];
                $subject = Common::replaceByVars($subject, $receiver);
                $subject = Common::replaceByVars($subject, $g['main']);
                $emailAuto = array_merge(Common::automailInfo('match_mail', $receiver['lang']), array('subject' => $subject, 'text' => $text));
                Common::sendAutomail($receiver['lang'], $receiver['mail'], 'match_mail', array('title' => $g['main']['title']), $emailAuto);
                self::debugger('SENT');
            }
            unset($parser);
            $options = array('last_receiver_id' => $receiver['user_id']);
            Config::updateAll(self::getModule(), $options);

            if(self::getLastReceiverId() <= self::getOption('last_receiver_id')) {
                self::stop($time);
            }
        }
    }

    static function findProfiles($user)
    {
        $users = array();

        self::debugger('START GET MATCHES', "{$user['user_id']} : {$user['name']}");

        $wheres = array();
        if (self::getOption('with_photo') == 'Y') {
            $wheres[] = 'is_photo = "Y"';
        }

        if (self::getOption('compatible_by_orientation') == 'Y') {
            if(Common::isOptionActive('your_orientation')) {
                $orientationInfo = User::getOrientationInfo($user['orientation']);
                if(isset($orientationInfo['search']) && $orientationInfo['search']) {
                    $wheres[] = 'orientation = ' . to_sql($orientationInfo['search'], 'Number');
                }
            } else {
                $partnerOrientation = User::getPartnerOrientationWhereSql('', $user['p_orientation']);
                if($partnerOrientation) {
                    $wheres[] = $partnerOrientation;
                }
            }
        }

        if (self::getOption('compatible_by_age') == 'Y') {
            $wheres[] = '(p_age_from = 0 OR p_age_from <= ' . to_sql($user['age'], 'Number') . ')
                AND (p_age_to = 0 OR p_age_to >= ' . to_sql($user['age'], 'Number') . ')';
            if ($user['p_age_from'] > 0) {
                $wheres[] = "DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d')) >= " . to_sql($user['p_age_from'], 'Number');
            }
            if ($user['p_age_to'] > 0) {
                $wheres[] = "DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d')) <= " . to_sql($user['p_age_to'], 'Number');
            }
        }

        $wheres[] = 'active = 1';
        $wheres[] = 'hide_time = 0';
        $wheres[] = 'register > NOW() - INTERVAL 7 DAY';
        $wheres[] = 'user_id != ' . to_sql($user['user_id'], 'Number');
        $wheres[] = 'ban_global = 0';

        $where = implode(' AND ', $wheres);

        // order by location - first of all near users

        $sql = "SELECT user_id, name, name_seo, gender, country, state, city, DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d')) AS age,
            IF(city_id = " . to_sql($user['city_id'], 'Number') . ", 1, 0) +
            IF(state_id = " . to_sql($user['state_id'], 'Number') . ", 1, 0) +
            IF(country_id = " . to_sql($user['country_id'], 'Number') . ", 1, 0) AS near
            FROM user
            WHERE $where
            ORDER BY near DESC, is_photo ASC, user_id DESC
            LIMIT " . to_sql(self::getOption('limit_users'), 'Number');
        DB::query($sql, 2);

        self::debugger('END GET MATCHES', $sql);
        self::debugger('USERS FOUND', DB::num_rows(2));

        while ($row = DB::fetch_row(2)) {
            $row['photo'] = User::getPhotoDefault($row['user_id'], self::getOption('photo_size'), false, $row['gender']);
            $sql = 'SELECT COUNT(*) FROM photo
                WHERE user_id = ' . to_sql($row['user_id'], 'Number')
                    . Common::getOption('photo_vis', 'sql');
            $row['photo_count'] = DB::result($sql, 0, 4);
            $users[] = $row;

            self::debugger('MATCH ADD', "{$row['user_id']} : {$row['name']}");
        }

        self::debugger('return users list', count($users));
        return $users;
    }

    static function parseMail($receiver, $profiles, &$parser)
    {
        $timer = microtime(true);
        self::setReceiver($receiver);
        self::setProfiles($profiles);

        $parser->stateSave();
        $tmp = null;
        $data = $parser->parse($tmp, true);
        $parser->stateRestore();

        $timer = microtime(true) - $timer;

        self::debugger('TIMER', $timer);

        return $data;
    }

    static function getBaseWhereForListOfReceivers()
    {
        $where = ' active = 1 AND use_as_online = 0 AND match_mail != 2 AND ban_global = 0';
        return $where;
    }

}

class Match_Mail_Parser extends CHtmlBlock {

    function parseBlock(&$html)
    {
        $receiver = Match_Mail::getReceiver();

        htmlSetVars($html, $receiver);

        $html->setvar('autologin_param', User::urlAddAutologin('', $receiver));

        $profiles = Match_Mail::getProfiles();
        $profilesOnRow = intval(Match_Mail::getOption('profiles_on_row'));
        if ($profilesOnRow < 1) {
            $profilesOnRow = 1;
        }

        $html->setblockvar('user', '');
        $html->setblockvar('user_empty', '');
        $html->setblockvar('users_row', '');

        $tdWidth = (100 / $profilesOnRow) . '%';

        if (is_array($profiles) && count($profiles)) {
            $index = 0;

            Match_Mail::debugger('Match_Mail_Parser', 'Start');

            foreach ($profiles as $profile) {
                $index++;

                Match_Mail::debugger('Match_Mail_Parser Profile', "{$profile['user_id']} : {$profile['name']}");

                // location of profile
                $delimiter = '';
                $location = '';
                $locationParts = array('city', 'state', 'country');

                foreach($locationParts as $locationPart) {
                    if($profile[$locationPart] != '') {
                        $location .= $delimiter . l($profile[$locationPart]);
                        $delimiter = ', ';
                    }
                }
                $profile['location'] = $location;

                $profile['link'] = Common::getOption('url_main', 'path') . User::urlAddAutologin(User::url($profile['user_id'], $profile), $receiver);

                foreach ($profile as $k => $v) {
                    $html->setvar('user_' . $k, $v);
                }

                $html->setvar('td_width', $tdWidth);

                $html->parse('user');

                if ($index == $profilesOnRow) {
                    $html->parse('users_row');
                    $html->setblockvar('user', '');
                    $index = 0;
                }
            }

            if ($index) {
                while ($index < $profilesOnRow) {
                    $index++;
                    $html->parse('user_empty');
                }
                $html->parse('users_row');

                Match_Mail::debugger('Match_Mail_Parser Parse', 'users_row');
            }

            Match_Mail::debugger('Match_Mail_Parser', 'End');
        }

        parent::parseBlock($html);
    }

}

/*

Prepare database for test:

UPDATE user SET match_mail = 1;
UPDATE user SET use_as_online = 0;

UPDATE `config` SET `value` = 0 WHERE `module` = 'match_mail' AND `option` = 'date';
UPDATE `config` SET `value` = 0 WHERE `module` = 'match_mail' AND `option` = 'status';

 */