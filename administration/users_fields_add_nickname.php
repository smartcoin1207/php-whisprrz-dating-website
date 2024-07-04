<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

// popcorn created at 8/20/2023
// Rade 2023-09-23
include("../_include/core/administration_start.php");

class CAdminFields extends CHtmlBlock {

    function write_to_users($option, $value, $position = 'max')
    {
        $position = Config::add('user_var', $option, '', $position);
        //$value['id'] = DB::insert_id();
        $value = serialize($value);
        Config::update('user_var', $option, $value);

        return $position;
    }

    function action()
    {
        global $g;
        global $p;
        $cmd = get_param("cmd", "");
        $table = get_param("table", "");
        #print_r($_POST);
        $name = trim(get_param("name", ""));

        $name_sql = to_sql(preg_replace('/ {1,}/u', '_', trim(preg_replace("/[^a-z0-9\s]/u", " ", mb_strtolower($name, 'UTF-8')))), "Plain");

        if ($cmd != "") {
            $fieldsExists = array('user_id', 'user_editor_xml', 'user_search_filters', 'values');
            if (isset($g['user_var'][$name]) || isset($g['user_var'][$name_sql]) || in_array($name_sql, $fieldsExists)){
                return false;
            }
        }

        if($cmd == "nickname_add") {
            $nicknames = get_param_array('nickname');

			foreach ($nicknames as $k => $v)
			{
				if ($k != -1 && $v != "")
				{
					DB::execute("UPDATE var_nickname SET `title`=" . to_sql($v) . " WHERE id=" . to_sql($k));
				}
			}
            $nicknames1 = get_param_array('nickname1');

			foreach ($nicknames1 as $k => $v)
			{
				if ($k != -1 && $v != "")
				{
                    DB::execute("INSERT INTO var_nickname SET title=" . to_sql($v, "Text") . "");

				}
			}
            redirect();

		} else {

            $name = get_param("name", "");
            $group = doubleval(get_param("group", 3));

            if ($name != "") {

                if ($name_sql == '') {
                    redirect();
                }
                $position = 'max';
                $isInt = true;
                if (DB::execute("CREATE TABLE var_" . to_sql($name_sql, "Plain") . " (
                                     id int(11) NOT NULL AUTO_INCREMENT ,
                                  title varchar(255) NOT NULL default '',
                                PRIMARY KEY (id)) ENGINE = MYISAM;"))
                {
                    DB::execute("ALTER TABLE userinfo ADD " . to_sql($name_sql, "Plain") . " INT(11) NOT NULL;");
                    $w = "\$g['user_var']['" . to_sql($name_sql, "Plain") . "'] = array(";
                    $w .= "\"from_table\", \"int\", \"var_" . to_sql($name_sql, "Plain") . "\", \"" . $name . "\", \"" . $group . "\"";
                    $w .= ");";


                    if ($cmd == "select_and_partner_checks") {
                        $w .= "\r\n\$g['user_var']['p_" . to_sql($name_sql, "Plain") . "'] = array(";
                        $w .= "\"from_table\", \"checks\", \"var_" . to_sql($name_sql, "Plain") . "\", \"" . $name . "\", \"2\"";
                        $w .= ");";


                        $value = array('from_table', 'checks', 'var_' . to_sql($name_sql, 'Plain'), $name, $group, 'status' => 'active',
                            'type' => 'checks', 'table'=> 'var_' . to_sql($name_sql, 'Plain'), 'title'=>$name, 'group'=> $group, 'number_values' => 0);
                        $position = $this->write_to_users('p_' . $name_sql, $value);

                        DB::execute("ALTER TABLE userpartner ADD p_" . to_sql($name_sql, "Plain") . " BIGINT(22) UNSIGNED NOT NULL;");
                    } elseif ($cmd == "checkbox" || $cmd == "radio") {
                        $isInt = false;
                        $value = array('type'          => 'checkbox',
                                       'table'         => 'var_' . to_sql($name_sql, 'Plain'),
                                       'title'         => $name,
                                       'status'        => 'active',
                                       'group'         => 1,
                                       'number_values' => 0);
                        $this->write_to_users($name_sql, $value);

                    } elseif ($cmd == "select_and_partner_interval") {
                        $w .= "\r\n\$g['user_var']['p_" . to_sql($name_sql, "Plain") . "_from'] = array(";
                        $w .= "\"from_table\", \"int\", \"var_" . to_sql($name_sql, "Plain") . "\", \"" . $name . "\", \"" . $name . " from\", \"from\", \"2\"";
                        $w .= ");\r\n";



                        $w .= "\$g['user_var']['p_" . to_sql($name_sql, "Plain") . "_to'] = array(";
                        $w .= "\"from_table\", \"int\", \"var_" . to_sql($name_sql, "Plain") . "\", \"" . $name . "\", \"" . $name . " to\", \"to\", \"2\"";
                        $w .= ");";
                        DB::execute("ALTER TABLE userpartner ADD p_" . to_sql($name_sql, "Plain") . "_from INT(11) NOT NULL;");


                        DB::execute("ALTER TABLE userpartner ADD p_" . to_sql($name_sql, "Plain") . "_to INT(11) NOT NULL;");


                        $value = array('from_table', 'int', 'var_' . to_sql($name_sql, 'Plain'), $name, $name . ' from', 'from', $group, 'status' => 'active',
                            'type' => 'from', 'table'=> 'var_' . to_sql($name_sql, 'Plain'), 'title'=>$name, 'group'=> $group, 'number_values' => 0);
                        $this->write_to_users('p_' . $name_sql . '_from', $value);

                        $value = array('from_table', 'int', 'var_' . to_sql($name_sql, 'Plain'), $name, $name . ' to', 'to', $group, 'status' => 'active',
                            'type' => 'to', 'table'=> 'var_' . to_sql($name_sql, 'Plain'), 'title'=>$name, 'group'=> $group, 'number_values' => 0);
                        $position = $this->write_to_users('p_' . $name_sql . '_to', $value);
                    }

                    if ($isInt) {
                        $value = array('from_table',
                                       'int',
                                       'var_' . to_sql($name_sql, 'Plain'),
                                       $name,
                                       $group,
                                       'status' => 'active',
                                       'type'   => 'int',
                                       'table'  => 'var_' . to_sql($name_sql, 'Plain'),
                                       'title'  => $name,
                                       'group'  => $group,
                                       'number_values' => 0);
                        $this->write_to_users($name_sql, $value, $position);
                    }

					if($name_sql == 'sexuality' && $cmd == "select_and_partner_checks"){
						DB::execute("ALTER TABLE  var_" . to_sql($name_sql, 'Plain') . " ADD `default` tinyint(1) NOT NULL DEFAULT '0'");
					}

                    redirect('users_fields_add.php?table=' . $name_sql . '&fields=' . $name_sql . '&action=saved#' . $name_sql);
                }
            }
        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $p;


        $sql = "SELECT * FROM var_nickname";
        $rows = DB::rows($sql);
        if($rows){
            foreach ($rows as $key => $row) {
                $html->setvar('nickname_fields_name', 'nickname[' . $row['id'] . ']');
                $html->setvar('nickname_fields_value', $row['title']);
                $html->parse('nickname_fields_item', true);
            }
        }

        for ($i=0; $i<5  ; $i++) { 
            $html->setvar('nickname_fields_add_name', 'nickname1[' . $i . ']');
            $html->parse('add', true);
        }

        parent::parseBlock($html);
    }

}

$page = new CAdminFields("", $g['tmpl']['dir_tmpl_administration'] . "users_fields_add_nickname.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuUsersFields());

include("../_include/core/administration_close.php");
?>
