<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('./_include/core/main_start.php');
include('./_include/current/vids/start.php');

class CPage extends CHtmlBlock
{
	function parseBlock(&$html)
	{
        $html->items('comment', CVidsTools::getCommentsByVideoId(ipar('id'), ipar('offset') . ',' . ipar('limit')), '', 'is_my');
        parent::parseBlock($html);
    }
}

vids_render_page();
include('./_include/core/main_close.php');
