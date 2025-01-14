<?php
class CProfileMenu extends CHtmlBlock
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

		if (Common::isOptionActive('music'))
		{
			$html->parse("my_music", true);
		}

		if (Common::isOptionActive('blogs'))
		{
			$html->parse("my_blog", true);
		}
		parent::parseBlock($html);
	}
}
