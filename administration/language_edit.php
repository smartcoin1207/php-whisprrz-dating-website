<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

$ajax = get_param('ajax');
if($ajax && IS_DEMO) {
    echo 'updated';
    die();
}

class CAdminLangEdit extends CHtmlBlock
{
	var $message_lang = "";
    var $part = '';
    var $lang = '';
    var $lang_page = '';
    var $filename = '';
    var $langDir = '';

    function prepareFilename()
    {
        $filename = $this->langDir . $this->part . '/'. $this->lang . '/language.php';

        return $filename;
    }

	function action()
	{
		global $g;

        $ajax = get_param('ajax');

		$cmd = get_param("cmd", "");
		$this->part = $part = get_param("part", "main");
		$this->lang = $lang = get_param("lang", "default");
		$this->lang_page = $lang_page = to_php(get_param("lang_page", "all"));

        $this->langDir = $langDir = Common::langPath($part, $g['path']['dir_lang']);
        $langPart = $part;


        $this->filename = $filename = $this->prepareFilename();

        $langDefault = $langDir . $langPart . '/default/language.php';

		if ($cmd == "update")
		{
            if($lang!=='default' && file_exists($langDefault)){
                include($langDefault);
                $defaultLang=$l;
                $l=array();
            }
			if(file_exists($filename)){
                include($filename);
            } else {
                $l=array();
            }

			for ($i = 1; $i <= 10; $i++)
			{
				if (to_php_alfabet(get_param("field" . $i, "")) != "")
				{
					$k = to_php_alfabet(get_param("field" . $i, ""));
					$v = get_param("new" . $i, "");
					$l[$lang_page][$k] = $v;
				}
			}
            $wordKey=get_param('word_key', "");
            if($wordKey == 'submit_js_patch') {
                $wordKey = 'submit';
            }
            if($lang!=='default' && $wordKey!=="" &&
                    !isset($l[$lang_page][$wordKey]) &&
                    isset($defaultLang[$lang_page][$wordKey])){

                if(!isset($l[$lang_page])){
                    $l[$lang_page]=array();
                }
                $l[$lang_page][$wordKey]=get_param('item_index_' . $wordKey, "");
            }

			$to = "";
			$to .= "<?php \r\n";
			foreach ($l as $k => $v)
			{
				if ($k == $lang_page) foreach ($v as $k2 => $v2)
				{
					$field_name = 'item_index_' . (($k2=="submit") ? "submit_js_patch" : $k2);
					$to .= "\$l['" . $k . "']['" . $k2 . "'] = \"" . to_php(get_param($field_name, 0) === 0 ? $v2 : get_param($field_name, "")) . "\";\r\n";
				}
				else foreach ($v as $k2 => $v2) $to .= "\$l['" . $k . "']['" . $k2 . "'] = \"" . to_php($v2) . "\";\r\n";
				$to .= "\r\n";
			}
			$to = substr($to, 0, strlen($to) - 2);
			$to .= "?>";

			#@chmod($g['path']['dir_lang'] . $part . "/". $lang . "/language.php", 0777);

            if(!file_exists($filename . '_src')) {
                copy($filename, $filename . '_src');
            }

            //if (is_writable($filename)) {
            if (!$handle = @fopen($filename, 'w')) {
                $this->message_lang .= "Can't open file (" . $filename . ").<br />";
            } elseif(is_writable($filename)) {
                if (@fwrite($handle, $to) === FALSE)
                    $this->message_lang .= "Can't write to file(" . $filename . ".).<br />";
                else {
                    @fclose($handle);

                    @file_put_contents($filename . '_' . date('Ymd_H'), $to);

                    if ($ajax) {
                        echo 'updated';
                        die();
                    } else {
                        redirect("language_edit.php?action=saved&part=" . $part . "&lang=" . $lang . "&lang_page=" . $lang_page . '&from_template=' . get_param('from_template'));
                    }
                }
                @fclose($handle);
            }  else {
                @fclose($handle);
                $this->message_lang .= "Can't open file (" . $filename . ").<br />";
            }

            //} else $this->message_lang .= "Can't open file (" . $filename . ").<br />";
            if($ajax) {
                echo $this->message_lang;
                die();
            }
		}
		elseif ($cmd == "delete")
		{
			include($filename);

			$key_del = get_param("key_del", "");

			if($key_del=="submit_js_patch") $key_del = "submit";

			$to = "";
			$to .= "<?php \r\n";
			foreach ($l as $k => $v)
			{
				if ($k == $lang_page)
				{
					foreach ($v as $k2 => $v2) if ($k2 != $key_del) $to .= "\$l['" . $k . "']['" . $k2 . "'] = \"" . to_php($v2) . "\";\r\n";
				}
				else foreach ($v as $k2 => $v2) $to .= "\$l['" . $k . "']['" . $k2 . "'] = \"" . to_php($v2) . "\";\r\n";
				$to .= "\r\n";
			}
			$to = substr($to, 0, strlen($to) - 2);
			$to .= "?>";

			#@chmod($g['path']['dir_lang'] . $part . "/". $lang . "/language.php", 0777);

			if (is_writable($filename))
			{
			    if (!$handle = @fopen($filename, 'w'))
			    {
			        $this->message_lang .= "Can't open file (" . $filename . ").<br />";
			    }
			    if (fwrite($handle, $to) === FALSE)
			    {
			        $this->message_lang .= "Can't write to file(" . $filename . ".).<br />";
			    }
			    else
			    {
					@fclose($handle);
                    if($ajax) {
                        $defaultWord='';
                        if($lang!=='default' && file_exists($langDefault)){
                            $l=array();
                            include($langDefault);
                            if(isset($l[$lang_page][$key_del])){
                                $defaultWord=$l[$lang_page][$key_del];
                            }
                        }
                        echo json_encode(array('message'=>'deleted','default_word'=>$defaultWord));
                        die();
                    } else {
                        redirect("language_edit.php?action=saved&part=" . $part . "&lang=" . $lang . "&lang_page=" . $lang_page);
                    }
			    }
		    	@fclose($handle);
			}
			else
			{
				$this->message_lang .= "Can't open file (" . $filename . ").<br />";

			}

            if($ajax) {
                echo $this->message_lang;
                die();
            }
		} elseif ($cmd == 'translate') {
            if($ajax) {
                $text = get_param('text');
                $fromLang = get_param('translate_from');
                $toLang = get_param('translate_to');
                $languageSection = get_param('language_section');
                $languageItemKey = get_param('language_item_key');

                $this->saveLanguageTranslatorSettings($lang, $fromLang, $toLang);

                if($this->isWordInTranslateStopList($languageSection, $languageItemKey)) {
                    $translate = $text;
                } else {
                    $translate = LanguageTranslator::autoTranslateSiteLanguageByAdministrator($text, $fromLang, $toLang);
                }

                $result = array('translate' => $translate);
                if(LanguageTranslator::$error) {
                    $result['error'] = LanguageTranslator::$errorMessage;
                }

                echo json_encode($result);
                die();
            }
		} elseif ($cmd == 'translate_batch') {
            if($ajax) {

                $translateType = get_param('translate_type');
                $translateAll = get_param('translate_all');

                $dataSrc = $data = json_decode(get_param('data'), true);
                $fromLang = get_param('translate_from');
                $toLang = get_param('translate_to');

                $this->saveLanguageTranslatorSettings($lang, $fromLang, $toLang);

                $currentLanguage = array();

                $batchSessionId = intval(get_param('batchSessionId', 0));
                $batchSessionIndex = intval(get_param('batchSessionIndex', 0));

                $batchData = array();

                if($translateType == 'translate_all') {

                    if(!$batchSessionId) {

                        //file_put_contents('log.txt', '');

                        $defaultLanguage = loadLanguage('default', $part);
                        if($lang !== 'default') {

                            $baseLanguage = array();

                            if($translateAll) {
                                $baseLanguage = $defaultLanguage;
                            }

                            $currentLanguage = loadLanguage($lang, $part, $baseLanguage);

                            if(!$translateAll) {
                                $translateLanguage = array();
                                // choose only not translated words
                                foreach($defaultLanguage as $defaultLanguageSection => $defaultLanguageSectionItems) {
                                    foreach($defaultLanguageSectionItems as $defaultLanguageSectionItemKey => $defaultLanguageSectionItemValue) {
                                        if(!isset($currentLanguage[$defaultLanguageSection][$defaultLanguageSectionItemKey])) {
                                            $translateLanguage[$defaultLanguageSection][$defaultLanguageSectionItemKey] = $defaultLanguageSectionItemValue;

                                        }
                                    }
                                }
                                $currentLanguage = $translateLanguage;
                            }

                        } else {
                            $currentLanguage = $defaultLanguage;
                        }

                        $batchData['currentLanguage'] = $currentLanguage;
                        $cleanLanguage = LanguageTranslator::removeDuplicates($currentLanguage);

                        $index = 0;

                        $itemsInBatch = get_param('batchTaskItemsLimit');

                        foreach($cleanLanguage as $key => $value) {

                            $keyIndex = floor($index / $itemsInBatch);

                            $batchData['cleanLanguage'][$keyIndex][$key] = $value;

                            $index++;
                        }

                        $batchSessionId = $this->startTranslateBatchSession($batchData);
                    } else {
                        $batchData = $this->getTranslateBatchSessionData($batchSessionId);
                        $currentLanguage = $batchData['currentLanguage'];
                    }

                } else {
                    $currentLanguage = $data;
                }

                //file_put_contents('log.txt', '$currentLanguage > ' . var_export($currentLanguage, true) . "\n\n", FILE_APPEND);

                $cleanLanguage = isset($batchData['cleanLanguage'][$batchSessionIndex]) ? $batchData['cleanLanguage'][$batchSessionIndex] : LanguageTranslator::removeDuplicates($currentLanguage);

                //var_dump_pre($cleanLanguage);

                $i = 0;
                $dataNumericArray = array();
                $dataNumericArrayKeys = array();
                foreach($cleanLanguage as $key => $value) {
                    $dataNumericArray[$i] = $value['value'];
                    $dataNumericArrayKeys[$i] = $key;
                    $i++;
                }

                //file_put_contents('log.txt', 'cleanLanguage > ' . var_export($cleanLanguage, true) . "\n\n", FILE_APPEND);

                // send batch item

                $translate = LanguageTranslator::autoTranslateSiteLanguageByAdministrator($dataNumericArray, $fromLang, $toLang);

                //var_dump_pre($translate);
                //file_put_contents('log.txt', 'translate > ' . var_export($translate, true) . "\n\n", FILE_APPEND);

                $i = 0;
                foreach($translate as $translateItem) {

                    $cleanLanguage[$dataNumericArrayKeys[$i]] = $translateItem;

                    $i++;
                }

                $translateWordKeys = array();

                $translatedWords = array();

                foreach($currentLanguage as $lSection => $lSectionItems) {
                    foreach($lSectionItems as $lSectionItemKey => $lSectionItemValue) {

                        if(!$this->isWordInTranslateStopList($lSection, $lSectionItemKey)) {
                            $value = $currentLanguage[$lSection][$lSectionItemKey];
                            if(isset($cleanLanguage[$value])) {
                                $currentLanguage[$lSection][$lSectionItemKey] = $cleanLanguage[$value];

                                if(isset($data[$lSection][$lSectionItemKey])) {
                                    $data[$lSection][$lSectionItemKey] = $cleanLanguage[$value];
                                }

                                $translatedWords[$lSection][$lSectionItemKey] = $cleanLanguage[$value];

                            }
                        } else {
                            //echo "[$lSection][$lSectionItemKey] = {$currentLanguage[$lSection][$lSectionItemKey]}\n";
                        }

                        if(isset($data[$lSection][$lSectionItemKey])) {
                            $translateWordKeys[$lSection][$lSectionItemKey] = str_replace('.', '_', $lSection . '_' . $lSectionItemKey);
                        }

                    }
                }

                // save only translated words
                // return only TRANSLATED words from current request

                $isLastItemInQueue = false;

                if($batchSessionId) {

                    //file_put_contents('log.txt', var_export($translatedWords, true) . "\n\n", FILE_APPEND);

                    $translateData = array();

                    if(isset($data[0])) {
                        foreach($data[0] as $lSection => $lSectionItems) {
                            foreach($lSectionItems as $lSectionItemKey => $lSectionItemValue) {

                                if(isset($translatedWords[$lSection][$lSectionItemKey])) {

                                    $translateData[$lSection][$lSectionItemKey] = $translatedWords[$lSection][$lSectionItemKey];

                                    //$translateData[$lSection][$lSectionItemKey] = $cleanLanguage[$lSectionItemValue];

                                    $translateWordKeys[$lSection][$lSectionItemKey] = str_replace('.', '_', $lSection . '_' . $lSectionItemKey);
                                } else {
                                    $string = "NO VALUE > $batchSessionIndex > $lSectionItemKey => $lSectionItemValue\n";
                                    //file_put_contents('log.txt', $string, FILE_APPEND);
                                }
                            }
                        }
                    }

                    if(!isset($batchData['cleanLanguage']) || $batchSessionIndex == (count($batchData['cleanLanguage']) - 1)) {
                        $isLastItemInQueue = true;
                        DB::delete('city_temp', 'id = ' . to_sql($batchSessionId));
                    }

                    $currentLanguage = $translatedWords;

                } else {
                    $translateData = $data;
                }

                if($this->saveBatchTranslation($currentLanguage)) {

                    $result = array('translate' => $translateData, 'translateWordKeys' => $translateWordKeys, 'batchSessionId' => $batchSessionId, 'batchSessionIndex' => $batchSessionIndex, 'isLastItemInQueue' => $isLastItemInQueue);
                    if(LanguageTranslator::$error) {
                        $result['error'] = LanguageTranslator::$errorMessage;
                    }

                    echo json_encode($result);
                } else {
                    echo $this->message_lang;
                }
                die();
            }
        } elseif($cmd == 'translate_batch_site_module') {

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
        }

	}

