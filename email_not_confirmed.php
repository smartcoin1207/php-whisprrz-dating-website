<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

$g['to_head'][] = '<link rel="stylesheet" href="'.$g['tmpl']['url_tmpl_main'].'css/main.css'.$g['site_cache']["cache_version_param"].'" type="text/css" media="all"/>';
$g['options']['no_user_menu'] = 'Y';

if ($g_user['user_id'] && !$g_user['active_code']){
    Common::toHomePage();
}

class CRegisterEmailConfirmation extends CHtmlBlock
{
	function action()
	{
		global $g;
		global $g_info;
		global $g_user;
		global $l;
		global $gc;

		$mail = get_param('email', null);
		$this->message = '';
		if ($mail!==null) {
            $br = "<br/>";
            if (Common::isOptionActiveTemplate('no_br_error')) {
                $br = "";
            }
			if (!Common::validateEmail($mail)) {
				$this->message .= l('incorrect_email') . $br;
			} else {
                $user_with_same_email = DB::result("SELECT user_id FROM user WHERE mail=" . to_sql($mail, "Text") . ";");
				if ($user_with_same_email != "" && $user_with_same_email != $g_user['user_id'])
				{
					$this->message .= l('exists_email') . $br;
				}
				/*$user_with_same_email = DB::result("SELECT user_id FROM user WHERE mail=" . to_sql($mail, "Text") . ";");
                if (!$user_with_same_email) {
                    $this->message .= l('E-mail is not registered in our system.') . "<br/>";
                }elseif ($user_with_same_email != $g_user['user_id']) {
					$this->message .= l('exists_email') . "<br/>";
				}*/
			}
			
			if(!$this->message) {
				user_change_email($g_user['user_id'], $mail);
			}
		}
	}

	function parseBlock(&$html)
	{
		$mail = get_param("email", "");
		$html->setvar("email", $mail);

		if ($this->message) {
			$html->setvar("message", $this->message);
		}

		if (!$this->message && $mail) {
			$html->parse("email_sent_title", true);
        } else {
			$html->parse("default_title", true);
        }

		parent::parseBlock($html);
	}
}

$page = new CRegisterEmailConfirmation("", $g['tmpl']['dir_tmpl_main'] . "email_not_confirmed.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");
