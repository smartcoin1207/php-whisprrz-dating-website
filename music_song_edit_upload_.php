<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

//$area = "login";
include('./_include/core/main_start.php');
require_once('./_include/current/FileAPI.class.php');
require_once('./_include/current/music/tools.php');
require_once('./_include/current/fileupload.class.php');
global $g;

if( !empty($_SERVER['HTTP_ORIGIN']) ){
	// Enable CORS
	header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
	header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Range, Content-Disposition, Content-Type');
}
if( $_SERVER['REQUEST_METHOD'] == 'OPTIONS' ){
    exit;
}
if( strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' ){
	$files	= FileAPI::getFiles();
	$jsonp	= isset($_REQUEST['callback']) ? trim($_REQUEST['callback']) : NULL;
	$json	= array('data'	=> array('_REQUEST' => $_REQUEST, '_FILES' => $files));

        $upload_name = get_param('upload_name');
        $upload_dir = $g['path']['dir_files'] . 'music/tmp/';
        $file_save = $upload_name . '.mp3';
        
        $options = array('upload_dir' => $upload_dir,
                         'file_name_save' => $file_save);
        $upload_handler = new UploadHandler($options); 
        //$tmpFile = $files['filedata']['tmp_name'];
        /*if (isset($tmpFile) && is_uploaded_file($tmpFile)) {
            
            if ($upload_name) {
                move_uploaded_file($tmpFile, $g['path']['dir_files'] . 'music/tmp/' . CMusicTools::sanitize_upload_name($upload_name) . '.mp3');
            }
        }*/
	FileAPI::makeResponse(array(
		  'status' => FileAPI::OK
		, 'statusText' => 'OK'
		, 'body' => $json
	), $jsonp);
	exit;
}
include("./_include/core/main_close.php");
