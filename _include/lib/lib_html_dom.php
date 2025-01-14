<?php

define('HDOM_TYPE_ELEMENT', 1);
define('HDOM_TYPE_COMMENT', 2);
define('HDOM_TYPE_TEXT', 3);
define('HDOM_TYPE_ENDTAG', 4);
define('HDOM_TYPE_ROOT', 5);
define('HDOM_TYPE_UNKNOWN', 6);
define('HDOM_QUOTE_DOUBLE', 0);
define('HDOM_QUOTE_SINGLE', 1);
define('HDOM_QUOTE_NO', 3);
define('HDOM_INFO_BEGIN', 0);
define('HDOM_INFO_END', 1);
define('HDOM_INFO_QUOTE', 2);
define('HDOM_INFO_SPACE', 3);
define('HDOM_INFO_TEXT', 4);
define('HDOM_INFO_INNER', 5);
define('HDOM_INFO_OUTER', 6);
define('HDOM_INFO_ENDSPACE', 7);

defined('DEFAULT_TARGET_CHARSET') || define('DEFAULT_TARGET_CHARSET', 'UTF-8');
defined('DEFAULT_BR_TEXT') || define('DEFAULT_BR_TEXT', "\r\n");
defined('DEFAULT_SPAN_TEXT') || define('DEFAULT_SPAN_TEXT', ' ');
defined('MAX_FILE_SIZE') || define('MAX_FILE_SIZE', 33554432);
define('HDOM_SMARTY_AS_TEXT', 1);

function fileGetDomHtml(
	$url,
	$use_include_path = false,
	$context = null,
	$offset = 0,
	$maxLen = -1,
	$lowercase = true,
	$forceTagsClosed = true,
	$target_charset = DEFAULT_TARGET_CHARSET,
	$stripRN = true,
	$defaultBRText = DEFAULT_BR_TEXT,
	$defaultSpanText = DEFAULT_SPAN_TEXT)
{
	if($maxLen <= 0) { $maxLen = MAX_FILE_SIZE; }

	$dom = new simpleHtmlDom(
		null,
		$lowercase,
		$forceTagsClosed,
		$target_charset,
		$stripRN,
		$defaultBRText,
		$defaultSpanText
	);

	/**
	 * For sourceforge users: uncomment the next line and comment the
	 * retrieve_url_contents line 2 lines down if it is not already done.
	 */
	/*$contents = file_get_contents(
		$url,
		$use_include_path,
		$context,
		$offset,
		$maxLen
	);*/
	//$contents = retrieve_url_contents($url);

    $contents = @urlGetContents($url, 'get', array(), 60);

	if (empty($contents) || strlen($contents) > $maxLen) {
		$dom->clear();
		return false;
	}

	return $dom->load($contents, $lowercase, $stripRN);
}

function strGetDomHtml(
	$str,
	$lowercase = true,
	$forceTagsClosed = true,
	$target_charset = DEFAULT_TARGET_CHARSET,
	$stripRN = true,
	$defaultBRText = DEFAULT_BR_TEXT,
	$defaultSpanText = DEFAULT_SPAN_TEXT)
{
	$dom = new simpleHtmlDom(
		null,
		$lowercase,
		$forceTagsClosed,
		$target_charset,
		$stripRN,
		$defaultBRText,
		$defaultSpanText
	);

	if (empty($str) || strlen($str) > MAX_FILE_SIZE) {
		$dom->clear();
		return false;
	}

	return $dom->load($str, $lowercase, $stripRN);
}

function dumpHtmlTree($node, $show_attr = true, $deep = 0)
{
	$node->dump($node);
}

function checkMetaImageSite($imgUrl, $checkParam = false)
{
    if(preg_match("/^.*\.(jpg|jpeg|png|gif)/i", $imgUrl)) {
        if ($checkParam) {
            $size = @custom_getimagesize($imgUrl);
            if ($size[0] > 400 && $size[1] > 400) {
                return array('width' => $size[0], 'height' => $size[1]);
            }
        } else {
            return true;
        }
    }
    return false;
}

