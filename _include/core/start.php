<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

// Use only before variable definition
if (isset($_POST)) foreach($_POST AS $key => $value) unset(${$key});
if (isset($_GET)) foreach($_GET AS $key => $value) unset(${$key});
if (isset($_REQUEST)) foreach($_REQUEST AS $key => $value) unset(${$key});
if (isset($_SESSION)) foreach($_SESSION AS $key => $value) unset(${$key});
if (isset($_ENV)) foreach($_ENV AS $key => $value) unset(${$key});
if (isset($_SERVER)) foreach($_SERVER AS $key => $value) unset(${$key});

$PHP = explode('.',  phpversion());
$g['php'] = $PHP[0];
$g['php2'] = $PHP[1];
@date_default_timezone_set(@date_default_timezone_get());

error_reporting(E_ALL);
ini_set("display_errors", 1);

ini_set('gd.jpeg_ignore_warning', 1);

ini_set('opcache.validate_timestamps', 1);
ini_set('opcache.revalidate_freq', 0);

ini_set('session.gc_probability', 0);
ini_set('memory_limit', '64M');

if (substr($_SERVER['HTTP_HOST'], 0, 4) == "www.") $domain = "." . substr($_SERVER['HTTP_HOST'], 4);
else $domain = "." . $_SERVER['HTTP_HOST'];

$xhprofActive = false;
$xhprofConfig = __DIR__ . '/../config/xhprof.php';
if(file_exists($xhprofConfig)) {
    include $xhprofConfig;
    if($xhprof['active']) {
        if(!isset($xhprof['domains']) || in_array($domain, $xhprof['domains'])) {
            $xhprofActive = true;
        }
    }
}

DEFINE('DEV_PROFILING', $xhprofActive);

function getMemoryLimitInBytes()
{
    $memorySize = @ini_get('memory_limit');
    if($memorySize === '-1') {
        $memorySize = PHP_INT_MAX;
    }
    $memorySizeType = strtolower(substr($memorySize, -1));

    if($memorySizeType == 'm') {
        $memorySize = substr($memorySize, 0, -1) * 1024 * 1024;
    } elseif($memorySizeType == 'g') {
        $memorySize = substr($memorySize, 0, -1) * 1024 * 1024 * 1024;
    } elseif($memorySizeType == 'k') {
        $memorySize = substr($memorySize, 0, -1) * 1024;
    }

    return $memorySize;
}

$memorySizeMinimumForFilesCache = 67108864; // 64M
$memorySize = getMemoryLimitInBytes();
DEFINE('USE_FILES_CACHE', $memorySize >= $memorySizeMinimumForFilesCache);

DEFINE('FOOTER_CUSTOM_JS', true);
DEFINE('DEV', true);
if(DEV_PROFILING) {
    #xhprof_enable(XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
    xhprof_enable();
}

if (!isset($g['to_root'])) {
    $g['to_root'] = '';
}

$g['db']['table_prefix'] = '';//Чтоб можно было слить с city.class  плагина

$p = basename($_SERVER['SCRIPT_FILENAME']);

// Fix for IIS default main page
if($p === 'Index.php') {
    $p = 'index.php';
}

$g['path']['dir_main'] = realpath(__DIR__ . '/../../') . '/';

if(!isset($g['path']['url_main'])) {
    $g['path']['url_main'] = './';
}

$g['path']['base_url_main'] = './';
$isBaseSeoUrl = isset($_GET['base_seo_url']) && intval($_GET['base_seo_url']);//URL - vids_list/451
if ($isBaseSeoUrl) {
    $isBaseSeoUrl = intval($_GET['base_seo_url']);
    $g['path']['base_url_main'] = '../';
    if ($isBaseSeoUrl == 2) {
        $g['path']['base_url_main'] = '../../';
    }
}

function fatalScriptError()
{
    $e = error_get_last();
    if($e) {
        if($e['message'] == 'ioncube_loader_version() has been disabled for security reasons') {
            return;
        }

        if(strpos($e['message'], "Comments starting with '#' are deprecated") !== false) {
            return;
        }

        if($e['type'] === E_WARNING || $e['type'] === E_NOTICE) {
            return;
        }
        die();

        error_echo_handler($e['type'], $e['message'], $e['file'], $e['line']);
    }
}

