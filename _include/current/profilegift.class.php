<?php
class ProfileGift extends CHtmlBlock
{
    private static $dataGift = array();
    private static $send = false;
    private static $cmd = '';

    function action() {

        $cmd = get_param('cmd');

        if($cmd == 'send_gift') {
            self::send();
        }
    }

    static function setCmd($cmd)
    {
        self::$cmd = $cmd;
    }

    static function getCmd()
    {
        return self::$cmd;
    }

    static function setParams($data)
    {
        self::$dataGift = $data;
    }

    static function getParams()
    {
        return self::$dataGift;
    }

    static function setSend($send)
    {
        self::$send = $send;
    }

    static function isSend()
    {
        return self::$send;
    }

    static function getActiveSet()
	{
        return DB::result('SELECT `id` FROM `gifts_set` WHERE `active` = 1');
    }

    static function parseGiftBox(&$html)
	{
        global $g;
        $block = 'pp_gift';
        if ($html->blockexists($block)) {
            $where = '';
            $activeSet = self::getActiveSet();
            if ($activeSet) {
                $where = ' WHERE `set` = ' . to_sql(self::getActiveSet());
            }

            $sql = 'SELECT *
                      FROM `gifts` ' .
                    $where .
                   ' ORDER BY `position`';
            $gifts = DB::all($sql);
            if (!empty($gifts)) {
                $isSelectGift = true;
                foreach ($gifts as $gift) {
                    $urlImg = self::getUrlImg($gift['id'], $gift['hash']);
                    if ($urlImg) {
                        if ($isSelectGift) {
                            if (!Common::isCreditsEnabled()) {
                                $title = l('grab_attention_with_a_gift_free_site');
                            } elseif ($gift['credits']) {
                                $vars = array('pay_credits' => $gift['credits']);
                                $title = lSetVars('grab_attention_with_a_gift', $vars);
                            } else {
                                $title = l('grab_attention_with_a_gift_free');
                            }
                            $html->setvar($block . '_title', $title);

                            $html->parse($block . '_select', false);
                            $isSelectGift = false;
                        } else {
                            $html->clean($block . '_select');
                        }
                        $html->setvar($block . '_id', $gift['id']);
                        $html->setvar($block . '_credits', $gift['credits']);
                        $html->setvar($block . '_url', $urlImg);
                        $html->parse($block . '_img');
                    }
                }
                //$html->parse($block, false);
            }
            if($html->blockexists($block.'_credits') && Common::isTransferCreditsEnabled()){
                $html->parse($block.'_credits', false);
            }
            $html->parse($block, false);
        }

    }

    static function deleteAllGiftsUser($uid)
	{
        DB::delete('user_gift', '`user_from` = ' . to_sql($uid, 'Number') . ' OR `user_to` = ' . to_sql($uid, 'Number'));
    }

    static function delete()
	{
        global $g_user;
        $responseData = false;
        if ($g_user['user_id']) {
            $gid = get_param('gid');
            $uid = get_param('uid');
            $img = get_param('img');
            DB::delete('user_gift', '`id` = ' . to_sql($gid, 'Number'));
            $search = "{gift:{$gid}:{$img}%";
            $where = "`system` = 1 AND `msg` LIKE '{$search}'";
            DB::delete('im_msg', $where);

            $responseData = true;
        }
        return $responseData;
	}

    static function deleteText($gid = null)
	{
        global $g_user;

        $responseData = false;

        if ($gid === null) {
            $gid = get_param('gid');
        }
        if ($g_user['user_id'] && $gid) {
            DB::update('user_gift', array('text' => ''), '`id` = ' . to_sql($gid, 'Number'));
            $responseData = true;
        }
        return $responseData;
	}


