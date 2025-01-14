<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");

class CProfile extends CHtmlBlock
{
    static $numberUsersNew = 0;

	function action()
	{
		global $g;
		global $g_user;
		$cmd = get_param('cmd', '');

        if ($cmd == 'hide') {
            $sql = "UPDATE `user`
                       SET `hide_time` = " . to_sql(Common::getOption('hide_time'),'Number')
                 . " WHERE `user_id` = " . to_sql(get_session('user_id'), 'Number');
			DB::execute($sql);
		}elseif ($cmd == 'active') {
            $sql = "UPDATE `user`
                       SET `hide_time` = 0
                     WHERE `user_id` = " . to_sql(get_session('user_id'), 'Number');
			DB::execute($sql);
		}
	}

	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $g_user;
		global $g_info;
		global $gc;

        $html->setvar("user_id", $g_user['user_id']);

        if (Common::isOptionActive('recorder')) {
			$html->setvar('unique', str_replace('.', '_', domain()));
			$html->setvar("myname", $g_user['name']);
			$html->parse("myrecorder", true);
			$html->parse("myrecorder_swf", true);
		}

        if (Common::isOptionActive('biorythm')) {
			$html->parse("biorythm", false);
		}

		foreach ($g_user as $k => $v) $html->setvar($k, $v);
		if ($g['options']['your_orientation'] == 'Y') {
			$html->setvar("p_orientation_for_search", $g_user['p_orientation']);
            $g_info['users_new'] = DB::result_cache("users_new_" . to_php_alfabet($g_user['p_orientation']), 30, "SELECT COUNT(user_id) FROM user WHERE hide_time = 0 " . $g['sql']['your_orientation'] .  " AND register>'" . date('Y-m-d H:i:s', (time() - 60 * 60 * 24 * $g['options']['new_time'])) . "'"); # AND register>(" . (time() - 60 * 60 * 24 * 10) . "
            $g_info['users_new_near'] = DB::result_cache("users_new_near_" . to_php_alfabet($g_user['p_orientation'] . "_" . $g_user['city_id']), 30, "SELECT COUNT(user_id) FROM user WHERE hide_time = 0 " . $g['sql']['your_orientation'] .  " AND register>'" . date('Y-m-d H:i:s', (time() - 60 * 60 * 24 * $g['options']['new_time'])) . "' AND city_id=" . $g_user['city_id'] . "");
		} else {
			$g_info['users_new'] = DB::result_cache("users_new", 30, "SELECT COUNT(user_id) FROM user WHERE hide_time = 0 AND register>'" . date('Y-m-d H:i:s', (time() - 60 * 60 * 24 * $g['options']['new_time'])) . "'"); # AND register>(" . (time() - 60 * 60 * 24 * 10) . "
			$g_info['users_new_near'] = DB::result_cache("users_new_near" . to_php_alfabet($g_user['city_id']), 30, "SELECT COUNT(user_id) FROM user WHERE hide_time = 0 AND city_id=" . $g_user['city_id'] . " AND register>'".date('Y-m-d H:i:s', (time() - 60 * 60 * 24 * $g['options']['new_time']))."'");
		}
		foreach ($g_info as $k => $v) $html->setvar($k, $v);

		$photo = User::getPhotoDefault($g_user['user_id'],"m");

		$html->setvar("photo", $photo);

		$city_title = (($g_user['city'] == "" or $g_user['city'] == "0") ? l('blank') : (isset($l['all'][to_php_alfabet($g_user['city'])]) ? $l['all'][to_php_alfabet($g_user['city'])] : $g_user['city']));
		$state_title = (($g_user['state'] == "" or $g_user['state'] == "0") ? l('blank') : (isset($l['all'][to_php_alfabet($g_user['state'])]) ? $l['all'][to_php_alfabet($g_user['state'])] : $g_user['state']));
		$country_title = (($g_user['country'] == "" or $g_user['country'] == "0") ? l('blank') : (isset($l['all'][to_php_alfabet($g_user['country'])]) ? $l['all'][to_php_alfabet($g_user['country'])] : $g_user['country']));

