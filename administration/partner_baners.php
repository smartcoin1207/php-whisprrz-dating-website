<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CPartnerBanners extends CHtmlBlock
{

	var $message = "";
	var $login = "";

	function action()
	{
		global $g, $l, $p;
		$cmd = get_param('cmd', '');
        $lang = get_param('lang');

		if ($cmd == "delete")
		{
			$file = DB::result("SELECT file FROM partner_banners WHERE id=" . to_sql(get_param("id", ""), "Number") . "");
                        $files = $g['path']['dir_files'] . "partner/" . $file;
                        Common::saveFileSize($files, false);
			@unlink($files);

			DB::execute("
				DELETE FROM partner_banners WHERE
				id=" . to_sql(get_param("id", ""), "Number") . "
			");
            redirect($p . '?action=delete');
		}
		elseif ($cmd == "edit")
		{
			DB::execute("
				UPDATE partner_banners
				SET
				name=" . to_sql(get_param("name", ""), "Text") . ",
				code=" . to_sql(get_param("code", ""), "Text") . ",
				size=" . to_sql(get_param("size", ""), "Text") . ",
				langs=" . to_sql($lang) . "
				WHERE id=" . to_sql(get_param("id", ""), "Number") . "
			");

			redirect("partner_baners.php?action=saved");

		}
		elseif ($cmd == "img")
		{
			$name = "fimg";

			$fileImageData = Common::uploadDataImageFromSetData(null, $name);
			$isImageUploadData = get_param_int('image_upload_data');

			if (isset($_FILES[$name]) && ($isImageUploadData ? file_exists($_FILES[$name]["tmp_name"]) : is_uploaded_file($_FILES[$name]["tmp_name"])))
			{

			// test file size

			$info = getimagesize($_FILES[$name]["tmp_name"]);

			if($info[0]>$g['image']['affiliates_banner_width'] || $info[1]>$g['image']['affiliates_banner_height'])
			{
				$this->message = isset($l['all']['file_too_big']) ? $l['all']['file_too_big'] : "File too big";
				return;
			}

                $ext = pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);
                if(!checkBannerExt($ext)) {
                    $this->message = "Incorrect file";
                    return;
                }

				$file = DB::result("SELECT file FROM partner_banners WHERE id=" . to_sql(get_param("id", ""), "Number") . "");
                                $files = $g['path']['dir_files']  . "partner/" . $file;
                                Common::saveFileSize($files, false);
				@unlink($files);

				$path_parts = pathinfo($_FILES[$name]['name']);
				$file = rand(10000, 99999) . "." . $path_parts['extension'];
                                $files = $g['path']['dir_files']  . "partner/" . $file;
				if ($fileImageData) {
					rename($_FILES[$name]['tmp_name'], $files);
				} else {
					move_uploaded_file($_FILES[$name]['tmp_name'], $files);
				}
				Common::saveFileSize($files);

				DB::execute("
					UPDATE partner_banners
					SET
					file='" . $file . "'
					WHERE id=" . to_sql(get_param("id", ""), "Number") . "
				");

				redirect("partner_baners.php?action=saved");

			}
		}
		elseif ($cmd == "add")
		{
			DB::execute("
				INSERT INTO partner_banners (name, code, size, langs)
				VALUES(
				" . to_sql(get_param("name", ""), "Text") . ",
				" . to_sql(get_param("code", ""), "Text") . ",
				" . to_sql(get_param("size", ""), "Text") . ",
				" . to_sql($lang) . ")
			");
            $id = DB::insert_id();

			$name = "fimg";
			if(isset($_FILES[$name]))
			{
				if (is_uploaded_file($_FILES[$name]["tmp_name"]))
				{

                    // test file size
                    $info = getimagesize($_FILES[$name]["tmp_name"]);

                    if($info[0]>$g['image']['affiliates_banner_width'] || $info[1]>$g['image']['affiliates_banner_height'])
                    {
                        $this->message = isset($l['all']['file_too_big']) ? $l['all']['file_too_big'] : "File too big";
                        return;
                    }

                    $ext = pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);
                    if(!checkBannerExt($ext)) {
                        $this->message = "Incorrect file";
                        return;
                    }

					$path_parts = pathinfo($_FILES[$name]['name']);
					$file = rand(10000, 99999) . "." . $path_parts['extension'];
                                        $files = $g['path']['dir_files']  . "partner/" . $file;
					move_uploaded_file($_FILES[$name]['tmp_name'], $files);
                                        Common::saveFileSize($files);
					DB::execute("
						UPDATE partner_banners
						SET
						file='" . $file . "'
						WHERE id=" . $id . "
					");
				}
			}
            redirect("partner_baners.php?action=saved");
		}
	}

	function parseBlock(&$html)
	{
		global $g;

		$html->setvar("message", $this->message);
		$html->setvar("add_baner", "<a href=\"{url_main}?p={id}\"><img src=\"{url_files}partner/{file}\" alt=\"\"></a>");
		$html->setvar("add_text", "<a href=\"{url_main}?p={id}\">text link</a>");
		DB::query("SELECT * FROM partner_banners ORDER BY id");

        $langs = Common::listLangs('partner');
        $langs = array('' => l('All')) + $langs;
        $isParseBanner = false;
		while ($row = DB::fetch_row())
		{
			foreach ($row as $k => $v)
			{
				$html->setvar($k, he($v));
			}
			if ($row['file'] != "")
			{
				$html->parse("img", false);
			}
			else
			{
				$html->setblockvar("img", "");
			}

            $langOptions = h_options($langs, $row['langs']);
            $html->setvar('language_opts', $langOptions);
			$isParseBanner = true;

			$html->parse("baner", true);
		}

		if (!$isParseBanner) {
			$html->parse('no_baner');
		}

        $langOptions = h_options($langs, '');
        $html->setvar('language_opts', $langOptions);

		parent::parseBlock($html);
	}
}

$page = new CPartnerBanners("", $g['tmpl']['dir_tmpl_administration'] . "partner_baners.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);


$page->add(new CAdminPageMenuPartner());

include("../_include/core/administration_close.php");