<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");

class CProfilePersonal extends  UserFields//CHtmlBlock
{
	function action()
	{
		global $g_user;
		$cmd = get_param('cmd', '');
		if ($cmd == 'update')
		{
			$this->message = '';
			$this->updateInfo($g_user['nsc_couple_id'], 'personal',false, $g_user['nsc_couple_id']);
            // Проверка селектов пустая $this->verification('personal');
			if ($this->message == '')
			{
                $fieldStatus = intval(get_param('status'));
                if($fieldStatus && guser('status') != 0 && guser('status') != $fieldStatus) {
                    $sql = 'SELECT title FROM var_status
                        WHERE id = ' . to_sql($fieldStatus, 'Number');
                    $fieldStatusValue = DB::result($sql);
                    Wall::add('field_status', 0, false, $fieldStatusValue);
                }

				/*DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id=" . guid());
				$g_user = DB::fetch_row();*/
				//g_user_full();
			}
		}
	}
	function parseBlock(&$html)
	{
		global $g_user;
		if (isset($this->message)) 
            $html->setvar('update_message', $this->message);
        if (Common::isOptionActive('partner_settings', 'options') || Common::isOptionActive('personal_settings', 'options')) {
            if (Common::isOptionActive('partner_settings', 'options')) {
                $html->parse('yes_partner');
            }
            if (Common::isOptionActive('personal_settings', 'options')) {
                $html->parse('yes_personal');
            }
            $html->parse('yes_settings');
        }
		$this->parseFieldsAll($html, 'personal', false, $g_user['nsc_couple_id']);
        
		parent::parseBlock($html);
	}
}

//g_user_full();
$page = new CProfilePersonal("", $g['tmpl']['dir_tmpl_main'] . "profile_personal_nsc_couple.html");
$page->setUser(guid());
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$profile_menu = new CMenuSection("profile_menu", $g['tmpl']['dir_tmpl_main'] . "_profile_menu.html");
$profile_menu->setActive('profile_nsc_couple');
$page->add($profile_menu);

$complite = new CComplite("complite", $g['tmpl']['dir_tmpl_main'] . "_complite.html");
$page->add($complite);

include("./_include/core/main_close.php");
?>