    function saveBatchTranslation($translation)
    {
        if(LanguageTranslator::$error) {
            return true;
        }

        $filename = $this->filename;

        if(file_exists($filename)){
            include($filename);
        } else {
            $l = array();
        }

        foreach($translation as $translationSection => $translationItem) {
            foreach($translationItem as $translationItemKey => $translationItemValue) {
                $l[$translationSection][$translationItemKey] = $translationItemValue;
            }
        }

        $to = "";
        $to .= "<?php \r\n";
        foreach ($l as $k => $v)
        {
            foreach ($v as $k2 => $v2) $to .= "\$l['" . $k . "']['" . $k2 . "'] = \"" . to_php($v2) . "\";\r\n";
            $to .= "\r\n";
        }
        $to = substr($to, 0, strlen($to) - 2);
        $to .= "?>";

        if(!file_exists($filename . '_src')) {
            copy($filename, $filename . '_src');
        }

        //if (is_writable($filename)) {
        if (!$handle = @fopen($filename, 'w')) {
            $this->message_lang .= "Can't open file (" . $filename . ").<br />";
        } elseif(is_writable($filename)) {
            if (@fwrite($handle, $to) === FALSE) {
                $this->message_lang .= "Can't write to file(" . $filename . ".).<br />";
            } else {
                @fclose($handle);

                @file_put_contents($filename . '_' . date('Ymd_H'), $to);
            }
            @fclose($handle);
        }  else {
            @fclose($handle);
            $this->message_lang .= "Can't open file (" . $filename . ").<br />";
        }

        if($this->message_lang) {
            echo $this->message_lang;
            die();
        }

        return true;
    }

