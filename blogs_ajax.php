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

function do_action()
{
    $cmd = param('cmd');
    if ($cmd == 'img_del') {
        $post_id = intval(param('post_id'));
        if ($post_id > 0) {
            $post = CBlogsTools::getPostById($post_id);
            if (is_array($post) and $post['user_id'] == guser('user_id')) {
                $img_id = intval(param('img_id'));
                if ($img_id > 0) {
                    CBlogsTools::deleteImg($post['id'], $img_id);
                    CBlogsTools::updatePostOnlyExistsImgs($post['id']);
                }
            }
        }
    }
}
do_action();

include('./_include/core/main_close.php');
