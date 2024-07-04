<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
include("./_include/current/gallery_functions.php");

payment_check('gallery_view');

$ajaxId = intval(get_param("id", "0"));
$image_id = intval(get_param("img_id", "0"));
if ($image_id == 0 && $ajaxId == 0)
{
	redirect('gallery_index.php');
}
$sql = "SELECT A.access, A.user_id, A.folder
          FROM gallery_albums AS A, gallery_images AS B
         WHERE A.id = B.albumid
           AND B.id = " . to_sql($image_id, 'Numeric');
$row = DB::row($sql);
if (guid() != $row['user_id']){
    if ($row['access'] == 'private'
        || ($row['access'] == 'friends' && !User::isFriend(guid(), $row['user_id']))) {
        redirect('gallery_index.php');
    }
}
if ($row['folder'] == 'chat_system') {
    redirect('gallery_index.php');
}

class CGallery_Image extends CHtmlBlock
{
	var $bIsError=false;
	function action()
	{
		global $g;
		global $g_user;

        parent::action();

        $this->bIsError=false;

		$make_comment=get_param("make_comment", "0");
		if($make_comment==0)
		{
			return;
		}
		if($g_user['user_id'] == 0)
		{
			redirect("join.php");
		}

		$name = isset($g_user['name']) ? $g_user['name'] : '';
		$email = "";
		$website = "";
		$comment = trim(get_param("comment", ""));

		if($comment){
		$comment = to_sql(to_html($comment));

		$date=date("Y-m-d H:i:s");

		$image_id=get_param("img_id", "0");
		$image_id=intval($image_id);
		if($image_id == 0)
		{
			redirect('gallery_index.php');
		}

        $comment = Common::filter_text_to_db($comment, false);
		$sql = "INSERT INTO `gallery_comments` (`user_id`, `imageid`, `name`, `email`, `website`, `date`, `comment`, `inmoderation`)
				VALUES (".$g_user["user_id"].", ".$image_id.", '".$name."', '".$email."', '".$website."', '".$date."', ".$comment.", 0)";

		DB::execute($sql);

        $cid = DB::insert_id();

        $sql = "SELECT A.access, A.user_id
                  FROM `gallery_albums` AS A, `gallery_images` AS B
                 WHERE A.id = B.albumid
                   AND B.id = " . to_sql($image_id, 'Numeric');
        $row = DB::row($sql);

        Wall::setSiteSectionItemId($image_id);
        Wall::add('pics_comment', $cid, false, '', false, 0, $row['access'], $row['user_id']);

		}


	}
	function parseBlock(&$html)
	{
		global $g_user;

		global $g;
		$html->setvar("gallery_image_title_length",$g['options']['gallery_image_title_length']);
		$html->setvar("gallery_image_description_length",$g['options']['gallery_image_description_length']);

		$html->setvar("commenter_name", isset($g_user["name"]) ? $g_user["name"] : "");
		$html->setvar("commenter_email", isset($g_user["mail"]) ? $g_user["mail"] : "");


		if($this->bIsError==true)
		{
			$html->parse("error", true);
		}
		$image_id=get_param("img_id", "0");
		$image_id=intval($image_id);
		if($image_id == 0) redirect('gallery_index.php');


		DB::query("SELECT i.*, a.folder, a.title as album, a.id as album_id FROM (gallery_images AS i LEFT JOIN gallery_albums AS a ON i.albumid=a.id) WHERE i.id=".$image_id."");
		$row=DB::fetch_row();

		if (is_array($row)) {
			$img_href= $g['dir_files'] . "gallery/images/".$row['user_id']."/".$row['folder']."/".$row['filename'];
			$html->setvar("image_src", $img_href);
            $html->setvar('image_width', $row['width']);
            $html->setvar('image_height', self::prepareImageHeigth($row));

			$author_photo_id = $row['user_id'] = intval($row['user_id']);
			$html->setvar("name_prof", DB::result("SELECT name FROM user WHERE user_id=" . $row['user_id'] . "", 0, 2));

            $html->setvar('uploaded_time_ago', timeAgo($row['datetime']));

			$html->setvar("album_id", $row['album_id']);

            if ($row['user_id'] == guid()) {
                $html->parse('edit_on', false);
            } else {
                $html->parse('edit_off', false);
            }
			DB::execute("UPDATE gallery_albums SET views=(views+1) WHERE id = " . $row['album_id'] . "");
			$html->setvar("album", $row['album']);

			DB::query("SELECT id FROM gallery_images WHERE albumid=".$row['album_id']."", 2);
			if(DB::num_rows(2)!=1) $link = true;
			else $link = false;
			while ($row2 = DB::fetch_row(2))
			{
				if (isset($thisp) and !isset($next)) {
					$next = $row2['id'];
				}
				if ($row2['id'] == $image_id) {
					$thisp = $image_id;
				}
				if (!isset($thisp)) {
					$prev = $row2['id'];
				}
				if (!isset($first)) {
					$first = $row2['id'];
				}
				$last = $row2['id'];
			}

			if (!isset($next)) {
				$next = $first;
			}
			if (!isset($prev)) {
				$prev = $last;
			}
			$html->setvar("next_id", $next);
			$html->setvar("prev_id", $prev);

			// MAKE DESCRIPTION EDITABLE BY OWNER
			if($author_photo_id==$g_user["user_id"])
			{
				$html->parse("delimiter", false);
                $html->setvar("image_id", $image_id);
				$html->parse("image_edit", false);
				$html->setvar("make_image_desc_editable", gallery_printDesc($image_id));
				$html->setvar("make_image_title_editable", gallery_printTitle($image_id));
				$html->setvar("ttl_cursor","cursor:pointer;");
			}

			//$img_desc=DB::result("SELECT `desc` FROM `gallery_images` WHERE id=".$image_id."", 0, 1);

			$img_desc = to_html($row["desc"]);
			if(trim($img_desc) && trim(to_html($row["title"]))) $html->parse("delimiter", false);

			$html->setvar("image_description", $img_desc);
			$html->setvar("image_description_short", neat_trim($img_desc, 50));
			$html->setvar("image_title", to_html($row["title"]));

			if($link==true) {
                $html->parse('yes_pagination');
                $html->setvar('dash', '&nbsp; | &nbsp;');
                $html->parse('link');
                $html->parse('link2');
                $html->setvar('alt', l('alt'));
            }


			DB::query("SELECT * FROM gallery_comments WHERE imageid=".$image_id." ORDER BY date DESC");
			$count=DB::num_rows();

			$html->setvar("num_comments", $count);

			for($i=0; $i<$count; $i++)
			{
				if ($row = DB::fetch_row())
				{
					$row['user_id'] = intval($row['user_id']);
					$name=DB::result("SELECT name FROM user WHERE user_id=".$row['user_id']."", 0, 1);

DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id='".$row['user_id']."' LIMIT 0, 1",2);
$row_user = DB::fetch_row(2);

$name = $row_user['name'];

					$user_photo = User::getPhotoDefault($row['user_id'],"r");

					$html->setvar("photo", $user_photo);

					if($row['website']=="")
					{
						$html->setvar("user_www", "javascript:void(0);");
					}
					else
					{
						$html->setvar("user_www", "http://".$row['website']);
					}
                    $html->setvar("comment_id",$row['id']);
                    $html->setvar("pid",$author_photo_id);

                    if (($row['user_id'] == guid()) or ($author_photo_id == guid())){
                        $html->parse("delete_comment", FALSE);
                    } else {
                        $html->setblockvar('delete_comment', '');
                    }
					$html->setvar("date", Common::dateFormat($row['date'], 'user_comment_date'));
					$html->setvar("comment_text", to_html(Common::parseLinksSmile($row['comment']), true, true));
					$html->setvar("user_name", $name);

					if ($name == "")
					{
						$html->parse("anonim_comment", false);
						$html->setblockvar("user_comment", "");
					}
					else
					{
						$html->parse("user_comment", false);
						$html->setblockvar("anonim_comment", "");
					}

					$html->setvar("num",$i);
                    $html->setvar("cid",$row['id']);
					$html->setvar("user_age",$row_user['age']);
					$html->setvar("user_country_sub",$row_user['country']);
					$html->parse("show_info", true);

					$html->parse("comment", true);
				}
			}
			parent::parseBlock($html);
		} else {
			redirect('gallery_index.php');
		}
	}

    public static function prepareImageHeigth($row)
    {
        $height = $row['height'];

        $maxWidth = 700;

        if(strpos($row['filename'], '.gif') !== false && $row['width'] > $maxWidth) {
            $height = $height * ($maxWidth / $row['width']);
        }

        return $height;
    }
}

// SEO

DB::query("SELECT `title`, `desc` FROM gallery_images WHERE id=".to_sql(get_param("img_id"),"Number"));
$meta = DB::fetch_row();
if(trim($meta['title'])!="")$g['main']['title'] = $g['main']['title'] . ' :: ' . $meta['title'];
$g['main']['description'] = addslashes(strip_tags($meta['desc']));

// SEO

$page = new CGallery_Image("gallery_index", $g['tmpl']['dir_tmpl_main'] . "gallery_image.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);




include("./_include/core/main_close.php");

?>