    function saveLanguageFile($filename, $text)
    {
        if(!file_exists($filename . '_src')) {
            copy($filename, $filename . '_src');
        }

        //if (is_writable($filename)) {
        if (!$handle = @fopen($filename, 'w')) {
            $this->message_lang .= "Can't open file (" . $filename . ").<br />";
        } elseif(is_writable($filename)) {
            if (@fwrite($handle, $text) === FALSE)
                $this->message_lang .= "Can't write to file(" . $filename . ".).<br />";
            else {
                @fclose($handle);

                @file_put_contents($filename . '_' . date('Ymd_H'), $text);

                if ($ajax) {
                    echo 'updated';
                    die();
                } else {
                    redirect("language_edit.php?action=saved&part=" . $part . "&lang=" . $lang . "&lang_page=" . $lang_page . '&from_template=' . get_param('from_template'));
                }
            }
            @fclose($handle);
        }  else {
            @fclose($handle);
            $this->message_lang .= "Can't open file (" . $filename . ").<br />";
        }
    }

    function isWordInTranslateStopList($languageSection, $languageItemKey)
    {
        return LanguageTranslator::isWordInTranslateStopList($languageSection, $languageItemKey);
    }

    function saveLanguageTranslatorSettings($language, $translateFrom, $translateTo)
    {
        $module = 'language_translator';
        $option = 'settings_of_languages';
        $settings = Common::getOption($option, $module);

        $isUpdate = true;

        if ($settings !== null) {
            $settings = json_decode($settings, true);
            if (!is_array($settings)) {
                $settings = array();
            }
        } else {
            $isUpdate = false;
            $settings = array();
        }

        $settings[$language] = array($translateFrom, $translateTo);

        if ($isUpdate) {
            Config::update($module, $option, json_encode($settings));
        } else {
            Config::add($module, $option, json_encode($settings), 'max', 0, '', true);
        }
    }

