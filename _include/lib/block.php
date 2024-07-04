<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

#[AllowDynamicProperties]
class CHtmlBlock
{
	var $m_blocks = Array();
	var $m_name = "";
	var $m_parent = null;
	var $m_root = null;

	var $m_html = null;

    var $state = null;

    var $parseBanner = false;
    static private $urlGetParams = false;

	function __construct($name, $html_path, $isTextTemplate = false, $textTemplate = false, $noTemplate = false)
	{
        if($noTemplate) {
            return;
        }

        global $g;
        global $l;
        global $p;
        global $g;

		$this->m_name = $name;
		$this->m_root = $this;

		if ($html_path != null || $isTextTemplate)
		{
			$this->m_html = new CHtml();
            $this->m_html->LoadTemplate($html_path , "main", $isTextTemplate, $textTemplate);

            /*$cacheKeys = array('arrayKeysCache', 'blockKeysCache', 'blockIncompatibleKeysCache');

            foreach($cacheKeys as $cacheKey) {
                if(isset($g['cache']['html_block_vars'][$cacheKey])) {
                    $this->m_html->$cacheKey = $g['cache']['html_block_vars'][$cacheKey];
                }
            }*/

            if(isset($g['cache']['html_block_vars']['arrayKeysCache'])) {
                $this->m_html->arrayKeysCache = $g['cache']['html_block_vars']['arrayKeysCache'];
            }

			#в хеад хтмла
            if (isset($g['to_head_meta']))
			{
                $tmp = "";
				foreach ($g['to_head_meta'] as $v) $tmp .= $v;
                $this->m_html->setvar("to_head_meta", $tmp);
            }
			if (isset($g['to_head']))
			{
				$tmp = "";
				foreach ($g['to_head'] as $v) $tmp .= $v;
                $tmp = str_replace('.css"', ".css{$g['site_cache']["cache_version_param"]}\"", $tmp);
				$this->m_html->setvar("to_head", $tmp);
			}

			#в хеад хтмла
			if (isset($g['to_js_autostart']))
			{
				$tmp = "";
				foreach ($g['to_js_autostart'] as $v) $tmp .= $v;
				$this->m_html->setvar("to_js_autostart", $tmp);
			}
			#пути
            foreach ($g['main'] as $k => $v)
			{
				#$this->m_html->setvar($k, $v);
                $this->m_html->globals[$k] = $v;
			}
			foreach ($g['path'] as $k => $v)
			{
				#$this->m_html->setvar($k, $v);
                $this->m_html->globals[$k] = $v;
			}
			foreach ($g['tmpl'] as $k => $v)
			{
				#$this->m_html->setvar($k, $v);
                $this->m_html->globals[$k] = $v;
			}

            if(isset($g['site_cache'])){
                foreach ($g['site_cache'] as $k => $v)
                {
                    $this->m_html->globals[$k] = $v;
                }
            }

			$this->m_html->setvar("url_page", $p);
            if ($this->m_html->varExists('url_get_params')) {
                if (self::$urlGetParams === false) {
                    $urlGetParams = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
                    if ($urlGetParams) {
                        $optionTmplName = Common::getOption('name', 'template_options');
                        $delParams = array('set_template',
                                           'set_template_runtime',
                                           'set_template_mobile',
                                           'set_template_mobile_runtime',
                                           'site_part_runtime',
                                           'from',
                                           'ref',
                                           'upload_page_content_ajax',
                                           'ajax',
                                           'edit_field_name',
                                           'site_guid');
                        if ($optionTmplName == 'impact_mobile') {
                            $delParams[] = 'show';
                            $delParams[] = 'photo_id';
							$delParams[] = 'video_show';
                        }
                        foreach ($delParams as $par) {
                            $urlGetParams = del_param($par, $urlGetParams, true, false);
                            if (!$urlGetParams) {
                                break;
                            }
                        }
                        if ($urlGetParams) {
                            $urlGetParams = '?' . $urlGetParams;
                        }
                    }
                    self::$urlGetParams = $urlGetParams;
                }
                $this->m_html->setvar('url_get_params', self::$urlGetParams);
            }

            // Template parser optimization
            foreach($this->m_html->vars as $var => $value) {
                $prfx = array('l_', 'j_', 'a_');
                foreach ($prfx as $prf) {
                    if(strpos($var, $prf) === 0) {
                        $varKey = substr($var, 2);
                        if($varKey) {
                            $str = '';

                            if (isset($l[$p][$varKey])) {
                                $str = $l[$p][$varKey];
                            } elseif (isset($l['all'][$varKey])) {
                                $str = $l['all'][$varKey];
                            }

                            $str = lReplaceSiteTitle($str, $varKey);

                            if ($str) {
                                if ($prf == 'j_') {
                                    $str = toJs($str);
                                } elseif ($prf == 'a_') {
                                    $str = toAttr($str);
                                }
                            }

                            $this->m_html->globals[$var] = $str;
                        }
                        break;
                    }
                }

                if(strpos($var, 'url_page_') === 0 && $var !== 'url_page_params') {
                    $this->m_html->globals[$var] = Common::pageUrl(substr($var, 9));
                }
            }

            /* OLD full language assignment
            foreach ($l['all'] as $k => $v)
			{
                // too much time to work via method
				#$this->m_html->setvar("l_" . $k, $v);
                $this->m_html->globals['l_' . $k] = $v;

			}
			if (isset($l[$p]))
			{
				foreach ($l[$p] as $k => $v)
				{
                    // too much time to work via method
					#$this->m_html->setvar("l_" . $k, $v);
                    $this->m_html->globals['l_' . $k] = $v;
				}
			}
            */
		}
	}

