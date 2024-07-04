<?php

class ClubOption {

    static function getBgPath($field_name) {
        $result = '';
        DB::query("SELECT ". $field_name ." FROM club_option");
        $row = DB::fetch_row();
        if(sizeof($row) > 0) {
            $result = $row[$field_name];
        }
        return $result;
    }

    static function setBgPath($field_name, $newpath) {
        DB::execute("UPDATE club_option SET " . $field_name . " = " . to_sql($newpath, 'Text'));
    }

}

?>