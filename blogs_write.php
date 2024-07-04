<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = 'login';
include('./_include/core/main_start.php');
include('./_include/current/blogs/start.php');
payment_check('blogs_write');



class CPage extends CHtmlBlock
{
    public $post = null;
    function init()
    {
        if (intval(param('id')) > 0) {
            $this->post = CBlogsTools::getPostById(param('id'));
            if (!is_array($this->post) or $this->post['user_id'] != guser('user_id')) {
                redirect('blogs.php');
            }
        }
    }
	function action()
	{
		if (CBlogsTools::filterText(param('text')) != '') {
            if ($this->post == null) {
                CBlogsTools::insertPost();
            } else {
                CBlogsTools::updatePostById($this->post['id']);
            }
            CStatsTools::count('new_blogs');
            redirect('blogs_blog.php');
		}
	}
	function parseBlock(&$html)
	{
        $html->setvar('max_file', ini_get('max_file_uploads'));
        
		$html->assign('my_photo', urphoto(guser('user_id')));
        if ($this->post == null) {
		    $html->assign('post', CBlogsTools::getPostFromPost());
            if (par('add_text') != '') {
		        $html->assign('post_text', strip_tags(par('add_text')));
            }
		    $html->parse('write');
        } else {
            if ($this->post['images'] != '') {
                $imgs_orig = explode('|', $this->post['images']);
                $imgs = array();
                foreach ($imgs_orig as $k => $img_orig) {
                    if (CBlogsTools::existsImg($this->post['id'], $img_orig)) {
                        $imgs[$k]['i'] = $img_orig;
                        $imgs[$k]['url'] = g('path', 'url_files') . 'blogs/' . $this->post['id'] . '_' . $img_orig . '_t.jpg';   
                    } else {
                        CBlogsTools::deleteImg($this->post['id'], $img_orig);
                        CBlogsTools::updatePostOnlyExistsImgs($this->post['id']);
                    }
                }
                $html->items('img', $imgs);
		        $html->parse('imgs');
		    }
            $this->post['subject'] = he($this->post['subject']);
            $html->assign('post', array_merge($this->post, CBlogsTools::getPostFromPostNotNull()));
		    $html->parse('edit');
        }
            if (get_param('id') != '') {
                $html->parse('delete');
            }
            parent::parseBlock($html);
	}
}

blogs_render_page();
include('./_include/core/main_close.php');
