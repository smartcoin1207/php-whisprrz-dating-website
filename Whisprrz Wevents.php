<?php

include('./_include/core/main_start.php');

class CPage extends CHtmlBlock
{
    function init()
    {
        global $g;
    }
    function parseBlock(&$html)
    {

        $html->setvar('email', guser("mail"));
        $html->setvar('username', guser("name"));
        $html->setvar('password', guser("password"));

        parent::parseBlock($html);
        
    }
}

if(!Common::isOptionActive('whisprrz_wevents')) {
    redirect(Common::getHomePage());
}

$page = new CPage("", $g['tmpl']['dir_tmpl_main'] . "whisp_wevents.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");

$page->add($header);
$page->add($footer);


include('./_include/core/main_close.php');
?>