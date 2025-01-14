<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class DB extends DB_Common {

    static $fetchTypes = array(
        'both' => MYSQLI_BOTH,
        'assoc' => MYSQLI_ASSOC,
        'numeric' => MYSQLI_NUM,
    );

    static function driver_connect($host, $user, $pass)
    {
        return mysqli_connect($host, $user, $pass);
    }

    static function driver_close()
    {
        mysqli_close(self::$DB_conn);
    }

    static function driver_query($sql)
    {
        $result = mysqli_query(self::$DB_conn, $sql) or self::triggerError($sql);
        return $result;
    }

    static function driver_fetch_array($r)
    {
        return mysqli_fetch_array($r, self::$fetchType);
    }

    static function esc($v)
    {
        return mysqli_real_escape_string(self::$DB_conn, $v);
    }

    static function affected_rows()
    {
        return mysqli_affected_rows(self::$DB_conn);
    }

    static function driver_num_rows($r)
    {
        return mysqli_num_rows($r);
    }

    static function driver_free_result($r)
    {
        return mysqli_free_result($r);
    }

    static function insert_id()
    {
        return mysqli_insert_id(self::$DB_conn);
    }

    static function error()
    {
        return mysqli_error(self::$DB_conn);
    }

    static function driver_select_db($db)
    {
        return mysqli_select_db(self::$DB_conn, $db);
    }

    static function getConnectError()
    {
        return mysqli_connect_error();
    }

}
