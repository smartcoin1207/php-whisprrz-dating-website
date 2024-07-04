<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");



class CForm extends CHtmlList
{

	var $message = "";
	var $login = "";
    var $countryId = 0;
    var $stateId = 0;

	function init()
	{
		parent::init();

        $this->initLocationData();

		$this->m_on_page = 1000;
		$this->m_sql_count = 'SELECT COUNT(*) FROM geo_city';
		$this->m_sql = 'SELECT * FROM geo_city';
        $this->m_sql_where = ' state_id = ' . to_sql($this->stateId);

		$this->m_sql_order = ' city_title ASC ';
		$this->m_field['city_id'] = array('city_id', null);
		$this->m_field['state_id'] = array('state_id', null);
		$this->m_field['country_id'] = array('country_id', null);
		$this->m_field['city_title'] = array('city_title', null);
		$this->m_field['lat'] = array('lat', null);
		$this->m_field['long'] = array('long', null);
	}

	function action()
	{
		global $g_options;
        global $p;

		$cmd = get_param("cmd", "");
		$id = get_param("id", "");
		$title = trim(get_param("name", ""));
		$state_id = trim(get_param("state_id", ""));
		$country_id = trim(get_param("country_id", ""));
		$lat = floatval(trim(get_param("lat"))) * IP::MULTIPLICATOR;
		$long = floatval(trim(get_param("long"))) * IP::MULTIPLICATOR;

		if ($cmd == "delete")
		{
            $del = get_param('item', 0);
            if ($del != 0) {
                $country = explode(',', $del);
                foreach ($country as $id) {
                    DB::execute("
                        DELETE FROM geo_city WHERE
                        city_id=" . to_sql($id, "Number") . "
                    ");
                }
            }
            die('ok');
			//global $p;
			//redirect($p . "?state_id=$state_id");
		}
		elseif ($cmd == "edit" && $title)
		{
			DB::execute("
				UPDATE geo_city
				SET
				`city_title`=" . to_sql($title, "Text") . ",
				`lat`=" . to_sql($lat, "Text") . ",
				`long`=" . to_sql($long, "Text") . "
				WHERE city_id=" . to_sql($id, "Number") . "
			");
            die('ok');
			//global $p;
			//redirect($p."?state_id=$state_id&action=saved");
		}
		elseif ($cmd == "add")
		{
			DB::execute("
				INSERT INTO geo_city (country_id, city_title, state_id, `lat`, `long`)
				VALUES(
				" . to_sql($country_id, "Text") . ",
				" . to_sql($title, "Text") . ",
				" . to_sql($state_id, "Text") . ",
				" . to_sql($lat, "Text") . ",
				" . to_sql($long, "Text") . "
				)
			");
			redirect($p . "?state_id=$state_id");
		} elseif ($cmd == 'hide') {
            $item = get_param('item', 0);
            if ($item) {
                $country = explode(',', $item);
                foreach ($country as $id) {
                    DB::update('geo_city', array('hidden' => 1), 'city_id = ' . to_sql($id));
                }
            }
            die('ok');
		} elseif ($cmd == 'show') {
            $item = get_param('item', 0);
            if ($item) {
                $country = explode(',', $item);
                foreach ($country as $id) {
                    DB::update('geo_city', array('hidden' => 0), 'city_id = ' . to_sql($id));
                }
            }
            die('ok');
		}

        if($cmd == 'default') {
            Config::update('options', 'city_default', intval(get_param('city_id')));
            redirect($p . "?state_id=$state_id&offset=" . get_param('offset'));
        }
	}

	function onPostParse(&$html)
	{
        $lang = get_param('lang');
        $html->setvar('select_options_language', adminLangsSelect('main', $lang));

		$html->setvar("message", $this->message);

        $country_id = $this->countryId;

		$html->setvar("country_id_real", $country_id);

		$first_state_id = DB::result("SELECT state_id FROM geo_state WHERE country_id = $country_id ORDER BY state_title ASC LIMIT 1");

		$state_id = $this->stateId;
		$html->setvar("state_id", $state_id);

        $count = $this->m_total;

        if ($count > 0) {
            $html->parse('delete_btn');
        }
        if ($count > 1) {
            $html->parse('select_btn');
        }

		$country_options = DB::db_options("SELECT country_id, country_title
		FROM geo_country
		ORDER BY country_title ASC", $country_id);
		$html->setvar("geo_countries", $country_options);

		$state_options = DB::db_options("SELECT state_id, state_title FROM geo_state WHERE country_id = $country_id ORDER BY state_title ASC", $state_id);
		$html->setvar("geo_states", $state_options);

        $html->setvar('offset_current', get_param('offset'));
	}

	function onItem(&$html, $row, $i, $last)
	{
        $html->setvar('offset_current', get_param('offset'));
        $html->setvar('city_id', $row['city_id']);
        $html->setvar('state_id', $row['state_id']);
        $html->setvar('country_id', $row['country_id']);

        $this->m_field['lat'][1] = $row['lat'] / IP::MULTIPLICATOR;
        $this->m_field['long'][1] = $row['long'] / IP::MULTIPLICATOR;
        $this->m_field['city_title'][1] = htmlentities($row['city_title'], ENT_QUOTES, "UTF-8");

        if($row['hidden'] == 1) {
            $html->parse('item_show', false);
            $html->clean('item_hide');
        } else {
            $html->parse('item_hide', false);
            $html->clean('item_show');
        }

        $cityDefault = Common::getOption('city_default');
        $html->subcond($row['city_id'] != $cityDefault, 'set_as_default_city');

		parent::onItem($html, $row, $i, $last);
	}

    function initLocationData()
    {
		$first_country_id = DB::result("SELECT country_id FROM geo_country
		ORDER BY country_title ASC LIMIT 1");

		// if save by state_id
		$state = intval(get_param('state_id', ''));
		if($state) {
			$first_country_id = DB::result("SELECT country_id FROM geo_state
			WHERE state_id = $state
			LIMIT 1");
		}

		$this->countryId = intval(get_param("country_id", $first_country_id));

        $first_state_id = DB::result("SELECT state_id FROM geo_state WHERE country_id = " . to_sql($this->countryId) . " ORDER BY state_title ASC LIMIT 1");

        $this->stateId = intval(get_param('state_id', $first_state_id));
    }
}

$page = new CForm("main", $g['tmpl']['dir_tmpl_administration'] . "users_fields_cities.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuUsersFields());

include("../_include/core/administration_close.php");

?>
