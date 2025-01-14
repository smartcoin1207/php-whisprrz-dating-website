<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['no_headers'] = true;

include("./_include/core/main_start.php");

$dirsParam = get_param('dirs');
$dirs = explode(',', $dirsParam);
$file = get_param('file');
if (strpos($file, 'user') === false) {
    $basePath = $g['tmpl']['dir_tmpl_main'] . implode(DIRECTORY_SEPARATOR, $dirs) . DIRECTORY_SEPARATOR;
} else {
    $basePath = $g['path']['url_files'] . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR;
}

$extAllow = array('gif', 'png', 'jpg', 'jpeg');

// preg replace!!!
$fileParts = explode('.', $file);
$ext = array_pop($fileParts);
if (!in_array($ext, $extAllow)) {
    return;
}

$file = implode('.', $fileParts);


$lang = get_param('lang');

$suffix = '';
if ($lang != 'default') {
    $suffix = '_' . $lang;
}

$fileNameTmpl = '{base_path}{file}{suffix}.{ext}';

$fileVars = array(
    'base_path' => $basePath,
    'file' => $file,
    'suffix' => $suffix,
    'ext' => $ext,
);

$filename = Common::replaceByVars($fileNameTmpl, $fileVars);

if (!file_exists($filename)) {
    $fileVars['suffix'] = '';
    $filename = Common::replaceByVars($fileNameTmpl, $fileVars);
}

if (file_exists($filename)) {

    $headers = apache_request_headers();
    $filemtime = gmdate('D, d M Y H:i:s', custom_filemtime($filename)) . ' GMT';

    if (isset($headers['If-Modified-Since']) && ($headers['If-Modified-Since'] == $filemtime)) {
        header('Last-Modified: ' . $filemtime, true, 304);
    } else {

        header('Pragma: public');
        header('Cache-Control: max-age=86400');
        header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));

        header('Last-Modified: ' . $filemtime, true, 200);
        header('Content-Length: ' . filesize($filename));

        // Fix mime-type for IE on some server configurations
        if($ext == 'jpg') {
            $ext = 'jpeg';
        }

        header('Content-Type: image/' . $ext);
        echo file_get_contents($filename);
    }
} else {
    header("HTTP/1.0 404 Not Found");
}