    function getCountOfItemsInLanguage($l)
    {
        $count = 0;

        foreach($l as $lKey => $lItems) {
            $count += count($l[$lKey]);
        }

        return $count;
    }

    function getCountOfItemsInLanguageForTranslation($currentLanguage, $baseLanguage)
    {
        $count = 0;

        foreach($baseLanguage as $lKey => $lItems) {
            foreach($lItems as $lItemKey => $lItemValue) {
                if(!isset($currentLanguage[$lKey][$lItemKey])) {
                    $count++;
                }
            }
        }

        return $count;
    }

    function startTranslateBatchSession($data)
    {
        $row = array('params' => serialize($data));
        DB::insert('city_temp', $row);
        return DB::insert_id();
    }

    function getTranslateBatchSessionData($id)
    {
        $sql = 'SELECT `params` FROM `city_temp` WHERE `id` = ' . to_sql($id);
        $data = DB::result($sql);

        if($data) {
            $data = unserialize($data);
        }

        return $data;
    }

    function updateTranslateBatchSessionData($id, $data)
    {
        $row = array('params' => serialize($data));
        DB::update('city_temp', $row);
    }

	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $p;

        $isAjaxLoadWords = get_param('load_words');

        $html->setvar('from_template', get_param('from_template'));

		$part = get_param("part", "main");
		$html->setvar("part", $part);

