<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */
//   Rade 2023-09-23

include("../_include/core/administration_start.php");

class CAdminSMSMass extends CHtmlList
{

    var $message_send = '';
    var $table = 'masssms';
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
                    if ($lang == 'default') {
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

            $toUsers = get_param('users', '');

            if ($whereLang == '') {
                $this->message_send = l('Please choose languages');
            } else {

                $row = array(
                    'subject' => $subject,
                    'text' => $text,
                    'users' => '1',
                    'other' => '0',
                    'partners' => '0',
                    'languages' => $whereLangValue,
                    'date' => time(),
                );

                DB::insert($this->getTable(), $row);

                $this->message_send = l('Mail is added.');
            }
        }

        $id = get_param('id');
        $where = 'id = ' . to_sql($id);

        if ($cmd == 'delete') {
            DB::delete($this->getTable(), $where);
        }
        if ($cmd == 'start') {
            DB::update($this->getTable(), array('status' => 0), $where);
        }
        if ($cmd == 'stop') {
            DB::update($this->getTable(), array('status' => 1), $where);
        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $p;
        $html->setvar("message_send", $this->message_send);
        $html->setvar("to", get_param("to", ""));

        $cmd = get_param('cmd');
        $id = get_param('id');

        $masssms = null;

        if ($cmd == 'view') {
            $sql = 'SELECT * FROM ' . $this->getTable() . ' WHERE id = ' . to_sql($id);
            $masssms = DB::row($sql);
        }

        $html->setvar("masssms_subject", htmlspecialchars(get_param('subject', $masssms['subject'])));
        $html->setvar("masssms_text", get_param("text", $masssms['text']));

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
        foreach ($forItemsList as $value) {
            if ($row[$value]) {
                $forItems[] = l($value);
            }
        }

        $languagesItems = explode(',', $row['languages']);
        foreach ($languagesItems as $key => $value) {
            $value = trim($value, "'");
            if ($value == 'default') {
                $value = 'language_default';
            }
            $languagesItems[$key] = l($value);
        }

        $this->m_field['for'][1] = implode(', ', $forItems);
        $this->m_field['languages'][1] = implode(', ', $languagesItems);

        $html->setvar('id', $row['id']);

        if ($row['status'] == 0) {
            $status = l('sending');
            $html->parse('stop', false);
            $html->setblockvar('start', '');
        }
        if ($row['status'] == 1) {
            $status = l('stop');
            $html->parse('start', false);
            $html->setblockvar('stop', '');
        }
        if ($row['status'] == 2) {
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
}

$page = new CAdminSMSMass("masssms", $g['tmpl']['dir_tmpl_administration'] . "masssms.html");

$items = new CAdminConfig("config_fields", $g['tmpl']['dir_tmpl_administration'] . "_config.html");
$items->setModule('masssms');
$page->add($items);

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
