<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

// City::accessCheck();

class CPage extends City
{

	function parseBlock(&$html)
	{
		global $g;
		global $g_user;

		foreach ($g_user as $k => $v) $html->setvar($k, $v);

        $html->setvar('city_url_page', City::url('city', true));

        parent::parseBlock($html);
	}
}

class CHeadCity extends CHeader
{
	function parseBlock(&$html)
	{
        $isMobileCity = get_param('view') == 'mobile';
        $optionTmplName = Common::getTmplName();
		$apiKey = CityMap::getKeyMap();
		if ($apiKey) {
			$html->setvar('google_maps_api_key_for_city', '?key=' . $apiKey);
		}

        if ($optionTmplName == 'edge') {
            if ($isMobileCity) {
                $html->parse('city_set_viewport_js', false);
            }
        } else {
            $html->parse('landscape_css', false);
        }

		CityLiveStreaming::parseDataInit($html);
		CityBase::initDataAudioChat($html);

		parent::parseBlock($html);
	}
}

$optionTmplSet = Common::getOption('set', 'template_options');
$optionTmplName = Common::getTmplName();

$tmplList = array('main' => $g['tmpl']['dir_tmpl_main'] . "city.html",
                  'city' => $g['tmpl']['dir_tmpl_city'] . 'city.html',
                  'list_users_chat' => $g['tmpl']['dir_tmpl_city'] . '_list_user.html',
                  'list_msg_users_chat' => $g['tmpl']['dir_tmpl_city'] . '_list_msg.html');

$isMobileCity = get_param('view') == 'mobile';
if ($optionTmplName == 'edge' && $isMobileCity) {
    $tmplList['list_users_chat_item'] = $g['tmpl']['dir_tmpl_city'] . '_list_user_item.html';
    $tmplList['list_msg_item'] = $g['tmpl']['dir_tmpl_city'] . '_list_msg_item.html';
}

$page = new CPage("", $tmplList);

if ($optionTmplName == 'edge' && $isMobileCity) {
    global $isMobile;
    global $numberOpenChats;

    $page::$isMobile = true;
    $page::$numberOpenChats = 5;
}
$page->isLoadCity = true;

$tmplListHeader = array('main' => $g['tmpl']['dir_tmpl_main'] . "_header.html",
                        'include_city' => $g['tmpl']['dir_tmpl_city'] . '_include.html');
if ($optionTmplSet != 'urban'){
    $tmplListHeader['include_custom_city'] = $g['tmpl']['dir_tmpl_city'] . '_include_custom.html';
}
if ($optionTmplName == 'edge') {
    $tmplListHeader['pp_messages'] = $g['tmpl']['dir_tmpl_main'] . 'pp_messages.html';
}

$header = new CHeadCity("header", $tmplListHeader);
$page->add($header);


$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . '_footer.html');
$page->add($footer);

include("./_include/core/main_close.php");