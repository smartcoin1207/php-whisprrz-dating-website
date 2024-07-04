<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
if (Common::isMultisite()) {
    redirect('home.php');
}
$bb_array = array("html", "head", "body", "textarea");
function bb_encode($h)
{
	global $bb_array;

    return he($h);

	foreach ($bb_array as $v)
	{
		$rs = grabs($h, "<" . $v, ">");
		#print_r($rs);
		foreach ($rs as $v2)
		{
			$v2 = "<" . $v . $v2 . ">";
			$v2en = str_replace("<", "[", $v2);
			$v2en = str_replace(">", "]", $v2en);
			$h = str_replace($v2, $v2en, $h);
		}
		$h = str_replace("</" . $v . ">", "[/" . $v . "]", $h);
	}
	return $h;
}
function bb_decode($h)
{
	global $bb_array;

    /*
	foreach ($bb_array as $v)
	{
		$rs = grabs($h, "[" . $v, "]");
		#print_r($rs);
		foreach ($rs as $v2)
		{
			$v2 = "[" . $v . $v2 . "]";
			$v2en = str_replace("[", "<", $v2);
			$v2en = str_replace("]", ">", $v2en);
			$h = str_replace($v2, $v2en, $h);
		}
		$h = str_replace("[/" . $v . "]", "</" . $v . ">", $h);
	}
     */

	$rs = grabs($h, "&PHPSESSID=", "\"");
	foreach ($rs as $v2) $h = str_replace("&PHPSESSID=" . $v2 . "\"", "\"", $h);
	$rs = grabs($h, "&PHPSESSID=", "'");
	foreach ($rs as $v2) $h = str_replace("&PHPSESSID=" . $v2 . "'", "'", $h);
	$rs = grabs($h, "&PHPSESSID=", " ");
	foreach ($rs as $v2) $h = str_replace("&PHPSESSID=" . $v2 . " ", " ", $h);
	$rs = grabs($h, "&PHPSESSID=", ">");
	foreach ($rs as $v2) $h = str_replace("&PHPSESSID=" . $v2 . ">", ">", $h);
	$rs = grabs($h, "PHPSESSID=", "\"");
	foreach ($rs as $v2) $h = str_replace("PHPSESSID=" . $v2 . "\"", "\"", $h);
	$rs = grabs($h, "PHPSESSID=", "'");
	foreach ($rs as $v2) $h = str_replace("PHPSESSID=" . $v2 . "'", "'", $h);
	$rs = grabs($h, "PHPSESSID=", " ");
	foreach ($rs as $v2) $h = str_replace("PHPSESSID=" . $v2 . " ", " ", $h);
	$rs = grabs($h, "PHPSESSID=", ">");
	foreach ($rs as $v2) $h = str_replace("PHPSESSID=" . $v2 . ">", ">", $h);
	#$rs = grabs($h, "=\"http://", "{url");
	#foreach ($rs as $v2) $h = str_replace("=\"http://" . $v2 . "{url", "=\"{url", $h);
	#$rs = grabs($h, "='http://", "{url");
	#foreach ($rs as $v2) $h = str_replace("='http://" . $v2 . "{url", "='{url", $h);
	#$rs = grabs($h, "=http://", "{url");
	#foreach ($rs as $v2) $h = str_replace("=http://" . $v2 . "{url", "={url", $h);
	return $h;
}

class CAdminTemplateEdit extends CHtmlBlock
{
	var $message_template = "";

