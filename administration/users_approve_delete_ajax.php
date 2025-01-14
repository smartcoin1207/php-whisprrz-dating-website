<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
// popcorn made new file 2024-05-06

include "../_include/core/administration_start.php";
require_once "../_include/current/approve_mail_sent.php";

function delete_adv_image($image_id) {
    global $g;

    $image = DB::row("SELECT * FROM adv_images WHERE id=" . to_sql($image_id, 'Number') . " LIMIT 1");
    if($image)
    {
        $filename_base = $g['path']['url_files'] . "adv_images/" . $image['id'];

        if(isS3SubDirectory($filename_base)) {
            $file_sizes = array(
                '_b.jpg',
                '_th.jpg',
                '_th_b.jpg',
                '_th_s.jpg',
                '_src.jpg',
            );

            foreach ($file_sizes as $key => $size) {
                custom_file_delete($filename_base . $size);
            }
        } else {
            $path = array($filename_base . '_b.jpg', $filename_base . '_th.jpg', $filename_base . '_th_b.jpg', $filename_base . '_th_s.jpg', $filename_base . '_src.jpg');
            Common::saveFileSize($path, false);
            $filename = $filename_base . "_th.jpg";
            if(custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_th_s.jpg";
            if(custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_th_b.jpg";
            if(custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_b.jpg";
            if(custom_file_exists($filename))
                @unlink($filename);
            $filename = $filename_base . "_src.jpg";
            if(custom_file_exists($filename))
                @unlink($filename);
        }

        DB::execute(("DELETE FROM adv_images WHERE id=" . to_sql($image['id'])));
    }
}

function delete_wowslider_image($image_path) {
    global $g;

    $image = DB::row("SELECT * FROM wowslider WHERE img_path=" . to_sql($image_path, 'Text') . " LIMIT 1");
    if($image) {
        $filename_base = $g['path']['url_files'] . "wowslider/" . $image['img_path'];
        if(isS3SubDirectory($filename_base)) {
            custom_file_delete($filename_base);
        } else {
            if(file_exists($filename_base)) {
                @unlink($filename_base);
            }
        }
    }

    DB::execute("UPDATE wowslider SET img_path='' WHERE img_path=" . to_sql($image_path, 'Text'));
}

//popcorn modified 2024-05-06
function do_action()
{
    $isAll = get_param('isAll', '');
    $ajax = get_param_int('ajax');

    if ($ajax) {
        $event_id = get_param('event_id', '');
        $hotdate_id = get_param('hotdate_id', '');
        $partyhou_id = get_param('partyhou_id', '');
        $craigs_id = get_param('craigs_id', '');
        $wowslider_id = get_param('wowslider_id', '');
        $image_id = get_param('image_id', '');
        $admin = true;

        if ($craigs_id) {

            if($image_id) {
                delete_adv_image($image_id);
            } else {
                $table = "adv_" . get_param('cat_name', '');
                $craigs = DB::row("SELECT * FROM " . $table . " WHERE id = '" . $craigs_id . "'");
                $user_id = $craigs['user_id'];
                $user = DB::row("SELECT * FROM user WHERE user_id = '" . $user_id . "'");
                $mail_row = DB::row("SELECT * FROM email_auto WHERE note = 'craigs_deleted'");
                $text = $mail_row['text'];
                $subject = $mail_row['subject'];
                $var['name'] = $user['name'];
                $var['item_title'] = $craigs['subject'];
                $ful_text = Common::replaceByVars($text, $var);

                $adv_cat_id = DB::result("SELECT id FROM adv_cats WHERE eng=" . to_sql(get_param('cat_name', ''), 'Text'));
                $adv_id = $craigs_id;

                $adv_images = DB::rows("SELECT * FROM adv_images WHERE adv_cat_id = " . to_sql($adv_cat_id, 'Text') . " AND adv_id= " . to_sql($adv_id, 'Text'));
                foreach ($adv_images as $key => $image) {
                    delete_adv_image($image['id']);
                }

                DB::execute("DELETE FROM " . $table . " WHERE id = '" . $craigs_id . "'");

                CApproveMail::approve_sent_mail($user_id, $subject, $ful_text, true);
            }
        } else if($wowslider_id) {
            if($image_id) {
                delete_wowslider_image($image_id . '.jpg');
            } else {
                $wowslider = DB::row("SELECT * FROM wowslider WHERE event_id = '" . $wowslider_id . "'");
                $user_id = $wowslider['user_id'];
                $user = DB::row("SELECT * FROM user WHERE user_id = '" . $user_id . "'");
    
                $mail_row = DB::row("SELECT * FROM email_auto WHERE note = 'wowslider_deleted'");
                $text = $mail_row['text'];
                $subject = $mail_row['subject'];
                $var['name'] = $user['name'];
                $var['item_title'] = $wowslider['title'];
                $ful_text = Common::replaceByVars($text, $var);
    
                CApproveMail::approve_sent_mail($user_id, $subject, $ful_text, true);

                $image_path  = $wowslider['img_path'];
                delete_wowslider_image($image_path);
                
                DB::execute("DELETE FROM wowslider WHERE event_id = '" . $wowslider_id . "'");
            }
        }

        die(getResponseDeleteDataAjaxByAuth(true));
    }
}

do_action();
