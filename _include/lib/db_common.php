<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

define('DB_MAX_INDEX', 9);

global $g;

if (isset($g['db_profiling']) && $g['db_profiling']) {
    DB_Common::$DB_debug = true;
}

class DB_Common {

    static $fetchType = 'both';
    static $queriesCount = 0;
    static $DB_res = array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0);
    static $DB_conn = 0;
    static $DB_debug = false;
    static $DB_timer = 0;
    static $DB_sqls = '';
    static $DB_log_in_file = false;

    static function setFetchType($fetchType)
    {
        if (is_int($fetchType)) {
            self::$fetchType = $fetchType;
        } else {
            self::$fetchType = DB::$fetchTypes[$fetchType];
        }
    }

    static function getFetchType()
    {
        return self::$fetchType;
    }

    static function triggerError($msg = '', $executeQueryError = true)
    {
        if ($executeQueryError) {
            $msg = "Can't execute query: " . DB::error() . "\r\n" . $msg;
        }
        trigger_error($msg);
    }

    static function connect()
    {
        global $g;

        if (self::$DB_debug) {

            global $p;

            if((isset($g['db_log_in_file']) && $g['db_log_in_file']) || strpos($p, 'ajax') !== false || strpos($p, 'server') !== false
                || strpos($p, 'js.php') !== false || strpos($p, 'css.php') !== false) {
                self::$DB_log_in_file = true;
            }

            $DB_timer_cur = microtime(true);
        }

        self::setFetchType(DB::$fetchType);

        self::$DB_conn = DB::driver_connect($g['db']['host'], $g['db']['user'], $g['db']['password']);
        if (!self::$DB_conn) {
            self::triggerError(DB::getConnectError(), false);
            return false;
        }

        if (!self::select_db($g['db']['db'])) {
            return false;
        }

        $sql = 'SET SESSION `sql_mode` = "", NAMES "utf8", SESSION collation_connection = "utf8_unicode_ci", time_zone = "' . date('P') . '"';

        DB::execute($sql);

        if (self::$DB_debug) {

            if(isset($g['db_disable_cache']) && $g['db_disable_cache']) {
                $sql = 'SET SESSION `query_cache_type` = 0';
                DB::execute($sql);
            }

            $DB_timer_cur = microtime(true) - $DB_timer_cur;
            self::$DB_timer = self::$DB_timer + $DB_timer_cur;
            self::$DB_sqls .= 'Connection time: ' . self::$DB_timer . '<br /><br />';
        }

        return true;
    }

    static function close()
    {

        if (self::$DB_debug)
            $DB_timer_cur = microtime(true);

        if (self::$DB_conn) {
            DB::driver_close(self::$DB_conn);
            self::$DB_conn = 0;
        }

        if (self::$DB_debug) {

            global $g;

            if(isset($g['debug_page_start_time'])) {
                $pageTime = round(microtime(true) - $g['debug_page_start_time'], 4);
            }

            $DB_timer_cur = microtime(true) - $DB_timer_cur;
            self::$DB_timer = self::$DB_timer + $DB_timer_cur;
            self::$DB_sqls .= "Disconnection time: " . $DB_timer_cur . "<br />";
            self::$DB_sqls .= 'Total Queries: ' . self::$queriesCount . '<br />';
            self::$DB_sqls .= "Total time: " . self::$DB_timer . "<br />";

            $slowQueries = array();
            $queriesWithoutCache = array();

            $pattern = '#Sql query: (.*)\<br \/\>Sql execute time: (.*)\<br#Uis';
            preg_match_all($pattern, self::$DB_sqls, $matches);

            if(isset($matches[2]) && count($matches[2])) {
                foreach($matches[2] as $key => $time) {
                    if($time >= 0.001) {
                        self::$DB_sqls = str_replace("Sql execute time: $time", "<b>Sql slow execute time: $time</b>", self::$DB_sqls);
                        $slowQueries[] = array($matches[1][$key], $time);
                    } elseif($time >= 0.0001) {
                        $queriesWithoutCache[] = array($matches[1][$key], $time);
                    }
                }
            }

            if(isset($matches[1]) && count($matches[1])) {

                $result = array_count_values($matches[1]);
                arsort($result);

                $duplicates = 0;
                $counter = 1;

                $dupicatesList = '';
                $timeLostAll = 0;

                foreach ($result as $query => $count) {
                    if($count > 1) {
                        $timeKeys = array_keys($matches[1], $query);

                        $timeLost = 0;
                        $timeCounter = 0;
                        foreach($timeKeys as $timeKey) {
                            if($timeCounter) {
                                $timeLost += $matches[2][$timeKey];
                            }
                            $timeCounter++;
                        }
                        $timeLostAll += $timeLost;

                        $duplicates += $count - 1;
                        $dupicatesList .= $counter++ . ') ' . $count . '  Sql query: ' . $query . '<br>Sql execute time: ' . $timeLost . '<br><br>';
                    }
                }

                self::$DB_sqls .= "<br /><br /><b>Duplicates: " . $duplicates . "</b><br />";
                self::$DB_sqls .= "Sql execute time: " . $timeLostAll . "<br /><br />";
                self::$DB_sqls .= $dupicatesList;
            }

            self::prepareDebugQueriesList('Slow queries', $slowQueries);
            self::prepareDebugQueriesList('Probably queries without cache', $queriesWithoutCache);

            // Template specific styles

            $css = '<style>';

            $tmplName = Common::getTmplName();

            if($tmplName == 'urban') {
                $css .= 'html, body {height: auto; overflow: visible;} .main {position: relative;}';
            } elseif ($tmplName == 'oryx') {
                $css .= '.bg_fon {z-index: -1;}';
            } elseif($tmplName == 'urban_mobile') {
                $css .= 'body {overflow: visible;}
                .debug_page_statistics {
                    z-index: 1000;
                    position: relative;
                    margin-top: 100%;
                    float: left;
                }';
            } elseif($tmplName == 'impact_mobile') {
                $css .= 'html, body {overflow: visible;}
                    .fixFixed {
                        overflow: visible;
                        position: relative;
                    }
                    ';
            }

            $css .= '</style>';

            $log = '<div class="debug_page_statistics" style="text-align: left; color: #000; background-color: #FFF;">'
                . '<p><b>Total Queries: ' . self::$queriesCount . '<br />'
                . 'Total time: ' . self::$DB_timer . '<br />'
                . 'Page created in ' . $pageTime . '</b></p>'
                . self::$DB_sqls;

            if(isset($g['debug_page_start_time'])) {
                $log .= '<p><b>Page created in ' . $pageTime . ' seconds</b></p>';
            }

            $log .= '</div>';

            if(self::$DB_log_in_file) {
                global $p;
                $file = $g['path']['dir_logs'] . '/db_' . $p . '_' . microtime(true)
                    . '_' . round(self::$DB_timer, 3) . '_' . self::$queriesCount
                    . (isset($pageTime) ? '_' . $pageTime : '' ) . '.html';
                file_put_contents($file, $log);
            } else {
                echo $css . $log;
            }

        }
    }

    static function prepareDebugQueriesList($title, $queries)
    {
        $queriesCount = count($queries);

        if($queriesCount) {
            $counter = 1;

            $queriesList = '';

            $timeAll = 0;

            foreach($queries as $queryInfo) {
                $queriesList .= $counter++ . ') Sql query: ' . $queryInfo[0] . '<br>Sql execute time: ' . $queryInfo[1] . '<br><br>';
                $timeAll += $queryInfo[1];
            }

            self::$DB_sqls .= "<br /><b>$title: " . $queriesCount . "</b><br />";
            self::$DB_sqls .= "Sql execute time: " . $timeAll . "<br /><br />";
            self::$DB_sqls .= $queriesList;
        }
    }

    static function execute($sql, $error = true)
    {
        if (self::$DB_debug)
            $DB_timer_cur = microtime(true);

        if (!($r = DB::driver_query($sql)) and $error) {
            self::triggerError($sql);
        }

        if (self::$DB_debug) {
            self::$queriesCount++;
            $DB_timer_cur = microtime(true) - $DB_timer_cur;
            self::$DB_timer = self::$DB_timer + $DB_timer_cur;
            self::$DB_sqls .= self::$queriesCount . ") Sql query: " . $sql . "<br />";
            self::$DB_sqls .= "Sql execute time: " . $DB_timer_cur . "<br /><br />";
        }

        if ($r) {
            return 1;
        }
    }

    static function query($sql, $r = 0)
    {
        if (self::$DB_debug)
            $DB_timer_cur = microtime(true);

        if (self::$DB_res[$r]) {
            self::free_result($r);
            self::$DB_res[$r] = 0;
        }

        self::$DB_res[$r] = DB::driver_query($sql, self::$DB_conn) or self::triggerError($sql);

        if (self::$DB_debug) {
            self::$queriesCount++;
            $DB_timer_cur = microtime(true) - $DB_timer_cur;
            self::$DB_timer = self::$DB_timer + $DB_timer_cur;
            self::$DB_sqls .= self::$queriesCount . ") Sql query: " . $sql . "<br />";
            self::$DB_sqls .= "Sql execute time: " . $DB_timer_cur . "<br /><br />";
        }

        if (self::$DB_res[$r]) {
            return 1;
        } else {
            return 0;
        }
    }

    static function fetch_row($r = 0)
    {

        if (!self::$DB_res[$r]) {
            return null;
        }

        $ret = DB::driver_fetch_array(self::$DB_res[$r]);
        if (!$ret) {
            self::free_result($r);
            self::$DB_res[$r] = 0;
        }
        return $ret;
    }

    static function row($sql, $r = 0, $useCache = false)
    {
        static $cache = array();

        if ($useCache && (isset($cache[$sql]) || array_key_exists($sql, $cache))) {
            $return = $cache[$sql];
        } else {
            if (!DB::query($sql, $r)) {
                $return = null;
            } else {
                $return = DB::fetch_row($r);
            }
        }
        if ($useCache) {
            // add to local cache
            $cache[$sql] = $return;
        }
        return $return;
    }

    static function rows($sql, $r = 0, $useCache = false)
    {
        return DB::all($sql, $r, $useCache);
    }

    static function all($sql, $r = 0, $useCache = false)
    {
        static $cache = array();

        if ($useCache && (isset($cache[$sql]) || array_key_exists($sql, $cache))) {
            $all = $cache[$sql];
        } else {
            if (!DB::query($sql, $r)) {
                $all = null;
            } else {
                $all = array();
                while ($row = DB::fetch_row($r)) {
                    $all[] = $row;
                }
            }
        }

        if ($useCache) {
            $cache[$sql] = $all;
        }

        return $all;
    }

    static function count($table, $where = '', $order = '', $limit = '', $group = '', $r = DB_MAX_INDEX, $useCache = false)
    {
        $sql = 'SELECT COUNT(*) ';
        $sql .= 'FROM `' . $table . '` ';
        $sql .= ($where == '' ? '' : 'WHERE ' . $where . ' ');
        $sql .= ($order == '' ? '' : 'ORDER BY ' . $order . ' ');
        $sql .= ($group == '' ? '' : 'GROUP BY ' . $group . ' ');
        $sql .= ($limit == '' ? '' : 'LIMIT ' . $limit . ' ');
        return DB::result($sql, 0, $r, $useCache);
    }

    static function field($table, $field, $where = '', $order = '', $limit = '', $group = '', $r = DB_MAX_INDEX, $useCache = false)
    {
        $sql = 'SELECT `' . $field . '` ';
        $sql .= 'FROM `' . $table . '` ';
        $sql .= ($where == '' ? '' : 'WHERE ' . $where . ' ');
        $sql .= ($order == '' ? '' : 'ORDER BY ' . $order . ' ');
        $sql .= ($group == '' ? '' : 'GROUP BY ' . $group . ' ');
        $sql .= ($limit == '' ? '' : 'LIMIT ' . $limit . ' ');
        return DB::column($sql, $r, $useCache);
    }

    static function select($table, $where = '', $order = '', $limit = '', $fields = '*', $group = '', $r = DB_MAX_INDEX)
    {
        $sql = 'SELECT ' . (is_array($fields) ? '`' . implode('`,`', $fields) . '`' : $fields) . ' ';
        $sql .= 'FROM `' . $table . '` ';
        $sql .= ($where == '' ? '' : 'WHERE ' . $where . ' ');
        $sql .= ($order == '' ? '' : 'ORDER BY ' . $order . ' ');
        $sql .= ($group == '' ? '' : 'GROUP BY ' . $group . ' ');
        $sql .= ($limit == '' ? '' : 'LIMIT ' . $limit . ' ');
        return DB::all($sql, $r);
    }

    static function one($table, $where = '', $order = '', $fields = '*', $group = '', $r = DB_MAX_INDEX, $useCache = false)
    {
        $sql = 'SELECT ' . (is_array($fields) ? '`' . implode('`,`', $fields) . '`' : $fields) . ' ';
        $sql .= 'FROM `' . $table . '` ';
        $sql .= ($where == '' ? '' : 'WHERE ' . $where . ' ');
        $sql .= ($order == '' ? '' : 'ORDER BY ' . $order . ' ');
        $sql .= ($group == '' ? '' : 'GROUP BY ' . $group . ' ');
        $sql .= 'LIMIT 0,1';
        return DB::row($sql, $r, $useCache);
    }

    static function insert($table, $row)
    {
        $sql = 'INSERT INTO `' . to_sql($table, 'Plain') . '` SET ';
        $sqlArr = array();
        foreach ($row as $k => $v) {
            $sqlArr[] = '`' . $k . '`=' . DB::escape($v) . '';
        }
        $sql .= implode(', ', $sqlArr);
        return DB::execute($sql);
    }

    static function insertRows($table, $rows)
    {
        $keys = array();
        $isKeysCollected = false;
        $sqlArr = array();
        $i = 0;
        foreach ($rows as $row) {
            $sqlArr[$i] = '';
            foreach ($row as $k => $v) {
                if (!$isKeysCollected) {
                    $keys[] = '`' . to_sql($k, 'Plain') . '`';
                }
                if ($sqlArr[$i]) {
                    $sqlArr[$i] .= ',';
                }
                $sqlArr[$i] .= DB::escape($v);
            }
            $sqlArr[$i] = '(' . $sqlArr[$i] . ')';
            $isKeysCollected = true;
            $i++;
        }
        $keys = implode(', ', $keys);
        $sql = 'INSERT INTO `' . to_sql($table, 'Plain') . '` (' . $keys . ') VALUES ';
        $sql .= implode(', ', $sqlArr);
        return DB::execute($sql);
    }

    static function update($table, $row, $where = '', $order = '', $limit = '', $plain = false)
    {
        $sql = 'UPDATE `' . $table . '` SET ';
        $sqlArr = array();
        foreach ($row as $k => $v) {
            $sqlArr[] = '`' . $k . '`=' . ($plain ? to_sql($v, 'Plain') : DB::escape($v)) . '';
        }
        $sql .= implode(', ', $sqlArr) . ' ';
        $sql .= ($where == '' ? '' : 'WHERE ' . $where . ' ');
        $sql .= ($order == '' ? '' : 'ORDER BY ' . $order . ' ');
        $sql .= ($limit == '' ? '' : 'LIMIT ' . $limit . ' ');
        return DB::execute($sql);
    }

    static function delete($table, $where = '', $order = '', $limit = '')
    {
        $sql = 'DELETE FROM `' . $table . '` ';
        $sql .= ($where == '' ? '' : 'WHERE ' . $where . ' ');
        $sql .= ($order == '' ? '' : 'ORDER BY ' . $order . ' ');
        $sql .= ($limit == '' ? '' : 'LIMIT ' . $limit . ' ');
        return DB::execute($sql);
    }

    static function escape($v, $key = false)
    {
        if ($v instanceof DBNoEsc) {
            $value = $v->toString();
        } else {
            $value = "'" . DB::esc($v) . "'";
        }

        if ($key) {
            $value = '`' . $value . '`';
        }

        return $value;
    }

    static function esc_like($v)
    {
        return str_replace(array('_', '%'), array('\_', '\%'), DB::esc($v));
    }

    static function column($sql, $r = 0, $useCache = false)
    {
        static $cache = array();

        if ($useCache && (isset($cache[$sql]) || array_key_exists($sql, $cache))) {
            $all = $cache[$sql];
        } else {
            if (!DB::query($sql, $r))
                return null;

            $all = array();
            while ($row = DB::fetch_row($r)) {
                $all[] = $row[0];
            }
        }

        if ($useCache) {
            $cache[$sql] = $all;
        }

        return $all;
    }

    static function result($sql, $i = 0, $r = 0, $useCache = false)
    {
        static $cache = array();

        if ($useCache && isset($cache[$sql])) {
            $return = $cache[$sql];
        } else {
            $return = 0;
            if (DB::query($sql, $r)) {
                if ($row = DB::fetch_row($r)) {
                    $return = $row[$i];
                    self::free_result($r);
                    self::$DB_res[$r] = 0;
                }
            }
        }
        if ($useCache) {
            $cache[$sql] = $return;
        }

        return $return;
    }

    static function free_result($r = 0)
    {
        if (self::$DB_res[$r]) {
            DB::driver_free_result(self::$DB_res[$r]);
            self::$DB_res[$r] = 0;
        }
    }

    static function result_cache($name, $mins, $sql, $i = 0, $r = 0)
    {
        $cache = cache($name, $mins);
        if ($cache === false) {
            $ret = DB::result($sql, $i, $r);
            cache_update($name, $ret);
        } else
            $ret = $cache;
        return $ret;
    }

    static function db_options_ul($sql, $selected = "", $r = 0, $sort = false)
    {
        return db_options_ul($sql, $selected, $r, $sort);
    }

    static function db_options($sql, $selected = "", $r = 0, $sort = false, $isFirstValueEmpty = false, $name = '')
    {
        return db_options($sql, $selected, $r, $sort, $isFirstValueEmpty, $name);
    }

    static function show_table($sql, $table, $limit = 0, $from = 0, $r = 0)
    {
        $html = "";
        $first_string = 1;
        if ($from != 0)
            $from = "," . $from . "";
        if ($limit == 0)
            $limit = "";
        DB::query("SELECT * FROM " . $table . " LIMIT " . $limit . $from . "", $r);
        $html .= "<table border=1 cellpacing=5 cellpadding=5>";
        while ($row = DB::fetch_row($r)) {
            if ($first_string == 1) {
                $html .= "<tr>";
                foreach ($row as $k => $v) {
                    if (!is_int($k)) {
                        $html .= "<td>";
                        $html .= "<b>" . $k . "</b>";
                        $html .= "</td>";
                    }
                }
                $html .= "</tr>";
                $first_string = 0;
            }

            $html .= "<tr>";
            foreach ($row as $k => $v) {
                if (!is_int($k)) {
                    $html .= "<td>";
                    $html .= nl2br($v);
                    $html .= "</td>";
                }
            }
            $html .= "</tr>";
        }
        $html .= "</table>";
        return $html;
    }

    static function select_db($db)
    {
        if (!DB::driver_select_db($db)) {
            self::triggerError();
            return false;
        }

        return true;
    }

    static function num_rows($r = 0)
    {
        if (!self::$DB_res[$r]) {
            return 0;
        }

        return DB::driver_num_rows(self::$DB_res[$r]);
    }

}

class DBNoEsc {

    protected $_value = '';

    public function __construct($value)
    {
        $this->_value = $value;
    }

    public function __toString()
    {
        return $this->_value;
    }

    public function toString()
    {
        return $this->_value;
    }

}
