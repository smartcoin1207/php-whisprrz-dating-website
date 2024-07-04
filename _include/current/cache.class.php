<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class Cache {

    static private $cache = array();

    static function add($key, $value)
    {
        self::$cache[$key] = $value;
    }

    static function get($key)
    {
        return isset(self::$cache[$key]) ? self::$cache[$key] : null;
    }

    static function getCache()
    {
        return self::$cache;
    }

    static function delete($key)
    {
        if(isset(self::$cache[$key])) {
            unset(self::$cache[$key]);
        }
    }

    static function reset()
    {
        self::$cache = array();
    }

}