<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CHtmlList extends CHtmlBlock
{
	var $m_sql_count = "";
	var $m_sql = "";
	var $m_sql_where = "1";
	var $m_sql_order = "";
	var $m_sql_group = "";
	var $m_sql_from_add = "";
	var $m_cache = false;
	var $m_cache_mins = 5;
	var $m_cache_name = "";
	var $m_offset = 1;
	var $m_offset_real = false;

	var $m_on_page = 10;
	var $m_on_bar = 10;

	var $m_params = "";
    var $m_parse_params_for_empty = false;

    var $m_total = 0;
    var $m_folder_total = '';
    var $m_folder_block = 'folder';
    var $m_folder_name = '';
    var $m_tmplBoxFolder = 'mail_folder_tile';
    var $m_tmplCounter = 'mail_folder_counter';
    var $m_nameItemBox = 'folder';
    var $m_search = false;
	var $m_field = array();

	var $m_debug = "N";

	var $row_breaks = false;
	var $row_breaks_n_cols = 2;
	var $row_break = '</tr><tr>';

    var $table = '';
    var $tableAlias = '';
    var $fields = null;
    var $fieldsFromAdd = null;
    var $fieldKey = 'id';

    var $translateForAdmin = false;

    var $redirectIfSingle = false;


	function parseBlock(&$html)
	{

        $sqlCount = $this->m_sql_count . " WHERE " . $this->m_sql_where;
        if ($this->m_sql_group) {
            $sqlCount = "SELECT COUNT(*) FROM (" . $sqlCount . ($this->m_sql_group ? (" GROUP BY " . $this->m_sql_group) : '') . ") t";
        }
        if ($this->m_cache) {
            $total = DB::result_cache($this->m_cache_name . "c", $this->m_cache_mins, $sqlCount);
        } else {
            $total = DB::result($sqlCount);
        }

        if ($this->m_debug == "Y") echo $sqlCount . ";<br />";


        $this->m_total = $total;

        if ($this->m_folder_total == '') {
            $this->m_folder_total = $total;
        }
        if ($this->m_folder_name != '') {
            $html->setvar($this->m_folder_block, Common::getFolderTitle($this->m_folder_name, $this->m_folder_total, $this->m_tmplBoxFolder, $this->m_tmplCounter, $this->m_nameItemBox));
        }
        $html->setvar("total", $total);

		if ($total > 0)
		{
			$offset_real = $this->m_offset_real;
			$on_page = (int) $this->m_on_page;
            if($on_page < 1) {
                $on_page = 1;
            }

			$html->setvar("num_total", $total);
			$offset = (int) get_param("offset", $this->m_offset);
			if ($offset_real) $offset = min($total, max($offset, 0));
			elseif (abs($offset) % $total == 0 and $offset <= 0) $offset = 1;
			elseif ($offset < 1) $offset = $total - abs($offset) % $total;
			elseif ($offset > $total) $offset = $offset % $total;

			if (isset($this->m_chk) && $total-$offset+1-$on_page < $this->m_chk) $on_page=$total-$offset+1;

			$tmp_params = $this->m_params;
			if ($tmp_params == "") $this->m_params = get_params_string();
			$this->m_params = del_param("offset", $this->m_params);
			$this->m_params = del_param("cmd", $this->m_params);
			$this->m_params = del_param("delete", $this->m_params);
            $this->m_params = del_param("action", $this->m_params);
			$this->m_params = del_param("edit", $this->m_params);
			$this->m_params = del_param("visible", $this->m_params);
			$this->m_params = del_param("id", $this->m_params);
			$this->m_params = del_param("PHPSESSID", $this->m_params);
			$html->setvar("params", htmlspecialchars($this->m_params, ENT_QUOTES));

            $html->setvar("on_page", $on_page);

			$on_bar = $this->m_on_bar;

			$num_pages = ceil($total / $on_page);
			$num_bars = ceil($num_pages / $on_bar);
			$this_page = ceil($offset / $on_page);
			$this_bar = ceil($this_page / $on_bar);

			$html->setvar("this_page", $this_page);
			$html->setvar("num_pages", $num_pages);

			$offset_real = $offset_real ? $offset : (($this_page - 1) * $on_page + 1);

			for ($i = $on_bar * ($this_bar - 1) + 1; $i <= (($this_bar == $num_bars) ? $num_pages : $on_bar * $this_bar); $i++)
			{
				$html->setvar("page", $i);
				$html->setvar("offset", (($i - 1) * $on_page) + 1);

				if ($i == $this_page)
				{
					$html->parse($this->m_name . "_this_page", false);
					$html->setblockvar($this->m_name . "_link_page", "");
				}
				else
				{
					$html->parse($this->m_name . "_link_page", false);
					$html->setblockvar($this->m_name . "_this_page", "");
				}
				$html->parse($this->m_name . "_page", true);
                $html->parse($this->m_name . "_page_top", true);
			}

			if ($this_page != 1)
			{
				$html->setvar("offset", (($this_page - 2) * $on_page) + 1);
				$html->parse($this->m_name . "_prev_page", true);
                $html->parse($this->m_name . "_prev_page_top", true);
			} else {
                $html->parse($this->m_name . "_prev_page_off", true);
                $html->parse($this->m_name . "_prev_page_off_top", true);
            }
			if ($this_page != $num_pages)
			{
				$html->setvar("offset", (($this_page - 0) * $on_page) + 1);
				$html->parse($this->m_name . "_next_page", true);
                $html->parse($this->m_name . "_next_page_top", true);
			} else {
                $html->parse($this->m_name . "_next_page_off", true);
                $html->parse($this->m_name . "_next_page_off_top", true);
            }

			if ($this_bar != 1)
			{
				$html->setvar("offset", 1);
				$html->parse($this->m_name . "_first_page", true);
                $html->parse($this->m_name . "_first_page_top", true);
				$html->setvar("offset", ((($this_bar - 1) * $on_bar - 1) * $on_page) + 1);
				$html->parse($this->m_name . "_prev_pages", true);
                $html->parse($this->m_name . "_prev_pages_top", true);
			}
			if ($this_bar != $num_bars)
			{
				$html->setvar("offset", (($num_pages - 1) * $on_page) + 1);
				$html->parse($this->m_name . "_last_page", true);
                $html->parse($this->m_name . "_last_page_top", true);
				$html->setvar("offset", ((($this_bar + 0) * $on_bar) * $on_page) + 1);
				$html->parse($this->m_name . "_next_pages", true);
                $html->parse($this->m_name . "_next_pages_top", true);
			}

			// SHOW PAGINATOR ONLY WHEN MORE 1 item
			if($num_pages>1) $html->parse($this->m_name . "_pages", true);
            if($num_pages>1) $html->parse($this->m_name . "_pages_top", true);

			$tmp_params = $this->m_params;
			if ($tmp_params == "") $this->m_params = htmlspecialchars(get_params_string(), ENT_QUOTES);
			$this->m_params = del_param("cmd", $this->m_params);
			$this->m_params = del_param("delete", $this->m_params);
            $this->m_params = del_param("action", $this->m_params);
			$this->m_params = del_param("edit", $this->m_params);
			$this->m_params = del_param("visible", $this->m_params);
			$this->m_params = del_param("id", $this->m_params);
			$this->m_params = del_param("display", $this->m_params);
			$this->m_params = del_param("PHPSESSID", $this->m_params);
			$html->setvar("params", $this->m_params);

            // URBAN PAGINATOR
            Common::parsePagesListUrban($html, $this_page, $total, $on_page, $on_bar);

			if ($this->m_sql_order != "") $this->m_sql_order = ", " . $this->m_sql_order;

			foreach ($this->m_field as $key => $field)
			{
				$this->m_params = set_param("order", $field[0], $this->m_params);
				$order = get_param("order", "");
				$sort = get_param("sort", "");
				$order = htmlspecialchars($order, ENT_QUOTES);
				$sort = htmlspecialchars($sort, ENT_QUOTES);

				if ($order == $field[0] and $sort == "asc")
				{
					$this->m_params = set_param("sort", "desc", $this->m_params);
					$this->m_sql_order = " " . $key . " ASC" . $this->m_sql_order;

					$html->setvar("params_current", $this->m_params);
					$html->parse("params_desc_" . $field[0], false);
				}
				elseif (($order == $field[0] and $sort == "desc"))
				{
					$this->m_params = set_param("sort", "asc", $this->m_params);
					$this->m_sql_order = " " . $key . " DESC" . $this->m_sql_order;

					$html->setvar("params_current", $this->m_params);
					$html->parse("params_asc_" . $field[0], false);
				}
				else
				{
					$this->m_params = set_param("sort", (isset($field[2]) ? $field[2] : "asc"), $this->m_params);

					$html->setvar("params_current", $this->m_params);
					$html->parse("params_none_" . $field[0], false);
				}
			}

			if (substr($this->m_sql_order, 0, 2) == ", ") $this->m_sql_order = substr($this->m_sql_order, 2);
			if ($offset_real <= 0) $offset_real = 1;
			if ($this->m_debug == "Y") echo $this->m_sql . " WHERE " . $this->m_sql_where . ($this->m_sql_group ? (" GROUP BY " . $this->m_sql_group) : '') . ($this->m_sql_order ?  " ORDER BY " . $this->m_sql_order : '' ) . " LIMIT " . ($offset_real - 1) . "," . $on_page . ";<br />";

			$i = 1;
            $preparedSql = $this->m_sql ." WHERE " . $this->m_sql_where . ($this->m_sql_group ? (" GROUP BY " . $this->m_sql_group) : '') . ($this->m_sql_order ?  " ORDER BY " . $this->m_sql_order : '' ) . " LIMIT " . ($offset_real - 1) . "," . $on_page . ";";

            DB::query($preparedSql, 8);
			$last = DB::num_rows(8);
			while ($row = DB::fetch_row(8))
			{
				if(!isset($first_row)){
					$first_row=$row;
				}
				$this->m_params = get_params_string();
				$this->m_params = del_param("cmd", $this->m_params);
				$this->m_params = del_param("delete", $this->m_params);
                $this->m_params = del_param("action", $this->m_params);
				$this->m_params = del_param("edit", $this->m_params);
				$this->m_params = del_param("visible", $this->m_params);
				$this->m_params = del_param("id", $this->m_params);
				$this->m_params = del_param("display", $this->m_params);
				$this->m_params = del_param("PHPSESSID", $this->m_params);
				$this->m_params = set_param("offset", ($offset_real + $i - 1), $this->m_params);

	            if (strpos($this->m_params, 'name_seo=wall_shared') !== false) {
	                $this->m_params = '';
	            }
				$html->setvar("params_item", $this->m_params);
				$html->setvar("offset", ($offset_real + $i - 1));

                $this->onItem($html, $row, $i, $last);
				foreach ($this->m_field as $key => $field)
				{
					if (!isset($field[1])) $field[1] = null;
					if (!isset($field[0])) trigger_error('Undefined field name: ' . $key . ', class: ' . get_class($this));
					if ($field[1] === null) {
						if (isset($row[$key]) or $row[$key] == null) {
							$field[1] = $row[$key];
						} else {
							trigger_error('Undefined db row index: ' . $key . ', class: ' . get_class($this));
						}
					}
                    if($this->translateForAdmin === true) {
                        $lang = loadLanguageAdmin();
                        if(isset($field[2])) {
                            $lang_key = $field[2];
                        }
                        else {
                         $lang_key = false;
                        }
                        $html->setvar($field[0], l($field[1],$lang,$lang_key));
                    }  else {
                        $html->setvar($field[0],$field[1]);
                    }
				}
				$html->parse($this->m_name . "_item", true);
				$i++;
			}
			if(!isset($first_row)){
				$preparedSql = $this->m_sql ." WHERE " . $this->m_sql_where . ($this->m_sql_group ? (" GROUP BY " . $this->m_sql_group) : '') . ($this->m_sql_order ?  " ORDER BY " . $this->m_sql_order : '' ) ." LIMIT " . ($offset_real - 1) . "," . $on_page . ";";
				DB::query($preparedSql);
				$row = DB::fetch_row();
			} else {
				$row=$first_row;
			}
			$html->setvar("offset", $offset);
			$html->parse($this->m_name . "_items", true);

            if($this->redirectIfSingle){
                $html->setvar("single_user_id", $first_row['user_id']);
                $html->parse("redirect_if_single", true);
            }
		}
		else
		{
            if ($this->m_parse_params_for_empty) {
                $this->m_params = get_params_string();
                $this->m_params = del_param("offset", $this->m_params);
                $this->m_params = del_param("cmd", $this->m_params);
                $this->m_params = del_param("delete", $this->m_params);
                $this->m_params = del_param("action", $this->m_params);
                $this->m_params = del_param("edit", $this->m_params);
                $this->m_params = del_param("visible", $this->m_params);
                $this->m_params = del_param("id", $this->m_params);
                $this->m_params = del_param("PHPSESSID", $this->m_params);
                $html->setvar("params", $this->m_params);
            }

            $row = null;
			$html->setvar("num_total", 0);
            if ($this->m_search !== false) {
                $html->parse('search_' . $this->m_name . "_noitems", true);
            } else {
                $html->parse($this->m_name . "_noitems", true);
            }
		}


		$this->onPostParse($html,$row);

		parent::parseBlock($html);
	}

	function onPostParse(&$html)
	{

	}

	function onItem(&$html, $row, $i, $last)
	{

		if($this->row_breaks && !($i % $this->row_breaks_n_cols))
		{
			$html->setvar('row_break', $this->row_break);
		}
		else
			$html->setvar('row_break', '');
	}

    function getTable()
    {

        return to_sql($this->table, 'Plain');
    }

    function getTableAlias()
    {
        return to_sql($this->tableAlias, 'Plain');
    }

    function init()
    {
        parent::init();

        $table = $this->getTable();
        $alias = $this->getTableAlias();

        if ($table && $alias) {
            $this->m_sql_count = "SELECT COUNT($alias." . to_sql($this->fieldKey, 'Plain') . ") FROM $table AS $alias " . $this->m_sql_from_add;
            $this->m_sql = "SELECT $alias.* " . to_sql($this->fieldsFromAdd, 'Plain') . " FROM $table AS $alias " . $this->m_sql_from_add;
        }

        if (is_array($this->fields) && count($this->fields)) {
            foreach ($this->fields as $field) {
                $this->m_field[$field] = array($field, null);
            }
        }
    }

    function setOrder($field, $sort = 'ASC', $alias = true)
    {
        $sortToUpper = strtoupper($sort);
        if ($sortToUpper == 'ASC' || $sortToUpper == 'DESC') {
            $tableAlias = ($alias === true ? $this->tableAlias . '.' : '');
            $this->m_sql_order = $tableAlias . to_sql($field, 'Plain') . ' ' . to_sql($sort, 'Plain');
        }
    }

}