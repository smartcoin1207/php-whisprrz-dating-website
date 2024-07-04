<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");
if(!Common::isOptionActive('help')) {
    redirect(Common::toHomePage());
}
class CHelp extends CHtmlBlock
{

	var $message;

	function parseBlock(&$html)
	{

        $lang = Common::getOption('lang_loaded', 'main');

        $sql = 'SELECT * FROM help_topic
            WHERE lang = ' . to_sql($lang) . '
            ORDER BY id ASC';
		DB::query($sql);
		$i = 1;
		while ($row = DB::fetch_row())
		{
			$html->setvar("id", $row['id']);
			$html->setvar("name", $row['name']);
			if ($i % 6 == 0)
			{
				$html->parse("topic_column", false);
			}
			else
			{
				$html->setblockvar("topic_column", "");
			}
			$html->parse("topic", true);
			$i++;
		}

		$t = get_param('t', 0);
		$topic = DB::row("SELECT * FROM help_topic WHERE id=" . to_sql($t, "Number") . "");
        if($topic) {
            if($topic['lang'] != $lang) {
                redirect('help.php');
            }
            $html->setvar('topic_name', $topic['name']);
        }

		DB::query("SELECT id, name, text FROM help_answer WHERE topic_id=" . to_sql($t, "Number") . " ORDER BY id");
        $parse = false;
		while ($row = DB::fetch_row())
		{
			$html->setvar("id", $row['id']);
			$html->setvar("name", $row['name']);
			$html->setvar("text", nl2br($row['text']));

			$html->parse("show", true);
			$html->parse("hide", true);
			$html->parse("question", true);
            $parse = true;
		}

        if($parse) {
            $html->parse('show_hide');
        }

		parent::parseBlock($html);
	}
}

$page = new CHelp("", $g['tmpl']['dir_tmpl_main'] . "help.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$mailMenu = new CMenuSection('menu_help', $g['tmpl']['dir_tmpl_main'] . "_menu_help.html");
$mailMenu->setActive('help');
$page->add($mailMenu);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>
