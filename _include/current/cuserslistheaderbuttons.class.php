<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CUsersListHeaderButtons extends CHtmlBlock {

    function parseBlock(&$html)
    {
        $html->setvar('button_active_' . Common::pageBaseName(), '_active');
        $html->setvar('button_active_separate_' . Common::pageBaseName(), 'active_btn');
        $i = 0;
        if (Common::isOptionActive('online_tab_enabled')) {
            $html->parse('online_tab_enabled', false);
            $i++;
        }
        if (Common::isOptionActive('new_tab_enabled')) {
            $html->parse('new_tab_enabled', false);
            $i++;
        }
        if (Common::isOptionActive('birthdays_tab_enabled ')) {
            $html->parse('birthdays_tab_enabled', false);
            $i++;
        }
        if (Common::isOptionActive('matching_tab_enabled')) {
            $html->parse('matching_tab_enabled', false);
            $i++;
        }
        if (Common::isOptionActive('i_viewed_tab_enabled')) {
            $html->parse('i_viewed_tab_enabled', false);
            $i++;
        }
        if (Common::isOptionActive('viewed_me_tab_enabled')) {
            $html->parse('viewed_me_tab_enabled', false);
            $i++;
        }
        if ($i == 0)
            $html->setvar('class_btn', 'userinfo_btn');
        else
            $html->setvar('class_btn', '');

        parent::parseBlock($html);
    }

}