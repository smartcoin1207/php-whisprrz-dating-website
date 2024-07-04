<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */


class CssPage extends CHtmlBlock
{
  	function action()
    {
        header('Content-type: text/css');
        header("Cache-Control: public");
        header("Pragma: cache");
        header('Expires: '. date('D, d M Y H:i:s \G\M\T', time() + 60 * 60 * 24 * 365));
    }

	function parseBlock(&$html)
	{

        global $g;

		$p = get_param('page');

        $optionTemplateName = Common::getTmplName();
        if ($optionTemplateName == 'edge') {
            if (guid()) {
                $vars = array('gallery_image_height', 'gallery_image_height_mobile');
                foreach ($vars as $key => $value) {
                    $galleryImageHeight = Common::getOptionInt($value, 'edge_gallery_settings');
                    if ($galleryImageHeight <= 0 || $galleryImageHeight < 20) {
                        $galleryImageHeight = 20;
                    } elseif ($galleryImageHeight > 100) {
                        $galleryImageHeight = 100;
                    }
                    $html->setvar($value, $galleryImageHeight);
                }
                $html->parse('member_settings', false);
            }
            parent::parseBlock($html);
            return;
        }

		if ($html->blockExists('scheme_visitor')
                && Common::isOptionActive('color_scheme_activate', 'template_options')
                    && !in_array($p, array('about.php', 'contact.php', 'page.php', 'info.php'))) {
            if (guid() || $p == 'join2.php') {
                $color = '#254c8e';
                $fontColorText = isColorBright($color) ? '#000000' : '#FFFFFF';
                $html->setvar('font_color_text', $fontColorText);
                $html->parse('scheme_member', false);
            } else {
                $html->setvar('main_page_header_background_color', Common::getOption('main_page_header_background_color'));
                $colors = User::getColorScheme();
                $html->setvar('body_background_color', $colors['upper']);
                $html->setvar('image_background_color', $colors['lower']);
                $fontColorText = ($colors['lower'] === null || isColorBright($colors['lower'])) ? '#000000' : '#FFFFFF';
                $html->setvar('font_color_text', $fontColorText);
                $block = 'scheme_visitor';
                if (Common::getOption('image_main_page_urban') != 'no_image') {
                    $html->parse("{$block}_bg_image", false);
                } else {
                    $html->parse("{$block}_bg_color", false);
                }
                $html->parse($block, false);
            }
		}

        if (Common::isOptionActive('map_on_main_page_urban', 'template_options')) {
            $color = Common::getOption('header_color_urban');
            $html->setvar('header_color_urban', $color);
        }

        if (Common::isOptionActive('tiled_footer_urban', 'template_options')
            && ($html->varExists('footer_tile_url') || $html->varExists('footer_solid_color'))) {
            $footerTileUrl = Common::getOption('url_tmpl_main', 'tmpl') . 'images/empty.gif';
            if (Common::getOption('tiled_footer_urban') == 'tiled') {
                $file = Common::getOption('footer_tile_image_urban');
                $footerTileUrl = getFileUrl('footer_tiles', $file, '_footer_tile_image_', 'footer_tile_image_urban', 'footer_tile_image_default_urban');
                $footerTileWidth = intval(Common::getOption('footer_tile_image_width_urban'));
                if ($footerTileWidth && $footerTileWidth >= 1920) {
                    $html->parse('footer_tile_image_big', false);
                }
            } else {
                $color = Common::getOption('footer_solid_color_urban');
                $html->setvar('footer_solid_color', $color);
                $html->parse('footer_solid_color', false);
            }
            $html->setvar('footer_tile_url', $footerTileUrl);
        }

        $option = 'footer_image_urban';
        if (Common::isOptionActive($option, 'template_options')) {
            $file = Common::getOption($option);
            $image = getFileUrl('footer_image', $file, '_footer_image_', $option, 'footer_image_default_urban');
            if (empty($image)) {
                $image = Common::getOption('url_tmpl_main', 'tmpl') . 'images/empty.gif';
            }
            $html->setvar('footer_image_url', $image);
        }

        if (Common::isOptionActive('map_on_main_page_urban', 'template_options')
        && (Common::getOption('map_on_main_page_urban') == 'image')
                or (Common::getOption('map_on_main_page_urban') == 'random_image') )  {
            $color = Common::getOption('background_color_urban');
            $html->setvar('background_color_urban', $color);
            $html->parse('main_page_image_content_style', false);
        }

        $fields = array('i_am_here_to', 'interests');
        if (Common::getOption('name', 'template_options') == 'urban_mobile') {
            $fields = array('interests');
        }
        UserFields::parseFieldsStyle($html, $fields);

        $html->setvar('fb_link_color', Common::getOption('fb_link_color'));
        $html->setvar('fb_link_color_hover', Common::getOption('fb_link_color_hover'));

        if ($html->varExists('service_map')) {
            $html->setvar('service_map', strtolower(Common::getOption('maps_service')));
        }

        $block = 'info_block_transparency';
        if ($html->varExists($block)) {
            $html->setvar($block, round(intval(Common::getOption($block))/100, 2));
        }

        CustomPage::parseCssFile($html);

		parent::parseBlock($html);
    }
}
