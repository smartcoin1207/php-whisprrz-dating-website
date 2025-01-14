<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include(__DIR__ . '/starter.php');

$g['path']['url_main'] = "../";
$sitePart = 'partner';

include(__DIR__ . '/start.php');

if(!Common::isOptionActive('partner')) {
    redirect('../');
}

if (get_session("partner_id") != '') {
	$area = "login";
	$cmd = get_param("cmd", "");
	if ($cmd == "logout") {
		set_session("partner_id", "");
		redirect("index.php");
	}
    $sql = 'SELECT * FROM partner '
        . 'WHERE partner_id = ' . to_sql(get_session('partner_id'), 'Number');
    $partnerInfo = DB::row($sql);

    if(!$partnerInfo) {
		set_session("partner_id", "");
		redirect("index.php");
    }

    if($partnerInfo['lang'] != $g['main']['lang_loaded']) {
        // update language value
        $sql = 'UPDATE partner '
            . 'SET lang = ' . to_sql($g['main']['lang_loaded'])
            . 'WHERE partner_id = ' . to_sql(get_session('partner_id'), 'Number');
        DB::execute($sql);
    }
} else {
	$area = "public";
	$pages_zone = array("index.php", "tips.php", "faq.php", "terms.php", "contact.php", "forget_password.php");
	$access = "N";
	foreach ($pages_zone as $k => $v) {
		if ($p == $v) {
			$access = "Y";
		}
	}
	if ($access == "N") {
		Common::toLoginPage();//redirect("index.php");
	}
}

class CPartnerHeader extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g;
		global $area;
		global $p;

                $html->setvar('header_favicon', Common::getfaviconSiteHtml());
                $html->setvar('header_url_logo', Common::getUrlLogo('logo', 'partner'));
		$dir = $g['path']['dir_lang'] . "partner/";
		if (is_dir($dir))
		{
	   		if ($dh = opendir($dir))
	   		{
				while (($file = readdir($dh)) !== false)
				{
					if (is_dir($dir . $file) and $file != "." and $file != "..")
					{
						$html->setvar("language_value", $file);
						$html->setvar("language_title", ucfirst($file));
						$html->parse("language", true);
					}
				}
				closedir($dh);
			}
		}
		$dir = $g['path']['dir_tmpl'] . "partner/";
		if (is_dir($dir))
		{
	   		if ($dh = opendir($dir))
	   		{
				while (($file = readdir($dh)) !== false)
				{
					if (is_dir($dir . $file) and $file != "." and $file != "..")
					{
						$html->setvar("template_value", $file);
						$html->setvar("template_title", ucfirst($file));
						$html->parse("template", true);
					}
				}
				closedir($dh);
			}
		}
		$html->parse("view", true);

		$html->parse("auth");

        Common::devCustomJs($html);

        $vars = array('year' => date('Y'));
        $html->setvar('footer_copyright', lSetVars('footer_copyright', $vars));

		parent::parseBlock($html);
	}
}
class CPartnerFooter extends CPartnerHeader
{

}
class CbannerP extends CHtmlBlock
{
	function parseBlock(&$html)
	{
		global $g;
        if (User::isPaid(guid())){
            $banner = get_banner("partner");
            if ($banner != '')
            {
                $html->setvar("banner_partner", $banner);
                $html->parse("banner_partner", true);
                parent::parseBlock($html);
            }
        }
	}
}

class CPartnerInfoPage extends CHtmlBlock
{
    var $table = 'tips';
    var $nl2br = true;

    public function getNl2br()
    {
        return $this->nl2br;
    }

    public function setNl2br($nl2br)
    {
        $this->nl2br = $nl2br;
    }

    function setTable($table)
    {
        $this->table = $table;
    }

    function getTable()
    {
        return $this->table;
    }

	function parseBlock(&$html)
	{
        $sql = "SELECT * FROM partner_{$this->getTable()}
            WHERE lang = " . to_sql(Common::getOption('lang_loaded', 'main')) . "
            ORDER BY id";
        DB::query($sql);
		while ($row = DB::fetch_row())
		{
			$html->setvar('id', $row['id']);
			$html->setvar('name', $row['name']);
            $text = $row['text'];
            if($this->getNl2br()) {
                $text = nl2br($text);
            }
			$html->setvar('text', $text);

			$html->parse('show', true);
			$html->parse('hide', true);
			$html->parse('question', true);
		}
		parent::parseBlock($html);
	}
}