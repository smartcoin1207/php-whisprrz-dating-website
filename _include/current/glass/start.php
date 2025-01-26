<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

if (g('options', 'videogallery') != 'Y') redirect('home.php');
if(!isset($checkVidsPaymentOff)) {
    payment_check('videogallery');
}

require_once dirname(__FILE__) . '/includes.php';

function vids_render_page() {
    global $page;
    global $g;
    global $g_info;

    #$g_info['body_addon'] = 'style="visibility: hidden;" onload="js_Load()"';

    if (class_exists('CPage', false)) {

        $page = new CPage("", $g['tmpl']['dir_tmpl_main'] . curpage() . ".html");
        $header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");

        #$customHead = new CHtmlBlock("custom_head", $g['tmpl']['dir_tmpl_main'] . "_vids_custom_head.html");
        #$header->add($customHead);

        $page->add($header);
        $footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
        $page->add($footer);
        $bheader = new CVidsHeader("vids_header", $g['tmpl']['dir_tmpl_main'] . "_glass_header.html");
        $page->add($bheader);
        $bside = new CVidsSide("vids_side", $g['tmpl']['dir_tmpl_main'] . "_glass_side.html");
        $page->add($bside);
        $bfooter = new CVidsFooter("vids_footer", $g['tmpl']['dir_tmpl_main'] . "_glass_footer.html");
        $page->add($bfooter);
    }
}
