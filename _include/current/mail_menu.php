<?php
class CMailMenu extends CHtmlBlock
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

        $favorite = DB::count('users_favorite', '`user_from` = ' . to_sql(guid(), 'Number'));
        $fans     = DB::count('users_interest', '`user_to` = ' . to_sql(guid(), 'Number'));
        $interest = DB::count('users_interest', '`user_from` = ' . to_sql(guid(), 'Number'));

        if (Common::isOptionActive('mail')) {
            $html->parse('mail_on');
        }
        if ($favorite > 0 && Common::isOptionActive('favorite_add')) {
            $html->parse('favorite_on');
        }
        if ($fans > 0) {
            $html->parse('fans_on');
        }
        if ($interest > 0) {
            $html->parse('interest_on');
        }
        if(Common::isOptionActive('wink')) {
            $html->parse('wink_on', false);
        }
		parent::parseBlock($html);
	}
}
