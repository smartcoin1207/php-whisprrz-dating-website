<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

function gallery_printDesc($image_id)
{	
	$html="<script type=\"text/javascript\">initEditableDesc('DescEditable', ".$image_id.");</script>";	
	return $html;
}
function gallery_printTitle($image_id)
{	
    $html="<script type=\"text/javascript\">initEditableTitle('TitleEditable', ".$image_id.");</script>";
	return $html;
}
