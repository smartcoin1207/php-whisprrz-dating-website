<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CForm extends CHtmlBlock {

    var $tables = array(
        'terms',
        'tips',
        'faq',
    );

    function table()
    {
        $table = get_param('t');

        if (!in_array($table, $this->tables)) {
            $table = $this->tables[0];
        }

        return $table;
    }

    function action()
    {
        $cmd = get_param('cmd', '');
        $table = $this->table();

        $name = get_param('name', '');
        $text = get_param('text', '');
        $lang = Common::langParamValue();
        $id = get_param('id', '');

        if ($cmd == "delete") {
            $sql = 'DELETE FROM partner_' . $table . '
                WHERE id = ' . to_sql($id);
            DB::execute($sql);
        } elseif ($cmd == "edit") {
            DB::execute("
				UPDATE partner_" . $table . "
				SET
				name=" . to_sql(get_param("name", ""), "Text") . ",
				text=" . to_sql(get_param("text", ""), "Text") . "
				WHERE id=" . to_sql(get_param("id", ""), "Number") . "
			");

            redirect("partner_pages.php?t=$table&lang=$lang&action=saved");
        } elseif ($cmd == "add") {
            DB::execute("
				INSERT INTO partner_$table (name, text, lang)
				VALUES(
				" . to_sql($name) . ",
				" . to_sql($text) . ",
				" . to_sql($lang) . ")
			");

            redirect("partner_pages.php?t=$table&lang=$lang");
        }
    }

    function parseBlock(&$html)
    {
        $table = $this->table();
        $html->setvar('table', $table);

        $lang = Common::langParamValue();
        $html->setvar('lang', $lang);

        $langs = Common::listLangs('main');
        $langs = h_options($langs, $lang);
        $html->setvar('langs', $langs);

        DB::query("SELECT * FROM partner_$table WHERE lang = " . to_sql($lang) . " ORDER BY id");
        while ($row = DB::fetch_row()) {
            foreach ($row as $k => $v) {
                $html->setvar($k, htmlspecialchars($v, ENT_QUOTES, 'UTF-8'));
            }

            $html->parse("question", true);
        }

        $html->parse("add", true);

        parent::parseBlock($html);
    }

}

$page = new CForm("", $g['tmpl']['dir_tmpl_administration'] . "partner_pages.html");

$moduleLangs = new CAdminLangs('langs', $g['tmpl']['dir_tmpl_administration'] . "_langs.html");
$page->add($moduleLangs);

$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);


$page->add(new CAdminPageMenuPartner());

include("../_include/core/administration_close.php");