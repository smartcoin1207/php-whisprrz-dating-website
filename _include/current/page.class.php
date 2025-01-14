<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class Page {

    static function addParts(&$page, $partsPage = false, $partsHeader = false, $partsFooter = false)
    {
        global $g;

        $header = new CHeader("header", $g['tmpl']['tmpl_loaded_dir'] . "_header.html");
        $footer = new CFooter("footer", $g['tmpl']['tmpl_loaded_dir'] . "_footer.html");

        if (is_array($partsPage) && count($partsPage)) {
            foreach ($partsPage as &$part) {
                $page->add($part);
            }
        } elseif ($partsPage) {
            $page->add($partsPage);
        }


        if (is_array($partsHeader) && count($partsHeader)) {
            foreach ($partsHeader as &$part) {
                $header->add($part);
            }
        } elseif ($partsHeader) {
            $header->add($partsHeader);
        }

        if (is_array($partsFooter) && count($partsFooter)) {
            foreach ($partsFooter as &$part) {
                $footer->add($part);
            }
        } elseif ($partsFooter) {
            $footer->add($partsFooter);
        }

        $page->add($header);
        $page->add($footer);

        return $page;
    }

}