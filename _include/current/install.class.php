<?php

class Class_Install {

    var $header = '';
    var $footer = '';
    var $file_config_db = '_include/config/db.php';
    var $dir_license = '_include/config/';
    var $project = 'cham-5.4';
    var $mode = 0777;
    var $mode_str = "0777";
    var $files = array(
        '../_files/',
        '../_include/config',
        '../_lang/',
        '../_frameworks/',
    );
    var $files_all = array(
        '../_files/',
        '../_include/config',
        '../_lang/',
        '../_frameworks/',
    );
    var $time_limit = 600;
    var $dumps = array(
        '../../_install/backup/schema.sql',
        '../../_install/backup/data.sql',
        '../../_install/backup/geo.sql',
//        '../../_install/backup/geoip1.sql',
//        '../../_install/backup/geoip2.sql',
//        '../../_install/backup/geoip3.sql',
//        '../../_install/backup/geoip4.sql',
//        '../../_install/backup/geoip5.sql',
//        '../../_install/backup/geoip6.sql',
//        '../../_install/backup/geoip7.sql',
//        '../../_install/backup/geoip8.sql',
//        '../../_install/backup/geoip9.sql',
//        '../../_install/backup/geoip10.sql',
//        '../../_install/backup/geoip11.sql',
    );
    var $charset = 'utf8';
    var $step = 0;
    var $ndump = 1;
    var $error = '';
    var $version = '';
    var $fiz = '';
    var $head = array('', '', '', '', '', '', '',);
    var $html = '';
    var $html_0 = '';
    var $html_1 = '';
    var $html_2 = '';
    var $html_4 = '';

    function __construct($title = NULL, $pageTitle = 'Chameleon Social Networking Software') {
        global $g;
        $path = explode('/', $_SERVER['REQUEST_URI']);

        $parts = array('_install', 'm', 'administration', 'partner');

        if(array_intersect($parts, $path)) {
            $g['path']['url_main'] = '../';
        }

        if(!isset($g['path']['url_main'])) {
            $g['path']['url_main'] = './';
        }

        if(empty($title)) {
            $title = l('error_to_proceed');
        }
        $this->header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

                    <html xmlns="http://www.w3.org/1999/xhtml">

                    <head>
                        <title>' . $pageTitle . '</title>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <link rel="stylesheet" type="text/css" href="' . $g['path']['url_main'] . '_frameworks/install/default/css/style.css" />
                    </head>

                    <body>

                        <div class="pp_installator">
                        <div class="head">'.$title.'</div>
                        <div class="cont">
                            <img src="' . $g['path']['url_main'] . '_frameworks/install/default/images/installator_decor.png" width="136" height="134" alt="" title="" />
                            <div class="bl">
                    ';
        $dirs = str_replace('./', '_install/', str_replace('../', '', implode('<br>', $this->files)));
        $this->html_0 = "<p>Welcome to the instalation!<br>
		Please set chmod 0777 to the folders and all the contents(subfolders and files):</p><p><strong>$dirs</strong></p>";
        $this->footer = '
                        </body>
                        </html>';

    }

    function html2() {
        $host = htmlspecialchars(isset($_POST['host']) ? $_POST['host'] : 'localhost');
        $name = htmlspecialchars(isset($_POST['name']) ? $_POST['name'] : '');
        $pass = htmlspecialchars(isset($_POST['pass']) ? $_POST['pass'] : '');
        $database = htmlspecialchars(isset($_POST['database']) ? $_POST['database'] : '');
        $license = isset($_POST['license']) ? $_POST['license'] : '';
        return $this->error . '
	<form name="form_subm" method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?step=2">
		                <h4>Connection settings</h4>

                <div class="frm_item">
                    <label>Domain Name:</label>
                    <label style="width: auto;">' . $_SERVER['HTTP_HOST'] . '</label>
                </div>
                <div class="frm_item">
                    <label>Database Host:</label>
                    <input class="inp" name="host" value="' . $host . '" type="text" />
                </div>
                <div class="frm_item">
                    <label>Database User:</label>
                    <input class="inp" name="name" value="' . $name . '" type="text" />
                </div>
                <div class="frm_item">
                    <label>Database Password:</label>
                     <input class="inp" name="pass" value="' . $pass . '" type="password" />
                </div>
                <div class="frm_item">
                    <label>Database:</label>
                    <input class="inp" name="database" value="' . $database . '" type="text" />
                </div>
                  <div class="frm_item">
                    <label>License key:</label>
                    <input name="license" class="inp" value="' . $license . '" type="text" />
                </div>
Localhost license key for testing and development:<br><br>e143627758f9e2c3718943c75d85d90f75e2b65923790e8d520afaf4dca4caac3c3578a6166a80fa1823d0701e4f5ff212ee790a590645c98ac8f3d11c507c53754411fc72c0f7909ad3db47b81b15ff1e686dafe72901a112e303f58c37d6cf<br>

            </div>
        </div>
        <div class="foot">

 <span class="btn_color color1 fl_right"><a href="javascript:document.form_subm.submit();">Next</a></span>
        </div>
		';
    }

