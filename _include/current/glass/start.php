<?php
/* (C) Websplosion LTD., 2001-2014 */

if (g('options', 'videogallery') != 'Y') redirect('home.php');
if(!isset($checkVidsPaymentOff)) {
    payment_check('videogallery');
}

require_once dirname(__FILE__) . '/includes.php';

class CPage extends CHtmlBlock {
    function init() {
        global $g;
    }
    
    function parseBlock(&$html) {
        if (guser('vid_videos') > 0) {
            $html->parse('my_vids');
        }
        
        if (count(CVidsTools::getSubscriptionsIds()) > 0) {
            $html->parse('my_subscriptions');
        }
        
        // Get most viewed videos
        $itemvs = CVidsTools::getVideosViews('0,4');
        $html->items('itemv', $itemvs, '', 'is_my');
        
        // Configure display settings
        CVidsTools::$numberTrim = 42;
        CVidsTools::$hardTrim = true;
        
        // Get newest videos
        $items = CVidsTools::getVideosNew('0,4');
        
        // Render grid
        echo '<div class="eca_glass_grid">';
        foreach($items as $item) {
            echo '<div class="eca_glass_item">';
            echo '<div class="eca_glass_thumbnail">';
            echo '<img src="' . $item['thumbnail'] . '" alt="' . $item['title'] . '">';
            echo '<span class="eca_glass_duration">' . $item['duration'] . '</span>';
            echo '</div>';
            echo '<div class="eca_glass_info">';
            echo '<h3 class="eca_glass_title">' . $item['title'] . '</h3>';
            echo '<div class="eca_glass_meta">';
            echo '<span class="eca_glass_date">' . date('m/d/Y', $item['date']) . '</span>';
            echo '</div></div></div>';
        }
        echo '</div>';
        
        $html->items('item', $items, '', 'is_my');
        parent::parseBlock($html);
    }
}

function glass_render_page() {
    global $page;
    global $g;
    global $g_info;

    if (class_exists('CPage', false)) {
        $page = new CPage("", $g['tmpl']['dir_tmpl_main'] . curpage() . ".html");
        
        $header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
        $page->add($header);
        
        $footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
        $page->add($footer);
        
        $bheader = new CGlassHeader("glass_header", $g['tmpl']['dir_tmpl_main'] . "_glass_header.html");
        $page->add($bheader);
        
        $bside = new CGlassSide("glass_side", $g['tmpl']['dir_tmpl_main'] . "_glass_side.html");
        $page->add($bside);
        
        $bfooter = new CGlassFooter("glass_footer", $g['tmpl']['dir_tmpl_main'] . "_glass_footer.html");
        $page->add($bfooter);
    }
}

glass_render_page();
include('./_include/core/main_close.php');
?>