		$html->setvar("country_title", $country_title);
		$html->setvar("state_title", $state_title);
		$html->setvar("city_title", $city_title);
		$html->setvar("country", $g_user['country_id']);
		$html->setvar("state", $g_user['state_id']);
		$html->setvar("city", $g_user['city_id']);

        if (Common::isOptionActive('hide_profile_enabled')) {
            $sql = "SELECT `hide_time`
                      FROM `user`
                     WHERE `user_id` = " . to_sql(get_session('user_id'), 'Number');
            $hide = DB::result($sql);
            if ($hide > 0) {
                $html->parse('active', true);
            } else {
                $html->parse('hide', true);
            }
        } else {
            $html->parse('li_last', true);
        }

        if(!Common::isOptionActive('free_site')) {
            if (User::isPaidFree() && !$g_user['free_access']) {
                $html->parse("my_nogold", true);
            } else {
                $html->parse("my_gold", true);
            }
        }

        if(Common::isOptionActive('viewed_me_tab_enabled')) {
                $html->parse('viewed_me', true);
        }

        if ($g_user['gender'] == 'M') $html->parse("my_male", true);
		else $html->parse("my_female", true);

		if ($g_user['city_id'] > 0 and $g_user['city'] != '') {
            if (UserFields::isActive('orientation') && Common::isOptionActive('your_orientation')) {
                CSearch::parseChecks($html, "p_orientation", "SELECT id, title FROM const_orientation ORDER BY id ASC", $g_user['p_orientation'], 2, 0, true);
            }
			$html->parse("new_near", true);
		}

// PROFILE STATUS

DB::query("SELECT status FROM profile_status WHERE user_id=".$g_user['user_id']);
if(DB::num_rows()>0) {
    $status = DB::fetch_row();
    $html->setvar("profile_status",$status['status']);
}

if (Common::isOptionActive('profile_status')) {
    $html->parse("profile_status", true);
}
// PROFILE STATUS



// PEOPLES

global $gc;
global $gm;

$on_page = 6;
if (isset($gm) and $gm) {
	$on_page = 6;
} elseif (isset($gc) and $gc) {
	$on_page = 6;
}

global $users_stop_list, $status_style, $member_index;
$users_stop_list = $g_user['user_id'];
$status_style = 0;

$is_photo = true;
$is_city = true;
$is_state = true;
$is_country = true;

// ORDER: PHOTO - city - state - country | JUST PHOTO | any other

