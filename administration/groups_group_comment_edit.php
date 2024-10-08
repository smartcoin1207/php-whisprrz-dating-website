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
	            "FROM wall_comments as m ".
	            "WHERE m.id=" . to_sql($comment_id, 'Number') . " LIMIT 1");
			if($comment = DB::fetch_row())
	        {
                $text = get_param('comment_text');

                DB::execute('UPDATE wall_comments SET comment=' . to_sql($text) .
                    ' WHERE id=' . $comment['id']);

                $id = DB::insert_id();
	        }
		redirect("groups_group_comments.php?action=saved");
		}
	}
	function parseBlock(&$html)
	{
		global $g;

        $comment_id = get_param('comment_id');


        DB::query("SELECT m.* ".
            "FROM wall_comments as m ".
            "WHERE m.id=" . to_sql($comment_id, 'Number') . " LIMIT 1");
        if($comment = DB::fetch_row())
        {
            $group_name=DB::field('groups_social','title','group_id ='.$comment['group_id']);

            // var_dump($group_name); die();

            if($group_name)
			$html->setvar('group_name',$group_name[0]);
        	$html->setvar('comment_id', $comment['id']);
        	$html->setvar('comment_text', $comment['comment']);
        }

		parent::parseBlock($html);
	}
}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "groups_group_comment_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuGroups());

include("../_include/core/administration_close.php");