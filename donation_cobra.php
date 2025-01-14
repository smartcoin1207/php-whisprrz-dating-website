<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. 
  
  This file is created and built by cobra --- 2020-02-06.
  */

include("./_include/core/main_start.php");
include("./_include/current/donation.option.php");

class CDonation extends CHtmlBlock  {

    function action() {
        //
        $cmd = get_param('cmd');
        if ($cmd == 'donate_paypal') {

            $system = 'paypal';
            $item = get_param('item');
            $amount = get_param('amount');
            $requestUri = get_param('request_uri');
            if (!$requestUri) {
                $requestUri = Pay::getUrl();
            }
            $requestUri = base64_encode($requestUri);

            $urlPay = '_pay/' . $system . '/donate.php';
            $urlRedirect = $urlPay . '?item=' . $item . '&request_uri=' . $requestUri . '&amount=' . $amount;
            redirect($urlRedirect);
        }
    }

    function parseBlock(&$html) {
        $lang = Common::getOption('lang_loaded', 'main');
        
        $bg_path = DonationOption::getBgPath('background_filepath');
        $html->setvar('bg_path', $bg_path);

        $art_title = DonationOption::getBgPath('art_title');
        $art_content = DonationOption::getBgPath('art_content');
        $html->setvar('art_title', $art_title);
        $html->setvar('art_content', $art_content);

        parent::parseBlock($html);
    }

}

$page = new CDonation("", $g['tmpl']['dir_tmpl_main'] . "donation_cobra.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>