	// new
	if($g['options']['show_home_page_online']=="N") {
		$where = " u.user_id NOT IN({users_stop_list}) AND u.hide_time=0 AND u.register> '" . date('Y-m-d H:i:s', (time() - 60 * 60 * 24 * $g['options']['new_time'])) . "' " . $g['sql']['your_orientation'] . " ";
		$html->assign("members_link1","users_new.php");
		$html->parse("members_new");
	}
	// online
	else {

            if($g_user['default_online_view']=='B' || $g_user['default_online_view']=='')
                $where = " u.user_id NOT IN({users_stop_list})  AND u.hide_time=0 AND (u.last_visit>'" . (date("Y-m-d H:i:s", time() - $g['options']['online_time'] * 60))."' " . " OR u.use_as_online=1)" . $g['sql']['your_orientation'] . " ";
		else
		
		$where = " u.user_id NOT IN({users_stop_list}) 
				AND u.hide_time=0 AND (u.last_visit>'" . (date("Y-m-d H:i:s", time() - $g['options']['online_time'] * 60))."' " . " OR u.use_as_online=1)" . $g['sql']['your_orientation'] . " ";
	
		$html->assign("members_link","users_online.php");
		$html->parse("members_online");
	}

for($i=0;$i<$on_page;$i++)
{

$member_index = $i;

// SELECT city
$where_query = str_replace("{users_stop_list}",$users_stop_list,$where);

$row = array();

// FIRST OF ALL with photo
if($is_photo)
{
	if($is_city == false || !parse_user_home($html, "SELECT u.*, vi.title as income_title, (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
) AS age FROM user AS u LEFT JOIN userinfo as i ON u.user_id=i.user_id LEFT JOIN var_income vi ON i.income=vi.id WHERE $where_query AND u.is_photo='Y' AND u.city_id = ".$g_user['city_id']))
	{
		$is_city = false;
		if($is_state == false || !parse_user_home($html, "SELECT u.*, vi.title as income_title, (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
) AS age FROM user AS u LEFT JOIN userinfo as i ON u.user_id=i.user_id LEFT JOIN var_income vi ON i.income=vi.id WHERE $where_query AND  u.is_photo='Y' AND u.state_id = ".$g_user['state_id']))
		{
			$is_state = false;
			if($is_country == false || !parse_user_home($html, "SELECT u.*, vi.title as income_title, (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
) AS age FROM user AS u LEFT JOIN userinfo as i ON u.user_id=i.user_id LEFT JOIN var_income vi ON i.income=vi.id WHERE $where_query AND  u.is_photo='Y' AND u.country_id = ".$g_user['country_id']))
			{
				$is_country = false;
				if(!parse_user_home($html, "SELECT u.*, vi.title as income_title, (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
) AS age FROM user AS u LEFT JOIN userinfo as i ON u.user_id=i.user_id LEFT JOIN var_income vi ON i.income=vi.id WHERE $where_query AND u.is_photo='Y'"))
				{
					$is_photo = false;
					$is_city = true;
					$is_state = true;
					$is_country = true;
				}
			}
		}
	}
// END PHOTO
}

// WITHOUT PHOTO
if(!$is_photo)
{
	if($is_city == false || !parse_user_home($html, "SELECT u.*, vi.title as income_title,  (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
) AS age FROM user AS u LEFT JOIN userinfo as i ON u.user_id=i.user_id LEFT JOIN var_income vi ON i.income=vi.id  WHERE $where_query AND u.city_id = ".$g_user['city_id']))
	{
		$is_city = false;
		if($is_state == false || !parse_user_home($html, "SELECT u.*, vi.title as income_title, (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
) AS age FROM user AS u LEFT JOIN userinfo as i ON u.user_id=i.user_id LEFT JOIN var_income vi ON i.income=vi.id WHERE $where_query AND u.state_id = ".$g_user['state_id']))
		{
			$is_state = false;
			if($is_country == false || !parse_user_home($html, "SELECT u.*, vi.title as income_title, (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
) AS age FROM user AS u LEFT JOIN userinfo as i ON u.user_id=i.user_id LEFT JOIN var_income vi ON i.income=vi.id WHERE $where_query AND u.country_id = ".$g_user['country_id']))
			{
				$is_country = false;
				if(!parse_user_home($html, "SELECT u.*, vi.title as income_title, (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
) AS age FROM user AS u LEFT JOIN userinfo as i ON u.user_id=i.user_id LEFT JOIN var_income vi ON i.income=vi.id WHERE $where_query ")) $is_photo = false;
			}
		}
	}
// END PHOTO
}

}
        if(Common::isOptionActive('mail')) {
            $html->parse('mail_on');
        }
        if(Common::isOptionActive('wink')) {
            $html->parse('wink_on');
        }

        CBanner::getBlock($html, 'footer_additional');

        if (self::$numberUsersNew){
            $html->parse("users_new");
        }

// PEOPLES
//popcorn modified 20231212
		$where = "u.user_id!=" . $g_user['user_id'] . " AND u.hide_time=0 " . $g['sql']['your_orientation'];
		$where .= " AND register >= " . to_sql(date('Y-m-d H:00:00', time() - intval($g['options']['new_time']) * 3600 * 24));
		$order = "u.is_photo DESC, u.user_id DESC";
		if ($where) {
			$where = ' WHERE ' . $where;
		}
		if ($order) {
			$order = ' ORDER BY ' . $order;
		}
		$from_add = "LEFT JOIN userinfo as i ON u.user_id=i.user_id LEFT JOIN var_income vi ON i.income=vi.id ";

		$html->assign("members_link","users_new.php");
		$html->parse("members_new");
		
		$sqlCount = 'SELECT COUNT(u.user_id) FROM user AS u ' . $from_add;
		$sql = $sqlCount . $where;
		$sql_content = 'SELECT u.*, vi.title as income_title FROM user AS u ' . $from_add . $where;
		$total = DB::result($sql);
		if($total>4) $total = 4;
		for($i=0;$i<$total;$i++)
		{
			//$where_query = str_replace("{users_stop_list}",$users_stop_list,$where);
			parse_user_new($html, $sql_content, $i);
		}
		if(Common::isOptionActive('show_home_page_online')) {
			$html->parse('people_online');
		}
			
		$where = "u.user_id!=" . to_sql($g_user['user_id'], "Number") . "";
		$order = "v.id DESC";	
		if ($where) {
			$where = ' WHERE ' . $where;
		}
		if ($order) {
			$order = ' ORDER BY ' . $order;
		}
		$from_add = " JOIN users_view AS v ON (u.user_id=v.user_from AND v.user_to=" . to_sql($g_user['user_id'], "Number") . ") LEFT JOIN userinfo as i ON u.user_id=i.user_id LEFT JOIN var_income vi ON i.income=vi.id ";

		$html->assign("members_link","users_viewed_me.php");
		$html->parse("members_viewed_me");
		
		$sqlCount = 'SELECT COUNT(u.user_id) FROM user AS u ' . $from_add;
		$sql = $sqlCount . $where;
		$sql_content = 'SELECT u.*, vi.title as income_title FROM user AS u ' . $from_add . $where;
		$total = DB::result($sql);
		// if($total>4) $total=4;
		for($i=0;$i<$total;$i++)
		{
			//$where_query = str_replace("{users_stop_list}",$users_stop_list,$where);
			parse_user_viewed_me($html, $sql_content, $i);
		}

		if($g_user['set_profile_visitor']==2){
			$html->parse("setting_profile_viewed_me",true);
		}else{
			$html->setblockvar("setting_profile_viewed_me", "");
		}
		
		g_user_full();
		$where = "";
		$typeFields = array('from', 'checks');

		foreach ($g['user_var'] as $k => $v)
		{
			if (in_array($v['type'], $typeFields) && $v['status'] == 'active')
			{
				$key = $k;
				if ($v['type'] == "from"){
					if (isset($g_user[$k]) && $g_user[$k] != 0){
						if(strpos($k,"p_")===0){
							$key = substr($k, 2);
						}
						if(strrpos($key,"_from")===(strlen($key)-strlen("_from"))){
							$key = substr($key, 0,(strlen($key)-strlen("_from")));
						}
						$where .= " AND (i." . $key . ">" . $g_user[$k];
						$fieldsTo = substr($k, 0, strlen($k) - 4) . 'to';
						if ($g_user[$fieldsTo] != 0)
							$where .= " AND i." . $key . "<" . $g_user[$fieldsTo] . ") ";
						else
							$where .= ') ';
						}
				}elseif ($v['type'] == "checks"){
					$user[$k] = get_checks_param($k);
					if (isset($g_user[$k]) && $g_user[$k] != 0){
							if(strpos($k,"p_")===0){
								$key = substr($k, 2);
							}
							//$where .= " AND " . $g_user[$k] . " & (1 << (" . $key . " - 1))";
							$where .= " AND " . to_sql($g_user[$k], 'Number') . " & (1 << (cast(i." . $key . " AS signed) - 1))";
					}
				}
			}
		}

		if (isset($g_user['p_relation']) && $g_user["p_relation"] != "0")
		{
			$where .= " AND " . $g_user["p_relation"] . " & (1 << (cast(u.relation AS signed) - 1))";
			//$where .= " AND " . $g_user["p_relation"] . " & (1 << (relation - 1))";
		}
		if ($g_user['p_age_from'] == $g['options']['users_age']) $g_user['p_age_from'] = 0;
		if ($g_user['p_age_to'] == $g['options']['users_age_max']) $g_user['p_age_to'] = 100;
		$user['p_age_from'] = (int) $g_user['p_age_from'];
		$user['p_age_to'] = (int) $g_user['p_age_to'];
		$userAgeToSrc = $user['p_age_to'];
		if ($user['p_age_from'] == $g['options']['users_age']) $user['p_age_from'] = 0;
		if ($user['p_age_to'] == $g['options']['users_age_max']) $user['p_age_to'] = 100;
		if ($user['p_age_from'] != 0)
		{
			$where .= " AND u.birth <= " . to_sql(Common::ageToDate($user['p_age_from']));
		}

		if ($userAgeToSrc && $userAgeToSrc != $g['options']['users_age_max'])
		{
			$where .= " AND u.birth >= " . to_sql(Common::ageToDate($userAgeToSrc, true));
		}


		$partnerOrientation = User::getPartnerOrientationWhereSql();
		if($partnerOrientation) {
			$partnerOrientation = ' AND ' . $partnerOrientation;
		}

		$where = "u.hide_time=0 AND u.user_id!=" . $g_user['user_id'] . $partnerOrientation . $where . " ";
		$order = "near DESC, last_visit DESC, user_id DESC";

		if(Users_List::isBigBase()) {
			$order = "last_visit DESC";
		}
		if ($where) {
			$where = ' WHERE ' . $where;
		}
		if ($order) {
			$order = ' ORDER BY ' . $order;
		}
		$from_add = "LEFT JOIN userinfo as i ON u.user_id=i.user_id LEFT JOIN var_income vi ON i.income=vi.id ";
		$html->assign("members_link","users_featured.php");
		$html->parse("members_matches");
		
		$sqlCount = 'SELECT COUNT(u.user_id) FROM user AS u ' . $from_add;
		$sql = $sqlCount . $where;
		$sql_content = 'SELECT u.*, vi.title as income_title FROM user AS u ' . $from_add . $where;
		$total = DB::result($sql);
		if($total>4) $total = 4;
		for($i=0;$i<$total;$i++)
		{
			//$where_query = str_replace("{users_stop_list}",$users_stop_list,$where);
			parse_user_matches($html, $sql_content, $i);
		}

		parseCloseHotdates($html);
		parseCloseEvents($html);
		//popcorn modified-20231212-end
        parent::parseBlock($html);
	}
}

function parse_user_home(&$html, $sql){
	return parse_user_common($html, $sql);
}

function parse_user_new(&$html, $sql, $i){
	$fliptxt = "new";
	return parse_user_common($html, $sql, $i, $fliptxt);
}

function parse_user_viewed_me(&$html, $sql, $i){

	$fliptxt = "viewed_me";
	return parse_user_common($html, $sql, $i, $fliptxt);
}

function parse_user_matches(&$html, $sql, $i){
	$fliptxt = "matches";
	return parse_user_common($html, $sql, $i, $fliptxt);
}

function parse_user_common(&$html, $sql, $i=0, $fliptxt="") {
    global $users_stop_list, $member_index, $status_style;
	global $g_user; //cuigao-diamond-20201113
    if(Users_List::isBigBase()) {
        $sql = 'SELECT * FROM (' . $sql . ' ORDER BY u.user_id DESC LIMIT 100) AS u_tmp';
    }

	if ($fliptxt == "") {
		$sql .= ' ORDER BY RAND() LIMIT 1';
	} else {
		$sql .= ' ORDER BY u.user_id DESC LIMIT '.$i.',1';
	}
	
	$row = DB::row($sql);

	if(!is_array($row)) return false;

	//cuigao-diamond-20201113-start
    $set_my_presence_couples = $row['set_my_presence_couples'];
	$set_my_presence_males = $row['set_my_presence_males'];
	$set_my_presence_females = $row['set_my_presence_females'];
	$set_my_presence_transgender = $row['set_my_presence_transgender'];
	$set_my_presence_nonbinary = $row['set_my_presence_nonbinary'];

	// $set_my_presence_everyone = $row['set_my_presence_everyone'];
	if($g_user['orientation']==5 && $set_my_presence_couples==1){		
		return false;
	}
	if($g_user['orientation']==1 && $set_my_presence_males==1){
		return false;
	}
	if($g_user['orientation']==2 && $set_my_presence_females==1){
		return false;
	}
	if($g_user['orientation']==6 && $set_my_presence_transgender==1){
		return false;
	}
	if($g_user['orientation']==7 && $set_my_presence_nonbinary==1){
		return false;
	}
	// if($set_my_presence_everyone==1){		
	// 	return false;
	// }
	//cuigao-diamond-20201113-end
    CProfile::$numberUsersNew++;

	$users_stop_list .= ",".$row['user_id'];
	

	$row['country_title'] = trim($row['country']);
	if(pl_strlen($row['country_title']) > 7 ) $row['country_title'] = pl_substr($row['country_title'], 0, 7) . "...";
	
	$user_id = $row['user_id'];
	$distance  = intval(Common::calculateDistance($user_id));
	$row['distance'] = $distance;
	$genderImgPath = Common::getGenderImage($user_id);
	$row['gender_image'] = $genderImgPath;
	
	$income_title = convertWordsByUnderLine($row['income_title']);
	$gender_text = "";
	if($income_title) {
		$gender_text = l($income_title . "_short");
	}
	$row['gender_text'] = $gender_text;
	
	foreach($row as $k=>$v) {
        if ($k == 'name') $v = User::nameOneLetterFull($v);
		if($k =='orientation') {
			$orientation_row = DB::row("SELECT * FROM const_orientation WHERE id = " . $v . ";");
			$v = " -". $orientation_row['title'];

		}
		// if($k == 'orientation') $v = "   -" . l($v);
        $html->assign("members_".$k, $v);
    }

	// PHOTO
	$html->assign("members_photo",User::getPhotoDefault($row['user_id'],"s"));

	// STATUS
	$status = DB::row("SELECT status FROM profile_status WHERE user_id=".$row['user_id']);
	if(is_array($status)) {
		$html->assign("members_status",$status['status']);
		$html->assign("members_status_style",$status_style++%6 + 1);
	}
    if (Common::isOptionActive('profile_status')) {
        $html->subcond(is_array($status), "user_status");
    }

	$profile_viewed_me = false;
	if($fliptxt == "viewed_me") {
		if($g_user['orientation']==5 && $row['set_profile_visitor_couples']==2){	
			$profile_viewed_me = true;
		} else if($g_user['orientation']==1 && $row['set_profile_visitor_males']==2){	
			$profile_viewed_me = true;
		} else if($g_user['orientation']==2 && $row['set_profile_visitor_females']==2){		
			$profile_viewed_me = true;
		} else if($g_user['orientation']==6 && $row['set_profile_visitor_transgender']==2){
			$profile_viewed_me = true;
		} else if($g_user['orientation']==7 && $row['set_profile_visitor_nonbinary']==2){		
			$profile_viewed_me = true;
		}
	} else {
		$profile_viewed_me = true;
	}
	if(!$profile_viewed_me) return false;

	$fliptxt_index = '';
	$tmp_suffix = '';

	if($fliptxt == "") {
		$fliptxt_index = $member_index;
		$tmp_suffix = '';
	} else {
		$fliptxt_index = $fliptxt.$i;
		$tmp_suffix = '_' . $fliptxt;

	}

	$html->assign("members_num", $fliptxt_index);

		CFlipCard::parseFlipCard($html, $row);

		$html->parse("users_new_item" . $tmp_suffix, true);


	return true;
}

function parseCloseHotdates(&$html){

	global $g_user;
	$city_id = $g_user['city_id'];


	$sql_location = closestCity($city_id);

	if($sql_location) {
		$forDays = 62;
		$sql_select = "SELECT ht.* , " . $sql_location . " FROM `hotdates_hotdate` as ht LEFT JOIN user AS u ON ht.user_id=u.user_id LEFT JOIN geo_city AS gc ON gc.city_id=u.city_id" ;
		$sql_where = " WHERE ht.hotdate_datetime <= " . to_sql(date('Y-m-d H:00:00', time() + intval($forDays * 3600 * 24))) . "AND ht.hotdate_datetime >= " . to_sql(date('Y-m-d H:00:00', time()));
		$sql_order = " ORDER by distance, ht.hotdate_datetime limit 8";

		$sql = $sql_select . $sql_where . $sql_order;

		$rows = DB::rows($sql);
		foreach ($rows as $key => $row) {

			$img = CFlipCard::hotdate_images($row['hotdate_id']);

            $html->setvar('hotdate_id', $row['hotdate_id']);
            $html->setvar('hotdate_title', strcut(to_html($row['hotdate_title']), 20));
            $html->setvar("image_thumbnail", $img["image_thumbnail"]);

            $html->parse('users_hotdates_item');
		}

	} else {
		return false;
	}
}


function parseCloseEvents(&$html){

	global $g_user;
	$city_id = $g_user['city_id'];


	$sql_location = closestCity($city_id);

	if($sql_location) {
		$forDays = 62;
		$sql_select = "SELECT ht.* , " . $sql_location . " FROM `events_event` as ht LEFT JOIN user AS u ON ht.user_id=u.user_id LEFT JOIN geo_city AS gc ON gc.city_id=u.city_id" ;
		$sql_where = " WHERE ht.event_datetime <= " . to_sql(date('Y-m-d H:00:00', time() + intval($forDays * 3600 * 24))) . "AND ht.event_datetime >= " . to_sql(date('Y-m-d H:00:00', time()));
		$sql_order = " ORDER by distance, ht.event_datetime limit 8";

		$sql = $sql_select . $sql_where . $sql_order;

		$rows = DB::rows($sql);
		foreach ($rows as $key => $row) {

			$img = CFlipCard::event_images($row['event_id']);

            $html->setvar('event_id', $row['event_id']);
            $html->setvar('event_title', strcut(to_html($row['event_title']), 20));
            $html->setvar("image_thumbnail", $img["image_thumbnail"]);

            $html->parse('users_events_item');
		}

	} else {
		return false;
	}
}


/*class CBanerHome extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g;
		global $g_user;
		if (User::isPaid(guid()))
            $baner = get_banner("home_paid");
		else
            $baner = get_banner("home");
		if ($baner !== false)
		{
			$html->setvar("banner_home", $baner);
			$html->parse("banner_home", true);
			parent::parseBlock($html);
		}
	}
}*/


g_user_full();


$page = new CProfile("", $g['tmpl']['dir_tmpl_main'] . "home.html");

$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$complite = new CComplite("complite", $g['tmpl']['dir_tmpl_main'] . "_complite.html");
$page->add($complite);

$baner = new CBanner("baner_home", null);
$page->add($baner);

$search = new CSearch("search", $g['tmpl']['dir_tmpl_main'] . "_search.html");
$page->add($search);
// echo 'ddd';  die();

demoImAdd();

include("./_include/core/main_close.php");
?>