function getMetaSite($url)
{

    $metaInfo = array(
        'url' => $url,
        'title' => '',
        'description' => '',
        'canonical' => '',
        'image' => '',
    );

    $html = fileGetDomHtml($url);

    if($html !== false && $html->innertext != '') {
        $head = $html->find('head');
        if ($head && isset($head[0])) {
            $head = $head[0];

            //"og:url" "og:title", "og:description", "og:url", "og:image", "og:site_name"
            //"twitter:title, "twitter:image"

            /* Canonical */
            $canonical = $head->find('meta[property=og:url"]');
            if ($canonical && isset($canonical[0])) {
                $metaInfo['canonical'] = trim($canonical[0]->content);
            }

            if (!$metaInfo['canonical']) {
                $canonical = $head->find('link[rel="canonical"]');
                if ($canonical && isset($canonical[0])) {
                    $metaInfo['canonical'] = $canonical[0]->href;
                }
            }
            /* Canonical */


            /* Title */
            $title = $head->find('meta[property=og:title"]');
            if ($title && isset($title[0])) {
                $metaInfo['title'] = trim($title[0]->content);
            }

            if (!$metaInfo['title']) {
                $title = $head->find('meta[name=twitter:title"]');
                if ($title && isset($title[0])) {
                    $metaInfo['title'] = trim($title[0]->content);
                }
            }

            if (!$metaInfo['title']) {
                $title = $head->find('title');
                if ($title && isset($title[0])) {
                    $metaInfo['title'] = trim($title[0]->innertext);
                }
            }

            if ($metaInfo['title']) {
                //Down for Maintenance 403
                //403 Forbidden
                //405 Not Allowed
                //Page Expired
                $prohibitedText = array(
                    'maintenance',
                    'forbidden',
                    'not allowed',
                    'page expired'
                );
                foreach ($prohibitedText as $value) {
                    if (stripos($metaInfo['title'], $value) !== false) {
                        $metaInfo['title'] = '';
                        break;
                    }
                }
            }
            /* Title */

            /* Description */
            $description = $head->find('meta[property="og:description"]');
            if ($description && isset($description[0])) {
                $metaInfo['description'] = trim($description[0]->content);
            }

            if (!$metaInfo['description']) {
                $description = $head->find('meta[name="description"]');
                if ($description && isset($description[0])) {
                    $metaInfo['description'] = trim($description[0]->content);
                }
            }
            /* Description */

            /* Image */
            $image = $head->find('meta[property="og:image"]');
            if ($image && isset($image[0])) {
                $metaInfo['image'] = $image[0]->content;
            }

            if (!$metaInfo['image']) {
                $image = $head->find('meta[name="twitter:image"]');
                if ($image && isset($image[0])) {
                    $metaInfo['image'] = $image[0]->content;
                }
            }
            /* Image */
        }

        if (!$metaInfo['image']) {
            $body = $html->find('body');
            if ($body && isset($body[0])) {
                $body = $body[0];
                $imageWidth = 0;
                $imageHeight = 0;
                $endSizeW = 400;
                $endSizeH = 400;

                $images = $body->find('img');
                foreach($images as $img) {
                    if ($img->src) {
                        $size = checkMetaImageSite($img->src, true);
                        if ($size) {
                            $metaInfo['image'] = $img->src;
                            break;

                            /*if ($size['width'] > $imageWidth && $size['height'] > $imageHeight) {
                                $imageWidth = $size['width'];
                                $imageHeight = $size['height'];
                                $metaInfo['image'] = $img->src;
                            }
                            if ($imageWidth > $endSizeW && $imageHeight > $endSizeH) {
                                $metaInfo['image'] = $img->src;
                                break;
                            }*/
                        }
                    }
                }
            }
        }

        $html->clear();
        unset($html);
    }

    return $metaInfo;
}