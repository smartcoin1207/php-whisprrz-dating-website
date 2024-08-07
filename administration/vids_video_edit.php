<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
require_once("../_include/current/vids/includes.php");

class CPage extends CHtmlBlock
{
    public $video = null;
    function init()
    {
		global $g;

        $pid = ipar('id');
        if ($pid > 0) {
			$g['options']['video_player_type'] = 'player_native';
            $this->video = CVidsTools::getVideoById($pid, true);
        }
        if (!isset($this->video) or !is_array($this->video)) {
            redirect('vids_videos.php');
        }
    }
	function action()
	{
        $cmd = get_param("cmd");
        if ($cmd == "delete") {
            CVidsTools::delVideoById(ipar('id'), true);
            redirect("vids_videos.php");
        } elseif ($cmd == 'update') {
            CVidsTools::updateVideoById($this->video['id'], true);
            redirect("vids_videos.php?action=saved");
		}
	}
	function parseBlock(&$html)
	{
        if (Common::getOption('video_player_type') == 'player_custom') {
            $html->parse('player_custom', false);
        }

        $this->video['subject'] = he($this->video['subject']);
        $this->video['tags'] = he($this->video['tags']);
        $html->assign('video', $this->video);

        $ch = ' checked="checked" ';
        $lang = loadLanguageAdmin();
        if ($this->video['private'] == 'Y')  // Divyesh - Added - 17042024
            $html->assign('check_private', $ch);
        else
            $html->assign('check_public', $ch);

        if($this->video['active']==3){
            $html->setvar('check_approval', $ch);
        }
        /* Divyesh - Added - 17042024 */
        /*$cat = explode(',', $this->video['cat']);
        $categories = CVidsTools::getCatsIdTitle();
        $cats =  $categories['title'];
        $catsId =  $categories['id'];
        $i = 1;
        foreach($catsId as $num => $item) {
                $html->setvar('cat_name', l($cats[$num], $lang, 'vids_category'));
                $html->setvar('cat_name2', $item);
                if (in_array($item, $cat)) {
                    $html->setvar('checked', 'checked');
                } else {
                    $html->assign('checked', '');
                }
                $i++;
                $html->parse('cat');
                $html->setvar('i',$i);
        }*/
        /* Divyesh - Added - 17042024 */

        //if(Common::getTmplSet() == 'old') {
            $html->parse('details');
        //}

		parent::parseBlock($html);
	}
}

$page = new CPage("main", $g['tmpl']['dir_tmpl_administration'] . "vids_video_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuVids());

include("../_include/core/administration_close.php");