register_shutdown_function('fatalScriptError');

include_once(__DIR__ . '/autoloader.php');

include_once(__DIR__ . '/../lib/lib.php');

include_once(__DIR__ . '/../lib/lib_html_dom.php');


$g['path']['base_url_main_head'] = Common::urlSiteSubfolders();


$nameSeoParams = get_param('name_seo');
if ($nameSeoParams && strpos($nameSeoParams, '/') === false) {
    //echo 'Name seo: ' . $nameSeoParams . '<br>';
    $requestUri = urldecode($_SERVER['REQUEST_URI']);
    //if (substr($requestUri, -1) != '/') {
    //    $requestUri = $requestUri . '/';
    //}
    $pattern = '/\/(' . preg_quote($nameSeoParams) . '[.]*)(\/|\?|$)/ui';
    //echo 'Pattern: ' . $pattern . '<br>';
    if (preg_match($pattern, $requestUri, $matches)) {
        $requestUriNameSeo = $matches[1];
        if ($requestUriNameSeo != $nameSeoParams) {
            $_GET['name_seo'] = $requestUriNameSeo;
            //var_dump_pre($requestUriNameSeo);
        }

    }
    //var_dump_pre(preg_match($pattern, $requestUri, $matches), true);
}

// redirect for incorrect "profile" urls like "folder/file" to 404 page
if($p == 'search_results.php') {

    $nameSeo = get_param('name_seo');
    if($nameSeo && strpos($nameSeo, '/') !== false) {
        // pageNotFound();
    }
}

$isMobileCity = get_param('view') == 'mobile';
if ($isMobileCity) {
    $sitePart = 'mobile';
    $g['mobile'] = true;
}
$sitePartRuntime = get_param('site_part_runtime', '');
if (!isset($sitePart) && $sitePartRuntime && $sitePartRuntime != 'main') {
    if($sitePartRuntime == 'main' || $sitePartRuntime == 'mobile' || $sitePartRuntime == 'administration' || $sitePartRuntime == 'partner') {
        $sitePart = $sitePartRuntime;
    }
}

$sitePartParam = '';
if(isset($sitePart)) {
    $sitePartParam = '_' . $sitePart;
} else {
    $sitePart = 'main';
}

if (!isset($g['error_output'])) {
    $g['error_output'] = 'browser';
}

error_enable($g['error_output']);

$g['multisite'] = '';
$multisite = __DIR__ . '/multisite.php';
if(file_exists($multisite)) {
    include_once($multisite);
}
$dirFiles = "_files{$g['multisite']}/";
if (!isset($g['path']['dir_files']) or $g['path']['dir_files'] == '') {
    $g['path']['dir_files'] = $g['path']['dir_main'] . $dirFiles;
}
$g['dir_files'] = $dirFiles;
$g['path']['dir_logs'] = $g['path']['dir_files'] . 'logs/';
$g['path']['dir_tmpl'] = $g['path']['dir_main'] . '_frameworks/';
$g['path']['dir_lang'] = $g['path']['dir_main'] . '_lang' . $g['multisite'] . '/';

if($g['multisite']) {
   $g['path']['dir_lang'] = $g['path']['dir_files'] . '_lang/';
}

$g['path']['dir_include'] = $g['path']['dir_main'] . '_include/';

$g['path']['url_tmpl'] = $g['path']['url_main'] . '_frameworks/';
$g['tmpl']['url_tmpl_common_apps'] = "{$g['path']['url_tmpl']}common/apps/";
$g['path']['url_files'] = $g['path']['url_main'] . $dirFiles;
$g['path']['url_files_root'] = $g['to_root'] . $dirFiles;
$g['path']['url_files_city'] = '../../../' . $dirFiles;
//$g['path']['url_files_city'] = $dirFiles;


$g['path']['url_city_absolute'] = '_server/city_js/';
$g['path']['url_city'] = "{$g['to_root']}{$g['path']['url_city_absolute']}";

include(__DIR__ . '/x.php');
$g['path']['url_main_mobile'] = $g['path']['url_main'] . MOBILE_VERSION_DIR . '/';

