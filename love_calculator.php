<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "login";
include("./_include/core/main_start.php");
include ("./_include/current/lovecalc.php");


if(!Common::isOptionActive('love_calculator')) {
   redirect(Common::toHomePage());
}
payment_check('love_calc');


class CLoveCalculator extends CHtmlBlock {

    var $love_level;
    var $name;
    var $myname;
    var $photo_id;
    var $myphoto_id;

    function action()
    {
        global $g_user;
        global $g;

        $id = get_param("id", 0);

        if ($id == 0 || $id == $g_user['user_id']) {
            Common::toHomePage();
        }

        // USER_NAME
        $sql = 'SELECT `name` FROM `user`
            WHERE `user_id` = ' . to_sql($id, 'Number');
        DB::query($sql);

        if ($row = DB::fetch_row()) {
            $this->name = $row['name'];
            $this->myname = $g_user['name'];
            $calc = new lovecalc($this->myname, $this->name);
            $this->love_level = $calc->getlove();
        } else {
            Common::toHomePage();
        }

        // PHOTO
        $this->photo_id = User::getPhotoDefault($id, 'm');

        // MYPHOTO
        $this->myphoto_id = User::getPhotoDefault($g_user['user_id'], 'm');
    }

    function parseBlock(&$html)
    {
        $html->setvar("love_level", $this->love_level);
        $html->setvar("name", $this->name);
        $html->setvar("myname", $this->myname);
        $html->setvar("photo", $this->photo_id);
        $html->setvar("myphoto", $this->myphoto_id);
        parent::parseBlock($html);
    }

}

$page = new CLoveCalculator("", $g['tmpl']['dir_tmpl_main'] . "love_calculator.html");
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

include("./_include/core/main_close.php");

?>