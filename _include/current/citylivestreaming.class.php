<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CityLiveStreaming extends CHtmlBlock
{

    static $table = 'city_live_streaming';
    static $liveId = 0;

    static function includePath()
    {
        return dirname(__FILE__) . '/../../';
    }

	static function parseDataInit(&$html)
    {
		global $g;
		global $g_user;

		$guid = $g_user['user_id'];
		$clientId = $guid;
        $html->setvar('client_guid', $guid);

		$clientIdStr = self::getIdByLiveStreaming($clientId);
		$html->setvar('client_id', $clientIdStr);

		$html->setvar('media_server', $g['webrtc_app_live_streaming']);

		$html->setvar('city_live_stream_aviable', intval(self::isAviableLiveStreaming()));
	}

	static function getIdByLiveStreaming($callId, $type = 'city_livestream')
    {
        if ($type) {
            $type = '_' . $type;
        }
        $key = domain() . '_' . $type . '_' . $callId;
        $key = str_replace(array('.'), '_', $key);
        return $key;
    }

    static function createLive()
    {
        $responseData = false;
        $guid = guid();
        $uid = get_param_int('uid');
        if ($uid) {
            $data = array(
                'user_id' => $uid,
                'hash' => hash_generate(64),
            );
            DB::insert(self::$table, $data);
            $liveId = DB::insert_id();

            $responseData = array('id' => $liveId, 'hash' => $data['hash']);
        }
        return $responseData;
    }

    static function deleteLive($id)
    {
        DB::delete(self::$table, 'id = ' . to_sql($id));
    }

    static function getInfoLive($id, $field = false, $dbIndex = 0, $cache = true)
    {
        $keyField = $field ? $field : 0;
        $key = 'live_streaming_info_' . $id;
        $info = null;
        if ($cache) {
            $info = Cache::get($key);
        }
        if($info === null) {
            $sql = 'SELECT * FROM `' . self::$table . '` WHERE `id` = ' . to_sql($id, 'Number');
            $info = DB::row($sql, $dbIndex);

            Cache::add($key, $info);
        }

        $return = $info;

        if($field !== false) {
            $return = isset($info[$field]) ? $info[$field] : '';
        }

        return $return;
    }

    static function updateLive($liveId, $data)
    {
        DB::update(self::$table, $data, 'id = ' . to_sql($liveId));

        $key = 'live_streaming_info_' . $liveId;
        $info = Cache::get($key);
        if($info) {
            $info = array_merge($info, $data);
            Cache::add($key, $info);
        }
    }

    static function checkStatusLive($time, $currentTime = null)
	{
        if ($currentTime == null) {
            $currentTime = time();
        }

		$time = intval($time);
        $d = 60;//If time is more than 2 minutes
        $d1 = abs($currentTime - $time);

        if($time && $d1 && $d1 > $d){
            $time = 0;
        }

        //var_dump_pre($time);
        return $time;
    }

    static function setStatusLive($liveId = null, $liveTime = null)
    {

        //$liveIdEnd = get_param_int('live_id_end');
        //if ($liveIdEnd) {
            //return;
        //}

        if ($liveId === null) {
            $liveId = get_param_int('live_id');
        }

        if ($liveTime === null) {
            $liveTime = get_param_int('live_time');
        }

        if ($liveId && $liveTime) {
            $liveTime = self::checkStatusLive($liveTime);
            self::updateLive($liveId, array('status' => $liveTime));
        }
    }

    static function setLiveStart()
    {
        $liveId = get_param_int('live_id');

        $time = time();
        $data = array('date_start' => date('Y-m-d H:i:s'),
                      'status' => $time);
        self::updateLive($liveId, $data);

        return true;
    }

    static function setLiveStop()
    {
        $liveId = get_param_int('live_id');

        self::setLiveStopOne($liveId);

        return true;
    }

    static function setLiveStopOne($liveId)
    {
        $liveInfo = self::getInfoLive($liveId);

		$location = get_param_int('location');
		if ($location) {
			$option = 'loc_' . $location;
			$usersVideo = trim(Common::getOption($option . '_user', '3d_city_video'));
			$guid = guid();
			if (strpos($usersVideo, "city_livestream_{$guid}:{$guid}:{$liveId}") !== false) {
				Config::update('3d_city_video', $option . '_user', '');
			}
		}

        if ($liveInfo) {
            $data = array('date_stop' => date('Y-m-d H:i:s'),
                          'status' => 0);
            self::updateLive($liveId, $data);
        }
    }

    static function itemAddToArray($item, &$array, $prf = '')
    {
        $item = intval($item);
        if ($item > 0) {
            $array[] = $item . $prf;
        }
    }

	static function isAviableLiveStreaming()
    {
        $isAvailable = true;
        if(Common::isAppIos() || (Common::isIosDevice() && !Common::isSafari())) {
            $isAvailable = false;
        }

        return $isAvailable;
	}

	static function notAviableLiveStreamingAdmin()
    {
		global $sitePart;

		return $sitePart != 'administration' && !LiveStreaming::isAviableLiveStreaming();
	}

	static function updateServer()
    {
		$liveId = get_param_int('live_id');
		$livePresenter = get_param_int('live_presenter');
		if ($liveId && $livePresenter) {
			self::setStatusLive();
		}
	}

	function parseBlock(&$html) {

		global $g_user;

        parent::parseBlock($html);
	}
}