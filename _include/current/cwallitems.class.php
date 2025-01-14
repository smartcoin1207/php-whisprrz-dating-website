<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CWallItems extends CHtmlBlock {

    function parseBlock(&$html)
    {
        $cmd = get_param('cmd');
        $uid = get_param('uid', guid());
        $isOnlySeeFriends = Wall::isOnlySeeFriends($uid);
        if ($cmd != 'update' && Common::isOptionTemplateSet('urban')) {
            $isOnlySeeFriends = true;
        }
        if ($isOnlySeeFriends) {
            if (Common::isMobile()) {
                Wall::$infoPhotoBigLimit = 2;
            }
            Wall::setUid($uid);
            $item = get_param_int('item');
            if ($item) {
                Wall::setSingleItemMode($item);
                Wall::parseItems($html, false, $item);
            } else {
                Wall::parseItems($html);
            }
        }

        parent::parseBlock($html);
    }

}