<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

Common::checkAreaLogin();

CustomPage::checkAccessPage();

CustomPage::setSelectedMenuItemById(get_param('id'));

$page = new CustomPage('', getPageCustomTemplate('page.html', 'custom_page_template'));

$header = new CHeader('header', $g['tmpl']['dir_tmpl_main'] . '_header.html');
$page->add($header);

if (Common::isParseModule('custom_page')) {
    if (guid()) {
        $column_narrow = new CProfileNarowBox('profile_column_narrow', $g['tmpl']['dir_tmpl_main'] . '_profile_column_narrow.html');
        $page->add($column_narrow);
    } else {
        $loginForm = new CLoginForm('login_form', $g['tmpl']['dir_tmpl_main'] . '_login_form.html');
        $page->add($loginForm);
    }
}

$footer = new CFooter('footer', $g['tmpl']['dir_tmpl_main'] . '_footer.html');
$page->add($footer);