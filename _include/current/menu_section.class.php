<?php
class CMenuSection extends CHtmlBlock
{
    var $active = '';

    function setActive($active)
    {
        $this->active = $active;
    }
    
    function parseBlock(&$html)
    {
        if($this->active) {
            $html->setvar($this->active . '_active', '_active', true);
            $html->setvar('button_oryx_' . $this->active . '_active', 'active_btn', true);
        }

        global $g_user;
        if(isset($g_user['name_seo']))
        $html->setvar('name_seo',$g_user['name_seo']);

        $favorite = DB::count('users_favorite', '`user_from` = ' . to_sql(guid(), 'Number'));
        $fans     = DB::count('users_interest', '`user_to` = ' . to_sql(guid(), 'Number'));
        $interest = DB::count('users_interest', '`user_from` = ' . to_sql(guid(), 'Number'));

        if (Common::isOptionActive('mail')) {
            $html->parse('mail_on');
        }
        // if ($favorite > 0 && Common::isOptionActive('favorite_add')) {
            $html->parse('favorite_on');
        // }
        // if ($fans > 0) {
            $html->parse('fans_on');
        // }
        // if ($interest > 0) {
            $html->parse('interest_on');
        // }
        if (Common::isOptionActive('music'))
        {
            $html->parse("my_music", true);
        }
        //start-nnsscc-diamond-20200205
        if(Common::isOptionOrientation(guid())){
            $html->parse("nsc_couple_profile", true);
            $html->parse("nsc_couple_profile_photo", true);
        }
        if (Common::isOptionActive('blogs'))
        {
            $html->parse("my_blog", true);
        }

        // var_dump(Common::isOptionActive('wink'));
        // die();
        if(Common::isOptionActive('wink')) {
            $html->parse('wink_on', false);
        }
        if($this->active == 'gallery_admin') {
            $html->parse('head');
        
        } else {
            $html->parse('head2');
        }
        if(Common::isOptionActive('news')) {
            $html->parse('news_on');
        }
        if(Common::isOptionActive('help')) {
            $html->parse('help_on');
        }
        if(Common::isOptionActive('contact')) {
            $html->parse('contact_on');
        }
        if (Common::isOptionActive('adv_search') || Common::isOptionActive('saved_searches')) {
            $html->parse('menu_search_basic', false);
        }
        if(Common::isOptionActive('adv_search')) {
            $html->parse('menu_search_advanced', false);
        }

        if(Common::isOptionActive('adv_map_search')) {
            $html->parse('menu_search_map_advanced', false);
        }

        if (Common::isOptionActive('saved_searches')) {
          $html->parse('menu_search_saved', false);
        }

        if(Common::isOptionActive('support_tickets_approval')) {
            $html->parse('support_ticket', false);
        }

        // if(Common::isOptionActive('select_group_approval')) {
            $html->parse('select_group', false);
        // }
        parent::parseBlock($html);
    }
}

