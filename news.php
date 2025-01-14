<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("./_include/core/main_start.php");
include("./_include/current/menu_section.class.php");
if(!Common::isOptionActive('news')) {
    redirect(Common::toHomePage());
}
class CNews extends CHtmlList {

    var $m_on_page = 5;

    function init()
    {
        parent::init();

        $this->m_sql_count = "SELECT COUNT(n.id) FROM news AS n " . $this->m_sql_from_add . "";
        $this->m_sql = "
			SELECT n.id, n.title, n.news_short, n.news_long, n.dt, n.cat, n.visible
			FROM news AS n
			" . $this->m_sql_from_add . "
		";

        $this->m_field['id'] = array("id", null);
        $this->m_field['title'] = array("title", null);
        $this->m_field['news_short'] = array("news_short", null);
        $this->m_field['news_long'] = array("news_long", null);
        $this->m_field['cat'] = array("cat", null);
        $this->m_field['date'] = array("date", null);

        //$this->m_debug = 'Y';

        $this->m_sql_order = " id DESC ";
    }

    function onItem(&$html, $row, $i, $last)
    {
        $this->m_field['date'][1] = Common::dateFormat($row['dt'], 'news_date', false);
        $this->m_field['news_short'][1] = nl2br($row['news_short']);
        $this->m_field['news_long'][1] = nl2br($row['news_long']);
        $this->m_field['cat'][1] = DB::result("SELECT title FROM news_cats WHERE id=" . $row['cat'] . "", 0, 2);
        if ($this->m_field['cat'][1] == "") {
            $this->m_field['cat'][1] = " ";

        }
    }

}

class CCats extends CHtmlBlock {

    function parseBlock(&$html)
    {
        $lang = Common::getOption('lang_loaded', 'main');
        if(Common::isOptionActive('help')) {
            $html->parse('help_on');
        }
        if(Common::isOptionActive('contact')) {
            $html->parse('contact_on');
        }
        DB::query("SELECT id, title FROM news_cats WHERE lang = " . to_sql($lang) . " ORDER BY id");

        while ($row = DB::fetch_row()) {
            if (get_param("cat", "") == $row['id']) {
                $html->setvar("id", $row['id']);
                $html->setvar("title_cat", $row['title']);
                $html->parse("cat_this", false);
                $html->setblockvar("cat_other", "");
            } else {
                $html->setvar("id", $row['id']);
                $html->setvar("title_cat", $row['title']);
                $html->parse("cat_other", false);
                $html->setblockvar("cat_this", "");
            }
            $html->parse("cat", true);
        }


        DB::query("SELECT id, title, news_short, dt, cat, visible FROM news WHERE visible='Y' AND cat=0 AND lang = " . to_sql($lang) . " ORDER BY id");
        $i = 1;
        while ($row = DB::fetch_row()) {
            $row['offset'] = $i;
            foreach ($row as $k => $v) {
                if ($k == "title") {
                    $html->setvar("title_news", $v);
                } else {
                    $html->setvar($k, $v);
                }
            }
            // NEWS ACTIVE
            if (get_param("view", "") == $row['id']) {
                $html->parse("cat_news_this", true);
            } else {
                $html->parse("cat_news", true);
            }
            $html->parse("news_cat", true);
            $html->setblockvar("cat_news_this", "");
            $html->setblockvar("cat_news", "");

            $i++;
        }

        parent::parseBlock($html);
    }

}

$page = new CHtmlBlock("", $g['tmpl']['dir_tmpl_main'] . "news.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$lang = Common::getOption('lang_loaded', 'main');

$cats = new CCats("cats", null);
$page->add($cats);
$cat = intval(get_param('cat', ''));
$view = intval(get_param('view', ''));
$key = get_param('keyword', '');

if ($cat) {
    $sql = 'SELECT * FROM news_cats WHERE id = ' . to_sql($cat);
    $info = DB::row($sql);
    if ($info['lang'] != $lang) {
        if ($info['root']) {
            $sql = 'SELECT id FROM news_cats WHERE root_id = ' . to_sql($cat) . '
                AND lang = ' . to_sql($lang);
            $newsId = DB::result($sql);
            if ($newsId) {
                redirect('news.php?cat=' . $newsId);
            }
        }
        redirect('news.php');
    }

    $news = new CNews('news', null);
    $where = ' AND cat = ' . to_sql($cat);
} elseif ($view) {
    $sql = 'SELECT * FROM news WHERE visible = "Y" AND id = ' . to_sql($view);
    $info = DB::row($sql);
    if ($info['lang'] != $lang) {
        if ($info['root']) {
            $sql = 'SELECT id FROM news WHERE root_id = ' . to_sql($view) . '
                AND lang = ' . to_sql($lang);
            $newsId = DB::result($sql);
            if ($newsId) {
                redirect('news.php?view=' . $newsId);
            }
        } else {
            redirect('news.php?view=' . $info['root_id']);
        }
        redirect('news.php');
    }
    $news = new CNews('news_view', null);
    $where = ' AND id = ' . to_sql($view, 'Number');
} elseif ($key != '') {
    $news = new CNews('news', null);
    if ($key != '') {
        $where = " AND (title LIKE '%" . to_sql($key, "Plain") . "%' OR news_short LIKE '%" . to_sql($key, "Plain") . "%' or news_long LIKE '%" . to_sql($key, "Plain") . "%')";
    }
    #$where .= " AND cat!=0";
} else {
    $news = new CNews('news', null);
    $sql = 'SELECT id FROM news
        WHERE visible = "Y"
            AND lang = ' . to_sql($lang) . '
        ORDER BY id DESC';
    $id = DB::result($sql);
    $where = ' AND id = ' . to_sql($id);
}


$news->m_sql_where = " visible='Y' " . $where . ' AND lang = ' . to_sql($lang);
$mailMenu = new CMenuSection('menu_help', $g['tmpl']['dir_tmpl_main'] . "_menu_help.html");
$mailMenu->setActive('news');
$page->add($mailMenu);
$page->add($news);

include("./_include/core/main_close.php");
?>