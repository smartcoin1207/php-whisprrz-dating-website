<?php

// Rade 2023-09-28 add
class DonationOption {

    static function getBgPath($field_name) {
        $result = '';
        DB::query("SELECT ". $field_name ." FROM donation_option");
        $row = DB::fetch_row();
        if($row != null && sizeof($row) > 0) {
            $result = $row[$field_name];
        }
        return $result;
    }

    static function setBgPath($field_name, $newpath) {
        DB::execute("UPDATE donation_option SET " . $field_name . " = " . to_sql($newpath, 'Text'));
    }

}

?>