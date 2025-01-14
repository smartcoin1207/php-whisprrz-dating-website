<?php

class LanguageTranslator
{
    static $error = false;
    static $errorMessage = '';
    static $debug = false;

    // languages list
    static $languagesList = array(
        'af' => 'Afrikaans',
        'sq' => 'Albanian',
        'am' => 'Amharic',
        'ar' => 'Arabic',
        'hy' => 'Armenian',
        'az' => 'Azerbaijani',
        'eu' => 'Basque',
        'be' => 'Belarusian',
        'bn' => 'Bengali',
        'bs' => 'Bosnian',
        'bg' => 'Bulgarian',
        'ca' => 'Catalan',
        'ceb' => 'Cebuano',
        'ny' => 'Chichewa',
        'zh-CN' => 'Chinese (Simplified)',
        'zh-TW' => 'Chinese (Traditional)',
        'co' => 'Corsican',
        'hr' => 'Croatian',
        'cs' => 'Czech',
        'da' => 'Danish',
        'nl' => 'Dutch',
        'en' => 'English',
        'eo' => 'Esperanto',
        'et' => 'Estonian',
        'tl' => 'Filipino',
        'fi' => 'Finnish',
        'fr' => 'French',
        'fy' => 'Frisian',
        'gl' => 'Galician',
        'ka' => 'Georgian',
        'de' => 'German',
        'el' => 'Greek',
        'gu' => 'Gujarati',
        'ht' => 'Haitian Creole',
        'ha' => 'Hausa',
        'haw' => 'Hawaiian',
        'iw' => 'Hebrew',
        'hi' => 'Hindi',
        'hmn' => 'Hmong',
        'hu' => 'Hungarian',
        'is' => 'Icelandic',
        'ig' => 'Igbo',
        'id' => 'Indonesian',
        'ga' => 'Irish',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'jw' => 'Javanese',
        'kn' => 'Kannada',
        'kk' => 'Kazakh',
        'km' => 'Khmer',
        'ko' => 'Korean',
        'ku' => 'Kurdish (Kurmanji)',
        'ky' => 'Kyrgyz',
        'lo' => 'Lao',
        'la' => 'Latin',
        'lv' => 'Latvian',
        'lt' => 'Lithuanian',
        'lb' => 'Luxembourgish',
        'mk' => 'Macedonian',
        'mg' => 'Malagasy',
        'ms' => 'Malay',
        'ml' => 'Malayalam',
        'mt' => 'Maltese',
        'mi' => 'Maori',
        'mr' => 'Marathi',
        'mn' => 'Mongolian',
        'my' => 'Myanmar (Burmese)',
        'ne' => 'Nepali',
        'no' => 'Norwegian',
        'ps' => 'Pashto',
        'fa' => 'Persian',
        'pl' => 'Polish',
        'pt' => 'Portuguese',
        'pa' => 'Punjabi',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'sm' => 'Samoan',
        'gd' => 'Scots Gaelic',
        'sr' => 'Serbian',
        'st' => 'Sesotho',
        'sn' => 'Shona',
        'sd' => 'Sindhi',
        'si' => 'Sinhala',
        'sk' => 'Slovak',
        'sl' => 'Slovenian',
        'so' => 'Somali',
        'es' => 'Spanish',
        'su' => 'Sundanese',
        'sw' => 'Swahili',
        'sv' => 'Swedish',
        'tg' => 'Tajik',
        'ta' => 'Tamil',
        'te' => 'Telugu',
        'th' => 'Thai',
        'tr' => 'Turkish',
        'uk' => 'Ukrainian',
        'ur' => 'Urdu',
        'uz' => 'Uzbek',
        'vi' => 'Vietnamese',
        'cy' => 'Welsh',
        'xh' => 'Xhosa',
        'yi' => 'Yiddish',
        'yo' => 'Yoruba',
        'zu' => 'Zulu',
    );

