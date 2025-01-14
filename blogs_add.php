<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = 'login';
include('./_include/core/main_start.php');
include('./_include/current/blogs/tools.php');

if (!Common::isOptionActive('blogs')) {
    redirect(Common::toHomePage());
}

class CPage extends CHtmlBlock
{
    public $post = null;

    function init()
    {
        $blogId = get_param_int('blog_id');
        if ($blogId) {
            Blogs::resetTagsHtml();
            $this->post = CBlogsTools::getPostById($blogId);
            if (!is_array($this->post) || $this->post['user_id'] != guid()) {
                redirect(Common::pageUrl('blogs_list'));
            }
        }
    }

	function parseBlock(&$html)
	{
        global $g;

        $block = 'blog';
        $blogId = get_param_int('blog_id');

        if ($this->post) {
            $photoId = 0;
            $image = $g['path']['url_files'] . 'blog_s.png';
            $btnUpload = $photoId ? l('use_another') : l('choose_an_image');
            $btnCreate = l('save');
            $html->assign('post', $this->post);
        } else {

            $html->parse('btn_post_disabled', false);
            $image = $g['path']['url_files'] . 'blog_s.png';
            $btnUpload = l('choose_an_image');
            $btnCreate = l('publish_post');
        }

        $info = array(
            'image' => $image,
            'photo_btn_upload' => $btnUpload,
            'btn_create' => $btnCreate
        );

        $html->assign($block, $info);
        /*$html->setvar('max_file', ini_get('max_file_uploads'));*/

        TemplateEdge::parseColumn($html);

        parent::parseBlock($html);
	}
}


$page = new CPage("", getPageCustomTemplate('blogs_add.html', 'blogs_add_template'));
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);


include('./_include/core/main_close.php');