    static function send()
	{
        global $g_user;

        $responseData = false;
        $userTo = get_param('user_to');
        $gift = get_param('gift');
        if(Common::isTransferCreditsEnabled()){
            $giftsCredits=(int) get_param('gifts_credits',0);
        } else {
            $giftsCredits=0;
        }
        if ($g_user['user_id'] && $userTo && $gift) {
            $optionTmplName = Common::getOption('name', 'template_options');
            $isCreditsEnabled = Common::isCreditsEnabled();
            $isAllowedSend = true;
            $currentCredits = $g_user['credits'];
            if ($isCreditsEnabled) {
                //$price = Pay::getServicePrice('gift');
                $giftCredits = DB::result('SELECT `credits` FROM `gifts` WHERE `id` = ' . to_sql($gift, 'Number'));
                //$currentCredits = $g_user['credits'] - $price['credits'];
                $currentCredits = $g_user['credits'] - $giftCredits-$giftsCredits;
                $isAllowedSend = $currentCredits >= 0;
                if ($optionTmplName == 'urban_mobile') {
                    $responseData = 'refill_credits';
                }
            }

            if ($isAllowedSend) {
                $msg = trim(get_param('text'));
                /*$to_user = $userTo;
				$censured = false;
                if($msg != ''){
                    $censuredFile = dirname(__FILE__) . '/../../_server/im_new/feature/censured.php';
                    if (file_exists($censuredFile)) include($censuredFile);
                }*/
                $msg = censured($msg);
                //if ($msg){
                    $recipient = get_param('recipient');
                    $date = date('Y-m-d H:i:s');
                    $vars = array('user_from' => $g_user['user_id'],
                                  'user_to' => $userTo,
                                  'gift' => $gift,
                                  'text' => $msg,
                                  'visibility' => $recipient,
                                  'date' => $date,
                                  'gifts_credits' => $giftsCredits,
                                );
                    DB::insert('user_gift', $vars);
                    $id = DB::insert_id();
                    $vars['id'] = $id;

                    $msg = "{gift:{$id}:{$gift}:{$giftsCredits}}";
                    CIm::addMessageToDb($userTo, $msg, $date, 0, 0, true, false, 1);
                    $vars['credits'] = $currentCredits;
                    self::setParams($vars);

                    if (Common::isEnabledAutoMail('gift')) {
                        $userInfo = User::getInfoBasic($userTo);
                        $isNotif = User::isOptionSettings('set_notif_gifts', $userInfo);
                        if ($isNotif) {
                            $vars = array('title' => Common::getOption('title', 'main'),
                                          'name' => $userInfo['name'],
                                          'name_sender'  => $g_user['name'],
                                          'url_site' => Common::urlSite());
                            Common::sendAutomail($userInfo['lang'], $userInfo['mail'], 'gift', $vars);
                        }
                    }
                    if ($isCreditsEnabled) {
                        User::update(array('credits' => $currentCredits), $g_user['user_id']);
                        User::addCreditsByUserId($userTo,$giftsCredits);
                    }
                    CStatsTools::count('gifts_sent');
                    DB::execute('UPDATE `gifts` SET `sent` = `sent` + 1 WHERE id = ' . to_sql($gift));
                    $responseData = true;
                //}
            }

        }
        self::setSend($responseData);
        return $responseData;
	}

    static function getUrlImg($id, $hash = null, $isHash = true, $dir = 'image')
    {
        if ($hash === null) {
            $sql = 'SELECT `hash` FROM `gifts` WHERE `id` = ' . to_sql($id);
            $hash = DB::result($sql);
        }
        $hash = $isHash ? "?v={$hash}" : '';

        $url = Common::getOption('url_files', 'path') . "gifts/{$dir}/{$id}.png{$hash}";
        return $url;
    }

