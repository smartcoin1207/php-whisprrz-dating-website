<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
include('./_include/core/main_start.php');
include('./_include/current/fileapi.class.php');

$action = get_param('action');
$uploadFile = $g['path']['dir_files'] . 'temp/tmp_wall_' . $g_user['user_id'] . '.jpg';
if ($action == 'delete') {
    @unlink($uploadFile);
    die('ok');
} elseif ($action == 'upload') {
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
    $files	= FileAPI::getFiles(); // Retrieve File List
    $error_upload = false;
    if (isset($files['filedata']['tmp_name'])&& Image::isValid($files['filedata']['tmp_name'])){
        move_uploaded_file($files['filedata']['tmp_name'], $uploadFile);
    } else {
        $error_upload = true;
    }
	// JSONP callback name
	$jsonp	= isset($_REQUEST['callback']) ? trim($_REQUEST['callback']) : null;


	// JSON-data for server response
	$json	= array('error_upload'	=> $error_upload,
                    'data'	=> array('_REQUEST' => $_REQUEST, '_FILES' => $files)
	);
	// Server response: "HTTP/1.1 200 OK"
	FileAPI::makeResponse(array(
                            'status' => FileAPI::OK,
                            'statusText' => 'OK',
                            'body' => $json
            ), $jsonp);
            exit;
    }
}
include('./_include/core/main_close.php');