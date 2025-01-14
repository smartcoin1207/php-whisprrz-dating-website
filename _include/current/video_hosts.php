<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class VideoHosts
{
    static $autoplay = false;
    static $mobile = false;
    static $embedUrlShow = false;
    static $videoSiteTagStart = '';
    static $ext = 'mp4';
    static $items = array();
    static $imMsg = false;

    static function setEmbedUrlShow($embedUrlShow)
    {
        self::$embedUrlShow = $embedUrlShow;
    }

    static function getEmbedUrlShow()
    {
        return self::$embedUrlShow;
    }
    static function embedInfo($id, $type)
    {
        $infoReturn = null;

        $sql = 'SELECT info FROM aux_embed_vids
            WHERE id = ' . to_sql($id, 'Text');
        $info = DB::result($sql, 0, DB_MAX_INDEX);

        if($type == 'vimeo') {
            if(!$info) {
                $info = array();
                $url = 'http://vimeo.com/api/v2/video/' . $id . '.php';
                $vimeoInfo = @urlGetContents($url);
                if ($vimeoInfo) {
                    $info = unserialize($vimeoInfo);
                    if(isset($info[0]['thumbnail_medium'])) {
                        $sql = 'INSERT IGNORE INTO aux_embed_vids
                                   SET id = ' . to_sql($id, 'Text') . ',
                                  info = ' . to_sql($vimeoInfo, 'Text');
                        DB::execute($sql);
                    }
                }
            } else {
                $info = unserialize($info);
            }
            $infoReturn = array();
            if ($info) {
                $infoReturn['id'] = $id;
                $infoReturn['title'] = $info[0]['title'];
                $infoReturn['img'] = $info[0]['thumbnail_medium'];
            }
        }

        if($type == 'youtube') {

            libxml_use_internal_errors(true);

            if(!$info) {
                $info = array();
                $url = 'https://www.youtube.com/oembed?url=youtu.be/' . $id . '&format=xml';
                $youtubeInfo = @urlGetContents($url);
                if ($youtubeInfo) {
                    $info = (array) simplexml_load_string($youtubeInfo);

                    if(isset($info['title'])) {
                        $sql = 'INSERT IGNORE INTO aux_embed_vids
                                   SET id = ' . to_sql($id, 'Text') . ',
                                  info = ' . to_sql($youtubeInfo, 'Text');
                        DB::execute($sql);
                    }
                }
            } else {
                $info = (array) simplexml_load_string($info);
            }
            $infoReturn = array();
            if ($info) {
                $infoReturn['id'] = $id;
                $infoReturn['title'] = isset($info['title']) ? $info['title'] : '';
            }
        }

        return $infoReturn;
    }

    static public function setMobile($mobile)
    {
        self::$mobile = $mobile;
    }

    static public function getMobile()
    {
        return self::$mobile;
    }

    static public function setAutoplay($autoplay)
    {
        self::$autoplay = $autoplay;
    }

    static public function getAutoplay()
    {
        return self::$autoplay;
    }

    static public function filterToDb($text)
    {
        $text = self::textUrlToVideoCode($text);
        $text = self::_filterToDbYoutube($text);
        $text = self::_filterToDbYoutubeIframe($text);
        $text = self::_filterToDbVimeo($text);
        $text = self::_filterToDbMetacafe($text);
        return $text;
    }

    static public function getYoutubeCode($code)
    {
        $id = self::_filterOneToDbYoutube($code);
        if ($id != '') {
            return $id;
        }

        $id = self::_filterOneToDbYoutubeIframe($code);
        if ($id != '') {
            return $id;
        }
        return null;
    }

    static public function filterOneToDb($code)
    {
        $id = self::_filterOneToDbYoutube($code);
        if ($id != '') {
            return 'youtube:' . $id . '';
        }

        $id = self::_filterOneToDbYoutubeIframe($code);
        if ($id != '') {
            return 'youtube:' . $id . '';
        }

        $id = self::_filterOneToDbVimeo($code);
        if ($id != '') {
            return 'vimeo:' . $id . '';
        }
        return null;
    }
    static public function filterFromDb($text, $pretag = '', $posttag = '', $w = null)
    {
        $text = self::_filterFromDbYoutube($text, $pretag, $posttag, $w);
        $text = self::_filterFromDbVimeo($text, $pretag, $posttag, $w);
        $text = self::_filterFromDbMetacafe($text, $pretag, $posttag, $w);
        if (self::$videoSiteTagStart) {
            $pretag = self::$videoSiteTagStart;
        }
        $text = self::_filterFromDbSite($text, $pretag, $posttag, $w);
        return $text;
    }
    static public function filterOneFromDb($code, $w = null, $h = null)
    {
        $arr = explode(':', $code);
        $type = $arr[0];
        unset($arr[0]);
        $id = implode(':', $arr);
        switch ($type) {
            case 'site':
                return self::_filterOneFromDbSite($id, $w, $h);
            case 'youtube':
                return self::_filterOneFromDbYoutube($id, $w);
            case 'vimeo':
                return self::_filterOneFromDbVimeo($id, $w);
        }
    }
    static public function filterOneImageFromDb($code, $w = null)
    {
        $arr = explode(':', $code);
        $type = $arr[0];
        unset($arr[0]);
        $id = implode(':', $arr);
        switch ($type) {
            case 'site':
                return self::_filterOneImageFromDbSite($id, $w);
            case 'youtube':
                return self::_filterOneImageFromDbYoutube($id, $w);
            case 'vimeo':
                return self::_filterOneImageFromDbVimeo($id, $w);
        }
    }
    static public function filterOneImageBigFromDb($code, $w = null)
    {
        $arr = explode(':', $code);
        $type = $arr[0];
        unset($arr[0]);
        $id = implode(':', $arr);
        switch ($type) {
            case 'site':
                return self::_filterOneImageBigFromDbSite($id, $w);
            case 'youtube':
                return self::_filterOneImageBigFromDbYoutube($id, $w);
            case 'vimeo':
                return self::_filterOneImageBigFromDbVimeo($id, $w);
        }
    }
    static public function filterOneUrlFromDb($code, $w = null)
    {
        $arr = explode(':', $code);
        $type = $arr[0];
        unset($arr[0]);
        $id = implode(':', $arr);
        switch ($type) {
            case 'site':
                return self::_filterOneUrlFromDbSite($id, $w);
            case 'youtube':
                return self::_filterOneUrlFromDbYoutube($id, $w);
            case 'vimeo':
                return self::_filterOneUrlFromDbVimeo($id, $w);
        }
    }

    static protected function _filterOneToDbYoutube($code)
    {
        $startUrl = array('youtube.com/v/',
                          'youtube.com/watch?v=',
                          'youtube-nocookie.com/v/',
                          'http://youtu.be/',
                          'https://youtu.be/'
            );
        foreach ($startUrl as $value) {
            $startUrlPos = strpos($code, $value);
            if ($startUrlPos !== false) {
                $movieId = substr($code, $startUrlPos + mb_strlen($value,'UTF-8'), 11);
                return $movieId;
            }
        }
        return null;
    }

    static protected function _filterOneToDbYoutubeIframe($code)
    {
        $result = null;
        $pattern = '#\/\/www\.youtube\.com\/embed\/([A-Za-z0-9\-_]+)#i';
        preg_match($pattern, $code, $matches);
        if(is_array($matches) && isset($matches[1])) {
            $result = $matches[1];
        }
        $pattern = '#\/\/www\.youtube-nocookie\.com\/embed\/([A-Za-z0-9\-_]+)#i';
        preg_match($pattern, $code, $matches);
        if(is_array($matches) && isset($matches[1])) {
            $result = $matches[1];
        }

        return $result;
    }

    static protected function _filterOneToDbVimeo($code)
    {

        $startUrl = array('http://vimeo.com/moogaloop.swf?clip_id=',
                          'http://vimeo.com/',
                          'https://vimeo.com/');
        $endUrl = '&';
        $obj = $code;
        foreach ($startUrl as $value) {
            $startUrlPos = strpos($obj, $value);
            if ($startUrlPos !== false) {
                //Подрезаем начало и ищем конец юрл адреса
                $rObj = substr($obj, $startUrlPos + mb_strlen($value,'UTF-8'));
                $endUrlPos = strpos($rObj, $endUrl);
                $movieId = ($endUrlPos !== false) ? substr($rObj, 0, $endUrlPos) : $rObj;
                $mPars = urlGetContents('http://vimeo.com/api/v2/video/' . $movieId . '.json');
                $mPars = json_decode($mPars, true);
                $mPars = $mPars[0];
                $thumb = $mPars['thumbnail_small']; //http://ats.vimeo.com/582/744/58274409_100.jpg
                #$thumb = substr($thumb, strlen('http://ats.vimeo.com/'));
                #$thumb = substr($thumb, 0, strlen($thumb) - strlen('_100.jpg'));
                return $movieId . ':' . $thumb;
            }
        }

        $pattern = '#vimeo.com/video/(\d*)#';
        preg_match($pattern, $obj, $matches);
        if(isset($matches[1])) {
            $movieId = $matches[1];
            $mPars = urlGetContents('http://vimeo.com/api/v2/video/' . $movieId . '.json');
            $mPars = json_decode($mPars, true);
            $mPars = $mPars[0];
            $thumb = $mPars['thumbnail_small'];
            #$thumb = substr($thumb, 0, strlen($thumb) - strlen('_100.jpg'));
            return $movieId . ':' . $thumb;
        }
        return null;
    }

    static protected function _filterToDbYoutube($text)
    {
        //Ищем теги object
        $objs = grabs($text, '<object', '</object>', true);
        $startUrl = 'youtube.com/v/';
        $startUrlNew = 'youtube-nocookie.com/v/';
        foreach ($objs as $obj) {
            $movieId = self::_filterOneToDbYoutube($obj);
            if($movieId) {
                $text = str_replace($obj, "{youtube:$movieId}", $text);
            }
        }
        return $text;
    }

    static protected function _filterToDbYoutubeIframe($text)
    {
        $pattern = '#<iframe(.*)(?!<iframe)\/\/www\.youtube(\-nocookie)?\.com\/embed\/([A-Za-z0-9\-_]+?)(.*)</iframe>#Ui';
        $text = preg_replace($pattern, '{youtube:$3}', $text);
        return $text;
    }

    static protected function _filterToDbVimeo($text)
    {
        //Ищем теги object
        $objs = grabs($text, '<object', '</object>', true);
        $startUrl = 'http://vimeo.com/moogaloop.swf?clip_id=';
        $endUrl = '&';
        foreach ($objs as $obj) {
            //Ищем в найденом начало юрл адреса ролика
            $startUrlPos = strpos($obj, $startUrl);
            if ($startUrlPos !== false) {
                //Подрезаем начало и ищем конец юрл адреса
                $rObj = substr($obj, $startUrlPos + strlen($startUrl));
                $endUrlPos = strpos($rObj, $endUrl);
                if ($endUrlPos !== false) {
                    //Делаем замену
                    $movieId = substr($rObj, 0, $endUrlPos);
                    $text = str_replace($obj, "{vimeo:$movieId}", $text);
                }
            }
        }
        $objs = grabs($text, '<p><a href="http://vimeo.com', '</p>', true);
        foreach ($objs as $obj) {
            $text = str_replace($obj, "", $text);
        }

        $pattern = '#<iframe src="http://player.vimeo.com/video/(\d*)[\D](.*)</iframe>#Uis';
        $text = preg_replace($pattern, '{vimeo:$1}', $text);

        return $text;
    }
    static protected function _filterToDbMetacafe($text)
    {
        //Ищем теги object
        $objs = grabs($text, '<embed', '</embed>', true);
        $startUrl = 'http://www.metacafe.com/fplayer/';
        $endUrl = '.swf';
        foreach ($objs as $obj) {
            //Ищем в найденом начало юрл адреса ролика
            $startUrlPos = strpos($obj, $startUrl);
            if ($startUrlPos !== false) {
                //Подрезаем начало и ищем конец юрл адреса
                $rObj = substr($obj, $startUrlPos + strlen($startUrl));
                $endUrlPos = strpos($rObj, $endUrl);
                if ($endUrlPos !== false) {
                    //Делаем замену
                    $movieId = substr($rObj, 0, $endUrlPos);
                    $text = str_replace($obj, "{metacafe:$movieId}", $text);
                }
            }
        }
        $objs = grabs($text, '<br><font size = 1><a href="http://www.metacafe.co', '</font>', true);
        foreach ($objs as $obj) {
            $text = str_replace($obj, "", $text);
        }
        return $text;
    }
    static protected function _filterFromDbYoutube($text, $pretag, $posttag, $w)
    {
        /*$whRel = 480 / 385;
        if ($w == null) {
            $w = 480;
            $h = 385;
        } else {
            $h = round($w / $whRel);
        }*/
        $objs = grabs($text, '{youtube:', '}');

        $htmlUrlTemplate = '<br><a class="embed_video_url" target="_blank" href="https://www.youtube.com/watch?v={id}">{title}</a><br>';

        /*$style = ' width="' . $w . '" height="' . $h . '"';
        if (Common::isOptionActiveTemplate('wall_video_item_without_styles')) {
            $style = '';
        }*/
        foreach ($objs as $obj) {
            $info = false;
            $htmlUrl = '';

            $objOrig = $obj;//Fix for google translate
            $obj = trim($obj);
            //$html = '<object width="' . $w . '" height="' . $h . '"><param name="wmode" value="transparent"/><param  name="movie" value="//www.youtube.com/v/' . $obj . '&autoplay='.self::getAutoplay().'&hl=en_US&fs=1&"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed wmode="transparent" src="//www.youtube.com/v/' . $obj . '&autoplay='.self::getAutoplay().'&hl=en_US&fs=1&" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="' . $w . '" height="' . $h . '"></embed></object>';
            //$html = '<object'. $style .'><param name="wmode" value="transparent"/><param  name="movie" value="//www.youtube.com/embed/' . $obj . '?autoplay='.self::getAutoplay().'"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed wmode="transparent" src="//www.youtube.com/embed/' . $obj . '?autoplay='.self::getAutoplay().'" allowscriptaccess="always" allowfullscreen="true" width="' . $w . '" height="' . $h . '"></embed></object>';
            $html = self::_filterOneFromDbYoutube($obj, $w);
            if(self::getEmbedUrlShow()) {
                $info = self::embedInfo($obj, 'youtube');
                if ($info) {
                    $htmlUrl = Common::replaceByVars($htmlUrlTemplate, $info);
                    $html .= $htmlUrl;
                }
            }

            if(self::getMobile()) {
                if(!$info) {
                    if ($info) {
                        $info = self::embedInfo($obj, 'youtube');
                        $htmlUrl = Common::replaceByVars($htmlUrlTemplate, $info);
                    }
                }
                $style = 'width="240"';
                if (Common::isOptionActiveTemplate('im_send_image')) {
                    $style = '';
                }
                $html = '<a target="_blank" class="one_media_youtube_img" href="https://www.youtube.com/watch?v=' . $obj . '"><img ' . $style . ' src="' . self::_filterOneImageBigFromDbYoutube($obj, 1, 'mq') . '"></a>' . $htmlUrl;
            }
            $tag = '{youtube:' . $objOrig . '}';
            $tagHtml =  $pretag . $html . $posttag;
            $text = Common::getTextTagsToBr($text, $tag, $tagHtml);
            #$text = str_replace($tag, $pretag . $html . $posttag, $text);
        }
        return $text;
    }

    static protected function _filterFromDbVimeo($text, $pretag, $posttag, $w)
    {
        /*$whRel = 400 / 225;
        if ($w == null) {
            $w = 400;
            $h = 225;
        } else {
            $h = round($w / $whRel);
        }*/
        $objs = grabs($text, '{vimeo:', '}');

        $htmlUrlTemplate = '<br><a class="embed_video_url" target="_blank" href="http://vimeo.com/{id}">{title}</a><br>';

        foreach ($objs as $obj) {
            //$ids = explode(':', $obj);
            //$vid = $ids[0];

            $info = false;
            $htmlUrl = '';

            //$html = '<object width="' . $w . '" height="' . $h . '"><param name="wmode" value="transparent"/><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="//vimeo.com/moogaloop.swf?clip_id=' . $vid . '&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" /><embed wmode="transparent" src="//vimeo.com/moogaloop.swf?clip_id=' . $vid . '&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="' . $w . '" height="' . $h . '"></embed></object>';
            $html = self::_filterOneFromDbVimeo($obj, $w);
            if(self::getEmbedUrlShow()) {
                $info = self::embedInfo($obj, 'vimeo');
                if ($info) {
                    $htmlUrl = Common::replaceByVars($htmlUrlTemplate, $info);
                    $html .= $htmlUrl;
                }
            }

            if(self::getMobile()) {
                if(!$info) {
                    if ($info) {
                        $info = self::embedInfo($obj, 'vimeo');
                        $htmlUrl = Common::replaceByVars($htmlUrlTemplate, $info);
                    }
                }
                $html = '<a target="_blank" href="http://vimeo.com/' . $obj . '"><img width="240" src="' . $info['img'] . '"></a>' . $htmlUrl;
            }

            $tag = '{vimeo:' . $obj . '}';
            $tagHtml = $pretag . $html . $posttag;
            $text = Common::getTextTagsToBr($text, $tag, $tagHtml);
            #$text = str_replace($tag, $pretag . $html . $posttag, $text);
        }
        return $text;
    }
    static protected function _filterFromDbMetacafe($text, $pretag, $posttag, $w)
    {
        /*$whRel = 480 / 385;
        if ($w == null) {
            $w = 480;
            $h = 385;
        } else {
            $h = round($w / $whRel);
        }*/
        $objs = grabs($text, '{metacafe:', '}');

        foreach ($objs as $obj) {
            $objArr = explode('/', $obj);
            //$html = '<embed src=//www.metacafe.com/fplayer/' . $obj . '.swf width="' . $w . '" height="' . $h . '" wmode="transparent" allowFullScreen="true" allowScriptAccess="always" name="Metacafe_' . $objArr[0] . '" pluginspage="//www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed>';
            $html = self::_filterOneFromDbMetacafe($obj, $w);

            $tag = '{metacafe:' . $obj . '}';
            $tagHtml = $pretag . $html . $posttag;
            $text = Common::getTextTagsToBr($text, $tag, $tagHtml);
            #$text = str_replace($tag, $pretag . $html . $posttag, $text);
        }
        return $text;
    }


    static protected function _filterFromDbSite($text, $pretag, $posttag, $w)
    {
        $objs = grabs($text, '{site:', '}');
        foreach ($objs as $obj) {
            $html = self::_filterOneFromDbSite($obj, $w);
            $tag = '{site:' . $obj . '}';
            $tagHtml = $pretag . $html . $posttag;
            $text = Common::getTextTagsToBr($text, $tag, $tagHtml);
            #$text = str_replace($tag, $pretag . $html . $posttag, $text);
        }
        return $text;
    }

    static function getHtmlCodeOneFromSite($id, $w, $h = null, $isPlayerNative = false, $preload = 'none', $prfId = '')
    {
        return self::_filterOneFromDbSite($id, $w, $h, $isPlayerNative, $preload, $prfId);
    }

    static protected function _filterOneFromDbSite($id, $w, $h = null, $isPlayerNative = false, $preload = 'none', $prfId = '')
    {
        //$whRel = 444 / 333;
        $whRel = 444/278;
        if ($w == null) {
            $w = 444;
            //$h = 333;
            //$h2 = 308;
            //$h2 = 327;
        }
        if ($h === null) {
            //$h = ceil($w/1.7777777);
            $h = round($w/1.7777777);
            //$h = round($w/$whRel) - 28;
            //$h2 = $h - 25;
        }

        $style = ' width="' . $w . '" height="' . $h . '" ';
        $objStyle = ' <param name="width" value="' . $w . '"/>
                      <param name="height" value="' . $h . '"/> ';
        if (Common::isOptionActiveTemplate('wall_video_item_without_styles')) {
            $style = '';
            $objStyle = '';
        }

        global $g, $g_user, $p;
        //TODO это *** здесь делать запрос, продумать бы
        if(isset(self::$items[$id])) {
            $info = self::$items[$id];
        } else {
            $info = DB::row("SELECT * FROM vids_video WHERE id=".to_sql($id,"Number"),4);
        }

        global $sitePart;

        if(isset($sitePart) && $sitePart == 'administration') {
            $info['private'] = 0;
        }

        //$infotext = htmlentities("<b><p><font size='18' face='Arial' color='#C6E4FF'>".$info['subject']."</font></p></b><p><font size='12' face='Arial' color='#FFFFFF'>".$info['text']."</font></p>",ENT_QUOTES,"UTF-8");

        //$link = "http://".$_SERVER['HTTP_HOST'] . str_replace("\\", "", dirname($_SERVER['PHP_SELF'])) . "/";
        //$link = str_replace('administration/', '', $link);

        /*$code = '<object width="' . $w . '" height="' . $h . '" type="application/x-shockwave-flash" data="' . $link . '_server/videogallery/player.swf">
            <param name="movie" value="' . $link . '_server/videogallery/player.swf" />
            <param name="codebase" value="//download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0"/>
            <param name="width" value="' . $w . '"/>
            <param name="height" value="' . $h . '"/>
            <param name="quality" value="high"/>
            <param name="align" value="middle"/>
            <param name="play" value="true"/>
            <param name="loop" value="true"/>

            <param name="wmode" value="opaque"/>
            <param name="devicefont" value="false"/>
            <param name="bgcolor" value="#2e2e2e"/>
            <param name="allowFullScreen" value="true"/>
            <param name="allowScriptAccess" value="sameDomain"/>

            <param name="flashvars" value="forcewidth=' . $w . '&forceheight=' . $h2 . '&skin=black&fullscreenbutton=on&infobutton=on&videopath=../../' . $g['dir_files'] . 'video/' . $id . '.flv&playonload=on&initialvolume=175&startbufferonload=off&defaultstretch=stretch to fit&buffersize=2&infotext='.$infotext.'"/>
            <embed src="' . $link . '_server/videogallery/player.swf" type="application/x-shockwave-flash"
                width="' . $w . '"
                height="' . $h . '"
                wmode="transparent"
                allowFullScreen="true"
                flashvars="forcewidth=' . $w . '&forceheight=' . $h2 . '&skin=black&fullscreenbutton=on&infobutton=on&videopath=../../' . $g['dir_files'] . 'video/' . $id . '.flv&playonload=on&initialvolume=175&startbufferonload=off&defaultstretch=stretch to fit&buffersize=2&infotext='.$infotext.'" >
            </embed>
        </object>';*/

        $autoplayEmbed = '';
        $autoplay = '';
        if (self::getAutoplay()) {
            $autoplayEmbed = 'autoPlay=true&';
            $autoplay = 'autoplay';
        }

        $videoFile = User::getVideoFile($info, 'video_src', '');
        //popcorn modified s3 bucket video
        if(getFileDirectoryType('video') == 2) {
            $videoUrl =  custom_getFileDirectUrl($g['path']['url_main'] . $g['dir_files'] . $videoFile);
        } else {
            $videoUrl = $g['path']['url_main'] . $g['dir_files'] . $videoFile;
        }
        $posterFile = User::getVideoFile($info, 'src', '');
        $poster = $g['path']['url_main'] . $g['dir_files'] . $posterFile;

        $videoVolume = get_cookie('videojs_volume', true);
        if ($videoVolume == '') {
            $videoVolume = 0.7;
        } elseif ($videoVolume > 1){//Fix
            $videoVolume = round($videoVolume/100, 1);
        }
        if (Common::getOption('video_player_type') == 'player_custom' && !$isPlayerNative) {
            //$setupOption = array('preload' => 'none');
            //$setupOption = json_encode($setupOption);
            //$setupOption = '{&quot;aspectRatio&quot;:&quot;' . $w . ':' . $h . '&quot;}';
            $setupOption = '{}';

            $code='<video id="user_video_' . $id . $prfId . '" class="video-js vjs-default-skin" data-setup=\'' . $setupOption . '\' preload="' . $preload . '" poster="'.$poster. '"  controls="controls" controlsList="nodownload" ' . $style . ' ' . $autoplay . '>
                    <source src="'. $videoUrl . '"  />
                    <p class="vjs-no-js">' . l('wall_video_is_not_supported') . '</p>
                   </video>';
            if ($p != 'vids_video_edit.php') {
                /*$code .="<script>$(function(){
                            videoPlayers['$id']=videojs('#user_video_{$id}').ready(function(){
                                var pl=$('#user_video_{$id}'),
                                    blOldTmplWall=pl.closest('.blogs_video');
                                    console.log(blOldTmplWall);
                                if(blOldTmplWall[0]){
                                    blOldTmplWall.fadeTo(100,1);
                                }
                                this.volume({$videoVolume});
                                this.on('volumechange',function(){
                                    setCookie('videojs_volume', this.volume());
                                }).on('fullscreenchange', function(){
                                    var blWillChange=pl.closest('.wall_item');
                                    if(blWillChange[0]){
                                        var isWillChange=blWillChange.css('will-change')=='unset';
                                        blWillChange.css('will-change', isWillChange?'transform':'unset');
                                    }
                                }).on('ended', function() {
                                    this.load();
                                });
                            })
                        })</script>";*/
                $code .="<script>$(function(){initCustomVideoPlayer('{$id}{$prfId}', {$videoVolume})})</script>";
            } else {
                $code .="<script>$(function(){initCustomVideoPlayerAdmin('{$id}{$prfId}', {$videoVolume})})</script>";
            }
        } else {
            $link = "http://".$_SERVER['HTTP_HOST'] . str_replace("\\", "", dirname($_SERVER['PHP_SELF'])) . "/";
            $link = str_replace('administration/', '', $link);

            $clearUrl = explode('?', $videoUrl);
            $format = mb_strtolower(pathinfo($clearUrl[0], PATHINFO_EXTENSION));

            $videoUrlEmbed = '../../' . $g['dir_files'] . $videoFile;
            $posterUrlEmbed = Common::urlSite() . $g['dir_files'] . $posterFile;

            $flashvars = $autoplayEmbed . 'buffer=10&videoURL=' . $videoUrlEmbed . '&urlBg=' . $posterUrlEmbed;
            $code = '<object ' . $style . ' type="application/x-shockwave-flash" data="' . $link . '_server/videogallery/player.swf">
                        <param name="movie" value="' . $link . '_server/videogallery/player.swf" />
                        <param name="codebase" value="//download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0"/>
                        ' . $objStyle . '
                        <param name="quality" value="high"/>
                        <param name="align" value="middle"/>
                        <param name="play" value="true"/>
                        <param name="loop" value="true"/>

                        <param name="wmode" value="opaque"/>
                        <param name="devicefont" value="false"/>
                        <param name="bgcolor" value="#2e2e2e"/>
                        <param name="allowFullScreen" value="true"/>
                        <param name="allowScriptAccess" value="sameDomain"/>

                        <param name="flashvars" value="' . $flashvars . '"/>
                        <embed src="' . $link . '_server/videogallery/player.swf" type="application/x-shockwave-flash"
                            ' . $style . '
                            wmode="transparent"
                            allowFullScreen="true"
                            flashvars="' . $flashvars . '"
                        ></embed>
                    </object>';
            $code = '';

            if ($format != 'flv') {
                /*$posterHtml = '';
                if (Common::isOptionActiveTemplate('video_poster_app') && Common::isApp()) {
                    $posterHtml = '<div class="video_native_poster" style="background-image: url(\'' . $poster . '\')">
                                       <button class="play_button" type="button" aria-live="polite"></button>
                                   </div>';
                }*/
                $code = '<video class="video_native" id="user_video_' . $id . $prfId . '" ' . $autoplay . ' controls ' . (Common::isAppIos() ? 'playsinline' : '') . ' controlsList="nodownload" poster="' . $poster . '" preload="' . $preload . '" ' . $style . '>
                            <source src="' . $videoUrl . '" type="video/mp4">'
                            . $code .
                        '</video>';
                $code .="<script>$(function(){initNativeVideoPlayer('{$id}{$prfId}')})</script>";
            }
        }
        return str_replace(array("\r", "\n", "\t"), "", $code);
    }
    static protected function _filterOneImageFromDbSite($id, $w)
    {
        $w = 160;
        $h = 120;
        global $g;
        return $g['path']['url_files'] . 'video/' . $id . '.jpg';
    }
    static protected function _filterOneImageBigFromDbSite($id, $w)
    {
        $w = 286;
        $h = 161;
        global $g;
        //width="' . $w . '" height="' . $h . '"
        return $g['path']['url_files'] . 'video/' . $id . '_b.jpg';
    }

    static protected function _filterOneFromDbYoutube($id, $w)
    {
        $whRel = 480/385;
        if ($w == null) {
            $w = 480;
            $h = 385;
        } else {
            $h = round($w/$whRel);
        }

        $style = ' width="' . $w . '" height="' . $h . '"';
        if (Common::isOptionActiveTemplate('wall_video_item_without_styles')
            || (Common::isOptionActiveTemplate('im_send_image') && self::$imMsg)) {
            $style = '';
        }
        return '<iframe class="one_media_youtube" ' . $style . ' src="https://www.youtube.com/embed/' . $id . '?feature=player_embedded&autoplay=' . self::getAutoplay() . '" frameborder="0" allowfullscreen></iframe>';
        /*
        <object width="' . $w . '" height="' . $h . '"><param name="wmode" value="transparent"/><param name="movie" value="http://www.youtube.com/v/' . $id . '&autoplay='.self::getAutoplay().'&hl=en_US&fs=1&"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed wmode="transparent" src="http://www.youtube.com/v/' . $id . '&autoplay='.self::getAutoplay().'&hl=en_US&fs=1&" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="' . $w . '" height="' . $h . '"></embed></object>
        */
    }
    static protected function _filterOneImageFromDbYoutube($id, $w = 160, $quality = 'mq')
    {
        $w = 160;
        $h = 120;

        $movieId = explode('?', $id);

        return '//i4.ytimg.com/vi/' . $movieId[0] . '/' . $quality . 'default.jpg';
    }
    static protected function _filterOneImageBigFromDbYoutube($id, $w = 286, $quality = 'mq')
    {
        $w = 286;
        $h = 161;

        $movieId = explode('?', $id);
        $movieId = trim($movieId[0]);

        return 'https://i4.ytimg.com/vi/' . $movieId . '/' . $quality . 'default.jpg';

        /*$pathVideo = 'https://i.ytimg.com/vi/';
        $previews = array('mqdefault.jpg', 'hqdefault.jpg', 'maxresdefault.jpg');
        foreach ($previews as $preview) {
            $urlPreview = $pathVideo . $movieId . '/' . $preview;
            if (file_exists($urlPreview)) {
                break;
            }
        }
        return $urlPreview;*/
    }


    static protected function _filterOneFromDbVimeo($id, $w)
    {
        $whRel = 400/225;
        if ($w == null) {
            $w = 400;
            $h = 225;
        } else {
            $h = round($w/$whRel);
        }

        $ids = explode(':', $id);
        $vid = $ids[0];

        $style = ' width="' . $w . '" height="' . $h . '"';
        if (Common::isOptionActiveTemplate('wall_video_item_without_styles')) {
            $style = '';
        }

        return '<iframe class="one_media_vimeo" src="https://player.vimeo.com/video/' . $vid . '?autoplay=' . self::getAutoplay() . '&byline=0&portrait=0&badge=0" ' . $style . ' frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
    }

    static protected function _filterOneImageFromDbVimeo($id, $w)
    {
        $w = 160;
        $h = 120;
        $ids = explode(':', $id);
        $iid = $ids[1];
        $start = 'http://b.vimeocdn.com/';
        if(strpos($iid, 'http') !== false) {
            $start = 'http://';
            $iid = isset($ids[2]) ? $ids[2] : $start . $ids[1];
        }
        return $iid;
    }

    static protected function _filterOneImageBigFromDbVimeo($id, $w = 286, $quality = '')
    {
        $w = 286;
        $h = 161;
        $ids = explode(':', $id);
        if(isset($ids[1])) {
            $iid = $ids[1];
        } else {
            $iid = $ids[0];
        }

        $start = 'http://b.vimeocdn.com/';
        if(strpos($iid, 'http') !== false) {
            $start = 'http://';
            $iid = isset($ids[2]) ? $ids[2] : $start . $ids[1];
        }

        return $iid;
    }

    static protected function _filterOneFromDbMetacafe($id, $w)
    {
        $whRel = 480 / 385;
        if ($w == null) {
            $w = 480;
            $h = 385;
        } else {
            $h = round($w / $whRel);
        }

        $style = ' width="' . $w . '" height="' . $h . '"';
        if (Common::isOptionActiveTemplate('wall_video_item_without_styles')) {
            $style = '';
        }

        return '<iframe class="one_media_metacafe"  ' . $style . ' src="http://www.metacafe.com/embed/' . $id . '/Metacafe_' . $id . '/" frameborder="0" allowfullscreen></iframe>';
    }

    static function textUrlToVideoCode($text)
    {
        $text = self::textUrlToYoutube($text);
        $text = self::textUrlToVimeo($text);
        $text = self::textUrlToMetacafe($text);

        return $text;
    }

    static function textUrlToYoutube($text)
    {
        //http://www.youtube.com/watch?v=I1Yvf3kDzdA
        $embedCode = '{youtube:$2}';

        $text = preg_replace('~
            # Match non-linked youtube URL in the wild. (Rev:20111012)
            (https?://)?         # Required scheme. Either http or https.
            (?:[0-9A-Z\-]+\.)? # Optional subdomain.
            (?:               # Group host alternatives.
              youtu\.be/      # Either youtu.be,
            | youtube\.com    # or youtube.com followed by
              \S*             # Allow anything up to VIDEO_ID,
              [^\w\-\s]       # but char before ID is non-ID char.
            )                 # End host alternatives.
            ([\w\-]{11})      # $1: VIDEO_ID is exactly 11 chars.
            (?=[^\w\-]|$)     # Assert next char is non-ID or EOS.
            (?!               # Assert URL is not pre-linked.
              [?=&+%\w]*      # Allow URL (query) remainder.
              (?:             # Group pre-linked alternatives.
                [\'"][^<>]*>  # Either inside a start tag,
              | </a>          # or inside <a> element text contents.
              )               # End recognized pre-linked alts.
            )                 # End negative lookahead assertion.
            [?=&+%\w.-]*        # Consume any URL (query) remainder.
            ~ix',
            $embedCode,
            $text);
        return $text;
    }

    static function textUrlToVimeo($text)
    {
        //https://vimeo.com/260792023
        $embedCode = '{vimeo:$2}';

        $text = preg_replace('~
            # Match non-linked youtube URL in the wild. (Rev:20111012)
            (https?://)?         # Required scheme. Either http or https.
            (?:[0-9A-Z\-]+\.)? # Optional subdomain.
            (?:               # Group host alternatives.
              vimeo\.com/      # but char before ID is non-ID char.
            )                 # End host alternatives.
            (\d+)      # $1: VIDEO_ID is only numbers.
            #[?=&+%\w]*
            (\S+[^\s.,>\)\];\'"!\?])*
            [/]*
            ~ix',
            $embedCode,
            $text);

        return $text;
    }

    static function textUrlToMetacafe($text)
    {
        //http://www.metacafe.com/watch/4308480/ufo_pryamid_attempting_to_land/
        //http://www.metacafe.com/watch/cb-ouYEV58MOwdz/santorum_wins_key_evangelical_endorsement/
        $embedCode = '{metacafe:$2}';

        $text = preg_replace('~
            (http?://)?        # Required scheme. Either http or https.
            (?:www\.)?
            (?:[0-9A-Z\-]+\.)? # Optional subdomain.
            (?:                # Group host alternatives.
              metacafe\.com/watch/     # but char before ID is non-ID char.
            )                  # End host alternatives.
            ([\w\-]+)          # $1: VIDEO_ID is only numbers.
            #[?=&+%\w]*
            (\S+[^\s.,>\)\];\'"!\?])*
            [/]*
            ~ix',
            $embedCode,
            $text);

        return $text;
    }

    static function filterFromDbYoutube($text, $pretag, $posttag, $w = null)
    {
        return self::_filterFromDbYoutube($text, $pretag, $posttag, $w);
    }
}

/*
youtube
http://www.youtube.com/watch?v=I1Yvf3kDzdA&feature=related
<object width="480" height="385"><param name="movie" value="http://www.youtube.com/v/I1Yvf3kDzdA&autoplay='.self::getAutoplay().'&hl=en_US&fs=1&"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/I1Yvf3kDzdA&autoplay='.self::getAutoplay().'&hl=en_US&fs=1&" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="385"></embed></object>

vimeo
http://vimeo.com/7405114
<object width="400" height="275"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=7405114&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" /><embed src="http://vimeo.com/moogaloop.swf?clip_id=7405114&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="400" height="275"></embed></object><p><a href="http://vimeo.com/7405114">Zen Coding v0.5</a> from <a href="http://vimeo.com/user2060676">Sergey Chikuyonok</a> on <a href="http://vimeo.com">Vimeo</a>.</p>

metacafe
http://www.metacafe.com/watch/4308480/ufo_pryamid_attempting_to_land/
<embed src=http://www.metacafe.com/fplayer/4308480/ufo_pryamid_attempting_to_land.swf width="400" height="345" wmode="transparent" allowFullScreen="true" allowScriptAccess="always" name="Metacafe_4308480" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"> </embed><br><font size = 1><a href="http://www.metacafe.com/watch/4308480/ufo_pryamid_attempting_to_land/">UFO Pryamid Attempting to Land</a> - <a href="http://www.metacafe.com/">The funniest home videos are here</a></font>

yahoo video
http://video.yahoo.com/network/100284668?v=7143414
<div><object width="512" height="322"><param name="movie" value="http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.46" /><param name="allowFullScreen" value="true" /><param name="AllowScriptAccess" VALUE="always" /><param name="bgcolor" value="#000000" /><param name="flashVars" value="id=18599061&vid=7143414&lang=en-us&intl=us&thumbUrl=http%3A//l.yimg.com/a/i/us/sch/cn/video04/7143414_rnda988eb76_18.jpg&embed=1&ap=9460582" /><embed src="http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.46" type="application/x-shockwave-flash" width="512" height="322" allowFullScreen="true" AllowScriptAccess="always" bgcolor="#000000" flashVars="id=18599061&vid=7143414&lang=en-us&intl=us&thumbUrl=http%3A//l.yimg.com/a/i/us/sch/cn/video04/7143414_rnda988eb76_18.jpg&embed=1&ap=9460582" ></embed></object><br /><a href="http://video.yahoo.com/watch/7143414/18599061">WTF? Celebs on Chat Roulette ?!</a> @ <a href="http://video.yahoo.com" >Yahoo! Video</a></div>
$text = '
text

youtube
http://www.youtube.com/watch?v=I1Yvf3kDzdA&feature=related
<object width="480" height="385"><param name="movie" value="http://www.youtube.com/v/I1Yvf3kDzdA&autoplay='.self::getAutoplay().'&hl=en_US&fs=1&"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/I1Yvf3kDzdA&autoplay='.self::getAutoplay().'&hl=en_US&fs=1&" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="385"></embed></object>
vimeo
http://vimeo.com/7405114
<object width="400" height="275"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=7405114&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" /><embed src="http://vimeo.com/moogaloop.swf?clip_id=7405114&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="400" height="275"></embed></object><p><a href="http://vimeo.com/7405114">Zen Coding v0.5</a> from <a href="http://vimeo.com/user2060676">Sergey Chikuyonok</a> on <a href="http://vimeo.com">Vimeo</a>.</p>
metacafe
http://www.metacafe.com/watch/4308480/ufo_pryamid_attempting_to_land/
<embed src=http://www.metacafe.com/fplayer/4308480/ufo_pryamid_attempting_to_land.swf width="400" height="345" wmode="transparent" allowFullScreen="true" allowScriptAccess="always" name="Metacafe_4308480" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"> </embed><br><font size = 1><a href="http://www.metacafe.com/watch/4308480/ufo_pryamid_attempting_to_land/">UFO Pryamid Attempting to Land</a> - <a href="http://www.metacafe.com/">The funniest home videos are here</a></font>
test
';
p(
//$text,
VideoHosts::filterToDb($text).
VideoHosts::filterFromDb(VideoHosts::filterToDb($text), '', '', 200)
);
die();
*/