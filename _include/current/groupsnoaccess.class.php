<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class GroupsNoAccess extends CHtmlBlock
{
	function parseBlock(&$html)
	{
        $guid = guid();
        $groupId = Groups::getParamId();

        $isSubscribers = Groups::isSubscribeUser($guid, $groupId);
        $title = '';
        $action = 'request';
        if ($isSubscribers) {
            $action = 'remove';
            $title = l('group_no_access_request');
        } else {
            $subscribeRequestInfo = Groups::getSubscribeRequestInfo($guid, $groupId);
            if ($subscribeRequestInfo && !$subscribeRequestInfo['accepted']) {
                $action = 'remove_request';
                $title = l('group_no_access_request');
            }
        }
        if (!$title) {
            $title = lSetVars('group_no_access', array('data_action' => $action));
        }

        $html->setvar('bl_access_title', $title);

        $uid = User::getParamUid(0);
        TemplateEdge::parseColumn($html, $uid);

		parent::parseBlock($html);
	}
}