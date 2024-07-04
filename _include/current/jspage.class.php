<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class JsPage extends CHtmlBlock
{
  	function action()
    {
        header('Content-type: text/javascript; charset=UTF-8');
        header("Cache-Control: public");
        header("Pragma: cache");
        header('Expires: '. date('D, d M Y H:i:s \G\M\T', time() + 60 * 60 * 24 * 365));
    }

	function parseBlock(&$html)
	{
        global $g, $p, $l;

        if (intval(get_param('get_lang'))) {
            $page = get_param('page', 'all');
            $html->setvar('page', $page);
            if (isset($l[$page])) {
                foreach ($l[$page] as $k => $v){
                    $html->setvar('key', $k);
                    $html->setvar('value', toJs($v));
                    $html->parse('lang_item', true);
                }
            }
        }

		parent::parseBlock($html);
    }

    static public function getTmplPath()
    {
        global $g;

        $nameFile = Common::sanitizeFilename(get_param('file', 'set_old.js'));
        $path = $g['tmpl']['tmpl_loaded_dir'] . 'js/';
        if (in_array($nameFile, array('set.js', 'set_old.js', 'set_language.js'))) {
            $path = $g['path']['dir_tmpl'] . 'common/js/';
        }

        return $path . $nameFile;
    }
}
