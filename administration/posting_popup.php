<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */
// Rade 2023-09-23
include("../_include/core/administration_start.php");

class CAdminPopupPages extends CHtmlBlock {

    var $table = 'posting_info';

    function getTable()
    {
        return $this->table;
    }

    function action()
    {
        global $g;
        $cmd = get_param('cmd', '');

        if ($cmd == 'edit') {
            $lang = Common::langParamValue();
            $page = get_param('page', '');
            $title = get_param('title', '');
            $text = get_param('text', '');
            $header = get_param('header', '');
            $active_text = get_param('active', '');
            $deactive_text = get_param('deactive', '');

            // check that item exists and add otherwise
            $sql = 'UPDATE ' . $this->getTable() . '
				SET page = ' . to_sql($page, 'Text') . ',
                    text = ' . to_sql($text, 'Text') . ',
                    active = ' . to_sql($active_text, 'Text') . ',
                    deactive = ' . to_sql($deactive_text, 'Text') . ',
                    header = ' . to_sql($header, 'Text') . ',
                    lang = ' . to_sql($lang, 'Text') . ''
                    . 'WHERE'
                    . ' page = ' . to_sql($page, 'Text');
            DB::execute($sql);

            global $p;
            redirect($p . '?page=' . $page . '&lang=' . $lang . '&action=saved');
        }
    }

    function parseBlock(&$html)
    {
        $languageCurrent = Common::langParamValue();
        $html->setvar('lang', $languageCurrent);

        $where = '';

        $tmplOptionSet = Common::getOption('set', 'template_options');
        // if($tmplOptionSet == 'urban') {
        //     $where = ' AND page IN ("priv_policy", "term_cond")';
        // }

        $sql = 'SELECT page FROM ' . $this->getTable() . '
            WHERE lang = "default" ' . $where . ' ORDER BY id LIMIT 1';

        $page = get_param('page', '');
        if($page == '') {
            $page = DB::result($sql);
        }

        $html->setvar('page_current', $page);

        $sql = 'SELECT * FROM ' . $this->getTable() . '
            WHERE lang = "default" ' . $where . ' ORDER by id ASC';
        DB::query($sql);

        while ($row = DB::fetch_row()) {
            $html->setvar('page', $row['page']);

            $pageTitle = ucfirst(str_replace('_', ' ', $row['page']));

            $html->setvar('page_title_link', $pageTitle);

            if ($page == $row['page']) {
                $html->parse("mail_on", false);
                $html->setblockvar("mail_off", "");
            } else {
                $html->parse("mail_off", false);
                $html->setblockvar("mail_on", "");
            }
            $html->parse("mail", true);

        }


        $sql = 'SELECT * FROM ' . $this->getTable() . ' '
                . 'WHERE lang = ' . to_sql($languageCurrent, 'Text')
                . ' AND page = ' . to_sql($page, 'Text');
        DB::query($sql);
        if ($row = DB::fetch_row()) {
            $html->setvar('page_current', $row['page']);
            $html->setvar('page_title', htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'));
            $html->setvar('page_text', $row['text']);
            $html->setvar('page_header', $row['header']);
            $html->setvar('page_active', $row['active']);
            $html->setvar('page_deactive', $row['deactive']);
        }


        $languageCurrent = Common::langParamValue();
        $html->setvar('lang', $languageCurrent);

        adminParseLangsModule($html, $languageCurrent);

        parent::parseBlock($html);
    }

}

$page = new CAdminPopupPages("", $g['tmpl']['dir_tmpl_administration'] . "posting_popup.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");
?>