<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class PushNotification
{

    private static $tableTokens = 'app_push_tokens';

    public static function send($to, $message, $data = false)
    {
        if(!Common::getOption('push_notifications_settings_api_key')) {
            return false;
        }

        $where = '`user_id` = ' . to_sql($to);
        $tokensInfo = DB::select(self::$tableTokens, $where);

        if ($tokensInfo) {
            $tokens = array();
            foreach ($tokensInfo as $tokenInfo) {
                $tokens[] = $tokenInfo['token'];
            }

            if ($tokens) {

                $params = array(
                    'registration_ids' => $tokens,
                    'notification' => array(
                        'title' => Common::getOption('title', 'main'),
                        'body' => $message,
                        'sound' => 'default',
                        'badge' => CIm::getCountNewMessages(null, null, $to),
                    ),
                );

                $color = trim(Common::getOption('push_notifications_android_icon_color'));
                if($color) {
                    $params['notification']['color'] = $color;
                }

                if ($data) {
                    $params['data'] = $data;
                }

                if(!isset($params['data']['userFrom'])) {
                    $params['data']['userFrom'] = guid();
                }

                $result = self::sendMessageToFirebase($params);

                if(!is_array($result)) {
                    error_to_log($result);
                    //error_to_log($result . "\n\nTOKENS > " . print_r($tokens, true));
                }

                if (is_array($result)) {
                    if (isset($result['results'])) {
                        foreach ($result['results'] as $index => $result) {
                            if (isset($result['error'])) {
                                $error = $result['error'];
                                if ($error == 'InvalidRegistration' || $error == 'NotRegistered' || $error == 'MismatchSenderId') {
                                    self::deleteToken($tokens[$index], $to);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public static function sendIm($to, $message, $dataPushNotification = null)
    {
        $data = array(
            'type' => 'im',
        );
        if (is_array($dataPushNotification)) {
            $data = array_merge($data, $dataPushNotification);
        }
        self::send($to, $message, $data);
    }

    public static function sendCity($to, $message)
    {
        $data = array(
            'type' => 'city',
        );
        self::send($to, $message, $data);
    }

    public static function sendChatVideo($to, $message)
    {
        $data = array(
            'type' => 'chatVideo',
        );
        self::send($to, $message, $data);
    }

    public static function sendChatAudio($to, $message)
    {
        $data = array(
            'type' => 'chatAudio',
        );
        self::send($to, $message, $data);
    }

    public static function sendChatStreet($to, $message)
    {
        $data = array(
            'type' => 'chatStreet',
        );
        self::send($to, $message, $data);
    }

    public static function addToken()
    {
        $isTokenAdded = false;

        $token = get_param('token');
        $oldToken = get_param('oldToken');

        $userId = guid();

        $operationSystem = false;

        if (Common::isAppAndroid()) {
            $operationSystem = 'android';
        } elseif (Common::isAppIos()) {
            $operationSystem = 'ios';
        }

        if ($userId && $token && $operationSystem) {

            $lastUpdate = time();
            $sql = 'INSERT IGNORE INTO `' . to_sql(self::$tableTokens, 'Plain') . '`
                SET `user_id` = ' . to_sql($userId) . ',
                    `operation_system` = ' . to_sql($operationSystem) . ',
                    `token` = ' . to_sql($token) . ',
                    `last_update` = ' . to_sql($lastUpdate) . '
                ON DUPLICATE KEY UPDATE `last_update` = ' . to_sql($lastUpdate) . ',
                    `user_id` = ' . to_sql($userId) . ',
                    `operation_system` = ' . to_sql($operationSystem);
            DB::execute($sql);

            $where = '`user_id` = ' . to_sql($userId) . '
                AND `operation_system` = ' . to_sql($operationSystem) . '
                AND `token` = ' . to_sql($token);
            $tokenData = DB::one(self::$tableTokens, $where);

            if ($tokenData && $tokenData['user_id'] == $userId && $tokenData['token'] == $token) {
                $isTokenAdded = true;
            }
        }

        if ($oldToken) {
            $oldTokenData = json_decode($oldToken, true);
            if (isset($oldTokenData['user_id']) && isset($oldTokenData['token']) && $oldTokenData['token'] != $token) {
                $where = '`user_id` = ' . to_sql($oldTokenData['user_id']) . '
                    AND `token` = ' . to_sql($oldTokenData['token']);
                DB::delete(self::$tableTokens, $where);
            }
        }

        return $isTokenAdded;
    }

    public static function addTokenToDatabase($userId, $token, $operationSystem)
    {
        $lastUpdate = time();
        $sql = 'INSERT IGNORE INTO `' . to_sql(self::$tableTokens, 'Plain') . '`
            SET `user_id` = ' . to_sql($userId) . ',
                `operation_system` = ' . to_sql($operationSystem) . ',
                `token` = ' . to_sql($token) . ',
                `last_update` = ' . to_sql($lastUpdate) . '
            ON DUPLICATE KEY UPDATE `last_update` = ' . to_sql($lastUpdate);
        DB::execute($sql);

        $where = '`user_id` = ' . to_sql($userId) . '
            AND `operation_system` = ' . to_sql($operationSystem) . '
            AND `token` = ' . to_sql($token);
        $tokenData = DB::one(self::$tableTokens, $where);

        return $tokenData;
    }

    public static function deleteToken($token = false)
    {
        $isDeleted = false;

        if(!$token) {
            $token = get_param('token');
        }

        if ($token) {
            $where = '`token` = ' . to_sql($token);

            DB::delete(self::$tableTokens, $where);

            if (DB::affected_rows()) {
                $isDeleted = true;
            } elseif (DB::count(self::$tableTokens, $where) == 0) {
                $isDeleted = true;
            }

            if(!$isDeleted && !DB::one(self::$tableTokens, $where)) {
                $isDeleted = true;
            }
        }

        return $isDeleted;
    }

    public static function sendMessageToFirebase($params)
    {
        $result = false;
   $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = array(
            'Authorization:key=' . Common::getOption('push_notifications_settings_api_key'),
            'Content-Type:application/json',
        );
        $response = urlGetContents($url, 'post', json_encode($params), 60, false, $headers);

        $responseData = json_decode($response, true);
        if($responseData === null) {
           $responseData = $response;
        }

        return $responseData;
    }

    public static function deleteTokensByUserId($userId)
    {
        DB::delete(self::$tableTokens, '`user_id` = ' . to_sql($userId));
    }

    public static function isTokenUpdateRequired()
    {
        $isTokenUpdateRequired = true;

        $uid = guid();

        if(!$uid) {
            $isTokenUpdateRequired = false;
        } elseif(isset($_COOKIE['appPushToken'])) {
            $appPushToken = $_COOKIE['appPushToken'];
            if($appPushToken) {
                $appPushTokenData = json_decode($appPushToken, true);
                if($appPushTokenData) {
                    if(isset($appPushTokenData['userId']) && $appPushTokenData['userId'] == $uid && isset($appPushTokenData['token'])) {
                        $where = '`user_id` = ' . to_sql($uid) . ' AND `token` = ' . to_sql($appPushTokenData['token']);

                        $tokenDbInfo = DB::one(self::$tableTokens, $where);

                        if($tokenDbInfo) {
                            if($tokenDbInfo['last_update'] < (time() - 3600 * 24)) {
                                $row = array('last_update' => time());
                                DB::update(self::$tableTokens, $row, $where);
                            }
                            $isTokenUpdateRequired = false;
                        }
                    }
                }
            }
        }

        return var_export($isTokenUpdateRequired, true);
    }

}