if ( !file_exists(__DIR__ . '/../config/db.php') || !empty($_GET['step'])) {
    if(file_exists(__DIR__ . '/../../_install/install.php')) {
        require_once(__DIR__ . '/../../_install/install.php');
        exit;
    }
}

if(file_exists(__DIR__ . '/../../_install')) {
    header('location:?step=5');
    exit;
}

include_once($g['path']['dir_main'] . '_include/lib/session.php');

if (extension_loaded('mysqli')) {
    if(!defined('MYSQL_ASSOC')) {
        define('MYSQL_ASSOC', MYSQLI_ASSOC);
    }
    if(!defined('MYSQL_BOTH')) {
        define('MYSQL_BOTH', MYSQLI_BOTH);
    }
    include_once($g['path']['dir_main'] . '_include/lib/db_mysqli.php');
} else {
    include_once($g['path']['dir_main'] . '_include/lib/db_mysql.php');
}

$dbConfig = 'db.php';
$g['db_local'] = '';

if(IS_DEMO) {
    include_once __DIR__ . '/demo.php';
    Demo::init();
}

include_once($g['path']['dir_main'] . "_include/config/{$dbConfig}");

DB::connect();

PWA::setModePwa();

//if(USE_FILES_CACHE) {
//    $cachePageConfigFile = 'config_all';
//    $g['cache_config_all'] = unserialize(cache($cachePageConfigFile, 30));
//    Config::allCache();
//}

Config::all();

$g['options']['gps_enabled'] = 'N';

if($g['main']['timezone'] === ' ') {
    Config::update('main', 'timezone', '');
}

if (function_exists('date_default_timezone_set')) {
    //$gmtOffset = Common::getOption('timezone', 'main') * 3600;
    //$dst = Common::getOption('daylight_saving_time', 'main') == 'Y' ? 1 : 0;
    //$timezoneName = timezone_name_from_abbr('', $gmtOffset, $dst);
    /*$timezoneName = Common::getOption('timezone', 'main');
    if (!empty($timezoneName)) {
        @date_default_timezone_set($timezoneName);
        $sql = 'SET time_zone = "' . date('P') . '"';
        DB::execute($sql);
    }*/
    TimeZone::setTimeZone();
}

$g['main']['site_part'] = $sitePart;

if($sitePart == 'administration' || $sitePart == 'partner') {
    $langFilePath = $g['path']['dir_lang'] . 'main/' . $g['lang_value']['main'] . '/language.php';
    if(file_exists($langFilePath)) {
        include_once($langFilePath);
    }
}

// main default language for mobile version
if($sitePart == 'mobile') {
    include_once($g['path']['dir_lang'] . 'main/default/language.php');
}
include_once($g['path']['dir_lang'] . $sitePart . '/default/language.php');

// check to_include function . .. /
$setLanguageParam = 'set_language' . $sitePartParam;

$setLanguageParamValue = get_param($setLanguageParam, '');
if ($setLanguageParamValue != '') {
    $setLanguage = to_include($setLanguageParamValue);
	set_cookie($setLanguageParam, $setLanguage);
	set_session($setLanguageParam, $setLanguage);
}

$setLanguageParamValueRuntime = get_param($setLanguageParam . '_runtime');
if ($setLanguageParamValueRuntime) {
    $setLanguage = Common::sanitizeFilename($setLanguageParamValueRuntime);
}

$setLanguageCookieValue = isset($setLanguage) ? $setLanguage : to_include(get_cookie($setLanguageParam));
$setLanguageCookieFile = "{$g['path']['dir_lang']}{$sitePart}/{$setLanguageCookieValue}/language.php";

$setLanguageSessionValue = isset($setLanguage) ? $setLanguage : to_include(get_session($setLanguageParam));
$setLanguageSessionFile = "{$g['path']['dir_lang']}{$sitePart}/{$setLanguageSessionValue}/language.php";

$setLanguageByBrowser = false;

if(IS_DEMO) {
    Common::setOptionRuntime('Y', 'detect_user_language');
}