	function action()
	{
		global $g;
		$cmd = get_param("cmd", "");
		$part = get_param("part", "main");
		$lang = get_param("lang", "default");
		$lang_page = get_param("lang_page", "all");
		$tmpl_type = get_param("tmpl_type", "html");

		if ($tmpl_type == "") $tmpl_type2 = "";
		elseif ($tmpl_type == "css") $tmpl_type2 = "css/";
		elseif ($tmpl_type == "js") $tmpl_type2 = "js/";
		else $tmpl_type2 = "";

        $part = get_param("part", "main");
        $tmpl = get_param("tmpl", "default");

        $tmplDir = Common::tmplPath($part, $g['path']['dir_tmpl']);
        $tmplPart = $part;

        $tmpl_area = get_param("tmpl_area", ".");
        $tmpl_page = get_param("tmpl_page", "");

        $pt = pathinfo($tmpl_page);
        if (empty($pt['extension'])) $tmpl_page .= ".html";

        $filename = realpath($tmplDir . $tmplPart . "/". $tmpl . "/" . $tmpl_area . "/" . $tmpl_type2 . $tmpl_page);

		if ($cmd == "edit_template")
		{
			$h = get_param("html", "");
			$to = bb_decode($h);

			#@chmod($g['path']['dir_tmpl'] . $part . "/". $tmpl . "/" . $tmpl_area . "/" . $tmpl_type2 . $tmpl_page, 0777);
			if ($filename && is_writable($filename) && $this->isTemplateFile($filename))
			{
                if(!file_exists($filename . '_src')) {
                    copy($filename, $filename . '_src');
                }

			    if (!$handle = @fopen($filename, 'w'))
			    {
			        $this->message_template .= "Can't open file (" . $filename . ").<br />";
			    }
			    if (fwrite($handle, $to) === FALSE)
			    {
			        $this->message_template .= "Can't write to file(" . $filename . ".).<br />";
			    }
			    else
			    {
			    	#redirect("language_edit.php?part=" . $part . "&lang=" . $lang . "&lang_page=" . $lang_page);
			    }
		    	@fclose($handle);

                @file_put_contents($filename . '_' . date('Ymd_H'), $to);

				$action = "";
				if($this->message_template=="") $action = "action=saved&";
		    	redirect("template_edit.php?".$action."part=" . $part . "&tmpl=" . $tmpl . "&tmpl_area=" . $tmpl_area . "&tmpl_type=" . $tmpl_type . "&tmpl_page=" . $tmpl_page);
			}
			else
			{
				$this->message_template .= "Can't open file (" . $filename . ").<br />";
			}
		} elseif ($cmd == 'restore') {
            if ($filename && is_writable($filename) && $this->isTemplateFile($filename)) {
                adminFileBackupRestore($filename);
            }
        }
	}

    function excludeNotAvailableTmpl($part, $tmpl)
	{
        if ($part == 'mobile') {
            $tmplOptionSet = loadTemplateSettings('mobile', $tmpl, 'set');
            return $tmplOptionSet != $this->tmplOptionSet;
        } else {
            return false;
        }

    }

