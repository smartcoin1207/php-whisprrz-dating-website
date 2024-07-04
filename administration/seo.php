<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('../_include/core/administration_start.php');

class CAdminSeo extends CHtmlBlock
{
	var $message = '';
    var $table = 'seo';

	function action()
	{
        global $p;

		$cmd = get_param('cmd', '');
        $id = get_param('id');
        $url = trim(get_param('url'));
        $title = trim(get_param('title'));
        $description = trim(get_param('description'));
        $keywords = trim(get_param('keywords'));
        $lang = trim(get_param('lang'));

        $isSaved = false;
        $isDeleted = !get_param('not_delete') && !$title && !$description && !$keywords;
		if($cmd == 'add' && !empty($url)) {
            $isSaved = true;

            $sql = 'INSERT INTO ' . $this->table . '
                       SET url = ' . to_sql($url) . ',
                         title = ' . to_sql($title) . ',
                   description = ' . to_sql($description) . ',
                      keywords = ' . to_sql($keywords) . ',
                          lang = ' . to_sql($lang);
            DB::execute($sql);
        }

		if ($cmd == 'update') {
            if ($isDeleted && !$url) {
                $cmd = 'delete';
            } else {
                $isSaved = true;
                $sql = 'UPDATE ' . $this->table . '
                           SET url = ' . to_sql($url) . ',
                         title = ' . to_sql($title) . ',
                   description = ' . to_sql($description) . ',
                      keywords = ' . to_sql($keywords) . '
                      WHERE id = ' . to_sql($id, 'Number');
                DB::execute($sql);
            }
		}

		if ($cmd == 'seo' || $cmd == 'seo_profile' || $cmd == 'seo_profile_group') {
            $isSaved = true;
            $fields = array(
                'title',
                'description',
                'keywords'
            );
            $options = array();
            foreach($fields as $field) {
                $options[$field] = trim(get_param($field));
            }
            Config::updateAll($cmd, $options);
		}

		if ($cmd == 'default_lang') {
            if ($isDeleted) {
                $cmd = 'delete';
            } else {
                $isSaved = true;
                $fields = array(
                    'title',
                    'description',
                    'keywords',
                    'lang',
                    'url'
                );
                $options = array('default' => 1);
                foreach($fields as $field) {
                    $options[$field] = trim(get_param($field));
                }

                $where = '`default` = 1 AND `lang` = ' . to_sql($lang) . ' AND `url` = ' . to_sql($url);
                $sql = 'SELECT id FROM ' . $this->table . ' WHERE ' . $where;
                if(DB::result($sql)) {
                    DB::update($this->table, $options, $where);
                } else {
                    DB::insert($this->table, $options);
                }
            }
		}

        if($cmd == 'delete') {
            $isSaved = true;
            $sql = 'DELETE FROM ' . $this->table . '
                     WHERE id = ' . to_sql($id);
            DB::execute($sql);
			redirect($p . '?action=delete&lang=' . get_param('lang'));
        }

        if($cmd && $isSaved) {
            redirect($p . '?action=saved&lang=' . get_param('lang'));
        }

	}

	function parseBlock(&$html)
	{
		$html->setvar('message', $this->message);

        $options = Config::getOptionsAll('seo');
        foreach($options as $key => $value) {
            $html->setvar('seo_' . $key, he($value));
        }

        $options = Config::getOptionsAll('seo_profile');
        foreach($options as $key => $value) {
            $html->setvar('seo_profile_' . $key, he($value));
        }

        $isGroupSocial = Common::isOptionActiveTemplate('groups_social_enabled');
        if ($isGroupSocial) {
            $options = Config::getOptionsAll('seo_profile_group');
            foreach($options as $key => $value) {
                $html->setvar('seo_profile_group_' . $key, he($value));
            }
        }


        $languageCurrent = Common::langParamValue();
//Profile for all languages
//Profile for this language
        $sql = 'SELECT * FROM ' . $this->table . '
                 WHERE `default` = 1 AND lang = ' . to_sql($languageCurrent);
        $options = DB::rows($sql);
        //print_r_pre($options, true);
        if($options) {
            foreach($options as $option) {
                if (!$isGroupSocial && $option['url'] == 'profile_group') {
                    continue;
                }
                $k = $option['url'] ? $option['url'] . '_' : '';
                foreach($option as $key => $value) {
                    $html->setvar('lang_seo_' . $k . $key, he($value));
                }
            }
        }

        $html->setvar('lang', $languageCurrent);

        adminParseLangsModule($html, $languageCurrent);

        if ($isGroupSocial) {
            $html->parse('group_seo', false);
        }

		if (get_param_int('offset') && $html->blockExists('scroll_to_content')) {
			$html->parse('scroll_to_content', false);
		}

		parent::parseBlock($html);
	}
}

class Cgroups extends CHtmlList
{
	function init()
	{
		parent::init();
		$this->m_on_page = 20;
		$this->m_sql_count = 'SELECT COUNT(*) FROM seo';
		$this->m_sql = 'SELECT * FROM seo';
        $this->m_sql_where = ' lang = ' . to_sql(Common::langParamValue()) . ' AND `default` = 0';

		$this->m_sql_order = ' id ASC ';
		$this->m_field['id'] = array('id', null);
		$this->m_field['url'] = array('url', null);
		$this->m_field['title'] = array('title', null);
		$this->m_field['description'] = array('description', null);
        $this->m_field['keywords'] = array('keywords', null);
        $this->m_field['lang'] = array('lang', null);
	}

	function onItem(&$html, $row, $i, $last)
	{
        $fields = array(
            'url',
            'title',
            'description',
            'keywords',
            'lang',
        );

        foreach($fields as $field) {
            $this->m_field[$field][1] = htmlentities($row[$field], ENT_QUOTES, 'UTF-8');
        }
	}
}

$page = new CAdminSeo('', $g['tmpl']['dir_tmpl_administration'] . 'seo.html');
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);

$group_list = new Cgroups("mail_list", null);
$page->add($group_list);

include('../_include/core/administration_close.php');