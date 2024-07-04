<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

#$area = "login";
include("./_include/core/main_start.php");
include("./_include/current/adv.class.php");

if (get_param('cat', '') == '' || get_param('id', '') == '') {
    redirect('adv.php');
}
CStatsTools::count('replies_to_ads');
class CHon extends CHtmlBlock
{
    function action ()
    {
        $cmd = get_param('cmd');
        if ($cmd  == 'delete') {
            $cat = get_param('cat');
            $id = get_param('id');
            if (CAdvTools::deleteAdv($cat, $id)){
                redirect('adv.php');
            }
        }

    }

	function parseBlock(&$html)
	{
	 	global $g;
		global $l;
		global $g_user;

		DB::query("select * from adv_cats where eng=".to_sql(get_param('cat'))."");
		if (!($adv_cat = DB::fetch_row())) redirect("adv.php");

		$html->setvar("var_adv_cat_id", $adv_cat['id']);
		$html->setvar("var_adv_cat_name", l($adv_cat['name']));

		$is_approved = "";
        if(!Common::isOptionActive('adv_show_before_approval')) {
            $is_approved = " AND (approved = 1 OR (approved = 0 AND user_id = " . to_sql(guid(), 'Number') . "))";
        }
        
		DB::query("select * from adv_".to_sql(get_param('cat'), 'Plain')." where id=".to_sql(get_param('id')) . $is_approved);
		if (!($row = DB::fetch_row())) redirect("adv.php");
		$html->setvar("var_subject", $row['subject']);

		/*if ($row['user_id'] == $g_user['user_id'] and get_param('cmd') == 'delete')
		{
			DB::execute('DELETE FROM adv_' . to_sql(get_param('cat'), 'Plain') . ' WHERE id=' . to_sql(get_param('id')) . '');
			redirect('adv.php');
		}*/

		DB::query("select * from adv_razd where id=".to_sql($row['razd_id'])."");
		if (!($adv_razd = DB::fetch_row())) redirect("adv.php");

		$html->setvar("var_adv_razd_id", $adv_razd['id']);
		$html->setvar("var_adv_razd_name", l($adv_razd['name'])); //$html->setvar("var_adv_razd_name",  isset($l['all'][to_php_alfabet($adv_razd['name'])]) ? $l['all'][to_php_alfabet($adv_razd['name'])] : mb_convert_case($adv_razd['name'], MB_CASE_TITLE));

		DB::query("select * from adv_images where adv_cat_id = " . $adv_cat['id'] . " and adv_id = " . $row['id'] . " LIMIT 4");
		$images = array();
		$image_n = 1;
		while($image = DB::fetch_row())
		{
			$html->setvar("image_" . $image_n . "_thumbnail", $g['path']['url_files'] . "adv_images/" . $image['id'] . "_th.jpg");
			$html->setvar("image_" . $image_n . "_file", $g['path']['url_files'] . "adv_images/" . $image['id'] . "_b.jpg");
			$html->parse("image_" . $image_n);

			++$image_n;
		}
		for(;$image_n <= 4; ++$image_n)
		{
			$html->parse("image_" . $image_n . "_empty");
		}

		$user_photo = User::getPhotoDefault($row['user_id'],"r");
		$html->setvar('var_user_photo', $user_photo);

		$result_user=DB::query("SELECT *, YEAR(FROM_DAYS(TO_DAYS('" . date('Y-m-d H:i:s') . "')-TO_DAYS(birth))) AS age FROM user WHERE user_id='".$row['user_id']."' LIMIT 0, 1", 2);
		$row_user=DB::fetch_row(2);

		$html->setvar("var_user_name", $row_user['name']);

		$html->setvar("var_date_posted",Common::dateFormat($row['created'], 'adv_date', false));

		$str = '';

		switch($adv_cat['eng']){
			case 'jobs':
				$job_types = array();

				if($row['telecommute'] == 1)
					$job_types[] = l('telecommute');
				if($row['contract'] == 1)
					$job_types[] = l('contract');
				if($row['internship'] == 1)
					$job_types[] = l('internship');
				if($row['part_time'] == 1)
					$job_types[] = l('part-time');
				if($row['non_profit'] == 1)
					$job_types[] = l('non-profit');

				$str .= implode(' / ', $job_types);
				break;
			case 'housting':
                                $row['rent'] = round($row['rent']) == $row['rent'] ? round($row['rent']) : sprintf('%01.2f', $row['rent']);
				$str .= '<b>' . l('Rent') . ':</b> ' .to_html($row['rent']).'<br>';
				$str .= '<b>' . l('Br') . ':</b> ' . ' '.to_html($row['br']);
				break;
			case 'myspace':
			case 'casting':
			case 'personals':
				$str .= '<b>' . l('Age') . ':</b> ' .to_html($row['age']);
				break;
			case 'services':
			case 'sale':
			case 'cars':
                                $row['price'] = round($row['price']) == $row['price'] ? round($row['price']) : sprintf('%01.2f', $row['price']);
				$str .= '<b>' . l('Price') . ':</b> ' .to_html($row['price']);
				break;
		}
		$html->setvar("var_details", $str);
		$html->setvar("var_body", to_html(Common::parseLinksSmile($row['body']), true, true));
		$html->setvar("var_user_id", $row['user_id']);
		$html->setvar("var_cat", get_param('cat'));
		$html->setvar("var_id", $row['id']);
		$html->setvar("user_id", get_param("user_id"));


		if ($row['user_id'] == $g_user['user_id'])
		{
			$html->parse('my_adv');
		}
		elseif(Common::isOptionActive('mail'))
		{
			$html->parse('not_my_adv');
		}
		parent::parseBlock($html);
	}
}

DB::query("select * from adv_".to_sql(get_param('cat'), 'Plain')." where id=".to_sql(get_param('id')));
$row = DB::fetch_row();
if($row)
{
	global $g;
	$g['main']['title'] = str_replace(array("\n", "\r"), array(' ', ' '), strip_tags($row['subject']));
	$g['main']['description'] = str_replace(array("\n", "\r"), array(' ', ' '), strip_tags($row['body']));
}

$page = new CHon("", $g['tmpl']['dir_tmpl_main'] . "adv_good.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

$search = new CSearch("search", $g['tmpl']['dir_tmpl_main'] . "_search.html");
$page->add($search);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");
