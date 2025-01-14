<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CAdvTools {

    private static $prfImage = array('b.jpg', 'th.jpg', 'th_s.jpg', 'src.jpg');

    public static function deleteAdv($cat = null, $id = 0, $catId = null, $admin = false)
    {
        global $g_user;

        $result = false;

        if ($cat === null && $catId !== null) {
            $catLink = DB::field('adv_cats', 'eng', '`id` = ' .  to_sql($catId, 'Number'));
            if($catLink) {
                $cat = $catLink[0];
            }
        }

        if (!empty($cat)) {
            $table = "adv_{$cat}";
            $adv = DB::select($table, '`id` = ' . to_sql($id, 'Number'));
            if(!$adv) {
                return;
            }
            $adv = $adv[0];
            if ($adv['user_id'] == $g_user['user_id'] || $admin) {
                self::deleteOne($table, $adv['cat_id'], $id);
                $result = true;
            }
        }
        return $result;
    }

    public static function deleteImage($cat, $id)
    {
        global $g;

        $images = DB::select('adv_images', '`adv_cat_id` = ' . to_sql($cat, 'Numbet') . ' AND `adv_id` = ' . to_sql($id, 'Numbet'));
        foreach ($images as $image) {
            $sFile_ = $g['path']['dir_files'] . 'adv_images/' . $image['id'] . '_';
            foreach (self::$prfImage as $prf) {
                $file = $sFile_ . $prf;
                Common::existsOneFileUserDelete($file);
                DB::execute("DELETE FROM `adv_images` WHERE `id` = " . to_sql($image['id'], 'Number'));
            }
        }
    }

    public static function deleteImageOne($image_id) {
        global $g;

        $image = DB::row("SELECT * FROM adv_images WHERE id = '" . to_sql($image_id, 'Number') . "'");
        if($image) {

            $sFile_ = $g['path']['dir_files'] . 'adv_images/' . $image['id'] . '_';
            foreach (self::$prfImage as $prf) {
                $file = $sFile_ . $prf;
                Common::existsOneFileUserDelete($file);
                DB::execute("DELETE FROM `adv_images` WHERE `id` = " . to_sql($image['id'], 'Number'));
            }
        }

    }

    public static function deleteUser($userId, $admin = true)
    {
        if ($admin) {
            $cats = DB::select('adv_cats');
            foreach ($cats as $cat) {
                $table = "adv_{$cat['eng']}";
                $catCurrent = DB::select($table, '`user_id` = ' . to_sql($userId, 'Number'));
                foreach ($catCurrent as $item) {
                    self::deleteOne($table, $item['cat_id'], $item['id']);
                }
            }
        }
    }

    public static function deleteSubcats($id)
    {
        $subcats = DB::field('adv_razd', 'cat_id', '`id` = ' . to_sql($id, 'Number'));
        if(!$subcats) {
            return;
        }

        $cats = DB::field('adv_cats', 'eng', '`id` = ' . to_sql($subcats[0], 'Number'));
        if(!$cats) {
            return;
        }

        $table = "adv_{$cats[0]}";
        $adv = DB::select($table, '`razd_id` = ' . to_sql($id, 'Number'));
        foreach ($adv as $item) {
            self::deleteOne($table, $subcats[0], $item['id']);
        }
        DB::execute('DELETE FROM `adv_razd` WHERE `id` = ' . to_sql($id, 'Number'));

    }

    public static function deleteOne($table, $catId, $id)
    {
        self::deleteImage($catId, $id);
        DB::execute("DELETE FROM {$table} WHERE `id` = " . to_sql($id, 'Number'));
    }







}
