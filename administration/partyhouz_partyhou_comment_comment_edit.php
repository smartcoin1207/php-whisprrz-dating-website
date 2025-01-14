<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
// Rade 2023-09-23

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
	            "FROM partyhouz_partyhou_comment_comment as m ".
	            "WHERE m.comment_id=" . to_sql($comment_id, 'Number') . " LIMIT 1");
			if($comment = DB::fetch_row())
	        {
                $text = get_param('comment_text');
                
                DB::execute('UPDATE partyhouz_partyhou_comment_comment SET comment_text=' . to_sql($text) . 
                    ' WHERE comment_id=' . $comment['comment_id']);
                                
                redirect("partyhouz_partyhou_comment_comments.php?comment_id=".$comment['parent_comment_id']."&action=saved");		 
	        }
		 
		}
	}
	function parseBlock(&$html)
	{
		global $g;

        $comment_id = get_param('comment_id');
        DB::query("SELECT m.* ".
            "FROM partyhouz_partyhou_comment_comment as m ".
            "WHERE m.comment_id=" . to_sql($comment_id, 'Number') . " LIMIT 1");
        if($comment = DB::fetch_row())
        {
		$partyhou_id = DB::field('partyhouz_partyhou_comment','partyhou_id','comment_id ='.$comment['parent_comment_id']);
                $partyhou_name=DB::field('partyhouz_partyhou','partyhou_title','partyhou_id ='.$partyhou_id[0]);
		$html->setvar('partyhou_name',$partyhou_name[0]);

        	$html->setvar('comment_id', $comment['comment_id']);
        	$html->setvar('comment_text', $comment['comment_text']);
        }
		
		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "partyhouz_partyhou_comment_comment_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");