if(Common::isOptionActive('detect_user_language')) {
    $setLanguageByBrowser = langFindByBrowser();
}


$setLanguageByBrowserFile = "{$g['path']['dir_lang']}{$sitePart}/{$setLanguageByBrowser}/language.php";

if ($setLanguageSessionValue != '' and realpath($setLanguageSessionFile) and file_exists($setLanguageSessionFile)) {
    $setLanguageFile = $setLanguageSessionValue;
} elseif ($setLanguageCookieValue != '' and realpath($setLanguageCookieFile) and file_exists($setLanguageCookieFile)) {
    $setLanguageFile = $setLanguageCookieValue;
} elseif ($setLanguageByBrowser && realpath($setLanguageByBrowserFile) &&  file_exists($setLanguageByBrowserFile)) {
    $setLanguageFile = $setLanguageByBrowser;
} elseif (file_exists($g['lang'][$sitePart] . 'language.php')) {
    $setLanguageFile = Common::getOption($sitePart, 'lang_value');
} else {
    $setLanguageFile = 'default';
}
if($setLanguageFile != 'default') {
    if($sitePart == 'mobile') {
        $l = loadLanguage($setLanguageFile, 'main', $l);
    }
    include_once($g['path']['dir_lang'] . $sitePart . '/' . $setLanguageFile . '/language.php');
    set_session('language_of_user', $setLanguageFile);
}

$g['lang_loaded'] = $setLanguageFile;
$g['main']['lang_loaded'] = $setLanguageFile;

Router::init();
if(isset($nameSeo) && $nameSeo) {
    if(!Router::getIncludePage($nameSeo)) {
        pageNotFound();
    }
}

// For {site_title} tag translation
$g['main']['title'] = lCascade($g['main']['title'], array('site_title'));
$g['main']['title_orig'] = htmlspecialchars($g['main']['title'], ENT_QUOTES, 'UTF-8');

$setTemplateParam = 'set_template' . $sitePartParam;
$setTemplateParamValue = get_param($setTemplateParam, '');

if ($setTemplateParamValue != '') {
    $setTemplate = to_include($setTemplateParamValue);
	set_cookie($setTemplateParam, $setTemplate);
    set_session($setTemplateParam, $setTemplate);

    /* For Edge */
    if (!IS_DEMO && !Common::getOption('version', 'db_info')) {
        if ($setTemplateParam == 'set_template') {
            $setTemplateCookieTempValue =  to_include(get_cookie('set_template_mobile'));
            $tmplsAvailable = array('new_age' => 'black',
                                    'oryx' => 'white',
                                    'mixer' => 'black',
                                    'urban' => 'urban_mobile',
                                    'impact' => 'impact_mobile');
            $tmpl = '';
            if (isset($tmplsAvailable[$setTemplate])) {
                $tmpl = $tmplsAvailable[$setTemplate];
            } elseif ($setTemplate == 'edge') {
                $tmpl = 'edge_mobile';
            }
            set_cookie('set_template_mobile', $tmpl);
            set_session('set_template_mobile', $tmpl);
        } elseif ($setTemplateParam == 'set_template_mobile') {
            if ($setTemplate == 'edge_mobile') {
                set_cookie('set_template', 'edge');
                set_session('set_template', 'edge');
            }
        }
    }
    /* For Edge */
}

$setTemplateCookieValue = isset($setTemplate) ? $setTemplate : to_include(get_cookie($setTemplateParam));

$setTemplateCookieDir = "{$g['path']['dir_tmpl']}{$sitePart}/{$setTemplateCookieValue}";

$setTemplateSessionValue = isset($setTemplate) ? $setTemplate : to_include(get_session($setTemplateParam));
$setTemplateSessionDir = "{$g['path']['dir_tmpl']}{$sitePart}/{$setTemplateSessionValue}";

if ($setTemplateSessionValue != '' and realpath($setTemplateSessionDir) and is_dir($setTemplateSessionDir)) {
    $setTemplateDir = $setTemplateSessionValue;
} elseif ($setTemplateCookieValue != '' and realpath($setTemplateCookieDir) and is_dir($setTemplateCookieDir)) {
    $setTemplateDir = $setTemplateCookieValue;
} else {
    $setTemplateDir = Common::getOption($sitePart, 'tmpl');
}

