<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class DB extends DB_Common {

    static $fetchTypes = array(
        'both' => MYSQL_BOTH,
        'assoc' => MYSQL_ASSOC,
        'numeric' => MYSQL_NUM,
    );

    static function driver_connect($host, $user, $pass)
    {
        return mysql_connect($host, $user, $pass);
    }

    static function driver_close()
    {
        mysql_close(self::$DB_conn);
    }

    static function driver_query($sql)
    {
        $result = mysql_query($sql, self::$DB_conn) or self::triggerError($sql);
        return $result;
    }

    static function driver_fetch_array($r)
    {
        return mysql_fetch_array($r, self::$fetchType);
    }

    static function esc($v)
    {
        return mysql_real_escape_string($v);
    }

    static function affected_rows()
    {
        return mysql_affected_rows(self::$DB_conn);
    }

    static function driver_num_rows($r)
    {
        return mysql_num_rows($r);
    }

    static function driver_free_result($r)
    {
        return mysql_free_result($r);
    }

    static function insert_id()
    {
        return mysql_insert_id(self::$DB_conn);
    }

    static function error()
    {
        return mysql_error();
    }

    static function driver_select_db($db)
    {
        return mysql_select_db($db);
    }

    static function getConnectError()
    {
        return self::error();
    }

}