    static $translateStopList = array(

        'all' => array(
            'menu_items' => true,
            'language_code' => true,
            'locale_windows' => true,
            'charset' => true,
            'profile_orientations_allow_lowercase' => true,
            'profile_orientations_allow_ucfirst' => true,
            'profile_looking_for_orientations_allow_ucfirst' => true,
            'profile_sexuality_allow_lowercase' => true,
            'profile_sexuality_allow_ucfirst' => true,

            'wall_plus' => true,
            'rating_empty' => true,
            'currency_sign' => true,
            'profile_looking_for_orientations_allow_lowercase' => true,
            'profile_short_info_here_to_delimiter' => true,
            'profile_short_info_age_delimiter' => true,

            'urban_mobile' => true,
            'impact_mobile' => true,

            'date_format_super_powers' => true,

            'afrikaans' => true,
            'arabic' => true,
            'armenian' => true,
            'belarusian' => true,
            'bulgarian' => true,
            'chinese' => true,
            'croatian' => true,
            'czech' => true,
            'danish' => true,
            'language_default' => true,
            'dutch' => true,
            'estonian' => true,
            'filipino' => true,
            'finnish' => true,
            'french' => true,
            'georgian' => true,
            'german' => true,
            'greek' => true,
            'hausa' => true,
            'hebrew' => true,
            'hindi' => true,
            'hungarian' => true,
            'icelandic' => true,
            'igbo' => true,
            'indonesian' => true,
            'italian' => true,
            'japanese' => true,
            'kazakh' => true,
            'korean' => true,
            'latvian' => true,
            'lithuanian' => true,
            'macedonian' => true,
            'malay' => true,
            'maltese' => true,
            'norwegian' => true,
            'persian' => true,
            'polish' => true,
            'portuguese' => true,
            'romanian' => true,
            'russian' => true,
            'serbian' => true,
            'slovak' => true,
            'slovenian' => true,
            'spanish' => true,
            'swahili' => true,
            'swedish' => true,
            'thai' => true,
            'turkish' => true,
            'ukrainian' => true,
            'vietnamese' => true,
            'xhosa' => true,
            'yoruba' => true,
            'zulu' => true,
        ),

        'join.php' => array(
            'placeholder_day' => true,
            'placeholder_month' => true,
            'placeholder_year' => true,
        ),

        'blogs_write.php' => array(
            'blogs_write_text_symbol' => true,
        ),
    );

    static function isWordInTranslateStopList($languageSection, $languageItemKey)
    {
        $isWordInTranslateStopList = false;
        if(isset(self::$translateStopList[$languageSection][$languageItemKey])) {
            $isWordInTranslateStopList = true;
        }

        return $isWordInTranslateStopList;
    }

