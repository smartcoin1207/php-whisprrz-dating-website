<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class PageInfo extends CHtmlBlock
{
    static $info = array();

	static function getInfo($page) {
		global $g;

        $lang = get_param('lang', $g['lang_loaded']);
        $sql = 'SELECT * FROM info
            WHERE page = ' . to_sql($page) . '
                AND lang = ' . to_sql($lang);
        $row = DB::row($sql);

        if(!$row && $lang != 'default') {
            $sql = 'SELECT * FROM info
                WHERE page = ' . to_sql($page) . '
                    AND lang = "default"';
            $row = DB::row($sql);
        }

		return $row;
	}

    function action()
    {
        global $l;
        global $p;

		self::$info = self::getInfo(get_param('page'));

        if(!self::$info) {
            Common::toHomePage();
        }

        $l[$p]['header_title'] = self::$info['title'];
    }

	function parseBlock(&$html)
	{
        if(self::$info) {
            foreach(self::$info as $k => $v) {
                $html->setvar('info_' . $k, $v);
            }

            $page = get_param('page');
            if ($page && $html->varExists('url_page_info')) {
                $urls = array('term_cond' => 'terms',
                              'priv_policy' => 'privacy_policy');
                if (isset($urls[$page])) {
                    $html->setvar('url_page_info', Common::pageUrl($urls[$page]));
                }
            }
        }

		parent::parseBlock($html);
	}
}