    static function parseGift(&$html, $uid, $gifts = null, $update = false, $lastId = 0)
    {
        global $g_user;
        global $p;

        $blockGift = 'user_gift';
        $blockGiftItem = "{$blockGift}_item";
        if($html->blockexists($blockGiftItem) && Common::isOptionActive('gifts_enabled')) {
            $isGift = false;
            if ($gifts === null) {
                // Optimize to immediately selected according to 'visibility'
                $gifts = DB::select('user_gift', '`user_to` = ' . to_sql($uid, 'Number'));
            }
            if (!empty($gifts)) {
                $numberCustomPart = Common::getOption('custom_show_part_gifts_number', 'template_options');
                $isParseMorePart = false;
                $i = 0;
                $numberGifts = count($gifts);
                foreach ($gifts as $key => $item) {
                    $urlImg = self::getUrlImg($item['gift']);
                    if (!$urlImg) {
                        continue;
                    }
                    $isMainGift = ($g_user['user_id'] == $uid);
                    $isGift = true;
                    if ($item['visibility'] && !$isMainGift
                        && $item['user_from'] != $g_user['user_id']) {
                        //$html->clean($blockGiftItem . '_text');
                        continue;
                    }
                    $i++;
                    // Mobile urban
                    if ($numberCustomPart
                        && $numberGifts > $numberCustomPart
                        && $i > $numberCustomPart) {
                        $isParseMorePart = true;
                        continue;
                    }

                    $html->setvar($blockGiftItem . '_id', $item['id']);
                    $html->setvar($blockGiftItem . '_user_id', $item['user_from']);
                    $html->setvar($blockGiftItem . '_user_profile_link', User::url($item['user_from']));
                    $html->setvar($blockGiftItem . '_url', $urlImg);
                    $html->setvar($blockGiftItem . '_img_id', $item['gift']);
                    $fromUserName = User::getInfoBasic($item['user_from'], 'name');
                    $html->setvar($blockGiftItem . '_name', $fromUserName);
                    $html->setvar($blockGiftItem . '_name_short', hard_trim(User::nameOneLetterFull($fromUserName), 8));
                    // the date of the languages d M Y
                    $html->setvar($blockGiftItem . '_date', Common::dateFormat($item['date'], 'gift_date'));
                    $isMainGift = $isMainGift || $g_user['user_id'] == $item['user_from'];
                    $html->setvar($blockGiftItem . '_title', $item['visibility'] ? l('only_you_can_see_this_gift') : '');
                    if ($isMainGift) {
                        $html->parse($blockGiftItem . '_delete');
                    } else {
                        $html->clean($blockGiftItem . '_delete');
                    }

                    $flagGiftCredits=false;
                    if($item['gifts_credits']>0 && $html->blockexists($blockGiftItem.'_credits') && Common::isTransferCreditsEnabled()){
                    $nnn='=';
                        $flagGiftCredits=true;
                        $html->setvar('gifts_credits', '+ '.lSetVars('credit_balance',array('credit'=>$item['gifts_credits'])));
                        $html->parse($blockGiftItem.'_credits', false);
                    } else {
                        $html->clean($blockGiftItem . '_credits');
                    }

                    $item['text']=trim($item['text']);

                    if (!empty($item['text']) || $flagGiftCredits) {
                        if ($isMainGift) {
                            $html->parse($blockGiftItem . '_text_delete');
                        } else {
                            $html->clean($blockGiftItem . '_text_delete');
                        }
                        /*
                        if($item['gifts_credits']>0){
                            $html->setvar($blockGiftItem . '_gifts_credits', $item['gifts_credits']);
                            $html->parse($blockGiftItem . '_credits',false);
                        }
                        */

                        $html->setvar($blockGiftItem . '_text', $item['text']);
                        //$html->setvar($blockGiftItem . '_text', hard_trim($item['text'],59));
                        $html->parse($blockGiftItem . '_text', false);
                    } else {
                        $html->clean($blockGiftItem . '_text');
                    }
                    if ($update) {
                        $html->parse($blockGiftItem . '_update', false);
                    } elseif (isset($item['credits'])) {
                        $html->setvar('credits', $item['credits']);
                        $html->parse($blockGiftItem . '_set_credits', false);
                    }
                    $html->parse($blockGiftItem, true);


                }

                if ($isParseMorePart) {
                    $html->setvar($blockGiftItem . '_more', lSetVars('show_more_part_gifts', array('number' => $i - $numberCustomPart)));
                    $html->parse($blockGiftItem . '_more', false);
                }
                if ($isGift) {
                    $html->parse($blockGift . '_b', false);
                }
            }
        }
    }

	function parseBlock(&$html) {

		global $g_user;

        $cmd = get_param('cmd');
        $page = get_param('page');
        $userTo = get_param('user_to');

        $data = self::getParams();
        if (self::getCmd() == 'update') {
            $lastId = get_param('last_gift');
            $userId = get_param('request_user_id');
            if (!$userId) {
                $userId = $g_user['user_id'];
            }
            $where = '`user_to` = ' . to_sql($userId, 'Number') . ' AND `id` > ' . to_sql($lastId, 'Number');
            $gifts = DB::select('user_gift', $where);
            self::parseGift($html, $userId, $gifts, true);

            $existingGifts = DB::field('user_gift', 'id' ,'`user_to` = ' . to_sql($userId, 'Number'));
            $html->setvar('existing_gifts', json_encode(array_flip($existingGifts)));
            $html->parse('user_gift_existing', false);
        }elseif ($cmd == 'send_gift' && $g_user['user_id'] && self::isSend()
            && !empty($data) && $page == 'search_results.php'
            && $g_user['user_id'] != $userTo) {
            self::parseGift($html, $userTo, array($data));
        }

		parent::parseBlock($html);
	}
}