    function SetDirectory($path, $mode, $mode_str) {
        if (is_file($path)) {
            $s = substr(sprintf('%o', fileperms($path)), -4);
            if ($s == $mode_str) {
                return true;
            } elseif (@chmod($path, $mode)) {
                return true;
            } else {
                #echo "error!";
                $this->error.="" . str_replace('../', '', $path) . "<br>";
                return false;
            }
        }

        if ($dir_handle = @opendir($path)) {
            $ress = true;
            while ($file = readdir($dir_handle)) {
                if ($file == "." || $file == "..")
                    continue;
                $ress = ($this->SetDirectory($path . '/' . $file, $mode, $mode_str) and $ress);
            }
            closedir($dir_handle);

            $s = substr(sprintf('%o', fileperms($path)), -4);
            if ($s == $mode_str) {
                return $ress;
            } elseif (@chmod($path, $mode)) {
                return $ress;
            } else {
                #echo "error!";
                $this->error.="" . str_replace('../', '', $path) . "<br>";
                return false;
            }
        }
        else
            return false;
    }

    function set_mode() {

        $res = true;
        foreach ($this->files as $url) {
            if (file_exists($url)) {
                $res = ($this->SetDirectory($url, $this->mode, $this->mode_str) and $res);
            } else {
                $res = false;
                $this->error .= "<p>Path not found: <strong>" . $url . "</strong></p>";
            }
        }
        #echo $this->error;
        return $res;
    }

    function SetDirectory_all($path, $mode, $mode_str) {
        if (is_file($path)) {
            $s = substr(sprintf('%o', fileperms($path)), -4);
            if ($s == $mode_str) {
                return true;
            } elseif (@chmod($path, $mode)) {
                return true;
            } else {
                #echo "error!";
                $this->error.="" . $path . "<br>";
                return false;
            }
        }

        if ($dir_handle = opendir($path)) {
            $ress = true;
            while ($file = readdir($dir_handle)) {
                if ($file == "." || $file == "..")
                    continue;
                $ress = ($this->SetDirectory_all($path . '/' . $file, $mode, $mode_str) and $ress);
            }
            closedir($dir_handle);

            $s = substr(sprintf('%o', fileperms($path)), -4);
            if ($s == $mode_str) {
                return $ress;
            } elseif (@chmod($path, $mode)) {
                return $ress;
            } else {
                #echo "error!";
                $this->error.="" . $path . "<br>";
                return false;
            }
        }
        else
            return false;
    }

    function set_mode_all() {

        $res = true;
        foreach ($this->files_all as $url) {
            if (file_exists($url)) {
                $res = ($this->SetDirectory_all($url, $this->mode, $this->mode_str) and $res);
            } else {
                $res = false;
                $this->error .= "<p>Path not found: <strong>" . $url . "</strong></p>";
            }
        }
        #echo $this->error;
        return $res;
    }
    function mysql_version() {
        global $g;

        $g['db']['host'] =  $this->escape($_POST['host']);
        $g['db']['db'] = $this->escape($_POST['database']);
        $g['db']['user'] =  $this->escape($_POST['name']);
        $g['db']['password'] = $this->escape($_POST['pass']);

        $text = "<?php\n";
        $text .= '$g["db"]["host"] = ' . $this->toConfig($this->escape($_POST['host'])) . ";\n";
        $text .= '$g["db"]["db"] = ' .  $this->toConfig($this->escape($_POST['database'])) . ";\n";
        $text .= '$g["db"]["user"] = ' .  $this->toConfig($this->escape($_POST['name'])) . ";\n";
        $text .= '$g["db"]["password"] = ' .  $this->toConfig($this->escape($_POST['pass'])) . ";\n";
        $text .= '?>';

        $dbConfigFile = $g['path']['url_main'] . $this->file_config_db;
        @file_put_contents($dbConfigFile, $text);
        if(!file_exists($dbConfigFile)) {
            $this->error = "Can't create MySql config file";
            return false;
        }

        if(!$this->saveLicense($_POST['license'])) {
            return false;
        }

        if(@DB::connect()) {
            DB::query("select version() as v");
            if (empty($this->error)) {
                $row = DB::fetch_row();
                $this->version = $row['v'];
                $versionParts = explode('.', $row['v']);
                if ($versionParts[0] < 4) {
                    $this->error = "Old Version of MySql";
                    return false;
                }
            } else {
                return false;
            }
            return true;
        } else {
            $this->error = DB::getConnectError();
            if(!$this->error) {
                $this->error = DB::error();
            }
            return false;
        }
    }

