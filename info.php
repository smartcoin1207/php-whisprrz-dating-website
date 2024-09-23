<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('./_include/core/main_start.php');

if (Common::isOptionActive('content_popup_on_page', 'template_options')) {
    $page = get_param('page');
    $pages = array('term_cond' => array('menu_terms_edge', 'bottom'),
                   'priv_policy' => array('menu_privacy_policy_edge', 'bottom'),
                   'social_network_info' => array('social_network_info', 'not_in_menu'));
    $_GET['page'] = 0;
    if (isset($pages[$page])) {
        $_GET['id'] = CustomPage::getIdFromAlias($pages[$page][0], $pages[$page][1]);
    }
    include('./page_custom.php');
} else {
    $page = new PageInfo('', $g['tmpl']['dir_tmpl_main'] . 'info.html');

    if (Common::getOption('name', 'template_options') == 'impact') {
        $header = new CHeader('header', $g['tmpl']['dir_tmpl_main'] . '_header.html');
        $page->add($header);
    }

    $footer = new CFooter('footer', $g['tmpl']['dir_tmpl_main'] . '_footer.html');
    $page->add($footer);


}


include('./_include/core/main_close.php');