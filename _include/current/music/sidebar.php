<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

require_once('tools.php');

class CMusicSidebar extends CHtmlBlock
{
	var $m_first_block = "top_plays";
	var $m_second_block = "top_rated";
	
	function parseBlock(&$html)
	{
		global $g_user;
		global $l;
		
		$this->parseSubBlock($html, $this->m_first_block, 1);
		$this->parseSubBlock($html, $this->m_second_block, 2);

		parent::parseBlock($html);
	}

    function parseSubBlock(&$html, $block_type, $block_n)
    {
        global $g_user;
        global $l;
        global $g;
        
        $block_title = isset($l['all']['music_' . $block_type]) ? $l['all']['music_' . $block_type] : ('music_' . $block_type);
        $html->setvar('block_title', $block_title);  
    	
        switch($block_type)
        {
            case "top_plays":
                $sql_base = CMusicTools::songs_top_plays_sql_base();
            	break;
            case "top_rated":
                $sql_base = CMusicTools::songs_top_rated_sql_base();
                break;
            case "just_added":
                $sql_base = CMusicTools::songs_recent_sql_base();
                break;
            default:
                $sql_base = CMusicTools::songs_most_discussed_sql_base();
                break;
        }
    	
    	$html->setvar('block_type', $block_type);
    	
        $songs = CMusicTools::retrieve_from_sql_base($sql_base, 2);
        $song_n = 1;

        foreach($songs as $song)
        {
            $html->setvar('song_id', $song['song_id']);
            $html->setvar('song_title', strcut(to_html($song['song_title']), 20));
            $html->setvar('song_title_full', to_html(he($song['song_title'])));
            $html->setvar('song_length', $song['song_length']);
            $html->setvar('song_n_plays', $song['song_n_plays']);
            $html->setvar('song_n_comments', $song['song_n_comments']);
            $html->setvar('song_player', 
                    CMusicTools::song_player(
                        $song['song_id'], 
                        $song['song_length'], 
                        3, 
                        "LittleClipPlayer.swf", 
                        94, 
                        26));
                        
            $html->setvar('musician_id', $song['musician_id']);
            $html->setvar('musician_name', strcut(to_html($song['musician_name']), 20));
            $html->setvar('musician_name_full', to_html(he($song['musician_name'])));

            $rating = $song['song_rating'];
            
            for($rating_n = 1; $rating_n <= 10; ++$rating_n)
                $html->setvar('music_song_rating_'.$rating_n.'_checked', ($rating_n == $rating) ? 'checked="checked"' : '');
            
            $images = CMusicTools::song_images($song['song_id'], $song['musician_id']);
            $html->setvar("image_thumbnail", $images["image_thumbnail"]);
            
            if($song_n != count($songs))
                $html->parse("song_" . $block_n . "_not_last");
            else
                $html->setblockvar("song" . $block_n . "_not_last", '');
            
            $html->parse("song_" . $block_n);
            
            ++$song_n;
        }
    	
    	$html->parse('block_' . $block_n);
    }
}

