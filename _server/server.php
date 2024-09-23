<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['no_headers'] = true;
$g['to_root'] = "../";
$area = "test";
include($g['to_root'] . "_include/core/main_start.php");
function widgetShortName($name)
{
    $nameShort = $name;
    $nameParts = explode(' ', $name);
    if(count($nameParts) > 1) {
        $nameShort = $nameParts[0] . '...';
    }

    return $nameShort;
}

function widgetShortNameHard($name, $n = 9)
{
    $name = User::nameShort($name);
    if (mb_strlen($name, 'UTF-8') > $n)
            $name = hard_trim($name, $n);
    return $name;
}
// DEMO: widgets

if (defined('IS_DEMO') && IS_DEMO) {
    define('WIDGET_DEMO_WHERE', ' AND session = "' . addslashes(session_id()) . '" ');
    define('WIDGET_DEMO_INSERT', ', session = "' . addslashes(session_id()) . '", session_date = NOW()');
} else {
    define('WIDGET_DEMO_WHERE', '');
    define('WIDGET_DEMO_INSERT', '');
}

$widgetDemoOffsetTop = 27;

if(Common::getOption('tmpl_loaded', 'tmpl') == 'oryx') {
    $widgetDemoOffsetTop = 60;
}
define('WIDGET_DEMO_OFFSET_TOP', $widgetDemoOffsetTop);
define('WIDGET_DEMO_OFFSET_LEFT', '280');
define('WIDGET_DEMO_OFFSET_LEFT_ORYX', '40');
define('WIDGET_DEMO_OFFSET_LEFT_MIXER', '100');

$widgetHeightBase = 196;

$widgetHeights = array(
    'default' => $widgetHeightBase,
    'Firefox' => $widgetHeightBase,
    'Chrome' => $widgetHeightBase - 1,
    'MSIE' => $widgetHeightBase + 2,
);

$widgetHeightDeltas = array(
    'default' => -1,
    'Firefox' => -1,
    'Chrome' => 0,
    'MSIE' => -2,
);

$widgetHeightDeltasLeft = array(
    'default' => -8,
    'Firefox' => -8,
    'Chrome' => -7,
    'MSIE' => -9,
);

$widgetHeightDeltasLeft = $widgetHeightDeltas;

$browserInfo = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'default';

$browser = 'default';

foreach($widgetHeights as $browserCheck => $v) {
    if(strstr($browserInfo, $browserCheck) !== false && $browserCheck != 'default') {
        $browser = $browserCheck;
        break;
    }
}



$g['widget_demo_order'] = array(1, 5, 2, 4);
$g['widget_demo_left_order'] = array(8, 6);

$g['widget_demo_order'] = array(1, 6, 8, 4);
$g['widget_demo_left_order'] = array(7, 5, 2, 3);

// social mode - add widgets
// not use last widget bottom offset
// check only direct link param and save mode in session!!!
#var_dump(Common::getOption('home_page_mode'));


#var_dump($g['widget_demo_order']);

$imWidthOffset = 374;
if($browser == 'MSIE') {
    $imWidthOffset = 379;
}
define('IM_WIDTH_OFFSET', $imWidthOffset);
define('WIDGET_LEFT_OFFSET', 141);
define('WIDGET_LEFT_OFFSET_MIXER', 279);
define('WIDGET_LEFT_OFFSET_ORYX', 144);
define('WIDGET_DEMO_TEST', false);

// DEMO: widgets

$widgetHeight = $widgetHeights[$browser];

$widgetLeftTopOffset = array(
    'default' => 0,
    'Firefox' => 0,
    'Chrome' => 0,
    'MSIE' => 4,
);

$widgetLeftTopOffsetValue = 410;
if(Common::getOption('home_page_mode') == 'social') {
    $widgetHeights['Chrome'] = 196;
    //$g['widget_demo_order'][] = 3;
    //$g['widget_demo_left_order'][] = 7;
    $widgetHeightDeltas[$browser] = 0;
    $widgetHeightDeltasLeft[$browser] = 0;
    $widgetLeftTopOffsetValue = $widgetLeftTopOffsetValue + $widgetLeftTopOffset[$browser];
} else {
    $widgetHeight -= 5;
}

$widgetLeftTopOffsetValue = WIDGET_DEMO_OFFSET_TOP;

define('WIDGET_DEMO_OFFSET_TOP_LEFT', $widgetLeftTopOffsetValue);
define('WIDGET_DEMO_HEIGHT', $widgetHeight);
define('WIDGET_DEMO_HEIGHT_DELTA', $widgetHeightDeltas[$browser]);
define('WIDGET_DEMO_HEIGHT_DELTA_LEFT', $widgetHeightDeltasLeft[$browser]);
define('WIDGET_DEMO_CLEAN_AFTER_DAYS', '3');



// FIX: addAppend must be executed before JS
global $js;
$js = "";

class Server_Timer {

    static $time = 0;
    static $executionLimit = 30;

    static function getTime()
    {
        if(self::$time === 0) {
            self::$time = time();
        }

        return self::$time;
    }

    static function getExecutionLimit()
    {
        return self::$executionLimit;
    }

    static function check()
    {
        if(time() - self::getTime() > self::getExecutionLimit()) {
            die('TIMEOUT');
        }
    }

}

// init timer
Server_Timer::check();
// better to init database queries timer

function to_js($html) {
	//return json_encode($html);
	return str_replace(array("\\","'","\n","\r"), array("\\\\","\\'","\\\n",""), trim($html));
}
function addOption($select, $option)
{
	$r = "";
	$r .= "var " . $select . ";";
	$r .= "var " . $select . "_option;";
	$r .= "" . $select . " = document.getElementById(\"" . $select . "\");";
	foreach ($option as $k => $v)
	{
		$r .= "" . $select . "_option = document.createElement(\"option\");";
		$r .= "" . $select . "_option.setAttribute(\"value\",\"" . $k . "\");";
		$r .= "" . $select . "_option.appendChild(document.createTextNode(\"" . $v . "\"));";
		$r .= "" . $select . ".appendChild(" . $select . "_option);";
	}
	return $r;
}

function countries()
{
	global $db;
	global $l;
	$objResponse = new xajaxResponse();
	$js = "";
	$objResponse->addClear("country", "innerHTML");
	$objResponse->addAlert("country");
	DB::query("SELECT country_id, country_title FROM geo_country WHERE hidden = 0"); # ORDER BY country_id
	$option = array('0' => isset($l['all']['select_all']) ? $l['all']['select_all'] : '');
	while ($row = DB::fetch_row()) {
		$option[$row['country_id']] = (isset($l['all'][to_php_alfabet($row['country_title'])]) ? $l['all'][to_php_alfabet($row['country_title'])] : $row['country_title']);
	}
	$js .= addOption("country", $option);
	$objResponse->addClear("state", "innerHTML");
	$country_id = DB::result("SELECT country_id FROM geo_country WHERE country_id!=-1 AND hidden = 0 LIMIT 1");
	DB::query("SELECT state_id, state_title FROM geo_state WHERE country_id=" . to_sql($country_id, "Number") . " AND hidden = 0 ORDER BY state_title");
	$option = array('0' => isset($l['all']['select_all']) ? $l['all']['select_all'] : '');
	while ($row = DB::fetch_row()) {
		$option[$row['state_id']] = (isset($l['all'][to_php_alfabet($row['state_title'])]) ? $l['all'][to_php_alfabet($row['state_title'])] : $row['state_title']);
	}
	$js .= addOption("state", $option);
	$objResponse->addClear("city", "innerHTML");
	$state_id = DB::result("SELECT state_id FROM geo_state WHERE country_id=" . to_sql($country_id, "Number") . " AND hidden = 0 ORDER BY state_title LIMIT 1");
	DB::query("SELECT city_id, city_title FROM geo_city WHERE state_id=" . to_sql($state_id, "Number") . " AND hidden = 0 ORDER BY city_title");
	$option = array('0' => isset($l['all']['select_all']) ? $l['all']['select_all'] : '');
	while ($row = DB::fetch_row()) {
		$option[$row['city_id']] = (isset($l['all'][to_php_alfabet($row['city_title'])]) ? $l['all'][to_php_alfabet($row['city_title'])] : $row['city_title']);
	}
	$js .= addOption("city", $option);
	$objResponse->addScript($js);
	return $objResponse;
}
function states($country_id, $selectAll = 1)
{
	global $l;
    $selectAll = intval($selectAll);
	$objResponse = new xajaxResponse();
	$objResponse->addClear("state", "innerHTML");
	$js = "";
    $option = array('0' => $selectAll ? l('select_all') : '- ' . l('state') . ' -');
	DB::query("SELECT state_id, state_title FROM geo_state WHERE country_id=" . to_sql($country_id, "Number") . " AND hidden = 0 ORDER BY state_title");
	while ($row = DB::fetch_row()) {
		$option[$row['state_id']] = (isset($l['all'][to_php_alfabet($row['state_title'])]) ? $l['all'][to_php_alfabet($row['state_title'])] : $row['state_title']);
	}

	$sql = "SELECT code FROM geo_country WHERE country_id=" . to_sql($country_id, "Number") . " LIMIT 1";
	DB::query($sql);
	$country_code = DB::fetch_row();

	// var_dump($country_code[0]); die(); exit;

	$js .= "current_country_code = '".$country_code[0]."'; ";

	$js .= addOption("state", $option);
	// MOD new selects
    $js .= " if(!jqTransformDaySelect) {";
	$js .= "document.getElementById('state').unload();";
	$js .= "selects(document.getElementById('state'));";
	$js .= "document.getElementById('state').init();";
	$js .= "} else {";
	$js .= "modFixSelect('select#state');";
	$js .= "};";
	// MOD new selects

	$objResponse->addClear("city", "innerHTML");
	$option = array('0' => $selectAll ? l('select_all') : '- ' . l('city') . ' -');
    $js .= addOption("city", $option);
    // MOD new selects
    $js .= "if(!jqTransformDaySelect) {";
    $js .= "document.getElementById('city').unload();";
    $js .= "selects(document.getElementById('city'));";
    $js .= "document.getElementById('city').init();";
	$js .= "} else {";
	$js .= "modFixSelect('select#city');";
	$js .= "};";
// MOD new selects

// AJAX LOADER
#$js .= " ajax_loader_img=document.getElementById('ajax_loader1');
#if(ajax_loader_img) ajax_loader_img.style.visibility='hidden';";

$js .= "hide_load_animation(1)";

	$objResponse->addScript($js);
	return $objResponse;
}
function cities($state_id, $selectAll = 1)
{
	global $db;
	global $l;
    $selectAll = intval($selectAll);
	$objResponse = new xajaxResponse();
	$objResponse->addClear("city", "innerHTML");
	$js = "";
	DB::query("SELECT city_id, city_title FROM geo_city WHERE state_id=" . to_sql($state_id, "Number") . " AND hidden = 0 ORDER BY city_title");
	$option = array('0' => $selectAll ? l('select_all') : '- ' . l('city') . ' -');
	while ($row = DB::fetch_row())
	{
		$option[$row['city_id']] = (isset($l['all'][to_php_alfabet($row['city_title'])]) ? $l['all'][to_php_alfabet($row['city_title'])] : $row['city_title']);
	}
	$js .= addOption("city", $option);
    // MOD new selects
    $js .= "if(!jqTransformDaySelect) {";
    $js .= "document.getElementById('city').unload();";
    $js .= "selects(document.getElementById('city'));";
    $js .= "document.getElementById('city').init();";
	$js .= "} else {";
	$js .= "modFixSelect('select#city');";
	$js .= "};";
// MOD new selects

// AJAX LOADER
#$js .= " ajax_loader_img=document.getElementById('ajax_loader2');
#if(ajax_loader_img) ajax_loader_img.style.visibility='hidden';";

$js .= "hide_load_animation(2)";

	$objResponse->addScript($js);
	#$objResponse->addAlert(strlen($js));
	return $objResponse;
}


function init_client($imMsgLayout, $dirTmplMain, $is_home_widget = false)
{
    $objResponse = new xajaxResponse();
    $js = widget($is_home_widget, false);
    // $js .= im($imMsgLayout, $dirTmplMain, false);
    if ($js) {
        $objResponse->addScript($js);
    }
    //read_msg(false);
    return $objResponse;
}

function update($id, $imMsgLayout, $dirTmplMain, $isFbModeTitle, $status, $status_writing, $timeoutSecServer, $set_is_read_msg)
{
	global $g_user;
	global $objResponse;
	global $js;

	$objResponse = new xajaxResponse();

	if ($g_user['user_id'] > 0)
	{
            if ($isFbModeTitle == 'true') {
                update_site_title($id);
            }
            set_writing($status_writing);
            im_update($id, $imMsgLayout, $dirTmplMain, $isFbModeTitle, $timeoutSecServer, $set_is_read_msg);
            game_update();
            video_update();
            audio_update();
            update_info_user();
			if ($status) widget_update($status, false);
	}

	// addAppend and then JS
	$objResponse->addScript($js);

	return $objResponse;
}

class CImOpen extends CHtmlBlock
{
	var $m_user;
        var $window_coords;
	function __construct($name, $html_path, $user, $window_coords = array('x' => 0, 'y' => 0, 'z' => 100), $dirTmplMain = '')
	{
		parent::__construct($name, $html_path);
		$this->m_user = $user;
		$this->window_coords = $window_coords;
        $this->urlTmplMain = mb_substr($dirTmplMain, 1, mb_strlen($dirTmplMain, 'UTF-8'),  'UTF-8');
	}
	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $g_user;

		DB::query("SELECT *, (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
) AS age FROM user WHERE user_id=" . $this->m_user . "");
		if ($user = DB::fetch_row())
		{

			$user['photo'] = User::getPhotoDefault($user['user_id'],"s");

            $user['name_short'] = User::nameShort($user['name']);

			if ($user['city'] == "") $user['city'] = l('blank');
			if ($user['state'] == "") $user['state'] = l('blank');
			if ($user['country'] == "") $user['country'] = l('blank');
            $user = array_merge($user,  $this->window_coords);
			foreach ($user as $k => $v)
			{
				$html->setvar($k, $v);
			}

                        // SOUND on/off

			if($g_user['sound']!=2) {
				$html->setvar("sound",0);
			}
			else {
				$html->setvar("sound",1);
			}
            if(Common::isOptionActive('contact_blocking'))
                $html->parse('contact_blocking');

            $html->setvar('url_tmpl_main_window', $this->urlTmplMain);
			parent::parseBlock($html);
		}
	}
}

function uploading_msg($toUser, $lastId, $imMsgLayout, $dirTmplMain) {
    global $g_user;
    $objResponse = new xajaxResponse();
	if ($g_user['user_id'] && $toUser && Common::isOptionActive('im'))
	{
        $scr_msg = '';
		$count = Common::getOption('im_history_messages','options');
        $sql = "(SELECT *
                   FROM im_msg
                  WHERE to_user=" . to_sql($toUser) . "
                    AND group_id = 0
                    AND from_user=" . to_sql($g_user['user_id']) . " AND `system` = 0 AND id < $lastId)
                UNION
                (SELECT *
                   FROM im_msg
                  WHERE to_user=" . to_sql($g_user['user_id']) . "
                    AND group_id = 0
                    AND from_user=" . to_sql($toUser) . " AND `system` = 0 AND id < $lastId)
                ORDER BY id DESC LIMIT {$count}";
		DB::query($sql);
		while ($row = DB::fetch_row()){
			$msg_html=imMessageToView($row, $imMsgLayout, $dirTmplMain);
            $scr_msg .= "append_msg({$toUser}, '$msg_html', {$row['id']},1);";
        }

		$objResponse->addScript($scr_msg);
	}

    return $objResponse;
}

