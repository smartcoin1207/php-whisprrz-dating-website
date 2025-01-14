<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
include("../_include/core/administration_start.php");

class CPayFeatures extends CHtmlBlock
{

	function action()
	{
		global $p;
        global $g;

		$cmd = get_param('cmd', '');
		if ($cmd == 'update') {
            $status = get_param_array('status');
            $typePaymentFeatures = Common::getOption('type_payment_features', 'template_options');
            $typePaymentFeatures = '%' . $typePaymentFeatures . '%';
            $features = DB::select('payment_features', '`type` LIKE ' . to_sql($typePaymentFeatures));
            foreach ($features as $key => $item) {
                $active = intval(isset($status[$item['id']]));
                if ($item['alias'] == 'invisible_mode' && $active
                    && Common::getOption('paid_access_mode') != 'free_site'
                    && !Common::isActiveFeatureSuperPowers('invisible_mode')) {
                    User::resetOptionsInvisibleMode();
                }
                DB::update('payment_features', array('status' => $active), '`id` = ' . to_sql($item['id']));
            }
			redirect($p . '?action=saved');
		}
	}

	function parseBlock(&$html)
	{
        $typePayment = Common::getOption('type_payment_features', 'template_options');
        $typePaymentFeatures = '%' . $typePayment . '%';
        $features = DB::select('payment_features', '`type` LIKE ' . to_sql($typePaymentFeatures));
        foreach ($features as $key => $item) {
            $html->setvar('key', $item['id']);
            $html->setvar('checked', $item['status']?'checked':'');
            $title = lCascade($item['title'], array($item['title'] . '_' . $typePayment, $item['title']));
			$html->setvar('title', $title);
			$html->parse('item', true);
        }

		parent::parseBlock($html);
	}
}

$page = new CPayFeatures('', $g['tmpl']['dir_tmpl_administration'] . 'pay_features.html');
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);
$page->add(new CAdminPageMenuPay());

include('../_include/core/administration_close.php');