$g['tmpl']['tmpl_active'] = $setTemplateDir;
// special
$setTemplateParamRuntime = get_param($setTemplateParam . '_runtime');
if($setTemplateParamRuntime) {
    $setTemplateParamRuntime = to_include($setTemplateParamRuntime);
    if($setTemplateParamRuntime) {
        $setTemplateParamRuntimeDir = "{$g['path']['dir_tmpl']}{$sitePart}/{$setTemplateParamRuntime}";
        if(realpath($setTemplateParamRuntimeDir) && is_dir($setTemplateParamRuntimeDir)) {
            $setTemplateDir = $setTemplateParamRuntime;
        }
    }
}

$g['tmpl']['dir_tmpl_' . $sitePart] = "{$g['path']['dir_tmpl']}{$sitePart}/{$setTemplateDir}/";
$g['tmpl']['url_tmpl_' . $sitePart] = "{$g['path']['url_tmpl']}{$sitePart}/{$setTemplateDir}/";

$g['tmpl']['tmpl_loaded'] = $setTemplateDir;
$g['tmpl']['tmpl_loaded_dir'] = $g['tmpl']['dir_tmpl_' . $sitePart];

#if (!(isset($g['no_image']) and $g['no_image'])) include_once($g['to_root'] . "_include/lib/image.php");
#if (!(isset($g['no_image']) and $g['no_image'])) include_once($g['to_root'] . "_include/current/class.uploadImage.php");
include_once($g['path']['dir_main'] . "_include/current/upload.php");
include_once($g['path']['dir_main'] . "_include/current/ajax.php");

include_once($g['path']['dir_main'] . "_pay/pay_start.php");

include_once($g['path']['dir_main'] . "_include/lib/s3.php");

//include_once($g['to_root'] . "_include/lib/html.php");
//include_once($g['to_root'] . "_include/lib/block.php");
//include_once($g['to_root'] . "_include/lib/list.php");

include_once($g['path']['dir_main'] . "_include/current/common.php");
#include_once($g['to_root'] . "_include/current/users.php");
#include_once($g['to_root'] . "_include/current/mail.php");

//include_once($g['to_root'] . "_include/current/user_fields.class.php");
//include_once($g['to_root'] . "_include/current/settings_field.class.php");
include_once($g['path']['dir_main'] . "_include/current/banner.class.php");
//include_once($g['to_root'] . "_include/current/gallery.class.php");

$g['sql']['photo_vis'] = '';
$g['sql']['photo_vis_prf'] = '';
if (Common::isOptionActive('photo_approval')) {
    $g['sql']['photo_vis'] = " AND visible='Y' ";
    $g['sql']['photo_vis_prf'] = " AND PH.visible='Y' ";
} elseif (Common::isOptionActive('nudity_filter_enabled')) {
    $g['sql']['photo_vis'] = " AND visible IN ('Y', 'N') ";
    $g['sql']['photo_vis_prf'] = " AND PH.visible IN ('Y', 'N') ";
}

if(Common::isOptionActive('disallow_indexing_in_search_engines')) {
    $g['to_head'][] = '<meta name="robots" content="noindex">';
    $g['to_head_meta'][] = '<meta name="robots" content="noindex">';
}
if(!Common::isOptionActive('mail')) {
    $g['options']['postcard'] = 'N';
}

$g['template_options'] = loadTemplateSettings($sitePart, $setTemplateDir);

if (!Common::isOptionActiveTemplate('groups_social_enabled')) {
    $g['sql']['photo_vis'] .= " AND group_id = 0 ";
    $g['sql']['photo_vis_prf'] .= " AND PH.group_id = 0 ";
}

$tmplOptionName = Common::getOption('name', 'template_options');

// Fix for group
if ($tmplOptionName == 'edge') {
    $paramGroupId = isset($_GET['group_id']) ? $_GET['group_id'] : '';
    if ($paramGroupId && !isset($_GET['view'])) {
        $groupInfo = Groups::getInfoBasic($paramGroupId);
        if ($groupInfo) {
            $_GET['view'] = $groupInfo['page'] ? 'group_page' : 'group';
        }
    }

    if(TemplateEdge::isModeLms()) {
        TemplateEdge::setModeLmsLanguageWords();
    }
}