function im($imMsgLayout, $dirTmplMain, $returnObjResponse = true) {
	global $g_user;
    if ($returnObjResponse) {
        $objResponse = new xajaxResponse();
    }
    $js = '';
	if (($g_user['user_id'] > 0) && Common::isOptionActive('im'))
	{
        $js = "(function(){ var timer=0; ";
		$scr_msg = "";
        $sql = "SELECT * FROM im_open
                 WHERE from_user=" . to_sql($g_user['user_id'], "Number")
               . " AND to_user!=" . to_sql($g_user['user_id'], "Number")
               . " AND group_id = 0"
               . " AND mid > 0" . WIDGET_DEMO_WHERE." ORDER BY z ASC";
		DB::query($sql, 1);
		while ($row = DB::fetch_row(1))
		{
            // IM CUSTOM TEMPLATE
            /*if($row['z'] < time()-28800) {
                DB::execute("DELETE FROM im_open WHERE to_user=" . to_sql($row['to_user'], "Number") . " AND from_user=" . to_sql($g_user['user_id'], "Number") . WIDGET_DEMO_WHERE);
                DB::execute("DELETE FROM im_open WHERE to_user=" . to_sql($g_user['user_id'], "Number") . " AND from_user=" . to_sql($row['to_user'], "Number") . WIDGET_DEMO_WHERE);
                continue;
            }*/
            $im_template = getTemplate($dirTmplMain);
            //$objResponse->addAlert($dirTmplMain);
            // IM CUSTOM TEMPLATE
                       $coords = array(
                            'x' => isset($row['x'])?$row['x']:0,
                            'y' => isset($row['y'])?$row['y']:0,
                            'z' => 1,
                        );
			$page = new CImOpen("", $im_template, $row['to_user'], $coords, $dirTmplMain);
            CIm::setMessageAsRead($row['to_user']);
			$tmp = null;
			$sData = to_js($page->parse($tmp, true));
			unset($page);
			//$objResponse->addAppend("x_loader", "innerHTML", $sData);

			$count = Common::getOption('im_history_messages','options');
            /*$cnt = DB::result("
				SELECT COUNT(id) FROM im_msg WHERE ((to_user=" . to_sql($row['to_user'], "Number") . " AND from_user=" . to_sql($g_user['user_id'], "Number") . "  AND `system` = 0)
				OR (to_user=" . to_sql($g_user['user_id'], "Number") . " AND from_user=" . to_sql($row['to_user'], "Number") . ")) AND `system` = 0
			", 0, 2);
			if ($cnt - $count > 0)
			{
				DB::execute("
					DELETE FROM im_msg WHERE ((to_user=" . to_sql($row['to_user'], "Number") . " AND from_user=" . to_sql($g_user['user_id'], "Number") . ")
					OR (to_user=" . to_sql($g_user['user_id'], "Number") . " AND from_user=" . to_sql($row['to_user'], "Number") . ")) AND `system` = 0
					ORDER BY id LIMIT " . ($cnt - $count) . "
				", 0, 2);
			}*/
            $firstId = DB::result("
                (SELECT id FROM im_msg WHERE to_user=" . to_sql($row['to_user'], "Number") . " AND group_id = 0 AND from_user=" . to_sql($g_user['user_id'], "Number") . " AND id>" . to_sql(0, "Number") . " AND `system` = 0)
                UNION (SELECT id FROM im_msg WHERE to_user=" . to_sql($g_user['user_id'], "Number") . " AND group_id = 0 AND from_user=" . to_sql($row['to_user'], "Number") . " AND id>" . to_sql(0, "Number") . " AND `system` = 0)
                ORDER BY id ASC LIMIT 1");

			DB::query("
				(SELECT * FROM im_msg WHERE to_user=" . to_sql($row['to_user'], "Number") . " AND group_id = 0 AND from_user=" . to_sql($g_user['user_id'], "Number") . " AND `system` = 0)
				UNION (SELECT * FROM im_msg WHERE to_user=" . to_sql($g_user['user_id'], "Number") . " AND group_id = 0 AND from_user=" . to_sql($row['to_user'], "Number") . " AND `system` = 0)
				ORDER BY id DESC LIMIT $count
			", 2);
			$msg_html="";
			while ($row2 = DB::fetch_row(2))
			{
				$msg_html=imMessageToView($row2, $imMsgLayout, $dirTmplMain).$msg_html;
			}
			$js.="uploading_msg[{$row['to_user']}]=1;
                  uploading_first_msg[{$row['to_user']}]={$firstId};
                  $('$sData').css({opacity:0}).appendTo('#xajax_im');
                  if(imScrollbar) {
                    $('.scrollbarY_".$row['to_user']."').tinyscrollbar({thumbSizeMin:20})
                     .on('move',function(){
                        uploading_im_msg({$row['to_user']},'{$imMsgLayout}','{$dirTmplMain}');
                     })
                  }else{
                    $('#xajax_im_msgs_".$row['to_user']."').on('scroll',function(){
                        uploading_im_msg({$row['to_user']},'{$imMsgLayout}','{$dirTmplMain}');
                    })
                  }
                  $('#xajax_im_msgs_". $row['to_user'] ."').append('$msg_html');
                  setTimeout(function(){
                        if(imScrollbar) {
                            $('.scrollbarY_".$row['to_user']."').data('plugin_tinyscrollbar').update('bottom');
                        }
                        Drag.init('xajax_im_head_".$row['to_user']."', 'xajax_im_open_".$row['to_user']."');
                  }, timer+=100);";
		}

		DB::query("SHOW TABLE STATUS LIKE 'im_msg'");
		$line = DB::fetch_row();
		if(intval($line['Auto_increment']) == 0) set_session("im_id", 0);
		else set_session("im_id", intval($line['Auto_increment']) - 1);

        $js .= "})();
                last_id = " . get_session('im_id') . ";
                timeout = setTimeout('updateAjax()', timeoutSec);";
        if ($returnObjResponse) {
            $objResponse->addScript($js);
        }
		//$objResponse->addAlert($scr_msg);
	}
    if ($returnObjResponse) {
        return $objResponse;
    } else {
        return $js;
    }
}
function im_sent($sent_msg, $id, $imMsgLayout, $dirTmplMain, $isFbModeTitle, $timeoutSecServer, $set_is_read_msg)
{
	global $g_user;
	global $l;
	global $objResponse;
	global $js;

    CStatsTools::count('im_messages');

	// $msg = strip_tags($sent_msg['msg']);
	$msg = str_replace("<", "&lt;", $sent_msg['msg']);
	$to_user = to_sql($sent_msg['to_user'], 'Number');
	$objResponse = new xajaxResponse();
	if ($g_user['user_id'] > 0) {
        if ($g_user['user_id'] == $to_user) {
            $js = 'window.location.href="' . Common::getHomePage() . '";';
        }elseif (!payment_check_return('im')) {
            $js = 'xajax_im_close(' . $to_user . '); window.location.href="upgrade.php";';
        }elseif(!User::isBlocked('im', $to_user, guid())){
			/*$censured = false;
			if (file_exists(dirname(__FILE__) . "/im_new/feature/censured.php")) include(dirname(__FILE__) . "/im_new/feature/censured.php");*/
            $msg = censured($msg);

			if ($msg){

                $translated=CIm::getTranslate($msg,$to_user);

                DB::execute("INSERT INTO im_msg SET from_user='{$g_user['user_id']}', to_user='{$to_user}', born='" . date('Y-m-d H:i:s') . "', ip=" . to_sql(IP::getIp()) . ", name=" . to_sql($g_user['name']) . ",  msg=" . to_sql($msg) . ", msg_translation=".to_sql($translated)."");
                $lastMid = DB::insert_id();
                $where = "to_user = '{$g_user['user_id']}' AND from_user = '$to_user' AND group_id = 0" . WIDGET_DEMO_WHERE;
                $isIm = DB::count('im_open', $where);
                if ($isIm) {
                    CIm::updateLastIdFromAddMessage($sent_msg['to_user'], $lastMid);
                   // DB::update('im_open', array('mid' => $lastMid), $where, '', 1);
                } else {
                    $sql = 'INSERT INTO `im_open`
                               SET `from_user` = ' . to_sql($to_user, 'Number') . ',
                                   `to_user` = ' . to_sql($g_user['user_id'], 'Number') . ',
                                   `mid` = ' . to_sql($lastMid, 'Number') . ',
                                   `z` = ' . time() .
                                   WIDGET_DEMO_INSERT;
                    DB::execute($sql);
                }
			}
            //updateAjax(".$id.");
			/*$objResponse->addScript("
				//clearTimeout(timeout);
				//updateAjax();
				document.forms.sent_msg_$to_user.msg.value='';
			");*/
		} else {
			$objResponse->addAlert(l('You are in block list'));
		}
	}
	im_update($id, $imMsgLayout, $dirTmplMain, $isFbModeTitle, $timeoutSecServer, $set_is_read_msg);
	$objResponse->addScript($js);
	return $objResponse;
}
function im_open_new($user_id, $imMsgLayout, $dirTmplMain, $x = 0, $y = 0, $isFbModeTitle = false)
{
	global $g_user;
	global $l;
    CStatsTools::count('im_started');

	$objResponse = new xajaxResponse();

    $im_check = User::isBlocked('im', $user_id, guid());

	if($im_check) $objResponse->addAlert(l('You are in block list'));
	if ($g_user['user_id'] > 0 && !$im_check)
	{

        /*$firstImOpen = false;

		DB::query("SELECT * FROM im_open WHERE to_user=" . to_sql($user_id, "Number") . " AND from_user=" . to_sql($g_user['user_id'], "Number") . WIDGET_DEMO_WHERE);
        if ($row = DB::fetch_row()) {
            //$objResponse->addRemove("xajax_im_open_" . $user_id . "");
            DB::update('im_open', array('mid' => 1, 'z' => time()), "from_user = {$row['from_user']} AND to_user = {$row['to_user']}" . WIDGET_DEMO_WHERE, '', 1);
        } else {
            DB::execute("INSERT INTO im_open SET to_user=" . to_sql($user_id, "Number") . ", from_user=" . to_sql($g_user['user_id'], "Number") . ", mid = 1, x = " . $x . ", y = " . $y .", z = ".time().WIDGET_DEMO_INSERT);
            $firstImOpen = true;
        }

		DB::query("SELECT * FROM im_open WHERE from_user=" . to_sql($user_id, "Number") . " AND to_user=" . to_sql($g_user['user_id'], "Number") . WIDGET_DEMO_WHERE);
		if (!($row1 = DB::fetch_row())) {
            DB::execute("INSERT INTO im_open SET from_user=" . to_sql($user_id, "Number") . ", to_user=" . to_sql($g_user['user_id'], "Number") . WIDGET_DEMO_INSERT);
        }*/
        CIm::firstOpenIm($user_id);

		// IM CUSTOM TEMPLATE
		/*global $g;
		$im_template = "./im_new/im.html";
		$im_custom  = $g['tmpl']['dir_tmpl_main']."im.html";
		if(file_exists($im_custom)) $im_template = $im_custom;*/
        $im_template = getTemplate($dirTmplMain);
		// IM CUSTOM TEMPLATE
        $coords = array('x' => isset($row['x'])?$row['x']:$x,
                        'y' => isset($row['y'])?$row['y']:$y,
                        'z' => 1,);

		$page = new CImOpen("", $im_template, $user_id, $coords, $dirTmplMain);
		$tmp = null;
		$sData = to_js($page->parse($tmp, true));

// calculate left side position$('#xajax_im_open_" . $user_id . "').offset({top:x, left:y});

/*if ($firstImOpen == true && $selectorLink != NULL){
    $scr_msg .= "var link = $('#" . $selectorLink . "'),
                     offset = link.offset(),
                     x,
                     y,
                     imObj = {'to_user': " . $user_id . "};
                 x = offset.top;
                 y = offset.left;
                 x = (x <0) ? 0 : x;
                 y = (y <0) ? 0 : y;

                 $('#xajax_im_open_" . $user_id . "').offset({top:x, left:y});
                 xajax_im_save_position(imObj, y, x, " . $coords['z'] . ");
                 ";
}*/
            $count = Common::getOption('im_history_messages','options');
			/*$cnt = DB::result("
				SELECT COUNT(id) FROM im_msg WHERE ((to_user=" . to_sql($user_id, "Number") . " AND from_user=" . to_sql($g_user['user_id'], "Number") . ")
				OR (to_user=" . to_sql($g_user['user_id'], "Number") . " AND from_user=" . to_sql($user_id, "Number") . ")) AND `system` = 0
			", 0, 2);
			if ($cnt - $count > 0)
			{
				DB::execute("
					DELETE FROM im_msg WHERE ((to_user=" . to_sql($user_id, "Number") . " AND from_user=" . to_sql($g_user['user_id'], "Number") . ")
					OR (to_user=" . to_sql($g_user['user_id'], "Number") . " AND from_user=" . to_sql($user_id, "Number") . ")) AND `system` = 0
					ORDER BY id LIMIT " . ($cnt - $count) . "
				", 0, 2);
			}*/
        $firstId = DB::result("
			(SELECT id FROM im_msg WHERE to_user=" . to_sql($user_id, "Number") . " AND group_id = 0 AND from_user=" . to_sql($g_user['user_id'], "Number") . " AND id>" . to_sql(0, "Number") . " AND `system` = 0)
			UNION (SELECT id FROM im_msg WHERE to_user=" . to_sql($g_user['user_id'], "Number") . " AND group_id = 0 AND from_user=" . to_sql($user_id, "Number") . " AND id>" . to_sql(0, "Number") . " AND `system` = 0)
			ORDER BY id ASC LIMIT 1");
		DB::query("
			(SELECT * FROM im_msg WHERE to_user=" . to_sql($user_id, "Number") . " AND group_id = 0 AND from_user=" . to_sql($g_user['user_id'], "Number") . " AND id>" . to_sql(0, "Number") . " AND `system` = 0)
			UNION (SELECT * FROM im_msg WHERE to_user=" . to_sql($g_user['user_id'], "Number") . " AND group_id = 0 AND from_user=" . to_sql($user_id, "Number") . " AND id>" . to_sql(0, "Number") . " AND `system` = 0)
			ORDER BY id DESC LIMIT $count
		", 2);
		$msg_html="";
		while ($row2 = DB::fetch_row(2))
		{
			$msg_html = imMessageToView($row2, $imMsgLayout, $dirTmplMain).$msg_html;
			//$msg_html = str_replace("\\", "\\\\", $msg_html);
			//$msg_html = str_replace("'", "\'", $msg_html);
			//$scr_msg = "append_msg('" . $row['to_user'] . "', '" . $msg_html . "'); " . $scr_msg;
		}
        $scr="uploading_msg[{$user_id}]=1;
              uploading_first_msg[{$user_id}]={$firstId};
              if ($('#xajax_im_open_$user_id')[0]) {reset_opens($user_id)}
              else { $('#xajax_im').append('$sData');
                     if(imScrollbar) {
                        $('.scrollbarY_$user_id').tinyscrollbar({thumbSizeMin:20})
                        .on('move',function(e){
                            uploading_im_msg({$user_id},'{$imMsgLayout}','{$dirTmplMain}');
                        });
                     }else{
                        $('#xajax_im_msgs_".$user_id."').on('scroll',function(){
                            uploading_im_msg({$user_id},'{$imMsgLayout}','{$dirTmplMain}');
                        })
                    }
                     $('#xajax_im_msgs_$user_id').append('$msg_html');
                     setTimeout(function(){
                        if(imScrollbar) {
                            $('.scrollbarY_$user_id').data('plugin_tinyscrollbar').update('bottom');
                        }
                        Drag.init('xajax_im_head_$user_id', 'xajax_im_open_$user_id');
                     }, 50);

              }";

        #mark open message
        if ($isFbModeTitle == 'false') {
            /*$sql = 'UPDATE `im_msg`
                       SET `is_new` = 0
                     WHERE `is_new` > 0
                       AND `system` = 0
                       AND `to_user` = ' . to_sql($g_user['user_id'], 'Number');
            DB::execute($sql);*/
            CIm::setMessageAsRead($user_id);
        }

//        $scr_msg .= "
//            coordsWrapper = getAbsolutePositionReal('wrapper');
//            imWidthOffset = " . IM_WIDTH_OFFSET . ";
//            if(coordsWrapper.x > imWidthOffset) {
//                coordX = coordsWrapper.x - imWidthOffset;
//                document.getElementById('xajax_im_open_$user_id').style.left = coordX + 'px';
//            }
//        ";

                //DB::query("SELECT * FROM im_open WHERE from_user=" . to_sql($g_user['user_id'], "Number") . " AND mid > 0");
                // while ($rowet = DB::fetch_row())
                // {
                    // $scr_msg .= "$('#xajax_im_open_".$rowet['to_user']."').tinyscrollbar();
                    // $('#xajax_im_open_".$rowet['to_user']."').tinyscrollbar_update('bottom');";
                // }

                //$scr_msg .= 'last_id = ' . get_session("im_id") . ';';
        $scr .= 'last_id = ' . get_session('im_id') . ';';
		$objResponse->addScript($scr);
	}
	return $objResponse;
}

function imMessageToView($row, $imMsgLayout, $dirTmplMain)
{
    $odd = '';

    if (guid() == $row['to_user']) {
        $odd = ' odd';
        $row=CIm::switchOnTranslate($row);
    } else {
        $row['msg_translation']='';
    }
    $template = array();
    //$imMsgLayout = Common::getOption('im_msg_layout', 'template_options');

    //$msg = Common::parseLinks($row['msg'], '_blank', 'txt_lower_header_color');
    //$msg = replaceSmile($msg);
    //$msg = Common::parseLinksSmile($row['msg'], '_blank', 'txt_lower_header_color');
    //$msg = Common::parseLinksTag(to_html($row['msg']), 'a', '&lt;', 'parseLinksSmile', '_blank', 'txt_lower_header_color');


    $msg = CIm::prepareMediaFromComment(to_html($row['msg']), $row['from_user']);

	$msg = ImAudioMessage::getHtmlPlayer($row, $row['id']) . $msg;

    $originalMsg='';
    $showOriginalMsg='';

    if($row['msg_translation']!='' && Common::isOptionActive('autotranslator_show_original')){
        $urlIco = Common::getOption('url_tmpl','path').'common/images/page_refresh.png';
        //$msgO = Common::parseLinksTag(to_html($row['msg_translation']), 'a', '&lt;', 'parseLinksSmile', '_blank', 'txt_lower_header_color');

        $msgO = CIm::prepareMediaFromComment(to_html($row['msg_translation']), $row['from_user']);
        $originalMsg='<div class="original_message">'.$msgO.'</div>';
        $showOriginalMsg='<a class="show_original_message" href="#" onclick="showOriginalMsg(this,'.$row['from_user'].'); return false;" title="'.l('the_message_was_autotranslated').' '.l('show_original_message').'"><img src="'.$urlIco.'" height="12px"  style="margin-bottom:-2px;margin-left:2px;"></a>';
    }

	if ($imMsgLayout != 'new_age') {
        $msg = nl2br($msg);
    }

    if ($imMsgLayout == 'oryx' || $imMsgLayout == 'mixer') {
        $photo = User::getPhotoDefault($row['from_user'], 'r');
        $part = explode('_', $photo);
        $prf = Common::tmplName($dirTmplMain);
        if ($part[0] == 'nophoto') {
            $photo = $prf . '_' . $photo;
        } elseif (isset($part[1]) && $part[1] == 'nophoto') {
            unset($part[0]);
            $photo = $prf . '_' . implode('_', $part);
        }
        $dateBorn = time_mysql_dt2u($row['born']);
        if (date('Y-m-d', $dateBorn) == date('Y-m-d')) {
            $formatName = 'im_message_oryx_today';
        } else {
            $formatName = 'im_message_oryx';
        }
        $date_im = Common::dateFormat($dateBorn, $formatName, FALSE);
        $url_main = Common::getOption('url_main', 'path');
        $statusMsg = "id = 'no_read_{$row['id']}'";
        if ($row['is_new'] == 0 && $row['from_user'] == guid()) {
            $date_im = $date_im . '<img class="read" src="' . substr($dirTmplMain, 1) . 'images/checkmark.png" width="10" height="8">';
            $statusMsg = '';
        }


        $html = '<div id="msg_'.$row['id'].'" class="message"><table><tr><th><a href="' . $url_main . 'search_results.php?display=profile&name=' . $row['name'] . '"><img class="user_photo_' .$row['from_user'] . '" src="' . Common::getOption('url_files', 'path') . $photo . '" width="37" height="41" align="left" /></a></th><td><div><h3><a class="txt_upper_header_color" href="' . Common::getOption('url_main', 'path') . 'search_results.php?display=profile&name=' . $row['name'] . '">' . $row['name'] . '</a></h3>' . $msg .$showOriginalMsg . '<br />'. $originalMsg . '<span ' . $statusMsg . '>' . $date_im . '</span></div></td></tr></table></div>';
    } else {
        $html = "<div id='msg_" . $row['id'] . "' class='cumsg " . $odd . "' style='text-align: left;'><span class='cunick'>" . $row['name'] . ": </span>" . $msg . $showOriginalMsg. $originalMsg. "</div>";
    }
    //$html = str_replace("\\", "\\\\", $html);
    //$html = str_replace("'", "\'", $html);
    //$html = str_replace(array("\\", "'"), array("\\\\", "\'"), $html);


    return to_js($html);
}

function im_close($user_id)
{
	global $g_user;
	$objResponse = new xajaxResponse();
	if ($g_user['user_id'] > 0)
	{
		//DB::execute("DELETE FROM im_open WHERE to_user=" . to_sql($user_id, "Number") . " AND from_user=" . to_sql($g_user['user_id'], "Number") . WIDGET_DEMO_WHERE);
        //Cim::clearHistoryMessages($user_id);
        Cim::setCurrentData();
        DB::delete(Cim::getTable(), Cim::getWhereMessagesFrom($user_id) . Cim::getWhereGroup(). Cim::$demoWhere);

        $objResponse->addScript("\$('#xajax_im_open_$user_id').stop().fadeOut(300, function(){\$(this).remove()})");
	}
	return $objResponse;
}
function im_save_position($im,$x,$y,$z){
    	global $g_user;
//638
//	$im = intval(substr($im, 15));

//        $x -= WIDGET_LEFT_OFFSET/2;
//        $y += WIDGET_DEMO_HEIGHT_DELTA;
        if(!empty($im['to_user'])) {
//        $y -= 5;
	// if($y<0) $y = 0;
	// if($x<0) $x = 0;

    $sql = "UPDATE `im_open` SET `x` = $x, `y` = $y, `z` = ".time()."
        WHERE `to_user` = ".$im['to_user']."
        AND from_user = ".$g_user['user_id'].WIDGET_DEMO_WHERE;
    DB::execute($sql);
    }

    // $sqlother = 'UPDATE `im_open` SET `z` = ' . '70' . '
        // WHERE `to_user` != ' . to_sql($im['to_user'], 'Number') . '
        // AND from_user = ' . $g_user['user_id'] . WIDGET_DEMO_WHERE;
    // DB::execute($sqlother);


	$objResponse = new xajaxResponse();
	//$objResponse->addAlert($sql);
	return $objResponse;
}

function im_save_position_input($iminput){
    global $g_user;

    $sql = 'UPDATE `im_open` SET `z` = ' . '100' . '
        WHERE `to_user` = ' . $iminput . '
        AND from_user = ' . $g_user['user_id'] . WIDGET_DEMO_WHERE;
    DB::execute($sql);

    $sqlother = 'UPDATE `im_open` SET `z` = ' . '70' . '
        WHERE `to_user` != ' . $iminput . '
        AND from_user = ' . $g_user['user_id'] . WIDGET_DEMO_WHERE;
    DB::execute($sqlother);

    $objResponse = new xajaxResponse();
    return $objResponse;
}

function im_update($id, $imMsgLayout, $dirTmplMain, $isFbModeTitle, $timeoutSecServer, $set_is_read_msg)
{
	global $g_user;
	global $objResponse;
	global $js;
    global $g;

	$check = "";
	$scr = "";
	$scr_msg = "";
    $msg_sending = false;
    //$lastId = ($id != NULL && $id < get_session("im_id")) ? $id : get_session("im_id");//?
    //$objResponse->addAlert('id-'.$id.' ses-'.get_session("im_id"). ' last-'.$lastId);
    $fromUserWriting = get_writing_user($timeoutSecServer);

	if ($g_user['user_id'] > 0)
	{

		DB::query("
			SELECT * FROM im_msg
			WHERE (to_user=" . to_sql($g_user['user_id'], "Number") . " OR from_user=" . to_sql($g_user['user_id'], "Number") . ")
              AND id > " . to_sql($id, 'Number') . "
              AND `system` = 0
              AND group_id = 0
			ORDER BY id DESC LIMIT 50
		", 2);
		while ($row2 = DB::fetch_row(2))
		{
            if($id == $row2['id']) {
                break;
            }
			if ($g_user['user_id'] == $row2['to_user']) {
				$check = 1;
				$user_id = $row2['from_user'];
                $msg_sending = true;
                unset($fromUserWriting[$row2['from_user']]);
                $scr_msg .= "delete_writing_user('$user_id');";
			} else {
				$check = 1;
				$user_id = $row2['to_user'];
			}
            if ($isFbModeTitle == 'false') {
                CIm::setMessageAsRead($user_id);
            }
            $msg_html = imMessageToView($row2, $imMsgLayout, $dirTmplMain);
            /*  Сейчас смайлики не озвучиваются
                    for ($sm = 1; $sm <= 10; $sm++) {
                    if (strpos(to_html($msg_html), 'smile sm' . $sm . '') !== false) {
                        $sound = $sm;
                    }
            */
			$scr_msg = "append_msg('$user_id', '$msg_html', '".$row2['id']."');
			".$scr_msg;
		}
        #mark open message
        /*if ($isFbModeTitle == 'false') {
            $sql = 'UPDATE `im_msg`
                       SET `is_new` = 0
                     WHERE `is_new` > 0
                       AND `system` = 0
                       AND `to_user` = ' . to_sql($g_user['user_id'], 'Number');
            DB::execute($sql);
        }*/
        if ($imMsgLayout == 'oryx' || $imMsgLayout == 'mixer') {
            //$msgIm = DB::select('im_msg', '`is_new` = 0 AND `from_user` = ' . to_sql($g_user['user_id'], 'Number'));
            $sql = 'SELECT M.id
                      FROM `im_msg` as M, `im_open` as IM
                     WHERE M.from_user = IM.from_user AND M.to_user = IM.to_user
                       AND M.group_id = 0
                       AND M.from_user = ' . to_sql($g_user['user_id'], 'Number') .
                     ' AND M.is_new = 0';
            $msgIm = DB::rows($sql);
            foreach ($msgIm as $msg) {
                if (!isset($set_is_read_msg[$msg['id']]) || $set_is_read_msg[$msg['id']] != 1) {
                    $scr_msg .= "is_read_msg('" . $msg['id'] . "');";
                }
            }
        }
        foreach ($fromUserWriting as $user => $status) {
            $scr_msg .= ($status) ? "is_writing_user('$user');" : "delete_writing_user('$user');";
        }

        $jsSound = '';
		if ($msg_sending == true && $g_user['sound']!=2 ) {
			/*if (isset($sound)) {
				$scr_msg .= "soundManager.play('im_sound" . $sound . "', '_server/im_new/sounds/" . $sound . ".mp3');";
			} else {
				$scr_msg .= "soundManager.play('im_sound0', '_server/im_new/sounds/signal_main.mp3');";
			}*/
            //$objResponse->addAlert(1111);
            $jsSound = playSound();
		}

		$js .= $jsSound . $scr_msg;

        /*
		DB::query("SHOW TABLE STATUS LIKE 'im_msg'");
		$line = DB::fetch_row();
		if (intval($line['Auto_increment']) == 0)
            set_session("im_id", 0);
		else
            set_session("im_id", intval($line['Auto_increment']) - 1);
         */

        $js .= 'last_id = ' . CIm::lastId() . ';
		';
        if ($check == 1 || $_COOKIE['pagechk']==0) {
            //DB::query("SELECT * FROM im_open WHERE from_user=" . to_sql($g_user['user_id'], "Number") . " AND mid > 0");
            $js .= "document.cookie='pagechk=1';";
        }
    }
}

class CGameInvite extends CHtmlBlock
{
	var $m_user;
	var $m_game;
	function __construct($name, $html_path, $user, $game)
	{
		parent::__construct($name, $html_path);
		$this->m_user = $user;
		$this->m_game = $game;
	}
	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $g_user;
		#echo $this->m_game;
		if ($this->m_game == "lovetree")
		{
			$html->setvar("game", isset($l['games.php']['love_tree']) ? $l['games.php']['love_tree'] : "Love Tree");
			$html->setvar("game_url", "lovetree");
		}
        elseif ($this->m_game == "test")
		{
			$html->setvar("game", "Test");
			$html->setvar("game_url", "test");
		}
		elseif ($this->m_game == "morboy")
		{
			$html->setvar("game", isset($l['games.php']['sea_battle']) ? $l['games.php']['sea_battle'] : "Sea Battle");
			$html->setvar("game_url", "morboy");
		}
		elseif ($this->m_game == "shashki")
		{
			$html->setvar("game", isset($l['games.php']['draughts']) ? $l['games.php']['draughts'] : "Draughts");
			$html->setvar("game_url", "shashki");
		}
		elseif ($this->m_game == "chess")
		{
			$html->setvar("game", isset($l['games.php']['chess']) ? $l['games.php']['chess'] : "Chess");
			$html->setvar("game_url", "chess");
		}
		elseif ($this->m_game == "pool")
		{
			$html->setvar("game", isset($l['games.php']['pool']) ? $l['games.php']['pool'] : "Pool");
			$html->setvar("game_url", "pool");
		}
		elseif ($this->m_game == "tanks")
		{
			$html->setvar("game", isset($l['games.php']['tanks']) ? $l['games.php']['tanks'] : "tanks");
			$html->setvar("game_url", "tanks");
		}
		if (DB::query("SELECT *, (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
) AS age FROM user WHERE user_id=" . to_sql($this->m_user) . ""))
		{
			$user = DB::fetch_row();

			foreach ($user as $k => $v)
			{
				$html->setvar($k, $v);
			}

			parent::parseBlock($html);
		}
	}
}
function game_invite($user_id, $game = "lovetree")
{
	global $g_user;
	global $l;

    CStatsTools::count('games_started');

	$objResponse = new xajaxResponse();
	$game_check = User::isBlocked('games', $user_id, guid());
	if($game_check != 0) $objResponse->addAlert(isset($l['all'][to_php_alfabet('You are in block list')]) ? $l['all'][to_php_alfabet('You are in block list')] : 'You are in block list');
	if ($g_user['user_id'] > 0 && $game_check == 0)
	{
		DB::query("SELECT * FROM game_invite WHERE to_user=" . to_sql($user_id, "Number") . " AND from_user=" . to_sql($g_user['user_id'], "Number") . "");
		if (!($row = DB::fetch_row()))
		{
			DB::execute("INSERT INTO game_invite SET to_user=" . to_sql($user_id, "Number") . ", from_user=" . to_sql($g_user['user_id'], "Number") . ", game=" . to_sql($game) . "");
			$page = new CGameInvite("", "./games/invite.html", $user_id, "");
			$tmp = null;
			$sData = $page->parse($tmp, true);
			$objResponse->addScript("$('#xajax_im').append('".to_js($sData)."');");
		}
		else
		{
			DB::execute("DELETE FROM game_invite WHERE to_user=" . to_sql($user_id, "Number") . " AND from_user=" . to_sql($g_user['user_id'], "Number") . "");
			DB::execute("INSERT INTO game_invite SET to_user=" . to_sql($user_id, "Number") . ", from_user=" . to_sql($g_user['user_id'], "Number") . ", game=" . to_sql($game) . "");
			$page = new CGameInvite("", "./games/invite.html", $user_id, "");
			$tmp = null;
			$sData = $page->parse($tmp, true);
			$objResponse->addScript("$('#xajax_im').append('".to_js($sData)."');");
		}
		#$objResponse->addAlert($sData);
	}
	return $objResponse;
}
function game_update()
{
	global $g_user;
	global $objResponse;
	global $js;

	DB::query("SELECT * FROM game_invite WHERE to_user=" . to_sql($g_user['user_id'], "Number") . "");
	if ($row = DB::fetch_row())
	{
		$page = new CGameInvite("", "./games/update.html", $row['from_user'], $row['game']);
		$tmp = null;
		$sData = $page->parse($tmp, true);
		$objResponse->addScript("$('#xajax_im').append('".to_js($sData)."');");
		DB::execute("DELETE FROM game_invite WHERE to_user=" . to_sql($g_user['user_id'], "Number") . " AND from_user=" . to_sql($row['from_user'], "Number") . "");
	}

	DB::query("SELECT * FROM game_reject WHERE to_user=" . to_sql($g_user['user_id'], "Number") . "");
	if ($row = DB::fetch_row())
	{
		if ($row['go'] == "N")
		{
			$objResponse->addRemove("xajax_game_invite");
			$page = new CGameInvite("", "./games/reject.html", $row['from_user'], "");
			$tmp = null;
			$sData = $page->parse($tmp, true);
			$objResponse->addScript("$('#xajax_im').append('".to_js($sData)."');");
			$js .= "game_request_sent = false;";

		}
		else
		{
			$objResponse->addRedirect("./games.php?game=" . $row['game'] . "&id=" . $row['from_user'] . "");
		}
		DB::execute("DELETE FROM game_reject WHERE to_user=" . to_sql($g_user['user_id'], "Number") . " AND from_user=" . to_sql($row['from_user'], "Number") . "");
	}

}
function game_reject($user_id)
{
	global $g_user;
	$objResponse = new xajaxResponse();

	DB::execute("INSERT INTO game_reject SET to_user=" . to_sql($user_id, "Number") . ", from_user=" . to_sql($g_user['user_id'], "Number") . ", go='N'");
	$objResponse->addRemove("xajax_game_update");
	return $objResponse;
}
function game_go($user_id, $game)
{
	global $g_user;
	$objResponse = new xajaxResponse();
	if ($g_user['user_id'] > 0)
	{
		DB::execute("INSERT INTO game_reject SET to_user=" . to_sql($user_id, "Number") . ", from_user=" . to_sql($g_user['user_id'], "Number") . ", game=" . to_sql($game, "Text") . ", go='Y'");
		$objResponse->addRedirect("./games.php?game=" . $game . "&id=" . $user_id);
	}
	return $objResponse;
}

class CVideoInvite extends CHtmlBlock
{
	var $m_user;
	function __construct($name, $html_path, $user)
	{
		parent::__construct($name, $html_path);
		$this->m_user = $user;
	}
	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $g_user;

		if (DB::query("SELECT *, (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
) AS age FROM user WHERE user_id=" . to_sql($this->m_user) . ""))
		{
			$user = DB::fetch_row();
			foreach ($user as $k => $v) $html->setvar($k, $v);
			parent::parseBlock($html);
		}
	}
}
function video_invite($user_id)
{
	global $g_user;
	global $l;
	$objResponse = new xajaxResponse();
	$video_check = User::isBlocked('videochat', $user_id, guid());
	if($video_check != 0) $objResponse->addAlert(isset($l['all'][to_php_alfabet('You are in block list')]) ? $l['all'][to_php_alfabet('You are in block list')] : 'You are in block list');
	if ($g_user['user_id'] > 0 && $video_check == 0)
	{
		DB::query("SELECT * FROM video_invite WHERE to_user=" . to_sql($user_id, "Number") . " AND from_user=" . to_sql($g_user['user_id'], "Number") . "");
		if (!($row = DB::fetch_row()))
		{
			DB::execute("INSERT INTO video_invite SET to_user=" . to_sql($user_id, "Number") . ", from_user=" . to_sql($g_user['user_id'], "Number") . "");
			$page = new CVideoInvite("", "./video/invite.html", $user_id);
			$tmp = null;
			$sData = $page->parse($tmp, true);
			$objResponse->addScript("$('#xajax_im').append('".to_js($sData)."');");
		}
		else
		{
			DB::execute("DELETE FROM video_invite WHERE to_user=" . to_sql($user_id, "Number") . " AND from_user=" . to_sql($g_user['user_id'], "Number") . "");
			DB::execute("INSERT INTO video_invite SET to_user=" . to_sql($user_id, "Number") . ", from_user=" . to_sql($g_user['user_id'], "Number") . "");
			$page = new CVideoInvite("", "./video/invite.html", $user_id);
			$tmp = null;
			$sData = $page->parse($tmp, true);
			$objResponse->addScript("$('#xajax_im').append('".to_js($sData)."');");
		}
		#$objResponse->addAlert($sData);
	}
	return $objResponse;
}
function video_update()
{
	global $g_user;
	global $objResponse;

	DB::query("SELECT * FROM video_invite WHERE to_user=" . to_sql($g_user['user_id'], "Number") . "");
	if ($row = DB::fetch_row()) {
		$page = new CVideoInvite("", "./video/update.html", $row['from_user']);
		$tmp = null;
		$sData = $page->parse($tmp, true);
		$objResponse->addScript("$('#xajax_im').append('".to_js($sData)."');");
		DB::execute("DELETE FROM video_invite WHERE to_user=" . to_sql($g_user['user_id'], "Number") . " AND from_user=" . to_sql($row['from_user'], "Number") . "");
	}

	DB::query("SELECT * FROM video_reject WHERE to_user=" . to_sql($g_user['user_id'], "Number") . "");
	if ($row = DB::fetch_row()) {
		if ($row['go'] == "N") {
			$objResponse->addRemove("xajax_video_invite");
			$page = new CVideoInvite("", "./video/reject.html", $row['from_user'], "");
			$tmp = null;
			$sData = $page->parse($tmp, true);
			$objResponse->addScript("$('#xajax_im').append('".to_js($sData)."');");
		} else {
			$objResponse->addRedirect("./videochat.php?id=" . $row['from_user'] . "");
		}
		DB::execute("DELETE FROM video_reject WHERE to_user=" . to_sql($g_user['user_id'], "Number") . " AND from_user=" . to_sql($row['from_user'], "Number") . "");
	}

}
function video_reject($user_id)
{
	global $g_user;
	$objResponse = new xajaxResponse();
	if ($g_user['user_id'] > 0)
	{
		DB::execute("INSERT INTO video_reject SET to_user=" . to_sql($user_id, "Number") . ", from_user=" . to_sql($g_user['user_id'], "Number") . ", go='N'");
		$objResponse->addRemove("xajax_video_update");
		#$objResponse->addAlert("INSERT INTO video_reject SET to_user=" . to_sql($user_id, "Number") . ", from_user=" . to_sql($g_user['user_id'], "Number") . ", go='N'");
	}
	return $objResponse;
}
function video_go($user_id)
{
	global $g_user;
	$objResponse = new xajaxResponse();
	if ($g_user['user_id'] > 0)
	{
		DB::execute("INSERT INTO video_reject SET to_user=" . to_sql($user_id, "Number") . ", from_user=" . to_sql($g_user['user_id'], "Number") . ", go='Y'");
		$objResponse->addRedirect("./videochat.php?id=" . $user_id . "");
		#$objResponse->addAlert("xajax_video_update");
	}
	return $objResponse;
}

class CAudioInvite extends CHtmlBlock
{
	var $m_user;
	function __construct($name, $html_path, $user)
	{
		parent::__construct($name, $html_path);
		$this->m_user = $user;
	}
	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $g_user;

		if (DB::query("SELECT *, (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
) AS age FROM user WHERE user_id=" . to_sql($this->m_user) . ""))
		{
			$user = DB::fetch_row();
            foreach ($user as $k => $v) $html->setvar($k, $v);
			parent::parseBlock($html);
		}
	}
}
function audio_invite($user_id)
{
	global $g_user;
	global $l;
	$objResponse = new xajaxResponse();
	$audio_check = User::isBlocked('audiochat', $user_id, guid());
	if($audio_check != 0) $objResponse->addAlert(l('You are in block list'));
	if ($g_user['user_id'] > 0 && $audio_check == 0)
	{
		DB::query("SELECT * FROM audio_invite WHERE to_user=" . to_sql($user_id, "Number") . " AND from_user=" . to_sql($g_user['user_id'], "Number") . "");
		if (!($row = DB::fetch_row()))
		{
			DB::execute("INSERT INTO audio_invite SET to_user=" . to_sql($user_id, "Number") . ", from_user=" . to_sql($g_user['user_id'], "Number") . "");
			$page = new CAudioInvite("", "./audiochat/invite.html", $user_id);
			$tmp = null;
			$sData = $page->parse($tmp, true);
			$objResponse->addScript("$('#xajax_im').append('".to_js($sData)."');");
		}
		else
		{
			DB::execute("DELETE FROM audio_invite WHERE to_user=" . to_sql($user_id, "Number") . " AND from_user=" . to_sql($g_user['user_id'], "Number") . "");
			DB::execute("INSERT INTO audio_invite SET to_user=" . to_sql($user_id, "Number") . ", from_user=" . to_sql($g_user['user_id'], "Number") . "");
			$page = new CAudioInvite("", "./audiochat/invite.html", $user_id);
			$tmp = null;
			$sData = $page->parse($tmp, true);
			$objResponse->addScript("$('#xajax_im').append('".to_js($sData)."');");
		}
		#$objResponse->addAlert($sData);
	}
	return $objResponse;
}
function audio_update()
{
	global $g_user;
	global $objResponse;

	DB::query("SELECT * FROM audio_invite WHERE to_user=" . to_sql($g_user['user_id'], "Number") . "");
	if ($row = DB::fetch_row())
	{
		$page = new CAudioInvite("", "./audiochat/update.html", $row['from_user']);
		$tmp = null;
		$sData = $page->parse($tmp, true);
		$objResponse->addScript("$('#xajax_im').append('".to_js($sData)."');");
		DB::execute("DELETE FROM audio_invite WHERE to_user=" . to_sql($g_user['user_id'], "Number") . " AND from_user=" . to_sql($row['from_user'], "Number") . "");
	}

	DB::query("SELECT * FROM audio_reject WHERE to_user=" . to_sql($g_user['user_id'], "Number") . "");
	if ($row = DB::fetch_row())
	{
		if ($row['go'] == "N")
		{
			$objResponse->addRemove("xajax_audio_invite");
			$page = new CAudioInvite("", "./audiochat/reject.html", $row['from_user'], "");
			$tmp = null;
			$sData = $page->parse($tmp, true);
			$objResponse->addScript("$('#xajax_im').append('".to_js($sData)."');");
		}
		else
		{
			$objResponse->addRedirect("./audiochat.php?id=" . $row['from_user'] . "");
		}
		DB::execute("DELETE FROM audio_reject WHERE to_user=" . to_sql($g_user['user_id'], "Number") . " AND from_user=" . to_sql($row['from_user'], "Number") . "");
	}

}
function audio_reject($user_id)
{
	global $g_user;
	$objResponse = new xajaxResponse();
	if ($g_user['user_id'] > 0)
	{
		DB::execute("INSERT INTO audio_reject SET to_user=" . to_sql($user_id, "Number") . ", from_user=" . to_sql($g_user['user_id'], "Number") . ", go='N'");
		$objResponse->addRemove("xajax_audio_update");
		#$objResponse->addAlert("INSERT INTO audio_reject SET to_user=" . to_sql($user_id, "Number") . ", from_user=" . to_sql($g_user['user_id'], "Number") . ", go='N'");
	}
	return $objResponse;
}
function audio_go($user_id)
{
	global $g_user;
	$objResponse = new xajaxResponse();
	if ($g_user['user_id'] > 0)
	{
		DB::execute("INSERT INTO audio_reject SET to_user=" . to_sql($user_id, "Number") . ", from_user=" . to_sql($g_user['user_id'], "Number") . ", go='Y'");
		$objResponse->addRedirect("./audiochat.php?id=" . $user_id . "");
		#$objResponse->addAlert("xajax_audio_update");
	}
	return $objResponse;
}

function saveAlbumTitle($new_title, $album_id)
{
	$objResponse = new xajaxResponse();

	if($album_id==0)
	{
		$objResponse->addAssign("TitleEditable", "innerHTML", $new_title);
		return $objResponse;
	}

	$sql = "UPDATE `gallery_albums` SET `title` = '".$new_title."' WHERE `id` = ".$album_id;
	DB::execute($sql);

	$objResponse->addAssign("TitleEditable", "innerHTML", $new_title);
	return $objResponse;
}
function saveAlbumDesc($new_desc, $album_id)
{
	$objResponse = new xajaxResponse();

	if($album_id==0)
	{
		$objResponse->addAssign("DescEditable", "innerHTML", $new_desc);
		return $objResponse;
	}

	$sql = "UPDATE `gallery_albums` SET `desc` = '".$new_desc."' WHERE `id` = ".$album_id;
	DB::execute($sql);

	$objResponse->addAssign("DescEditable", "innerHTML", $new_desc);
	return $objResponse;
}
function saveImageTitle($new_title, $image_id)
{
	$objResponse = new xajaxResponse();

	if($image_id==0)
	{
		$objResponse->addAssign("TitleEditable", "innerHTML", $new_title);
		return $objResponse;
	}

	$sql = "UPDATE `gallery_images` SET `title` = '".$new_title."' WHERE `id` = ".$image_id;
	DB::execute($sql);

	$objResponse->addAssign("TitleEditable", "innerHTML", $new_title);
	return $objResponse;
}
function saveImageDesc($new_desc, $image_id)
{
	$objResponse = new xajaxResponse();

	if($image_id==0)
	{
		$objResponse->addAssign("DescEditable", "innerHTML", $new_desc);
		return $objResponse;
	}
	$sql = "UPDATE `gallery_images` SET `desc` = '".$new_desc."' WHERE `id` = ".$image_id;
	DB::execute($sql);

	$objResponse->addAssign("DescEditable", "innerHTML", $new_desc);
	return $objResponse;
}



function sound(){
    User::saveImSound();

    $objResponse = new xajaxResponse();
    return $objResponse;
}

// WIDGETS

function widget_save($widget, $x, $y, $z) {
	global $g_user;

	$widget = intval(substr($widget, 7));

	if($y<0) $y = 0;
	if($x<0) $x = 0;

    $sql = 'UPDATE `widgets` SET `x` = ' . to_sql($x, 'Number') . ',
        `y` = ' . to_sql($y, 'Number') . ',
        `z` = '.time().'
        WHERE `widget` = ' . to_sql($widget, 'Text') . '
            AND user_id = ' . $g_user['user_id'] . WIDGET_DEMO_WHERE;
    DB::execute($sql);

	$objResponse = new xajaxResponse();
	//$objResponse->addAlert($sql);
	return $objResponse;
}

class CWidget extends CHtmlBlock
{
    private $widget = 0;
    private $open = 0;

    function setWidget($widget)
    {
        $this->widget = $widget;
    }

    function setOpen($open)
    {
        $this->open = $open;
    }

	function __construct($name, $html_path, $user, $open = 0)
	{
		// CUSTOM TEMPLATE
		global $g;
		$template_custom  = $g['tmpl']['dir_tmpl_main']."widget.html";
		if(file_exists($template_custom)) $html_path = $template_custom;
		// CUSTOM TEMPLATE

		parent::__construct($name, $html_path);
        $this->setWidget($user);
        $this->setOpen($open);
	}
	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $g_user;
		global $objResponse;
		global $widgets_titles;
		global $widget_title;
		global $widget_settings;

		DB::query("SELECT * FROM widgets WHERE widget=" . $this->widget . " AND user_id=" . $g_user['user_id'] . WIDGET_DEMO_WHERE);
		if (DB::num_rows()>0)
		{
			$row = DB::fetch_row();

			foreach ($row as $k => $v)
			{
				$html->setvar($k, $v);
			}
			$widget_settings['open'] = $row['open'];
			$html->setvar("widget_open", $row['open']?'':'hidden');
			$widget_class = "bl_widget_shadow";

			// content of selected widget
			$widget_title = "";

			switch($this->widget) {
				case 1:

				$sql = "SELECT COUNT(*) FROM friends_requests AS f
LEFT JOIN user AS u ON u.user_id=IF(f.user_id='".$g_user['user_id']."', f.friend_id, f.user_id) WHERE accepted=1 AND (last_visit>'".(date("Y-m-d H:i:s", time() - $g['options']['online_time'] * 60)). " OR use_as_online=1) " . "' AND (f.user_id='".$g_user['user_id']."' OR f.friend_id='".$g_user['user_id']."')";
				$friends = DB::result($sql);

				$start = DB::result("SELECT settings FROM widgets WHERE widget=1 AND user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE);

				// more then members
				$end = $friends % 3;
				if($end==0 && $start>=($friends-6)) {
				// min show 6 friends
				$start = $friends - 6;
				}
				elseif($start>=($friends - 3 - $end)) {
					$start = $friends - 3 - $end;
				}

				if($start<0) $start = 0;


				$sql = "UPDATE widgets SET settings=$start WHERE widget=1 AND user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE;
				DB::execute($sql);

				$sql = "SELECT f.*,u.*
				FROM friends_requests AS f
LEFT JOIN user AS u ON u.user_id=IF(f.user_id='".$g_user['user_id']."', f.friend_id, f.user_id) WHERE accepted=1 AND (last_visit>'".(date("Y-m-d H:i:s", time() - $g['options']['online_time'] * 60)). " OR use_as_online=1)" . "' AND (f.user_id='".$g_user['user_id']."' OR f.friend_id='".$g_user['user_id']."')
				ORDER BY last_visit DESC, u.user_id DESC LIMIT $start,6";
				DB::query($sql,3);
				$items = DB::num_rows(3);

				while($row2=DB::fetch_row(3)){
					// show user photo
                                        $html->setvar("user_id", $row2['user_id']);
					$html->setvar("name", $row2['name']);
					$html->setvar("name_short",  widgetShortNameHard($row2['name']));

					$photo = User::getPhotoDefault($row2['user_id'],"r");

					$html->setvar("photo",$photo);
                                        if (Common::isOptionActive('im')) {
                                            $html->parse("row_1_im", true);
                                        }
					$html->parse("row_1",true);
				}

				// SHOW NOTHING BLOCKS
				$add = (6 - $items) % 6;
				if($add>0) {
					for($n=0;$n<$add;$n++) $html->parse("row_1_no", true);
				}
				// SHOW NOTHING BLOCKS

				$widget_title = (isset($l['widgets.php']['widget_title_'.$this->widget]) ? $l['widgets.php']['widget_title_'.$this->widget] : "My Friends Online")." <span>($friends)</span>";
				break;


				case 2:

$html->setvar("widget_by",isset($l['widgets.php']['by']) ? $l['widgets.php']['by'] : "By");

$sql = "SELECT * FROM friends_requests WHERE (user_id='".$g_user['user_id']."' OR friend_id='".$g_user['user_id']."') AND accepted=1";
DB::query($sql,3);


				$friends = 0;
				while($row2=DB::fetch_row(3)){
					if($row2['friend_id']!=$g_user['user_id']) $friends .= ",".$row2['friend_id'];
					else $friends .= ",".$row2['user_id'];
				}

//	SELECT NEW BLOGS IN CYCLE UNTIL FOUND 2 with content;

DB::query("SELECT b.*,u.name FROM blogs_post AS b LEFT JOIN user AS u ON u.user_id=b.user_id WHERE u.user_id IN($friends) ORDER BY b.id DESC LIMIT 10");

$b = 0;
				while($row3=DB::fetch_row()){

					$row3['msg'] = strip_tags($row3['text']);
					#$row3['msg'] = preg_replace("/{img:+[^{](.*?)+\}/", "", $row3['msg']);
					#$row3['msg'] = preg_replace("/{youtube:+[^{](.*?)+\}/", "", $row3['msg']);
					$row3['msg'] = preg_replace("/{+[^{](.*?)+\}/", "", $row3['msg']);
					$row3['msg'] = trim($row3['msg']);

					if($row3['msg']=="") continue;
					$b++;

					$row3['msg'] = neat_trim($row3['msg'], 110);

					$row3['dt'] =  date("d.m.Y",time_mysql_dt2u($row3['dt']));
					$row3['subject'] = strip_tags($row3['subject']);
					$row3['subject'] = neat_trim($row3['subject'], 25);
						foreach ($row3 as $k => $v)
							{
								$html->setvar($k, $v);
							}
						$html->parse("row_2");
					if($b==2) break;
					}

				$widget_title = isset($l['widgets.php']['widget_title_'.$this->widget]) ? $l['widgets.php']['widget_title_'.$this->widget] : "My Friends' New Blogs";
				break;

				// photo comments
				case 3:
                    $sqlPhoto = "SELECT IF(pc.id != 0, 'photo', '') AS type, pc.date, pc.photo_id, pc.id, pc.comment, u.name
                                    FROM photo_comments AS pc
                                    LEFT JOIN photo AS p
                                      ON pc.photo_id = p.photo_id
                                    LEFT JOIN user AS u
                                      ON u.user_id = pc.user_id
                                   WHERE pc.user_id != " . to_sql($g_user['user_id'], 'Number') . "
                                     AND p.user_id = " . to_sql($g_user['user_id'], 'Number') . "
                                   ORDER BY date DESC LIMIT 8";
                if (Common::isOptionActive('gallery')) {
                    $photoComment = DB::all($sqlPhoto);

                    $sqlImage = "SELECT IF(pc.id != 0, 'images', '') AS type, pc.date, pc.imageid AS photo_id, pc.id, pc.comment, u.name
                                    FROM gallery_comments AS pc
                                    LEFT JOIN gallery_images AS p
                                      ON pc.imageid = p.id
                                    LEFT JOIN gallery_albums AS g
                                      ON p.albumid = g.id
                                    LEFT JOIN user AS u
                                      ON u.user_id = pc.user_id
                                   WHERE pc.user_id != " . to_sql($g_user['user_id'], 'Number') . "
                                     AND p.user_id = " . to_sql($g_user['user_id'], 'Number') . "
                                   ORDER BY date DESC LIMIT 8";

                    $imageComment = DB::all($sqlImage);

                    $allComment = array_merge($photoComment, $imageComment);
                    uasort($allComment, function($first, $second) {
                                     if ($first['date'] == $second['date'])
                                         return 0;
                                         return ($first['date'] < $second['date']) ? 1 : -1;
                    });
                } else {
                    $allComment = DB::all($sqlPhoto);
                }

                $i = 0;
                $currentId = 0;
                $offsetCurrent = 0;
                foreach ($allComment as $row) {
                    if ($i == 8)
                        break;
                    $html->setvar("name", $row['name']);
					$html->setvar("name_short",  widgetShortNameHard($row['name']));
					$html->setvar("photo_id",$row['photo_id']);
					$html->setvar("cid",$row['id']);
					$html->setvar("comment",mb_strlen($row['comment'],"UTF-8") > 24 ? trim(mb_substr($row['comment'], 0, 24, "UTF-8")) . "..." : $row['comment']);
                    $block = $row['type'];
                    $blockNo = ($row['type'] == 'photo') ? 'images' : 'photo';

                    if (($row['type'] == 'photo') && ($currentId != $row['photo_id'])) {
                        $currentId = $row['photo_id'];
                        $offsetCurrent = User::photoOffset($g_user['user_id'], $currentId);
                    }

                    if ($row['type'] == 'photo')
                        $html->setvar('photo_offset', $offsetCurrent);
                    $html->parse('row_3_' . $block , false);
                    $html->setblockvar('row_3_' . $blockNo, "");
                    $html->parse("row_3", true);
                    $i++;
                }

				$widget_title = isset($l['widgets.php']['widget_title_'.$this->widget]) ? $l['widgets.php']['widget_title_'.$this->widget] : "My Photos Comments";
				break;


				case 4:
				// select friends statuses order by DATE desc
				$sql = "SELECT f.*, u.name, p.status, p.date FROM friends_requests AS f
LEFT JOIN user AS u ON u.user_id=IF(f.user_id='".$g_user['user_id']."', f.friend_id, f.user_id) LEFT JOIN profile_status AS p ON p.user_id=u.user_id WHERE accepted=1 AND (f.user_id='".$g_user['user_id']."' OR f.friend_id='".$g_user['user_id']."')  AND p.status!='' ORDER BY p.date DESC LIMIT 8";
				DB::query($sql);

				while($row=DB::fetch_row()){
					$html->setvar("name",$row['name']);
					$html->setvar("name_short",  widgetShortNameHard($row['name']));
					$html->setvar("status",mb_strlen($row['status'],"UTF-8") > 25 ? trim(mb_substr($row['status'], 0, 25,"UTF-8")) . "..." : $row['status']);
					$html->parse("row_4");
				}
				$widget_title = isset($l['widgets.php']['widget_title_'.$this->widget]) ? $l['widgets.php']['widget_title_'.$this->widget] : "My Friends' Statuses";

				break;


				case 5:

// DAYS titles

$html->setvar("widget_mo",isset($l['widgets.php']['mo']) ? $l['widgets.php']['mo'] : "Mo" );
$html->setvar("widget_tu",isset($l['widgets.php']['tu']) ? $l['widgets.php']['tu'] : "Tu" );
$html->setvar("widget_we",isset($l['widgets.php']['we']) ? $l['widgets.php']['we'] : "We" );
$html->setvar("widget_th",isset($l['widgets.php']['th']) ? $l['widgets.php']['th'] : "Th" );
$html->setvar("widget_fr",isset($l['widgets.php']['fr']) ? $l['widgets.php']['fr'] : "Fr" );
$html->setvar("widget_sa",isset($l['widgets.php']['sa']) ? $l['widgets.php']['sa'] : "Sa" );
$html->setvar("widget_su",isset($l['widgets.php']['su']) ? $l['widgets.php']['su'] : "Su" );

// DAYS titles

$time = time();
$settings = DB::result("SELECT settings FROM widgets WHERE user_id=".$g_user['user_id']." AND widget=5" . WIDGET_DEMO_WHERE);
if($settings>0) {
	$time = $settings;
}

			$date_full = date("Y-m-d");
			$cyear = date('Y',$time);
			$cmonth = date('m',$time);
			$date['d'] = date("d");
			$date['m'] = date('m',$time);
			$date['y'] = date('Y',$time);

			$count_day = date("t",mktime(0, 0, 0, $date['m'], 1, $date['y']));
			$first_day = date("w", mktime(0, 0, 0, $date['m'], 1, $date['y']));
			if($first_day == 0) $first_day = 7;

		$today['mday'] = date("d");
		$today['mon'] = date("m");
		$today['year'] = date("Y");

				$j =1;
				for($k=1;$k<$first_day;$k++){
					$html->setvar("td_class".$j, '');
					$html->setvar("td".$j, '&nbsp;');
					$j++;
				}
				$j = $k-1;
				for($i=$first_day;$i<$count_day+$first_day;$i++){
					$j++;
					if($j == 8){
						$html->parse('row_5',true);
						$j = 1;
					}

$str2 = ($i-$first_day+1);
if($str2<10) $str2 = "0".$str2;

$widgetCalendarDate = "$cyear-$cmonth-$str2";

DB::query("SELECT e.* FROM events_event as e LEFT JOIN events_event_guest as eg ON (e.event_id = eg.event_id AND e.user_id != " . $g_user['user_id'] . ") WHERE ( eg.user_id=" . $g_user['user_id'] . " OR e.user_id = ".$g_user['user_id']." ) AND event_datetime>='".$date['y'].'-'.$date['m'].'-'."$str2 00:00:00' and event_datetime<='".$date['y'].'-'.$date['m'].'-'."$str2 23:59:59' order by event_datetime asc, event_id asc",3);

					$data_rows = DB::num_rows(3);

					$events_today = "";

					while($data = DB::fetch_row(3))
					{
						if($events_today == "") $today_first_id = $data['event_id'];
						$events_today .= ", ".$data['event_title'];
					}
					if($events_today != "")
					{
						$events_today = substr($events_today,2);
						$data['event_id'] = $today_first_id;
					}

					if($today['mday'] == $i-$first_day+1){
						if($data_rows){
								if($cmonth == $today['mon'] && $cyear == $today['year']){
									$html->setvar("td_class".$j,'c_todayandevent');
								}else{
									$html->setvar("td_class".$j,'c_event');
								}
								$str = ($i-$first_day+1);

								if($data_rows>1) $str = "<a title='".htmlentities($events_today,ENT_QUOTES,"UTF-8")."' href='events_search.php?event_datetime=$widgetCalendarDate'>$str</a>";
								elseif($data_rows==1) {

										$str = '<a title="'.htmlentities($events_today,ENT_QUOTES,"UTF-8").'" href="events_event_show.php?event_id='.$data['event_id'].'">'.$str.'</a>';

								}
							}else{
							$str =($i-$first_day+1);
								if(empty($cmonth) && empty($cyear)){
									$html->setvar("td_class".$j,'c_today');
									$str =($i-$first_day+1);
								}else{
									if($cmonth == $today['mon'] && $cyear == $today['year']){
										$html->setvar("td_class".$j,'c_today');
										$str = "<a href='events_event_edit.php?event_private=1&date=$widgetCalendarDate'>$str</a>";
									}else{
										$str = ($i-$first_day+1);
										$str = "<a href='events_event_edit.php?event_private=1&date=$widgetCalendarDate'>$str</a>";
									}
								}
							}

						}else{
							if($data_rows){
								$html->setvar("td_class".$j,'c_event');

								$str = ($i-$first_day+1);

								if($data_rows>1) $str = "<a title='".htmlentities($events_today,ENT_QUOTES,"UTF-8")."' href='events_search.php?event_datetime=$widgetCalendarDate'>$str</a>";
								elseif($data_rows==1) {

										$str = '<a title="'.htmlentities($events_today,ENT_QUOTES,"UTF-8").'" href="events_event_show.php?event_id='.$data['event_id'].'">'.$str.'</a>';

								}
							}else{
								$html->setvar("td_class".$j,'');
								$str = ($i-$first_day+1);
								$str = "<a href='events_event_edit.php?event_private=1&date=$widgetCalendarDate'>$str</a>";
							}
						}
						$html->setvar("td".$j,$str);


				}
				for($k=$j+1;$k<8;$k++){
					$html->setvar("td_class".$k,'');
					$html->setvar("td".$k,'&nbsp;');
				}
				$html->parse('row_5',true);

                if (!function_exists('calendar_title')) {
                    function calendar_title($t) {
                        global $l;
                        return (isset($l['all'][strtolower(date("F",$t))]) ? $l['all'][strtolower(date("F",$t))]." ".date("Y",$t) : date("F Y",$t) );
                    }
                }
				$widget_title =
'<img src="_server/widgets/images/switch_l.gif" onclick="xajax_widget_calendar_shift(0)" alt="&lt" title="◀ '.calendar_title(strtotime('-1 month',$time)).'"> ' . calendar_title($time) . ' <img src="_server/widgets/images/switch_r.gif" onclick="xajax_widget_calendar_shift(1)" alt="&gt" title="'.calendar_title(strtotime('+1 month',$time)).' ▶">';

				$widget_settings['long'] = 0;

				if(($first_day+$count_day)>36) {
					$widget_settings['long'] = 1;
					if($widget_settings['open']) $widget_class = "bl_widget_shadow_calendar";
					$html->setvar("calendar","_3");
					$html->setvar("calendar2","w_5_2");
				}

				break;

                case 6:
				$sql = 'SELECT m.*, u.name
                        FROM mail_msg AS m,
                        user AS u
                    WHERE u.user_id = m.user_from
                        AND m.folder = 1
                        AND m.user_id = m.user_to
                        AND m.user_id = ' . $g_user['user_id'] . '
                    ORDER by id DESC LIMIT 8';
				DB::query($sql);
				while($row = DB::fetch_row()) {
                    $mailIcon = 'ico_asnw_mail';
                    if($row['new'] == 'Y') {
                        $mailIcon = 'ico_new_mail';
                    }
					//$html->setvar("name", $row['name']);
					$html->setvar("mail_id",$row['id']);
					$html->setvar("mail_icon",$mailIcon);
                    $name = $row['name'];
                    if ($row['user_from'] == $g_user['user_id']) {
                        $name = l('website_administration_short');
                    }
					$html->setvar("mail_name", $name);
					$html->setvar("mail_name_short",  widgetShortNameHard($name));
					$html->setvar("mail_subject", mb_strlen($row['subject'],"UTF-8") > 20 ? trim(mb_substr($row['subject'], 0, 20,"UTF-8")) . "..." : $row['subject']);
                    if ($row['user_from'] == $g_user['user_id']) {
                        $html->parse('row_6_admin', false);
                        $html->clean('row_6_user');
                    } else {
                        $html->parse('row_6_user', false);
                        $html->clean('row_6_admin');
                    }
					$html->parse('row_6');
				}
				$widget_title = isset($l['widgets.php']['widget_title_'.$this->widget]) ? $l['widgets.php']['widget_title_'.$this->widget] : "My Mail";

//                $sql = 'SELECT COUNT(*)
//                    FROM mail_msg
//                WHERE new = "Y"
//                    AND user_id = ' . $g_user['user_id'];
//                $count = DB::result($sql);
                $count = $g_user['new_mails'];

                if($count) {
                    $widget_title .= " ($count)";
                }

                break;

                case 7:
                #1
                /*$sql = 'SELECT DISTINCT (topic_id) AS id
                            FROM forum_message
                           WHERE user_id = ' . $g_user['user_id'];

                $topicsByMessage = DB::rows($sql);

				$sql = 'SELECT `id`
                          FROM `forum_topic`
                         WHERE `user_id` = ' . $g_user['user_id'];

                $topicsByUser = DB::rows($sql);

                $topics = array(0);

                foreach($topicsByMessage as $k => $v) {
                    $topics[] = $v['id'];
                }
                foreach($topicsByUser as $k => $v) {
                    $topics[] = $v['id'];
                }

                $topics = array_unique($topics);*/
                #2
                /*$sql = 'SELECT DISTINCT (T2.id) AS id
                          FROM `forum_message` M,
                               `forum_topic` T,
                               `forum_forum` F,
                               `forum_topic` T2
                         WHERE ((M.user_id = ' . to_sql($g_user['user_id'], 'Number') . ' AND T.id = M.topic_id)
                              OR T.user_id = ' . to_sql($g_user['user_id'], 'Number') . ')
                           AND F.id = T.forum_id
                           AND T2.forum_id = F.id';

                $topicsAll = DB::rows($sql);

                $topics = array(0);
                foreach($topicsAll as $k => $v) {
                    $topics[] = $v['id'];
                }

                $topicsList = implode(',', $topics);

                $sql = 'SELECT M2.*, U.name, T.title
                    FROM (SELECT *, MAX(id) AS maxid FROM forum_message
                        WHERE topic_id IN(' . $topicsList . ')
                        GROUP BY topic_id
                        ORDER BY maxid DESC) AS M
                    JOIN forum_message AS M2
                    JOIN user AS U
                    JOIN forum_topic AS T
                    WHERE M2.id = M.maxid
                        AND M2.user_id != ' . $g_user['user_id'] . '
                        AND U.user_id = M2.user_id
                        AND T.id = M2.topic_id
                    ORDER BY M.maxid DESC
                    LIMIT 8
                ';*/

                $sql = 'SELECT DISTINCT (T2.id) AS id
                          FROM `forum_message` M,
                               `forum_topic` T,
                               `forum_forum` F,
                               `forum_topic` T2
                         WHERE ((M.user_id = ' . to_sql($g_user['user_id'], 'Number') . ' AND T.id = M.topic_id)
                              OR T.user_id = ' . to_sql($g_user['user_id'], 'Number') . ')
                           AND F.id = T.forum_id
                           AND T2.forum_id = F.id';

                $topicsAll = DB::rows($sql);

                $topics = array(0);
                foreach($topicsAll as $k => $v) {
                    $topics[] = $v['id'];
                }
                $topicsList = implode(',', $topics);

                $sql = 'SELECT T.id, T.title, T.created_at, COUNT(M.id) AS count, U.name
                          FROM `forum_topic` T
                     LEFT JOIN `forum_message` M
                            ON M.topic_id = T.id
                          JOIN user AS U
                         WHERE T.id IN(' . $topicsList . ')
                           AND T.user_id !=' . to_sql($g_user['user_id'], 'Number') . '
                           AND U.user_id = T.user_id
                      GROUP BY T.id
                      ORDER BY T.id';
                $topicsAll = DB::rows($sql);

                $topicsNoMsg = array();
                foreach($topicsAll as $k => $v) {
                    if ($v['count'] == 0)
                        $topicsNoMsg[$v['id']] = array('topic_id' => $v['id'],
                                                       'id' => $v['count'],
                                                       'name' => $v['name'],
                                                       'title' => $v['title'],
                                                       'date' => $v['created_at']);
                }
                $sql = 'SELECT M2.id, M2.created_at, U.name, T.title, T.id as topic_id
                    FROM (SELECT *, MAX(id) AS maxid FROM forum_message
                        WHERE topic_id IN(' . $topicsList . ')
                        GROUP BY topic_id
                        ORDER BY maxid DESC) AS M
                    JOIN forum_message AS M2
                    JOIN user AS U
                    JOIN forum_topic AS T
                    WHERE M2.id = M.maxid
                        AND M2.user_id != ' . to_sql($g_user['user_id'], 'Number') . '
                        AND U.user_id = M2.user_id
                        AND T.id = M2.topic_id
                    ORDER BY M.maxid DESC
                    LIMIT 8
                ';
                $topicsAll = DB::rows($sql);
                $topicsYesMsg = array();
                foreach($topicsAll as $k => $v) {
                    $topicsYesMsg[$v['topic_id']] = array('topic_id' => $v['topic_id'],
                                                          'id' => $v['id'],
                                                          'name' => $v['name'],
                                                          'title' => $v['title'],
                                                          'date' => $v['created_at']);
                }

                $topics = array_merge($topicsNoMsg, $topicsYesMsg);
                uasort($topics, function($first, $second) {
                                     if ($first['date'] == $second['date'])
                                         return 0;
                                         return ($first['date'] < $second['date']) ? 1 : -1;
                });
                //print_r($topics);
				/*#1,#2
                DB::query($sql);
				while($row = DB::fetch_row()) {
					$html->setvar("forum_topic_id",$row['topic_id']);
					$html->setvar("forum_message_id",$row['id']);
					$html->setvar("forum_name", $row['name']);
					$html->setvar("forum_name_short", widgetShortName($row['name']));
					$html->setvar("forum_subject", mb_strlen($row['title'],"UTF-8") > 20 ? trim(mb_substr($row['title'], 0, 20,"UTF-8")) . "..." : $row['title']);
					$html->parse('row_7');
				}*/
                $i = 0;
                foreach ($topics as $item) {
                    if ($i == 8) break;
                    $html->setvar("forum_topic_id",$item['topic_id']);
					$html->setvar("forum_message_id",$item['id']);
					$html->setvar("forum_name", $item['name']);
					$html->setvar("forum_name_short",  widgetShortNameHard($item['name']));
					$html->setvar("forum_subject", mb_strlen($item['title'],"UTF-8") > 20 ? trim(mb_substr($item['title'], 0, 20,"UTF-8")) . "..." : $item['title']);
					$html->parse('row_7');
                    $i++;
                }
                $widget_title = lp('widget_title_'.$this->widget, 'widgets.php');
				//$widget_title = isset($l['widgets.php']['widget_title_'.$this->widget]) ? $l['widgets.php']['widget_title_'.$this->widget] : "New Forum Posts";
                break;

                case 8:
                $sql = 'SELECT I.*, U.name, A.folder
                    FROM gallery_images AS I,
                    user AS U,
                    gallery_albums AS A
                    WHERE I.user_id != ' . $g_user['user_id'] . '
                        AND U.user_id = I.user_id
                        AND A.id = I.albumid
                    ORDER BY I.id DESC LIMIT 2';
                DB::query($sql);

                $rowClass = 'pn_l';

                while($row = DB::fetch_row()) {
                    $html->setvar("row_class",$rowClass);
                    $html->setvar("photo_id",$row['id']);
                    $html->setvar("photo_name",$row['name']);
                    $html->setvar("photo_title", mb_strlen($row['title'],"UTF-8") > 20 ? trim(mb_substr($row['title'], 0, 20,"UTF-8")) . "..." : $row['title']);

                    if($row['title'] == '') {
                        $html->setblockvar('row_8_title', '');
                    } else {
                        $html->parse('row_8_title', false);
                    }

                    $thumb = $g['dir_files'] . "gallery/thumb/".$row['user_id']."/".$row['folder']."/".$row['filename'];
                    $html->setvar("photo_url", $thumb);

                    $html->parse('row_8');
                    $rowClass = 'pn_r';
                }
                $widget_title = isset($l['widgets.php']['widget_title_'.$this->widget]) ? $l['widgets.php']['widget_title_'.$this->widget] : "New Photos";

                break;

                case 9:
                //$sql = 'выбрать все записи пользователя';
//                DB::query($sql);
//
//				while($row = DB::fetch_row()) {
//                	$html->parse('row_9');
//				}
				$widget_title = isset($l['widgets.php']['widget_title_'.$this->widget]) ? $l['widgets.php']['widget_title_'.$this->widget] : "My Notes";
                $html->setvar("add_note_button",'<a href="#"><img class="img_add" src="./_server/widgets/images/ico_add_note.png" width="12" height="12"  alt=""></a>');


                break;

			}

			$html->parse("open_".$this->widget);

			$html->setvar("widget_title", $widget_title);
			$html->setvar("widget",$this->widget);

			$html->setvar("widget_class",$widget_class);

			if($this->open == 0) {
				$html->parse("widget");
				$html->parse("widget_end");
			}

			parent::parseBlock($html);
		}
	}
}

function widget($is_home_widget = false, $returnObjResponse = true)
{
	global $g_user;
	global $objResponse;
	global $g;

    $js = '';
    if ($returnObjResponse) {
        $objResponse = new xajaxResponse();
    }

	// nothing if disabled
	// if(isset($g['options']['widgets']) && $g['options']['widgets']=="N") return $objResponse;

	if ($g_user['user_id'] > 0)
	{
        if(IS_DEMO) {
            // WIDGETS AUTOCLEAN
            $sql = 'DELETE FROM widgets
                WHERE session != ""
                AND session_date < DATE_SUB(NOW(),
                    INTERVAL ' . WIDGET_DEMO_CLEAN_AFTER_DAYS . ' DAY)';
            DB::execute($sql);
        }

		$js = "(function(){ var timer=0;";
		$where = WIDGET_DEMO_WHERE;
		$data = "";
		$where .= ($is_home_widget=="false")?" AND status<2" : " AND status<3";
		$sql = "SELECT * FROM widgets WHERE user_id=" . to_sql($g_user['user_id'], "Number") . " $where ORDER BY z ASC";
		DB::query($sql,2);

		while ($row = DB::fetch_row(2))
		{
//          $page = new CWidget("", "./widgets/widget.html", $row['widget']);
//			$tmp = null;
//			$data = to_js($page->parse($tmp, true));
			$data = to_js(WidgetParser::run($row['widget']));
			$js .= "
	if (!widgetStatus[{$row['widget']}]) setTimeout(function(){
		Drag.init($('$data').insertBefore('[id*=\"xajax_im_open\"]:eq(0)')[0].id)
	}, timer+=100);
	widgetStatusSet({$row['widget']}, 'loaded');";
		}
        WidgetParser::clean();
				// IM FIX
		// $js .= "for(n in opens)
			// {
				// if (document.getElementById('xajax_im_open_' + opens[n]))
				// {
					// Drag.init(document.getElementById('xajax_im_head_' + opens[n]), document.getElementById('xajax_im_open_' + opens[n]));
				// }
			// }";
            // // error tinyscrollbar() not exists
            // $js .= updateScrollbar();
		// $objResponse->addAppend("xajax_im", "innerHTML", $data);

// DEMO: start widgets
        $demoWidgetsCheck = get_session('demo_widgets');
        if(WIDGET_DEMO_TEST) {
            $demoWidgetsCheck = false;
        }
        if(IS_DEMO && !$demoWidgetsCheck) {
            $js .= "demoImSetToTop = true;";
            foreach($g['widget_demo_order'] as $widgetIndex) {
                $js .= "document.demoWidgetCoordX = 0;
                    xajax_widget_site($widgetIndex, true);";
            }

            foreach($g['widget_demo_left_order'] as $widgetIndex) {
                $js .= "document.demoWidgetCoordX = 0;
                    xajax_widget_site($widgetIndex, true);";
            }

            set_session('demo_widgets', true);
        }
// DEMO: start widgets

		$js.="})();";
        if ($returnObjResponse) {
            $objResponse->addScript($js);
        }
		//$objResponse->addAlert($sData);
	}
    if ($returnObjResponse) {
        return $objResponse;
    } else {
        return $js;
    }
}

function widget_show($widget,$open){
	global $g_user;
	$objResponse = new xajaxResponse();

	if($open!=1) {
		$open = 0;
		$open2 = 1;
	}
	else $open2=0;

	$sql = "UPDATE widgets SET open=".to_sql($open,"Number")." WHERE widget=".to_sql($widget,"Text")." AND user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE;
	DB::execute($sql);

	// change open function ONCLICK element
	// $js = "document.getElementById('widget_show_$widget').onclick = function(){
				// widget_show($widget,$open2); return false;
			// }";

	// remove/load content
	// if($open==1) {
		// $data = "TEXT";
		// $page = new CWidget("", "./widgets/widget.html", $widget,1);
		// $tmp = null;
		// $data = $page->parse($tmp, true);
		// unset($page);

		// //$objResponse->addAppend("widget_".$widget."_content", "innerHTML", $data);
	// }

// $js .= "widgets_count=".DB::result("SELECT COUNT(*) FROM widgets WHERE user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE);

	//$objResponse->addScript($js);
	return $objResponse;
}

function widget_close($widget,$z){
	global $g_user;
	$objResponse = new xajaxResponse();

	// $sql = "DELETE FROM widgets WHERE widget=".to_sql($widget,"Text")." AND user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE;
	// DB::execute($sql);

	// UPDATE positions of other widgets
	#$sql = "UPDATE widgets SET z=z-1 WHERE z>".to_sql($z,"Number")." AND user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE;
	#DB::execute($sql);
	//$objResponse->addAlert($sql);

	$js = "widgets_count=".DB::result("SELECT COUNT(*) FROM widgets WHERE user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE);
	$objResponse->addScript($js);

	$sql = "UPDATE widgets SET status=3 WHERE widget=".to_sql($widget,"Text")." AND user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE;
	DB::execute($sql);

	return $objResponse;
}

function widget_site($widget, $demo=false){
    global $g_user;
    global $g;
    $objResponse = new xajaxResponse();
    $load = false;

    $tmplCurrent = Common::getOption('tmpl_loaded', 'tmpl');

    // widgets ALL status < 3 AND

    DB::query("SELECT * FROM widgets WHERE user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE);
    $widgets = DB::num_rows();

    $js = "";

    // check widget status status < 3 AND
    DB::query("SELECT * FROM widgets WHERE widget=".to_sql($widget,"Number")." AND user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE);
    if(DB::num_rows()==0) {
		$sql = 'INSERT INTO widgets
                           SET user_id = ' . to_sql($g_user['user_id']) . ',
                               widget = ' . to_sql($widget, 'Number') . ',
                               status = 1,
                               open = 1,
                               x = 0,
                               y = 0,
                               z = ' . time() . ',
                               settings = 0 ' . WIDGET_DEMO_INSERT;
                // load widget right NOW
		$load = true;
		// JS POSITION
                /*if($demo) {
                    $coordsObject = 'header';
                    if($tmplCurrent == 'mixer') {
                        $coordsObject = 'wrapper';
                    }

                } else {
                    $coordsObject = 'row_' . $widget;
                }
                // FIX for fixed position - start near widget description
                $js = "coords = getAbsolutePosition('$coordsObject');
                       coords.y = coords.y - $(document).scrollTop();
                       coords.x = coords.x - $(document).scrollLeft();
                    ";*/
    } else{

		$row = DB::fetch_row();
		// find max position
		// DB::query("SELECT * FROM widgets WHERE user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE);
		// $z = DB::num_rows();
		// shift other widgets down
		// DB::execute("UPDATE widgets SET z=z-1 WHERE z>".$row['z']." AND user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE);

		$sql = "UPDATE widgets SET status=1, open = 1, z = ".time().", settings = 0  "
		.WIDGET_DEMO_INSERT." WHERE widget=".to_sql($widget,"Number").
		" AND user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE;
		//if($row['status']>1)
		$load = true;
    }
    DB::execute($sql);

    $widget_index = intval($widget);

    $widgetHeightBase = 50;
    $w_top = (intval(($widget_index-1)/2))*($widgetHeightBase) + 50;

    $w_left = 520;
    if($widget_index%2 === 0) {
    	$w_left = 0;
    }


	if ($load) {
                // JS POSITION
                if($demo) {
                    $coordsObject = 'header';
                    if($tmplCurrent == 'mixer') {
                        $coordsObject = 'wrapper';
                    }

                } else {
                    $coordsObject = 'row_' . $widget;
                }
                // FIX for fixed position - start near widget description
                $js = "coords = getAbsolutePosition('$coordsObject');
                       coords.y = ". $w_top .";
                       coords.x = coords.x - $(document).scrollLeft() - ". $w_left .";

                    ";

			$js .= "
var newEl=$('".to_js(WidgetParser::run($widget))."');";
			WidgetParser::clean();

			//$objResponse->addAppend("xajax_im", "innerHTML", $data);

			// $js .= "widgets_count=".DB::result("SELECT COUNT(*) FROM widgets WHERE user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE) . ';';

			// // DOWN Z of other widgets!!!

			// $js .= "widget_down(".$widget.",".$z.");";

            // modify positions of autoloaded widgets
            if($demo) {

                $widgetHeight = WIDGET_DEMO_HEIGHT;

                $widgetIndex = array_search($widget, $g['widget_demo_order']);

                $widgetHeightDelta = '';
                if($widgetIndex == ( count( $g['widget_demo_order'] ) - 1) ) {
                    $widgetHeightDelta = ' + (' . WIDGET_DEMO_HEIGHT_DELTA . ')';
                }


                $widgetRightSideOffset = '';
                if($tmplCurrent == 'oryx') {
                    $widgetRightSideOffset = ' - ' . WIDGET_DEMO_OFFSET_LEFT_ORYX;
                }

                if($tmplCurrent == 'mixer') {
                    $widgetRightSideOffset = ' + ' . WIDGET_DEMO_OFFSET_LEFT_MIXER;
                }

                if($widgetIndex !== false) {
                // prevent FF random left position after page scrollbar appear
                $js .= " if(document.demoWidgetCoordX == 0) {
                    document.demoWidgetCoordX = coords.x;
                }";
                $js .= "coords.x = document.demoWidgetCoordX + " . WIDGET_DEMO_OFFSET_LEFT . " $widgetRightSideOffset;";

                $startOffsetTopDelta = '';
                if($widgetIndex == 0) {
                    //$startOffsetTopDelta = ' - 10 ';
                }

                $js .= "coords.y = " . WIDGET_DEMO_OFFSET_TOP . $startOffsetTopDelta
                    . " + " . $widgetHeight . " * $widgetIndex $widgetHeightDelta;";

                }

// LEFT WIDGETS
                $widgetHeight = WIDGET_DEMO_HEIGHT;

                $widgetIndex = array_search($widget, $g['widget_demo_left_order']);

                if($widgetIndex !== false) {
                    $widgetHeightDelta = '';
                if($widgetIndex == ( count( $g['widget_demo_left_order'] ) - 1) ) {
                    $widgetHeightDelta = ' + (' . WIDGET_DEMO_HEIGHT_DELTA_LEFT . ')';
                } else {
                    $widgetHeightDelta = ' ';
                }
                    // prevent FF random left position after page scrollbar appear

                $widgetLeftOffset = WIDGET_LEFT_OFFSET;

                if($tmplCurrent == 'mixer') {
                    $widgetLeftOffset = WIDGET_LEFT_OFFSET_MIXER;
                }


                if($tmplCurrent == 'oryx') {
                    $widgetLeftOffset = WIDGET_LEFT_OFFSET_ORYX;
                }

                    $js .= "
                        coordsWrapper = getAbsolutePositionReal('wrapper');
                        imWidthOffset = " . IM_WIDTH_OFFSET . ";

                        coordXoffset = " . $widgetLeftOffset . ";
                        coordX = coordXoffset;

                        if(coordsWrapper.x > imWidthOffset) {

                            coordX = coordsWrapper.x - imWidthOffset + coordXoffset;
                        }
                        coords.x = coordX;
                        ";
                    $js .= "coords.y = " . WIDGET_DEMO_OFFSET_TOP_LEFT
                        . " + " . $widgetHeight . " * $widgetIndex $widgetHeightDelta;";
                }


// LEFT WIDGETS


                #$js .= " alert(coords.x + ':' + coords.y); ";
            }

		// show widget at widgets page

            if($widget_index%2 === 0) {
	        	$left_right = 'left: coords.x';

	        } else {
	        	$left_right = 'right:coords.x, left: "auto"';
	        }
            $js .= "
if(widgetIsLoaded($widget) == 'none') {
    console.log('widget_$widget load');
    widgetStatusSet($widget, 'loaded');
    $('.bl_widget_cont', newEl).css('margin-top', -175);
    Drag.init(newEl.css({ left: coords.x, top:coords.y}).appendTo($('#xajax_im'))[0].id);
    xajax_widget_save('widget_$widget',coords.x,coords.y,1);
} else {
    console.log('widget_$widget already loaded');
}";

            if(IS_DEMO && ($widget == 5 || $widget == 7 || $widget == 2))  {
                $js .= "if(typeof(demoImSetToTop) != 'undefined') { $('#xajax_im_open_12').css('z-index', 1).removeClass('active').not(':last-child').appendTo('#xajax_im').mouseover(); console.log('im_up_from_$widget'); }";
            }
		}

		// for($i=1;$i<=9;$i++) {
		// $js .= "if(document.getElementById('widget_".$i."')) Drag.init(document.getElementById('widget_".$i."'));";
		// IM FIX
		// $js .= "for(n in opens)
			// {
				// if (document.getElementById('xajax_im_open_' + opens[n]))
				// {
					// Drag.init(document.getElementById('xajax_im_head_' + opens[n]), document.getElementById('xajax_im_open_' + opens[n]));
				// }
			// }";
		// }

                // DB::query("SELECT * FROM im_open WHERE from_user=" . to_sql($g_user['user_id'], "Number") . " AND mid > 0");
                // while ($rowet = DB::fetch_row())
                // {
                    // $js .= "$('#xajax_im_open_".$rowet['to_user']."').tinyscrollbar();
                    // $('#xajax_im_open_".$rowet['to_user']."').tinyscrollbar_update('bottom');";
                // }

		$objResponse->addScript($js);

		// var_dump($objResponse); die(); exit;
	return $objResponse;
	//echo $js; die();
}

function widget_home($widget){
	global $g_user;
	$objResponse = new xajaxResponse();

	// find max position
	DB::query("SELECT * FROM widgets WHERE user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE);
	$z = DB::num_rows();

	// check widget status
	DB::query("SELECT * FROM widgets WHERE widget=".to_sql($widget,"Number")." AND user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE);
	if(DB::num_rows()==0) {
		$sql = 'INSERT INTO widgets SET
            user_id = ' . to_sql($g_user['user_id']) . ',
            widget = ' . to_sql($widget, 'Number') . ',
            status = 2,
            open = 1,
            x = 0,
            y = 0,
            z = ' . to_sql($z, 'Number') . ',
            settings = 0 ' . WIDGET_DEMO_INSERT;
	} else {
        $sql = "UPDATE widgets SET status=2 WHERE widget=".to_sql($widget,"Number")." AND user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE;
    }
	DB::execute($sql);

	$js = "widgets_count=".DB::result("SELECT COUNT(*) FROM widgets WHERE user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE);
	$objResponse->addScript($js);

	return $objResponse;
}

function widget_up($widget,$z,$z_old){
	global $g_user;
	$objResponse = new xajaxResponse();

	// down top widgets
	$sql = "UPDATE widgets SET z=z-1 WHERE z>".to_sql($z_old,"Number")." AND user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE;
	DB::execute($sql);
	//$objResponse->addAlert($sql);

	// update widget Z
	$sql = "UPDATE widgets SET z=".to_sql($z,"Number")." WHERE widget=".to_sql($widget,"Number")." AND user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE;
	DB::execute($sql);
	//$objResponse->addAlert($sql);

	return $objResponse;
}

function widget_update($status, $return=true){
	global $g_user;
	global $objResponse;
	global $g;
	if ($return) $objResponse = new xajaxResponse();

	// nothing update if disabled
	if(isset($g['options']['widgets']) && $g['options']['widgets']=="N") return $objResponse;

	// update content of widget: title & content

	// check if widget exists on site
	//$objResponse->addScript("console.log('widgets')");
	DB::query("SELECT * FROM widgets WHERE status<".to_sql($status+1,"Number")." AND user_id=".$g_user['user_id'].($status==6?" AND widget=6":"") . WIDGET_DEMO_WHERE, 2);
	while ($w = DB::fetch_row(2)) {
		// if($w['status']==3 || $w['widget']==0) $js = "widget_close($widget);";
		// if($w['status']==1) $js = "if(!$('#widget_$widget')[0]) xajax_widget_site($widget);";

		$objResponse->addScript("console.log('w', ".$w['widget']."); ");

		switch($w['widget']){
			case 1:
			widget_friends();
			break;

			case 2:
			widget_blogs();
			break;

			case 3:
			widget_comments();
			break;

			case 4:
			widget_status();
			break;

			case 5:
			widget_calendar();
			break;

			case 6:
			widget_mails();
			break;

			case 7:
			widget_forum();
			break;

			case 8:
			widget_photos();
			break;

		}
	}

    WidgetParser::clean();

	if ($return) return $objResponse;
}


function nd_filter_change($is_nd) {
	global $g_user;
	$objResponse = new xajaxResponse();
	if ($g_user['user_id'] > 0)
	{
		$sql = "UPDATE user SET isnd_filter = '$is_nd' "." WHERE user_id = ".$g_user['user_id'];
		DB::execute($sql);
// 		$objResponse->addScript("alert('$is_nd');");
	}
	return $objResponse;
}

function widget_friends(){
	global $g_user;
	global $objResponse;
	global $g;
	global $l;

    $data = WidgetParser::run(1, 1);

$sql = "SELECT COUNT(*) FROM friends_requests AS f
LEFT JOIN user AS u ON u.user_id=IF(f.user_id='".$g_user['user_id']."', f.friend_id, f.user_id) WHERE accepted=1 AND (last_visit>'".(date("Y-m-d H:i:s", time() - $g['options']['online_time'] * 60)). " OR use_as_online=1)" . "' AND (f.user_id='".$g_user['user_id']."' OR f.friend_id='".$g_user['user_id']."')";

	$friends = DB::result($sql);

// title
	$title=(isset($l['widgets.php']['widget_title_1']) ? $l['widgets.php']['widget_title_1'] : "My Friends Online");
	$objResponse->addClear("widget_title_1", "innerHTML");
	$objResponse->addAppend("widget_title_1", "innerHTML", $title." <span>($friends)</span>");
	$objResponse->addClear("widget_1", "title");
	$objResponse->addAppend("widget_1", "title", $title." ($friends)");
// content
	$objResponse->addClear("widget_inner_1", "innerHTML");
	$objResponse->addAppend("widget_inner_1", "innerHTML", $data);
}

function widget_friends_scroll($direction){
	global $g_user;
	global $objResponse;
	$objResponse = new xajaxResponse();

	if($direction=="1") {
		$sql = "UPDATE widgets SET settings=settings+3 WHERE widget=1 AND user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE;
	}
	else {
		$sql = "UPDATE widgets SET settings=settings-3 WHERE widget=1 AND user_id=".$g_user['user_id'] . WIDGET_DEMO_WHERE;
	}
	DB::execute($sql);

	widget_friends();

    return $objResponse;
}

function widget_blogs(){
	global $g_user;
	global $objResponse;
	global $widgets_titles;
	global $g;

    $data = WidgetParser::run(2, 1);

	// content
	$objResponse->addClear("widget_inner_2", "innerHTML");
	$objResponse->addAppend("widget_inner_2", "innerHTML", $data);
}

function widget_comments(){
	global $g_user;
	global $objResponse;
	global $widgets_titles;
	global $g;

    $data = WidgetParser::run(3, 1);

	// content
	$objResponse->addClear("widget_inner_3", "innerHTML");
	$objResponse->addAppend("widget_inner_3", "innerHTML", $data);
}

function widget_calendar(){
	global $g_user;
	global $objResponse;
	global $widgets_titles;
	global $widget_title;
	global $g;
	global $widget_settings;

    $data = WidgetParser::run(5, 1);

	// change style for long month and title
	$js =
"$('#widget_5')[0].title=$.trim($('#widget_title_5').text());
widgets_calendar_long=!!".$widget_settings['long'].";
$('#widget_content_5').".($widget_settings['long']?"addClass":"removeClass")."('w_5_2');";
	// content
	$objResponse->addClear("widget_inner_5", "innerHTML");
	$objResponse->addAppend("widget_inner_5", "innerHTML", $data);
	$objResponse->addClear("widget_title_5", "innerHTML");
	$objResponse->addAppend("widget_title_5", "innerHTML", $widget_title);
	$objResponse->addScript($js);
}

function widget_calendar_shift($shift){
	global $g_user;
	global $objResponse;
	$objResponse = new xajaxResponse();

	$time = time();
	$settings = DB::result("SELECT settings FROM widgets WHERE user_id=".$g_user['user_id']." AND widget=5" . WIDGET_DEMO_WHERE);
	if($settings>0) {
		$time = $settings;
	}
	if($shift>0) $time = strtotime("+1 month",$time);
	else $time = strtotime("-1 month",$time);
	DB::execute("UPDATE widgets SET settings=$time WHERE user_id=".$g_user['user_id']." AND widget=5" . WIDGET_DEMO_WHERE);
	widget_calendar();

	return $objResponse;
}

function widget_status(){
	global $objResponse;

    $data = WidgetParser::run(4, 1);

// content
	$objResponse->addClear("widget_inner_4", "innerHTML");
	$objResponse->addAppend("widget_inner_4", "innerHTML", $data);
}

function widget_mails(){
	global $g_user;
	global $objResponse;
	global $widgets_titles;
	global $g;
    global $l;

    $data = WidgetParser::run(6, 1);

// title
    $widget_title = isset($l['widgets.php']['widget_title_6']) ? $l['widgets.php']['widget_title_6'] : "My Mail";

    $count = $g_user['new_mails'];

    if($count) {
        $widget_title .= " ($count)";
    }

	$objResponse->addClear("widget_title_6", "innerHTML");
	$objResponse->addAppend("widget_title_6", "innerHTML", $widget_title);
	$objResponse->addClear("widget_6", "title");
	$objResponse->addAppend("widget_6", "title", $widget_title);
	// content
	$objResponse->addClear("widget_inner_6", "innerHTML");
	$objResponse->addAppend("widget_inner_6", "innerHTML", $data);
}

function widget_forum(){
	global $g_user;
	global $objResponse;
	global $widgets_titles;
	global $g;

    $data = WidgetParser::run(7, 1);

	// content
	$objResponse->addClear("widget_inner_7", "innerHTML");
	$objResponse->addAppend("widget_inner_7", "innerHTML", $data);
}

function widget_photos(){
	global $g_user;
	global $objResponse;
	global $widgets_titles;
	global $g;

    $data = WidgetParser::run(8, 1);

	// content
	$objResponse->addClear("widget_inner_8", "innerHTML");
	$objResponse->addAppend("widget_inner_8", "innerHTML", $data);
}


// WIDGETS

// PROFILE STATUS

function profile_status($status)
{
	global $g_user,$l;
	$objResponse = new xajaxResponse();

	// delete if exists
	$sql = "DELETE FROM profile_status WHERE user_id=".$g_user['user_id'];
	DB::execute($sql);

	if(trim($status)!="") {
	$sql = "INSERT INTO profile_status VALUES(".$g_user['user_id'].",".to_sql($status,"Text").",NOW())";
	DB::execute($sql);
        Wall::setUid(guid());
        Wall::add('status', 0, false, trim($status));
	}
	else $status = isset($l['home.php']['your_status_here']) ? $l['home.php']['your_status_here'] : "Your status here...";

	$objResponse->addAssign("profile_status", "innerHTML", $status);
	return $objResponse;
}

// PROFILE STATUS


// PROFILE PHOTO

function photo_default($photo_id, $label_id = '')
{
	global $g_user;
	$objResponse = new xajaxResponse();

    User::photoToDefault($photo_id);
    if ($label_id != '') {
        $js = "$('[id ^= lb_default_]').text('" . l('make_default') . "');
               $('#" . $label_id . "').text('" . l('default') . "');";
        $objResponse->addScript($js);
    }
	return $objResponse;
}

function photo_private($photo_id, $label_id = '')
{
	global $g_user;
	$objResponse = new xajaxResponse();

	$sql = "SELECT private FROM photo WHERE photo_id=".to_sql($photo_id,"Number")." AND user_id=".$g_user['user_id'];
	$private = DB::result($sql);

    if ($private == "Y") {
        $private = "N";
        $access = 'public';
        $title = l('make_private');
    } else {
        $private = "Y";
        $access = 'friends';
        $title = l('private');
    }
    if ($label_id != '') {
        $objResponse->addScript('$(\'#' . $label_id . '\').text(\'' . $title . '\');');
    }
	$sql = "UPDATE photo SET `private`='$private' WHERE photo_id=".to_sql($photo_id,"Number")." AND user_id=".$g_user['user_id'];
	DB::execute($sql);
    Wall::UpdateAccessPhoto($photo_id, $access);
    User::setAvailabilityPublicPhoto($g_user['user_id']);

	return $objResponse;
}

function profile_photo_save_title($new_title, $photo_id)
{
	global $g_user;
	$objResponse = new xajaxResponse();

	if($photo_id==0)
	{
		return $objResponse;
	}

	if(trim($new_title)=="") $new_title = DB::result("SELECT photo_name FROM photo WHERE user_id=".$g_user['user_id']." AND `photo_id` = ".to_sql($photo_id,"Number"));

	$sql = "UPDATE `photo` SET `photo_name` = ".to_sql($new_title,"Text")." WHERE user_id=".$g_user['user_id']." AND `photo_id` = ".to_sql($photo_id,"Number");
	DB::execute($sql);

	$objResponse->addAssign("TitleEditable$photo_id", "innerHTML", $new_title);
	return $objResponse;
}

function profile_photo_save_desc($new_title, $photo_id)
{
	global $g_user;
	$objResponse = new xajaxResponse();

	if($photo_id==0)
	{
		return $objResponse;
	}

    $new_title = trim($new_title);

	$sql = "UPDATE `photo` SET `description` = ".to_sql($new_title,"Text")." WHERE user_id=".$g_user['user_id']." AND `photo_id` = ".to_sql($photo_id,"Number");
	DB::execute($sql);

	$objResponse->addAssign("DescEditable$photo_id", "innerHTML", $new_title);
	return $objResponse;
}

// PROFILE PHOTO
function update_info_user()
{
    global $objResponse;
    global $g_info;
    global $g_user;
    global $g;
    global $js;

    $isMailActive = Common::isOptionActive('mail');

    if($isMailActive) {
        $js .= "if($('#xajax_new_mail').length) {";
        $js .= "if ($('#xajax_new_mail').text() < " . $g_info['new_mails'] . ")" . playSound();
        $js .= "$('#xajax_new_mail').text(" . $g_info['new_mails'] . ");";
        $js .= "}";
    }

    $numberCityVisitors = json_encode(City::getNumberUsersVisitors());
    $js .= "if(typeof city === 'object' && typeof city.updateNumberUsersVisitors === 'function'){";
    $js .= "city.updateNumberUsersVisitors({$numberCityVisitors});}";

    //$objResponse->addAssign("xajax_new_mail", "innerHTML", $g_info['new_mails']);
    $objResponse->addAssign("xajax_new_wink", "innerHTML", $g_info['new_interest']);
    $objResponse->addAssign("xajax_online_user", "innerHTML", $g_info['users_online']);
    if (Common::isOptionActive('header_block_info', 'template_options')) {

        if($isMailActive) {
            if ($g_info['new_mails'] == 1) {
                $sql = 'SELECT `id`
                          FROM `mail_msg`
                         WHERE `user_id` = ' . to_sql($g_user['user_id'], 'Number') . '
                           AND `user_to` = ' . to_sql($g_user['user_id'], 'Number') . '
                           AND `folder` = 1
                           AND `new` = "Y"
                         ORDER BY `id` DESC
                         LIMIT 1';
                $mid = DB::result($sql);
                $js .= "$('#xajax_new_mail_header_href_oryx')
                           .attr('href', '" . $g['path']['url_main'] . "mail.php?display=text&mid=" . $mid . "');
                        $('#xajax_new_mail_menu')
                           .attr('href', '" . $g['path']['url_main'] . "mail.php?display=text&mid=" . $mid . "');";
                $objResponse->addAssign("xajax_new_mail_header_href_oryx", "innerHTML", '<span id="xajax_new_mail_header_oryx">' . $g_info['new_mails'] . '</span>');
            } elseif ($g_info['new_mails'] == 0) {
                $js .= "$('#xajax_new_mail_header_href_oryx').attr('href', '" . $g['path']['url_main'] . "mail.php');
                        $('#xajax_new_mail_menu').attr('href', '" . $g['path']['url_main'] . "mail.php');";
                $objResponse->addAssign("xajax_new_mail_header_href_oryx", "innerHTML", '');
            } else {
                $js .= "$('#xajax_new_mail_header_href_oryx').attr('href', '" . $g['path']['url_main'] . "mail.php');
                        $('#xajax_new_mail_menu').attr('href', '" . $g['path']['url_main'] . "mail.php');";
                $objResponse->addAssign("xajax_new_mail_header_href_oryx", "innerHTML", '<span id="xajax_new_mail_header_oryx">' . $g_info['new_mails'] . '</span>');
            }
        }
        $countPending = DB::count('friends_requests', '`friend_id` = ' . to_sql($g_user['user_id'], 'Number') . ' AND `accepted` = 0', '`created_at` DESC');
        if ($countPending > 0) {
            $js .= "$('#xajax_pending_header_href_oryx').attr('href', '" . $g['path']['url_main'] . "my_friends.php?show=requests');";
            $objResponse->addAssign("xajax_pending_header_href_oryx", "innerHTML", '<span id="xajax_pending_header_oryx">' . $countPending . '</span>');
        } else {
            $js .= "$('#xajax_pending_header_href_oryx').attr('href', '" . $g['path']['url_main'] . "my_friends.php');";
            $objResponse->addAssign("xajax_pending_header_href_oryx", "innerHTML", '');
        }
    }
 }

 function updateScrollbar()
 {
    // error tinyscrollbar() not exists
    return;

    global $g_user;
    $js = '';

    DB::query('SELECT * FROM im_open WHERE from_user=' . to_sql($g_user['user_id'], 'Number') . ' AND group_id = 0 AND mid > 0 ' . WIDGET_DEMO_WHERE);
    $js .= "if(jQuery().tinyscrollbar) {";
    while ($row = DB::fetch_row()) {
        $js .= "$('#xajax_im_open_" . $row['to_user'] . "').tinyscrollbar({thumbSizeMin:20});
                $('#xajax_im_open_" . $row['to_user'] . "').tinyscrollbar_update('bottom');";
    }
    $js .= '};';

    return $js;
 }

 function playSound($file = 'pop_sound_chat.mp3')
 {
    $js = "soundManager.setup({url: '_server/js/sound/',
                               onready: function() {
                                 var mySound = soundManager.createSound({
                                   id: 'aSound',
                                   url: '_server/im_new/sounds/" . $file . "'
                                 });
                                 mySound.play();
                               }
                              });";
    return $js;
 }

 function getTemplate($dirTmplmain)
 {
	$imTemplate = './im_new/im.html';
    $imCustom  = $dirTmplmain . 'im.html';
    if (file_exists($imCustom))
        $imTemplate = $imCustom;

    return $imTemplate;
 }

function update_site_title($id)
{
    global $objResponse;
    global $g_info;
    global $g_user;
    global $js;

    $lastImMsg = get_session('window_last_im_msg');

    $count = $g_info['new_mails'];
    $countPending =  DB::count('friends_requests', '`friend_id` = ' . to_sql($g_user['user_id'], 'Number') . ' AND `accepted` = 0', '`created_at` DESC');
    $countImMsg = DB::count('im_msg', '`to_user` = ' . to_sql($g_user['user_id'], 'Number') . ' AND group_id = 0 AND id > ' . to_sql($lastImMsg, 'Number') . ' AND `system` = 0');
    $count = $count + $countPending + $countImMsg;

    $countEvent = $count - get_session('window_count_event');
    if ($countEvent > 0) {
        $titleCounter = lSetVars('title_site_counter', array('count' => $countEvent));
        //localStorage.removeItem('title_site_counter');
        $js .= "localStorage.setItem('title_site_counter', '" . $titleCounter . "');
                $('title').text('" . $titleCounter . " '+siteTitle);";
    }
 }

function unset_window_active()
{
    global $g_info;
    global $g_user;
    if ($g_user['user_id'] > 0) {
        $lastImMsg =  get_session('im_id', 0);
        set_session('window_last_im_msg', $lastImMsg);
        set_session('window_count_event', getSiteEvent($lastImMsg));
    }
    $objResponse = new xajaxResponse();
    return $objResponse;

}

function getSiteEvent($lastImMsg)
{
    global $g_info;
    global $g_user;

    $count = $g_info['new_mails'];
    $countPending =  DB::count('friends_requests', '`friend_id` = ' . to_sql($g_user['user_id'], 'Number') . ' AND `accepted` = 0', '`created_at` DESC');
    $countImMsg = DB::count('im_msg', '`to_user` = ' . to_sql($g_user['user_id'], 'Number') . ' AND group_id = 0 AND id > ' . to_sql($lastImMsg, 'Number'));
    $count = $count + $countPending + $countImMsg;

    return $count;
}

function read_msg($returnObjResponse = true)
{
    global $g_user;
    if ($g_user['user_id'] > 0) {
        DB::update('im_msg', array('is_new' => 0), 'group_id = 0 AND `to_user` = ' . to_sql($g_user['user_id'], 'Number'));
    }
    if ($returnObjResponse) {
        $objResponse = new xajaxResponse();
        return $objResponse;
    }
}

function set_writing($status)
{
    global $g_user;
    if (!empty($status)) {
        $where = 'group_id = 0 AND `from_user` = ' . to_sql($g_user['user_id'], 'Number');
        $isWriting = DB::select('im_open', $where, '', '', array('to_user', 'last_writing'));
        $statusAll = array();
        foreach ($isWriting as $item) {
            $statusAll[$item['to_user']] = $item['last_writing'];
        }
        foreach ($status as $user => $is) {
            if (isset($statusAll[$user]) && $statusAll[$user] != $status[$user]) {
                set_last_writing($user, $is);
            }
        }
    }
}

function set_last_writing($userId, $status)
{
    global $g_user;
    $where = '`from_user` = ' . to_sql($g_user['user_id'], 'Number') .
             ' AND `to_user` = ' . to_sql($userId, 'Number') .
             ' AND group_id = 0';
    DB::update('im_open', array('last_writing' => $status), $where);
}

function get_writing_user($timeoutSecServer)
{
    $fromUserWriting = DB::select('im_open', 'group_id = 0 AND `to_user` = ' . to_sql(guid(), 'Number'), '', '', array('from_user', 'last_writing'));
    $writing = array();
    $currentTime = time();
    foreach ($fromUserWriting as $user) {
        $writing[$user['from_user']] = (($currentTime - $user['last_writing']) <= $timeoutSecServer) ? 1 : 0;
    }

    return $writing;
}




class WidgetParser {

    static $parser = null;

    static function run($widget, $open = 0)
    {
        $data = 'ERROR - Parser not exists';

        if(self::$parser === null) {
            self::$parser = new CWidget('', './widgets/widget.html', 0, 0);
            self::$parser->stateSave();
        }

        if(self::$parser !== null) {
            self::$parser->setWidget($widget);
            self::$parser->setOpen($open);

            $tmp = null;
            self::$parser->stateSave();
            $data = self::$parser->parse($tmp, true);
            self::$parser->stateRestore();
        }

        return $data;
    }

    static function clean()
    {
        self::$parser = null;
    }
}

$xajax->processRequests();
include($g['to_root'] . "_include/core/main_close.php");
