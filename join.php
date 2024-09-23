<?php
/* (C) Websplosion LLC, 2001-2021

Gregory 7/3/2023 modified

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "public";
include("./_include/core/main_start.php");
;
// get login_type from user table
$ajax_login = get_param('ajax_login');

$login  = get_param('user_name');
$password = get_param('password');
if($ajax_login) {
    $user_1 = User::getUserByLoginAndPassword($login, $password);
    if(!$user_1) {
        header('Content-Type: application/json');
        $response = array(
            'state' => false
        );
        echo json_encode($response);
        exit;  
    }

    $user = [];

    if($user_1['orientation'] == '5') {
        $user = $user_1;
    }


    if($user){

        $uid = $user['user_id'];
        $user_couple = DB::row("SELECT * FROM user WHERE user_id ='" . to_sql($user['nsc_couple_id'], 'Number') . "'");
    
        header('Content-Type: application/json');
        $partner_t = DB::row("SELECT * FROM var_nickname WHERE id='". $user['partner_type'] ."'");
        $partner_n_t = DB::row("SELECT * FROM var_nickname WHERE id='". $user_couple['partner_type'] ."'");
        $partner_default_t1 = DB::row("SELECT * FROM var_nickname WHERE id='12'");
        $partner_default_t2 = DB::row("SELECT * FROM var_nickname WHERE id='13'");

        $response = array(
            'state' => true,
            'status' => true,
            'partner_type1' => $partner_t['title'] =="" ? $partner_default_t1['title'] :$partner_t['title'],
            'partner_type2' => $partner_n_t['title'] =="" ? $partner_default_t2['title'] :$partner_n_t['title'],

            'message' => 'You have successfully joined!'
        );

        echo json_encode($response);

    } else {
        header('Content-Type: application/json');
        $response = array(
            'state' => true,
            'status' => false,
            'message' => 'You failed in login!'
        );
        echo json_encode($response);
    } 
    exit;
}

$demoSite = get_param('demo_site');
if($demoSite
    && (Common::getOption('tmpl_loaded', 'tmpl') == 'mixer'
        || Common::getTmplSet() == 'urban')) {
    redirect('index.php');
}

$template = 'join.html';

$cmd = get_param('cmd', '');

if($cmd =='login'){

    $eighteen_type = get_param('eighteen_type');

    // if($eighteen_type == "yes") {
    
    // } else if($eighteen_type == "no") {
    //     redirect('https://www.netflix.com');
    // } else {
    //     redirect('https://www.netflix.com');
    // }   

    $login_user = User::getUserByLoginAndPassword($login, $password);
    $login_type = get_param('login_type');
    if($login_type == '2') {
        DB::execute("UPDATE user SET login_type = 0 where user_id ='" . $login_user['user_id'] . "' ;");
        $l_t = $login_type;

        $nsc_new_couple_row = DB::row('SELECT * FROM user WHERE user_id = ' . to_sql($login_user['user_id'], 'Number'), 1);
        if($nsc_new_couple_row['orientation']=="5"){
            if($nsc_new_couple_row['nsc_couple_id']>0){
                DB::execute("UPDATE user SET login_type = '" . $l_t . "' where user_id ='" . to_sql($nsc_new_couple_row['nsc_couple_id'], 'Number') . "'");
            }
        }
    } else if($login_type == '1') {
        DB::execute("UPDATE user SET login_type = '".get_param(('login_type'))."' where user_id ='" . $login_user['user_id'] . "' ;");
        $l_t = $login_type;

        $nsc_new_couple_row = DB::row('SELECT * FROM user WHERE user_id = ' . to_sql($login_user['user_id'], 'Number'), 1);
        if($nsc_new_couple_row['orientation']=="5"){
            if($nsc_new_couple_row['nsc_couple_id']>0){
                DB::execute("UPDATE user SET login_type = 0 where user_id ='" . to_sql($nsc_new_couple_row['nsc_couple_id'], 'Number') . "'");
            }
        }
    } else {
        DB::execute("UPDATE user SET login_type = '".get_param(('login_type'))."' where user_id ='" . $login_user['user_id'] . "' ;");

        $l_t = $login_type;

        $nsc_new_couple_row = DB::row('SELECT * FROM user WHERE user_id = ' . to_sql($login_user['user_id'], 'Number'), 1);
        if($nsc_new_couple_row['orientation']=="5"){
            if($nsc_new_couple_row['nsc_couple_id']>0){
                DB::execute("UPDATE user SET login_type = '" . $l_t . "' where user_id ='" . to_sql($nsc_new_couple_row['nsc_couple_id'], 'Number') . "'");
            }
        }
    }
}

if($cmd=='get_email'){
    $user = User::getUserByLoginAndPassword($login, $password);
    if($user){
        $email = $user['mail'];
        echo json_encode(['status'=>'success', 'email'=>$email]);
    } else{
        echo json_encode(['status'=>'error']);
    }
    exit;
}
if(($cmd == 'login' || $cmd == 'please_login')
    && Common::getOption('login_page_template', 'template_options')) {
    redirect(Common::getHomePage());
    $template = Common::getOption('login_page_template', 'template_options');
}
if(($cmd == 'register'  || $cmd == '' || $cmd == 'wait_approval')
    && Common::getOption('register_page_template', 'template_options')) {
    $template = Common::getOption('register_page_template', 'template_options');
    $g['page_mode'] = 'for_join_page';
    set_session("j_couple_type", '');
}

if (get_session('ref_login_link') == '') {

    $refererFromSite = Common::refererFromSite();

    if(strpos($refererFromSite, 'join_facebook.php') === false) {
        set_session('ref_login_link', $refererFromSite);
    }
}

$ajax = get_param('ajax');
if($ajax) {
    if ($cmd == 'register') {
        $page = new CJoinForm('join', null);
        $page->init();
        set_session("j_couple_type", '');

        echo $page->responseData;
    } else {
        $page = new CJoinPage('', '', '', '', true);
        $page->action(false);
        echo $page->message;
    }
    die();
}

Common::mainPageSetRandomImage();

$page = new CJoinPage("", preparePageTemplate($template));
$header = new CHeader("header", $g['tmpl']['dir_tmpl_main'] . "_header_login.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_main'] . "_footer.html");
$page->add($footer);

$register = new CJoinForm("join", null);
$page->add($register);

include("./_include/core/main_close.php");