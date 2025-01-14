<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CForgot extends CHtmlBlock
{
	var $message = '';
    var $sent = false;
    var $redirect = false;
    var $ajax = false;

	function action($redirect = false)
	{
        global $p;


        $this->ajax = get_param('ajax');

        $login = trim(get_param('login' , false));
        if($login){
            $sql = 'SELECT * FROM user WHERE password_reminder = ' . to_sql($login);
            $user=DB::row($sql);

            if($user){
                $keyMessage = '';
                if($user['ban_global']==1){
                    $keyMessage = 'account_has_been_banned';
                    $this->message = l($keyMessage);
                    $this->redirect= 'index.php';
                }
                if (Common::isOptionActive('manual_user_approval') && !$user['active']) {
                    $keyMessage = 'no_confirmation_account';
                    $this->message = l($keyMessage);
                    $this->redirect= 'index.php';
                }
                if($this->message==''){
                    User::forgotPasswordChange($user);
                    $this->redirect= Common::getHomePage();
                    $keyMessage = 'the_password_has_been_sent';
                    if($this->ajax){
                        $this->message = 'send';
                    } else {
                        $this->message = l($keyMessage);
                    }
                    if(Common::getOptionSetTmpl() == 'old'){
                        if(Common::isMobile()){
                            redirect('index.php?cmd=sent');
                        } else {
                            redirect('join.php?cmd=sent');
                        }
                    }
                    $this->redirect='join.php?cmd=sent';
                }
                if ($keyMessage
                        && Common::isOptionActive('forgot_password_redirect_login', 'template_options')) {
                    set_session('alert_msg', $keyMessage);
                    redirect(Common::pageUrl('login'));
                }
            } else {
                redirect(Common::toHomePage());
            }

        }

		$mail = trim(get_param('mail' , ''));

		if ($mail != '') {
            $mail = str_replace(' ', '+', $mail);
            $sql = 'SELECT * FROM user WHERE mail = ' . to_sql($mail);
			DB::query($sql);
			if ($user = DB::fetch_row()) {

                User::forgotPassword($user);

                if($redirect) {
                    redirect('join.php?cmd=sent');
                } else {
                    if($this->ajax){
                        $this->message = 'link_send';
                    } else {
                        $this->message = l('the_link_for_changing_password_has_been_sent');
                    }
                }
			} else {
				$this->message = l('this_email_is_not_registered_in_our_system');
			}
		}
	}

	function parseBlock(&$html)
	{
		$html->setvar("message", $this->message);
		$html->setvar("error_message", $this->message);
        $html->setvar("redirect", $this->redirect);

        if($this->message) {
            $html->parse('alert');
            $html->parse('error');
        }

        $urlFrom = get_param('url_from', Common::refererFromSite());
		$html->setvar('url_from', $urlFrom);

        if ($html->varExists('link_to_login')) {
            $vars = array('url' => Common::pageUrl('index'));
            $attr = array('class' => 'go_to_page no_color_t',
                          'data-cl-loader' => 'loader_link_forgot_page');
            $html->setvar('link_to_login', Common::lSetLink('link_to_login', $vars, false, '', $attr));
        }

		parent::parseBlock($html);
	}
}
