<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
// Rade 2023-09-23

include("../_include/core/administration_start.php");

class CAdminMailMass extends CHtmlList {

    var $message_send = '';
	var $message_error = '';
    var $table = 'massmail';
    var $tableAlias = 'm';
    var $fields = array(
        'id',
        'subject',
        'text',
        'for',
        'languages',
        'date',
        'status',
    );
    var $m_sql_order = 'id DESC';
    var $usersFilter = array();

    function action()
    {
        global $g;

        $cmd = get_param('cmd', '');
        $lang = get_param_array('language');

        if ($cmd == 'send') {
            $whereLang = '';
            if (count($lang) > 0) {
                $whereLangParts = array();
                foreach ($lang as $language => $value) {
                    if($lang == 'default') {
                        $whereLangParts[] = "''";
                    }
                    $whereLangParts[] = to_sql($language);
                }
                $whereLangValue = implode(',', $whereLangParts);
                if ($whereLangValue != '') {
                    $whereLang = ' AND lang IN (' . $whereLangValue . ')';
                }
            }

            $subject = get_param('subject', '');
            $text = get_param('text', '');
            $to = trim(get_param('to', ''));

            $toPartners = get_param('partners', '');
            $toUsers = get_param('users', '');
            $toOther = get_param('other', '');

            if ($to != '') {
                if(Common::validateEmail($to)) {
                    send_mail($to, $g['main']['info_mail'], $subject, $text);
                    $this->message_send = l('sent');
                } else {
                    $this->message_send = l('Specific email is incorrect');
					$this->message_error = $this->message_send;
                }
            } elseif($whereLang == '') {
                $this->message_send = l('Please choose languages');
				$this->message_error = $this->message_send;
            } else {

                if($toPartners == '1' || $toUsers == '1' || $toOther == '1') {

                    $send_partners = 0;
                    if(!$toUsers && !$toOther) {
                        $send_partners = 1;
                    }

                    $usersFilter = array();

                    if($toUsers == '1') {
                        $usersFilter['paid'] = get_param('paid');
                        $usersFilter['orientation'] = get_param_array('orientation');
                        $usersFilter['date_register_from'] = get_param('date_register_from');
                        $usersFilter['date_register_to'] = get_param('date_register_to');
                        $usersFilter['last_visit_from'] = get_param('last_visit_from');
                        $usersFilter['last_visit_to'] = get_param('last_visit_to');
                        $usersFilter['age_from'] = get_param('age_from');
                        $usersFilter['age_to'] = get_param('age_to');
                    }

                    $row = array(
                        'subject' => $subject,
                        'text' => $text,
                        'users' => $toUsers,
                        'other' => $toOther,
                        'partners' => $toPartners,
                        'languages' => $whereLangValue,
                        'date' => time(),
                        'users_filter' => json_encode($usersFilter),
                    );

                    $id = get_param('id');
                    if($id) {
                        DB::update($this->getTable(), $row, '`id` = ' . to_sql($id));
                        $this->message_send = l('Mail is updated.');
                    } else {
                        DB::insert($this->getTable(), $row);
                        $this->message_send = l('Mail is added.');
                    }

                }
            }
        }

        $id = get_param('id');
        $where = 'id = ' . to_sql($id);

        if ($cmd == 'delete') {
            DB::delete($this->getTable(), $where);
        }
        if ($cmd == 'start') {
            DB::update($this->getTable(), array('status' => 0, 'emails_sent' => 0), $where);
        }
        if ($cmd == 'stop') {
            DB::update($this->getTable(), array('status' => 1), $where);
        }

        if($cmd == 'update') {
            $items = new CAdminConfig('config_fields', '');
            $items->setModule('massmail');
            $items->action();
        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $p;

		$isTmplModern = Common::isAdminModer();
		if ($isTmplModern) {
			if ($this->message_error) {
				$html->setvar("message_error", $this->message_error);
			} else {
				$html->setvar("message_send", $this->message_send);
			}
		} else {
			$html->setvar("message_send", $this->message_send);
		}

        $html->setvar("to", get_param("to", ""));

        $cmd = get_param('cmd');
        $id = get_param('id');

        $massmail = null;

        if($cmd == 'view') {
            $massmail = DB::one($this->getTable(), 'id = ' . to_sql($id));
            if($massmail) {
                if($massmail['users_filter']) {
                    $this->usersFilter = json_decode($massmail['users_filter'], true);
                }
                if(isset($massmail['languages'])) {
                    $massmailLanguages = explode(',', $massmail['languages']);
                    foreach($massmailLanguages as $language) {
                        $_POST['language'][trim($language, "'")] = 1;
                    }
                }
            }
            $html->setvar('edit_id', $id);
        }

        $html->setvar("massmail_subject", htmlspecialchars(get_param('subject', $massmail['subject'])));
        $html->setvar("massmail_text", get_param("text", $massmail['text']));

        $receiverTypes = array(
            'users',
            'other',
            'partners',
        );

        foreach($receiverTypes as $receiverType) {
            if(get_param($receiverType, isset($massmail[$receiverType]) ? $massmail[$receiverType] : 0)) {
                $html->setvar($receiverType . '_checked', 'checked');
            }
        }

        $language = loadLanguageAdmin();

        $paid = $this->getUsersFilterValue('paid');
        if($paid === '1') {
            $html->setvar('paid_checked', 'checked');
        } elseif ($paid === '0') {
            $html->setvar('free_checked', 'checked');
        }

        if (UserFields::isActive('orientation')) {
            $orientation = get_param_array('orientation');

            $this->parseChecks($html, 'SELECT id, title FROM const_orientation', $this->getUsersFilterValue('orientation'), 2, 0, 'orientation', 'orientation', $language);
        }

        $html->setvar('date_register_from', $this->getUsersFilterValue('date_register_from'));
        $html->setvar('date_register_to', $this->getUsersFilterValue('date_register_to'));
        $html->setvar('last_visit_from', $this->getUsersFilterValue('last_visit_from'));
        $html->setvar('last_visit_to', $this->getUsersFilterValue('last_visit_to'));

        $html->setvar("age_from_options", n_options($g['options']['users_age'], $g['options']['users_age_max'], $this->getUsersFilterValue('age_from'), true));
		$html->setvar("age_to_options", n_options($g['options']['users_age'], $g['options']['users_age_max'], $this->getUsersFilterValue('age_to'), true));

        $lang = get_param_array('language');

        $langs = Common::listLangs();

        if ($langs) {
            foreach ($langs as $file => $title) {
                $html->setvar('language_value', $file);
                $html->setvar('language_title', $title);

                $languageChecked = '';

                if (isset($lang[$file]) && $lang[$file] == 1) {
                    $languageChecked = 'checked';
                }

                $html->setvar('language_checked', $languageChecked);
                $html->parse('language');
            }
        }

        $lang = Common::getOption('administration', 'lang_value');
        $langTinymceUrl =  $g['tmpl']['url_tmpl_administration'] . "js/tinymce/langs/{$lang}.js";
        if (!file_exists($langTinymceUrl)) {
            $lang = 'default';
        }
        $html->setvar('lang_vw', $lang);

        parent::parseBlock($html);
    }

	function onItem(&$html, $row, $i, $last)
	{
		$this->m_field['date'][1] = date('Y-m-d H:i:s', $row['date']);

        $forItems = array();

        $forItemsList = array('users', 'other', 'partners');
        foreach($forItemsList as $value) {
            if($row[$value]) {
                $forItems[] = l($value);
            }
        }

        $languagesItems = explode(',' , $row['languages']);
        foreach($languagesItems as $key => $value) {
            $value = trim($value, "'");
            if($value == 'default') {
                $value = 'language_default';
            }
            $languagesItems[$key] = l($value);
        }

        $this->m_field['for'][1] = implode(', ', $forItems);
        $this->m_field['languages'][1] = implode(', ', $languagesItems);

		$html->setvar('id', $row['id']);
		$html->setvar('emails_sent', $row['emails_sent']);

		if($row['status']==0) {
			$status = l('sending');
			$html->parse('stop', false);
			$html->setblockvar('start', '');
		}
		if($row['status']==1) {
			$status = l('stop');
			$html->parse('start', false);
			$html->setblockvar('stop', '');
		}
		if($row['status']==2) {
			$status = l('sent');
			$html->parse('start', false);
			$html->setblockvar('stop', '');
		}

		$this->m_field['status'][1] = $status;

        if ($i % 2 == 0) {
            $html->setvar("class", 'color');
            $html->setvar("decl", '_l');
            $html->setvar("decr", '_r');
        } else {
            $html->setvar("class", '');
            $html->setvar("decl", '');
            $html->setvar("decr", '');
        }

	}

    public function translation($field, $value, $lang = null)
    {

        static $tmpl = null;
        if(!$tmpl) {
            $tmpl = Common::getOption('name', 'template_options');
        }
        if ($lang === null ) {
            $lang = loadLanguageAdmin();
        }

        $keys = array($tmpl . '_' . $field . '_' . $value, $field . '_' . $value, $value);
        return lCascade($value, $keys, $lang);
    }

	function parseChecks(&$html, $sql, $activeValues, $num_columns = 1, $add = 0, $p = "", $field = null, $lang = null)
	{
		if (DB::query($sql))
		{
            if(!is_array($activeValues)) {
                $activeValues = array();
            }

            if ($field === null ) {
                $field = $p;
            }
            if ($lang === null ) {
                $lang = loadLanguageAdmin();
            }

            $html->setvar($p . '_field_title', l($field, $lang));

			$i = 0;
			$total_checks = DB::num_rows();
			$in_column = ceil(($total_checks + $add) / $num_columns);

			while ($row = DB::fetch_row())
			{
				$i++;

				$html->setvar($p . "_id", $row[0]);
                //echo $row['title'] . '<br>';
                $value = $this->translation($field, $row['title'], $lang);
				$html->setvar($p . "_title", $value);
				if (in_array($row[0], $activeValues)) {
					$html->setvar("checked", " checked");
				} else {
					$html->setvar("checked", "");
				}
				$html->parse($p, true);
			}
			$html->parse($p . "s", true);
			$html->setblockvar($p, "");
			DB::free_result();
		}
	}

    function getUsersFilterValue($param, $defaultValue = '')
    {
        $value = get_param($param, isset($this->usersFilter[$param]) ? $this->usersFilter[$param] : $defaultValue);
        return $value;
    }
}

$page = new CAdminMailMass("massmail", $g['tmpl']['dir_tmpl_administration'] . "massmail.html");

$items = new CAdminConfig("config_fields", $g['tmpl']['dir_tmpl_administration'] . "_config.html");
$items->setModule('massmail');
$page->add($items);

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMassMail());

include("../_include/core/administration_close.php");