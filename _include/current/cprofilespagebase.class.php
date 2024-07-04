<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CProfilesPageBase extends CHtmlBlock {

    function parseBlock(&$html)
    {
        global $p;

        if (Common::getOption('blogs') == 'Y') {
            $html->parse('my_blog', true);
        }
        if (Common::getOption('music') == 'Y') {
            $html->parse('my_music', true);
        }
        if ($html->varExists('url_pages')) {
            $show = get_param('show');
            if ($show == 'wall_liked' || $show == 'wall_shared') {
                $wallLikedId = get_param_int('wall_item_id', get_param_int('wall_shared_item_id'));
                if (!$wallLikedId || !DB::count('wall', 'id = ' . to_sql($wallLikedId))) {
                    Common::toHomePage();
                }
                $pageUrl = Common::pageUrl($show);
            } elseif ($show == 'blogs_post_liked') {
                $blogId = get_param_int('blog_id');
                if (!$blogId || !DB::count('blogs_post', 'id = ' . to_sql($blogId))) {
                    Common::toHomePage();
                }
                $pageUrl = Common::pageUrl($show, null, $blogId);
            } elseif ($show == 'photo_liked') {
                $photoId = get_param_int('photo_id');
                if (!$photoId || !DB::count('photo', 'photo_id = ' . to_sql($photoId))) {
                    Common::toHomePage();
                }
                $pageUrl = Common::pageUrl('photo_liked', null, $photoId);
            } elseif ($show == 'video_liked') {
                $videoId = get_param_int('video_id');
                if (!$videoId || !DB::count('vids_video', 'id = ' . to_sql($videoId))) {
                    Common::toHomePage();
                }
                $pageUrl = Common::pageUrl('video_liked', null, $videoId);
            } elseif ($show == 'wall_liked_comment' || $show == 'photo_liked_comment'
                        || $show == 'video_liked_comment' || $show == 'blogs_post_liked_comment') {
                $commentId = get_param_int('comment_id');
                if (!$commentId) {
                    Common::toHomePage();
                }
                $pageUrl = Common::pageUrl($show);
            } else {
                $pageUrl = substr($p, 0, -4);
                $pageUrl = Common::pageUrl($pageUrl);
            }

            $html->setvar('url_pages', $pageUrl);
        }
        if ($html->varExists('page_param')) {
            $pageParam = 'page';
            if ($p == 'users_viewed_me.php') {
                $pageParam = 'offset';
            }
            $html->setvar('page_param', $pageParam);
            $html->setvar('page_user_id', guid());
            $html->setvar('page_number', get_param_int($pageParam));
        }

        parent::parseBlock($html);
    }
}