<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
class CAbout extends CHtmlBlock
{
	function parseBlock(&$html)
	{
        $pageId = CustomPage::getIdFromAlias('menu_bottom_about_us');
        CustomPage::parsePage($html, $pageId);

        if (Common::isOptionActive('contact')) {
            $html->parse('contact', false);
        } else {
            $html->parse('no_contact', false);
        }

        TemplateEdge::parseColumn($html);

		parent::parseBlock($html);
	}
}

$page = new CAbout("", getPageCustomTemplate('about.html', 'custom_page_template'));
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");