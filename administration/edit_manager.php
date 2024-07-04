<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CEditManager extends CHtmlBlock
{


	function action()
	{
		global $g;
		global $l;
		$cmd = get_param("cmd", "");
		$cmd_ajax = get_param("cmd_ajax", "");
        $m_user_id = get_param('m_user_id','');

		if ($cmd == "logout")
		{
			set_session('replier_auth', '');
			set_session('replier_id', '');
			set_session('admin_auth', '');
            set_session('admin_last_login', false);
			redirect("index.php");
		}
		elseif ($cmd == "edit_manager")
		{
			$password = get_param("password", "");
			$name = get_param("name", "");
			$Frameworks = get_param("Frameworks", "");
			$Languages = get_param("Languages", "");
			$Site_news = get_param("Site_news", "");
			$Users = get_param("Users", "");
			$Modules = get_param("Modules", "");
			$Advertise = get_param("Advertise", "");
			$Media = get_param("Media", "");
			$SMS_TEXT = get_param("SMS_TEXT", "");
			$Options = get_param("Options", "");
			$Statistics = get_param("Statistics", "");
			$Payment = get_param("Payment", "");
			$Donation    = get_param("Donation", "");
			$New_Page = get_param("New_Page", "");
			$New_Menu = get_param("New_Menu", "");
			
            $sql = "UPDATE add_manager SET         
                        `name`  = '$name',
                        `password` = '$password',
                        `Frameworks`= '$Frameworks',
                        `Languages` = '$Languages',
                        `Site_news` = '$Site_news',
                        `Users` = '$Users',
                        `Modules` = '$Modules',
                        `Advertise` = '$Advertise',
                        `Media` = '$Media',  
                        `SMS_TEXT` = '$SMS_TEXT',
                        `Options` = '$Options', 
                        `Statistics` = '$Statistics',
                        `Payment` = '$Payment',
                        `Donation` = '$Donation',
                        `New_Page` = '$New_Page', 
                        `New_Menu` = '$New_Menu'
                        WHERE id  = '" . $m_user_id . "'
                        ";


            DB::execute($sql);
		}
	}

	function parseBlock(&$html)
	{
        $cmd_ajax = get_param("cmd_ajax", "");

        $fields = array(
             "Frameworks" => l('menu_templates'), "Languages" => l('menu_languages'), "Site_news" => l('menu_help_and_news'), "Users" => l('mmenu_users'), "Modules" => l('menu_modules'), "Advertise" => l('menu_advertise'), "Media" => l('menu_media'), 
            "SMS_TEXT" => l('menu_sms_text'), "Options" => l('menu_city_options'), "Statistics" => l('menu_stats'), "Payment" => l("menu_payment"), "Donation" => l('menu_donation'), "New_Page" => l('menu_club_add'),
            "New_Menu" => l('menu_new_add')
        );

        $m_user_id = get_param('m_user_id','');


        $sql_1 = "SELECT * FROM add_manager WHERE id = '" . $m_user_id . "'";
        DB::query($sql_1);
        $m_edit_row = DB::fetch_row();

        $html->setvar('m_username', $m_edit_row['name']);
        $html->setvar('m_password', $m_edit_row['password']);
        $html->setvar('m_user_id', $m_user_id);
        
        
        foreach ($fields as $key => $field) {
            $html->setvar('checkbox_name', $key);
            $html->setvar('checkbox_label', $field);
            $m_checked = "";
            if($m_edit_row[$key] == '1') {
                $m_checked = "checked";
            }
            $html->setvar('m_checked', $m_checked);
            $html->parse('manage_checkbox_item', true);
        }
        $html->parse('manage_checkbox', true);

        $sql = 'SELECT COUNT(*) FROM admin_login WHERE
           success="N"
            AND  time >  DATE_SUB(NOW(), INTERVAL 10 MINUTE)
            AND ip = ' . to_sql(IP::getIp(), 'Text') .'';
        $count = DB::result($sql);
        if($count >= 5 && $cmd_ajax) {
            $html->parse('admin_page_auth_time_error');
        }
        elseif ($cmd_ajax && (get_session("admin_auth") == "Y" || get_session("replier_auth") == "Y")){
            $html->parse("admin_page_auth");
        }
        elseif ($cmd_ajax) {
            $html->setvar("prevent_cache",time().rand(0,1000));
            $html->parse("admin_page_auth_error");
        } else {
            if(IS_DEMO) {
                $html->parse('demo');
            }
            $html->parse("admin_page");
        }

      
        // $html->clean('manage_checkbox_item');

        parent::parseBlock($html);
	}
}

$page = new CEditManager("", $g['tmpl']['dir_tmpl_administration'] . "edit_manager.html");

$cmd_ajax = get_param("cmd_ajax", "");

if(!$cmd_ajax) {
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);
}

include("../_include/core/administration_close.php");

?>
