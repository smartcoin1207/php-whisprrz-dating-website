<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = 'login';
include('./_include/core/main_start.php');
include('./_include/current/glass/start.php');

class CPage extends CHtmlBlock
{
    function action()
    {
        $file = get_param('glassfilevideo');
        $isUploadVideo = 0;
        if ($file) {
            //VideoUpload::upload(0, $file);
            $isUploadVideo = 1;
        }
		if (CVidsTools::validateVideoFile() || $isUploadVideo) {
            CStatsTools::count('videos_uploaded');
            $active=1;
            if(Common::isOptionActive('video_approval')){
                $active=3;
            }
            $id = CVidsTools::insertVideo($file, $active, true, $isUploadVideo);
            if(Common::isOptionActive('video_approval')){
                if(Common::isEnabledAutoMail('approve_video_admin')){
                    global $g_user;
                    $vars = array(
                        'name'  => User::getInfoBasic($g_user['user_id'],'name'),
                    );
                    Common::sendAutomail(Common::getOption('administration', 'lang_value'), Common::getOption('info_mail', 'main'), 'approve_video_admin', $vars);
                }
            }
            redirect('LookingGlass.php?');
        }
    }
    function parseBlock(&$html)
    {
        global $g_user;
        $maxSize = Common::getOption('video_size');
        $html->setvar('video_file_size_limit', mb_to_bytes($maxSize));
        $html->setvar('error_video_file_size_limit', lSetVars(l('max_file_size'), array('size' => $maxSize)));
        $html->setvar('video_max_chunk_size', Common::getOption('video_max_chunk_size_kb', 'upload_files') * 1024);
        $html->setvar('upload_name', 'upload_video_' . $g_user['user_id'] . '_' . time() . md5(microtime()) . rand(0,100000));
        parent::parseBlock($html);
    }
}

vids_render_page();
include('./_include/core/main_close.php');
