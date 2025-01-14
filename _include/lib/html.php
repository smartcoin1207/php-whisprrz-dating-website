<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CHtml {

    var $globals = array();  // initial data:files and blocks
    var $blocks = array();  // resulted data and variables
    var $block_keys = array();  // associate array (short key, full key)
    var $parse_array = array();  // array ready for parsing
    var $position = 0;        // position in parse string
    var $length = 0;        // length of parse string
    var $block_path = "";
    var $delimiter = "";
    var $tag_sign = "";
    var $begin_block = "";
    var $end_block = "";
    var $arrayKeysCache = array();
    var $blockKeysCache = array();
    var $blockIncompatibleKeysCache = array();
    var $vars = array();
    var $blockKeysByLastItems = array();
    var $cacheBlockExists = array();
    var $cacheGetname = array();

    function __construct() {
        $this->delimiter = "%%%DELIMITER%%%"; #chr(27);
        $this->tag_sign = "%%%TAG_SIGN%%%"; #chr(15);
        $this->begin_block = "%%%BEGIN_BLOCK%%%"; #chr(16);
        $this->end_block = "%%%END_BLOCK%%%"; #chr(17);
    }

    function LoadTemplate($filename, $block_name, $isTextTemplate = false, $textTemplate = false) {
        global $g;
        $file_path = $filename;

        $continue = true;

        // Template constructor
        if(is_array($filename)) {
            $textTemplate = '';

            foreach($filename as $fileTemplateKey => $file_path) {

                if (file_exists($file_path)) {
                    $file_content = file_get_contents($file_path);
                } else {
                    $continue = false;
                    break;
                }

                if(trim($textTemplate) == '') {
                    $textTemplate = $file_content;
                } else {
                    $textTemplate = str_replace('{' . $fileTemplateKey . '}', $file_content, $textTemplate);
                }
            }
            $isTextTemplate = true;
        }

        if ($continue && ($isTextTemplate || file_exists($file_path))) {
            if ($isTextTemplate) {
                $file_content = $textTemplate;
            } else {
                $file_content = file_get_contents($file_path);
            }

            if(preg_match_all("/\{(\w+)\}/is", $file_content, $matches)) {
                if(isset($matches[1]) && count($matches[1])) {
                    $this->vars = array_flip($matches[1]);
                }
            }

            $file_content = preg_replace("/<!\-\-\s*begin_\s*([\w\s]*\w+)\s*\-\->/is", $this->delimiter . $this->begin_block . $this->delimiter . "\\1" . $this->delimiter, $file_content);
            $file_content = preg_replace("/<!\-\-\s*end_\s*([\w\s]*\w+)\s*\-\->/is", $this->delimiter . $this->end_block . $this->delimiter . "\\1" . $this->delimiter, $file_content);

            global $gm;
            if (strpos($file_path, '_frameworks/main') and isset($gm)) {
                $file_content = preg_replace("/\<<<(\w+)\>>>/is", $this->delimiter . $this->tag_sign . $this->delimiter . "\\1" . $this->delimiter, $file_content);
            } else {
                $file_content = preg_replace("/\{(\w+)\}/is", $this->delimiter . $this->tag_sign . $this->delimiter . "\\1" . $this->delimiter, $file_content);
            }
            $this->parse_array = explode($this->delimiter, $file_content);
            $this->position = 0;
            $this->length = sizeof($this->parse_array);
            $block_names[0] = $this->block_path . "/" . $block_name;
            $this->set_block($block_names, "block");
            return 1;
        } else {

            require_once($g['path']['dir_main'] . '_include/current/install.class.php');
            $myinstall = new Class_Install(l('error_to_proceed'));
            $path = explode('_frameworks/', $file_path);
            $path = explode('/', $path[1]);
            if (strpos($file_path, '_frameworks/partner')) {
                $count = countFrameworks('partner');
                if (empty($count))
                    redirect($g['path']['url_main']);
            }
            else {
                $count = countFrameworks($path[0]);
                if (empty($count)) {
                    $myinstall->html .= '<p>' . l('site_needs_a_framework_to_function') . '</p>';
                } elseif (is_dir($g['path']['dir_tmpl'] . $path[0] . '/' . $path[1])) {
                    // prevent template not found issue on different templates
                    if(!get_param('debug_no_redirect')) {
                        $url = Common::getHomePage();
                        // prevent infinite redirect
                        if($url != Common::page()) {
                            redirect($url);
                        }
                    }

                    $myinstall->html .= 'Template file ' . $file_path . ' does not exist';
                } else {
                    if (file_exists($g['path']['dir_tmpl'] . $path[0] . '/')) {
                        $dir = scandir($g['path']['dir_tmpl'] . $path[0] . '/');
                        unset($dir[0]);
                        unset($dir[1]);
                        foreach ($dir as $key) {
                            if (is_dir($g['path']['dir_tmpl'] . $path[0] . '/' . $key)) {
                                if ($path[0] == 'main') {
                                    redirect('?set_template=' . $key);
                                } else {
                                    redirect('?set_template_' . $path[0] . '=' . $key);
                                }
                            }
                        }
                    } else {
                        $myinstall->html .= '<p>' . l('site_needs_a_framework_to_function') . '</p>';
                    }
                }
                   echo $myinstall->header;
                   echo $myinstall->html;
                   $ns = '</div></div><div class="foot">
                <span class="btn_color color1 fl_right"><a href="' . $g['path']['url_main'] . 'index.php">Try again</a></span>
            </div>';
                    echo $ns;
                    echo $myinstall->footer;
                    exit;
            }
        }
    }

    function set_block($block_names, $is_subblock = false) {
		$block_level = sizeof($block_names);
		$block_name = "/" . join("/", $block_names);

		$block_number = 0;
		$block_array  = array();
		$block_array[0] = 0;

		while ($this->position < $this->length)
		{
			$element_parse = $this->parse_array[$this->position];

			if ($element_parse == $this->tag_sign)
			{
				#��� - (� ������� 1 = ���_����, 2 = ��������)
				$block_number++;
				$block_array[$block_number] = $this->parse_array[$this->position + 1];
				$this->position += 2;
			}
			elseif ($element_parse == $this->begin_block)
			{
				#���� - (� ������� 1 = �����_����, 2 = ��������, 3 = ��������, 4 = ���_����, 5 = ��������)
				$block_number++;
				$block_array[$block_number] = $block_name . "/" . $this->parse_array[$this->position + 1];
				$this->position += 2;
				$block_names[$block_level] = $this->parse_array[$this->position - 1];
				$this->set_block($block_names, true);
			}
			elseif ($element_parse == $this->end_block && $is_subblock == true)
			{
				if ($this->parse_array[$this->position + 1] == $block_names[$block_level - 1])
				{
					$block_array[0] = $block_number;
					$this->position += 2;
					$this->blocks[$block_name] = $block_array;
					$this->set_keys($block_names);
					return;
				}
				else
				{
					echo "���� ������ � �����: $block_name";
					exit;
				}
			}
			else
			{
				#���� ����� ������� � ������ - (� ������� 1 = ��������)
				$block_number++;
				$block_array[$block_number] = $block_name . "#" . $block_number;
				$this->globals[$block_name . "#" . $block_number] = $element_parse;
				$this->position++;
			}
		}
		$block_array[0] = $block_number;
		$this->blocks[$block_name] = $block_array;
		$this->set_keys($block_names);
	}

	function set_keys($block_names)
	{
		$full_block_name = "/" . join("/", $block_names);
		$key = "";
		$keyFromStart = "";
        $lastItemIndex = sizeof($block_names) - 1;
        $index = 0;

		for($i = $lastItemIndex; $i >= 0; $i--)
		{
			$key = "/" . $block_names[$i] . $key;
			if(!isset($this->block_keys[$key]))
			{
				$this->block_keys[$key] = $full_block_name;
			}

            $keyFromStart .= '/' . $block_names[$index];

            if($index == 0 && $block_names[$index] == '/main') {
                $blocksLastItemsIndex = 'main';
            } else {
                $blocksLastItemsIndex = $block_names[$index];
            }

            $this->blockKeysByLastItems[$blocksLastItemsIndex] = $keyFromStart;

            $index++;
		}
	}

	function setvar($key, $value)
	{
		$this->globals[$key] = $value;
	}

	function assign($key, $value)
	{
        if (is_array($value)) {
            if ($key) {
                $key .= '_';
            }
            foreach ($value as $k => $v) {
                $this->assign($key . $k, $v);
            }
        } elseif ($key) {
            $this->setvar($key, $value);
        }
	}

	function items($blockname, $items, $start_num = null, $subconds = array())
	{
        if ($start_num === null or $start_num === '') {
            $start_num = 1;
        }
        if (is_string($subconds)) {
            $subconds = explode('|', $subconds);
        }
		$i = 1;
		$num = $start_num;
        $total = count($items);
		foreach ($items as $k => $v) {
			$this->assign($blockname, $v);
			$this->assign($blockname . '_num', $num);
			$this->subcond($i == 1, $blockname . '_first', $blockname . '_not_first');
			$this->subcond($i == $total, $blockname . '_last', $blockname . '_not_last');
			$this->subcond($i == 2, $blockname . '_after2');
			$this->subcond($i == 3, $blockname . '_after3');
			$this->subcond($i == 4, $blockname . '_after4');
			$this->subcond($i == 5, $blockname . '_after5');
			$this->subcond($i % 3 == 0, $blockname . '_third');
			$this->subcond($i % 5 == 0, $blockname . '_fifth');
			$this->subcond($i % 2 == 0, $blockname . '_even');
			$this->subcond($i % 2 != 0, $blockname . '_odd');
            foreach ($subconds as $sc) {
			    $this->subcond($v[$sc], $blockname . '_' . $sc, $blockname . '_not_' . $sc);
            }
			$this->parse($blockname);
			$i++;
			$num++;
		}
	}
	function subcond($condition, $block_name, $alt_block_name = null)
	{
		if ($condition) {
		    $this->subparse($block_name);
            if ($alt_block_name !== null) {
                $this->clean($alt_block_name);
            }
		} else {
		    $this->clean($block_name);
            if ($alt_block_name !== null) {
                $this->subparse($alt_block_name);
            }
		}
	}
	function cond($condition, $block_name, $alt_block_name = null)
	{
		if ($condition) {
		    $this->parse($block_name);
		} elseif ($alt_block_name !== null) {
		    $this->parse($alt_block_name);
		}
	}

	function setblockvar($key, $value)
	{
		$key = $this->getname($key, true);
		$this->globals[$key] = $value;
	}

	function clean($key)
	{
		$key = $this->getname($key, true);
		$this->globals[$key] = '';
	}

	function replaceblock($key, $value)
	{
		$key = $this->getname($key, true);
		$this->globals[$key] = $value;
		$this->blocks[$key] = "";
	}

	function parse($block_name = "", $accumulate = true)
	{
		$this->globalparse($block_name, $accumulate, "");
	}

	function subparse($block_name = "")
	{
		$this->globalparse($block_name, false, "");
	}

	function r_parse($block_name = "", $accumulate = true)
	{
		$this->globalparse($block_name, $accumulate, "", false, true);
	}

	function parse_to($block_name, $accumulate, $parse_to)
	{
		$this->globalparse($block_name, $accumulate, $parse_to);
	}

	function globalparse($block_name, $accumulate = true, $parse_to = "", $output = false, $reverse = false)
	{
		$block_name = $this->getname($block_name, true);

		if($parse_to == "")
		{
			$parse_to = $block_name;
		}
		else
		{
			$parse_to = $this->getname($parse_to, true);
		}
		$block_value = "";

		if (isset($this->blocks[$block_name]))
		{
			$block_array = $this->blocks[$block_name];

			if(is_array($block_array))
			{
				$globals = $this->globals;
				$array_size = $block_array[0];
				for($i = 1; $i <= $array_size; $i++)
				{
					$block_value .= isset($globals[$block_array[$i]]) ? $globals[$block_array[$i]] : "";
				}
				$left_value = $reverse ? $block_value : "";
				$right_value = $reverse ? "" : $block_value;
				// var_dump($parse_to);
				// die();
				$this->globals[$parse_to] = ($accumulate && isset($this->globals[$parse_to])) ? $left_value . $this->globals[$parse_to] . $right_value : $block_value;
			}
			if($output)
			{
				$value = $this->globals[$block_name];
				echo $value;
			}
		}
	}

	function getname($array_key, $is_block)
	{
        if(isset($this->cacheGetname[$is_block][$array_key])) {
            return $this->cacheGetname[$is_block][$array_key];
        }

        $array_key_start = $array_key;
        if(isset($this->arrayKeysCache[$array_key])) {
            $arrayKeyLen = $this->arrayKeysCache[$array_key];
        } else {
            $arrayKeyLen = strlen($array_key);
            $this->arrayKeysCache[$array_key] = $arrayKeyLen;
        }

		if($arrayKeyLen && $array_key[0] != '/')
		{
			$array_key = '/' . $array_key;
            $arrayKeyLen++;
		}

		$searching_array = ($is_block) ? $this->blocks : $this->globals;

		if($this->block_path != '')
		{
			if($this->block_path[0] != '/')
			{
				$this->block_path = '/' . $this->block_path;
			}

            $blockPathLen = strlen($this->block_path);

			if($this->block_path[$blockPathLen - 1] == '/')
			{
                $this->block_path = substr($this->block_path, 1, $blockPathLen - 1);
			}

			$array_key = $arrayKeyLen ? $this->block_path . $array_key : $this->block_path;
		}

		if ($is_block && isset($this->block_keys[$array_key]))
		{
			$array_key = $this->block_keys[$array_key];
		}
		elseif (!isset($searching_array[$array_key]))
		{
            if(isset($this->blockKeysByLastItems[$array_key_start])) {
                $array_key = $this->blockKeysByLastItems[$array_key_start];
            }
		}

        $this->cacheGetname[$is_block][$array_key_start] = $array_key;

		return $array_key;
	}

	function getvar($key = "")
	{
		$key = $this->getname($key, false);
		$value = "";
		if (isset($this->globals[$key]))
		{
			$value = $this->globals[$key];
		}
		return $value;
	}

	function blockexists($block_name)
	{
        if(isset($this->cacheBlockExists[$block_name])) {
            $result = $this->cacheBlockExists[$block_name];
        } else {
            $block_name_in_template = $this->getname($block_name, true);
            $result = isset($this->blocks[$block_name_in_template]);
            $this->cacheBlockExists[$block_name] = $result;
        }
		return $result;
	}

    function varexists($varName)
    {
        return isset($this->vars[$varName]);
    }

	function parse_out($block_name, $accumulate = true)
	{
		$this->globalparse($block_name, $accumulate, "", true);
	}

	function print_block($block_name)
	{
		$block_name = $this->getname($block_name, true);
		reset($this->blocks[$block_name]);
		echo "<table border=\"1\">";
		while(list($key, $value) = each($this->blocks[$block_name]))
		{
			$block_value = isset($this->globals[$value]) ? $this->globals[$value] : "";
			echo "<tr><th valign=top>$value</th><td>" . nl2br(htmlspecialchars($block_value)) . "</td></tr>";
		}
		echo "</table>";
	}

	function print_globals()
	{
		reset($this->globals);
		echo "<table border=\"1\">";
		while(list($key, $value) = each($this->globals))
		{
			echo "<tr><th valign=top>$key</th><td>" . nl2br(htmlspecialchars($value)) . "</td></tr>";
		}
		echo "</table>";
	}

}

?>