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
	var $category_title_error = '';

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
	        $title = get_param("category_title", "");

	        if(!$title)
	        {
	        	$this->error = true;
	        	$this->category_title_error = l('required_field');
	        }

	        if(!$this->error)
	        {
	        	if(user_has_role_admin_edit())
                    CForumCategory::create_new($title);
	        	redirect('forum.php');
	        }
		}
	}

	function parseBlock(&$html)
	{
		global $g;
		global $g_user;

        $title       = get_param("category_title", "");

        $html->setvar('category_title', l($title, false, 'forum_category_title'));

        $html->setvar("category_title_error", $this->category_title_error);

		parent::parseBlock($html);
	}
}

$g['main']['title'] = $g['main']['title'] . ' :: ' . l('add_new_category');
$g['main']['description'] = $g['main']['title'];

$page = new CForumTopics("", $g['tmpl']['dir_tmpl_main'] . "forum_new_category.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>
