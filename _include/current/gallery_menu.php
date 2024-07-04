<?php
class CGallery_Menu extends CHtmlBlock
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
			$html->setvar($this->active, 'active_btn', true);
        }
        if($this->active == 'gallery_admin') {
            $html->parse('head');
        
        } else {
            $html->parse('head2');
        }

		parent::parseBlock($html);
	}
}