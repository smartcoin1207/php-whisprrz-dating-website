<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */


$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/forum.php");

payment_check('forum');

class CForumTopics extends CHtmlBlock
{
	var $error = false;
	var $forum_title_error = '';
	var $forum_description_error = '';
	var $forum_message_error = '';

	function action()
	{
		global $g;
		global $l;
		global $g_user;
		global $g_info;

        if(!user_has_role_admin_view())
            redirect("forum.php");

		$cmd = get_param("cmd");
		if($cmd == 'post')
		{
	        $category_id = get_param("category_id");

	        $category = CForumCategory::retrieve_by_id($category_id);
	        if(!$category)
	            redirect("forum.php");

	        $title       = get_param("forum_title", "");
	        $description = get_param("forum_description", "");

	        if(!$title)
	        {
	        	$this->error = true;
	        	$this->forum_title_error = l('required_field');
	        }

	        if(!$this->error)
	        {
	        	if(user_has_role_admin_edit())
                    CForumForum::create_new($category_id, $title, $description);
	        	redirect('forum.php');
	        }
		}
	}

	function parseBlock(&$html)
	{
		global $g;
		global $g_user;

        $title       = get_param("forum_title", "");
        $description = get_param("forum_description", "");

        $html->setvar('forum_title', l($title, false, 'forum_title'));
        $html->setvar('forum_description',l($description, false, 'forum_description'));

        $html->setvar("forum_title_error", $this->forum_title_error);
        $html->setvar("forum_description_error", $this->forum_description_error);

        $category_id = get_param("category_id");
        $html->setvar('category_id', $category_id);

		parent::parseBlock($html);
	}
}

$g['main']['title'] = $g['main']['title'] . ' :: ' . l('add_new_forum');
$g['main']['description'] = $g['main']['title'];

$page = new CForumTopics("", $g['tmpl']['dir_tmpl_main'] . "forum_new_forum.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>
