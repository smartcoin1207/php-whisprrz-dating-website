<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class CHtml
{
	var $globals        = array();  // initial data:files and blocks
	var $blocks         = array();  // resulted data and variables
	var $block_keys     = array();  // associate array (short key, full key)
	var $parse_array    = array();  // array ready for parsing
	var $position       = 0;        // position in parse string
	var $length         = 0;        // length of parse string
	var $block_path     = "";

	var  $delimiter      = "";
	var  $tag_sign       = "";
	var  $begin_block    = "";
	var  $end_block      = "";

    function __construct() 
	{
		$this->delimiter      = "%%%DELIMITER%%%";#chr(27);
		$this->tag_sign       = "%%%TAG_SIGN%%%";#chr(15);
		$this->begin_block    = "%%%BEGIN_BLOCK%%%";#chr(16);
		$this->end_block      = "%%%END_BLOCK%%%";#chr(17);
	}

	function LoadTemplate($filename, $block_name)
	{
		$file_path = $filename;

		if (file_exists($file_path))
		{
			$file_content = file_get_contents($file_path);
			$file_content = preg_replace("/<!\-\-\s*begin_\s*([\w\s]*\w+)\s*\-\->/is",  $this->delimiter . $this->begin_block . $this->delimiter . "\\1" . $this->delimiter, $file_content);
			$file_content = preg_replace("/<!\-\-\s*end_\s*([\w\s]*\w+)\s*\-\->/is",  $this->delimiter . $this->end_block . $this->delimiter . "\\1" . $this->delimiter, $file_content);
			if (strpos($file_path, '_themes/main')) {
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
		}
		else exit('<h1 style="text-align:center">'.l('not_template').'</h1>');
	}

	function set_block($block_names, $is_subblock = false)
	{
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
		for($i = sizeof($block_names) - 1; $i >= 0; $i--)
		{
			$key = "/" . $block_names[$i] . $key;
			if(!isset($this->block_keys[$key]))
			{
				$this->block_keys[$key] = $full_block_name;
			}
		}
	}

	function setvar($key, $value)
	{
		$this->globals[$key] = $value;
	}

	function setblockvar($key, $value)
	{
		$key = $this->getname($key, true);
		$this->globals[$key] = $value;
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
		if(strlen($array_key) && substr($array_key, 0, 1) != "/")
		{
			$array_key = "/" . $array_key;
		}

		$searching_array = ($is_block) ? $this->blocks : $this->globals;

		if(strlen($this->block_path))
		{
			if(substr($this->block_path, 0, 1) != "/")
			{
				$this->block_path = "/" . $this->block_path;
			}
			if(substr($this->block_path, strlen($this->block_path) - 1, 1) == "/")
			{
				$this->block_path = substr($this->block_path, 1, strlen($this->block_path) - 1);
			}

			$array_key = strlen($array_key) ? $this->block_path . $array_key : $this->block_path;
		}

		if ($is_block && isset($this->block_keys[$array_key]))
		{
			$array_key = $this->block_keys[$array_key];
		}
		elseif (!isset($searching_array[$array_key]))
		{
			reset($searching_array);
			while (list($key,) = each($searching_array))
			{
				$key_len = strlen($key);
				$array_key_len = strlen($array_key);
				if($key_len >= $array_key_len && substr($key, $key_len - $array_key_len, $array_key_len) == $array_key)
				{
					$array_key = $key;
					break;
				}
			}
		}
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
		$block_name = $this->getname($block_name, true);
		return isset($this->blocks[$block_name]);
	}

	#����� � �.�.

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