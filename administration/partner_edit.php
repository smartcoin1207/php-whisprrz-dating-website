<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CForm extends CHtmlBlock
{

	var $message = "";
	var $login = "";

	function action()
	{
		global $g_options;
		$cmd = get_param("cmd", "");

		if ($cmd == "delete")
		{
			DB::execute("
				DELETE FROM partner WHERE
				partner_id=" . to_sql(get_param("id", ""), "Number") . "
			");
		}
	}

	function init()
	{
		$cmd = get_param("cmd", "");

		if ($cmd == "edit")
		{
			$pass = trim(get_param("join_password", ""));
			$pass2 = trim(get_param("verify_password", ""));
			$mail = trim(get_param("email", ""));

			$this->message = "";

			if (!Common::validateEmail($mail)) {
				$this->message .= "The E-mail incorrect.<br>Please choose another.<br>";
			}

			if ($pass && ($pass != $pass2 || User::validatePassword($pass)))
			{
				$this->message .= "The Password incorrect.<br>Please choose another.<br>";
			}

			$contact_name = to_sql(get_param("contact_name", ""), "Text");
			$phone = to_sql(get_param("phone", ""), "Text");
			$checkPayee = to_sql(get_param("checkPayee", ""), "Text");
			$addr1 = to_sql(get_param("addr1", ""), "Text");
			$addr2 = to_sql(get_param("addr2", ""), "Text");
			$zip = to_sql(get_param("zip", ""), "Text");
            $tax = to_sql(get_param("tax", ""), "Text");

			$company_name = to_sql(get_param("name", ""), "Text");
			$referring_domains = to_sql(get_param("referring_domains", ""), "Text");

			$real_email = DB::result("SELECT mail FROM partner WHERE partner_id=" .  to_sql(get_param("id", ""), "Number"));


			if ( $mail!=$real_email && DB::result("SELECT partner_id FROM partner WHERE mail=" . to_sql($mail) . ";") != 0)
			{
				$this->message .= "The E-mail you entered already exists on our system.<br>Please enter another.<br>";
			}

			if ($this->message == "")
			{
				$country = to_sql(get_param("country", ""), "Number");
				$state = to_sql(get_param("state", ""), "Number");
				$city = to_sql(get_param("city", ""), "Number");

                if($pass) {
                    $setPassword = '`password` = ' . to_sql(User::preparePasswordForDatabase($pass)) . ',';
                } else {
                    $setPassword = '';
                }

				DB::execute("
					UPDATE partner
					SET
					" . $setPassword . "
					company=" . $company_name . ",
					domain=" . $referring_domains . ",
					real_name=" . $contact_name . ",
					phone=" . $phone . ",
					mail=" . to_sql($mail) . ",
					adress=" . $addr1 . ",
					adress2=" . $addr2 . ",
					country_id=" . $country . ",
					state_id=" . $state . ",
					city_id=" . $city  . ",
					zip=" . $zip . ",
                    tax=" . $tax . "
					WHERE partner_id=" . to_sql(get_param("id", ""), "Number") . "
				");
				redirect("partner_edit.php?id=".get_param("id", "")."&action=saved");
			}
		}
	}

	function parseBlock(&$html)
	{
		global $g_options;

		$html->setvar("message", $this->message);

		DB::query("SELECT * FROM partner WHERE partner_id=" . to_sql(get_param("id", ""), "Number") . " ORDER BY partner_id");
		if ($row = DB::fetch_row())
		{
			foreach ($row as $k => $v)
			{
				$html->setvar($k, he($v));
			}

			$name = get_param("join_username", "");
			$pass = get_param("join_password", "");
			$pass2 = get_param("verify_password", "");
			$mail = get_param("email", "");

			$html->setvar("join_handle", $name);
			$html->setvar("join_password", $pass);
			$html->setvar("verify_password", $pass2);
			$html->setvar("email", $mail);

			$country = get_param("country", $row['country_id']);
			$state = get_param("state", $row['state_id']);
            $city = get_param("city", $row['city_id']);

            $focusField = get_param('focusField');

            if($focusField == 'state') {
                $state = 0;
                $city = 0;
            }
            if($focusField == 'city') {
                $city = 0;
            }

			$html->setvar("country_options", Common::listCountries($country));

			$state_options = Common::listStates($country, $state);
			$html->setvar("state_options", $state_options);

            $city_options = Common::listCities($state, $city);
            $html->setvar("city_options", $city_options);
		}

		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "partner_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuPartner());

include("../_include/core/administration_close.php");