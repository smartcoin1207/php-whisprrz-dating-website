<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('./_include/core/main_start.php');
include('./_include/current/blogs/start.php');

if (!guid()) {
    Common::toLoginPage();
}

class CPage extends CHtmlBlock
{
    public $post = null;
    public $user = null;
    public $to_comment = 0;

    function init()
    {
        global $g;

        $isSocialBlogs = Common::isOptionActiveTemplate('blogs_social_enabled');
        $blogId = Blogs::getParamId();
        $pageBlogs = 'blogs.php';
        if ($isSocialBlogs) {
            $pageBlogs = Common::pageUrl('blogs_list');
        }

        if ($blogId) {
            if  (!User::isNarrowBox('blogs')) {
                CBlogsTools::$thumbnail_postfix = 'o';
            }
            $this->post = CBlogsTools::getPostById($blogId);

            if (!is_array($this->post)) {
                redirect($pageBlogs);
            } else {
                $this->user = user($this->post['user_id']);
                if ($this->post['user_id'] != guid()) {
                    CBlogsTools::viewPostByIdAndUserId($this->post['id'], $this->post['user_id']);
                }
            }
        } else {
            redirect($pageBlogs);
        }

        if (!$isSocialBlogs) {
            $g['main']['title'] = htmlspecialchars($this->post['subject_req'], ENT_QUOTES, 'UTF-8');
            $g['main']['description'] = htmlspecialchars($this->post['text_short'], ENT_QUOTES, 'UTF-8');
        }
    }

	function action()
	{
		if (CBlogsTools::filterCommentText(param('text')) != '') {
            $this->to_comment = CBlogsTools::insertCommentByPostId($this->post['id']);
		}
		if (intval(param('del')) > 0) {
            CBlogsTools::delCommentById(intval(param('del')));
		}
	}

	function parseBlock(&$html)
	{
        $guid = guid();

        $blogId = Blogs::getParamId();
        $isSocialBlogs = Common::isOptionActiveTemplate('blogs_social_enabled');
        $optionTemplateName = Common::getTmplName();

        if (Common::getOption('video_player_type') == 'player_custom' && grabs($this->post['text'], '{site:', '}')) {
            $html->parse('player_custom', false);
        }

        $html->assign('post', $this->post);

        $this->user['photo'] = urphoto($this->user['user_id']);
        $this->user['url'] = User::url($this->user['user_id']);

        $html->assign('blogger', $this->user);

        if ($isSocialBlogs) {
            if (User::isOnline($this->user['user_id'])) {
                $html->parse('blogger_online', false);
            }
            $blogsRandom = array();
            $whereTags = 'no_tags';
            $limit = 2;

            $tags = Blogs::getTags($blogId);
            if ($tags) {
                $tags = implode(',', $tags);
                $whereTags = Blogs::getWhereTags('TR.', $tags);
            }

            if ($whereTags != 'no_tags' && $whereTags) {
                $sql = 'SELECT *
                          FROM `blogs_post_tags_relations` AS TR
                          JOIN `blogs_post` AS B  ON B.id = TR.blog_id
                         WHERE ' . $whereTags
                             . ' AND B.id != ' . to_sql($blogId)
                             . ' AND B.user_id = ' . to_sql($this->post['user_id'])
                             . ' GROUP BY B.id '
                             . ' ORDER BY RAND() LIMIT ' . to_sql($limit, 'Plain');
                $blogsRandom = DB::rows($sql);
            }

            $countBlogs =  $limit - count($blogsRandom);
            if ($countBlogs > 0) {
                $where = '';
                $whereNoBlogs = array();
                if ($blogsRandom) {
                    foreach ($blogsRandom as $key => $item) {
                        $whereNoBlogs[] = $item['id'];
                    }
                }
                if ($whereNoBlogs) {
                    $where = ' AND id NOT IN(' . implode(',', $whereNoBlogs) . ')';
                }
                $sql = 'SELECT *, IF(user_id=' .  to_sql($this->post['user_id'], 'Plain') . ', 1, 0) AS my
                          FROM `blogs_post`
                         WHERE id != ' . to_sql($blogId) . $where .
                       ' ORDER BY my DESC, RAND() LIMIT ' . to_sql($countBlogs, 'Plain');
                $blogsRandom_1 = DB::rows($sql);
                if ($blogsRandom_1) {
                    $blogsRandom = array_merge($blogsRandom, $blogsRandom_1);
                }
            }
            if ($blogsRandom) {
                $blockItem = 'post_random_item';
                foreach ($blogsRandom as $key => $item) {
                    $image = Blogs::getImageDefault($item['id'], 'bm', $item);
                    $vars = array(
                        'title' => $item['subject'],
                        'url' => Blogs::url($item['id'], $item),
                        'image' => $image['image'],
                        'time_ago' => timeAgo($item['dt'], 'now', 'string', 60, 'second')
                    );
                    $html->assign('post_random', $vars);

                    if ($image['placeholder']) {
                        $html->parse("{$blockItem}_image_placeholder", false);
                    } else {
                        $html->clean("{$blockItem}_image_placeholder");
                    }
                    $html->parse($blockItem, true);
                    //break;
                }
                $html->parse('module_posts_random', false);
            }

            $userInfo = User::getInfoBasic($guid);
            $vars = array(
                'uid'   => $guid,
                'photo' => User::getPhotoDefault($guid, 'r', false, $userInfo['gender']),
                'url'   => User::url($guid)
            );
            $html->assign('comment_guid', $vars);

            $html->setvar('page_edit_url', Common::pageUrl('blog_edit', $blogId));
            $html->setvar('page_my_blogs', Common::pageUrl('user_blogs_list', $guid));


            if ($this->post['comments_enabled']) {

                Blogs::parseLikes($html, $blogId, $this->post['likes']);

                Blogs::parseComments($html, $blogId, 'photo');

                $html->parse('blog_post_block_module_action', false);
            }

            TemplateEdge::parseColumn($html);

        } else {

            $html->items('post_comment', CBlogsTools::getCommentsByPostId($this->post['id']), '', 'is_my');

            if ($this->to_comment > 0) {
                $html->assign('to_comment', $this->to_comment);
                $html->parse('to_comment');
            }

            $state = User::isNarrowBox('blogs');
            if  ($state) {
                $html->setvar('hide_narrow_box', 'block');
                $html->setvar('show_narrow_box', 'none');
            } else {
                CBlogsTools::$thumbnail_postfix = 'o';
                $html->setvar('hide_narrow_box', 'none');
                $html->setvar('show_narrow_box', 'block');
            }
        }

        $isParseBlLink = false;
        if ($guid == $this->post['user_id']) {
            $html->parse('post_edit');
            $isParseBlLink = true;
        }

        if ($html->blockExists('post_all') && Blogs::getTotalBlogs(0) > 1) {
            $html->setvar('post_all_link', Common::pageUrl('blogs_list'));
            $html->parse('post_all', false);
            $isParseBlLink = true;
        }

        if ($isParseBlLink && $html->blockExists('post_link')) {
            $html->parse('post_link', false);
        }

        parent::parseBlock($html);
    }
}

blogs_render_page();

include('./_include/core/main_close.php');