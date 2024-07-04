<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

$page = get_param('page');
if (!$page || !DB::count('pages', '`id` = ' . to_sql($page) . ' OR `parent` = ' .  to_sql($page))) {
    redirect('pages.php');
}

class CCustomPageEdit extends CHtmlBlock
{
    private $message = '';
    private $editSystem = array('menu_bottom_about_us',
                                'menu_bottom_contact_us',
                                'menu_bottom_terms',
                                'menu_bottom_privacy_policy',
                                'menu_terms_edge',
                                'menu_privacy_policy_edge',
                                'social_network_info');
    private $noStyleSystem = array('menu_bottom_about_us',
                                   'menu_bottom_contact_us',
                                   'menu_bottom_affiliates',
                                   'menu_bottom_blogs',
                                   'menu_bottom_videos',
                                   'menu_bottom_terms',
                                   'menu_bottom_privacy_policy',
                                   'menu_bottom_search_results',
                                   'social_network_info');
	function action()
	{
        global $p;

		$cmd = get_param('cmd');

		if ($cmd == 'save')
		{
            $lang = get_param('lang');
            $id = get_param('page');
            $isSystem = intval(get_param('system'));
            $pageKey = get_param('page_key');
            $where = '`parent` = ' . to_sql($id) . ' AND `lang` = ' .  to_sql($lang);
            if ($lang == 'default' || ($isSystem && !in_array($pageKey, $this->editSystem))) {
                $where = '`id` = ' . to_sql($id);
            }

			$data = array(
                'menu_title' => trim(get_param('menu_title')),
                'title' => trim(get_param('title')),
                'content' => trim(get_param('content')),
                'section' => get_param('section'),
                'system' => $isSystem,
            );

            if ($isSystem) {
                $data['menu_title'] = $pageKey;
                if (!in_array($pageKey, $this->editSystem)) {
                    $data = array('menu_style' => intval(get_param('menu_style')!=''));
                }
            }
            if (DB::count('pages', $where)) {
                DB::update('pages', $data, $where);
            } else {
                $data['lang'] = $lang;
                $data['parent'] = $id;
                DB::insert('pages', $data, $where);
            }


            $this->uploadIcon($id);

            if ($isSystem) {
                $posts = array('cmd', 'lang', 'page', 'system', 'section', 'page_key', 'title', 'content');
                foreach ($posts as $key) {
                    unset($_POST[$key]);
                }
                $this->message = updateLanguage($lang);
            }
            if ($this->message == '') {
                redirect("{$p}?lang={$lang}&page={$id}&action=saved");
            }
		}
	}

	function parseBlock(&$html)
	{
		global $g;

        if ($this->message) {
            $html->setvar('message', $this->message);
            $html->parse('message');
        }

        $lang = Common::getOption('administration', 'lang_value');
        $langTinymceUrl =  $g['tmpl']['url_tmpl_administration'] . "js/tinymce/langs/{$lang}.js";
        if (!file_exists($langTinymceUrl)) {
            $lang = 'default';
        }
        $html->setvar('lang_vw', $lang);

        $id = get_param('page');
        $html->setvar('id', $id);

        $languageCurrent = Common::langParamValue();
        $html->setvar('lang', $languageCurrent);

        adminParseLangsModule($html, $languageCurrent);
        $html->parse('block_language');

        $isParentContent = true;
        $isSystem = null;
        $pageKey = 'menu_title';
        $sql = 'SELECT *
                  FROM `pages`
                 WHERE `lang` = ' . to_sql($languageCurrent) .
                 ' AND `parent` = ' . to_sql($id);
        $sqlParent = 'SELECT *
                        FROM `pages`
                       WHERE `id` = ' . to_sql($id);
        if ($languageCurrent == 'default') {
            $isParentContent = false;
            $sql = $sqlParent;
        }

        $lang = loadLanguage(get_param('lang', 'default'), 'main');
        $row = DB::row($sql);
        if ($row) {
            $isSystem = $row['system'];
            $menuTitle = $row['menu_title'];
            if ($isSystem) {
                $pageKey = $menuTitle;
                $menuTitle = he_decode(l($pageKey, $lang));
            }
            $row['menu_title'] = he($menuTitle);
            $html->assign('page', $row);
        } else {
            $row = DB::row($sqlParent);
            $isSystem = $row['system'];
            if ($isSystem) {
                $pageKey = $row['menu_title'];
                $html->setvar('page_menu_title', he(he_decode(l($pageKey, $lang))));
            }
            $html->setvar('page_section', $row['section']);
        }

        $html->setvar('system', intval($isSystem));
        $html->setvar('page_key', $pageKey);

        if ($isSystem && !in_array($pageKey, $this->editSystem)) {
            if (!in_array($pageKey, $this->noStyleSystem)) {
                $html->setvar('menu_style_checked', $row['menu_style'] ? 'checked' : '');
                $html->parse('menu_style');
            }
        } else {
            if (!in_array($pageKey, $this->editSystem)) {
                $html->setvar('domain_name', Common::urlSiteSubfolders());
                $html->parse('link_page', false);
            }
            $html->parse('content');
        }

        if(!$isSystem && CustomPage::isMenuIconUploadAvailable() && $row['section'] == 'narrow') {

            if(CustomPage::isMenuIconExists($id)) {
                $html->setvar('rand', rand(0, 100000));
                $html->setvar('url_page_icon', CustomPage::menuIconUrl($id));
                $html->parse('icon');
            }

            $html->parse('icon_upload');
        }

		if (isset(CAdminHeader::$linkToMainMenuAdmin['news'])) {
			$html->setvar('section_page',  CAdminHeader::$linkToMainMenuAdmin['news']);
		}

		parent::parseBlock($html);
	}

    function uploadIcon($id, $prf = '')
	{
        global $g;

		$icon = false;
		$fileTemp = $g['path']['dir_files'] . 'temp/admin_upload_icon_' . time();
		$fileImageData = Common::uploadDataImage($fileTemp);
		if ($fileImageData) {
			$icon = $fileImageData;
		} elseif (isset($_FILES['icon'])) {
			$icon = $_FILES['icon']['tmp_name'];
		}

        if (!empty($icon) && Image::isValid($icon)) {
                $image = new uploadImage($icon);
                if ($image->uploaded) {
                    $image->file_safe_name = false;
                    $image->image_resize = true;
                    $image->image_ratio = true;
                    $image->image_convert = 'png';
                    $image->png_compression = 0;
                    $image->image_y = 22;
                    $image->image_x = 25;
                    $image->file_new_name_body = $id . $prf;
                    $image->file_new_name_ext = 'png';
                    $image->Process($g['tmpl']['dir_tmpl_main'] . 'images/menu_icons/');
                    unset($image);
                }
		}

    }
}

$page = new CCustomPageEdit("", $g['tmpl']['dir_tmpl_administration'] . "pages_edit.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");