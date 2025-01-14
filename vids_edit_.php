<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = 'login';
include('./_include/core/main_start.php');
include('./_include/current/vids/start.php');

class CPage extends CHtmlBlock
{
    public $video = null;
    function init()
    {
        if (ipar('id') > 0) {
            $this->video = CVidsTools::getVideoById(ipar('id'));
            if ($this->video['user_id'] != guser('user_id')) {
                redirect('vids.php');
            }
        }
    }
	function action()
	{
        if ($this->video == null) {
            payment_check('video_upload');
        }
        $cmd = get_param('cmd');
        if (CVidsTools::validateVideoInfo() != '' || $cmd == 'video_edit') {
            if ($this->video == null) {
                CVidsTools::saveVideoInfoToSession();
                redirect('vids_upload.php');
            } else {
                CVidsTools::updateVideoById($this->video['id']);
                redirect('vids_collect.php?m=my');
            }
	}
	}
	function parseBlock(&$html)
	{
        $ch = ' checked="checked" ';

        if ($this->video == null) {
            $html->assign('check_public', $ch);
            $html->parse('upload');
            $html->parse('upload_button');
            $html->setvar('video_subject', l('Untitled'));
            $html->setvar('video_text', l('No description'));
            $html->setvar('video_tags', l('video'));
        } else {
            $this->video['subject'] = htmlentities($this->video['subject'], ENT_COMPAT, 'UTF-8');
            $this->video['text'] = htmlentities($this->video['text'], ENT_COMPAT, 'UTF-8');
            $this->video['tags'] = htmlentities($this->video['tags'], ENT_COMPAT, 'UTF-8');
            $html->assign('video', $this->video);

            if ($this->video['private']) $html->assign('check_private', $ch);
            else $html->assign('check_public', $ch);

            $html->parse('edit');
            $html->parse('edit_button');
        }

        $categories = CVidsTools::getCatsIdTitle();
        //$cats = CVidsTools::getCats();
        //$catsId = CVidsTools::getCatsId();
        //unset($catsId[0]);
        //unset($cats[0]);
        $cats = $categories['title'];
        $catsId = $categories['id'];

        $video_cat = mb_strtolower($this->video['cat'], 'UTF-8');
        $cat = explode(',', $video_cat);

        if (get_param('id') == ''){
            $cat = CVidsTools::getDefaultCats();
        }
        $block = ceil(count($cats) / 4);
        $d = 1;
        $r = 1;
        for ($i = 0; $i < $block; $i++) {
            for ($k = 0; $k < 4; $k++) {
                if ($d > count($cats)) {
                    break;
                }
                $html->assign('cat_name', l($cats[$d], false, 'vids_category'));
                $html->assign('cat_name2', $catsId[$d]);
                //$currentCat = mb_strtolower($cats[$d], 'UTF-8');
                if (in_array($catsId[$d], $cat)) {
                    $html->assign('checked', 'checked');
                }
                else {
                    $html->assign('checked', '');
                }
                $html->assign('i', $d);
                $html->parse('cat_item');
                $d++;
            }
            if ($r != 3) {
                $html->parse('last_off');
                $r++ ;
            } else {
                $html->parse('last_on');
                $r = 1;
            }
            $html->parse('cat');
            $html->clean('cat_name');
            $html->clean('cat_item');
            $html->setblockvar('last_on', '');
            $html->setblockvar('last_off', '');
        }

        parent::parseBlock($html);
	}
}

vids_render_page();
include('./_include/core/main_close.php');
