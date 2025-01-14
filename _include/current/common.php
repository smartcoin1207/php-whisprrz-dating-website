<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

#хедер, футер
Common::setSiteOptions();
$filesSizeLimit = Common::getOption('files_size_limit', 'main');
$paymentEnd = Common::getOption('payment_end', 'main');
$guid = get_session('user_id');
$adminUserId = Common::getOption('admin_user_id');
$admin = false;
if (($guid && $guid == $adminUserId) || get_session('admin_auth') == 'Y'){
    $admin = true;
}

if ((Common::isOptionActive('maintenance')
     || (intval(Common::getOption('files_size', 'main'))/1073741824 > $filesSizeLimit
         && $filesSizeLimit != 0)
     || (date('Y-m-d H:i:s') > $paymentEnd
         && $paymentEnd != 0))
     && $sitePart != 'administration' && $p != 'cron.php' && $p != 'after.php' && $p != 'prepare_data.php' && $p != 'updater.php' && !$admin && !($p === 'ajax.php' && get_param('cmd') === 'live_stream_save_record_file')) {
    if(Common::isMobile()) {
        $g['path']['url_main'] = '../';
    }
    /*require_once('install.class.php');
    $myinstall = new Class_Install(l('Maintenance'), Common::getOption('title', 'main'));
    $myinstall->html .= '<p>' . l('Site is under maintenance') . '</p>';
    echo $myinstall->header;
    echo $myinstall->html;
    echo $myinstall->footer;*/

    class CMaintenance extends CHtmlBlock
    {
        function parseBlock(&$html) {
            $html->setvar('header_favicon', Common::getfaviconSiteHtml());
            parent::parseBlock($html);
        }
    }

    $maintenance = new CMaintenance('', $g['path']['dir_main'] . '_frameworks/install/default/maintenance.html');
    $maintenance->init();
    $maintenance->action();
    $tmp = null;
    echo $maintenance->parse($tmp, true);
    exit;
}

IP::block();

#общественные классы
// TODO: add to userfields

function autocomplete_off()
{
    $value = '';
    if(isset($_SERVER['HTTP_USER_AGENT'])) {
        if (preg_match('/Chrome/i', $_SERVER['HTTP_USER_AGENT'])) {
            $value = 'autocomplete="off"';
        }
    }
    return $value;
}

function parse_blog_msg($msg)
{
#$msg = str_replace("{videowidth}","300",$msg);
#$msg = str_replace("{videoheight}","225",$msg);
#$msg = str_replace("{videoheight2}","200",$msg);
#$msg = str_replace("<a","<a target=_blank ",$msg);
    return $msg;
}