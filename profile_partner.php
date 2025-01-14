<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");

class CProfilePartner extends UserFields//CHtmlBlock
{
        var $sMessage = "";

	function action()
	{
            $cmd = get_param('cmd', '');
            if ($cmd == 'update') {

                // $this->verification('par_check');
                $this->updatePartner(guid());
            }
	}

	function parseBlock(&$html)
	{
            if (isset($this->message)) 
                $html->setvar('update_message', $this->message);
        
            $this->parseFieldsAll($html, 'partner');

            if (Common::isOptionActive('partner_settings', 'options') || Common::isOptionActive('personal_settings', 'options')) {
                if (Common::isOptionActive('partner_settings', 'options')) {
                    $html->parse('yes_partner');
                }
                if (Common::isOptionActive('personal_settings', 'options')) {
                    $html->parse('yes_personal');
                }
                $html->parse('yes_settings');
            }
            
            parent::parseBlock($html);
	}
}
$page = new CProfilePartner("", $g['tmpl']['dir_tmpl_main'] . "profile_partner.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$profile_menu = new CMenuSection("profile_menu", $g['tmpl']['dir_tmpl_main'] . "_profile_menu.html");
$profile_menu->setActive('profile');
$page->add($profile_menu);

$complite = new CComplite("complite", $g['tmpl']['dir_tmpl_main'] . "_complite.html");
$page->add($complite);

include("./_include/core/main_close.php");
?>

