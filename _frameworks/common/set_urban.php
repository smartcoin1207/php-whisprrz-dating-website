<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$g['set_options'] = array(
    // 'fields_not_available' => array(133,134,145,146,149,150,155,156,157,158,163,164,165,166,167,168,169,170,348,468), // popcorn delete 2023-11-24
    'fields_not_available' => array(454,455,456,457,458,459,460,468,472,485,473,494,552,541,542,708), // popcorn modified 2023-11-24

    'sitemap' => array(
        'disabled' => array(
            'pages_from_options' => array(
                'music' => '', 'places' => '', 'events' => '', 'groups' => '',
                'news' => '', 'help' => '', 'blogs' => '', 'forum' => '', 'biorythm' => '',
                'top5' => '', 'adv' => '',
                'vidogallery' => 'vids.php',
                'gallery' => 'gallery_index.php',

            ),
            'urls' => array(
                'widgets.php', 'users_hon.php', 'my_friends.php', 'contact.php',
                'join2.php', 'join3.php', 'users_online.php', 'search.php' , 'users_new.php',
                'search_advanced.php'

            ),
        ),
    ),
    'hide_profile_settings' => array('set_notif_show_my_age'),

);

// Common::setOptionRuntime('N', 'widgets');
// Common::setOptionRuntime('N', 'bookmarks');


if (Common::getTmplName() != 'edge') {
    // Common::setOptionRuntime('N', 'favorite_add');
    Common::setOptionRuntime('N', 'music');
    Common::setOptionRuntime('Y', 'partner_settings');
}