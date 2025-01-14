<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
$area = "login";
<?php
include('./_include/core/main_start.php');
include('./_include/current/glass/start.php');

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

glass_render_page();
include('./_include/core/main_close.php');
?>
