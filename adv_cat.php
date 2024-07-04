<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

#$area = "login";
include("./_include/core/main_start.php");

class CHon extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		if(!empty($_GET['cat_id'])){

			DB::query("select * from adv_cats where id=".to_sql(get_param('cat_id'),"Number"));
			$cat = DB::fetch_row();
			///block search
			$html->setvar("cat_id", get_param('cat_id'));

			if(!empty($_GET['razd_id']))
				$html->setvar("razd_id", get_param('razd_id'));
			else
				$html->setvar("razd_id", '');


			switch($cat['eng']){
				case 'jobs':
					$html->parse("jobs",true);
					break;
				case 'housting':
					$html->parse("housting",true);
					break;
				case 'myspace':
					$html->parse("age",true);
					break;
				case 'services':
					$html->parse("price",true);
					break;
				case 'casting':
					$html->parse("age",true);
					break;
				case 'personals':
					$html->parse("age",true);
					break;
				case 'sale':
					$html->parse("price",true);
					break;
				case 'cars':
					$html->parse("price",true);
					break;

			}
		}else Common::toHomePage();
		$html->setvar("user_id", intval(get_param("user_id")));
		parent::parseBlock($html);
	}
}





class CAdv extends CHtmlList
{
	var $var_eng;
	function init()
	{
		parent::init();

		if(!empty($_GET['cat_id'])){
			$this->m_on_page = 30;

			DB::query("select * from adv_cats where id=".to_sql(get_param('cat_id'),"Number"));
			$cat = DB::fetch_row();
			if (!is_array($cat)) {
				$cat['eng'] = 'cars';
			}
			$find_str = '';
			$sql = '';
			if(!empty($_GET['find_str'])){
				$find_str = '&find_str='.get_param('find_str');

				$sql = "  and (subject like '%".to_sql(get_param('find_str'), 'Plain')."%' or body like '%".to_sql(get_param('find_str'), 'Plain')."%' ) ";
				switch($cat['eng']){
					case 'jobs':
						if(isset($_GET['telecommute'])){
							$sql .=  ' and telecommute = 1  ';
							$find_str .= '&telecommute = 1';
						}
						if(isset($_GET['contract'])){
							$sql .=  ' and contract = 1   ';
							$find_str .= '&contract = 1';
						}

						if(isset($_GET['internship'])){
							$sql .=  ' and internship = 1  ';
							$find_str .= '&internship = 1';
						}

						if(isset($_GET['part_time'])){
							$sql .=  ' and part_time = 1  ';
							$find_str .= '&part_time = 1';
						}

						if(isset($_GET['non_profit'])){
							$sql .=  ' and non_profit = 1  ';
							$find_str .= '&non_profit = 1';
						}

						break;
					case 'housting':
						if(isset($_GET['rent_from'])){
							if($_GET['rent_from'] != 'min') $sql .=  ' and rent >= '.to_sql(get_param('rent_from'));
							$find_str .= '&rent_from = '.$_GET['rent_from'];
						}
						if(isset($_GET['rent_to'])){

							if($_GET['rent_to'] != 'max') $sql .=  ' and rent <= '.to_sql(get_param('rent_to'));
							$find_str .= '&rent_to = '.$_GET['rent_to'];
						}
						if(isset($_GET['br'])){

							$sql .=  ' and br = '.$_GET['br'];
							$find_str .= '&br = '.$_GET['br'];
						}
						break;
					case 'myspace':
						if(isset($_GET['age_from'])){
							if($_GET['age_from'] != 'min') $sql .=  ' and age >= '.to_sql(get_param('age_from'));
							$find_str .= '&age_from = '.$_GET['age_from'];
						}
						if(isset($_GET['age_to'])){
							if($_GET['age_to'] != 'max') $sql .=  ' and age <= '.to_sql(get_param('age_to'));
							$find_str .= '&age_to = '.$_GET['age_to'];
						}

						break;
					case 'services':
						if(isset($_GET['price_from'])){
							if($_GET['price_from'] != 'min') $sql .=  ' and price >= '.to_sql(get_param('price_from'));
							$find_str .= '&price_from = '.$_GET['price_from'];
						}

						if(isset($_GET['price_to'])){
							if($_GET['price_to'] != 'max') $sql .=  ' and price <= '.to_sql(get_param('price_to'));
							$find_str .= '&price_to = '.$_GET['price_to'];
						}



						break;
					case 'casting':
						if(isset($_GET['age_from'])){
							if($_GET['age_from'] != 'min') $sql .=  ' and age >= '.to_sql(get_param('age_from'));
							$find_str .= '&age_from = '.$_GET['age_from'];
						}
						if(isset($_GET['age_to'])){
							if($_GET['age_to'] != 'max') $sql .=  ' and age <= '.to_sql(get_param('age_to'));
							$find_str .= '&age_to = '.$_GET['age_to'];
						}

											break;
					case 'personals':
						if(isset($_GET['age_from'])){
							if($_GET['age_from'] != 'min') $sql .=  ' and age >= '.to_sql(get_param('age_from'));
							$find_str .= '&age_from = '.$_GET['age_from'];
						}
						if(isset($_GET['age_to'])){
							if($_GET['age_to'] != 'max') $sql .=  ' and age <= '.to_sql(get_param('age_to'));
							$find_str .= '&age_to = '.$_GET['age_to'];
						}
						break;
					case 'sale':
						if(isset($_GET['price_from'])){
							if($_GET['price_from'] != 'min') $sql .=  ' and price >= '.to_sql(get_param('price_from'));
							$find_str .= '&price_from = '.$_GET['price_from'];
						}

						if(isset($_GET['price_to'])){
							if($_GET['price_to'] != 'max') $sql .=  ' and price <= '.to_sql(get_param('price_to'));
							$find_str .= '&price_to = '.$_GET['price_to'];
						}


						break;
					case 'cars':
						if(isset($_GET['price_from'])){
							if($_GET['price_from'] != 'min') $sql .=  ' and price >= '.to_sql(get_param('price_from'));
							$find_str .= '&price_from = '.$_GET['price_from'];
						}

						if(isset($_GET['price_to'])){
							if($_GET['price_to'] != 'max') $sql .=  ' and price <= '.to_sql(get_param('price_to'));
							$find_str .= '&price_to = '.$_GET['price_to'];
						}
						break;
				}
			}else{
				$sql='';
			}		

	        $is_approved = "";
	        if(!Common::isOptionActive('adv_show_before_approval')) {
	            $is_approved = " AND (C.approved = 1 OR (C.approved = 0 AND C.user_id = " . to_sql(guid(), 'Number') . "))";
	        }
	        
			if(!empty($_GET['razd_id'])){
				$this->m_sql_count = "select count(C.id) as col from adv_".$cat['eng']." as C
					join user as U on U.user_id=C.user_id ";

				$this->m_sql = "select C.id,subject,C.user_id,created,U.name as login from adv_".$cat['eng']." as C
					join user as U on U.user_id=C.user_id";
				$this->m_sql_where = "cat_id=".to_sql(get_param('cat_id'),"Number")."
					and razd_id=".to_sql(get_param('razd_id'),"Number")." ".$sql . $is_approved;
				$this->m_sql_order = "  created desc ";

			}else{
				$this->m_sql_count = "select count(C.id) as col from adv_".$cat['eng']." as C
					join user as U on U.user_id=C.user_id ";
				$this->m_sql = "select C.id,subject,C.user_id, created,U.name as login from adv_".$cat['eng']." as C
					join user as U on U.user_id=C.user_id ";
				$this->m_sql_where = "cat_id=".to_sql(get_param('cat_id'),"Number")."  ".$sql . $is_approved;
				$this->m_sql_order = " created desc ";
			}
			$this->var_eng = $cat['eng'];
			$this->m_field['id'] = array("id", null);
			$this->m_field['subject'] = array("subject", null);
			$this->m_field['login'] = array("login", null);
			$this->m_field['created'] = array("created", null);
            
	//		$this->m_field['user_id'] = array("var_user_id", null);

		} else Common::toHomePage(); ///if(!empty($_GET['cat_id'])){
	}

	function onItem(&$html, $row, $i, $last)
	{
		global $g;
		$this->m_field['subject'][1] = to_html($row['subject']);
		$this->m_field['login'][1] = to_html($row['login']);
		$html->setvar("subject", $row['subject']);
		$html->setvar("login", $row['login']);
		$html->setvar("date_add",Common::dateFormat($row['created'], 'adv_date', false));

		$html->setvar("var_eng", $this->var_eng);

		if($i==1) $html->setvar("first",1);
		else $html->setvar("first","");

		//$html->parse("adv_list_item",true);
		//$html->parse("adv_list",true);
	}



	function parseBlock(&$html)
	{
		parent::parseBlock($html);
	}
}



$page = new CHon("", $g['tmpl']['dir_tmpl_main'] . "adv_cat.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);

$search = new CSearch("search", $g['tmpl']['dir_tmpl_main'] . "_search.html");
$page->add($search);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$adv_list = new CAdv("adv_list", null);
$page->add($adv_list);


include("./_include/core/main_close.php");

?>