	function parseBlock(&$html)
	{
		global $g;
		global $l;
		global $p;
		global $area;

        $part = get_param("part", "main");
        $tmpl = get_param("tmpl", str_replace('/', '', str_replace($g['path']['dir_tmpl'] . "main/", '', $g['tmpl']['dir_tmpl_main'])));

        $this->tmplOptionSet = Common::getOption('set', 'template_options');
        $this->tmplOptionSettigs = isOptionActiveLoadTemplateSettings('template_edit_settings', null, $part, $tmpl);

		$html->setvar("part", $part);
		$html->setvar("part_title", ucfirst($part));

        $tmplDir = $g['path']['dir_tmpl'];
        $tmplPart = $part;

		$dir = $tmplDir . $tmplPart . '/';
		$langs = array();
		if (is_dir($dir)) {
	   		if ($dh = opendir($dir)) {
		        while (($file = readdir($dh)) !== false) {
					if (is_dir($dir . $file) and substr($file, 0, 1) != ".") {
						$langs[$file] = array($file, ucfirst($file));
					}
		        }
		        closedir($dh);
    		}
		}

		sort($langs);

        if(!$this->isTemplateFile($dir . $tmpl)) {
            redirect('template_edit.php');
        }

		$html->setvar("tmpl_this", $tmpl);
		$html->setvar("tmpl_title_this", ucfirst($tmpl));

        if ($this->tmplOptionSettigs) {
            $html->parse('menu_settings', false);
        }

		foreach ($langs as $k => $v) {
            if ($this->excludeNotAvailableTmpl($part, $v[0])){
                continue;
            }
            $html->setvar("tmpl_link", $v[0]);

			$titleTmpl = $v[1];
			if ($part == 'administration' && $titleTmpl == 'Default') {
				$titleTmpl = l('template_admin_default');
			}
            $html->setvar("title", $titleTmpl);

			if ($tmpl == $v[0]) {
				$html->parse("mtmpl_on", false);
			} else {
				$html->setblockvar("mtmpl_on", "");
			}
			$html->parse("mtmpl", true);
        }

        $tmpl_area = '.';

		$html->setvar("tmpl_area_this", $tmpl_area);

		$tmpl_type = get_param("tmpl_type", "html");
		$html->setvar("tmpl_type_this", $tmpl_type);

        $patternFileSrcOrBackup = '/(_src|_\d\d)$/';

		if ($tmpl_type == "css")
		{
			$tmpl_page = get_param("tmpl_page", "");
			$html->setvar("tmpl_page_this", $tmpl_page);
			$html->setvar("tmpl_page_title", ucfirst($tmpl_page));

			if ($tmpl_page != "")
			{
                $h = $this->getHtmlAreaContent($tmplPart, $tmpl, $tmpl_area, '/css/', $tmpl_page);
                $html->setvar("html", $h);

                if(adminFileBackupExists($tmplDir . $tmplPart . "/". $tmpl . "/" . $tmpl_area . "/css/" . $tmpl_page)) {
                    $html->parse('button_restore');
                }

				$html->parse("html", true);
			}

			$dir = $tmplDir . $tmplPart . "/". $tmpl . "/" . $tmpl_area . "/css/";
			if (is_dir($dir))
			{
				if ($dh = opendir($dir))
		   		{
			        while (($file = readdir($dh)) !== false)
			        {
						if (!is_dir($dir . $file) and substr($file, 0, 1) != ".")
						{

                            if(preg_match($patternFileSrcOrBackup, $file)) {
                                continue;
                            }

                            if (empty($tmpl_page)) {
                                                            $tmpl_page = $file;
                                                            $html->setvar("tmpl_page_this", $tmpl_page);
                                                            $h = $this->getHtmlAreaContent($tmplPart, $tmpl, $tmpl_area, '/css/', $tmpl_page);

                                                            $html->setvar("html", $h);

                                                            if(adminFileBackupExists($tmplDir . $tmplPart . "/" . $tmpl . "/" . $tmpl_area . "/css/" . $tmpl_page)) {
                                                                $html->parse('button_restore');
                                                            }

                                                            $html->parse("html", true);
                                                            $html->setvar("tmpl_page_title", ucfirst($tmpl_page));
                            }

                            $ltitle = str_replace(array('_', '.css'), array(" ", ""), $file);
                            $ltitle = ucfirst(trim($ltitle));
                            $html->setvar("tmpl_page", $file);
                            $html->setvar("title", $ltitle);
                            if ($tmpl_page == $file) {
								$html->parse("css_page_on", false);
								$html->setblockvar("css_page_off", "");
							} else {
								$html->parse("css_page_off", false);
								$html->setblockvar("css_page_on", "");
							}
							$html->parse("css_page", true);
						}
			        }
			        closedir($dh);
	    		}
	    		$html->parse("css_edit", true);
			}

			$html->parse("css_on", true);
			$html->parse("pages_off", true);
			$html->parse("js_off", true);
			$html->parse("images_off", true);
		}
		elseif ($tmpl_type == "js")
		{
			$tmpl_page = get_param("tmpl_page", "");
			$html->setvar("tmpl_page_this", $tmpl_page);
			$html->setvar("tmpl_page_title", ucfirst($tmpl_page));

			if ($tmpl_page != "")
			{
                $h = $this->getHtmlAreaContent($tmplPart, $tmpl, $tmpl_area, '/js/', $tmpl_page);
                $h = bb_encode($h);

				$html->setvar("html", $h);

                if(adminFileBackupExists($tmplDir . $tmplPart . "/". $tmpl . "/" . $tmpl_area . "/js/" . $tmpl_page)) {
                    $html->parse('button_restore');
                }

				$html->parse("html", true);
			}

			$dir = $tmplDir . $tmplPart . "/". $tmpl . "/" . $tmpl_area . "/js/";
			if (is_dir($dir))
			{
				if ($dh = opendir($dir))
		   		{
			        while (($file = readdir($dh)) !== false)
			        {
						if (!is_dir($dir . $file) and substr($file, 0, 1) != ".")
						{

                            if(preg_match($patternFileSrcOrBackup, $file)) {
                                continue;
                            }

                            if (empty($tmpl_page)) {
                                                            $tmpl_page = $file;
                                                            $html->setvar("tmpl_page_this", $tmpl_page);
                                                            $h = $this->getHtmlAreaContent($tmplPart, $tmpl, $tmpl_area, '/js/', $tmpl_page);
                                                            $html->setvar("html", $h);

                                                            if(adminFileBackupExists($tmplDir . $tmplPart . "/" . $tmpl . "/" . $tmpl_area . "/js/" . $tmpl_page)) {
                                                                $html->parse('button_restore');
                                                            }

                                                            $html->parse("html", true);
                                                            $html->setvar("tmpl_page_title", ucfirst($tmpl_page));
                            }
                            $ltitle = str_replace(array('_', '.js'), array(" ", ""), $file);
                            $ltitle = ucfirst(trim($ltitle));
                            $html->setvar("tmpl_page", $file);
                            $html->setvar("title", $ltitle);
							if ($tmpl_page == $file) {
								$html->parse("js_page_on", false);
								$html->setblockvar("js_page_off", "");
							} else {
								$html->parse("js_page_off", false);
								$html->setblockvar("js_page_on", "");
							}
							$html->parse("js_page", true);
						}
			        }
			        closedir($dh);
	    		}
	    		$html->parse("js_edit", true);
			}

			$html->parse("js_on", true);
			$html->parse("pages_off", true);
			$html->parse("css_off", true);
			$html->parse("images_off", true);
		}
		elseif ($tmpl_type == "images")
		{
			$tmpl_page = get_param("tmpl_page", "");
			$html->setvar("tmpl_page_this", $tmpl_page);
			$html->setvar("tmpl_page_title", ucfirst($tmpl_page));

			/*if ($tmpl_page != "")
			{
				$h = file_get_contents($g['path']['dir_tmpl'] . $part . "/". $tmpl . "/" . $tmpl_area . "/js/" . $tmpl_page);
				$html->setvar("html", $h);
				$html->parse("html", true);
			}*/

			$dir = $tmplDir . $tmplPart . "/". $tmpl . "/" . $tmpl_area . "/img/";
			if (is_dir($dir))
			{
				if ($dh = opendir($dir))
		   		{
			        while (($file = readdir($dh)) !== false)
			        {
						if (!is_dir($dir . $file) and substr($file, 0, 1) != ".")
						{

                            if(preg_match($patternFileSrcOrBackup, $file)) {
                                continue;
                            }

							if ($tmpl_page == $file)
							{
								$html->setvar("tmpl_page", $file);
								$html->setvar("file", $g['path']['url_tmpl'] . $part . "/". $tmpl . "/" . $tmpl_area . "/img/" . $file);
								#$html->parse("js_page_on", false);
								#$html->setblockvar("js_page_off", "");
							}
							else
							{
								$html->setvar("tmpl_page", $file);
								$html->setvar("file", $g['path']['url_tmpl'] . $part . "/". $tmpl . "/" . $tmpl_area . "/img/" . $file);
								#$html->parse("js_page_off", false);
								#$html->setblockvar("js_page_on", "");
							}
							$html->parse("image", true);
						}
			        }
			        closedir($dh);
	    		}
	    		$html->parse("images", true);
			}

			$html->parse("images_on", true);
			$html->parse("css_off", true);
			$html->parse("js_off", true);
			$html->parse("pages_off", true);
		}
		else
		{
			$tmpl_page = get_param("tmpl_page", "_header.html");
			$html->setvar("tmpl_page_this", $tmpl_page);
			$html->setvar("tmpl_page_title", ucfirst($tmpl_page));



            $filename = $tmplDir . $tmplPart . "/". $tmpl . "/" . $tmpl_area . "/" . $tmpl_page;

            if(adminFileBackupExists($filename)) {
                $html->parse('button_restore');
            }

            $h = $this->getHtmlAreaContent($tmplPart, $tmpl, $tmpl_area, '/', $tmpl_page);

			$h = bb_encode($h);

			$html->setvar("html", $h);
			$html->parse("html", true);

			$dir = $tmplDir . $tmplPart . "/" . $tmpl . "/" . $tmpl_area . "/";
			if (is_dir($dir))
			{
				if ($dh = opendir($dir))
		   		{
			        while (($file = readdir($dh)) !== false)
			        {
						if (!is_dir($dir . $file) and substr($file, 0, 1) != ".")
						{
                            if(preg_match($patternFileSrcOrBackup, $file)) {
                                continue;
                            }

                            $ltitle = substr($file, 0, strlen($file) - 5);
                            /*if (substr($ltitle, 0, 1) == "_"){
                                $ltitle = substr($ltitle, 1)  . "*";
                            }*/
                            $ltitle = trim(str_replace("_", " ", $ltitle));
                            $ltitle = ucfirst($ltitle);
                            $html->setvar("title", $ltitle);
                            $html->setvar("tmpl_page", basename($file));

							if ($tmpl_page == $file) {
								$html->parse("tmpl_on", false);
								$html->setblockvar("tmpl_off", "");
							} else {
								$html->parse("tmpl_off", false);
								$html->setblockvar("tmpl_on", "");
							}
							$html->parse("tmpl", true);
						}
			        }
			        closedir($dh);
	    		}
	    		$html->parse("tmpl_edit", true);
			}

			$html->parse("pages_on", true);
			$html->parse("css_off", true);
			$html->parse("js_off", true);
			$html->parse("images_off", true);
		}

		$dir = $g['path']['dir_tmpl'] . $part . "/". $tmpl . "/";
		if (is_dir($dir) and false)
		{
			if ($dh = opendir($dir))
	   		{
		        while (($file = readdir($dh)) !== false)
		        {
					if (is_dir($dir . $file) and substr($file, 0, 1) != ".")
					{
						if ($tmpl_area == $file)
						{
							$html->setvar("tmpl_area", $file);
							$html->setvar("title", ucfirst($file));
							$html->parse("area_on", false);
							$html->setblockvar("area_off", "");
						}
						else
						{
							$html->setvar("tmpl_area", $file);
							$html->setvar("title", ucfirst($file));
							$html->parse("area_off", false);
							$html->setblockvar("area_on", "");
						}
						$html->parse("area", true);
					}
		        }
		        closedir($dh);
    		}
		}
		else
		{
			$html->setvar("tmpl_area", "");
			$html->setvar("title", l('main_area'));
			$html->parse("area_on", false);
			$html->setblockvar("area_off", "");
			$html->parse("area", true);
		}


		$html->setvar("message_template", $this->message_template);

		parent::parseBlock($html);
	}

    public function getHtmlAreaContent($tmplPart, $tmpl, $tmpl_area, $tmpl_section, $tmpl_page)
    {
        $filename = Common::getOption('dir_tmpl', 'path') . '/' . $tmplPart . "/". $tmpl . "/" . $tmpl_area . $tmpl_section . $tmpl_page;

        $h = '';

        if($this->isTemplateFile($filename)) {
            $h = file_get_contents($filename);
        }

        return $h;
    }

    public function isTemplateFile($filename)
    {
        $fileRealPath = realpath($filename);
        $templatesRealPath = realpath(Common::getOption('dir_tmpl', 'path'));

        $ext = strtolower(pathinfo($fileRealPath, PATHINFO_EXTENSION));

        $isTemplateFile = false;

        if($ext != 'php' && strpos($fileRealPath, $templatesRealPath) === 0) {
            $isTemplateFile = true;
        }

        return $isTemplateFile;
    }
}

$page = new CAdminTemplateEdit("", $g['tmpl']['dir_tmpl_administration'] . "template_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");