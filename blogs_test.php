<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");

class CPage extends CHtmlBlock
{
    function parseBlock(&$html)
    {
        global $g;
        $dir = $g['tmpl']['dir_tmpl_main'] . "blogs/";
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if (pathinfo($dir . $file, PATHINFO_EXTENSION) == 'htm') {
                    $links[] = "<a href='./blogs_test.php?t=" . pathinfo($dir . $file, PATHINFO_FILENAME) . "'>"
                             . pathinfo($dir . $file, PATHINFO_FILENAME) . "</a>";
                }
            }
            closedir($dh);
        }

        $html->assign('links', implode('<br>', $links));

        parent::parseBlock($html);
    }
}
class CPageM extends CHtmlBlock
{
}

$t = param('t', 'index');

$page = new CPageM("", $g['tmpl']['dir_tmpl_main'] . "blogs_test.html");
$spage = new CPage("blog_template", $g['tmpl']['dir_tmpl_main'] . "blogs/$t.htm");
$page->add($spage);
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");
