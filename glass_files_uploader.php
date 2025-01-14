<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

//$area = "login";
include("./_include/core/main_start.php");

global $g;

#cleanup_upload_folder_all();

global $p;

//if (isset($_FILES['Filedata']) && is_uploaded_file($_FILES['Filedata']["tmp_name"]))
    //{
        $file = get_param('glassfilevideo');
        $isAjax = get_param('ajax');
        if($isAjax){
            global $errorTypeOut;
            $errorTypeOut='plainText';
        }    
        if($file)
        {
            // add .dat extension to prevent upload of PHP and other hack files
            //$file_name = $g['path']['dir_files'] . "video/" . sanitize_upload_name_all($file_name_video);
            //move_uploaded_file($_FILES['Filedata']["tmp_name"], $file_name.".dat");
            include('./_include/current/glass/tools.php');
            $r=VideoUpload::upload(0, sanitize_upload_name_all($file), 'mp4');
			//$file_name = $g['path']['dir_files'] . "video/" . sanitize_upload_name_all($file_name_video);
            // wait convertation
            #@set_time_limit(1000);
            #while(!custom_file_exists($file_name.".flv") && !custom_file_exists($file_name.".jpg")) sleep(1);
            if($r===true){
                die('ok');
            } else {
                if($r=='error_license'){
                    $r=l('error_license_title');
                }
                die($r);
            }    
        }
   //}

function cleanup_upload_folder_all()
    {
        global $g;

    	$path = $g['path']['dir_files'] . "temp/";

        $files = scandir($path);

        foreach($files as $file)
        {
            if($file != '.' && $file != '..' && $file != '.svn' && $file != 'index.html' && $file != '.htaccess')
            {
                $file_path = $path . DIRECTORY_SEPARATOR . $file;
                if(is_file($file_path))
                {
                    if(time() - custom_filemtime($file_path) > 24 * 60 * 60) @unlink($file_path);
                }
            }
        }
    }
include("./_include/core/main_close.php");
