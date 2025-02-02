<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
$area = "login";
include('./_include/core/main_start.php');

class CLookingGlass extends CHtmlBlock
{
    function init()
    {
        global $g;
    }

    function parseBlock(&$html)
    {
        global $g_user;
        $guid = guid();
        $uid = User::getParamUid(0);

        $pageTitle = l('looking_glass_photos');
        $mediaPageTitle = l('photos');
        $pageDescription = '';
        $pageClass = 'photos_list';

        $pageUrl = Common::pageUrl('photos_list');
        $optionTmplName = Common::getTmplName();
		$isEdge = $optionTmplName == 'edge';

        $vars = array(
            'page_number' => 1,
            'page_class'  => $pageClass,
            'page_title'  => $pageTitle,
            'page_description' => $pageDescription,
            'page_user_id'     => $uid,
            'page_guid' => $guid,
            'url_pages' => $pageUrl,
            'page_type' => 'photos',
            'page_filter' => intval(!$uid),
            'group_id'    => '',
            'group_type'  => '',
        );

        $pagerOnPage = Common::getOptionInt('looking_glass_display_photo_count', "options");
        $mOnBar = Common::getOption('usersinfo_pages_per_list', 'template_options');
        $limit = $pagerOnPage;

        CProfilePhoto::$isGetDataWithFilter = true;
        $itemsTotal = CProfilePhoto::getTotalAccessedPhotos($uid, '');
        $html->assign('', $vars);

        $class = "Template{$optionTmplName}";        
        $rows = $class::parseListPhotos($html, '', $limit, '', true);
        if ($rows) {
            Common::parsePagesListUrban($html, 1, $itemsTotal, $pagerOnPage, $mOnBar, $pageUrl, 'page', '');
        } else {
            $html->parse("list_noitems");
        }

        $html->setvar('looking_glass_group_title', $pageTitle);
        $html->setvar('media_page_link', $pageUrl);
        $html->setvar('media_page_title', $mediaPageTitle);

        $html->parse('photo_items', false);
        $html->parse('looking_glass_group');
        $html->clean('photo_items');

        //---------------------------------------------------------------------------------


        $page = get_param_int('page');
        $page = 1;

        $pageTitle = l('videos');
        $pageDescription = '';
        $pageClass = 'videos_list';

		$pageUrl = Common::pageUrl('vids_list');
		
        $vars = array('page_number' => $page,
                      'page_class'  => $pageClass,
                      'page_title'  => $pageTitle,
                      'page_description' => $pageDescription,
                      'page_user_id'     => $uid,
                      'page_guid' => $guid,
                      'url_pages' => $pageUrl,
                      'page_type' => 'videos',
                      'page_filter' => intval(!$uid),
                      'group_id'    => '',
                      'group_type'  => ''
                );

        $pagerOnPage = Common::getOptionInt('looking_glass_display_video_count');
        $mOnBar = Common::getOption('usersinfo_pages_per_list', 'template_options');
        $limit = $pagerOnPage;

        $block = 'list_video';
        $class = "Template{$optionTmplName}";

        $itemsTotal = CProfileVideo::getTotalVideos($uid, '', true, '');

        if ($html->varExists('auto_play_video')) {
            $html->setvar('auto_play_video', Common::isOptionActive('video_autoplay')?'autoplay':'');
        }

        $html->assign('', $vars);

        $rows = $class::parseListVideos($html, '', $limit, $block, '', true);

        if ($rows) {
            Common::parsePagesListUrban($html, $page, $itemsTotal, $pagerOnPage, $mOnBar, $pageUrl);
        } else {
            $html->parse("list_noitems");
        }

        $html->setvar('looking_glass_group_title', $pageTitle);
        $html->setvar('media_page_link', $pageUrl);
        $html->setvar('media_page_title', $mediaPageTitle);

        $html->parse('video_items', false);
        $html->parse('looking_glass_group');

        parent::parseBlock($html);
    }
}

$dirTmpl = $g['tmpl']['dir_tmpl_main'];
$tmplList = array(
    'main'   => $dirTmpl . 'looking_glass.html',
    'photo_item'   => $dirTmpl . '_list_photos_item.html',
    'video_item'   => $dirTmpl . '_list_vids_item.html'
);

$page = new CLookingGlass("", $tmplList);

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include('./_include/core/main_close.php');
