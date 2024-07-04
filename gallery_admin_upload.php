<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/gallery_functions.php");
include("./_include/current/image_functions.php");
include("./_include/current/menu_section.class.php");
payment_check('gallery_edit');

class CGallery_AdminUpload extends CHtmlBlock
{
    var $bIsError=false;
    var $error_message="";
    function action()
    {
        global $g;
        global $l;
        global $g_user;
        parent::action();

        $action=get_param("action", "");

        if($action=="upload")
        {
            $time = DB::result('SELECT NOW()');

            $files_empty = true;
            if (isset($_FILES['files']))
                foreach ($_FILES['files']['name'] as $name) {
                    if (!empty($name)) $files_empty = false;

                }
            if (isset($_POST['processed']) && !$files_empty) {
                $albumselect = intval(get_param('albumselect'));
                if ($albumselect != '' && ($g['path']['dir_files'] . 'gallery/images/' . $g_user['user_id'] . '/' . $albumselect)) {
                    $folder = $albumselect;
                } else {
                    $folder=1;
                    while (custom_file_exists($g['path']['dir_files'] . 'gallery/images/' . $g_user['user_id'] . '/' . $folder)) {
                        $folder++;
                    }
                }

                $uploaddir = $g['path']['dir_files'] . 'gallery/images/' . $g_user['user_id'] . '/' . $folder;
                $uploaddir = str_replace("\\", "/", $uploaddir);

                $uploaddir_thumb = $g['path']['dir_files'] . 'gallery/thumb/' .$g_user['user_id'] . '/' . $folder;
                $uploaddir_thumb = str_replace('\\', '/', $uploaddir_thumb);

                $user_dir_img = $g['path']['dir_files'] . 'gallery/images/' .$g_user['user_id'];
                $user_dir_img = str_replace("\\", "/", $user_dir_img);

                $user_dir_tmb = $g['path']['dir_files'] . 'gallery/thumb/' . $g_user['user_id'];
                $user_dir_tmb = str_replace("\\", "/", $user_dir_tmb);

                if (!is_dir($user_dir_img)) {
                    mkdir ($user_dir_img, 0777);
                }
                if (!is_dir($user_dir_tmb)) {
                    mkdir ($user_dir_tmb, 0777);
                }
                if (!is_dir($uploaddir)) {
                    mkdir ($uploaddir, 0777);
                }
                if (!is_dir($uploaddir_thumb)) {
                    mkdir ($uploaddir_thumb, 0777);
                }
                @chmod($uploaddir, 0777);
                @chmod($uploaddir_thumb, 0777);

                $files_count = count($_FILES['files']['name']);

                #p($_FILES);

                $error = false;
                for($i=0; $i < $files_count; $i++)
                {
                    if ($_FILES['files']['name'][$i] == "") continue;
                    #p('1111 ');
                    //foreach ($_FILES['files']['error'] as $key => $error) {
                    //if ($_FILES['files']['name'][$key] == "") continue;
                    //if ($error == UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES['files']['tmp_name'][$i];
                    $namei = 0;
                    do {
                        $namei++;
                    } while ( custom_file_exists($uploaddir . '/' . $namei . '.jpg'));
                    $name = $namei . '.jpg';
                    $nameSrc = $namei . '_src.jpg';

                    if (is_image($name)) {
                        $uploadfile = $uploaddir . '/' . $name;
                        $uploadfileSrc = $uploaddir . '/' . $nameSrc;
                        $im = new Image();

                        $imWidth = 0;
                        $imHeight = 0;

			if ($im->loadImage($tmp_name)) {
                            // NO RESIZE IF SMALLER THEN gallery_width
                            $imWidth = $im->getWidth();

                            if($imWidth > $g['image']['gallery_width']) {
                                $imWidth = $g['image']['gallery_width'];
                            }
                            $im->resizeWH($imWidth, $g['image']['gallery_height'], false, $g['image']['logo'], $g['image']['logo_size']);
                            $imHeight = $im->getHeight();
                            $imWidth = $im->getWidth();
                            $im->saveImage($uploadfile, $g['image']['quality']);
                        }
                        if ($im->loadImage($tmp_name)){
                            $im->saveImage($uploadfileSrc, $g['image']['quality_orig']);
                        }
                        @chmod($uploadfile, 0777);

                        $thumbfile = $uploaddir_thumb. '/' . $name;
                        $im = new Image();
                        if ($im->loadImage($uploadfile)) {
                            CStatsTools::count('pics_uploaded');
                            $im->resizeCropped(100, 100, $g['image']['logo'], 0);
                            $im->saveImage($thumbfile, $g['image']['quality']);
                        }
                        Common::saveFileSize(array($uploadfile, $thumbfile, $uploadfileSrc));
                        $id = DB::result('SELECT id
                                            FROM gallery_albums
                                           WHERE folder=' . to_sql($folder) . ' AND user_id=' . to_sql($g_user['user_id'], 'Number'));

                        if($id == 0) {
                            //New album required
                            // Creating
                            $album_title = get_param('albumtitle', '');
                            $date = date('Y-m-d H:i:s');
                            $sql = "INSERT INTO `gallery_albums` ( `user_id` , `parentid` , `folder` , `title` , `desc` , `date` , `place` , `show` , `thumb` , `sort_type` , `sort_order` , `views` )
                                    VALUES ('" . $g_user['user_id'] . "', NULL, " . to_sql($folder) . ', ' . to_sql(he($album_title)) . ", '', '" . $date . "', '' , '1', '" . $name . "', NULL , NULL , '0')";
                            DB::execute($sql);
                            $id = DB::insert_id();
                            $access = 'public';
                        } else {
                            $access = DB::result('SELECT `access`
                                                    FROM `gallery_albums`
                                                   WHERE `folder` = '.to_sql($folder)
                                                 . ' AND `user_id` = ' . $g_user['user_id']);
                        }

                        $table = 'gallery_images';
                        $row = array(
                            'user_id' => guid(),
                            'albumid' => $id,
                            'filename' => $name,
                            'width' => $imWidth,
                            'height' => $imHeight,
                            'datetime' => $time,
                            'title' => '',
                            'desc' => '',
                        );
                        DB::insert($table, $row);
                        $imgId = DB::insert_id();

                        Wall::addItemForUser($imgId, 'pics', guid());

                        Wall::add('pics', $id, false, $time, true, 0, $access, true);

                    }

                }
                redirect('gallery_admin_edit_album.php?album_id=' . (isset($id) ? $id : '') . "");

            }else {
				// Handle the error and return to the upload page.

				$this->bIsError = true;

				if ($files_empty) {
					$a = isset($l['all'][to_php_alfabet("You must upload at least one file.")]) ? $l['all'][to_php_alfabet("You must upload at least one file.")] : "You must upload at least one file.";
				  $this->error_message = $a;
				} else if (empty($_POST['albumtitle'])) {
					$a = isset($l['all'][to_php_alfabet("You must enter a title for your new album.")]) ? $l['all'][to_php_alfabet("You must enter a title for your new album.")] : "You must enter a title for your new album.";
				  $this->error_message = $a;
				} else if (empty($_POST['folder'])) {
					$a = isset($l['all'][to_php_alfabet("You must enter a folder name for your new album.")]) ? $l['all'][to_php_alfabet("You must enter a folder name for your new album.")] : "You must enter a folder name for your new album.";
				  $this->error_message = $a;
				} else if (empty($_POST['processed'])) {
					$a = isset($l['all'][to_php_alfabet("You've most likely exceeded the upload limits.")]) ? $l['all'][to_php_alfabet("You've most likely exceeded the upload limits.")] : "You've most likely exceeded the upload limits.";
				  $this->error_message = $a;

				} else {
				  $this->error_message = "There was an error submitting the form";
				}
			  }
		}
	}
	function init()
	{
	 	parent::init();
		global $g;
		global $g_user;
	}
	function parseBlock(&$html)
	{
		global $g_user;
		global $g;

        $idAlbum = get_param('id_album', '');

		$html->setvar("gallery_album_title_length",$g['options']['gallery_album_title_length']);
		$html->setvar("gallery_album_description_length",$g['options']['gallery_album_description_length']);
        $html->setvar('max_file', ini_get('max_file_uploads'));
		if($this->bIsError)
		{
			$html->setvar("error_message", $this->error_message);
			$html->parse("error", false);
		}

		//$html->setvar("max_filesize", ini_get('upload_max_filesize'));
        $html->setvar('max_filesize', Common::getOption('photo_size'));

        $where = '`user_id` = ' . to_sql($g_user['user_id'], 'Number') . 
                 ' AND `folder` != 0 ';
        $where = CGalleryAlbums::getCustomWhere($where, '');
        $sql = 'SELECT *
                  FROM `gallery_albums`
                 WHERE ' . $where . ' ORDER BY `id` DESC';
		DB::query($sql);
		$count = DB::num_rows();
		for ($i=0; $i<$count; $i++) {
			if ($row = DB::fetch_row()) {
				if($i!=$count-1) {
					$html->setvar("coma", ",");
				} else {
					$html->setvar("coma", "");
				}

                if ($idAlbum == $row['id'])
                    $html->setvar('selected', 'selected');
                else
                    $html->setvar('selected', '');
				$html->setvar("album_folder", $row["folder"]);
				$html->setvar("album_title", $row["title"]);
				$html->parse("array", true);
				$html->parse("album", true);
			}
		}
		parent::parseBlock($html);
	}
}


$page = new CGallery_AdminUpload("gallery_index", $g['tmpl']['dir_tmpl_main'] . "gallery_admin_upload.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$galleryMenu = new CMenuSection('gallery_menu', $g['tmpl']['dir_tmpl_main'] . "_gallery_menu.html");
$galleryMenu->setActive('admin_upload');
$page->add($galleryMenu);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);


include("./_include/core/main_close.php");

?>