    static function translate($text, $fromLang, $toLang)
    {
        self::$error = false;

        // test
        if(false) {
            foreach($text as $key => $value) {
                $text[$key] = 'TRANSLATED - ' . $value;
            }
            return $text;
        }

        $translatorEngine = 'google';

        if ($translatorEngine == 'google') {
            $apiKey = trim(Common::getOption('autotranslator_key'));
            if ($apiKey != '') {

                $url = 'https://www.googleapis.com/language/translate/v2?key='
                        . $apiKey . '&source=' . $fromLang . '&target=' . $toLang;

                $params = '';

                foreach($text as $textItem) {
                    $params .= '&q=' . urlencode($textItem);
                }

                if(false) {
                    echo $url . "\n";
                    var_dump_pre($params);
                    die();
                }

                $result = urlGetContents($url, 'post', $params, 3600, false);
/*
$result = '{
  "error": {
    "code": 403,
    "message": "Daily Limit Exceeded",
    "errors": [
      {
        "message": "Daily Limit Exceeded",
        "domain": "usageLimits",
        "reason": "dailyLimitExceeded"
      }
    ]
  }
}';
 */

                if($result) {
                    $result = json_decode($result, true);

                    //var_dump_pre($result);
                    //die();

                    if(isset($result['error'])) {
                        self::$error = true;
                        if(isset($result['error']['message'])) {
                            self::$errorMessage = $result['error']['message'];
                        }
                    }

                    if(isset($result['data']['translations'])) {
                        foreach($result['data']['translations'] as $translationKey => $translation) {

                            // detect case
                            $firstLetter = mb_substr($text[$translationKey], 0, 1);
                            $lowerCaseLetter = mb_strtolower($firstLetter, 'utf-8');
                            $isFirstLetterUpper = false;
                            if($firstLetter != $lowerCaseLetter) {
                                $isFirstLetterUpper = true;
                            }

                            $isSrcTextFirstLetterUpperCase = self::isFirstLetterUpperCase($text[$translationKey]);
                            $isTranslationTextFirstLetterUpperCase = self::isFirstLetterUpperCase($translation['translatedText']);
                            if($isSrcTextFirstLetterUpperCase && !$isTranslationTextFirstLetterUpperCase) {
                                $translation['translatedText'] = mb_strtoupper(mb_substr($translation['translatedText'], 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($translation['translatedText'], 1, mb_strlen($translation['translatedText']), 'UTF-8');
                            }

                            $text[$translationKey] = $translation['translatedText'];
                        }
                    }
                }

            }
        }

        return $text;
    }

    static function autoTranslateSiteLanguageByAdministrator($text, $fromLang, $toLang)
    {
        $isSingleItem = false;

        if(!is_array($text)) {
            $text = array($text);
            $isSingleItem = true;
        }

        $textSrc = $text;

        // $pattern = '#{(.*)}#Uis';
        // $replacement = '<span class="notranslate">{\1}</span>';

        $pattern = '#(?<!href=("|\')){(.*)}#Uis';
        $replacement = '<span class="notranslate">{\2}</span>';

        // url like href=link
        $patternUrlWithoutBorders = '# href=([^\'"].*)(\s|>)#Uis';
        $replacementUrl = ' href="\1"\2';

        $patternUrlWithNotranslate = '#href=("|\')(.*)(?<!notranslate)(\1)(?!notranslate)#Uis';

        $patternNotranslate = '#<span class="notranslate">(.*)</span>#Uis';
        $replacementNotranslate = '\1';

        $patternUrlContent = '#<a (.*)>#Uis';

        foreach($text as $key => $value) {

            $urls = array();

            if(preg_match_all($patternUrlContent, $value, $matches)) {
                foreach($matches[1] as $url) {
                    $urlKey = md5($url);
                    $urls[$urlKey] = $url;
                    $value = str_replace($url, $urlKey, $value);
                }
            }

            if(preg_match_all($pattern, $value, $matches)) {
                foreach($matches[0] as $tag) {
                    $search = array(
                        $tag . '\'s ',
                        $tag . '&#39;s ',
                    );
                    $value = str_ireplace($search, $tag . ' ', $value);
                }
            }

            // correct urls like href=link
            // $value = preg_replace($patternUrlWithoutBorders, $replacementUrl, $value);

            $value = preg_replace($pattern, $replacement, $value);

            /*
            if(preg_match_all($patternUrlWithNotranslate, $value, $matches)) {
                foreach($matches[2] as $url) {
                    $replace = preg_replace($patternNotranslate, $replacementNotranslate, $url);
                    $value = str_replace($url, $replace, $value);
                }
            }
            */

            if($urls) {
                foreach($urls as $urlKey => $url) {
                    $value = str_replace($urlKey, $url, $value);
                }
            }

            $text[$key] = $value;
        }

        $translate = self::translate($text, $fromLang, $toLang);

        foreach($translate as $key => $value) {
            $translate[$key] = preg_replace($patternNotranslate, $replacementNotranslate, $value);
            $translate[$key] = self::autoTranslateCleanSpacesNearTags($textSrc[$key], $translate[$key]);
        }

        $result = $isSingleItem ? $translate[0] : $translate;

        return $result;
    }

    static function autoTranslateSiteLanguageByAdministratorTest($text, $fromLang, $toLang)
    {
        $isSingleItem = false;

        if(!is_array($text)) {
            $text = array($text);
            $isSingleItem = true;
        }

        $replacement = '<span class="notranslate">/space/</span>';
        $pattern = '#[\s*]#Uis';
        foreach($text as $key => $value) {
            $text[$key] = preg_replace($pattern, $replacement, $value);
        }

        $pattern = '#{(.*)}#Uis';
        $replacement = '<span class="notranslate">{\1}</span>';

        foreach($text as $key => $value) {
            $text[$key] = preg_replace($pattern, $replacement, $value);
        }

        $translate = self::translate($text, $fromLang, $toLang);

        $pattern = '#<span class="notranslate">(.*)</span>#Uis';
        $replacement = '\1';

        foreach($translate as $key => $value) {
            $translate[$key] = str_replace('<span class="notranslate">/space/</span>', '/space/', $value);
            $translate[$key] = preg_replace($pattern, $replacement, $value);
            //$translate[$key] = self::autoTranslateCleanSpacesNearTags($text[$key], $translate[$key]);
            $translate[$key] = str_replace(" ", "", $translate[$key]);
            $translate[$key] = str_replace("/space/", " ", $translate[$key]);
        }

        $result = $isSingleItem ? $translate[0] : $translate;

        return $result;
    }

    static function autoTranslateCleanSpacesNearTags($textSource, $textTranslated)
    {
        if(strtolower($textSource) == 'id') {
            return $textSource;
        }

        $pattern = '#({(.+)})#uUis';
        $cleanTextSource = preg_replace($pattern, '', $textSource);

        $pattern = '#<[^>]*>#uUis';
        $cleanTextSource = preg_replace($pattern, '', $cleanTextSource);

        $pattern = '/((.*)(\w+)(.*))/uUis';
        $isTextSymbolExists = preg_match($pattern, $cleanTextSource, $matches);

        self::debug("cleanTextSource |$cleanTextSource|");

        //if(!$isTextSymbolExists && !trim($cleanTextSource) == '&') {
        if(!$isTextSymbolExists) {
            return $textSource;
        }

        $textTranslated = str_replace(' <br>', '<br>', $textTranslated);

        self::debug("PREPARED textTranslated |$textTranslated|");

        $pattern = '#(([\s*]|^){(.*)}([\s*]|$))#uUis';
        preg_match_all($pattern, $textTranslated, $matches);
/*
        if (isset($matches) && Common::isValidArray($matches[0])) {

            $isSingleItem = count($matches[0]) === 1;

            foreach ($matches[0] as $match) {

                if (strpos($textSource, $match) === false) {

                    $matchNoSpaceLeft = ltrim($match);
                    if (strpos($textSource, $matchNoSpaceLeft) !== false) {
                        if(strpos($textSource, $matchNoSpaceLeft) !== 0) {
                            $textTranslated = str_replace($match, $matchNoSpaceLeft, $textTranslated);
                        }
                    }

                    $matchNoSpaceRight = rtrim($match);
                    $strposInTextSource = strpos($textSource, $matchNoSpaceRight);
                    if ($strposInTextSource !== false) {
                        if($isSingleItem) {
                            if(substr($textSource, -strlen($matchNoSpaceRight)) !== $matchNoSpaceRight) {
                            }
                        } else {
                            $textTranslated = str_replace($match, $matchNoSpaceRight, $textTranslated);
                        }
                    }

                    $matchNoSpaces = trim($match);
                    if (strpos($textSource, $matchNoSpaces) !== false) {
                        if(strpos($textSource, $matchNoSpaces) !== 0) {
                            $textTranslated = str_replace($match, $matchNoSpaces, $textTranslated);
                        }
                    }
                }
            }
        }
*/
        self::debug("textTranslated |$textTranslated|");
        self::debug("textSource |$textSource|");

        $pattern = '#({(.*)})#uUis';
        preg_match_all($pattern, $textSource, $matches);

        self::debug('Matches <pre>' . print_r($matches, true) . '</pre>');

        if (isset($matches) && Common::isValidArray($matches[0])) {

            foreach ($matches[0] as $match) {

                self::debug('match');
                self::debug($match, true);

                //$isSingleItem = count($matches[0]) === 1;
                $isSingleItem = (substr_count($textSource, $match) === 1);

                $matchBothSpaces = ' ' . $match . ' ';

                $matchLeftSpace = ' ' . $match;

                $matchRightSpace = $match . ' ';

                if (strpos($textSource, $matchBothSpaces) !== false) {

                    self::debug('matchBothSpaces');

                    if (strpos($textTranslated, $matchBothSpaces) === false) {
                        $textTranslated = str_replace(' ' . $match, $matchBothSpaces, $textTranslated);

                        if (strpos($textTranslated, $matchBothSpaces) === false) {
                            $textTranslated = str_replace($match . ' ', $matchBothSpaces, $textTranslated);
                        }

                        if (strpos($textTranslated, $matchBothSpaces) === false) {
                            $textTranslated = str_replace($match, $matchBothSpaces, $textTranslated);
                        }
                    }
                } elseif (strpos($textSource, $matchLeftSpace) !== false) {

                    self::debug("matchLeftSpace |$matchLeftSpace|$textTranslated|");

                    if (strpos($textTranslated, $matchLeftSpace) === false) {
                        self::debug('IN matchLeftSpace');
                        if (strpos($textTranslated, $matchRightSpace) !== false) {
                            // if tag at the end of original text it can have a space at the right after translation
                            if(!$isSingleItem || substr($textSource, -strlen($match)) !== $match) {
                                $textTranslated = str_replace($matchRightSpace, $matchLeftSpace, $textTranslated);
                                self::debug('Not Single Item OR End of Phrase');
                            } elseif ($isSingleItem && substr($textSource, -strlen($match)) === $match) {
                                self::debug('SingleItem and End of source phrase');
                            }
                        } else {
                            $textTranslated = str_replace($match, $matchLeftSpace, $textTranslated);
                        }
                    } else {
                        // if tag at the center of phrase
                        if ($isSingleItem && substr($textSource, -strlen($match)) === $match) {
                            self::debug('HERE NOW!!!');
                        }
                    }

                } elseif (strpos($textSource, $matchRightSpace) !== false) {
                    self::debug('matchRightSpace>');

                    if (strpos($textTranslated, $matchRightSpace) === false) {
                        $textTranslated = str_replace($match, $matchRightSpace, $textTranslated);
                    }

                }

                // if tag at the center of phrase
                if ($isSingleItem && substr($textSource, -strlen($match)) === $match && substr($textTranslated, -strlen($match)) !== $match) {
                    $textTranslated = str_replace($match, $matchRightSpace, $textTranslated);
                }
                if ($isSingleItem && substr($textSource, 0, strlen($match)) === $match && substr($textTranslated, 0, strlen($match)) !== $match) {
                    $textTranslated = str_replace($match, $matchLeftSpace, $textTranslated);
                }
            }
        }

        if(strpos($textSource, ' ') === 0 && strpos($textTranslated, ' ') !== 0) {
            $textTranslated = ' ' . $textTranslated;
        }

        if(substr($textSource, -1) == ' ' && substr($textTranslated, -1) != ' ') {
            $textTranslated = $textTranslated . ' ';
        }

        $pattern = '#((\s*|^)\<(.+)\>(.+)\</(.+)\>([\s*]|$))#uUis';
        preg_match_all($pattern, $textTranslated, $matches);

        if (isset($matches) && Common::isValidArray($matches[0])) {

            foreach ($matches[0] as $match) {

                self::debug('match > ' . $match);

                if (strpos($textSource, $match) === false) {

                    self::debug('check 1');
                    self::debug($match, true);

                    $matchNoSpaceLeft = ltrim($match);
                    if (strpos($textSource, $matchNoSpaceLeft) !== false) {
                        self::debug('matchNoSpaceLeft');
                        $textTranslated = str_replace($match, $matchNoSpaceLeft, $textTranslated);
                    }

                    $matchNoSpaceRight = rtrim($match);
                    if (strpos($textSource, $matchNoSpaceRight) !== false) {
                        self::debug('matchNoSpaceRight');
                        $textTranslated = str_replace($match, $matchNoSpaceRight, $textTranslated);
                    }

                    $matchNoSpaces = trim($match);
                    if (strpos($textSource, $matchNoSpaces) !== false) {
                        self::debug('matchNoSpaces');
                        $textTranslated = str_replace($match, $matchNoSpaces, $textTranslated);
                    }
                } else {

                    $matchBothSpaces = ' ' . $match . ' ';

                    $matchLeftSpace = ' ' . $match;

                    $matchRightSpace = $match . ' ';

                    if (strpos($textSource, $matchBothSpaces) !== false) {

                        if (strpos($textTranslated, $matchBothSpaces) === false) {
                            $textTranslated = str_replace(' ' . $match, $matchBothSpaces, $textTranslated);

                            if (strpos($textTranslated, $matchBothSpaces) === false) {
                                $textTranslated = str_replace($match . ' ', $matchBothSpaces, $textTranslated);
                            }

                            if (strpos($textTranslated, $matchBothSpaces) === false) {
                                $textTranslated = str_replace($match, $matchBothSpaces, $textTranslated);
                            }
                        }
                    } elseif (strpos($textSource, $matchLeftSpace) !== false) {

                        if (strpos($textTranslated, $matchLeftSpace) === false) {
                            if (strpos($textTranslated, $matchRightSpace) !== false) {
                                $textTranslated = str_replace($matchRightSpace, $matchLeftSpace, $textTranslated);
                            } else {
                                $textTranslated = str_replace($match, $matchLeftSpace, $textTranslated);
                            }
                        }

                    } elseif (strpos($textSource, $matchRightSpace) !== false) {

                        if (strpos($textTranslated, $matchRightSpace) === false) {
                            $textTranslated = str_replace($match, $matchRightSpace, $textTranslated);
                        }

                    }

                }

            }
        }

        $pattern = '~((\d+) &#39;(\d+) &quot;[\s]*)~uis';
        preg_match_all($pattern, $textTranslated, $matches);

        if (isset($matches) && Common::isValidArray($matches[0])) {

            foreach ($matches[0] as $key => $match) {

                if (strpos($textSource, $match) === false) {

                    $matchFormatted = "{$matches[2][$key]}' {$matches[3][$key]}\"";

                    if (strpos($textSource, $matchFormatted . ' ') !== false) {
                        $textTranslated = str_replace($match, $matchFormatted . ' ', $textTranslated);
                    }

                    $matchFormatted = "{$matches[2][$key]}&#39; {$matches[3][$key]}\"";

                    if (strpos($textSource, $matchFormatted . ' ') !== false) {
                        $textTranslated = str_replace($match, $matchFormatted . ' ', $textTranslated);
                    }

                }
            }
        }

        $pattern = '~([\s]*\.\.\.)~uis';
        preg_match_all($pattern, $textTranslated, $matches);

        if (isset($matches) && Common::isValidArray($matches[0])) {

            foreach ($matches[0] as $match) {

                if (strpos($textSource, $match) === false) {

                    $matchNoSpaceLeft = ltrim($match);
                    if (strpos($textSource, $matchNoSpaceLeft) !== false) {
                        $textTranslated = str_replace($match, $matchNoSpaceLeft, $textTranslated);
                    }

                }

            }
        }

        $lastEntryItems = array('.', ',', '!', ')', '”', ':');

        $search = array();
        $replace = array();

        foreach($lastEntryItems as $lastEntryItem) {
            $search[] = '} ' . $lastEntryItem;
            $replace[] = '}' . $lastEntryItem;
        }

        // usually it is incorrect space at the start of tag like "Items ({count})"
        $search[] = '( {';
        $replace[] = '({';

        $textTranslated = str_replace($search, $replace, $textTranslated);

        if(substr($textTranslated, -1) == ' ' && substr($textSource, -1) != ' ') {
            $textTranslated = rtrim($textTranslated);
        }

        if(substr($textTranslated, 0, 1) == ' ' && substr($textSource, 0, 1) != ' ') {
            $textTranslated = ltrim($textTranslated);
        }

        $textTranslated = str_replace(' </strong>', '</strong>', $textTranslated);
        $textTranslated = str_replace(' .', '.', $textTranslated);
        $textTranslated = str_replace(' ,', ',', $textTranslated);
        $textTranslated = str_replace(',.', ', .', $textTranslated);

        $textTranslated = str_replace('.jpg', ' .jpg', $textTranslated);
        $textTranslated = str_replace('.gif', ' .gif', $textTranslated);
        $textTranslated = str_replace('.png', ' .png', $textTranslated);

        if(strpos($textTranslated, '“%s„') === false) {
            $textTranslated = str_replace('„ ', '„', $textTranslated);
        }

        if(strpos($textTranslated, ':-') === false) {
            $textTranslated = str_replace(' :', ':', $textTranslated);
        }

        $pattern = '#\s+#u';
        $textTranslated = preg_replace($pattern, ' ', $textTranslated);

        $pattern = '#^% 1#u';
        $textTranslated = preg_replace($pattern, '%1', $textTranslated);

        $pattern = '#% (\d)#u';
        $textTranslated = preg_replace($pattern, ' %\1', $textTranslated);

        return $textTranslated;
    }

    static function getLanguageLocale($l = false, $chineseLastPartUppercase = true)
    {
        $languageCode = l('language_code', $l);

        $localeShortCode = Common::getLocaleShortCode($languageCode);

        if ($localeShortCode == 'zh') {
            $localeShortCode = $languageCode;
            if ($chineseLastPartUppercase) {
                $languageCodeParts = explode('-', $languageCode);
                if (isset($languageCodeParts[1])) {
                    $languageCodeParts[1] = strtoupper($languageCodeParts[1]);
                    $localeShortCode = implode('-', $languageCodeParts);
                }
            }
        }

        return $localeShortCode;
    }

    static function removeDuplicates($language)
    {
        $cleanLanguage = array();

        //$symbolsCount = 0;

        foreach($language as $lSection => $lSectionItems) {
            foreach($lSectionItems as $lSectionItemKey => $lSectionItemValue) {

                //$hash = md5($lSectionItemValue);

                $hash = $lSectionItemValue;

                //$symbolsCount += strlen($lItemValue);

                if(isset($cleanLanguage[$hash])) {
                    $cleanLanguage[$hash]['count']++;
                } else {
                    $cleanLanguage[$hash]['count'] = 0;
                    $cleanLanguage[$hash]['value'] = $lSectionItemValue;
                    //$cleanLanguage[$hash] = $lSectionItemValue;
                }

            }
        }

        /*
         *
        $duplicatesCount = 0;
        $duplicatesSymbolsCount = 0;

        foreach($duplicates as $key => $item) {
            if($item['count'] > 0) {
                echo $item['count'] . ' > ' . $item['value'] . '<br>';
                $duplicatesCount += $item['count'];
                $duplicatesSymbolsCount += $item['count'] * strlen($item['value']);
            }
        }

        echo "DUPLICATES COUNT > $duplicatesCount<br>";
        echo "DUPLICATES SYMBOLS COUNT > $duplicatesSymbolsCount<br>";
        echo "SYMBOLS COUNT > $symbolsCount<br>";
         */

        return $cleanLanguage;
    }

    static function getLanguageSettings($language)
    {
        $settings = Common::getOption('settings_of_languages', 'language_translator');

        if ($settings !== null) {
            $settings = json_decode($settings, true);
        }

        if (!is_array($settings)) {
            $settings = array();
        }

        return isset($settings[$language]) ? $settings[$language] : false;
    }

    static function isFirstLetterUpperCase($text)
    {

        $firstLetter = mb_substr($text, 0, 1, 'utf-8');
        $lowerCaseLetter = mb_strtolower($firstLetter, 'utf-8');

        $isFirstLetterUpperCase = true;
        if($firstLetter == $lowerCaseLetter) {
            $isFirstLetterUpperCase = false;
        }

        return $isFirstLetterUpperCase;
    }

    static function saveLanguageTranslatorSettings($language, $translateFrom, $translateTo)
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

    static function parseModule(&$html, $langParam = 'lang')
    {
        $lang = get_param($langParam, 'default');

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
                'translate_just_this_mail' => l('translate_just_this_mail'),
                'translate_all_mails' => l('translate_all_mails'),
            );

            $html->setvar('translate_type_options', h_options($translateTypeOptions, 'first'));

            $html->parse('autotranslator_header');
        }
    }

    static function translateBatchAutomailSettings()
    {
        $translateType = get_param('translate_type');
        $translateAll = get_param('translate_all');

        $dataSrc = $data = json_decode(get_param('data'), true);
        $fromLang = get_param('translate_from');
        $toLang = get_param('translate_to');

        $lang = get_param('lang');

        self::saveLanguageTranslatorSettings($lang, $fromLang, $toLang);

        $currentLanguage = array();

        $batchSessionId = intval(get_param('batchSessionId', 0));
        $batchSessionIndex = intval(get_param('batchSessionIndex', 0));

        $batchData = array();

        // нужен список всех языков сайта и вариантов перевода(причем смотреть нужно на сохраненные настройки перевода!!!)
        // нужна базовая фраза - если уже есть в переводе, то она. Иначе брать English?
        // или проще всегда English

        $langs = array_keys(Common::listLangs('main'));

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
    }

    static public function debug($message, $showDelimiters = false)
    {
        if(self::$debug) {
            if($showDelimiters) {
                $message = '|' . $message . '|';
            }
            echo "$message<br>";
        }
    }

}
