<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class Table {

    protected $table = 'table';
    protected $item = null;
    protected $indexField = 'id';
    protected $error = false;
    public $debug = false;

    private function setItem($item)
    {
        $this->item = $item;
    }

    public function getIndexField()
    {
        return to_sql($this->indexField, 'Plain');
    }

    public function isLoaded()
    {
        return $this->item;
    }

    public function getTable()
    {
        return to_sql($this->table, 'Plain');
    }

    public function info($id)
    {
        $sql = 'SELECT * FROM ' . $this->getTable() . '
            WHERE ' . $this->getIndexField() . ' = ' . to_sql($id);
        return DB::row($sql);
    }

    public function __construct($id = null)
    {
        if($id) {
            $this->load($id);
        }
    }

    public function load($id)
    {
        $this->setItem($this->info($id));
    }

    public function id()
    {
        return $this->getItem($this->indexField);
    }

    public function getItem($key)
    {
        return isset($this->item[$key]) ? $this->item[$key] : null;
    }

    public function add($row, $table = false)
    {
        DB::insert($table ? $table : $this->getTable(), $row);
        return DB::insert_id();
    }

    public function update($row, $where = false)
    {
        return DB::update($this->getTable(), $row, $where !== false ? $where : $this->getIndexField() . ' = ' . to_sql($this->id()));
    }

    public function delete($id)
    {
        return DB::delete($this->getTable(), $this->getIndexField() . ' = ' . to_sql($id));
    }

    public function getError()
    {
        return $this->error;
    }

    public function count($where)
    {
        return DB::count($this->getTable(), $where);
    }

    public function select($where, $limit = '', $order = '')
    {
        return DB::select($this->getTable(), $where, $order, $limit);
    }

    public function logger($msg = '', $value = '')
    {
        if($this->debug) {
            $msg = "<b>$msg</b>";
            if($value) {
                $msg .= ": $value";
            }
            echo $msg . '<br>';
        }
    }

}