	function init()
	{
		foreach ($this->m_blocks as $n => $b )
		{
			#$b->init();
			$this->m_blocks[$n]->init();
		}
	}

	function action()
	{
		foreach ($this->m_blocks as $n => $b )
		{
			#$b->action();
			$this->m_blocks[$n]->action();
		}
	}

	function add(&$b)
	{
		if ($b->m_name == "")
		{
			trigger_error("Empty block");
			return;
		}
		if (isset($this->m_blocks[$b->m_name]))
		{
			trigger_error("Block " . $b->m_name . " already exists");
			return;
		}
		$this->m_blocks[$b->m_name] = &$b; # php4.3: &$b    php5: $b
		$b->m_parent = $this;
		$b->m_root = $this->m_root;
	}


	#parse only this block
	#don't call this method
	function parseBlock(&$html)
	{
		#if ($this->m_name != "")
		if ($this->m_html != null)
		{
            if ($this->parseBanner) {
                $parseBannerBloksTmpl = Common::getOption('parse_banner_block', 'template_options');
                if (is_array($parseBannerBloksTmpl)) {
                    foreach ($parseBannerBloksTmpl as $block => $prf) {
                        if ($prf) {
                            $prf = '_' . $prf;
                        }
                        if ($this->m_html->blockexists("banner_{$block}{$prf}")) {
                            CBanner::getBlock($this->m_html, $block, $prf);
                        }
                    }
                }
            }

			$html->parse("main");
		}
		else
		{
			$html->parse($this->m_name);
		}
	}


	#parse the block in blocks tree
	#call this method
	function parse(&$html, $return = false)
	{
		if ($this->m_html != null)
		{
			$html_ = &$this->m_html;
		}
		else
		{
			if ($html == null) return;

			$html_ = &$html;
		}
		foreach ($this->m_blocks as $name => $b)
		{
			#$b->parse(&$html_);
			$this->m_blocks[$name]->parse($html_);
		}
		if ($this->m_html != null)
		{
			if ($this->m_parent == null)
			{
                $this->parseBlock($this->m_html);

				if ($return == false) echo $this->m_html->getvar("main");
				if ($return == true) {
                    $this->cache();
                    return $this->m_html->getvar("main");
                }
			}
			else
			{
				#$this->m_html->parse("main");
				$this->parseBlock($this->m_html);

				#if ($html != null)
                $html->setvar($this->m_name, $this->m_html->getvar("main"));
			}

            $this->cache();

		} else {
			#$html->parse($this->m_name);
			$this->parseBlock($html);
		}


	}

    function cache()
    {
        global $g;

        /*$cacheKeys = array('arrayKeysCache', 'blockKeysCache', 'blockIncompatibleKeysCache');

        foreach($cacheKeys as $cacheKey) {
            if(!isset($g['cache']['html_block_vars'][$cacheKey])) {
                $g['cache']['html_block_vars'][$cacheKey] = array();
            }
            $g['cache']['html_block_vars'][$cacheKey] += $this->m_html->$cacheKey;
        }
        */

        if(!isset($g['cache']['html_block_vars']['arrayKeysCache'])) {
            $g['cache']['html_block_vars']['arrayKeysCache'] = array();
        }
        $g['cache']['html_block_vars']['arrayKeysCache'] += $this->m_html->arrayKeysCache;

    }

    function stateSave()
    {
        $this->state = array(
            'globals' => $this->m_html->globals,
            'blocks' => $this->m_html->blocks,
        );
    }

    function stateRestore()
    {
        if($this->state) {
            $this->m_html->globals = $this->state['globals'];
            $this->m_html->blocks = $this->state['blocks'];
        }
    }

    function parseScriptInit($block = '')
    {
        if (!$block) {
            $block = 'load_page_script_init';
        }
        if ($this->m_html != null && $this->m_html->blockexists($block)) {
            $this->m_html->parse($block);
        }
    }

    function parseSEO()
    {
        if ($this->m_html != null) {
            Common::parseSeoSite($this->m_html);
        }
    }
}
?>