    function fn_open($name, $mode) {
        if ($this->SET['comp_method'] == 2) {
            $this->filename = "{$name}";
            return bzopen(dirname(__FILE__) . '/' . $this->filename, "{$mode}b{$this->SET['comp_level']}");
        } elseif ($this->SET['comp_method'] == 1) {
            $this->filename = "{$name}";
            return gzopen(dirname(__FILE__) . '/' . $this->filename, "{$mode}b{$this->SET['comp_level']}");
        } else {
            $this->filename = "{$name}";
            return fopen(dirname(__FILE__) . '/' . $this->filename, "{$mode}b");
        }
    }

    function fn_read($fp) {
        if ($this->SET['comp_method'] == 2) {
            return bzread($fp, 4096);
        } elseif ($this->SET['comp_method'] == 1) {
            return gzread($fp, 4096);
        } else {
            return fread($fp, 4096);
        }
    }

    function fn_read_str($fp) {
        $string = '';
        $this->file_cache = ltrim($this->file_cache);
        $pos = strpos($this->file_cache, "\n", 0);
        if ($pos === false) {
            while (!$string && ($str = $this->fn_read($fp))){
                $pos = strpos($str, "\n", 0);
                if ($pos === false) {
                    $this->file_cache .= $str;
                } else {
                    $length = $pos;
                    if($pos === 0) {
                        $length = 1;
                    }
    				$string = $this->file_cache . substr($str, 0, $length);
                    $this->file_cache = substr($str, $pos + 1);
                }
            }
            if (!$str) {
                if ($this->file_cache) {
                    $string = $this->file_cache;
                    $this->file_cache = '';
                    return trim($string);
                }
                return false;
            }
        } else {
            $length = $pos;
            if($pos === 0) {
                $length = 1;
            }
            $string = substr($this->file_cache, 0, $length);
            $this->file_cache = substr($this->file_cache, $pos + 1);
        }
        return trim($string);
    }
    public function licenseError($domain)
    {
        $cmd = get_param('cmd');

        if($cmd == 'update_license') {
            if($this->saveLicense(get_param('license'))) {
                redirect();
            }
        }

        $text = $this->error . "<p>Oops! Wrong license key or license key file absent. Please leave a ticket at <a href='http://clients.websplosion.com'>http://clients.websplosion.com</a> to receive your license key for {$domain}.<br><br></p>" . '
<form name="form_subm" method="post" action="">
    <input type="hidden" name="cmd" value="update_license"/>
    <p>Please paste the license key in this field:</p>

    <textarea class="license" name="license"></textarea>

    </div>
</div>
<div class="foot">
    <span class="btn_color color1 fl_right"><a href="javascript:document.form_subm.submit();">Save</a></span>';

        $this->html .= $text;
        echo $this->header;
        echo $this->html;
        echo $this->footer;

        exit;
    }
    function mysql_restore($file) {
        if(!@DB::connect()) {
           $this->error = "Can't connect to MySql";
            return false;
        }

        if (preg_match("/^(.+?)\.sql(\.(bz2|gz))?$/", $file, $matches)) {
            if (isset($matches[3]) && $matches[3] == 'bz2') {
                $this->SET['comp_method'] = 2;
            } elseif (isset($matches[2]) && $matches[3] == 'gz') {
                $this->SET['comp_method'] = 1;
            } else {
                $this->SET['comp_method'] = 0;
            }
            $this->SET['comp_level'] = '';
            if (!file_exists(dirname(__FILE__) . '/' . $file))
                die("File not found!");
        }

        $fp = $this->fn_open($file, "r");
        $this->file_cache = $sql = $table = $insert = '';
        $is_skd = $query_len = $execute = $q = $t = $i = $aff_rows = 0;
        $limit = 300;
        $index = 4;
        $tabs = 0;
        $cache = '';
        $info = array();



        while (($str = $this->fn_read_str($fp)) !== false) {
            if (empty($str) || preg_match("/^(#|--)/", $str)) {
                if (!$is_skd && preg_match("/^#SKD101\|/", $str)) {
                    $info = explode("|", $str);
                    $is_skd = 1;
                }
                continue;
            }
            $query_len += strlen($str);

            if (!$insert && preg_match("/^(INSERT INTO `?([^` ]+)`? .*?VALUES)(.*)$/i", $str, $m)) {
                if ($table != $m[2]) {
                    $table = $m[2];
                    $tabs++;
                    $i = 0;
                }
                $insert = $m[1] . ' ';
                $sql .= $m[3];
                $index++;
                $info[$index] = isset($info[$index]) ? $info[$index] : 0;
                $limit = round($info[$index] / 20);
                $limit = $limit < 300 ? 300 : $limit;
                if ($info[$index] > $limit) {
                    $cache = '';
                }
            } else {
                $sql .= $str;
                if ($insert) {
                    $i++;
                    $t++;
                }
            }

            if (!$insert && preg_match("/^CREATE TABLE (IF NOT EXISTS )?`?([^` ]+)`?/i", $str, $m) && $table != $m[2]) {
                $table = $m[2];
                $insert = '';
                $tabs++;
                $cache .= "Table `{$table}`.";
                $i = 0;
            }
            if ($sql) {
                if (preg_match("/;$/", $str)) {
                    $sql = rtrim($insert . $sql, ";");
                    $insert = '';
                    $execute = 1;
                }
                if ($query_len >= 65536 && preg_match("/,$/", $str)) {
                    $sql = rtrim($insert . $sql, ",");
                    $execute = 1;
                }
                if ($execute) {
                    $q++;

                    if(trim($sql)) {
                        DB::execute($sql);
                    }

                    if (preg_match("/^insert/i", $sql)) {
                        $aff_rows += DB::affected_rows();
                    }
                    $sql = '';
                    $query_len = 0;
                    $execute = 0;
                }
            }
        }


        DB::close();
        return true;
    }

