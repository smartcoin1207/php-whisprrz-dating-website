<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class BlogsList extends Blogs{

    static $isGetDataWithFilter = false;
    static $tbPost = 'blogs_post';
    static $tbTags = 'blogs_post_tags';
    static $tbTagsRelations = 'blogs_post_tags_relations';

    static public function getTotalBlogsFromUser($uid = null) {
        if ($uid === null) {
            $uid = User::getParamUid(0);
        }

        $key = "BlogsList_getTotalBlogsFromUser_{$uid}";
        $count = Cache::get($key);
        if($count === null) {
            $where = self::getWhereList('', $uid);
            $count = DB::count(self::$tbPost, $where);
            Cache::add($key, $count);
        }

        return $count;
    }
}