<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

class CRegisterConfirmEmail extends CHtmlBlock
{
	function action()
	{
		$hash = get_param("hash", "");
		if($hash)
		{
			//$user_id = DB::result("SELECT user_id FROM user WHERE active_code = " . to_sql($hash, "Text") .";");
            $user = DB::select('user', "`active_code` = " . to_sql($hash));
			if (!empty($user)) {
				DB::execute("UPDATE user SET active_code = '' WHERE user_id=" . $user[0]['user_id'] . "");
                DB::execute("UPDATE user SET last_ip=" . to_sql(IP::getIp()) . " WHERE user_id=" . $user[0]['user_id'] . "");
                if (Common::isOptionActive('manual_user_approval') && !$user[0]['active']) {
                        redirect('join.php?cmd=wait_approval');
                } else {
                    set_session('user_id', $user[0]['user_id']);
                    set_session('user_id_verify', $user[0]['user_id']);
                    $redirectWaitApproval = Common::getOption('redirect_wait_approval', 'template_options');
                    if ($redirectWaitApproval){
                        redirect($redirectWaitApproval . '?cmd=confirmed');
                    } else {
                        redirect('profile.php?cmd=confirmed');
                    }
                }
			} else {
				redirect('index.php');
			}
		}
		else
		{
			redirect('index.php');
		}
	}
	function parseBlock(&$html)
	{
		parent::parseBlock($html);
	}
}

$page = new CRegisterConfirmEmail("", $g['tmpl']['dir_tmpl_main'] . "index.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>