    function escape($str) {
        if(PHP_MAJOR_VERSION >= 7 || @get_magic_quotes_gpc() == 0) {
            $str = addslashes($str);
        }
        return $str;
    }

    function toConfig($str)
    {
        return 'htmlspecialchars_decode(\'' . addslashes(htmlspecialchars(stripslashes($str))) . '\')';
    }

    public function saveLicense($license)
    {
        global $g;
        $license = str_replace(array(' ', "\n", "\r", "\t"), '', trim($license));

        $domain = $_SERVER['HTTP_HOST'];
        $domain = explode(':', $domain);
        $domain = $domain[0];
        $domain = strtolower(trim($domain));
        $domain = str_replace('www.', '', $domain);
        // clean subdomains
        $domain = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $domain);

        $domainParts = explode('.', $domain);
        $domainPartsCount = count($domainParts);

        if($domainPartsCount >= 2) {
            $indexLimit = $domainPartsCount - 2;
            for($index = 0; $index <= $indexLimit; $index++) {

                $licenseFile = $g['path']['url_main'] . $this->dir_license . 'lic-' . $this->project . '-' . $domain . '.txt';

                @file_put_contents($licenseFile, $license);
                if(!file_exists($licenseFile)) {
                    $this->error = "<b>Can't create license file<br></b>";
                    return false;
                }

                unset($domainParts[$index]);
                $domain = implode('.', $domainParts);
            }
        }


        return true;
    }

    public static function saveVersionToConfig($connect = true) {
        if($connect && !@DB::connect()) {
            $this->error = "Can't connect to MySql";
            return false;
        }

        $version = @file_get_contents(__DIR__ . '/../../v');

        $row = array(

        );

        $sql = 'INSERT INTO `config` SET `module` = "db_info",
            `option` = "version",
            `show_in_admin` = 0,
            `value` = "' . DB::esc($version) . '"';
        DB::execute($sql);

        if($connect) {
            DB::close();
        }

        return true;
    }

    public static function setAdminEmail($connect = true) {

        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $host = trim(preg_replace('/^www\./i', '', $host), '.');

        if($host) {

            if($connect && !@DB::connect()) {
                $this->error = "Can't connect to MySql";
                return false;
            }

            $adminEmail = DB::esc('info@' . $host);

            $sql = 'UPDATE `config` SET `value` = "' . $adminEmail . '"
                WHERE `option` = "info_mail" and `module` = "main"';
            DB::execute($sql);

            $replaceChameleon = array(
                'info@demo.chameleonsocial.com' => $adminEmail,
                'Chameleon' => $host,
                'www.Our site.com' => $host,
                'www.site.com' => $host,
                'Our site.com' => $host,
                'Our site' => $host,
            );

            foreach($replaceChameleon as $find => $replace) {
                $find = DB::esc($find);
                $replace = DB::esc($replace);
                $sql = 'UPDATE `pages` SET `content` = REPLACE(`content`, "' .$find . '", "' . $replace . '")';
                DB::execute($sql);

                $sql = 'UPDATE `info` SET `text` = REPLACE(`text`, "' . $find . '", "' . $replace . '")';
                DB::execute($sql);
            }

            if($connect) {
                DB::close();
            }

        }

        return true;
    }

}