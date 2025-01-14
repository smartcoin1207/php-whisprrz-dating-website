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
		global $g;
		$cmd = get_param("cmd", "");

		if ($cmd == "update")
		{
	        $comment_id = get_param('comment_id');
	        DB::query("SELECT m.* ".
	            "FROM music_musician_comment as m ".
	            "WHERE m.comment_id=" . to_sql($comment_id, 'Number') . " LIMIT 1");
			if($comment = DB::fetch_row())
	        {
                $text = get_param('comment_text');

                DB::execute('UPDATE music_musician_comment SET comment_text=' . to_sql($text) .
                    ' WHERE comment_id=' . $comment['comment_id']);

            		redirect("music_musician_comment_edit.php?action=saved&comment_id=" . $comment['comment_id']);
	        }

		}
	}
	function parseBlock(&$html)
	{
		global $g;

        $comment_id = get_param('comment_id');
        DB::query("SELECT m.*, ms.musician_name ".
            "FROM music_musician_comment as m, music_musician as ms ".
            "WHERE m.musician_id = ms.musician_id AND m.comment_id=" . to_sql($comment_id, 'Number') . " LIMIT 1");
        if($comment = DB::fetch_row())
        {
        	$html->setvar('comment_id', $comment['comment_id']);
        	$html->setvar('comment_text', $comment['comment_text']);
        	$html->setvar('musician_name', $comment['musician_name']);
        }

		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "music_musician_comment_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuMusic());

include("../_include/core/administration_close.php");