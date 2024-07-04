<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

Class TemplateBase {

    /* Live */
    static function parseListLive(&$html, $typeOrder, $limit, $blockItems, $online = true)
    {
        global $p;

        $postDisplayType = 'info';

        $rows = LiveStreaming::getLists($limit, $typeOrder, $online);
        $html->parse("list_live_{$postDisplayType}");

        if ($rows) {
            foreach ($rows as $row) {
                self::parseLive($html, $row, $postDisplayType, $online);
            }
        } else {
            $blockItems = '';
        }

        return $blockItems;
    }


    static function parseLive(&$html, $row, $postDisplayType, $online = true)
    {
        $blockItem = 'list_live_item';
        $blockItemType = "{$blockItem}_{$postDisplayType}";
        if ($html->blockExists($blockItemType)) {
            global $g;
            $uidParam = User::getParamUid(0);
            $uid = $row['user_id'];
            $guid = guid();
            $info = array('id'             => $row['id'],
                          'url'            => $row['url'],
                          'user_id'        => $row['user_id'],
                          'user_name'      => User::nameOneLetterFull($row['name']),
                          'user_url'       => $row['user_url'],
                          'user_city'      => $row['city'] ? l($row['city']) : l($row['country']),

                          'count_comments' => $row['count_comments'],
                          'time_ago'       => $row['time_ago'],
                          'image'          => custom_getFileDirectUrl($g['path']['url_files'] . $row['src_bm']),
                          'tags'           => $row['tags_html'],
                          'subject'        => $row['subject'],
						  'subject_attr'   => toAttr($row['subject']),
						  'subject_short' => neat_trim($row['subject'], 100, ''),
                    );
            $html->assign($blockItem, $info);

            $html->subcond($row['subject'], "{$blockItemType}_description");
            //$html->subcond($row['tags_html'], "{$blockItemType}_tags");

            if (!$uidParam) {
                if (User::isOnline($uid) || !$online) {
                    $html->parse("{$blockItemType}_online_top", false);
                    $html->parse("{$blockItemType}_online", false);
                } else {
                    $html->clean("{$blockItemType}_online_top");
                    $html->clean("{$blockItemType}_online");
                }

                $html->subcond(!$uidParam, "{$blockItemType}_name");
            }

             if (get_param('ajax')) {
                $html->parse("{$blockItemType}_hide", false);
            }
            $html->parse($blockItemType);
        }
    }
    /* Live */

	/* Videos */
	static function parseListVideos(&$html, $typeOrder, $limit, $blockItems, $groupId = 0, $showAllMyVideo = false)
    {
        global $p;

        $postDisplayType = 'info';

        $rows = CProfileVideo::getVideosList($typeOrder, $limit, null, guid(), true, 0, '', $groupId, $showAllMyVideo);
        $html->parse("list_video_{$postDisplayType}");

        if ($rows) {
            foreach ($rows as $row) {
                self::parseVideo($html, $row, $postDisplayType);
            }
        } else {
            $blockItems = '';
        }

        return $blockItems;
    }

    static function parseVideo(&$html, $row, $postDisplayType)
    {
        $blockItem = 'list_video_item';
        $blockItemType = "{$blockItem}_{$postDisplayType}";
        if ($html->blockExists($blockItemType)) {
            global $g;
            $uidParam = User::getParamUid(0);
            $uid = $row['user_id'];
            $info = array('id'             => $row['video_id'],
                          'video_id'       => 'v_' . $row['video_id'],
                          'user_id'        => $row['user_id'],
                          'user_name'      => User::nameOneLetterFull($row['name']),
                          'user_city'      => $row['city'] ? l($row['city']) : l($row['country']),
                          'image'          => custom_getFileDirectUrl($g['path']['url_files'] . $row['src_src']),
                          'src'            => custom_getFileDirectUrl($g['path']['url_files'] . $row['src_v']),
                          'subject'        => $row['subject'],
                          'subject_attr'  => toAttr($row['subject']),
                          'subject_short' => neat_trim($row['subject'], 100, ''),
                          'count_comments' => $row['count_comments'],
                          'text'           => $row['description'],
                          'time_ago'       => $row['time_ago'],
                          'info'           => json_encode($row),
                          'tags'           => $row['tags_html'],
                          'hide_header'     => $row['hide_header'] ? l('picture_add_in_header') : l('picture_remove_from_header'),
                          'hide_header_icon'=> $row['hide_header'] ? 'fa-plus-square' : 'fa-minus-square'
                    );
			if (Common::isMobile()) {
				$info['user_url' ] = 'profile_view.php?user_id=' . $uid;
			} else {
				$info['user_url' ] = User::url($uid, $row['user_info']);
			}
            $html->assign($blockItem, $info);
            if (guid()) {
                $html->parse('set_video_data', false);
            }
            $html->subcond($row['tags_html'], "{$blockItemType}_tags");

            $html->subcond(CProfilePhoto::isVideoOnVerification(0, $row['visible']), "{$blockItemType}_not_checked");

            if (!$uidParam) {
                $html->subcond(User::isOnline($uid), "{$blockItemType}_online");
                $html->subcond(!$uidParam, "{$blockItemType}_name");
            }
            $html->subcond($row['subject'], "{$blockItemType}_description");

            if (get_param('ajax')) {
                $html->parse("{$blockItemType}_hide", false);
            }
            $html->parse($blockItemType);
        }
    }
	/* Videos */
}