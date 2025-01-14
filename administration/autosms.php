<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */
//   Rade 2023-09-23

include("../_include/core/administration_start.php");

class CAdminSMSAuto extends CHtmlBlock {

    var $message_auto = "";

    
    function action()
    {
        global $g;
        global $p;

        $cmd = get_param('cmd', '');

        $lang    = get_param('lang', 'default');
        $note    = get_param('note', 'join');

        if ($cmd == 'edit') {
            $subject = get_param('subject', '');
            $text    = get_param('text', '');

            /*$enabled  = get_param('enabled') == 'on' ? 'Y' : 'N';
            if (isset($g['automail'][$note])) {
                Config::update('automail', $note, $enabled);
            } else {
                Config::add('automail', $note, $enabled, 'max', 0);
            }*/

            // check that item exists and add otherwise
            $sql = 'INSERT INTO `sms_auto`
                       SET `subject` = ' . to_sql($subject, 'Text') . ',
                              `text` = ' . to_sql($text, 'Text') . ',
                              `note` = ' . to_sql($note, 'Text') . ',
                              `lang` = ' . to_sql($lang, 'Text')
                    . ' ON DUPLICATE KEY UPDATE
                           `subject` = ' . to_sql($subject, 'Text') . ',
                              `text` = ' . to_sql($text, 'Text');
            DB::execute($sql);

        } elseif($cmd == 'translate_batch') {

            $dataSrc = $data = json_decode(get_param('data'), true);
            $fromLang = get_param('translate_from');
            $toLang = get_param('translate_to');

            LanguageTranslator::saveLanguageTranslatorSettings($lang, $fromLang, $toLang);

            $dataArrayKeys = array_keys($data);
            foreach($dataArrayKeys as $index => $key) {
                $dataNumericArray[$index] = $data[$key];
            }

            $translate = LanguageTranslator::autoTranslateSiteLanguageByAdministrator($dataNumericArray, $fromLang, $toLang);

            foreach($translate as $key => $translateItem) {
                $data[$dataArrayKeys[$key]] = $translateItem;
            }

            $result = array('translate' => $data);
            if(LanguageTranslator::$error) {
                $result['error'] = LanguageTranslator::$errorMessage;
            }

            echo json_encode($result);

            die();

        } elseif($cmd == 'delete') {
            if($lang != 'default') {
                DB::delete('sms_auto', '`note` = ' . to_sql($note) . ' AND `lang` = ' . to_sql($lang));
            }
        }

        if($cmd) {
            redirect($p . '?note=' . $note . '&lang=' . $lang . '&action=saved');
        }
    }

    function parseBlock(&$html)
    {
        global $g;
        global $p;

        $html->setvar("message_auto", $this->message_auto);

        $languageCurrent = Common::langParamValue();
        $html->setvar('lang', $languageCurrent);

        $note = get_param('note', 'new_mail_sms');

        $html->setvar('note_current', $note);

        if (!in_array($note, array('invite', 'forget', 'forget_link', 'partner_forget'))) {
            $html->setvar("checked", Common::isEnabledAutoMail($note) ? 'checked' : '');
            $html->parse("sms_msg_enabled");
        }

        $sql = 'SELECT * FROM `sms_auto` '
                . 'WHERE `lang` IN (' . to_sql($languageCurrent, 'Text') . ', "default")'
                . ' AND `note` = ' . to_sql($note, 'Text') . '
                ORDER BY `lang` = ' . to_sql($languageCurrent, 'Text') . ' DESC';
        DB::query($sql);

        $lang = Common::getOption('administration', 'lang_value');
        $langTinymceUrl =  $g['tmpl']['url_tmpl_administration'] . "js/tinymce/langs/{$lang}.js";
        if (!file_exists($langTinymceUrl)) {
            $lang = 'default';
        }
        $html->setvar('lang_vw', $lang);

        $currentEmailTextLanguage = 'default';

        if ($row = DB::fetch_row()) {
            $currentEmailTextLanguage = $row['lang'];
            $html->setvar("note", $row['note']);
            $html->setvar("subject", $row['subject']);
            if (strip_tags($row['text']) == $row['text']) {
               $row['text'] = nl2br($row['text']);
            }
            $html->setvar("text", $row['text']);
            $html->parse("sms_msg", true);
        } else {
            $html->parse("sms_nomsg", true);
        }

        $sql = "SELECT * FROM sms_auto"
             . " WHERE lang = 'default' "
             . ' ORDER BY id';
        DB::query($sql);
        while ($row = DB::fetch_row()) {
            if ($note == $row['note']) {
                $html->setvar("id", $row['id']);
                $html->setvar("note", $row['note']);
                $html->setvar("note_title", l(ucfirst(str_replace("_", " ", $row['note']))));
                $html->parse("sms_on", false);
                $html->setblockvar("sms_off", "");
                $html->parse("sms", true);
            } else {
                $html->setvar("id", $row['id']);
                $html->setvar("note", $row['note']);
                $html->setvar("note_title", l(ucfirst(str_replace("_", " ", $row['note']))));
                $html->parse("sms_off", false);
                $html->setblockvar("sms_on", "");
                $html->parse("sms", true);
            }
        }

        adminParseLangsModule($html, $languageCurrent);

        $lang = get_param('lang', 'default');

        if(IS_DEMO) {
            Common::setOptionRuntime('Y', 'autotranslator_enabled');
        }

        $isAutotranslatorEnabled = Common::isOptionActive('autotranslator_enabled');

        if($isAutotranslatorEnabled) {

            $currentLang=array();
            if($lang!=='default'){
                $l = null;
                $l = loadLanguage($lang, 'main', $l);
                $currentLang=$l;
                $l=array();
                $l = loadLanguage('default', 'main', $l);

            }

            if(!isset($l)){
                $l=array();
            }

            $defaultSiteLanguage = loadLanguage('default', 'main');

            $translateFrom = 'en';
            $translateTo = false;

            $languageLocale = false;

            $languageSettings = LanguageTranslator::getLanguageSettings($lang);

            if($languageSettings) {
                $translateFrom = $languageSettings[0];
                $translateTo = $languageSettings[1];
            } else {

                $translateToLanguage = $currentLang;

                if(l('language_code', $translateToLanguage) === 'language_code') {
                    $languageLocale = array_search(mb_ucfirst(mb_strtolower($lang)), LanguageTranslator::$languagesList);
                }

                $translateTo = $languageLocale ? $languageLocale : LanguageTranslator::getLanguageLocale($translateToLanguage);

            }

            $html->setvar('translate_from', h_options(LanguageTranslator::$languagesList, $translateFrom));

            $html->setvar('translate_to', h_options(LanguageTranslator::$languagesList, $translateTo));

            $translateTypeOptions = array(
                'translate_on_page' => l('translate_only_the_phrases_on_this_page'),
                'translate_all' => l('translate_everything'),
            );

            $html->setvar('translate_type_options', h_options($translateTypeOptions, 'first'));

            $html->parse('autotranslator_header');
        } else {
            $html->setvar('autotranslator_hide_item_class', 'hide');
        }

        if($currentEmailTextLanguage != 'default') {
            $html->parse('delete');
        }

        parent::parseBlock($html);
    }

}

$page = new CAdminSMSAuto("", $g['tmpl']['dir_tmpl_administration'] . "autosms.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
?>
