<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. 
This file is built by cobra.  --- 20200209*/
//   Rade 2023-09-23
include("../_include/core/administration_start.php");
include("../_include/current/nsc_club.option.php");

class CAdminDonation extends CHtmlBlock {

    function action()
    {   
		$cmd = get_param('cmd', '');
		$club_content = get_param('content');
		if ($cmd == 'update') {
			$club_title = get_param('club_title');
			$club_content = get_param('content');

			ClubOption::setBgPath('club_title', $club_title);
			ClubOption::setBgPath('club_content', $club_content);
			redirect();
		}
    }
    
    function parseBlock(&$html) {        
		$lang = Common::getOption('lang_loaded', 'main');
        
        $club_content = ClubOption::getBgPath('club_content');
        $html->setvar('club_content', $club_content);        

        parent::parseBlock($html);
    }

    private function fileupload()
    {
        $target_dir = "../_files/";
        $msg = '';
        $target_file = $target_dir . date("U") . '-' . basename($_FILES["background"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES["background"]["tmp_name"]);
            if($check !== false) {
                $msg = "File is an image - " . $check["mime"] . ".";
                $uploadOk = 1;
            } else {
                $msg = "File is not an image.";
                $uploadOk = 0;
            }
        }
        // Check file size
        if ($_FILES["background"]["size"] > 500000) {
            $msg = "Sorry, your file is too large.";
            $uploadOk = 0;
        }
        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            $msg = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["background"]["tmp_name"], $target_file)) {
                $msg = "The file ". basename( $_FILES["background"]["name"]). " has been uploaded.";
            } else {
                $msg = "Sorry, there was an error uploading your file.";
                $uploadOk = 0;
            }
        }
        
        if ($uploadOk == 1) {
			//var_dump($target_file);
			//die();
            return substr($target_file, (strlen($target_file) - 3) * (-1));
        }
        else {
            return NULL;
        }
    }
}

$page = new CAdminDonation('', $g['tmpl']['dir_tmpl_administration'] . 'nsc_club_admin.html');

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header_club_admin.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>