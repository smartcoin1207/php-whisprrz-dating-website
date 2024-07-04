<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

// Learning Management System
Class LMS {

    public static $userTypes = null;
    private static $tableUserTypes = 'const_lms_user_type';

    public static function getTableUserTypes()
    {
        return to_sql(self::$tableUserTypes, 'Plain');
    }

    public static function joinPrepareProfileTypes(&$vars, $isIos)
    {
        $vars['orientation_options'] = '';

        $vars['orientation_class'] = 'orientation_bl';
        $default = 0;
        $selectedOrientation = 0;
        if (!$isIos) {
            $default = self::getDefaultUserType();
            $selectedOrientation = get_param("orientation", $default);
        }
        $vars['orientation_options'] = '';
        if (!$default){
            $lPleaseChoose = l('i_am');
            $vars['orientation_options'] = '<option value="0" selected="selected">' . $lPleaseChoose . '</option>';
        }
        $vars['orientation_options'] .= DB::db_options("SELECT id, title FROM " . self::getTableUserTypes() . " ORDER BY id ASC", $selectedOrientation);
    }

    public static function getUserTypeInfo($userType, $dbIndex = DB_MAX_INDEX)
    {
        if(self::$userTypes === null) {
            $userTypesInfo = DB::select(self::getTableUserTypes());
            if($userTypesInfo) {
                foreach ($userTypesInfo as $userTypeInfo) {
                    self::$userTypes[$userTypeInfo['id']] = $userTypeInfo;
                }
            }
        }
        return isset(self::$userTypes[$userType]) ? self::$userTypes[$userType] : null;
    }

    public static function getDefaultUserType()
    {
		$defaultUserType = DB::result('SELECT `id` FROM ' . self::getTableUserTypes() . ' ORDER BY `default` DESC, `id` ASC LIMIT 1');
		return $defaultUserType;
    }

    public static function getTeacherTypeIds()
    {
        $teacherTypeIds = 0;

        $teacherTypeIdsList = DB::field(self::getTableUserTypes(), 'id', '`type` = "teacher"');
        if($teacherTypeIdsList) {
            $teacherTypeIds = implode(',', $teacherTypeIdsList);
        }

        return $teacherTypeIds;
    }

}