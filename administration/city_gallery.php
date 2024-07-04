<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminCityGalleryImages extends CHtmlBlock {
    function actionOne() {
        global $g;
        $cmd = get_param('cmd');
        if($cmd == 'upload' || $cmd == 'delete') {
            if ($cmd == 'delete') {
                $_FILES['upload_file']['name'] = '1px.png';
                $_FILES['upload_file']['type'] = 'image/png';
                $_FILES['upload_file']['tmp_name'] = $g['path']['dir_tmpl'].'common/images/1px.png';
            }
            $url = CityGallery::uploadImageCityGallery('upload_file', $g['path']['url_files'] . 'city/gallery/');
            if ($url) {
                $response['status'] = 1;
                $response['url'] = $url;
            } else {
                $response['status'] = 0;
                $response['msg'] = l('file_upload_failed');
            }
            die(json_encode($response));
        }
    }

    function parseBlock(&$html) {
        global $g;
        $location = get_param('loc', 3);
        $images = Common::getOption('list_images_' . $location, '3d_city_gallery');
        if ($images) {
            $url = $g['path']['url_files'] . 'city/gallery/';
            $dir = $g['path']['dir_files'] . 'city/gallery/';
            $images = json_decode($images, true);
            foreach ($images as $id => $hash) {
                $urlImage = $url . '1px.jpg';
                $exts = array('gif', 'jpg');
                foreach ($exts as $ext) {
                    $file = $dir . $id . '.' . $ext;
                    if (custom_file_exists($file)) {
                        $urlImage = $url . $id . '.' . $ext . '?v=' . $hash;
                        break;
                    }
                }
                $html->setvar('block', $id);
                $html->setvar('url_images', $urlImage);
                $html->setvar('rand', rand());
                $html->parse('block_image', true);
            }
        }
        parent::parseBlock($html);
    }

}

$ajax = get_param('ajax');
if ($ajax) {
    $page = new CAdminCityGalleryImages('', '', '', '', true);
    $page->actionOne();
    die();
}

$page = new CAdminCityGalleryImages('', $g['tmpl']['dir_tmpl_administration'] . 'city_gallery.html');

$location = get_param('loc', 3);
$items = new CAdminConfig('config_fields', $g['tmpl']['dir_tmpl_administration'] . '_config.html');
$items->setModule('3d_city_gallery_options_' . $location);
$page->add($items);

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuCity());

include("../_include/core/administration_close.php");