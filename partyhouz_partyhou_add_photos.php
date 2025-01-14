<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
require_once("./_include/current/partyhouz/custom_head.php");
require_once("./_include/current/partyhouz/header.php");
require_once("./_include/current/partyhouz/sidebar.php");
require_once("./_include/current/partyhouz/tools.php");
require_once("./_include/current/partyhouz/partyhou_show.php");
require_once("./_include/current/partyhouz/partyhou_image_list.php");
require_once("./_include/current/partyhouz/partyhou_guest_list.php");
require_once("./_include/current/partyhouz/partyhou_comment_list.php");

class Cpartyhouz extends CHtmlBlock
{
	function action()
	{
		global $g_user;
        global $l;
        global $g;

        $cmd = get_param('cmd');
        if($cmd == 'save')
        {
            $partyhou_id = intval(get_param('partyhou_id'));
            if($partyhou_id)
            {
                $partyhou = CpartyhouzTools::retrieve_partyhou_by_id($partyhou_id);
                if($partyhou)
                {
                    $guests = CpartyhouzTools::getGuestUsers($partyhou_id);

                    $is_guest = false;
                    
                    foreach ($guests as $key => $guest) {
                        if($guest['user_id'] == $g_user['user_id']) {
                            $is_guest = true;
                            break;
                        }
                    }

                    if(!$is_guest && $g_user['user_id'] != $partyhou['user_id']) {
                        redirect('partyhouz_partyhou_show.php?partyhou_id=' . $partyhou_id);
                    }

                    $time = DB::result('SELECT NOW()');
                    for($image_n = 1; $image_n <= 4; ++$image_n)
                    {
                        $name = "image_" . $image_n;
                        CpartyhouzTools::do_upload_partyhou_image($name, $partyhou_id, $time, $partyhou['partyhou_private'] ? false : true);
                    }

                    redirect('partyhouz_partyhou_show.php?partyhou_id=' . $partyhou['partyhou_id']);
                }
            }
            redirect('partyhouz.php');
        }
	}

	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		global $g;

        $partyhou_id = get_param('partyhou_id');

		$partyhou = CpartyhouzTools::retrieve_partyhou_by_id($partyhou_id);
        if($partyhou)
        {
            $guests = CpartyhouzTools::getGuestUsers($partyhou_id);
            $is_guest = false;
            
            foreach ($guests as $key => $guest) {
                if($guest['user_id'] == $g_user['user_id']) {
                    $is_guest = true;
                    break;
                }
            }

            if(!$is_guest && $g_user['user_id'] != $partyhou['user_id']) {
                redirect('partyhouz_partyhou_show.php?partyhou_id=' . $partyhou_id);
            }

            $html->setvar('partyhou_id', $partyhou['partyhou_id']);
        	$html->setvar('partyhou_title', strcut(to_html($partyhou['partyhou_title']), 20));
            $html->setvar('partyhou_title_full', to_html($partyhou['partyhou_title']));
        }

        parent::parseBlock($html);
	}
}

$page = new Cpartyhouz("", $g['tmpl']['dir_tmpl_main'] . "partyhouz_partyhou_add_photos.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$partyhouz_custom_head = new CpartyhouzCustomHead("custom_head", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_custom_head.html");
$header->add($partyhouz_custom_head);
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$partyhouz_partyhou_show = new Cpartyhouzpartyhouzhow("partyhouz_partyhou_show", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_partyhou_show.html");
$page->add($partyhouz_partyhou_show);
$partyhouz_partyhou_image_list = new CpartyhouzpartyhouImageList("partyhouz_partyhou_image_list", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_partyhou_image_list.html");
$partyhouz_partyhou_show->add($partyhouz_partyhou_image_list);

$partyhouz_header = new CpartyhouzHeader("partyhouz_header", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_header.html");
$page->add($partyhouz_header);
$partyhouz_partyhou_guest_list = new CpartyhouzpartyhouGuestList("partyhouz_partyhou_guest_list", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_partyhou_guest_list.html");
$page->add($partyhouz_partyhou_guest_list);
$partyhouz_sidebar = new CpartyhouzSidebar("partyhouz_sidebar", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_sidebar.html");
$partyhouz_sidebar->m_first_block = "";
$partyhouz_sidebar->m_second_block = "partyhou_show";
$page->add($partyhouz_sidebar);

include("./_include/core/main_close.php");