$cityTemplate = 'default';
if ($isMobileCity){
    $cityTemplate = 'mobile';
}

/* EDGE */
$isSetMobileCity = CityBase::isEdgeMobile() && $p == 'city.php';
if (IS_DEMO && get_param('view') == 'mobile_demo') {
    $isSetMobileCity = true;
}
if ($isSetMobileCity) {
    $cityTemplate = 'mobile';
    $_GET['view'] = 'mobile';
}
/* EDGE */

$g['tmpl']['url_tmpl_common'] = "{$g['to_root']}{$g['path']['url_city_absolute']}tmpl/common/";
$g['tmpl']['url_tmpl_city'] = "{$g['to_root']}{$g['path']['url_city_absolute']}tmpl/{$cityTemplate}/";
$g['tmpl']['dir_tmpl_city'] = "{$g['path']['dir_main']}{$g['path']['url_city_absolute']}tmpl/{$cityTemplate}/";

if ($tmplOptionName == 'impact') {
    $g['options']['map_on_main_page_urban'] = Common::impactGetMapOnMainPageUrbanValue($g['options']['map_on_main_page_urban']);
}

/* RTL */
$g['lang_loaded_rtl'] = false;
$g['main']['lang_loaded_rtl'] = false;
if (Common::isOptionActiveTemplate('rtl_style') && Common::getOption($setLanguageFile, 'rtl_language_' . $sitePart)) {
    $g['lang_loaded_rtl'] = true;
    $g['main']['lang_loaded_rtl'] = true;
}
/* RTL */

if(IS_DEMO) {
    Demo::setSiteConfig();
}

/*$nameTmpl = Common::getOption('name', 'template_options');
$dimensions = array('big_x' => "big_x_{$nameTmpl}", 'big_y' => "big_y_{$nameTmpl}");
foreach ($dimensions as $key => $value) {
	if (isset($g['image'][$key]) && isset($g['image'][$value])) {
		$g['image'][$key]=$g['image'][$value];
	}
}*/

if (!(isset($g['no_headers']) and $g['no_headers'])) {
	header("Content-Type: text/html; charset=UTF-8");
    // Fix for IE iframe cookies
    if(IS_DEMO) {
        header('P3P: CP="This site does not have a p3p policy."');
    }
}

if(USE_FILES_CACHE) {
    $cachePageFile = $sitePart . '_g_cache_' . $p;
    $g['cache'] = @unserialize(cache($cachePageFile, 30));

    //var_dump_pre(var_export($g['cache'], true));
    $g['cache_init'] = $g['cache'];
}
/*$part = array('main', 'mobile', 'administration', 'partner');
foreach ($part as $item) {
        if (!countFrameworks($item.'/'.Common::getOption($item, 'tmpl'))) {

            unset($dir[0]);
            unset($dir[1]);
            foreach ($dir as $key) {
                if (is_dir($g['to_root'].'_frameworks/'.$item.'/'. $key)) {
                    Config::update('tmpl', $item, $key);
                    break;
                }
            }
        }
}*/
Common::setTemplateNotIs($sitePart);

if(Common::isOptionActive('seo_friendly_urls') && ( (Common::getOptionSetTmpl() == 'old') || (isset($_SERVER['SERVER_SOFTWARE']) && (
        stripos($_SERVER['SERVER_SOFTWARE'], 'microsoft') !== false
        )) )
    ) {
    // Common::setOptionRuntime('N', 'seo_friendly_urls');
}

if(Common::isAppIos()) {
    $g['to_head_meta'][] = "<meta http-equiv=\"Content-Security-Policy\" content=\"img-src * blob: android-webview-video-poster: cdvphotolibrary: 'self' data: 'unsafe-inline' 'unsafe-eval' ; default-src * blob: 'self' gap: wss: ws: data:; style-src 'self' 'unsafe-inline'; script-src * 'self' 'unsafe-inline' 'unsafe-eval'; connect-src * http: https: ws: wss:;\">";
}