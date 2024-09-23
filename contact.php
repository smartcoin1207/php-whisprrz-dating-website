<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$ajax = isset($_POST['ajax']) ? $_POST['ajax'] : false;
if ($ajax ) {
   $g['mobile_redirect_off'] = true;
}
include("./_include/core/main_start.php");

if(!Common::isOptionActive('contact')) {
    Common::toHomePage();
}

Common::checkAreaLogin();

class CContact extends CHtmlBlock
{
    var $responseData = null;
	var $message;

	function action()
	{
		global $g, $g_user, $p;

		$cmd = get_param('cmd');
        $ajax = get_param('ajax');

		if ($cmd == 'send')
		{
			$mail = get_param('contact_email');
			$name = get_param('contact_username');
			$userId = 0;
            if (guid()) {
				$userId = $g_user['user_id'];
                $user = User::getInfoBasic($g_user['user_id']);
                $mail = $user['mail'];
                $name = $user['name'];
            }

			$this->message = '';
            $message = 0;

            if(!guid()) {
                $captcha = get_param('contact_captcha');
        
                // When Ajax mail is certainly true - it makes no sense to check
                if (!Common::validateEmail($mail) && (!$ajax || ($ajax && !guid()))) {
                    $this->message = l('the_e_mail_incorrect_please_choose_another');
                    if ($ajax) {
                        $this->responseData = '<span class="error">' . $this->message . '<span>';
                    }
                    $message = 1;
                } else if (!$ajax && !Securimage::check($captcha)) {
                    $message = 3;
                }
            }
            
			$comment = trim(strip_tags(get_param('contact_comment')));

            $name = strip_tags($name);
            $mail = strip_tags($mail);
            if (!$message && $comment !== '')
			{
				DB::execute("
					INSERT INTO contact
					(user_id, name, mail, comment, date)
					VALUES(
					" . to_sql($userId) . ",
					" . to_sql($name, "Text") . ",
					" . to_sql($mail, "Text") . ",
					" . to_sql($comment, "Text") . ",
					" . to_sql(date('Y-m-d H:i:s'), "Text") . "
					)
				");
                if (Common::isEnabledAutoMail('contact')) {
                    //Sent message copy to admin
                    $vars = array('title' => $g['main']['title'] . ' ' . $name,
                                  'name' => $name,
                                  'from' => $mail,
                                  'comment' => nl2br($comment),
                            );
                    Common::sendAutomail(Common::getOption('administration', 'lang_value'), $g['main']['info_mail'], 'contact', $vars);
                }
                $this->message = l('your_comment_has_been_sent');
                $message = 2;

                if ($ajax) {
                    $this->responseData = '<span>' . $this->message . '<span>';
                } else {
                    set_session('j_captcha', false);
                }
			}
            if (!$ajax) {
                redirect($p . '?message=' . $message);
            }
        } elseif ($cmd == 'check_captcha') {
            $responseData = check_captcha_mod(get_param('contact_captcha'), '', false, false, '', '');
            die(getResponseAjaxByAuth(true, $responseData));
        }
	}

	function parseBlock(&$html)
	{
        $msg = array('0' => '',
                     '1' => l('the_e_mail_incorrect_please_choose_another') . '<br/>',
                     '2' => l('your_comment_has_been_sent') . '<br/>',
                     '3' => l('incorrect_captcha') . '<br/>');

        $message = get_param('message', 0);

		$html->setvar('contact_message', $msg[$message]);
		$html->setvar('username', get_session('my_name'));

        if(Common::isOptionActive('news')) {
            $html->parse('news_on');
        }
        if(Common::isOptionActive('help')) {
            $html->parse('help_on');
        }

        if ($html->varExists('page_content')) {
            $pageId = CustomPage::getIdFromAlias('menu_bottom_contact_us');
            CustomPage::parsePage($html, $pageId);
        }

        $isParseCaptcha = true;
        if (guid() && Common::isOptionActive('captcha_contact_only_visitor', 'template_options')) {
            $isParseCaptcha = false;
        }
        if ($isParseCaptcha) {
            Common::parseCaptcha($html);
        }

        if (!guid()){
            $blocksVisitor = array('contact_email', 'contact_username', 'contact_captcha');
            foreach ($blocksVisitor as $block) {
                if ($html->blockExists($block)) {
                    $html->parse($block, false);
                }
            }
        }

		parent::parseBlock($html);
	}
}

$ajax = get_param('ajax');
if ($ajax) {
    $page = new CContact('', '', '', '', true);
    $page->action(false);
    die(getResponseDataAjax($page->responseData));
}

$page = new CContact("", $g['tmpl']['dir_tmpl_main'] . "contact.html");
$getPageAjax = get_param('get_page_ajax');
if ($getPageAjax) {
    $tmp = null;
    $return = $page->parse($tmp, true);
    die(getResponseDataAjax(trim($return)));
}

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

if (Common::isParseModule('menu_help')) {
    include("./_include/current/menu_section.class.php");
    $mailMenu = new CMenuSection('menu_help', $g['tmpl']['dir_tmpl_main'] . "_menu_help.html");
    $mailMenu->setActive('contact');
    $page->add($mailMenu);
}

$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");