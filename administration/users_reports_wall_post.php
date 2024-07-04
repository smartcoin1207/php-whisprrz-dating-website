<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('../_include/core/administration_start.php');

class CUsersReports extends AdminReportsContent
{
	function init()
	{
		global $g;

        $this->m_on_page = 20;
		$this->m_on_bar = 10;

		$this->m_sql_count = "SELECT COUNT(UR.id) FROM users_reports AS UR " . $this->m_sql_from_add;
		$this->m_sql = "SELECT UR.*, UF.name AS name_from, UT.name AS name_to, UT.ban_global AS ban_to, UT.gender AS gender_to
                          FROM users_reports AS UR
                          JOIN user AS UF ON UF.user_id = UR.user_from
                          JOIN user AS UT ON UT.user_id = UR.user_to" . $this->m_sql_from_add;

        $this->m_field['id'] = array("id", null);
        $this->m_field['date'] = array("date", null);
        $this->m_field['user_from'] = array("user_from", null);
        $this->m_field['user_to'] = array("user_to", null);
        $this->m_field['msg'] = array("msg", null);
        $this->m_field['ban_to'] = array("ban_to", null);
        $this->m_field['name_from'] = array("name_from", null);
        $this->m_field['name_to'] = array("name_to", null);
        $this->m_field['wall_id'] = array("wall_id", null);
		$this->m_field['comment_id'] = array("comment_id", null);
		$this->m_field['comment_type'] = array("comment_type", null);
        $this->m_field['video'] = array("video", null);

        $this->m_sql_where = "UR.wall_id != 0 AND UR.group_id = 0";
		$this->m_sql_order = "id";
		#$this->m_debug = "Y";
	}
}

Common::setOptionRuntime('player_native', 'video_player_type');
Common::setOptionRuntime('Y', 'groups_social_enabled', 'template_options');
//Common::setOptionRuntime('edge', 'wall_type', 'template_options');
VideoHosts::setAutoplay(false);
VideoHosts::setMobile(false);
$g['sql']['photo_vis'] = '';
$g['sql']['photo_vis_prf'] = '';

$templateWallSectionsOnly = Common::getOptionTemplate('wall_sections_only');
if (is_array($templateWallSectionsOnly)) {
	Wall::setSiteSectionsOnly($templateWallSectionsOnly);
} else {
    $sections = array(
        'photo',
        'interests',
    );
    Wall::setSectionsHidden($sections);
}

$tmpls = array(
	'main' => $g['tmpl']['dir_tmpl_administration'] . 'users_reports_wall_post.html',
	'wall_item' => $g['tmpl']['dir_tmpl_administration'] . '_wall_post.html'
);
$page = new CUsersReports('main', $tmpls);
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);

$page->add(new CAdminPageMenuBlock());

include('../_include/core/administration_close.php');