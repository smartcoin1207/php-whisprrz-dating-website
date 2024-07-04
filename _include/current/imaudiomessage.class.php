<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class ImAudioMessage {

    public static $table = 'im_audio_message';

    public static function save()
    {
        $isSaved = false;

        $uid = guid();
        $fileString = Common::getFileFromStringParam('audio');

        if($fileString && $uid) {

            $hash = md5($uid . microtime() . hash_generate(32));

            $file = self::createFilePath(0, $uid, $hash);

            if(file_put_contents($file, $fileString) === strlen($fileString)) {

                $row = array(
                    'user_id' => $uid,
                    'hash' => $hash,
                    'date' => time(),
                );
                DB::insert(self::$table, $row);
                $id = DB::insert_id();

                $fileFinal = self::createFilePath($id, $uid, $hash);
                if(rename($file, $fileFinal)) {
                    $isSaved = $id;
                } else {
                    @unlink($file);
                    @unlink($fileFinal);
                }

            } else {
                if(file_exists($file)) {
                    @unlink($file);
                }
            }
        }

        return $isSaved;
    }

	public static function saveBlob()
    {
        $isSaved = false;

        $uid = guid();
		$param = 'im_msg_audio_blob';
		$response  = array('error' => l('upload_error'));

		if (!isset($_FILES[$param])) {
			return $response;
		}

		$errors = array(
            1 => 'upload_max_filesize_php',
            2 => 'max_file_size_html',
            3 => 'file_uploaded_partially',
            4 => 'no_file_uploaded',
            6 => 'temporary_folder',
            7 => 'failed_write_file_disk',
            8 => 'php_stopped_upload',
		);
        $error = $_FILES[$param]['error'];
        if ($error) {
            return array('error' => l($errors[$error]));
        }

		if ($_FILES[$param]['size'] == 0) {
			return $response;
		}

		$hash = md5($uid . microtime() . hash_generate(32));

		$file = self::createFilePath(0, $uid, $hash);

		if (!move_uploaded_file($_FILES[$param]['tmp_name'], $file)) {
			return $response;
        }

		$row = array(
                'user_id' => $uid,
                'hash' => $hash,
                'date' => time(),
        );
        DB::insert(self::$table, $row);
        $id = DB::insert_id();

        $fileFinal = self::createFilePath($id, $uid, $hash);
        if(rename($file, $fileFinal)) {
			$response = array(
				'result' => 'success',
				'id' => $id,
				'url' => self::getUrl($id)
			);
		} else {
			@unlink($file);
			@unlink($fileFinal);
		}

		return $response;
    }

    public static function updateImMsgId($id, $imMessageId, $param = 'im_msg_id')
    {
        if($id) {
            $row = array($param => $imMessageId);
            DB::update(self::$table, $row, 'id = ' . to_sql($id));
        }
    }
    
    public static function delete($id, $uid = 0, $param = 'id')
    {
        $isDeleted = false;

        if(!$uid) {
            $uid = guid();
        }

        if($uid) {
            $where = 'user_id = ' . to_sql($uid) . " AND {$param} = " . to_sql($id);

            $row = DB::one(self::$table, $where);
            if($row) {
				$id = $row['id'];
                $file = self::createFilePath($id, $uid, $row['hash']);
                if(file_exists($file)) {
                    @unlink($file);
                }
                DB::delete(self::$table, $where);

                $isDeleted = true;
            }
        }

        return $isDeleted;
    }

    public static function deleteByUid($uid)
    {

        $imAudioMessages = DB::select(self::$table, 'user_id = ' . to_sql($uid));
        if($imAudioMessages) {
            foreach($imAudioMessages as $imAudioMessage) {
                self::delete($imAudioMessage['id'], $imAudioMessage['user_id']);
            }
        }

    }

    public static function deleteNotUsedFiles()
    {
        $where = '`im_msg_id` = 0 AND `date` < ' . to_sql(time() - 3600 * 24 * 7);
        $rows = DB::select(self::$table, $where);
        if($rows) {
            foreach($rows as $row) {
                self::delete($row['id'], $row['user_id']);
            }
        }
    }

    public static function createBasePath($id, $uid, $hash)
    {
        return 'im_audio_message/' . $id . '_' . $uid . '_' . $hash . '.wav';
    }

    public static function createFilePath($id, $uid, $hash)
    {
        $filePath = Common::getOption('dir_files', 'path') . self::createBasePath($id, $uid, $hash);
        return $filePath;
    }

    public static function getUrl($id)
    {
        $url = '';

        $where = 'id = ' . to_sql($id);
        $row = DB::one(self::$table, $where);
        if($row) {
            $url = Common::getOption('url_files', 'path') . self::createBasePath($id, $row['user_id'], $row['hash']);
        }

        return $url;
    }

    public static function isActive()
    {
        return Common::isOptionActive('im_audio_messages') && Common::getAppIosApiVersion() >= 48;
    }

	public static function isActiveAudioComment()
    {
        return Common::isOptionActive('audio_comment') && Common::getAppIosApiVersion() >= 48;
    }

	public static function parseControlAudioCommentPost(&$html, $block = 'feed_comment_audio')
    {
		if (!self::isActiveAudioComment()) {
			return;
		}

		$blockFeedAudioMsg = "{$block}_top";
		if ($html->blockExists($blockFeedAudioMsg)) {
			$html->parse($blockFeedAudioMsg, false);
		}

		$blockFeedAudioMsg = "{$block}_bottom";
		if ($html->blockExists($blockFeedAudioMsg)) {
			$html->parse($blockFeedAudioMsg, false);
		}
	}

	public static function parseControlAudioComment(&$html, $blockFeedAudioMsg, $cid = 0)
    {
		if (!self::isActiveAudioComment()) {
			return;
		}
		if ($html->blockExists($blockFeedAudioMsg)) {
			$html->setvar($blockFeedAudioMsg . '_comment_id', $cid);
			$html->parse($blockFeedAudioMsg, false);
		}
    }

	public static function getHtmlPlayer($row, $id, $prf = 'pp_messages_audio_', $br = false)//For old template
    {
		if (!Common::isOptionActiveTemplate('audio_comment_old')){
			return '';
		}

		$audioHtml = '';
		$isExistsAudioMsg = isset($row['audio_message_id']) && $row['audio_message_id'];
		if($isExistsAudioMsg) {
			$audioUrl = self::getUrl($row['audio_message_id']);
			$audioHtml = '<span class="im_audio_message">' .
							'<span id="' . $prf . $id . '" class="im_audio_message_loader" data-audio-message-file="' . $audioUrl .'" >' .
								'<i class="fa fa-play" aria-hidden="true"></i>' .
							'</span>' .
							'<span class="im_audio_message_process"></span>' .
							'<span class="im_audio_message_process_play"></span>' .
						'</span>';
			if ($br) {
				$audioHtml .= '<br>';
			}
		}
		return $audioHtml;
	}

}