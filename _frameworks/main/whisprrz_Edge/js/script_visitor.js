$(function(){
    $('#login_main_page').click(function(){
        var $loginFrm=$jq('#frm_login_page');
        if ($loginFrm[0]) {
            var sel='#frm_login_page';//$('#frm_login_page_icon').is(':visible')?'#frm_login_page_icon':'#frm_login_page';
            clMediaTools.scrollToEl(sel, function(){
                $jq('#frm_login_page_name').focusEl();
            })
            return false;
        }
        $loginFrm=$jq('#frm_login_header');
        if ($loginFrm[0]) {
            if ($loginFrm.is(':visible')) {
                clMediaTools.scrollTop(function(){
                    $jq('#frm_login_header_name').focusEl();
                })
                return false;
            }
        }
        redirectUrl(urlPagesSite.login);
        return false;
    })

    $('.join_main_page').click(function(){
        if ($jq('#frm_join_page')[0]) {
            clMediaTools.scrollToEl('#frm_join_page', function(){
                $jq('#join_email').focusEl();
            })
            return false;
        }
        redirectUrl(urlPagesSite.join);
        return false;
    })

    /*if(isMobileSite){
        var $blBackground=$('.cham-cover-style-2, .wrap_cham-cover-text');
        if($blBackground[0]){
            setWndResizeEvent(function(){
                if($('input:focus')[0]){
                    alert(1);
                    return;
                }
                $blBackground.height($win.height())
            })
        }
    }*/

    /*if(isMobileSite&&typeof window.orientation!='undefined'){
        var $blBackground=$('.cham-cover-style-2, .cham-cover-text');
        if($blBackground[0]){
            var fnSetBgResize=function(){$blBackground.height($win[0].innerHeight)};
            //setWndResizeEvent(fnSetBgResize)
            console.log('Height:', $win.height(), $win[0].innerHeight);
            fnSetBgResize();
            $win.on('orientationchange',function(){
                setTimeout(fnSetBgResize,evWndResTime)
            })
        }
    }*/

    var $frmLogin=$('.login_form_module');
    if ($frmLogin[0]) {
        $('.login_form_module').each(function(){
            var $blForm=$(this);
            initLoginFrmSite($blForm,function(){
                hideError($("input[name='user']", $blForm));
            })
        })
    }

    var $frmJoin=$('#frm_join_page');
    if ($frmJoin[0]) {
        initJoinFrmSite($frmJoin,
           function($el,msg, hide, nofocus){showError($el, msg, hide, nofocus)},
           function($el){hideError($el)},
           function($el){focusError($el)},
           function($el){blurError($el)})
    }

    var $ppForgotEmail=$('#pp_resend_password_email');
    if ($ppForgotEmail[0]) {
        $ppForgotEmail.on('change propertychange input',function(){
            var email=trim($ppForgotEmail.val()),
                is=checkEmail(email);
            hideError(this);
            $ppForgotSubmit.prop('disabled',!is);
            return is;
        }).keydown(doOnEnter(function(){
            if(!$ppForgotSubmit.prop('disabled'))$ppForgotSubmit.click()
        }))

        var $ppForgotSubmit=$jq('#pp_resend_password_submit').click(function(){
            var url=url_main+'forget_password.php?ajax=1&mail='+trim($ppForgotEmail.val());
            $ppForgotEmail.prop('disabled', true);
            $ppForgotSubmit.addChildrenLoader().prop('disabled', true);
            $.get(url, function(data){
                if(data == 'link_send'){
                    $jq('#pp_forgot').on('hidden.bs.modal', function(){
                        alertCustom(l('the_link_for_changing_password_has_been_sent'), l('alert_success'));
                    }).modal('hide');
                    $ppForgotEmail.prop('disabled', false);
                }else{
                    showError($ppForgotEmail, data)
                }
                $ppForgotSubmit.removeChildrenLoader();
            })
        })
    }

    // sync remember checkbox for desktop and mobile
    $('input[name="remember_lg"]').change(function(){
        $('input[name="remember"]').prop('checked', $(this).prop('checked'));
    });

    $('input[name="remember"]').change(function(){
        $('input[name="remember_lg"]').prop('checked', $(this).prop('checked'));
    });

})

function setBgResizeMainPage(){
    if(isMobileSite&&typeof window.orientation!='undefined'){

		if($('.cham-cover-style-2').hasClass('cham-cover-style-top')) {
			return;
		}

        var $blBackground=$('.cham-cover-style-2, .cham-cover-text');
        if($blBackground[0]){
            var fnSetBgResize=function(){
				var setBgHeight = $win[0].innerHeight;
				if(!setBgHeight) {
					//console.log('fnSetBgResize', 'exit zero', setBlBackground);
					return;
				}
				$blBackground.height(setBgHeight);
				//console.log('fnSetBgResize', setBlBackground);
				};
            //setWndResizeEvent(fnSetBgResize)
            if(isIos){
                setTimeout(function(){
                    fnSetBgResize()
                },evWndResTime)
            }else{
               fnSetBgResize();
            }
            $win.on('orientationchange',function(){
                setTimeout(fnSetBgResize,evWndResTime)
            })
        }
    }
}

function loginIn($btn){
    var $blForm=$btn.closest('.login_form_module'),
        $name=$("input[name='user']", $blForm);
    hideError($name)
    loginInSite($btn, $blForm, function(msg){
        showError($name, msg);
        $btn.prop('disabled', true);
    })
}

function showForgotFrm(){
    $jq('#pp_forgot').modal('show');
}

/* Pwa social login */
function OpenWindow( sUri, iWidth, iHeight ) {
    var sWindowName = 'LoginSocial';
    var iRealWidth = iWidth ? iWidth : 600;
    var iRealHeight = iHeight ? iHeight : screen.height - 300;
    var iLeft = Math.round( (screen.width-iRealWidth)/2 );
    var iTop =  Math.round( (screen.height-iRealHeight)/2 ) - 35;
    var sWindowOptions = 'status=yes,menubar=no,toolbar=no';
    sWindowOptions += ',resizable=no,scrollbars=yes,location=no';
    sWindowOptions += ',width='  + iRealWidth;
    sWindowOptions += ',height=' + iRealHeight;
    sWindowOptions += ',left='   + iLeft;
    sWindowOptions += ',top='    + iTop;
    var oWindow = window.open(sUri, sWindowName, sWindowOptions);
    oWindow.focus();
    return oWindow;
}

var pwaWindow = null;
function pwaSocialLogin(el){
    return true;
    if(!isPwaIos)return true;
    var urlSocial=el.href;
    /*var wnd=window.open(
        urlSocial,
        '',
        'width=350,height=250'
    );*/
    $.cookie('pwa_social_callback', 1);
    pwaWindow=OpenWindow(urlSocial, 600, 600);
    if(pwaWindow===null){
        //alert no popups allowed
    }
    return false;
}

if (isPwaIos) {
    $win.on('message', function(e) {
        var ev = e.originalEvent;
        //if (ev.origin != ) return;
        var data = $.parseJSON(ev.data),
            cmd = data.type, data = data.data;
        if (cmd == 'social_callback' && data.url) {
            /*var url=data.url,part=url.split('#');
            url=addUniqueVariableToURL(part[0], 'pwa_social_callback', 1);
            if(part[1])url+='#'+part[1];*/
            $.cookie('pwa_social_callback', 0);
            var url=data.url
            redirectUrl(url);
        }
    })
}
/* Pwa social login */