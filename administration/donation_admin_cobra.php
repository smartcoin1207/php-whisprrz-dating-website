<?php

/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. 
This file is built by cobra.  --- 20200209*/
//   Rade 2023-09-23
include("../_include/core/administration_start.php");
include("../_include/current/donation.option.php");

class CAdminDonation extends CHtmlBlock {

    function action()
    {
        global $p;
        $cmd = get_param('cmd', '');

        if ($cmd == 'update') {
            $module = get_param('module');
            $options = get_param_array('option');
            Config::updateAll($module, $options);
            redirect($p . '?action=saved');
        }
		
		if ($cmd == 'update_donation_bg') {
            $fp = $this->fileupload() ;
            if ($fp != NULL) {
                // settin new file path to db
                DonationOption::setBgPath('background_filepath', $fp);
            }
            else {
                return;
            }
            redirect();
        }
        
        if ($cmd == 'update_art') {
            $art_title = get_param('art_title');
            $art_content = get_param('art_content');

            DonationOption::setBgPath('art_title', $art_title);
            DonationOption::setBgPath('art_content', $art_content);
            redirect();
        }
    }
    
    function parseBlock(&$html) {
        global $g;

        $paymentModules = $g['payment_modules'];

        foreach ($paymentModules as $paymentModule => $values) {

            if(l($paymentModule)==="PayPal"){
				$config = Config::getOptionsAll($paymentModule, 'position', 'ASC', true);
				$html->setvar('module', $paymentModule);
				$html->setvar('payment', l($paymentModule));			
				foreach ($config as $key => $row) {
					foreach ($row as $k => $v) {
						$html->setvar($k, $v);
					}				
					$html->setvar('label', l(ucfirst(str_replace("_", " ", $key))));
					$field = $row['type'];

					if ($field == 'checkbox') {
						$checked = '';
						if ($row['value'] == 1 || $row['value'] == 'Y') {
							$checked = 'checked';
						}
						$html->setvar('checked', $checked);
					}

					$html->parse('donation_' . $field, true);

					$html->parse('donation');
					$html->setblockvar('donation_' . $field, '');
				}
				
				$html->parse("pay", true);
				//$html->setblockvar('donation', '');
			}
        }
		$lang = Common::getOption('lang_loaded', 'main');
        
        $bg_path = DonationOption::getBgPath('background_filepath');
        $html->setvar('bg_path', $bg_path);

        $art_title = DonationOption::getBgPath('art_title');
        $art_content = DonationOption::getBgPath('art_content');
        $html->setvar('art_title', $art_title);
        $html->setvar('art_content', $art_content);

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

$page = new CAdminDonation('', $g['tmpl']['dir_tmpl_administration'] . 'donation_admin.html');

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>