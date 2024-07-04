<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

// Rade 2023-09-24
include("../_include/core/administration_start.php");

class CAddManager extends CHtmlBlock
{
	function action()
	{
		global $g;
		global $l;
		$cmd = get_param("cmd", "");
		$cmd_ajax = get_param("cmd_ajax", "");

		if ($cmd == "logout")
		{
			set_session('replier_auth', '');
			set_session('replier_id', '');
			set_session('admin_auth', '');
            set_session('admin_last_login', false);
			redirect("index.php");
		}
		
		elseif ($cmd == "add_manager" && get_param("name", "") != "admin")
		{
			$password = get_param("password", "");
			$name = get_param("name", "");
			$Main = get_param("Main", "");
			$Frameworks = get_param("Frameworks", "");
			$Languages = get_param("Languages", "");
			$Site_news = get_param("Site_news", "");
			$Users = get_param("Users", "");
			$Modules = get_param("Modules", "");
			$Advertise = get_param("Advertise", "");
			$Media = get_param("Media", "");
			$SMS_TEXT = get_param("SMS_TEXT", "");
			$Home = get_param("Home", "");
			$Options = get_param("Options", "");
			$Statistics = get_param("Statistics", "");
			$Payment = get_param("Payment", "");
			$Donation = get_param("Donation", "");
			$New_Page = get_param("New_Page", "");
			$New_Menu = get_param("New_Menu", "");
			
			$exist_name = DB::result("SELECT * FROM add_manager WHERE name = '".$name."'");
			if($exist_name) {
			    redirect("add_manager.php");
			}
			
            $sql = "INSERT INTO add_manager (        
                        `name`,
                        `password`,
                        `Frameworks`,
                        `Languages`,
                        `Site_news`,
                        `Users`,
                        `Modules`,
                        `Advertise`,
                        `Media`,
                        `SMS_TEXT`,
                        `Options`,
                        `Statistics`,
                        `Payment`,
                        `Donation`,
                        `New_Page`,
                        `New_Menu`
                        ) 
                        VALUES (        
                            '$name',
                            '$password',     
                            '$Frameworks',
                            '$Languages',
                            '$Site_news',     
                            '$Users',
                            '$Modules',
                            '$Advertise',
                            '$Media',     
                            '$SMS_TEXT',
                            '$Options',     
                            '$Statistics',
                            '$Payment',
                            '$Donation',
                            '$New_Page',     
                            '$New_Menu'
                    )";
                
            DB::execute($sql);

		}
	}

	function parseBlock(&$html)
	{
        $cmd_ajax = get_param("cmd_ajax", "");
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

        parent::parseBlock($html);
	}
}

$page = new CAddManager("", $g['tmpl']['dir_tmpl_administration'] . "add_manager.html");

$cmd_ajax = get_param("cmd_ajax", "");

if(!$cmd_ajax) {
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);
}

include("../_include/core/administration_close.php");

?>
