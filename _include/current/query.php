<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

if (!defined("_query_inc")) : ?>
<?php define("_query_inc",1); ?>
<?php

//include "dbconn.php";

define("DEBUG_MODE",true);



class query{
    var $query;

    function __construct($query=''){
        $this->query = $query;
    }
    function select($query=''){
        if ($query) $this->query = $query;

        return DB::rows($this->query);
    }
    function select1($query=''){
        return DB::result($query);
    }
    function insert($query=''){
        if ($query) $this->query = $query;
        DB::execute($this->query);
        return DB::insert_id();
    }
    function exec($query=''){
        if ($query) $this->query = $query;
        return DB::execute($this->query);
    }
    function execute($query=''){
      $this->exec($query);
    }
    function update($query=''){
        return $this->exec($query);
    }
    function delete($table, $id){
        $query = "delete from $table where id=$id";
        return $this->exec($query);
    }
    function clearstatus($table, $id=-1, $status=0){
        if ($id == -1){
            $query = "update $table set status=$status";
            return $this->exec($query);
        }else return $this->setstatus($table, $id, 0);
    }
    function setstatus($table, $id, $status=1){
        $query = "update $table set status=$status, created=created";
        $query .= " where id=$id";
        return $this->exec($query);
    }
    function setstatus1($table, $id, $status=1, $created=''){
        $query = "update $table set status=$status";
        if ($created) $query .= ", created=created";
        $query .= " where id=$id";
        return $this->exec($query);
    }
    function error($message){
      if(DEBUG_MODE){
        echo '<span style="font-weight: bold; color: red; font-family: Verdana;">MySQL: '.$this->query.'</span><br>';
        echo '<span style="font-weight: bold; color: red; font-family: Verdana;">MySQL: '.$message.'</span><br>';
      }
    }
}

?>
<?php endif;