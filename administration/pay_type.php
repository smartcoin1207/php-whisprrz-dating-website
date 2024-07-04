<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminPay extends CHtmlBlock
{
	var $message = "";
	function action()
	{
		global $g;
		global $pay;
		global $l;
		$cmd = get_param("cmd", "");

		if ($cmd == "update")
		{
			DB::execute("TRUNCATE TABLE payment_type");
			// die();

	        $set = Common::getOption('set', 'template_options');
	        $sql = 'SELECT * FROM `payment_plan` WHERE `set` = ' . to_sql($set) . ' AND `type` = ' . to_sql('payment', 'Text') . ' ORDER BY `item` ASC';
			$payment_plans = DB::rows($sql);

			foreach ($payment_plans as $key => $row) {
				$payplan = get_param_array($row['item']);
				foreach ($payplan as $k => $v) DB::execute("INSERT INTO payment_type SET code=" . to_sql($v) . ", type=".to_sql($row['item']));
			}

			// $gold = get_param_array("gold");
			// $silver = get_param_array("silver");
			// $platinum = get_param_array("platinum");
			// $platinumplus = get_param_array("platinum_plus");
			// $platinumevent = get_param_array("platinum_events");

			// foreach ($gold as $k => $v) DB::execute("INSERT INTO payment_type SET code=" . to_sql($v) . ", type='gold'");
			// foreach ($silver as $k => $v) DB::execute("INSERT INTO payment_type SET code=" . to_sql($v) . ", type='silver'");
			// foreach ($platinum as $k => $v) DB::execute("INSERT INTO payment_type SET code=" . to_sql($v) . ", type='platinum'");
			// foreach ($platinumplus as $k => $v) DB::execute("INSERT INTO payment_type SET code=" . to_sql($v) . ", type='platinum_plus'");
			// foreach ($platinumevent as $k => $v) DB::execute("INSERT INTO payment_type SET code=" . to_sql($v) . ", type='platinum_events'");
			global $p;
			redirect($p."?action=saved");

		}
	}
	function parseBlock(&$html)
	{
		global $g;
		global $pay;
		global $p;
        
        $set = Common::getOption('set', 'template_options');
        $sql = 'SELECT * FROM `payment_plan` WHERE `set` = ' . to_sql($set) . ' AND `type` = ' . to_sql('payment', 'Text') . ' ORDER BY `item` ASC';
		$payment_plans = DB::rows($sql);

		foreach ($payment_plans as $key => $row) {
			$html->setvar('payplan_title', $row['item_name']);
			$html->parse('payplan_title');
		}

		$html->parse('payplan_titles');

		DB::query('SELECT * FROM payment_cat ORDER BY name ASC');
		while ($row = DB::fetch_row()) {
			$html->setvar('item', $row['id']);
			$html->setvar('pay_name', $row['name']);
			$html->setvar('pay_code', $row['code']);


			foreach ($payment_plans as $key => $payplan) {
				$check = DB::result("select code from payment_type where type=".to_sql($payplan['item'])." and code=".to_sql($row['code']),0,2);
				if(empty($check)) $html->setvar("payplan_checked", '');
				else $html->setvar("payplan_checked", 'checked');
				$html->setvar('payplan_id', $payplan['item']);
				$html->parse('payplan_checkbox');
			}


			// $check = DB::result("select code from payment_type where type='silver' and code=".to_sql($row['code']),0,2);
			// if(empty($check)) $html->setvar("silver_checked", '');
			// else $html->setvar("silver_checked", 'checked');

			// $check = DB::result("select code from payment_type where type='gold' and code=".to_sql($row['code']),0,2);
			// if(empty($check)) $html->setvar("gold_checked", '');
			// else $html->setvar("gold_checked", 'checked');

			// $check = DB::result("select code from payment_type where type='platinum' and code=".to_sql($row['code']),0,2);
			// if(empty($check)) $html->setvar("platinum_checked", '');
			// else $html->setvar("platinum_checked", 'checked');

			// $check = DB::result("select code from payment_type where type='platinum_plus' and code=".to_sql($row['code']),0,2);
			// if(empty($check)) $html->setvar("platinum_plus_checked", '');
			// else $html->setvar("platinum_plus_checked", 'checked');
			
			// $check = DB::result("select code from payment_type where type='platinum_events' and code=".to_sql($row['code']),0,2);
			// if(empty($check)) $html->setvar("platinum_events_checked", '');
			// else $html->setvar("platinum_events_checked", 'checked');

			$html->parse("item", true);
			$html->clean('payplan_checkbox');
		}

		parent::parseBlock($html);
	}
}

$page = new CAdminPay("", $g['tmpl']['dir_tmpl_administration'] . "pay_type.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);
$page->add(new CAdminPageMenuPay());

include("../_include/core/administration_close.php");

?>
