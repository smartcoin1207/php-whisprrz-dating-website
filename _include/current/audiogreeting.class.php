<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class AudioGreeting {

    public static $table = 'audio_greeting';

    public static function save()
    {
        $isSaved = false;

        $uid = guid();
        $fileString = Common::getFileFromStringParam('audio');

        if($fileString && $uid) {

            $hash = md5($uid . microtime() . hash_generate(32));

            $file = self::createFilePath($uid, $hash);

            if(file_put_contents($file, $fileString) === strlen($fileString)) {

                self::delete($hash);

                $row = array(
                    'user_id' => $uid,
                    'hash' => $hash,
                );
                DB::insert(self::$table, $row);

                $isSaved = true;
            } else {
                if(custom_file_exists($file)) {
                    @unlink($file);
                }
            }
        }

        return $isSaved;
    }

    public static function delete($excludeHash = '')
    {
        $isDeleted = false;

        $uid = guid();

        if($uid) {
            $where = 'user_id = ' . to_sql($uid);
            if($excludeHash) {
                $where .= ' AND `hash` != ' . to_sql($excludeHash);
            }
            $rows = DB::select(self::$table, $where);
            foreach($rows as $row) {
                $file = self::createFilePath($uid, $row['hash']);
                if(custom_file_exists($file)) {
                    @unlink($file);
                }
                $where = 'user_id = ' . to_sql($uid) . ' AND `hash` = ' . to_sql($row['hash']);
                DB::delete(self::$table, $where);

                $isDeleted = true;
            }
        }

        return $isDeleted;
    }

    public static function createBasePath($uid, $hash)
    {
        return 'audio_greeting/' . $uid . '_' . $hash . '.wav';
    }

    public static function createFilePath($uid, $hash)
    {
        $filePath = Common::getOption('dir_files', 'path') . self::createBasePath($uid, $hash);
        return $filePath;
    }

    public static function getUrl($uid)
    {
        $url = '';

        $where = 'user_id = ' . to_sql($uid);
        $row = DB::one(self::$table, $where);
        if($row) {
            $url = Common::getOption('url_files', 'path') . self::createBasePath($uid, $row['hash']);
        }

        return $url;
    }

    public static function parseProfileSettings($html)
    {
        if(self::isActive()) {
            $block = 'audio_greeting_settings';
            if($html->blockExists($block)) {

                $parseDeleteButton = false;
                $parseRecordButton = false;

                if(!self::getUrl(guid())) {
                    $html->setvar($block . '_delete_button_class', 'hide');
                } else {
                    $parseDeleteButton = true;
                }

                if(Common::isAppIos()) {
                    $parseRecordButton = true;
                    $parseDeleteButton = true;
                }

                if($parseRecordButton) {
                    global $g_user;
                    $html->setvar('app_ios_auth_key', User::urlAddAutologin('', $g_user));
                    $html->parse($block . '_record');
                }

                if($parseDeleteButton) {
                    $html->parse($block . '_delete');
                }

                if($parseDeleteButton || $parseRecordButton) {
                    $html->parse($block);
                }
            }

        }
    }

    public static function isActive()
    {
        return Common::isOptionActive('audio_greeting') && Common::getAppIosApiVersion() >= 48;
    }

}