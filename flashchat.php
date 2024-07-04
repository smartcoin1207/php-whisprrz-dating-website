<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

CStatsTools::count('flash_chat_opened');

payment_check('flash_chat');

class CFlashChat extends CHtmlBlock
{

	function parseBlock(&$html)
	{
		global $g;
		global $g_user;

        $reason = get_param("reason", "");

        $html->setvar('flash_chat', User::flashChat());

        $sections = array(
            'videogallery',
            'forum',
            'groups',
            'events',
        );

        foreach($sections as $section) {
            if(Common::isOptionActive($section)) {
                $html->parse('kick_' . $section);
                $html->parse('ban_' . $section);
            }
        }

        if ($reason == "kick")
        {
            $html->parse("kick", true);
        }
        elseif ($reason == "ban")
        {
            $html->parse("ban", true);
        }
        else
        {
            $html->parse("chat", true);
        }

		parent::parseBlock($html);
	}
}

$page = new CFlashChat("", $g['tmpl']['dir_tmpl_main'] . "flashchat.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);




include("./_include/core/main_close.php");

?>