        $langDir = Common::langPath($part, $g['path']['dir_lang']);
        $langPart = $part;


		$html->setvar("part_title", ucfirst($part));
		$lang = get_param("lang", "default");
		$html->setvar("lang_this", $lang);

        $langTitle = ucfirst($lang);
        if($lang == 'default') {
            $langTitle = 'English';
        }

		$html->setvar("lang_title_this", $langTitle);
		$lang_page = get_param("lang_page", "all");
		$html->setvar("lang_page_this", $lang_page);

        $html->setvar('lang_page_this_id', str_replace('.', '_', $lang_page));


		$dir = $langDir . $langPart . '/';
		$langs = array();
		if (is_dir($dir)) {
	   		if ($dh = opendir($dir)) {
		        while (($file = readdir($dh)) !== false) {
					if (is_dir($dir . $file) and substr($file, 0, 1) != '.') {

                        $langTitle = ucfirst($file);
                        if($file == 'default') {
                            $langTitle = 'English';
                        }

						$langs[$langTitle] = $file;
					}
		        }
		        closedir($dh);
    		}
		}

		natsort($langs);

        $langActive =  Common::getOption($part, 'lang_value');
        $langCurrentKey = array_search($langActive, $langs);

        if($langCurrentKey !== false) {
            $langCopy = $langs[$langCurrentKey];
            unset($langs[$langCurrentKey]);
            $langs = array($langCurrentKey => $langCopy) + $langs;
        }

		$l_glob = $l;
        unset($l);

        foreach ($langs as $k => $v) {
            $html->setvar("language", $v);
            $html->setvar("title", $k);
            //$wordsCount = loadAndCountLanguageWords($v, $part);
            //$html->setvar('words_count', $wordsCount);

            if ($v==$lang) {
				$html->parse("mlang_on", false);
			} else {
				$html->setblockvar("mlang_on", "");
			}
			$html->parse("mlang", true);
        }

        $langPath = $langDir . $langPart . '/'. $lang . '/language.php';
        $langDefault = $langDir . $langPart . '/default/language.php';
        /*
        if(!file_exists($langPath)){
            $fp = fopen($langPath, "w");
            fclose($fp);
        }
        */
		//if (file_exists($langPath))
        if(is_dir($langDir . $langPart . '/'. $lang)){
            $currentLang=array();
            if($lang!=='default' && file_exists($langDefault)){
                include($langPath);
                if(!isset($l)) {
                    $l = null;
                }
                $currentLang=$l;
                $l=array();
                include($langDefault);

            }
			if (file_exists($langPath)){
                include($langPath);
            }
            if(!isset($l)){
                $l=array();
            }

            if(get_param('from_template')) {
                $l = wordsFromTemplate($l, $part);
            }

            ksort($l);

            $defaultSiteLanguage = loadLanguage(Common::getOption('main', 'lang_value'), $langPart);

            if($langPart != 'main' && l('language_code', $defaultSiteLanguage) === 'language_code') {
                $defaultSiteLanguage = loadLanguage(Common::getOption('main', 'lang_value'), 'main');
            }

            $defaultSitePartLanguage = loadLanguage('default', $langPart);


            if(IS_DEMO) {
                Common::setOptionRuntime('Y', 'autotranslator_enabled');
            }

            $isAutotranslatorEnabled = Common::isOptionActive('autotranslator_enabled');

            if($isAutotranslatorEnabled) {

                $translateFrom = 'en';
                $translateTo = false;

                $languageLocale = false;

                $languageSettings = LanguageTranslator::getLanguageSettings($lang);

                if($languageSettings) {
                    $translateFrom = $languageSettings[0];
                    $translateTo = $languageSettings[1];
                } else {

                    $translateToLanguage = $currentLang;

                    if($langPart != 'main' && l('language_code', $translateToLanguage) === 'language_code') {
                        $translateToLanguage = loadLanguage($lang, 'main');
                    }

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

                $html->setvar('langItemsCount', $this->getCountOfItemsInLanguage($l));
                $html->setvar('langItemsCountForTranslation', $this->getCountOfItemsInLanguageForTranslation($currentLang, $defaultSitePartLanguage));

                $html->parse('autotranslator_header');
            } else {
                $html->setvar('autotranslator_hide_item_class', 'hide');
            }

            //$wordsCount = wordsCountInLanguage($l);

			$tmplAdmin = Common::getOption('administration', 'tmpl');
			$isTmplModern = $tmplAdmin == 'modern';

            //var_dump($l);
            if (isset($l[$lang_page]) && count($l[$lang_page]) < 200) {
                $html->parse('language_words_show', false);
                $isAjaxLoadWords = true;
            }
			foreach ($l as $k => $v)
			{
				if ($isTmplModern) {
					$ltitle = ucfirst($k);
				} else {
					$ltitle = substr(ucfirst($k),0,12);
					if(strlen($k)>strlen($ltitle)) $ltitle=$ltitle."...";
				}

				if ($k == $lang_page)
				{
					$html->setvar("page_title", ucfirst($k));
					$html->setvar("page", $k);

                    $lPart = array();
                    $lPart[$k] = $l[$k];
                    $html->setvar('words_count_section', wordsCountInLanguage($lPart));

                    $fieldIndex = 1;

                    ksort($v);
                    $keyLetter = array();
					foreach ($v as $k2 => $v2)
					{
						$field_name = $k2=="submit" ? $k2."_js_patch" : $k2;

                        $letter = substr(mb_strtolower($field_name, 'UTF-8'), 0, 1);
                        $keyLetter[] = $letter;
                        $html->setvar("letter", $letter);
                        $html->setvar("value", $v2);

                        if ($isAjaxLoadWords) {
                            $html->setvar("field", $field_name);
                            $html->setvar("field_title", str_replace("_", " ", ucfirst($k2)));
                            //150 -> 90 did not own http://sitesman.com/s/1014-2017-12-16_19-01-18.png
                            if (strlen($v2) > 90 || strpos($v2, "\n") !== false)
                            {
                                $html->setvar('class_field', 'textarea');
                            } else {
                                $html->setvar('class_field', '');
                            }

                            $html->setvar('field_index', $fieldIndex);

                            if($lang!='default' && !isset($currentLang[$k][$k2]) && !$this->isWordInTranslateStopList($k, $k2)){
                                $html->setvar('delete_style', 'visibility: hidden;');
                                $html->setvar('not_translated_class', 'not_translated');
                            } else {
                                $html->setvar('delete_style', '');
                                $html->setvar('not_translated_class', '');
                            }

                            $fieldIndex++;
                            $html->parse("field", true);
                        }
					}
                    $keyLetter = array_unique($keyLetter);
                    $alphabet = array_merge(range(0, 9), range('A','Z'), array('ALL'));
                    foreach($alphabet as $l) {
                        $html->setvar("abc", $l);
                        $key = mb_strtolower($l, 'UTF-8');
                        if (in_array($key, $keyLetter) || $key == 'all') {
                            if ($key == 'all') {
                               $html->setvar("class_separator", 'separator_lf');
                               $html->parse("li_all", false);
                            }
                            $html->setvar("id_abc", $key);
                            $html->setvar("title_abc", $l);
                            $html->setblockvar("no_value", '');
                            $html->parse("yes_value", false);
                        } else {
                            $html->setblockvar("yes_value", '');
                            $html->parse("no_value", false);
                        }
                        if ($key == 8) {
                            $html->setvar("class_separator", 'separator_rg');
                        } elseif ($key != 'all') {
                            $html->setvar("class_separator", '');
                        }
                        $html->parse("alphabet", true);
                    }

					$html->setvar("lang_page", $k);
                    $html->setvar('lang_page_id', str_replace('.', '_', $k));
					$html->setvar("lang_page_title", $ltitle);
					$html->parse("lang_on", false);
					$html->setblockvar("lang_off", "");
				}
				else
				{
					$html->setvar("lang_page", $k);
                    $html->setvar('lang_page_id', str_replace('.', '_', $k));
					$html->setvar("lang_page_title", $ltitle);
					$html->parse("lang_off", false);
					$html->setblockvar("lang_on", "");
				}
				$html->parse("lang", true);
			}
		} else {
			//$this->message_lang = "Incorrect language files.<br />";
            redirect("language.php");
		}

		$html->setvar("message_lang", $this->message_lang);
		unset($l);
		$l = $l_glob;

        $addCount = 5;
        for($addIndex = 1; $addIndex <= $addCount; $addIndex++) {
            $html->setvar('add_index', $addIndex);
            $html->parse('add');
        }

        if ($isAjaxLoadWords) {
            $html->parse('language_words', false);
            $html->parse('language_words_js', false);
        } else {
            $html->parse('language_words_loader', false);
        }

		parent::parseBlock($html);
	}
}

$page = new CAdminLangEdit("", $g['tmpl']['dir_tmpl_administration'] . "language_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>