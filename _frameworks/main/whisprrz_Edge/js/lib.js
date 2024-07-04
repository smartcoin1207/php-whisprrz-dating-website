var MSG_MAX_LENGTH			= '%1 may only be a maximum of %2 characters long.';
var MSG_MIN_LENGTH			= '%1 must be a minimum of %2 characters long.';
var MSG_REQ_FIELD           = '%1 is a required field.';
var MSG_INVALID_EMAIL       = 'Invalid email address: %1';
var MSG_REQUIRED_SELECT		= 'Please select a value for %1.';
var MSG_ALPHA_NUMERIC		= '%1 may only contain alphanumeric characters.';
var MSG_NUMERIC             = '%1 may only contain numeric characters!';
var MSG_TWO_FIELDS			= '%1 and %2 must be the same.';
var MSG_NOT_TWO_FIELDS      = '%1 and %2 may not have the same value.';
var MSG_INVALID_LOGIN       = '%1 can not contain #, &, \', \\, / or " !';

var userAgentBrowser = navigator.userAgent;

var isMobileBrowserIOS=/iPhone|iPad|iPod/i.test(userAgentBrowser)/* iOS pre 13 */
                       || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);/* iPad OS 13 */
var isMobileIOS=/webOS/i.test(userAgentBrowser) || isMobileBrowserIOS;
var isMobileBrowser=/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(userAgentBrowser);

var h_chat = 388;
var w_chat = 317;
var h_chat_offset = 34;
var siteTopOffset = 0;
var widgetParamsInit = [9, 2, 101];
var cacheElement={};
var isPwaIos = false;
var isDemoSite = false;
var siteGroupId = 0;
var siteGroupUserId = 0;
var siteGroupView = '';
var siteGroupViewList = '';

var isCloseJivoPopup = false;//Support popup demo

if (window.jQuery) {
var $win=$(window), $doc=$(document);
}

if ('standalone' in window.navigator
        && window.navigator['standalone']
        && isMobileBrowserIOS) {
    isPwaIos = true;
}

// Fix console issue in old IE versions.
if (!window.console) console = { log: function(){}, error: function(){} };

var jqTransformDaySelect = false;

var widgetStatus = {};

function validateMaxLength(field, name, maxLength) {
	var value = field.value;
	var originalVal = value;	//store a copy with the \n's in it
	var newVal = "";	//new value with any extra characters removed from it so as not to go over maxLength
	var character = null;
	value = value.replace(/\n/g,'**'); // bug #4830 when the javascript validates it sees \n's and java validates it sees \r\n's so a string may pass javascript validation but fail java validation, solution validate on a copy of the string with all \n's replaced with 2 characters to simulate the java length

	if (value.length > maxLength)
	{
		//loop through the string getting one character at a time.
		//If we encounter a \n we have to count it as 2 characters due to bug #4830
		for(var i=0, count=1; count<=maxLength; i++, count++){
				character = originalVal.charAt(i);

				//if this is a new line char make sure we have 2 spaces available in the new string
				if(character == "\n" && count<=maxLength-1){
					newVal = newVal.concat(character);
					count++;
				}else{
					newVal = newVal.concat(character);
				}
		}

		var msg = MSG_MAX_LENGTH.replace('%1', name);
		msg = msg.replace('%2', maxLength);
		alert(msg);
		try{
			//substitute in the shortened string into the field.
			field.value = newVal;
			field.focus();
		}catch(e){}
		return false;
	}
	return true;
}
function validateMinLength(field, name, minLength) {
	if (field.value.length < minLength) {
		var msg = MSG_MIN_LENGTH.replace('%1', name);
		msg = msg.replace('%2', minLength);
		alert(msg);
		try{field.focus();}catch(e){}
		return false;
	}
	else {
		return true;
	}
}
function nonEmptyDependency(field1, field1Name, field2, field2Name, message) {
	if(!isEmpty(field1) && isEmpty(field2)){
		alert(message);
		return false;
	}else{
		return true;
	}
}
function validateRequiredField(field, name, dv, no_msg) {
	try
	{
        if(typeof(field.val()) == 'string') {
            field.value = field.val();
        }
		field.value = trim(field.value);
		dv = trim(dv);
	}
	catch(e) {}

	if (field.value.length == 0 || trim(field.value) == '' || field.value == dv)
	{
        no_msg = no_msg || false;
		if(no_msg==true) alertCustom(name);
		else alertCustom(MSG_REQ_FIELD.replace('%1', name));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}
function validateEmailField(emailField, name) {
	if (isEmpty(emailField)) return true;
	if (!checkEmail(emailField.value)) {
		alert(MSG_INVALID_EMAIL.replace('%1', emailField.value));
		try{emailField.focus();}catch(e){}
		return false;
	}
	return true;
}
function validateRequiredCheckbox(field, name, msg) {
	if (!isCheckBoxChecked(field)) {
		alert(msg.replace('%1', name));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}
function validateRequiredSelect(field, name, defaultValue) {
	if (field.value == null || field.value == '' || field.value == defaultValue) {
		alert(MSG_REQUIRED_SELECT.replace('%1', name).replace('&#39;', "'"));
		try{field.focus();}catch(e){}
		return false;
	}
	else {
		return true;
	}
}
function validateTwoFields(field,name,field2,name2) {
	if (field.value != field2.value){
		var msg = MSG_TWO_FIELDS.replace('%1', name);
		msg = msg.replace('%2', name2);
		alert(msg);
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}
function validateNotTwoFields(field,name,field2,name2) {
	if (field.value == field2.value){
		var msg = MSG_NOT_TWO_FIELDS.replace('%1', name);
		msg = msg.replace('%2', name2);
		alert(msg);
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}
function validateAlphaNumeric(field, name) {
	var mask = /^[_0-9a-zA-Z-\.]*[_0-9a-zA-Z-\.]$/
	if (!mask.test(field.value)) {
		alert(MSG_ALPHA_NUMERIC.replace('%1', name));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

function validateAlphaNumericSpace(field, name) {
	var mask = /^[ _0-9a-zA-Z-\.]*[ _0-9a-zA-Z-\.]$/
	if (!mask.test(field.value)) {
		alert(MSG_ALPHA_NUMERIC.replace('%1', name));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

function validateAlphaNumeric_search(field, name) {
	var mask = /^[_0-9a-zA-Z-\.\s]*[_0-9a-zA-Z-\.\s]$/
	if (!mask.test(field.value)) {
		alert(MSG_ALPHA_NUMERIC.replace('%1', name));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

function validateNumeric(field, name) {
	var val = trim(field.value);
	field.value = val;
	var mask = /^-?[0-9]*(\.)?[0-9]*$/
	if (!mask.test(val)) {
		alert(MSG_NUMERIC.replace('%1', name));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

function validateUserName(field, name, minLength, maxLength) {

    if (!(validateRequiredField(field, name))) {
        return false;
    }
    if (/#|&|'|"|\\|\//.test(field.value)) {
        alert(MSG_INVALID_LOGIN.replace('%1', name));
        try{field.focus();}catch(e){}
        return false;
    }
    if (!(validateMinLength(field, name, minLength))) {
        return false;
    }
    if (!(validateMaxLength(field, name, maxLength))) {
        return false;
    }
    return true;
}

function isEmpty(field) {
	if (field.disabled){return true;}

	if (field.type=='checkbox'||(field[0]&&field[0].type == 'checkbox')) {
		return !isCheckBoxChecked(field);
	}
	if (field.type=='radio'||(field[0]&&field[0].type == 'radio')) {
		return !isCheckBoxChecked(field);
	}
	//Try trim - will fail for input type="file".
	try
	{
		field.value = trim(field.value);
	}
	catch(e) {}
	if (field.value.length == 0) {
		return true;
	}
}
function isCheckBoxChecked(field) {
	if (field[0]) {
		for (i = 0;i<field.length;i++) {
			if (field[i].checked) return true
		}
	}
	return field.checked||false
}
function setFocus(form,field) {
	if (form != '') {
		try	{document.forms[form][field].focus();} catch(e) {}
	}
	else {
		try	{document.forms[0][field].focus();} catch(e) {}
	}
}
function giveFocus(frm, elm) {
  eval("document."+frm+"."+elm+".focus()");
}
function winpop(loc,w,h,scroll) {
	var name = loc.replace(/\W/g, "");
	window.open(loc,name,'width='+w+', height='+h+', location=no, directories=no, menubar=no, scrollbars='+scroll+', resizable=no, status=no, toolbar=no');
}
function getById(id) {return $('#'+id)[0]}
var getRefToDiv=getById;
function div_show(id) {$('#'+id).show()}
function div_hide(id) {$('#'+id).hide()}

function switchdiv(div1_id, div2_id, form) {
	form[div1_id].value=$('#'+div1_id).toggle().is(':visible')
	form[div2_id].value=$('#'+div2_id).toggle().is(':visible')
}
function characterCounter(fieldName, maxLength, elementName) {
	var field = getById(fieldName);
	var value = field.value.replace(/\n/g,'**'); // bug #4830 when the javascript validates it sees \n's and java validates it sees \r\n's so a string may pass javascript validation but fail java validation, solution validate on a copy of the string with all \n's replaced with 2 characters to simulate the java length
	getById(elementName).innerHTML = value.length;
}
function trim(str) {
	str = new String(str);
	return str.replace(/^\s+/,'').replace(/\s+$/,'');
}
function rtrim(str) {
	str = new String(str);
	return str.replace(/\s+$/,'');
}
function ltrim(str) {
	str = new String(str);
	return str.replace(/^\s+/,'');
}
function submitForm(form, action) {
	form.action = action;
	form.submit();
}
function addOnload(f) {
	$(window).load(f)
}
function checkEmail(emailStr) {
	var emailPat=/^(.+)@(.+)$/;
	var specialChars="\\(\\)><@,;:\\\\\\\"\\.\\[\\]!%";
	var validChars="\[^\\s" + specialChars + "\]";
	var quotedUser="(\"[^\"]*\")";
	var ipDomainPat=/^\[(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\]$/;
	var atom=validChars + '+';
	var word="(" + atom + "|" + quotedUser + ")";
	var userPat=new RegExp("^" + word + "(\\." + word + ")*$");
	var domainPat=new RegExp("^" + atom + "(\\." + atom +")*$");

	var matchArray=emailStr.match(emailPat);

	if (matchArray==null) {
		return false;
	}
	var user=matchArray[1];
	var domain=matchArray[2];
	for (i=0; i<user.length; i++) {
		if (user.charCodeAt(i)>127) {
			return false;
		}
	}
	for (i=0; i<domain.length; i++) {
		if (domain.charCodeAt(i)>127) {
			return false;
		}
	}
	if (user.match(userPat)==null) {
		return false;
	}

	var IPArray=domain.match(ipDomainPat);
	if (IPArray!=null) {
		for (var i=1;i<=4;i++) {
			if (IPArray[i]>255) {
				return false;
			}
		}
		return true;
	}

	var atomPat=new RegExp("^" + atom + "$");
	var domArr=domain.split(".");
	var len=domArr.length;
	for (i=0;i<len;i++) {
		if (domArr[i].search(atomPat)==-1) {
			return false;
		}
	}
	if (domArr[len-1].length < 2) {
		return false;
	}
	if (len<2) {
		return false;
	}
	/*mask=/^(root|abuse|webmaster|help|postmaster|sales|resumes|contact|advertising|spam|spamtrap|nospam|noc|admin|support|daemon|listserve|listserver|autoreply)@/i;
	if (mask.test(emailStr.toLowerCase())) {
		return false;
	}*/

	return true;
}
function modFixSelect(element)
{

}
function updateDay(change,formName,yearName,monthName,dayName,fnRefresh,firstValue)
{
	var form = document.forms[formName];
	var yearSelect = form[yearName];
	var monthSelect = form[monthName];
	var daySelect = form[dayName];
	var year = yearSelect[yearSelect.selectedIndex].value*1;
	var month = monthSelect[monthSelect.selectedIndex].value*1;
	var day = daySelect[daySelect.selectedIndex].value*1;

    if(!year&&!month){
        return;
    }

	if (change == 'month' || (change == 'year' && month == 2))
	{
        var i = 31;
        var flag = true;
        while(flag)
        {
            var date = new Date(year,month-1,i);
            if (date.getMonth() == month - 1)
            {
                flag = false;
            }
            else
            {
                i = i - 1;
            }
        }
        daySelect.length = 0;
        daySelect.length = i;
        var j = 0;
        var isUnload = typeof daySelect.unload;
        if ( isUnload == 'function'){
            daySelect.unload();
        }

        if (firstValue) {
            daySelect[0] = new Option(firstValue,0);
            while(j < i){
                daySelect[j+1] = new Option(j+1,j+1);
                j = j + 1;
            }

        } else {
            while(j < i){
                daySelect[j] = new Option(j+1,j+1);
                j = j + 1;
            }
        }

        if (day <= i){
            if (firstValue) {
                daySelect.selectedIndex = day ? day : 0;
            }else{
                daySelect.selectedIndex = day - 1;
            }
        }else{
            daySelect.selectedIndex = daySelect.length - 1;
        }

        if (isUnload == 'function')
        {
            selects(daySelect);
            daySelect.init();
        }
        if(typeof(fnRefresh)=='function'){
            fnRefresh();
        }
	}
}
function checkedCount(field) {
	var checked=0;

	if (field) {
		if (field.length) {
			for (var i = 0 ; i < field.length	; i++) {
				checked+=(field[i].checked||0);
			}
		} else {checked+=(field.checked||0)}
	}
	return checked
}
function isChecked(field) {
	return !!checkedCount(field)
}
function isOneChecked(field) {
	return checkedCount(field) == 1
}

// AJAX LOADER
function show_load_animation(number) {
	$("#load_animation"+(number||"")).css('visibility', 'visible');
}

function hide_load_animation(number) {
	$("#load_animation"+(number||"")).css('visibility', 'hidden');
}
// AJAX LOADER

function getElementsByClass(searchClass, tag) {
	return $((tag||'')+'.'+searchClass);
}

// IM SOUND

function im_sound(sound) {
	xajax_sound(sound^=1);
	$('a.sound_link').each(function(){
		this.className='sound_link status_'+sound;
		this.onclick=function(){im_sound(sound); return false;}
	})
}

// WIDGETS

function widget_show(wid, status){
	var widget=$('#widget_inner_'+wid);
	// save status to database
	xajax_widget_show(wid, widget.is('.hidden')*1);
	setTimeout(function(){widget.toggleClass('hidden')}, 25)
}

function widget_close(wid, home){
	widgetStatusSet(wid, 'closed');
	var el=$('#widget_'+wid).delay(300)
	 .fadeOut(300, function(){el.remove()});
	setTimeout(function(){$('.bl_widget_cont', el).addClass('hidden')}, 25);
	// radio checked
	if (home) return
	$('#widget_'+wid+'_off').prop('checked',1);
	xajax_widget_close(wid,1);
}

function widget_site(wid){
	console.log('widget_site', wid);
	if (widgetIsLoaded(wid) != 'loaded' || !$('#widget_'+wid).appendTo('#xajax_im')[0]) {
		console.log('widget_site', 'loading');
		widgetStatusSet(wid, 'none');
		$('#widget_'+wid).remove();
		xajax_widget_site(wid);
	}
}
function widget_home(wid){
	widget_close(wid, 1)
	xajax_widget_home(wid);
}

function widget_up(wid){

	$('#widget_'+wid).appendTo('#xajax_im')

	//xajax_widget_up(wid,widgets_count + 50,z_old);
}

function widget_down(wid,z){

// for(i=1;i<=widgets_site_count;i++) {
	// widget = document.getElementById('widget_'+i);
	// if(widget) {
		// if(i!=wid) {
			// if(widget.style.zIndex>z) widget.style.zIndex = widget.style.zIndex-1;
		// }

		// }
	// }
	// //alert(wid+"::"+z);

}

function getAbsolutePosition ( elem ) {
var r = {}, pos=$('#'+elem).position();
r = { x:0, y:0 };
elem = document.getElementById(elem);

while (elem) {
r.x += (elem.offsetLeft + elem.clientLeft);
r.y += (elem.offsetTop + elem.clientTop);
elem = elem.offsetParent;
}

// correct position of widget
r.x += 523;
r.y -= 9;

return r;
}

function getAbsolutePositionReal( elem )
{
	r = { x:0, y:0 };
	elem = document.getElementById( elem );

	while ( elem ) {
		r.x += ( elem.offsetLeft + elem.clientLeft );
		r.y += ( elem.offsetTop + elem.clientTop );
		elem = elem.offsetParent;
	}

	return r;
}

function getWHSizes() {
var w = document.documentElement;
var d = document.body;
h = Math.max( w.scrollHeight, d.scrollHeight, w.clientHeight);
wd = Math.max( w.scrollWidth, d.scrollWidth, w.clientWidth);

return {
ww:w.clientWidth, //window width
wh:w.clientHeight, //window height
wsl:w.scrollLeft, //window scroll left
wst:w.scrollTop, //window scroll top
dw:wd, //document width
dh:h //document height
}

}

function moduleDebugLog(msg, val)
{
    var moduleDebugElement = '#module_debug_log';
    $(moduleDebugElement).html( msg + ' : ' + val  + '<br>' + $(moduleDebugElement).html() );
}

var mobileNotifyUpdaterInterval = false;
var mobileNotifyExclude = '';
function mobileNotifyUpdater()
{
	clearInterval(mobileNotifyUpdaterInterval);
	url = 'ajax.php?cmd=check_new_items&dummy=' + new Date().getTime() + '&exclude=' + mobileNotifyExclude;
	$.get(url, function(data) {
		data = trim(data);
		if(data != '') {
			$('#ichat_status').html(data);
			$('#ichat_status').show();
		} else {
			$('#ichat_status').hide();
		}
		mobileNotifyUpdaterInterval = setInterval(mobileNotifyUpdater, 10000);
	});
}

function alertCustom(msg, shadow, title, handleAlert)
{
    var handleAlert = handleAlert||false;

    if(typeof(alertHtmlCustom) === 'boolean' && alertHtmlCustom === true) {
        if (handleAlert === true) {
           alertHandHtml(msg, shadow, title)
        } else {
           alertHtml(msg, shadow, title);
        }
		$(window).resize();
    } else {
        alert(msg);
    }
}

function confirmCustom(msg, handler, title)
{
    if(typeof(alertHtmlCustom) === 'boolean' && alertHtmlCustom === true) {
        confirmHtml(msg, handler, title);
    } else {
        confirm(msg);
    }
}

function confirmHandler(msg, hOk, hCancel, title)
{
    if(typeof(alertHtmlCustom) === 'boolean' && alertHtmlCustom === true) {
        confirmHtmlHandler(msg, hOk, hCancel, title);
    }
}

function siteSetLanguage(language, part)
{
    var urlParams = location.search;
    var urlParamsStart = '&';
    var part = part || 'set_language';
    urlParams = removeVariableFromURL(urlParams, part);

    if(urlParams == '') {
        urlParamsStart = '?';
    }
    if(urlParams == '?') {
        urlParamsStart = '';
    }

    var urlParamLanguage = urlParamsStart + part + '=' + language;
    var url = location.pathname + urlParams + urlParamLanguage + location.hash;
    location.href = url;
}

function removeVariableFromURL(url_string, variable_name)
{
    var URL = String(url_string);
    var regex = new RegExp( "\\?" + variable_name + "=[^&]*&?", "gi");
    URL = URL.replace(regex,'?');
    regex = new RegExp( "\\&" + variable_name + "=[^&]*&?", "gi");
    URL = URL.replace(regex,'&');
    URL = URL.replace(/(\?|&)$/,'');
    regex = null;
    return URL;
}

function addVariableToURL(url, variable)
{
	urlParamsStart = '&';
    if(url == '') {
        urlParamsStart = '?';
    }
    if(url == '?') {
        urlParamsStart = '';
    }

	url = url + urlParamsStart + variable;

    return url;
}

function addUniqueVariableToURL(url, variable, value)
{
	var url = removeVariableFromURL(url, variable);

    if(url.indexOf('?') === -1) {
        url = url + '?';
    }

	url = addVariableToURL(url, variable + '=' + value);
    return url;
}

function equalHeight(group) {
    tallest = 0;
    group.each(function() {
        thisHeight = $(this).height();
        if(thisHeight > tallest) {
        tallest = thisHeight;
        }
    });
    group.height(tallest);
}

function changeTmplInCycle(tmplCurrent, direction)
{
	tmplsCount = tmplsList.length;
	indexCurrent = tmplsList.indexOf(tmplCurrent);
	if(direction == 'next') {
		indexCurrent = indexCurrent + 1;
		if(indexCurrent >= tmplsCount) {
			indexCurrent = 0;
		}
	} else {
		indexCurrent = indexCurrent - 1;
		if(indexCurrent < 0) {
			indexCurrent = tmplsCount - 1;
		}
	}

	url = removeVariableFromURL(location.search, 'set_template' + sitePartParam);
	location.href = location.pathname + addVariableToURL(url, 'set_template' +  sitePartParam + '=' + tmplsList[indexCurrent]) + location.hash;
}

function switchLanguageParamInCurrentUrl()
{
	url = removeVariableFromURL(location.search, 'set_language' + sitePartParam);
	lang = 'default';
	if(siteLanguage == lang) {
		lang = languageOfUser;
	}
	location.href = location.pathname + addVariableToURL(url, 'set_language' +  sitePartParam + '=' + lang) + location.hash;
}

var mButtonPressed = false;

function initDevFunctions() {

	document.onkeydown = function(e) {
		if ($(':focus').is('textarea, :text, :password, [type="email"], [contenteditable]')) return;
		e = e || window.event;

		var keyCode = (window.event) ? e.which : e.keyCode;
		if(e.keyCode) {
			keyCode = e.keyCode;
		}

		keyCode = parseInt(keyCode);

		//console.log(keyCode);

		if(keyCode == 77) {
			mButtonPressed = true;
		}

		var controlButton = (e.ctrlKey || mButtonPressed);

		if (controlButton && (keyCode == 37 || keyCode == 39)) {
			switchLanguageParamInCurrentUrl();
		}

		if (controlButton && keyCode == 38) {
			changeTmplInCycle(tmplCurrent, 'prev');
		}
		if (controlButton && keyCode == 40) {
			changeTmplInCycle(tmplCurrent, 'next');
		}
	};

	document.onkeyup = function(e) {
		e = e || window.event;

		var keyCode = (window.event) ? e.which : e.keyCode;
		if(e.keyCode) {
			keyCode = e.keyCode;
		}

		keyCode = parseInt(keyCode);

		if(keyCode == 77) {
			mButtonPressed = false;
		}
	};

}

if (!Array.indexOf) {
	Array.prototype.indexOf = function (obj, start) {
		for (var i = (start || 0); i < this.length; i++) {
			if (this[i] === obj) {
				return i;
			}
		}
		return -1;
	}
}

function setAvatar(avatar)
{
    var urlParams = location.href.split('?'),
        url = urlParams[0] + '?cmd=setavatar&avatar=' + avatar + '&ajax=1',
        checked = $('#setAvatar');
    $.get(url, function() {
        checked.remove();
        $('#btnSelect' + avatar).before(checked);
    });
}

function groupEmail(group, incorrect_email, some_email_addresses)
{
    var flag = false;
    var flag_no = false;
    group.each(function (i) {
                            var email = $.trim($(this).val());
                            if (checkEmail(email)) {
                                flag = true;
                            } else {
                                if (email != '') {
                                    flag_no = true;
                                    flag = false;
                                    alert(incorrect_email);
                                    $(this).focus();
                                    return false;
                                }
                            }
    });
    if (!flag && !flag_no) {
        alert(some_email_addresses);
        return false;
    }
    return flag;
}

function closeRecorder()
{
    document.getElementById('rec').style.display = 'none';
}

function showRecorder()
{
    document.getElementById('rec').style.display = 'block';
}

function preloadImageInsertInDom(images) {
    if (typeof document.body == "undefined") return;
    try {
        var div = document.createElement("div");
        var s = div.style;
            s.position = "absolute";
        s.top = s.left = 0;
        s.visibility = "hidden";
        document.body.appendChild(div);
        div.innerHTML = "<img src=\"" + images.join("\" /><img src=\"") + "\" />";
        var lastImg = div.lastChild;
        lastImg.onload = function(){document.body.removeChild(div)};
    } catch(e) {
       //console.log('Error. preloadImageInsertInDom');
    }
}

function preloadImages()
{
    var d = document;
    if(!d.preload) {
		d.preload=new Array();
	}
    var a = preloadImages.arguments;
	for(i = 0; i < a.length; i++)
	{
		d.preload[i] = new Image();
		d.preload[i].src = a[i];
    }
}


function preloadImagesWidgets(url)
{
    url = url + '_server/widgets/images/';
    preloadImages(
        url+'w_ico_minimize.png',
        url+'w_ico_close.png',
        url+'wh_yellow.png',
        url+'wh_violet.png',
        url+'wh_blue.png',
        url+'wh_brown.png',
        url+'wh_green.png',
        url+'w_calendar.png',
        url+'w_calendar_2.png',
        url+'w_violet.png',
        url+'w_brown.png',
        url+'w_green.png',
        url+'w_blue.png',
        url+'c_today.png',
        url+'c_event.png',
        url+'c_todayandevent.png',
        url+'bl_foto_bg.png',
        url+'icon_chat.png',
        url+'wh_red.png',
        url+'w_greenl.png',
        url+'wh_greenl.png',
        url+'w_blue_line.png',
        url+'wh_grey.png',
        url+'wh_brown2.png',
        url+'w_yellow_line.png',
        url+'switch_t.gif',
        url+'switch_b.gif',
        url+'note_bg.png'
    );
}

function xajax_im_open(uid, e) {
	if ($('#xajax_im_open_'+uid)[0]) return reset_opens(uid);
    /*var d = document.documentElement,
        height = self.innerHeight || (d && d.clientHeight) || document.body.clientHeight,
        width = self.innerWidth || (d && d.clientWidth) || document.body.clientWidth;*/
    var e = e || window.event,
        //h = $(window).height() + siteTopOffset,
        w = $(window).width(),
        top_im = 0,
        left_im = 0;

    if (typeof e != 'undefined') {
        top_im = e.clientY,
        left_im = e.clientX;
    }
    if ((top_im - h_chat + h_chat_offset) < 0) {
        top_im = 0;
    } else {
        top_im = top_im - h_chat + h_chat_offset;
    }
    if (w < (left_im + w_chat)) {
        left_im = left_im - w_chat;
    }

    xajax_im_open_new(uid, imMsgLayout, dirTmplMain, left_im, top_im, 'false');
}

function strip_tags(input, allowed) {

    allowed = (((allowed || '') + '')
        .toLowerCase()
        .match(/<[a-z][a-z0-9]*>/g) || [])
        .join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
        commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
    return input.replace(commentsAndPhpTags, '')
            .replace(tags, function($0, $1) {
                return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
            });
}

function isKeyPressed(e, key_press) {
    if(typeof e == 'undefined') {
        e = window.event;
    }
    if (e.keyCode) key = e.keyCode;
    else if(e.which) key = e.which;
    if (key == key_press) {
        return true;
    }
    return false;
}

function videoResize(box) {
    box.find('object, video, iframe').each(function() {
        var el = $(this),
            newWidth = el.closest(box).width(),
            newHeight = newWidth * el.attr('data-aspectRatio');
        el.width(newWidth).height(newHeight).find('embed').width(newWidth).height(newHeight);
        if(el.closest('.player_custom')[0]){
            el.closest('.video-js').width(newWidth).height(newHeight );
        }
    })
    //var newWidth = box.width();
    /*box.find('object').each(function() {
        var el = $(this),
            newWidth = el.parents(box).width(),
            newHeight = newWidth * el.attr('data-aspectRatio');
        el.width(newWidth).height(newHeight).find('embed').width(newWidth).height(newHeight);
    });*/
}

function prepareVideoResize(box) {
    box.find('embed, object, video, iframe').each(function() {
        $(this).attr('data-aspectRatio', this.height / this.width)
               .removeAttr('height')
               .removeAttr('width');
    });
}

function videoResizeStep() {
    globalID = requestAnimationFrame(videoResizeStep);
    videoResize($('.groups_video'));
}

function imageResize(box, images, url_files, sfx, index, split)
{
    split = split || '_';
    index = index || 2;
    if (box.css('display') != 'none') {
        images.each(function (i) {
            var id = $(this).attr('id').split(split),
                img = url_files + '/' + id[index];
                $(this).attr('src', img + '_' + sfx);
        });
    }
}

function enterCaret(el, text) {
    text=defaultFunctionParamValue(text, '\n');
	if(document.selection){
		sel = document.selection.createRange();
		sel.text = text;
	} else if (el.selectionStart || el.selectionStart == '0') {
		var start = el.selectionStart, end = el.selectionEnd;
		el.value=el.value.substring(0, start)+text+el.value.substring(end, el.value.length);
		el.setSelectionRange(++start, start)
	} else el.value += text;
	$(el).trigger('autosize');
	return false;
}


function insertCaretDivContentEditable(insertEl, el) {
	if (el == undefined) {
		el = document.createElement('br');
	} else if (el == 'space') {
		el = document.createTextNode("\u00A0");

	}
	try {
		var selection = window.getSelection(),
			range = selection.getRangeAt(0);

		range.deleteContents();
		range.collapse(false);
		range.insertNode(el);
		range.selectNode(el);
		range.collapse(false);
		selection.removeAllRanges();
		selection.addRange(range);
	} catch (z) {
		var range = document.selection.createRange();
		range.pasteHTML(el.outerHTML);
		range.select();
	}
	$(insertEl).trigger('autosize');

	return false;
}

function doOnEnter(fn, isCaret) {
    isCaret=isCaret||0;
	return function(e){
		if (e.which == 13){
			var el=this,
				submitOnEnter=!/submitOnEnter=0/.test(document.cookie);//||isMobileBrowser);
			if (submitOnEnter && !e.shiftKey && !e.ctrlKey && !e.metaKey ) {
				return fn(el);
			}
			if (e.ctrlKey&&!isCaret) return submitOnEnter?enterCaret(el):fn(el)
		}
	}
}

function lazyLoadImage(el, is_no_init, speed, placeholder, delay, event) {
    var el=el||'img.lazy', is_no_init=is_no_init||false,
        event=event||'load',
        placeholder=placeholder||'', speed=speed||400,
        delay=delay||1;
        $(el).show().delay(delay).lazyload({
            effect : 'fadeIn',
            placeholder: placeholder,
            skip_invisible : false,
            event : event,
            effect_speed: speed
        });
        if (is_no_init == true) $(el).removeClass('lazy');
}

function choiceChkbox(ch, chkbox) {
    if(ch.is(':checked')) {
        chkbox.prop("checked", true);
    }else{
        chkbox.prop("checked", false);
    }
}

function getChoiceSelectChkbox(chkbox) {
        var StrID = '',
            chkbox = chkbox || 'chk';
        $('[id ^= '+chkbox+'_]:checked').each(function(){
            StrID += ($(this).attr('id').replace(/chk_/g, '')+',')
        })
        return StrID.slice(0, -1);
}

function actionChecked(page, act, param) {
        var StrID = '',
            act = act || 'delete',
            param = param || '';
        $('[id ^= chk_]:checked').each(function(){
            StrID += ($(this).attr("id").replace(/chk_/g, '')+',')
        })
        var items = StrID.slice(0, -1);
        window.location.href=page+'?cmd='+act+param+'&item='+items;
}

function widgetStatusSet(wid, status)
{
	widgetStatus[wid] = status;
	//console.log('widget_status', wid, status);
}

function widgetIsLoaded(wid)
{
	var loaded = 'none';
	if(wid in widgetStatus) {
		loaded = widgetStatus[wid];
	}
	return loaded;
}

function setWidthOverWrap(id, wrap, offset, param) {
    var wrap = wrap || '#hdr',
        header = $(wrap),
        offset = offset || 75,
        param = param || 'width';//'max-width'
    if (header.length) {
        var newWidth = header.width()-offset;
        $(id).css(param, newWidth+'px');
    }
}

function isAuthOnly(value) {
    if (value == 'please_login') {
        var urlLogin='';
        if(typeof(urlPageLogin) === 'string' && urlPageLogin !== '') {
            urlLogin=urlPageLogin;
        }else if(typeof(url_main) === 'string' && url_main !== '') {
            urlLogin=url_main;
        }else if(typeof(urlMain) === 'string' && urlMain !== '') {
            urlLogin=urlMain;
        }
        if (urlLogin) {
            window.location.href = urlLogin;
        }
        return false;
    }
    return true;
}

function checkDataAjax(res) {
    if(res=='')return false;
    try{
        var obj = jQuery.parseJSON(res);
        if (obj.status && isAuthOnly(obj.data)){return obj.data
        }else{return false}
    }catch(e){return false}
}

function getDataAjax(res, data) {
    var data=data||'page';
    if (res == '') {
        return false;
    }
    try{
        var obj = jQuery.parseJSON(res);
        return (obj.status) ? obj[data] : false;
    }catch(e){return false}
}

function postAjax(url, param, fnc) {
    $.post(url, param, fnc);
}

function setCenteringPopup(popup, top) {
    var windowWidth=document.documentElement.clientWidth,
        windowHeight=document.documentElement.clientHeight,
        popupHeight=popup.height(),
        popupWidth=popup.width(),
        top=alertHtmlTop||(windowHeight/2-popupHeight/2);

    popup.css({position: "fixed",
               top: top,
               left: windowWidth/2-popupWidth/2
    });
}
function removeSubmissionBlock()
{
	blockSubmission = false;
}


function insertFromDataHtmlToHtml(data, dataBlocks)
{
	var dataBlock = '';

	for(var dataBlocksKey in dataBlocks) {
		dataBlock = $(data).filter(dataBlocksKey);
		if(dataBlock[0]) {
			$(dataBlocks[dataBlocksKey]).html(dataBlock.html());
		}
	}
}

function showTipFromData(data, dataBlocks, btn)
{
	var dataBlock = '';

	for(var dataBlocksKey in dataBlocks) {
		dataBlock = $(data).filter(dataBlocksKey);
		if(dataBlock.length) {
            customShowTip(dataBlocks[dataBlocksKey],btn,dataBlock.text())
		}
	}
}

function partnerCheckboxCheckUncheck(checkboxArea)
{
	var checkboxArea = checkboxArea || '.checkbox_fields_area input[type="checkbox"]';
	$(document).ready(function() {
		$(checkboxArea).click(function(){
			var currentCheckboxValue = $(this).val();
			var currentCheckboxName = $(this).attr('name');
			$('input[name="' + currentCheckboxName + '"]').each(function(){
				if(currentCheckboxValue != 0) {
					if($(this).attr('value') == 0) {
						$(this).attr('checked', false);
					}
				} else {
					if($(this).attr('value') != 0) {
						$(this).attr('checked', false);
					}
				}
			});
		});
	});
}

var videoPlayers={};
if (window.jQuery) {
	partnerCheckboxCheckUncheck();

    $.preloadImages = function () {
        if (typeof arguments[arguments.length - 1] == 'function') {
            var callback = arguments[arguments.length - 1];
        } else {
            var callback = false;
        }
        if (typeof arguments[0] == 'object') {
            var images = arguments[0];
            var n = images.length;
        } else {
            var images = arguments;
            var n = images.length - 1;
        }
        var not_loaded = n;
        for (var i = 0; i < n; i++) {
            $(new Image()).load(function() {
                if (--not_loaded < 1 && typeof callback == 'function') {
                    callback();
                }
            }).attr('src', images[i]);
        }
    }

    $.fn.toggleDisabled = function(limit,rang) {
		return this.each(function(){
            if(rang){
                if(this.value>limit){this.disabled=true}
                else{this.disabled=false}
            }else{
                if(this.value<limit){this.disabled=true}
                else{this.disabled=false}
            }
		});
	};

    function initCustomVideoPlayer(id, vol) {
        videoPlayers[id]=videojs('#user_video_'+id).ready(function(){
            var pl=$('#user_video_'+id),
                blWall=pl.closest('.blogs_video_player_custom, .player_custom');
                if(blWall[0]){
                    blWall.addClass('to_show');
                }
                vol=getVolumeVideoPlayer();
                this.volume(vol);
                this.on('volumechange',function(){
                    if(this.muted()){
                        this.volume(0);
                    }
                    //setCookie('videojs_volume', this.volume());
                    $.cookie('videojs_volume', this.volume(), {path:'/'});
                }).on('fullscreenchange', function(){
                    var blWillChange=pl.closest('.wall_item');
                    if(blWillChange[0]){
                        var isWillChange=blWillChange.css('will-change')=='unset';
                        blWillChange.css('will-change', isWillChange?'transform':'unset');
                    }
                }).on('ended', function() {
                    this.load();
                    this.pause();
                });
        })
    }

    function initCustomVideoPlayerAdmin(id, vol) {
        videojs('#user_video_'+id).ready(function(){
            var pl=$('#user_video_'+id),
                blWall=pl.closest('.player_custom');
                if(blWall[0]){
                    blWall.addClass('to_show');
                }
                vol=getVolumeVideoPlayer();
                this.volume(vol);
                this.on('ended', function() {
                    this.load();
                    this.pause();
                }).on('volumechange',function(){
                    if(this.muted()){
                        this.volume(0);
                    }
                    //setCookie('videojs_volume', this.volume());
                    $.cookie('videojs_volume', this.volume(), {path:'/'});
                });
        })
    }

    function initNativeVideoPlayer(id) {
        var pl=document.getElementById('user_video_'+id),
            $pl=$('#user_video_'+id);
        videoPlayers[id]=pl;

        if (typeof mobileAppLoaded == 'undefined') mobileAppLoaded = false;
        if (typeof tmplCurrent == 'undefined')tmplCurrent = '';

        pl.volume=getVolumeVideoPlayer();
        pl.onvolumechange=function(){
            if(this.muted){
                this.volume=0;
            }else{
                var volume=$.cookie('videojs_volume')*1;
                if (!volume) {
                    this.volume=getLastVolumeVideoPlayer();
                }
                this.volume && $.cookie('videojs_volume_last', this.volume, {path:'/'});
            }
            //setCookie('videojs_volume', this.volume);
            $.cookie('videojs_volume', this.volume, {path:'/'});
        };

        var $blPlayer=$pl.closest('#pp_gallery_video_one_bl');
        if (mobileAppLoaded && tmplCurrent == 'edge' && $blPlayer[0]) {
            var videoPlayingTimeout=0,
                isAutoPlay=$pl.data('autoplay'),
                $poster=$pl.nextAll('.video_native_poster'),
                $btnPlay=$poster.find('.play_button');

            var videoPlayingShow = function() {
                clearTimeout(videoPlayingTimeout);

                if($pl[0].currentTime >= 0.1) {
                    //var text=$('#pp_gallery_date').text();
                    //$('#pp_gallery_date').text(text+Math.round((performance.now() - time)) + 'ms /' + $pl[0].currentTime);
                    $pl[0].currentTime = 0;
                    $pl[0].muted = false;
                    $blPlayer.addClass('ready');
                    $pl.toggleClass('to_hide to_show');
                    $poster.addClass('to_hide');
                } else {
                    videoPlayingTimeout = setTimeout(videoPlayingShow, 100);
                }
            }
            //var time = performance.now();
            $pl.one('canplay', function(){
                if (isAutoPlay) {
                    $btnPlay.addClass('to_hide');
                    $pl.trigger('play');
                    //$('#pp_gallery_date').text(Math.round((performance.now() - time)) +'ms /');
                    videoPlayingShow();
                } else {
                    $blPlayer.addClass('ready');
                    $btnPlay.click(function(){
                        $btnPlay.addClass('to_hide');
                        $blPlayer.removeClass('ready');
                        $pl.trigger('play');
                        videoPlayingShow();
                    })
                }
            })

            $pl.load();
        } else {
            pl.onended=function(){
                $pl.removeAttr('autoplay');
                this.load();
                //this.pause();
            }

            pl.onloadedmetadata=function(){
                var $gallery=$('.bl_photo_one', '#pp_gallery_photo_one_cont');
                if(!$gallery[0])$gallery=$('#pp_gallery_video_one_bl');//EDGE
                $gallery[0]&&$gallery.addClass('ready');
            }
        }

        if (detectApiFullScreen()) {
            var blChange=$pl.closest('.wall_item, .bl_video_one_cont'),isChange;
            blChange=$pl.closest('.wall_item, .pp_wrapper');
            if(blChange[0]){
                changeFullScreen(pl,function(){
                    isChange=blChange.css('will-change')=='unset';
                    blChange.css('will-change', isChange?'transform':'unset');
                })
            }
        }
    }
}

function getLastVolumeVideoPlayer()
{
    var volume=$.cookie('videojs_volume_last');
    if(volume>1){
        volume=((volume/100).toFixed(1));//Fix
    }else if(!volume) {
        volume=0.7;
    }
    return volume;
}

function getVolumeVideoPlayer()
{
    var volume=$.cookie('videojs_volume');
    if(volume>1){
        volume=((volume/100).toFixed(1));//Fix
    }else if(!volume) {
        volume=0.7;
    }
    return volume;
}

function defaultFunctionParamValue(param, value)
{
	if (typeof param === 'undefined') {
		param = value;
	}
	return param;
}

function setCaretToPos(input, pos, el) {
  var input = el||document.getElementById(input),
      selectionEnd = pos,
      selectionStart = pos;
  if (input.setSelectionRange) {
    input.focus();
    input.setSelectionRange(selectionStart, selectionEnd);
  }
  else if (input.createTextRange) {
    var range = input.createTextRange();
    range.collapse(true);
    range.moveEnd('character', selectionEnd);
    range.moveStart('character', selectionStart);
    range.select();
  }
}

function getRandomInt(min, max){
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function in_array(needle, haystack, argStrict) {
    var key = '',
        strict = !! argStrict;

    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true;
            }
        }
    } else {
        for (key in haystack) {
            if (haystack[key] == needle) {
                return true;
            }
        }
    }
    return false;
}

function in_array_key(needle, haystack, argStrict) {
    var key = '',
        strict = !! argStrict;
    if (strict) {
        for (key in haystack) {
            if (key === needle) {
                return true;
            }
        }
    } else {
        for (key in haystack) {
            if (key == needle) {
                return true;
            }
        }
    }
    return false;
}

function arraysEqual(a, b) {
  if (a === b) return true;
  if (a == null || b == null) return false;
  if (a.length != b.length) return false;
  for (var i = 0; i < a.length; ++i) {
    if (a[i] != b[i]) return false;
  }
  return true;
}

function detectApiFullScreen() {
    return  document.fullscreenEnabled
           || document.webkitFullscreenEnabled
           || document.msFullscreenEnabled
           || document.mozFullScreenEnabled;
}

function isFullScreen() {
    var result=true;
    if (!document.fullscreenElement &&
        !document.webkitFullscreenElement &&
        !document.msFullscreenElement &&
        !document.mozFullScreenElement) {
        result=false;
    }
    return result;
}

function toggleFullScreen(el) {
    if (!document.fullscreenElement &&
        !document.webkitFullscreenElement &&
        !document.msFullscreenElement &&
        !document.mozFullScreenElement) {
        if (el.requestFullscreen) {
            el.requestFullscreen();
        } else if (el.webkitRequestFullscreen) {
            el.webkitRequestFullscreen();
        } else if (el.msRequestFullscreen) {
            el.msRequestFullscreen();
        } else if (el.mozRequestFullScreen) {
            el.mozRequestFullScreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        }
    }
}

function changeFullScreen(el, onfullscreenchange) {
    el.addEventListener("webkitfullscreenchange", onfullscreenchange);
    el.addEventListener("mozfullscreenchange",    onfullscreenchange);
    el.addEventListener("MSFullscreenChange",     onfullscreenchange);
    el.addEventListener("fullscreenchange",       onfullscreenchange);
}

function playSound(sound){
    if (typeof audioNotificationBuffer != 'undefined') {
        console.log('Notification sound - play');
        playNotificationSound();
        return;
    }
    if (typeof soundManager != 'undefined' &&  typeof urlMain != 'undefined') {
        var sound=sound||'pop_sound_chat.mp3';
        /*soundManager.setup({url: urlMain+'_server/js/sound/',
                    onready: function() {
                              var mySound = soundManager.createSound({
                                  id: 'aSound',
                                  url: urlMain+'_server/im_new/sounds/'+sound
                              });
                              mySound.play();
                    }
        })*/

        var soundPlay = soundManager.createSound({url: urlMain+'_server/im_new/sounds/'+sound});
        soundPlay.play();
    }
}

function nl2br(str, is_xhtml) {
  var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>';
  return (str + '')
	.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function br2nl(str,replaceMode) {
  var replaceStr = (replaceMode) ? "" : "\n";
  // Includes <br>, <BR>, <br />, </br>
  return str.replace(/<\s*\/?br\s*[\/]?>/gi, '\n');
}

function strToHtml(str, noBr) {
    str = str.replace(/</g, "&lt;");
    noBr=noBr||0;
    if(!noBr) str = nl2br(str);
    return str;
}

function webglDetect(){
    try {
		return !!window.WebGLRenderingContext && !! document.createElement('canvas').getContext('experimental-webgl');
	} catch (e) {
		return false;
	}
}


function mobileAppNotification(id, text, type, title, url_notif)
{
    // disabled in new apps
    if(typeof MobileApp !== 'undefined') {
        return;
    }

    if(!mobileAppLoaded || !id) {
        return;
    }

    title = title || appTitle;
    url_notif = url_notif || '';

    if(app.activeStatus == undefined || !app.activeStatus)
    {
        navigator.vibrate(appVibrationDuration);
        type = type || 'im';

        var notificationParams = {
            id: id,
            title: title,
            text: text,
            data: type,
            url_notif : url_notif
        }

        if(device.platform != 'Android') {
            notificationParams['sound'] = 'res://sound.caf';
            notificationParams['title'] = '';
        } else {
			notificationParams['smallIcon'] = 'res://icon_rem';
		}

        cordova.plugins.notification.local.on("click", function(notification) {
            // don't show and redirect if chat already active
            var urls = {
                    im : 'messages.php?display=one_chat&user_id=' + notification.id,
                    city : urlCity + 'index.php?view=mobile&from=' + notification.id,
                },
                url = '';
            if (tmplCurrent == 'edge') {
                urls.city = urlPageCity + '?from=' + notification.id;
            }
            if (notification.url_notif) {
                url = notification.url_notif;
            } else if (notification.data == 'im' && appCurrentImUserId != notification.id){
                url = urls.im;
            /*} else if (notification.data == 'city'){
                var is = 0;
                for (var k in appCityListUser){
                    if (appCityListUser[k]['id'] == notification.id){
                        is = 1;
                        break;
                    }
                }
                if (!is) {
                    url = urls.city;
                }*/
            } else if (urls[notification.data]) {
                url = urls[notification.data];
            }


            if (url) {
                var curPage=document.location.href.split('#')[0],
                    setPage=url.split('#')[0];
                if(curPage!=setPage)appPreloaderShow();
                setTimeout(function(){document.location.href = url;},100);
            }
        });

        cordova.plugins.notification.local.schedule(notificationParams);
    }
}

function mobileAppCityNotification(data)
{
    if (!mobileAppLoaded || !isWebglDetect || !data.id || !data.uid) return;
    if (appCityLastMsgId < data.id) {
        mobileAppNotification(data.uid, data.message, 'city');
        appCityLastMsgId = data.id;
    }
}

function setCookie(name, value, options) {
    options=options||{};
    var expires = options.expires;
    if (typeof expires == "number" && expires) {
        var d = new Date();
        d.setTime(d.getTime() + expires * 1000);
        expires=options.expires=d;
    }
    if (expires && expires.toUTCString) {
        options.expires=expires.toUTCString();
    }
    value=encodeURIComponent(value);
    var updatedCookie = name + "=" + value;
    for (var propName in options) {
        updatedCookie += "; " + propName;
        var propValue = options[propName];
        if (propValue !== true) {
        updatedCookie += "=" + propValue;
        }
    }
    document.cookie = updatedCookie;
}

function supportWebrtc(){
    /*var support=true;
    navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
    if (navigator.getUserMedia) {
        //Хром только так можно проверить наверняка на предмет доступа но тогда камера включается
        /*navigator.getUserMedia({audio:true,video:true},function(stream){},
        function(err) {
            support=false;
            //console.log("The following error occurred: " + err.name);
        })*/
        /*if(/Chrome/i.test(navigator.userAgent)
            &&window.location.protocol!='https:'
            &&window.location.host!='localhost'){
            support='ssl';
        }
    } else {
        //console.log("getUserMedia not supported");
        support=false;
    }*/

    var support=false,
        infoCurrentBrowser = detectBrowserWebRtc();
    //alert(JSON.stringify(infoCurrentBrowser));
    if (infoCurrentBrowser['browser']) {
        support=true;
        var ssl=window.location.protocol == 'https:' || window.location.host == 'localhost';
		if(!ssl){
			support='ssl';
		}
    }
    return support;
}

function checkWebrtc(){
    var is=supportWebrtc();
    if(is=='ssl')is=false;
    return is;
}

/* Redirect URL */
function redirectToLoginPage() {
    var url;
    if (typeof urlPageLogin=='undefinded') {
        url=urlPagesSite.login;
    } else {
        url=urlPageLogin;
    }
    redirectUrl(url);
}

function redirectRequiresAuth(data) {
    if(data == 'please_login') {
        redirectToLoginPage();
        return true;
    }
    return false;
}

function redirectUrl(href){
    var lastBrowserUrl = window.location.href;
    window.location.href = href;

    // fix for ios + Doesn't work in mobile chrome either
    if(iOSversion() || /Android/i.test(navigator.userAgent)) {
        setTimeout(
            function(){
                if(window.location.href == lastBrowserUrl) {
                    window.location.href = href;
                }
            }
        );
    }
}

function redirectToLogin(){
    redirectUrl(url_main+urlPageLogin);
}

function redirectToUpgrade(param){
    var url;
    param=param||'';
    if(param){
        param='?'+param;
    }
    if (typeof urlPageUpgrade=='undefinded') {
        url=urlPagesSite.upgrade;
    } else {
        url=urlPageUpgrade;
    }
    redirectUrl(url_main+url+param);
}

function checkLoginStatus(){
    if (!ajax_login_status) {
        redirectToLogin();
        return false;
    }
    return true;
}

function goLink(url,params){
    params=params||'';
    var f=document.createElement('form');
    f.method='POST';
    f.action=url;
    if(params){
        params=params.split('&');
        for (var key in params) {
			if (typeof params[key] == 'string') {
				var param=params[key].split('=');
				var i=document.createElement('input');
				i.setAttribute('type','hidden');
				i.setAttribute('name',param[0]);
				i.value=param[1];
				f.appendChild(i);
			}
        }
    }
    document.body.appendChild(f);
    f.submit();
}

function replaceUrl(url){
    if(window.history && history.pushState){
        url=url||'';
        //url=url||window.location.href;
        if(url){
            history.replaceState(history.state, document.title, url);
        }else{
            history.replaceState(history.state, document.title);
        }
    }
}
/* Redirect URL */

function globalAjaxError(xhr, textStatus, errorThrown){
    //https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
    //textStatus = 'timeout', 'error', 'abort', 'parsererror'
    console.log('GLOBAL AJAX ERROR xhr:', xhr.status, xhr.responseText, xhr);
    console.log('GLOBAL AJAX ERROR TextStatus: ' + textStatus + ' ErrorThrown: ' + errorThrown);
    var error = '';
    if (xhr.status === 0) {
        error = 'Not connect. Verify Network.';
    } else if (xhr.status === 404) {
        error = '404 Not Found';
    } else if (xhr.status === 500) {
        error = '500 Internal Server Error';
    } else if (textStatus === 'timeout') {
        error = 'Time out error.';
    } else if (textStatus === 'abort') {
        error = 'Ajax request aborted.';
    } else if (textStatus === 'parsererror') {
        error = 'Requested JSON parse failed.';
    } else {
        error = xhr.responseText;
    }
    if (!error) {
        error = '-';
    }
    if (!errorThrown) {
        errorThrown = '-';
    }
    error = 'Ajax error: ' + error;
    error += '<br>Status: ' + xhr.status + '<br>TextStatus: ' + textStatus + '<br>ErrorThrown: ' + errorThrown;
    //alertCustom(error);
}

var globalTimeoutAjax = 30000,
    globalTimeoutRetryAjax = 5000;
function globalRetryAjaxTimeout(xhr, textStatus, errorThrown, fn){
    globalAjaxError(xhr, textStatus, errorThrown);
    if (xhr.status === 0 || textStatus === 'timeout') {
        if (typeof fn == 'function') {
            setTimeout(fn,globalTimeoutRetryAjax)
        }
    }
}

function $ajax(url, data, fnSuccess, fnError){
    $.ajax({url: url,
            type: 'POST',
            data: data,
            timeout: globalTimeoutAjax,
            //cache: false,
            success: function(res){
                if(typeof fnSuccess=='function')fnSuccess(res)
            },
            error: function(xhr, textStatus, errorThrown){
                globalRetryAjaxTimeout(xhr, textStatus, errorThrown, function(){
                    if(typeof fnError=='function')fnError()
                })
            },
    })
}

function setAjaxPrefilter() {
    var isParamTmpl = (typeof tmplCurrent === 'string') && (typeof sitePartParam === 'string') && (typeof sitePart === 'string');
    var isSiteGuid = typeof siteGuid === 'number';
    if ((isParamTmpl || isSiteGuid) && (typeof $.ajaxPrefilter !== 'undefined')) {
        $.ajaxPrefilter(function(options) {
            if (isParamTmpl) {
                options.url = addUniqueVariableToURL(options.url, 'set_template' + sitePartParam + '_runtime', (typeof tmplCurrentFolderName === 'string' ? tmplCurrentFolderName : tmplCurrent));
                options.url = addUniqueVariableToURL(options.url, 'site_part_runtime', sitePart);
            }
            if (isSiteGuid) {
                options.url = addUniqueVariableToURL(options.url, 'site_guid', siteGuid*1);
            }
            if (siteGroupId) {//Edge groups
                options.url = addUniqueVariableToURL(options.url, 'group_id', siteGroupId*1);
                options.url = addUniqueVariableToURL(options.url, 'view', siteGroupView);
            }
            if (siteGroupViewList) {//Edge groups
                options.url = addUniqueVariableToURL(options.url, 'view_list', siteGroupViewList);
            }
        })
    }
    $.ajaxSetup({
        cache: true
    })
}

function l(key) {
    if(typeof siteLangParts!=='object')return '';
    var page='all';
    if(typeof currentPage!=='undefined')page=currentPage;
    if(siteLangParts[page]&&siteLangParts[page][key]) {
        return siteLangParts[page][key];
    }
    if(page!=='all'&&siteLangParts['all']&&siteLangParts['all'][key]){
        return siteLangParts['all'][key];
    }
    return '';
}

function colorRgbToHex(colorRgb)
{
    if (colorRgb === 'transparent') {
        colorRgb = 'rgb(0, 0, 0)';
    }
    var colorPartsRgb = colorRgb.match(/(\d+),\s*(\d+),\s*(\d+)/);

    var colorPartsHex = [];
    for (var i = 1; i <= 3; i++) {
        colorPartsHex[i] = parseInt(colorPartsRgb[i]).toString(16);
        if (colorPartsHex[i].length == 1) {
            colorPartsHex[i] = '0' + colorPartsHex[i];
        }
    }
    var colorHex = '#' + colorPartsHex.join('');

    return colorHex;
}

function centerItemInArea(itemWidth, itemHeight, areaWidth, areaHeight)
{
        var horizontalGap, verticalGap;

        var itemWidthNew = itemWidth;
        var itemHeightNew = itemHeight;

        var itemProportion = itemWidth / itemHeight;

        // make smaller if more then container
        if(itemHeight > areaHeight) {
            itemHeightNew = areaWidth;
            itemWidthNew = itemHeightNew * itemProportion;
        }
        if(itemWidth > areaWidth) {
            itemWidthNew = areaWidth;
            itemHeightNew = itemWidthNew / itemProportion;
        }

        // make bigger if less then container
        if(areaHeight * itemProportion < areaWidth) {
            itemHeightNew = areaHeight;
            itemWidthNew = itemHeightNew * itemProportion;
        } else if(areaWidth / itemProportion < areaHeight) {
            itemWidthNew = areaWidth;
            itemHeightNew = itemWidthNew / itemProportion;
        } else {
            // max to borders
            var areaProportion = areaWidth / areaHeight;

            if(areaProportion < 1) {
                itemHeightNew = areaHeight;
                itemWidthNew = itemHeightNew;
            } else {
                itemWidthNew = areaWidth;
                itemHeightNew = itemWidthNew / itemProportion;
            }
        }

        verticalGap = (areaHeight - itemHeightNew) / 2;
        horizontalGap = (areaWidth - itemWidthNew) / 2;

        var result = {
            'width' : itemWidthNew,
            'height' : itemHeightNew,
            'horizontalGap' : horizontalGap,
            'verticalGap' : verticalGap
        };

        //console.log(result);

        return result;
}

function centerItemInAreaByHeightWithCrop(itemWidth, itemHeight, areaWidth, areaHeight)
{
        var horizontalGap, verticalGap;

        var itemWidthNew = itemWidth;
        var itemHeightNew = itemHeight;

        var itemProportion = itemWidth / itemHeight;

        itemHeightNew = areaHeight;
        itemWidthNew = itemHeightNew * itemProportion;

        if(itemWidthNew < areaWidth) {
            itemWidthNew = areaWidth;
            itemHeightNew = itemWidthNew / itemProportion;
        }

        verticalGap = (areaHeight - itemHeightNew) / 2;
        horizontalGap = (areaWidth - itemWidthNew) / 2;

        var result = {
            'width' : itemWidthNew,
            'height' : itemHeightNew,
            'horizontalGap' : horizontalGap,
            'verticalGap' : verticalGap
        };

        //console.log(result);

        return result;
}

function onLoadImgToShow(url,$bl,call) {
    var $img=$('<img>').one('load error', function(){
        if(typeof call=='function'){
            call()
        }else{
            $bl.addClass('to_show')
        }
    })[0].src=url;
    if($img[0].complete)$img.load();
}

function getEmojiRegExp() {
    var emojiRanges = [
	'(?:\uD83C[\uDDE6-\uDDFF]){2}', // флаги
	'[\u0023-\u0039]\u20E3', // числа
	'(?:[\uD83D\uD83C\uD83E][\uDC00-\uDFFF]|[\u270A-\u270D\u261D\u26F9])\uD83C[\uDFFB-\uDFFF]', // цвет кожи
	'\uD83D[\uDC68\uDC69][\u200D\u200C].+?\uD83D[\uDC66-\uDC69](?![\u200D\u200C])', // семья
	'[\uD83D\uD83C\uD83E][\uDC00-\uDFFF]', // суррогатная пара
	'[\u3297\u3299\u303D\u2B50\u2B55\u2B1B\u27BF\u27A1\u24C2\u25B6\u25C0\u2600\u2705\u21AA\u21A9]', // обычные
	'[\u203C\u2049\u2122\u2328\u2601\u260E\u261d\u2620\u2626\u262A\u2638\u2639\u263a\u267B\u267F\u2702\u2708]',
	'[\u2194-\u2199]',
	'[\u2B05-\u2B07]',
	'[\u2934-\u2935]',
	'[\u2795-\u2797]',
	'[\u2709-\u2764]',
	'[\u2622-\u2623]',
	'[\u262E-\u262F]',
	'[\u231A-\u231B]',
	'[\u23E9-\u23EF]',
	'[\u23F0-\u23F4]',
	'[\u23F8-\u23FA]',
	'[\u25AA-\u25AB]',
	'[\u25FB-\u25FE]',
	'[\u2602-\u2618]',
	'[\u2648-\u2653]',
	'[\u2660-\u2668]',
	'[\u26A0-\u26FA]',
	'[\u2692-\u269C]'
    ];
    return new RegExp(emojiRanges.join('|'), 'g');
}

function emojiToHtml(str) {
	str = str.replace(/\uFE0F/g, '');
	return str.replace(getEmojiRegExp(), extractEmojiToCodePoint);
}

function extractEmojiToCodePoint(emoji) {
	var code = emoji
		.split('')
		.map(function (symbol, index) {
			return emoji.codePointAt(index).toString(16);
		})
		.filter(function (codePoint) {
			return !isEmojiSurrogatePair(codePoint);
		}, this)
		.join('-');
	var codeParts = code.split('-');
	var result = '';

	for(var i = 0; i < codeParts.length; i++) {
		result += '&#' + parseInt(codeParts[i], 16) + ';';
	}

    return result;
}

function isEmojiSurrogatePair(codePoint) {
	codePoint = parseInt(codePoint, 16);
	return codePoint >= 0xD800 && codePoint <= 0xDFFF;
}


function setOptionsSite(options){
    siteOptions={};
    if(typeof options==='object'){
        siteOptions=options;
    }
}

function isSiteOptionActive(option, key){
    key=key||'options';
    if(typeof siteOptions!=='object'||typeof siteOptions[key]=='undefined'||typeof siteOptions[key][option]=='undefined')return false;
    return siteOptions[key][option] === 'Y';
}

function getSiteOption(option, key){
    key=key||'options';
    if(typeof siteOptions!=='object'||typeof siteOptions[key]=='undefined'||typeof siteOptions[key][option]=='undefined')return null;
    return siteOptions[key][option];
}

function setGUserOptions(options){
    gUserOptions={};
    if(typeof options==='object'){
        gUserOptions=options;
    }
}

function getGUserOption(key){
    if(typeof gUserOptions!=='object'||typeof gUserOptions[key]=='undefined')return false;
    return gUserOptions[key];
}

function iOSversion(fullVer) {
    if (!!navigator.platform && /iP(hone|od|ad)/i.test(navigator.platform)) {
        fullVer=fullVer||0;
        var v = (navigator.appVersion).match(/OS (\d+)_(\d+)_?(\d+)?/);
        if(fullVer){
            return [parseInt(v[1], 10), parseInt(v[2], 10), parseInt(v[3] || 0, 10)]
        }else{
            return parseInt(v[1], 10)
        }
    }
    return 0;
}

function getBrowserInfo() {
    var nAgt = navigator.userAgent;

    var isEdge = nAgt.indexOf('Edge') !== -1 && (!!navigator.msSaveOrOpenBlob || !!navigator.msSaveBlob);
    var isIE = typeof document !== 'undefined' && !!document.documentMode && !isEdge;

    var isFirefox = typeof window.InstallTrigger !== 'undefined';

    //var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    var isSafari = /^((?!chrome).)*safari/i.test(nAgt);// && isMobileBrowserIOS;
    var isOpera = !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;
    //var isChrome = !!window.chrome && !isOpera;
    var isChrome = /chrome/i.test(nAgt) && !isOpera && !isSafari;


    var browserName = navigator.appName;
    var fullVersion = '' + parseFloat(navigator.appVersion);
    var majorVersion = parseInt(navigator.appVersion, 10);
    var nameOffset, verOffset, ix;

    // In Opera, the true version is after 'Opera' or after 'Version'
    if (isOpera) {
        browserName = 'opera';
        try {
            fullVersion = navigator.userAgent.split('OPR/')[1].split(' ')[0];
            majorVersion = fullVersion.split('.')[0];
        } catch (e) {
            fullVersion = '0.0.0.0';
            majorVersion = 0;
        }
    }
    // In MSIE version <=10, the true version is after 'MSIE' in userAgent
    // In IE 11, look for the string after 'rv:'
    else if (isIE) {
        verOffset = nAgt.indexOf('rv:');
        if (verOffset > 0) { //IE 11
            fullVersion = nAgt.substring(verOffset + 3);
        } else { //IE 10 or earlier
            verOffset = nAgt.indexOf('MSIE');
            fullVersion = nAgt.substring(verOffset + 5);
        }
        browserName = 'IE';
    }
    // In Chrome, the true version is after 'Chrome'
    else if (isChrome) {
        verOffset = nAgt.indexOf('Chrome');
        browserName = 'chrome';
        fullVersion = nAgt.substring(verOffset + 7);
    }
    // In Safari, the true version is after 'Safari' or after 'Version'
    else if (isSafari) {
        verOffset = nAgt.indexOf('Safari');

        browserName = 'safari';
        fullVersion = nAgt.substring(verOffset + 7);

        if ((verOffset = nAgt.indexOf('Version')) !== -1) {
            fullVersion = nAgt.substring(verOffset + 8);
        }
        if (navigator.userAgent.indexOf('Version/') !== -1) {
            fullVersion = navigator.userAgent.split('Version/')[1].split(' ')[0];
        }
    }
    // In Firefox, the true version is after 'Firefox'
    else if (isFirefox) {
        verOffset = nAgt.indexOf('Firefox');
        browserName = 'firefox';
        fullVersion = nAgt.substring(verOffset + 8);
    }
    // In most other browsers, 'name/version' is at the end of userAgent
    else if ((nameOffset = nAgt.lastIndexOf(' ') + 1) < (verOffset = nAgt.lastIndexOf('/'))) {
        browserName = nAgt.substring(nameOffset, verOffset);
        fullVersion = nAgt.substring(verOffset + 1);

        if (browserName.toLowerCase() === browserName.toUpperCase()) {
            browserName = navigator.appName;
        }
    }

    if (isEdge) {
        browserName = 'edge';
        fullVersion = navigator.userAgent.split('Edge/')[1];
        // fullVersion = parseInt(navigator.userAgent.match(/Edge\/(\d+).(\d+)$/)[2], 10).toString();
    }

    // trim the fullVersion string at semicolon/space/bracket if present
    if ((ix = fullVersion.search(/[; \)]/)) !== -1) {
            fullVersion = fullVersion.substring(0, ix);
    }

    majorVersion = parseInt('' + fullVersion, 10);

    if (isNaN(majorVersion)) {
        fullVersion = '' + parseFloat(navigator.appVersion);
        majorVersion = parseInt(navigator.appVersion, 10);
    }

    return {
        name: browserName,
        version: majorVersion,
        fullVersion: fullVersion,
        nAgt: nAgt
    };
}

var infoBrowserWebRtc = null;
function getVersionChromeAgent(){
    var nAgt = navigator.userAgent;
    if (/chrome/i.test(nAgt)) {
        var verOffset = nAgt.indexOf('Chrome'),
            fullVersion = nAgt.substring(verOffset + 7),
            majorVersion = parseInt('' + fullVersion, 10);

        return majorVersion;
    }
    return false;

}

function detectBrowserWebRtc(){
    if (infoBrowserWebRtc !== null) {
        return infoBrowserWebRtc;
    }

    var result = {browser:'', version:''};

    if (typeof window === 'undefined' || !window.navigator) {
      infoBrowserWebRtc = result;
      return result;
    }

    var isWebRTCSupported = false;
    ['RTCPeerConnection', 'webkitRTCPeerConnection', 'mozRTCPeerConnection', 'RTCIceGatherer'].forEach(function(item) {
        if (isWebRTCSupported) {
            return;
        }
        if (item in window) {
            isWebRTCSupported = true;
        }
    });

    if (isWebRTCSupported) {
        var infoBrowser = getBrowserInfo();
        infoBrowser.nameReal = infoBrowser.name;
        if (infoBrowser.name != 'chrome') {
            var versionChromeAgent = getVersionChromeAgent();
            if (versionChromeAgent && versionChromeAgent >= 86) {//Opera mobile version - 86
                infoBrowser.name = 'chrome';
                infoBrowser.version = versionChromeAgent;
            }
        }

        if (infoBrowser.name !== 'safari' || infoBrowser.version > 10) {
            result = {browser:infoBrowser.name, version:infoBrowser.version}
        }
    }

    infoBrowserWebRtc = result;

    return result;
}

function iSIOSSafariWebRTC(infoCurrentBrowser) {
    infoCurrentBrowser = infoCurrentBrowser || detectBrowserWebRtc();
    return isMobileBrowserIOS && ('browser' in infoCurrentBrowser);
}

function iSMacOSSafariWebRTC(infoCurrentBrowser) {
    infoCurrentBrowser = infoCurrentBrowser || detectBrowserWebRtc();
    return !!navigator.platform.match(/^Mac/i) && infoCurrentBrowser.browser == 'safari';
}

function iSAppleSafariWebRTC() {
    var infoCurrentBrowser = detectBrowserWebRtc();
    return iSIOSSafariWebRTC(infoCurrentBrowser) || iSMacOSSafariWebRTC(infoCurrentBrowser);
}


if (window.jQuery) {
    $.fn.autocolumnlist = function(params){
        var defaults = {
            columns: 5,
            classname: 'column',
            min: 1,
            clickEmpty:function(){}
        };
        var options = $.extend({}, defaults, params);
        return this.each(function() {
            options.columns *=1;
            var data_parameters = $(this).data(), i;
            if ( data_parameters ) {
                $.each(data_parameters, function (key, value) {
                    options[key] = value;
                });
            }

            var $el=$(this), els = $el.find('> li');
            var dimension = els.length;
            if (dimension > 0) {
                var elCol = Math.ceil(dimension/options.columns);
                if (elCol < options.min) {
                    elCol = options.min;
                }
                var start = 0, end = elCol, j=0, m=0;
                for (i=0; i<options.columns; i++) {
                    if ((i + 1) == options.columns) {
                        j++;
                        var cl=options.classname + ' cm_last';
                        if(options.columns==1){
                            cl=options.classname + ' cm_first';
                        }
                        els.slice(start, end).wrapAll('<div class="'+cl+'" />');
                        var $last=$el.find('.cm_last'), lEmpty=elCol-$last.find('li').length;
                        if(lEmpty){
                            for (j=0; j<lEmpty; j++) {
                                $('<li class="li_empty"><a href=""></a></li>')
                                .appendTo($last).
                                on('click', function(){
                                    options.clickEmpty();
                                    return false;
                                })
                            }
                        }
                    } else {
                        var cl=options.classname;
                        if (!m) {
                            cl += ' cm_first';
                        }
                        m++;
                        els.slice(start, end).wrapAll('<div class="'+cl+'" />');
                    }
                    start = start+elCol;
                    end = end+elCol;
                }
            }
        });
    };

    //data-cl-loader="btn_action_loader" data-no-fade-in="true" data-cl-children=".name"
    $.fn.addChildrenLoader = function(){
        var $btn=$(this);
        if($btn.is('.add_loader_transparent')||$btn.is('.add_loader'))return;
        var clLoader=$btn.data('clLoader');
        if(!clLoader)clLoader='btn_action_loader';

        var clBtn=$btn.data('clBtn');
        if(clBtn)$btn.addClass(clBtn);
        if(!$btn.data('noFadeIn')){
            $btn.addClass('add_loader').append(createLoader(clLoader,true,true).delay(1).removeClass('hidden',0));
            if($btn.data('clChildren')){
                $btn.find($btn.data('clChildren')).siblings(':not(.css_loader)').stop().fadeTo(200,0);
            }else{
                $btn.children('button, .frame').stop().fadeTo(200,.5);
                $btn.children(':not(.css_loader)').not('button, .frame').stop().fadeTo(200,0);
            }
        }else{
            $btn.addClass('add_loader_transparent').append(createLoader(clLoader,false,true));
        }
        return $btn;
    }

    $.fn.removeChildrenLoader = function(){
        var $btn=$(this);
        var fnDisabled=function(){
            $btn.data('disabled')&&$btn.prop('disabled', false);
        }
        if($btn.is('.add_loader_transparent')||$btn.is('.add_loader')){
            var clBtn=$btn.data('clBtn');
            if(clBtn)$btn.removeClass(clBtn);
            if($btn.is('.add_loader')){
                if($btn.data('clChildren')){
                    $btn.find($btn.data('clChildren')).siblings().stop().fadeTo(200,1,function(){
                        $btn.find('.css_loader').remove();
                        $btn.removeClass('add_loader');
                        fnDisabled();
                    })
                }else{
                    var $btnFade=$btn.children(':not(.css_loader)');
                    if($btnFade[0]){
                        $btn.children(':not(.css_loader)').stop().fadeTo(200,1,function(){
                            $btn.find('.css_loader').remove();
                            $btn.removeClass('add_loader');
                            fnDisabled();
                        })
                    } else {
                        $btn.find('.css_loader').remove();
                        $btn.removeClass('add_loader');
                        fnDisabled();
                    }
                }
                $btn.find('.css_loader').oneTransEnd(function(){
                    $(this).remove();
                }).addClass('hidden',0);
            }else{
                $btn.find('.css_loader').remove();
                $btn.removeClass('add_loader_transparent');
                fnDisabled();
            }
        }

        return $btn;
    }
}

function $jq(sel,context){
    context=context||false;
    var key=sel;
    if(context!==false){
        key=sel+'_'+context;
    }
    if(typeof cacheElement[key] == 'undefined' || !cacheElement[key][0]){
        if(context){
            cacheElement[key]=$(sel,context);
        }else{
            cacheElement[key]=$(sel);
        }
    }
    return cacheElement[key];
}

function createLoader(cl,isHide,isWhite){
    cl=cl||'';
    isHide&&(cl=cl+' hidden');
    var clSpin=isWhite?'spinnerw':'',
    $loader=$('<div class="css_loader '+cl+'">'+
                '<div class="spinner center '+clSpin+'">'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '<div class="spinner-blade"></div>'+
                '</div>'+
            '</div>');
    return $loader;
}

/* Join */
var isFrmJoinSiteSubmit = false,
    isJoiniRecaptcha = false,
    joinRecaptchaWd,
    joinRecaptchaResponse = '',
    joinRecaptchaKey,
    joinRecaptchaTheme,
    joinFnErrorBlur = function(){},
    isDisabledBirthday = false;


function joinRecaptchaVerifyCallback(response) {
    if (tmplCurrent == 'edge')joinFnErrorBlur($jq('#join_recaptcha'));
};

function onloadJoinRecaptchaCallback() {
    joinRecaptchaWd = grecaptcha.render('join_recaptcha', {
        'sitekey' : joinRecaptchaKey,
        'callback' : joinRecaptchaVerifyCallback,
        'theme' : joinRecaptchaTheme
    })

    if (tmplCurrent == 'edge' && !device.mobile()){
        detectWhenReCaptchaChallengeIsShown();
    }
}

function initJoinFrmSite($blForm, fnErrorShow, fnErrorHide, fnErrorFocus, fnErrorBlur) {
    if (!$blForm[0]) return;

    if (typeof fnErrorShow != 'function'){
        fnErrorShow = function(){};
    }
    if (typeof fnErrorHide != 'function'){
        fnErrorHide = function(){};
    }
    if (typeof fnErrorFocus != 'function'){
        fnErrorFocus = function(){};
    }
    if (typeof fnErrorBlur != 'function'){
        fnErrorBlur = function(){};
    } else {
        joinFnErrorBlur = fnErrorBlur;
    }

    if (tmplCurrent == 'edge') {
        $jq('#orientation', $blForm).on('change.bs.select',function(e){
            var $el=$(e.currentTarget);
            if ($el[0].value == 0) {
                if(isFrmJoinSiteSubmit)fnErrorShow($el,l('required_field'));
            } else {
                fnErrorHide($el)
            }
            fnErrorFocus($(e.currentTarget))
        })
        .on('show.bs.select',function(e){fnErrorFocus($(e.currentTarget))})
        .on('hide.bs.select',function(e){
            var $el=$(e.currentTarget);
            if ($el[0].value != 0)fnErrorBlur($(e.currentTarget))
        })
    }

    /* Location */
    if (tmplCurrent == 'edge') {
        var $fieldGeoAll=$jq('.field_geo', $blForm),
            $fieldGeo=$jq('.geo, #city', $blForm);

        $fieldGeo.on('change.bs.select',function(e){
            var $el=$(e.currentTarget);
            if ($el[0].value == 0) {
                if(isFrmJoinSiteSubmit)fnErrorShow($el,l('required_field'));
            } else {
                fnErrorHide($el)
            }
            fnErrorFocus($(e.currentTarget))
        })
        .on('show.bs.select',function(e){fnErrorFocus($(e.currentTarget))})
        .on('hide.bs.select',function(e){
            var $el=$(e.currentTarget);
            if ($el[0].value != 0)fnErrorBlur($(e.currentTarget))
        })
    }
    var $state = $('#state', $blForm),
        $city = $('#city', $blForm);
    $('.geo', $blForm).change(function(){
        var type=$(this).data('location'),
            $elLoader=[],$field=[],$btn;

        $fieldGeo.prop('disabled', true);
        if (tmplCurrent == 'edge') {
            $field=$fieldGeoAll.find('button.dropdown-toggle').addClass('disabled');
            if (type == 'geo_states') {
                $elLoader=$state.closest('.field').addChildrenLoader();
                $btn=$('button.dropdown-toggle[data-id="state"]').addClass('trans');
            } else {
                $elLoader=$city.closest('.field').addChildrenLoader();
                $btn=$('button.dropdown-toggle[data-id="city"]').addClass('trans');
            }
        } else {
            //$elLoader.removeChildrenLoader();
        }

        $.ajax({type: 'POST',
                url: urlMain+'ajax.php',
                data: { cmd:type,
                        select_id:this.value,
                        filter:'1',
                        list: 0},
                        beforeSend: function(){
                        },
                        success: function(res){
                            $fieldGeo.prop('disabled', false);
                            if (tmplCurrent == 'edge') {
                                $elLoader.removeChildrenLoader();
                                $btn.removeClass('trans');
                                $field.removeClass('disabled');
                            }
                            var data=checkDataAjax(res);
                            if (data) {
                                var option='<option value="0">'+l('choose_a_city')+'</option>';
                                switch (type) {
                                    //"Refresh" for Edge
                                    case 'geo_states':
                                        if (tmplCurrent == 'edge') {
                                            $state.html('<option value="0">'+l('choose_a_state')+'</option>' + data.list).selectpicker('refresh');
                                            $city.html(option).selectpicker('refresh');
                                        } else {
                                            $state.html('<option value="0">'+l('choose_a_state')+'</option>' + data.list);
                                            $city.html(option);
                                        }

                                        break
                                    case 'geo_cities':
                                        if (tmplCurrent == 'edge') {
                                            $city.html(option + data.list).selectpicker('refresh');
                                        } else {
                                            $city.html(option + data.list)
                                        }
                                        break
                                }
                            }

                        }
                    });
        return false;
    })
    /* Location */
    /* Birthday */
    var joinFrmSiteBirthAge = function() {
        var birth=new Date($year.val(), $month.val()-1, $day.val()),
            now = new Date(),
            age = now.getFullYear() - birth.getFullYear();
            age = now.setFullYear(1972) < birth.setFullYear(1972) ? age - 1 : age;
        return age >= usersAge;
    }

    var joinFrmSiteValidBirthday = function(){
        if(isIosApp)return;
        if(joinFrmSiteBirthAge()){
            fnErrorHide($month);
        }else{
            isFrmJoinSiteSubmit && fnErrorShow($month,l('incorrect_date'));
        }
    }

    var $day=$('#join_day', $blForm),
        $month=$('#join_month',$blForm),
        $year=$('#join_year', $blForm);

	$('.birthday',$blForm).change(function() {
        if(this.id!='day'){
            updateDay('month','frm_date','year','month','day',function(){
                if (tmplCurrent == 'edge') {
                    $day.selectpicker('refresh');
                }
            })
        }
        joinFrmSiteValidBirthday();
    })

	$month.change();
    /* Birthday */
    /* Email */
    var joinFrmSiteValidMail = function(){
        var val=trim($email.val()),res=false,f=f||1;
        if(!checkEmail(val)){
            if (isFrmJoinSiteSubmit) {
                fnErrorShow($email,l('incorrect_email'));
            }
		} else {
            fnErrorHide($email);
        }
        return res;
    }

    var $email=$('#join_email', $blForm).on('change propertychange input',joinFrmSiteValidMail)
               .on('focus',function(){fnErrorFocus($(this))}).on('blur',function(){fnErrorBlur($(this))});

	/* Email */
    /* Name */
    var joinFrmSiteValidUserName = function(){
        var val=$name.val(),len=$.trim(val).length;
        if (/[#&'"\/\\<]/.test(val)){
            fnErrorShow($name,l('invalid_username'));
        }else if((len<nameLengthMin||len>nameLengthMax)){
            if(isFrmJoinSiteSubmit){
                fnErrorShow($name,joinLangParts.incorrect_name_length);
            }
        } else {
            fnErrorHide($name);
        }
    }

    var $name=$jq('#join_name', $blForm).on('change propertychange input',joinFrmSiteValidUserName)
              .on('focus',function(){fnErrorFocus($(this))}).on('blur',function(){fnErrorBlur($(this))});
    /* Name */
    /* Pass */
    var joinFrmSiteValidatePassword = function(){
        var val=$pass.val(),len=val.length;
        if(~val.indexOf("'")<0){
            fnErrorShow($pass,l('invalid_password_contain'));
        } else if(len<passwordLengthMin||len>passwordLengthMax) {
            if(isFrmJoinSiteSubmit){
                fnErrorShow($pass,joinLangParts.incorrect_password_length);
            }
        } else {
            fnErrorHide($pass);
        }
    }

    var $pass=$jq('#join_password', $blForm).on('change propertychange input',joinFrmSiteValidatePassword)
              .on('focus',function(){fnErrorFocus($(this))}).on('blur',function(){fnErrorBlur($(this))});
    /* Pass */

    /* Captcha */
    if (isJoiniRecaptcha) {

    } else {
        var $captcha=$('#join_captcha', $blForm);
        if ($captcha[0]) {
            var $imgCaptcha = $('#img_captcha', $blForm);
            $imgCaptcha.click(function(){
                $imgCaptcha.attr('src', urlMain+'_server/securimage/securimage_show_custom.php?sid=' + Math.random());
                $captcha.val('').change();
            })

        $captcha.on('change propertychange input', function(){
                var val=trim($captcha.val());
                if(val){
                    fnErrorHide($captcha);
                }else{
                    if(isFrmJoinSiteSubmit){
                        fnErrorShow($captcha,l('incorrect_captcha'));
                    }
                }
            }).keydown(function(e){
                //if (e.keyCode==13&&!$jq('#join_done').prop('disabled')) {
                    //$jq('#join_done').click();
                    //return false;
                //}
            }).on('focus',function(){fnErrorFocus($(this))}).on('blur',function(){fnErrorBlur($(this))});
        }

        $jq('#join_img_captcha').click(function(){
            this.src = urlMain+'_server/securimage/securimage_show_custom.php?sid=' + Math.random();
        })
    }
    /* Captcha */
    /* Agree */
    var $agree = $jq('#join_agree', $blForm),
        $agreeBlError = $agree;
    if (tmplCurrent == 'edge') {
        $agree = $jq('.join_agree:visible', $blForm);
        $agreeBlError = $jq('.join_agree_bl:visible');
    }
    $agree.on('change',function(){
        if (tmplCurrent == 'edge') {
            $jq('.join_agree', $blForm).not($agree).prop('checked', $agree.prop('checked'));
        }
        if($agree.prop('checked')){
            fnErrorHide($agreeBlError);
        }else{
            fnErrorShow($agreeBlError,l('you_need_to_agree_to_the_terms'));
        }
    })

    $jq('body').on('click', function(e){
        if (isJoiniRecaptcha) fnErrorBlur($jq('#join_recaptcha'));
        var $el=$(e.target);
        if ($el.is('.join_agree')) {
            return;
        }
        fnErrorBlur($agreeBlError);
    })
    /* Agree */
    /* Submit */
    var joinFrmSiteDisabledControl = function(state){
        state=defaultFunctionParamValue(state, true);
        $jq('input', $blForm).prop('disabled', state);
        $jq('select', $blForm).prop('disabled', state).selectpicker('refresh');
    }

    var joinFrmSiteSetDisabledSubmitJoin = function(setError, notSubmitDisabled){
        notSubmitDisabled=notSubmitDisabled||0;
        setError=setError||0;
		var is=0,isError,isF=false;
		$jq('input:visible, select', $blForm).not('.not_frm').each(function(){
			var val=trim(this.value), $el=$(this);
            if(isIosApp
                && ($el.closest('.field_geo')[0] || $el.is('#orientation'))){
                return true;
            }
            if ($el.is('input')&&$el.closest('.bootstrap-select')[0]) {
                return true;
            }
            if (this.id=='join_email') {
                isError=!checkEmail(val);
                if(isError)fnErrorShow($el,l('incorrect_email'),isF,isF);
            } else if($el.is('.join_agree')) {
                isError=!$el.prop('checked');
                if(isError)fnErrorShow($agreeBlError,l('you_need_to_agree_to_the_terms'),isF,isF);
            } else {
                isError=(val==0||val=='');
                if (isError) {
                    var msg=$el.data('error')?$el.data('error'):l('required_field');
                    fnErrorShow($el,msg,isF,isF);
                }
            }
            if(isError)isF=true;
            is|=isError;
		})

        if (!isDisabledBirthday) {
            is|=!joinFrmSiteBirthAge();
        }

        if(isJoiniRecaptcha){
            isError=grecaptcha.getResponse(joinRecaptchaWd)=='';
            if(isError){
                fnErrorShow($jq('#join_recaptcha'),l('incorrect_captcha'),isF,isF);
            }
            is|=isError;
        }

        return is;
    }

    var showErrorFromDataJoin = function(data, dataBlocks){
        var dataBlock = '',isF=false;
        for(var dataBlocksKey in dataBlocks) {
            dataBlock = $(data).filter(dataBlocksKey);
            if(dataBlock.length) {
                fnErrorShow($(dataBlocks[dataBlocksKey]),dataBlock.text(),isF,isF);
                isF=true;
            }
        }
    }

    var dataFrm={};
	$jq('#join_submit', $blForm).click(function(){
        isFrmJoinSiteSubmit=true;
        if (joinFrmSiteSetDisabledSubmitJoin(false,true,true)) {
            return false;
        }

        joinFrmSiteDisabledControl();
        var $btn=$(this).prop('disabled',true).addChildrenLoader();

        $jq('input:visible, select, .location_default', $blForm).each(function(){
            if(this.name)dataFrm[this.name]=trim(this.value);
		})

        if(isJoiniRecaptcha){
            dataFrm['recaptcha']=grecaptcha.getResponse(joinRecaptchaWd);
        }

        $.post(urlMain+'join.php?cmd=register&ajax=1',dataFrm,
                    function(data){

                        var res=$(data).filter('.redirect');
                        if(res[0]){
                            redirectUrl(res.text());
                            return;
                        }
                        $btn.removeChildrenLoader();
                        joinFrmSiteDisabledControl(false);

                        res=$(data).filter('.wait_approval');
                        if(res[0]){
                            confirmCustom(l('no_confirmation_account'), redirectToLoginPage, l('alert_html_alert')) ;
                        }else{
                            $btn.prop('disabled',false);
                            if(isJoiniRecaptcha){
                                grecaptcha.reset(joinRecaptchaWd);
                            }else{
                                $jq('#join_img_captcha').click();
                                $captcha.val('');
                            }
                            var dataBlocks = {'.mail' : '#join_email',
                                              '.name' : '#join_name',
                                              '.password' : '#join_password',
                                              '.birthday' : '#join_month',
                                              '.captcha' : '#join_captcha',
                                              '.recaptcha' : '#join_recaptcha',
                            };
                            showErrorFromDataJoin(data, dataBlocks);
                        }
        })
    })
    /* Submit */
}
/* Join */
/* Log In */
function initLoginFrmSite($blForm, fnHideError) {
    if (!$blForm[0]) return;
    var $btnSubmit=$("button, input[type='submit']", $blForm);
    $("input[name='user'], input[name='password']", $blForm).on('change propertychange input',function(){
        if(typeof fnHideError=='function'){
            fnHideError()
        }
        $btnSubmit.prop('disabled', false);
    }).keydown(doOnEnter(function(){
        if(typeof fnHideError=='function'){
            fnHideError()
        }
        $btnSubmit.click()
    }))
}

function loginInSite($btnSubmit, $blForm, fn) {
    var $controls=$("input[name='user'], input[name='password'], input[name='remember']", $blForm).add($btnSubmit),
        $name=$("input[name='user']", $blForm),
        $pass=$("input[name='password']", $blForm),
        $remember=$("input[name='remember']", $blForm);
    if (typeof fn!= 'function'){
        fn = alertCustom;
    }

    if($blForm.data('submit'))return;
    $blForm.data('submit', true);
    $controls.prop('disabled', true);
    $name.val($.trim($name.val()));
    var data={user:$name.val(), password:$pass.val()};
    if($remember.prop('checked'))data.remember=1;
    $btnSubmit.addChildrenLoader();

    $.post(url_ajax+'?cmd=login&ajax=1',data,function(res){
        $blForm.data('submit', false);
        if(res.substring(0, 11) == '#js:logged:') {
            redirectUrl(res.substring(11));
			return false;
		}
		if(res.substring(0, 10) == '#js:error:') {
            $btnSubmit.removeChildrenLoader();
            $controls.prop('disabled', false);
            fn(res.substring(10));
			return false;
		}
        redirectUrl('index.php');
    })
    return false;
}
/* Log In */
/* Profile settings */
function disabledControlsProfileSettingsFrm($blSettings,is){
    is=is||false;
    $jq('input, select, textarea, button.btn_save',$blSettings).not('[type="hidden"]').prop('disabled',is);
    $jq('select',$blSettings).selectpicker('refresh');
}

function initProfileChangePassword($blForm, fnShowError, fnHideError, fnErrorFocus, fnErrorBlur, fnConfirm) {
    $blForm=$blForm||$('#fields_5');
    var $blSettings=$jq('#bl_forms_settings'),
        $btn=$('button.btn_save',$blForm),
        $controls=$("input[type=password], button.btn_save", $blForm);

    if (typeof fnShowError!= 'function'){
        fnShowError = alertCustom;
    }

    if (typeof fnHideError!= 'function'){
        fnHideError = closeAlert;
    }

    if (typeof fnConfirm!= 'function'){
        fnConfirm = confirmCustom;
    }

    if (typeof fnErrorFocus != 'function'){
        fnErrorFocus = function(){};
    }
    if (typeof fnErrorBlur != 'function'){
        fnErrorBlur = function(){};
    }

    function setDisabledBtn(){
        var is=0;
        $jq('input[type=password]', $blForm).each(function(){
            var val=this.value;
            is|=(val==''||$(this).is('.wrong'));
        })
        $btn.prop('disabled',is);
    }

    var $newPass=$('#new_password',$blForm),
        $verPass=$('#verify_new_password', $blForm),
        $oldPass=$('#old_password', $blForm);
    var validatePass = function($pass, hide){
        hide=hide||false;
        var val=$pass.val(),ln=val.length,res=1;
        if(~val.indexOf("'")<0){
            fnShowError($pass,l('invalid_password_contain'),hide);
        }else if($blForm.data('change')&&(ln<settingsData.passLenMin||ln>settingsData.passLenMax)) {
            fnShowError($pass,settingsData.minMaxLenPass,hide);
            res=0;
        }else if($blForm.data('change')&&$pass[0].name=='verify_new_password'&&$pass[0].value!=$newPass.val()){
            fnShowError($pass,l('passwords_not_same'),hide);
            res=0;
        }else if($blForm.data('change')&&$pass[0].name=='new_password'){
            if ($pass[0].value==$oldPass.val()) {
                fnShowError($pass,l('old_and_new_passwords_are_the_same'),hide);
                hide=true;
                res=0;
            }else{
                fnHideError($pass);
            }
            if ($pass[0].value==$verPass.val()) {
                fnHideError($verPass);
            }else{
                fnShowError($verPass,l('passwords_not_same'),hide, true);
                res=0;
            }
        } else {
            fnHideError($pass)
        }
        setDisabledBtn();
        return res;
    }

    var $passAll=$jq('input[type=password]',$blForm)
        .on('change propertychange input', function(e){
        validatePass($(this))
    }).on('focus',function(){fnErrorFocus($(this))
    }).on('blur',function(){fnErrorBlur($(this))
    }).keydown(function(e){
        if(e.keyCode==13) {
            $btn.click();
            return false;
        }
    })


    var showErrorFromDataJoin = function($data, dataBlocks){
        var dataBlock = '',isF=false;
        for(var dataBlocksKey in dataBlocks) {
            dataBlock = $data.filter(dataBlocksKey);
            if(dataBlock.length) {
                fnShowError($(dataBlocks[dataBlocksKey]),dataBlock.text(),isF,isF);
                isF=true;
            }
        }
    }

    $btn.click(function(){
        if($blForm.data('submit'))return;
        $blForm.data('submit', true);
        $blForm.data('change', true);

        var isError=false,
            oldVal=$oldPass.val(),
            newVal=$newPass.val(),
            verVal=$verPass.val();
        if(newVal.length!=verVal.length){
            fnShowError($verPass,l('passwords_not_same'));
            isError=true;
        }else if(oldVal==newVal) {
            fnShowError($newPass,l('old_and_new_passwords_are_the_same'));
            isError=true;
        }

        if (isError) {
            $blForm.data('submit', false);
            return false;
        }

        $btn.prop('disabled', true).addChildrenLoader();
        $blSettings.data('change',true);
        disabledControlsProfileSettingsFrm($blSettings,true);

        var data={
            old_password : $oldPass.val(),
            new_password : $newPass.val(),
            verify_new_password: $verPass.val()
        }

        $.post(url_main+'profile_settings.php?cmd=password&ajax=1',data,
            function(res){
                var data=checkDataAjax(res);
                $btn.removeChildrenLoader().prop('disabled', false);
                $blForm.data('submit', false);
                $blSettings.data('change',false);
                disabledControlsProfileSettingsFrm($blSettings);
                if(data!==false){

                    if(data==''){
                        $("input[type=password]", $blForm).val('');
						if (tmplCurrent == 'edge') {
							alertCustomIcon(l('changes_saved'),l('alert_success'),'success');
						} else {
							alertCustom(l('changes_saved'),l('alert_success'));
						}
                    } else {
                        var $data=$(data),
                            dataBlocks = {'.old_password_error' : '#old_password',
                                          '.new_password_error' : '#new_password',
                                          '.ver_password_error' : '#verify_new_password'};
                        showErrorFromDataJoin($data, dataBlocks);
                        $btn.prop('disabled', true);
                    }
                }
        })
        return false;
    })
}

function initProfileChangeEmail($blForm, fnShowError, fnHideError, fnErrorFocus, fnErrorBlur, fnConfirm) {
    $blForm=$blForm||$('#fields_6');
    var $blSettings=$jq('#bl_forms_settings'),
        $btn=$('button.btn_save',$blForm),
        $controls=$("input#new_email, input#password_email, button.btn_save", $blForm);

    if (typeof fnShowError!= 'function'){
        fnShowError = alertCustom;
    }

    if (typeof fnHideError!= 'function'){
        fnHideError = closeAlert;
    }

    if (typeof fnConfirm!= 'function'){
        fnConfirm = confirmCustom;
    }

    if (typeof fnErrorFocus != 'function'){
        fnErrorFocus = function(){};
    }
    if (typeof fnErrorBlur != 'function'){
        fnErrorBlur = function(){};
    }

    function setDisabledBtn(){
        var is=0;
        $jq('input#new_email, input#password_email', $blForm).each(function(){
            var val=this.value;
            if(this.id=='new_email')val=$.trim(val);
            is|=(val==''||$(this).is('.wrong'));
        })
        $btn.prop('disabled',is);
    }

    var validatePass = function(hide){
        hide=hide||false;
        var val=$pass.val(),ln=val.length,res=1;
        if(~val.indexOf("'")<0){
            fnShowError($pass,l('invalid_password_contain'),hide);
            res=0;
        }else if($blForm.data('change')&&(ln<settingsData.passLenMin||ln>settingsData.passLenMax)) {
            fnShowError($pass,settingsData.minMaxLenPass,hide);
            res=0;
        } else {
            fnHideError($pass)
        }
        setDisabledBtn();
        return res;
    }

    var $pass=$jq('input#password_email',$blForm)
        .on('change propertychange input', function(e){
        validatePass()
    }).on('focus',function(){fnErrorFocus($(this))
    }).on('blur',function(){fnErrorBlur($(this))
    }).keydown(function(e){
        if(e.keyCode==13) {
            $btn.click();
            return false;
        }
    })

    var validateEmail = function(hide){
        hide=hide||false;
        var val=trim($email.val()),res=1;
        if($blForm.data('change') && !checkEmail(val)){
            fnShowError($email,l('incorrect_email'),hide);
            res=0;
		} else {
            fnHideError($email);
        }
        setDisabledBtn();
        return res;
    }

    var $email=$jq('input#new_email',$blForm)
        .on('change propertychange input', function(e){
        validateEmail();
    }).on('focus',function(){fnErrorFocus($(this))
    }).on('blur',function(){fnErrorBlur($(this))
    }).keydown(function(e){
        if(e.keyCode==13) {
            $btn.click();
            return false;
        }
    })

    var showErrorFromDataJoin = function($data, dataBlocks){
        var dataBlock = '',isF=false;
        for(var dataBlocksKey in dataBlocks) {
            dataBlock = $data.filter(dataBlocksKey);
            if(dataBlock.length) {
                fnShowError($(dataBlocks[dataBlocksKey]),dataBlock.text(),isF,isF);
                isF=true;
            }
        }
    }

    $btn.click(function(){
        if($blForm.data('submit'))return;
        $blForm.data('submit', true);
        var isError=0;

        $blForm.data('change', true);
        isError=!validateEmail();
        if (isError) {
            $email.focus();
        }
        if(!validatePass(isError)){
            if (!isError) {
                $pass.focus();
                isError=1;
            }
        }

        if (isError) {
            $blForm.data('submit', false);
            return false;
        }

        var data={
            ajax : 1,
            cmd : 'update_email',
            new_email : trim($email.val()),
            password : $pass.val()
        }

        $btn.prop('disabled', true).addChildrenLoader();
        $blSettings.data('change',true);
        disabledControlsProfileSettingsFrm($blSettings,true);

        $.post(url_main+'profile_settings.php',data,
                function(res){
                    $controls.prop('disabled',false);
                    var data=checkDataAjax(res);
                    $btn.removeChildrenLoader().prop('disabled', false);
                    $blForm.data('submit', false);
                    $blSettings.data('change',false);
                    disabledControlsProfileSettingsFrm($blSettings);
                    if(data!==false){
                        var $data=$(data),
                            dataBlocks = {'.email_new_error' : '#new_email',
                                          '.password_error' : '#password_email'};
                        if($data.filter('span')[0]){
                            showErrorFromDataJoin($data, dataBlocks);
                            $btn.prop('disabled', true);
                        } else {
							if (tmplCurrent == 'edge') {
								alertCustomIcon(l('changes_saved'),l('alert_success'),'success');
							} else {
								alertCustom(l('changes_saved'),l('alert_success'));
							}

                        }
                    }
        })
        return false;
    })
}

function initProfileDelete($blForm, fnShowError, fnHideError, fnConfirm) {
    $blForm=$blForm||$('#fields_7');
    var $blSettings=$jq('#bl_forms_settings'),
        $btn=$('button.btn_save',$blForm),
        $cmd=$("input[type='hidden']", $blForm),
        $controls=$("input[name='password'], button.btn_save", $blForm);
    $blForm.data('values',{});
    if (typeof fnShowError!= 'function'){
        fnShowError = alertCustom;
    }

    if (typeof fnConfirm!= 'function'){
        fnConfirm = confirmCustom;
    }

    function setDisabledBtn(){
        var is=$pass.val()==''||$pass.is('.wrong');
        $btn.prop('disabled',is);
    }

    var $pass=$jq("input[type='password']",$blForm)
        .on('change propertychange input', function(e){
        var val=this.value,ln=val.length;
        if(~val.indexOf("'")<0){
            fnShowError($(this),l('invalid_password_contain'));
        }else if($blForm.data('change')&&(ln<settingsData.passLenMin||ln>settingsData.passLenMax)) {
            fnShowError($(this),settingsData.minMaxLenPass);
        } else {
            if(typeof fnHideError=='function'){
                fnHideError($(this))
            }
        }
        setDisabledBtn();
    }).keydown(function(e){
        if(e.keyCode==13) {
            $btn.click();
            return false;
        }
    })

    $btn.click(function(){
        if($blForm.data('submit'))return;
        $blForm.data('submit', true);
        var cmd=$cmd.val();
        $controls.prop('disabled', true);
        if(cmd=='check_password'){
            var val=$pass.val(),ln=val.length;
            $blForm.data('change', true);
            if(ln<settingsData.passLenMin||ln>settingsData.passLenMax) {
                $blForm.data('submit', false);
                $pass.prop('disabled', false);
                fnShowError($pass,settingsData.minMaxLenPass);
                return false;
            }
            $blForm.data('values',{ajax:1,password:val});
        }
        var data=$blForm.data('values');
        data.cmd=cmd;
        $pass.val('');
        $btn.addChildrenLoader();
        $blSettings.data('change',true);

        disabledControlsProfileSettingsFrm($blSettings,true);
        $.post(url_main+'profile_settings.php',data,
                function(res){
                    $controls.prop('disabled',false);
                    var data=checkDataAjax(res);
                    if(data!==false){
                        var $data=$(data);
                        if($data.is('error')){
                            fnShowError($pass,$data.text());
                        }else if(data=='check'){
                            $pass.prop('disabled',true);
                            fnConfirm(l('the_profile_will_be_deleted_forever'), function(){
                                $cmd.val('profile_delete');
                                $btn.addChildrenLoader().click().prop('disabled',true);
                                closeAlert();
                            },function(){
                                $pass.prop('disabled',false);
                                closeAlert();
                            });
                        }else if(data=='delete'){
                            redirectUrl(url_main+'index.php');
                        }else if(data=='demo') {
                            console.log('DEMO USER NO DELETE');
                        }
                    }
                    $cmd.val('check_password');
                    $btn.removeChildrenLoader();
                    $blForm.data('submit', false);
                    $blSettings.data('change',false);
                    disabledControlsProfileSettingsFrm($blSettings);
            })
            return false;
    })
}

function checkModifiedSettingsData($blForm) {
    $blForm=$blForm||$('#frm_profile_settings');
    if($blForm.data('submit')){
        return false
    }else{
       return $('button.btn_save',$blForm).not(':disabled')[0]
    }
}

function initProfileChangeSettings($blForm) {
    $blForm=$blForm||$('#frm_profile_settings');
    var $blSettings=$jq('#bl_forms_settings'),
        $btn=$('button.btn_save',$blForm);

    $blForm.data('values',{});

    function setSettingsData(){
        $('input:radio:checked, select',$blForm).each(function(){
            var data=$blForm.data('values');
            data[this.name]=this.value;
            $blForm.data('values',data);
        })
    }
    setSettingsData();

    function isModifiedSettingsData(){
        var is=0,data=$blForm.data('values');
        $('input:radio:checked, select',$blForm).each(function(){
            is|=(this.value!=data[this.name])
        })
        return is;
    }

    function setDisabledSettingsBtn() {
        $btn.prop('disabled',!isModifiedSettingsData());
    }

    $('input:radio, select',$blForm).on('change',setDisabledSettingsBtn);

    $btn.click(function(){
        $blForm.data('this_btn',$(this));
    })

    function saveSettingsResponse(res){
        var data=checkDataAjax(res);
        if(data!==false){
            var info=$blForm.data('values');
            if(info.set_language!=$("select[name='set_language']",$blForm).val()){
                //alertCustomRedirect(urlPagesSite.profile_settings,data.msg,data.title);
                redirectUrl(urlPagesSite.profile_settings);
            } else {
                setSettingsData();
				if (tmplCurrent == 'edge') {
					alertCustomIcon(l('changes_saved'),l('alert_success'),'success');
				} else {
					alertCustom(l('changes_saved'),l('alert_success'));
				}
            }
        }
        $blForm.data('submit', false);
        $blForm.data('this_btn').removeChildrenLoader();
        disabledControlsProfileSettingsFrm($blSettings);
        setDisabledSettingsBtn();
    }

    $blForm.submit(function(){
        if (!isModifiedSettingsData()||$blForm.data('submit')) return false;
        $blForm.data('submit', true);
        $blForm.data('this_btn').addChildrenLoader();
        $(this).ajaxSubmit({success:saveSettingsResponse});
        disabledControlsProfileSettingsFrm($blSettings,true);
        return false;
    })
}
/* Profile settings */
/* Contact us */
function initContactUs($blForm, fnSuccess, fnError, fnErrorHide) {
    var $email=$("input[name='contact_email']", $blForm),
        $name=$("input[name='contact_username']", $blForm),
        $msg=$("textarea[name='contact_comment']", $blForm),
        $captcha=$("input[name='contact_captcha']", $blForm);
    if (typeof fnSuccess!= 'function'){
        fnSuccess = function(){
            alertCustom(l('message_sent'))
        };
    }
    if (typeof fnError!= 'function'){
        fnError = alertCustom;
    }
    if (typeof fnErrorHide!= 'function'){
        fnErrorHide = closeAlert;
    }
    var $controls=$('input, textarea', $blForm)
        .on('change propertychange input',function(){
            if(!isSubmit)return;
            if (this.name=='contact_email') {
                if (checkEmail(trim(this.value))) {
                    fnErrorHide(this)
                } else {
                    fnError($(this), l('incorrect_email'));
                }
            } else {
                fnErrorHide(this)
            }
        });//.on('focus',focusError).on('blur',blurError);

    var fnCheckData=function(){
        var is=false;
        $controls.each(function(){
            if (this.name=='contact_email') {
                if (!checkEmail(trim(this.value))) {
                    fnError($(this), l('incorrect_email'), is);
                    is=true;
                }
            }else if(trim(this.value)==''){
                fnError($(this), l('required_field'), is);
                is=true;
            }
        })
        if (isRecaptchaContact) {
            if(grecaptcha.getResponse(recaptchaWdContact)==''){
                fnError($jq('#contact_recaptcha'), l('incorrect_captcha'));
                is=true;
            }
        }
        return is;
    }

    var send =function(){
        if(ajax_login_status){
            var data={comment:trim($msg.val())};
        } else {
            var data={comment:trim($msg.val()),
                      email:trim($email.val()),
                      username:trim($name.val())
            }
        }
        $.post(url_main+'contact.php?cmd=send&ajax=1',data,function(res){
            if(!ajax_login_status){
                if(isRecaptchaContact){
                    grecaptcha.reset(recaptchaWdContact);
                }else{
                    refreshCaptcha();
                }
            }
            var data=getDataAjax(res, 'data');
            if(data!==false){
                fnSuccess()
            }else{
                alertServerError()
            }
            $controls.val('').prop('disabled',false);
            $btnSubmit.prop('disabled',false).removeChildrenLoader();
        })
    }

    var prepareMsg = function(){
        $controls.prop('disabled',true);
        $btnSubmit.prop('disabled',true).addChildrenLoader();
        if(!ajax_login_status){
            if(!isRecaptchaContact){
                captchaContact=trim($captcha.val());
            }
            $.post(url_main+'contact.php?cmd=check_captcha&ajax=1',{captcha:captchaContact},function(res){
                var data=getDataAjax(res, 'data');
                if(data===false){
                    $controls.prop('disabled',false);
                    $btnSubmit.prop('disabled',false).removeChildrenLoader();
                    if(isRecaptchaContact){
                        grecaptcha.reset(recaptchaWdContact);
                        fnError($jq('#contact_recaptcha'), l('incorrect_captcha'));
                    }else{
                        refreshCaptcha();
                        fnError($captcha.val(''), l('incorrect_captcha'));
                    }
                }else{
                    send()
                }
            })
        }else{
            send()
        }
    }

    var isSubmit=false;
    var $btnSubmit=$('.contact_submit', $blForm).click(function(){
        isSubmit=true;
        if(fnCheckData())return;
        prepareMsg()
    })
    return false;
}
/* Contact us */


function initClickOnLogoMainPage(url, call){
    url=url||urlPagesSite.index;
    $('.logo_main_page, .logo').each(function(){
        var $el=$(this).click(function(){
            if(currentPage!='index.php'){
                if (typeof call=='function') {
                    if(call()===true){
                        return false;
                    } else {
                        setTimeout(function(){redirectUrl(url)},1);
                    }
                }else{
                    redirectUrl(url);
                }
            }
            return false;
        });
        if(currentPage=='index.php')$el.css({cursor:'default'})
    })
}

function redirectUrlWithLoader($el, url){
    url=url||'';
    $el.addChildrenLoader();
    if($el[0].href){
        url=$el[0].href;
    }
    redirectUrl(url);
}

function logOut(){
    confirmCustom(l('do_you_want_to_log_out'), function(){
        redirectUrl(url_main+'index.php?cmd=logout');
    })
}

function updateSiteSeo(seo){
    var title=document.title;
    if (title!=seo.title) {
        document.title=seo.title;
        siteTitle=seo.title;
        siteTitleTemp=seo.title;
    }
    var $description=$('meta[name=description]'), description=$description.attr('content');
    if(description!=seo.description){
        $description.attr('content', seo.description);
    }
    var $keywords=$('meta[name=keywords]'), keywords=$keywords.attr('content');
    if(keywords!=seo.keywords){
        $keywords.attr('content', seo.keywords);
    }
}

function getOffsetElement(elem){
    if(elem.getBoundingClientRect){
        return getOffsetElementRect(elem)
    }else{
        return getOffsetElementSum(elem)
    }
}

function getOffsetElementSum(elem) {
    var top=0, left=0
    while(elem) {
        top=top+parseInt(elem.offsetTop)
        left=left+parseInt(elem.offsetLeft)
        elem=elem.offsetParent
    }
    return {top:top,left:left}
}

function getOffsetElementRect(elem) {
    var box = elem.getBoundingClientRect();
    var body = document.body;
    var docElem = document.documentElement;
    var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop;
    var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;
    var clientTop = docElem.clientTop || body.clientTop || 0;
    var clientLeft = docElem.clientLeft || body.clientLeft || 0;;
    var top  = box.top +  scrollTop - clientTop;
    var left = box.left + scrollLeft - clientLeft;
    return {top:Math.round(top),left:Math.round(left)};
}

function getMouseOffset(e) {
    var top=(e.clientY || e.pageY || e.originalEvent.touches[0].clientY) + $win.scrollTop(),
        left=(e.clientX || e.pageX || e.originalEvent.touches[0].clientX) + $win.scrollLeft();
    return {top:top,left:left};
}

function prepareStatusWritingIm(){
    var time=parseInt(new Date()/1000);
    for (var uid in status_writing) {
        if((time-status_writing[uid]) > timeoutSecServer){
            delete status_writing[uid];
        }
    }
    //console.log('STATUS WRITING',status_writing);
}

function prepareStatusWritingImOne(){
    var time=parseInt(new Date()/1000);
    if((time-status_writing) > timeoutSecServer){
        status_writing='';
    }
}

function grabsTextLink(text){
    var linksImg;
    var grabImg = function(text){
        //var pattern = /^https?:\/\/(?:[a-z\-]+\.)+[a-z]{2,6}(?:\/[^\/#?]+)+\.(?:jpe?g|gif|png)$/igm;
        var pattern = /https?:\/\/(?:[a-z\-_0-9\/\:\.]*\.(jpg|jpeg|png|gif))/igm;
        linksImg = text.match(pattern);
        //console.log(links);
        for (var key in linksImg){
            //console.log(linksImg[key]);
            text=text.replace(linksImg[key], '{img:'+key+'}')
        }
        return text;
    }
            var replaceLinksWithTag=function(text) {
                var pattern = /((https?:\/\/|ftp:\/\/|www\.)((?![.,?!;:()]*(\s|$))[^\s]){2,})/gim;
                text = text.replace(pattern, '<a href="$1" target="_blank">$1</a>');
                return text;
            }
            text = grabImg(text);
            text = replaceLinksWithTag(text);

            var replaceImg = function(text){
                //var pattern = /https?:\/\/(?:[a-z\-_0-9\/\:\.]*\.(jpg|jpeg|png|gif))/igm;
                //linksImg = text.match(pattern);
                //console.log(links);
                for (var key in linksImg){
                    text=text.replace('{img:'+key+'}', '<img src="'+linksImg[key]+'">')
                }
                return text;
            }
    text = replaceImg(text);
    return text;
}

/* GPS */
var geoPoint={lat:0,long:0},
    geoPointData={city:'',country:''}
    watchPositionTimeoutSec=60000,
    watchPositionTimeout=0;
function getGeoPosition() {
    if (!navigator.geolocation){
        return;
    }

    function success(pos) {
        geoPoint.lat=pos.coords.latitude;
        geoPoint.long=pos.coords.longitude;
        console.log('%cGEO POINT GET:','background: #f7f68f', geoPoint);
    };

    function error() {
        clearTimeout(watchPositionTimeout);
        console.log('%cGEO GPS NOT AVIABLE:','background: #ff0000');

    };
    navigator.geolocation.getCurrentPosition(success, error);
}

function setWatchPositionTimeOut(time){
    watchPositionTimeoutSec=time*1000;
}

function watchPosition(){
    console.log('%cGEO POINT INIT:','background: #00ac20',watchPositionTimeoutSec);
    watchPositionTimeout&&clearTimeout(watchPositionTimeout);
    watchPositionTimeout=setTimeout('watchPosition()', watchPositionTimeoutSec);
    getGeoPosition();
}

function setGeoPointData(data){
    geoPointData = data;
}
/* GPS */

async function showAdmobBanner(adMobBannerConfig) {
    var config = adMobBannerConfig;

    if(typeof isAdmobBannerVisible !== "undefined" && isAdmobBannerVisible === false) {
        config = false;
    }

    if(typeof AdMob !== "undefined" && ('banner' in AdMob)) {
		if(config !== false) {
			AdMob.banner.config(config);
			AdMob.banner.prepare();
			AdMob.banner.show();
		} else {
			AdMob.banner.remove();
		}
    }

    if(typeof admob !== "undefined" && ('BannerAd' in admob)) {
		console.log('admob start');
		let banner;
		let bannerConfig = {
			adUnitId: '',
			position: 'bottom',
			id: 1
		}

		var lastBannerInfo = admobGetLastBannerInfo();
		console.log('showAdmobBanner lastBannerInfo', lastBannerInfo);
		var lastBannerId = 1;

		if(config !== false) {

			bannerConfig.adUnitId = config.id;
			bannerConfig.position = (config.bannerAtTop ? 'top' : 'bottom');

			if(lastBannerInfo !== null) {

				if(bannerConfig.adUnitId == lastBannerInfo.adUnitId && bannerConfig.position == lastBannerInfo.position) {
					console.log('admob config.id == lastBannerInfo.adUnitId');

					if(await admobIsBannerLoaded(lastBannerId) === true) {
						console.log('admobIsBannerLoaded return');
						return;
					}
				}

				console.log('remove banner if exists, will use another');
				await admobHideBanner(lastBannerId);
			}

			try {
				await admob.start();

				await admobHideBanner(lastBannerId);

				banner = new admob.BannerAd(bannerConfig);

				banner.on('load', (ex) => {
					//console.log('lastBannerId', ex.adId, ex);
					//bannerConfig.id = ex.adId;
					localStorage.setItem('lastBannerInfo', JSON.stringify(bannerConfig));
				});

				banner.load();
				banner.show();

				console.log('admob banner.show');
			} catch (e) {
				console.log('showAdmobBanner error', e);
			}

			console.log('admob config', bannerConfig);

			console.log('admob show end');
		} else {
			if(lastBannerInfo !== null) {
				await admobHideBanner(lastBannerId);
			}
		}

		console.log('admob end');
    }

}

function admobGetLastBannerInfo()
{
	var lastBannerInfo = localStorage.getItem('lastBannerInfo');
	if(lastBannerInfo !== null) {
		lastBannerInfo = JSON.parse(lastBannerInfo);
	}
	return lastBannerInfo;
}

async function admobHideBanner(bannerId)
{
	console.log('admobHideBanner start');

	var banner = new admob.BannerAd({
		id: bannerId
	});

	try {
		await banner.hide();
	} catch(e) {
		console.log('admobHideBanner error', e);
	}

	localStorage.removeItem('lastBannerInfo');

	console.log('admobHideBanner end');
}

async function admobIsBannerLoaded(bannerId)
{
	var isBannerLoaded = false;

	try {
		isBannerLoaded = await admob.BannerAd.isLoaded(bannerId);
	} catch (e) {
		console.log('admobIsBannerLoaded error', e);
	}

	return isBannerLoaded;
}


function appPermissionsActivator(permissions, callbackSuccess, callbackError) {

    if(getAndroidVersion() < 6) {
        callbackSuccess();
        return;
    }

    var permissionsPlugin = cordova.plugins.permissions;

    var permissionsList = [];

    for (i in permissions) {
        permissionsList.push(permissionsPlugin[permissions[i]]);
    }

    permissionsPlugin.hasPermission(permissionsList, function(status){
        if( !status.hasPermission ) {
          permissionsPlugin.requestPermissions(
            permissionsList,
            function(status) {
                if( !status.hasPermission ) {
                    callbackError()
                } else {
                    callbackSuccess();
                };
            },
            callbackError);
        } else {
            callbackSuccess();
        }
    }, null);

}

function initMediaChatMobileVersion() {
    $(function(){
        if(isMobileApp()) {
            //console.log('addEventListener initMediaChatMobileVersion');
            document.addEventListener('deviceready', appMediaChatCheckPermissions, false);
        } else {
            initMediaChat();
        }
    });
}

function initMediaLiveStreamingMobileVersion() {
    $(function(){
        if(isMobileApp()) {
            //console.log('addEventListener initMediaChatMobileVersion');
            document.addEventListener('deviceready', appMediaChatCheckPermissions, false);
        } else {
            initMediaLiveStreaming();
        }
    });
}

function appMediaChatCheckPermissions() {
    if(typeMediaChatData === 'video') {
        appVideochatCheckPermissions();
    } else {
        appAudiochatCheckPermissions();
    }
}

function appVideochatCheckPermissions() {
	if(MobileApp.isAndroid()) {
		var androidPermissions = ['CAMERA', 'RECORD_AUDIO'];
		appPermissionsActivator(androidPermissions, initMediaChat, appVideochatCheckPermissionsError);
	} else {
        initMediaChat();
        /*
		MobileApp.requestPermissionCamera(
            function() {
                cordova.plugins.diagnostic.isCameraAuthorized({
                    successCallback: function(authorized){
                        if(authorized) {
                            appAudiochatCheckPermissions();
                        } else {
                            appVideochatCheckPermissionsError();
                        }
                    },
                    errorCallback: appVideochatCheckPermissionsError,
                    externalStorage: false
                });
            },
            appVideochatCheckPermissionsError,
            false
        );
        */
	}
}

function appAudiochatCheckPermissions() {
	if(MobileApp.isAndroid()) {
        var androidPermissions = ['RECORD_AUDIO'];
        appPermissionsActivator(androidPermissions, initMediaChat, appAudiochatCheckPermissionsError);
	} else {
        initMediaChat();
		/*
        MobileApp.requestPermissionMicrophone(
            function() {
                cordova.plugins.diagnostic.isMicrophoneAuthorized(
                    function(authorized){
                        if(authorized) {
                            initMediaChat();
                        } else {
                            appAudiochatCheckPermissionsError();
                        }
                    },
                    appAudiochatCheckPermissionsError
                );
            },
            appAudiochatCheckPermissionsError
        );
        */
	}
}

function appVideochatCheckPermissionsError() {
    appCheckPermissionsShowAlert('app_does_not_have_permissions_to_access_the_camera_and_the_microphone');
}

function appAudiochatCheckPermissionsError() {
    appCheckPermissionsShowAlert('app_does_not_have_permission_to_access_the_microphone');
}

function appCheckPermissionsShowAlert(message)
{
	if(typeof _lsMediaAlert != 'undefined') {
		_lsMediaAlert(l(message));
	} else if(typeof urbanMobileTemplate != 'undefined') {
        showAlert(l(message));
    } else {
        showAlert(l(message), false, 'fa-info-circle');
    }
}

function isMobileApp() {
    return !!window.cordova;
}

function isMobileAppIos() {
    return (navigator.userAgent.indexOf('IOSWebview') >= 0);
}

function getAndroidVersion(type) {
    var version = MobileAppDevice.version;
    var type = type || 'main';
    if(type === 'main') {
        var versionParts = version.split('.');
        version = versionParts[0];
    }
    return version;
}

function getAndroidVersionUa(round) {
    round=round||false;
    var match=navigator.userAgent.toLowerCase().match(/android\s([0-9\.]*)/),
        version=match?match[1]:0;
    if(version&&round)version=parseInt(version, 10);
    return match ? match[1] : undefined;
};


/* City */
function cityParentClick(){
    if (typeof cityParentClickTemplate == 'function') {
        cityParentClickTemplate();
    }
}

/* iFrame */
function cilyIframeLogoLoad(){
    cityIframeLogoMobilePrepare();
    $('#city_logo').addClass('to_show');
}

function cityIframeClick(){
    if (typeof cityIframeClickTemplate == 'function') {
        cityIframeClickTemplate();
    }
}

function cityIframeSetUrlLocation(locUrl, seoTitle){
    if(window.history && history.pushState && locUrl){
        siteTitle=seoTitle;
        document.title=siteTitle;
        history.replaceState(history.state, siteTitle, locUrl);
    }
}

function cityIframeExit(){
    redirectUrl(urlPagesSite.home);
}

function cityIframeLogoMobilePrepare(){
    var $logo=$('.city_logo_mobile');
    if (!$logo[0] || $logo.is(':hidden')) return;
    var unit=isLandscapeCityIframe?'vh':'vw',
        w=$('.city_logo_mobile_img').data('w')+unit;
    if(isLandscapeCityIframe){
        w='calc('+w+' - 25px)';
    }
    $('.city_logo_mobile_img').css({width:w});
}

var isLandscapeCityIframe = false;
function cityIframeResize(e){
    var or=(e&&e.orientation)?e.orientation:$win[0].innerWidth>$win[0].innerHeight?'landscape':'portrait';
    isLandscapeCityIframe=or=='landscape';
    cityIframeLogoMobilePrepare()
}

function cityIframeInit(){
    if(window.orientation){
        $win.on('orientationchange',cityIframeResize);
    }else{
        $win.on('resize',cityIframeResize);
    }
    cityIframeResize();

    var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
    var eventer = window[eventMethod];
    var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

    eventer(messageEvent, function(e) {
        var msg = JSON.parse(e.data), data=msg.data;
        console.log('Iframe city msg: ', msg.type, data);
        switch (msg.type) {
            case 'click':
                cityIframeClick()
            break;
            case 'set_url':
                cityIframeSetUrlLocation(data.locUrl, data.seoTitle);
            break;
            case 'exit':
                cityIframeExit();
            break;
        }
    })
}
/* iFrame */
/* City */

function moveCaretToEnd(el){
    if (el.createTextRange){
        var r = el.createTextRange();
        r.collapse(false);
        r.select();
    }else if (el.selectionStart) {
        var end = el.value.length;
        el.setSelectionRange(end,end);
        //el.focus();
    }
}

var isTemplateDebug = true;
function debugLog(msg, data, color) {
    if(!isTemplateDebug)return;
    data=data||'';
    color=color||'#e6eaea';
    //console.log(msg, data);
    console.log('%c'+msg, 'background: '+color, data);
}

function he(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function appSetExternalUrlHandler() {
	$(document).on('click', 'a[target="_blank"]', function (e) {

		var url = trim($(this).prop('href'));
		var urlStart = url.substr(0, 4).toLowerCase();
		if(urlStart === 'http') {
			var platform = MobileAppDevice.platform.toLowerCase();
			if(platform === 'android') {
				navigator.app.loadUrl(url, { openExternal: true });
			} else if(platform === 'ios') {
				window.open(url, '_system');
			}
			e.preventDefault();
		}

	});
}

function appIosRecordAudioGreeting(authKey)
{
    $('.audio_greeting_record_button').addChildrenLoader();

    document.addEventListener(
        'deviceready',
        function() {
            navigator.device.capture.captureAudio(
                function(){
                    appIosRecordAudioGreetingHideLoader();
                    $('.delete_audio_greeting').removeClass('hide');
                },
                function() { appIosRecordAudioGreetingHideLoader(); },
                {
                    limit: 1,
                    successUrl: $('base').prop('href') + 'ajax.php?cmd=save_audio_greeting' + appIosPrepareAuthKey(authKey)
                }
            )
        }
    );
}

function appIosRecordAudioGreetingHideLoader()
{
    $('.audio_greeting_record_button').removeChildrenLoader();
}

function ieVersion() {
    var ua = window.navigator.userAgent;
    if (ua.indexOf("Trident/7.0") > -1)
        return 11;
    else if (ua.indexOf("Trident/6.0") > -1)
        return 10;
    else if (ua.indexOf("Trident/5.0") > -1)
        return 9;
    else
        return 0;
}

function visibilityChange(callFocus, callBlur) {
    var hidden, visibilityChange;
    if (typeof document.hidden !== "undefined") {
        hidden = "hidden";
        visibilityChange = "visibilitychange";
    } else if (typeof document.msHidden !== "undefined") {
        hidden = "msHidden";
        visibilityChange = "msvisibilitychange";
    } else if (typeof document.webkitHidden !== "undefined") {
        hidden = "webkitHidden";
        visibilityChange = "webkitvisibilitychange";
    }
    //document.visibilityState == "hidden"
    //document.visibilityState == "visible"
    function handleVisibilityChange() {
        if (document[hidden]) {
            callBlur();
        } else {
            callFocus();
        }
    }

    if (typeof document.addEventListener === "undefined" || hidden === undefined) {
        //console.log("This demo requires a browser, such as Google Chrome or Firefox, that supports the Page Visibility API.");
        return false;
    } else {
        document.addEventListener(visibilityChange, handleVisibilityChange, false);
        return true;
    }
}

if (window.jQuery) {
    $.fn.isHidden = function(){
        var $el=$(this);
        return $el.is(':hidden')||!isVisiblePage;
    }

    $.fn.aSlideDown = function(params){
        var defaults = {
            dur: 0,
            hidden: false,
            display: '',
            delay: 1,
            complete: function(){}
        };
        var options = $.extend({}, defaults, params),
            $el=$(this),
            isHidden=options.hidden||!isVisiblePage;
        if(isHidden){
            if(options.display){
                $el.css({display:options.display});
            } else {
                $el.show();
                /* jquery-1.11.2
                jQuery.each([ "toggle", "show", "hide" ], function( i, name ) {
                    var cssFn = jQuery.fn[ name ];
                    jQuery.fn[ name ] = function( speed, easing, callback ) {
                        return speed == null || typeof speed === "boolean"
                                        ? cssFn.apply( this, arguments )
                                        : this.animate( genFx( name, true ), speed, easing, callback );
                    };
                });*/
            }
            options.complete();
        } else {
            $el.delay(options.delay).slideDown(options.dur, options.complete)
        }
        return $el;
    }

    $.fn.aSlideUp = function(params){
        var defaults = {
            dur: 0,
            hidden: false,
            complete: function(){},
            param: false
        };
        var options = $.extend({}, defaults, params),
            $el=$(this),
            isHidden=options.hidden||!isVisiblePage;
        if(isHidden){
            $el.hide();
            options.complete();
        } else {
            if (typeof options.param=='object') {
                $el.slideUp(options.param)
            } else {
                $el.slideUp(options.dur, options.complete)
            }
        }
        return $el;
    }
}


(function() {
    var timeouts = [];
    var messageName = "zero-timeout-message";
    // Like setTimeout, but only takes a function argument.  There's
    // no time argument (always zero) and no arguments (you have to use a closure).
    function setZeroTimeout(fn) {
        timeouts.push(fn);
        window.postMessage(messageName, "*");
    }

    function handleMessage(event) {
        if (event.source == window && event.data == messageName) {
            event.stopPropagation();
            if (timeouts.length > 0) {
                var fn = timeouts.shift();
                fn();
            }
        }
    }

    window.addEventListener("message", handleMessage, true);

    // Add the one thing we want added to the window object.
    window.setZeroTimeout = setZeroTimeout;
})();



function initLightboxOldTemplate($el,offsetLeft){
    offsetLeft=offsetLeft||0;
    $el=$el||$('.lightbox');
    $el.lightBox({
        resizeImage:   true,
        offsetLeft:    offsetLeft,
        maxWidth:      $win.width()*.8,
        imageLoading:  url_tmpl_main+'images/svg/loading-spin-oryx.svg',
        imageBtnPrev:  url_tmpl+'common/lightbox/images/prev.gif',
        imageBtnNext:  url_tmpl+'common/lightbox/images/next.gif',
        imageBtnClose: url_tmpl+'common/lightbox/images/close.gif',
        imageBlank:    url_tmpl+'common/lightbox/images/blank.gif'
    })
}

function initLightboxOldTemplateMixer($el){
    initLightboxOldTemplate($el,0)
}

function initLightboxOldTemplateNewAge($el){
    initLightboxOldTemplate($el)
}

var serviceWorkerRegistration=false;
function notifInit(){
    if(!ajax_login_status || isDemoSite)return;
    if(!("Notification" in window))return;
    if(Notification.permission!=='granted' && Notification.permission!=='denied') {
        Notification.requestPermission(function(permission){
            debugLog('Web notif: Init', permission, '#edd9f0');
        })
    }else{
       debugLog('Web notif: Init', Notification.permission, '#edd9f0');
    }

    if (navigator.serviceWorker) {
        navigator.serviceWorker.register(urlMain+'_server/js/service_worker.js'+cacheVersionParam).then(function(registration) {
            serviceWorkerRegistration = registration;
            debugLog('Web notif: serviceWorker.register', true, '#edd9f0');
        }).catch(function(error) {
            debugLog('Web notif: serviceWorker.register ERROR', error, '#edd9f0');
        });
    } else {
        debugLog('Web notif: browser does not support service worker', 'ERROR', '#edd9f0');
    }
}

function notifSend(title, msg, params, data){
    var path=location.href.split('#'),
        pageUrl=path[0];
    if(params && params['tag']){
        pageUrl +='#'+params['tag'];
    }
    if(mobileAppLoaded) {
        mobileAppNotification(1, msg, false, false, pageUrl);
        return;
    }

    if(isVisiblePage)return;

    if(params && params['tag']){
        //location.hash=params['tag'];
        setPosToHistory(pageUrl)
    }

    if(isDemoSite)return;

    if(!isMobileSite && getGUserOption('sound'))playSound();

    params=params||{};
    var defaults = {
            body: msg,
            icon: faviconUrl,
            //image:
            //dir: 'rtl'
            tag: 'global_wn',
            requireInteraction: true,//To force a notification to stay visible until the user interacts,
            //sound: $('base')[0].href+'_server/im_new/sounds/pop_sound_chat.mp3',
            data: {pageUrl: pageUrl,
                   resetHash: false
            },
            serviceWorkerRegistration: serviceWorkerRegistration,
            onClick: function() {//Only Notification API
                debugLog('Web notif: Notification API click', true, '#edd9f0');
                window.focus();
                resetHashMedia();
            },
            //autoClose: 4000

    };
    var options=$.extend({},defaults,params);
    if (data) {
        options['data']=$.extend({},options['data'],data);
    }

    webNotification.showNotification(title, options, function onShow(error,hide) {
        if(error){
            debugLog('Web notif: Unable to show notification ERROR', error.message, '#edd9f0');
        }else{
            debugLog('Web notif: Notification Shown', true, '#edd9f0');
            /*setTimeout(function hideNotification(){
                hide();
            }, 5000)*/
        }
    })
}

var audioNotificationContext, audioNotificationBuffer;
function loadNotificationBufferSound(url) {
    var request = new XMLHttpRequest();
        request.open('GET', url, true);
        request.responseType = 'arraybuffer';

    request.onload = function() {
        audioNotificationContext.decodeAudioData(request.response, function(buffer) {
            audioNotificationBuffer = buffer;
        }, function(){});
    }
    request.send();
}

function playNotificationSound() {
    var source = audioNotificationContext.createBufferSource();
    source.buffer = audioNotificationBuffer;
    source.connect(audioNotificationContext.destination);
    source.start(0);
}

function initNotificationSound() {
    window.addEventListener('load', function(){
        try {
            window.AudioContext = window.AudioContext||window.webkitAudioContext;
            audioNotificationContext = new AudioContext();
            loadNotificationBufferSound(urlMain+'_server/im_new/sounds/pop_sound_chat.mp3');
            console.log('Web Audio API - init');
        } catch(e) {
            console.log('Web Audio API - is not supported in this browser');
        }
    }, false);
}

function initSmoothScroll() {
    if (window.WheelEvent && !window.MouseScrollEvent) {
        if (/chrome/.test(navigator.userAgent.toLowerCase())) {
            document.addEventListener('wheel', smooth_scroll, {useCapture: true, passive: false});
        } else {
            $(document).wheel(smooth_scroll);
        }
    }
}

if (window.jQuery) {
    $(document).on('click', '.im_audio_message > .im_audio_message_loader', function(){
        playImAudioMessage($(this));
    });
}

var imAudioPlayers = {};
function playImAudioMessageControlPause(key){
	imAudioPlayers[key]['icon'].removeClass('fa-pause').addClass('fa-play');
}

function playImAudioMessagePause(key){
    imAudioPlayers[key]['player'].pause();
}

function playImAudioMessageControlPlay(key){
	imAudioPlayers[key]['icon'].removeClass('fa-play').addClass('fa-pause');
}

function playImAudioMessagePlay(key){
	playImAudioMessageControlPlay(key);
	//_data=imAudioPlayers[_file]['player'].data_player
	imAudioPlayers[key]['player'].play();
}

function playImAudioMessageStopAll(){
	if (typeof soundManager != 'undefined') {
		 soundManager.stopAll();
	}
}

function playImAudioMessagePauseAll(){
	if (typeof soundManager != 'undefined') {
		soundManager.pauseAll();
	}
}

function playImAudioClearPlayer($item){
	$item.find('.fa').attr('style', '').removeClass('fa-pause').addClass('fa-play');
	$item.find('.im_audio_message_process_play').attr('style', '');
	var $blLoader=$item.find('.im_audio_message_loader');
	removeChildrenLoader($blLoader);
	var key=$blLoader.data('audio-message-file');
	if ($blLoader[0].id) {
		key=$blLoader[0].id;
	}
	delete imAudioPlayers[key];
}

function readImAudioMessage($bl){
	var mid=$bl.data('mid');
	if (mid) {
		var data=$bl.data();
		$.post(url_ajax+'?cmd=im_read_msg',{mid:data.mid, user_from:data.fromUserId,is_mode_fb:'false'},function(res){
			var data=checkDataAjax(res);
            if(data!==false){
                clCounters.update(data)
            }
		})
		$bl.data('mid', 0);
	}
}

function playImAudioMessage(item){
    var _$blLoader = $(item)
		_$icon = _$blLoader.find('.fa'),
		_file = _$blLoader.data('audio-message-file');
    if(!_$icon[0] || !_file) {
        return;
    }
	var _$bl=_$icon.closest('.im_audio_message'),
		_$processWrap=_$bl.find('.im_audio_message_process'),
		_$process=_$bl.find('.im_audio_message_process_play'),
		key=_file;
	if (_$blLoader[0].id) {
		key=_$blLoader[0].id;
	}
    if(imAudioPlayers[key]) {
		var _data=false;
		if (imAudioPlayers[key]['player']) {
			_data=imAudioPlayers[key]['player'].data_player;
		}
        if(imAudioPlayers[key]['isLoaderActive']) {
            return;
        } else if (!imAudioPlayers[key]['player'] || imAudioPlayers[key]['player'].playState === 0) {
            // play audio
		} else if(imAudioPlayers[key]['player'].paused){
			playImAudioMessagePauseAll();

			playImAudioMessagePlay(key);
			return;
        } else {
			playImAudioMessagePause(key);
            return;
        }
    }

    if (typeof soundManager != 'undefined' &&  typeof urlMain != 'undefined') {
		/* Read msg im */
		readImAudioMessage(_$blLoader);
		/* Read msg im */
        if(!imAudioPlayers[key]) {
            imAudioPlayers[key] = {};
        }
		//iOS, Android - in all running players STOP will work instead of PAUSE
		//By default, SoundManager 2 only applies the "singleton"-style,
		//single HTML5 Audio() object for mobile devices like iOS, Android and the like.
		//If you want to force it for desktop browsers, you can set forceUseGlobalHTML5Audio to true.
		//soundManager.setup({ forceUseGlobalHTML5Audio: true });
		playImAudioMessagePauseAll();

        imAudioPlayers[key]['isLoaderActive'] = true;
		imAudioPlayers[key]['icon'] = _$icon;


        addChildrenLoader(_$blLoader);
		//var urlFile = urlMain + _file;
        var urlFile =_file;

        if(!imAudioPlayers[key]['player']) {
			playImAudioMessageControlPlay(key);

			var _widthProgress=_$processWrap.width(), _dur, _proc, _duration=0, _audio;
			_$process.css({width:'0px',transition:'none'});

            imAudioPlayers[key]['player'] = soundManager.createSound({
					data_player : {
						url: urlFile,
						file: _file,
						key: key,
						icon: _$icon,
						loader: _$blLoader,
						duration: 0,
						duration_check: false,
						audio: false,
						progress_bar: _$process,
						progress_width: _widthProgress,
						progress_set: function(pl){
							var data=this,
								dur=pl.duration, proc=pl.position;

							if (!dur) {//Fix - There is not always audio duration in wav
								if (data.duration) {
									dur=data.duration;
								} else if(!data.duration_check) {
									var audio;
									data.duration_check=true;
									audio = new Audio(urlFile);
									audio.addEventListener("durationchange", function(){
										if (this.duration!=Infinity) {
											data.duration=this.duration*1000;
										}
									});
									audio.load();
									audio.currentTime = 24*60*60;
									audio.volume = 0;
									audio.play();
								}
							}
							var d=Math.round((proc*data.progress_width)/dur);
							//console.log('SOUND progress', dur, proc);
							data.progress_bar.css({width:d+'px',transition:''});
						},
						stop: function(pl){
							var data=this;
							//console.log('SOUND call END', data);
							if (data.progress_bar.is(':hidden')) {
								data.progress_bar.css({width:'0px',transition:'none'})
							} else {
								data.progress_bar.oneTransEnd(function(){
									data.progress_bar.css({width:'0px',transition:'none'})
								}).css({width:data.progress_width+'px'});
							}
							data.control_pause();

							//if (false) {
							// delete object to prevent error at second load of not exists file
							// reinit audio player because in ios no sound from moment when stop button clicked
							pl.destruct();
							imAudioPlayers[data.key]['player'] = null;
							imAudioPlayers[data.key]['isLoaderActive'] = false;
						},
						control_pause: function(){
							var data=this;
							//console.log('SOUND control pause', data);
							data.icon.removeClass('fa-pause').addClass('fa-play');
						},
						control_play: function(){
							var data=this;
							//console.log('SOUND control play', data);
							data.icon.removeClass('fa-play').addClass('fa-pause');
						},
						remove_loader: function(){
							var data=this;
							//console.log('SOUND remove loader', data);
							removeChildrenLoader(data.loader);
						}
					},
                    url: urlFile,
                    onload: function() {
						var data=this['data_player'];
						//console.log('SOUND onLoad', data);

                        // sometimes doesn't switch on first/second play
						data.control_play();
						data.remove_loader();
                        imAudioPlayers[key]['isLoaderActive'] = false;
                    },
					whileplaying: function(e) {
						var data=this['data_player'];
						data.progress_set(this);
					},
					onpause: function(e){
						var data=this['data_player'];
						data.control_pause();
					},
                    onstop: function(e){
						var data=this['data_player'];
						//console.log('SOUND onstop', data);
						data.stop(this);
					},
                    onfinish: function(e){
						var data=this['data_player'];
						//console.log('SOUND onfinish', data);
						data.stop(this);
					},
                    onerror: function(errorCode, description) {
                        imAudioPlayers[key]['player'].stop();
                    }
            })
        }

        imAudioPlayers[key]['player'].play();
    }

}

function openMessagesCityFromAppNotifications(uid, url){
    if(!checkLoginStatus())return false;
    if(typeof initCityPage == 'undefined'){//No page city
        redirectUrl(url);
        return false;
    }

    city.openMessagesCityFromAppNotifications(uid);

    return true;
}

function mobileAppSetBadgeNumber(number)
{
    if(typeof(MobileApp) !== 'undefined') {
        MobileApp.setBadgeNumber(number);
    }
}

function initAudioOldTmpl(){
    $(function(){
        var $plA=$('.player_audio').on('playing', function(){
            $plA.not(this).each(function(){
                if(!this.paused) this.pause();
            })
        })
    })
}

function detectWhenReCaptchaChallengeIsShown(){
    var targetElement = document.body,
        observerConfig = {
            childList: true,
            attributes: false,
            attributeOldValue: false,
            characterData: false,
            characterDataOldValue: false,
            subtree: false
        },
        $body=$('body, html'),
        $page=$('#cham-page'),
        offsetTop=0;

    function DOMChangeCallbackFunction(mutationRecords) {
        mutationRecords.forEach((mutationRecord) => {
            if (mutationRecord.addedNodes.length) {
                    var reCaptchaParentContainer = mutationRecord.addedNodes[0];
                    var reCaptchaIframe = reCaptchaParentContainer.querySelectorAll('iframe[title*="recaptcha"]');

                    if (reCaptchaIframe.length) {
                        var reCaptchaChallengeClosureObserver = new MutationObserver(function () {
                            if ((reCaptchaParentContainer.style.visibility === 'hidden')) {
                                if (offsetTop) {
                                    console.log('Recapcha hide');
                                    $body.css({height:''});
                                    $body.scrollTop(offsetTop);
                                    offsetTop=0;
                                }
                            } else {
                                var sT=Math.abs($page.offset().top);
                                if(sT){
                                    offsetTop=sT;
                                    console.log('Recapcha show');
                                    $body.css({height:'auto'});
                                    $body.scrollTop(offsetTop);
                                }
                            }
                        })
                        reCaptchaChallengeClosureObserver.observe(reCaptchaParentContainer, {
                            attributes: true,
                            attributeFilter: ['style']
                        })
                    }
            }
        })
    }

    var reCaptchaObserver = new MutationObserver(DOMChangeCallbackFunction);
    reCaptchaObserver.observe(targetElement, observerConfig);
}

/* Display block */
var _lockDisplay=false;

function requestWakeLock(unlock){
	if(typeof requestWakeLockScreen === 'function') {
		requestWakeLockScreen(unlock); // definition in the PHP class Common
	}
}

function lockDisplay(){
	requestWakeLock();
}

function unLockDisplay(){
	requestWakeLock(true);
}
/* Display block */

function inviteFriendsFacebook()
{
	var url = location.href.split('/')
	url.pop();
	url = url.join('/');

	if(typeof isAppAndroid !== 'undefined' && isAppAndroid && typeof facebookAppId !== 'undefined') {
		var facebookUrl = 'https://www.facebook.com/dialog/send?app_id=' + facebookAppId + '&link=' + url +  '&redirect_uri=' + url;
		navigator.app.loadUrl(facebookUrl, { openExternal: true });
	} else {
		FB.ui({
			method: 'send',
			link: url,
			display: 'popup'
		}, function(response){/* required by new SDK */});
	}
}

function isInitCityPage()
{
	return typeof initCityPage != 'undefined' && initCityPage;
}

function upgradeNotificationBalanceRefilled(credits, request_uri)
{
    console.log('upgradeNotificationBalanceRefilled', credits, JSON.stringify(request_uri));
	$(function(){
		setTimeout(function(){
            if(typeof tmplCurrent !== 'undefined') {
                switch(tmplCurrent) {
                    case 'urban_mobile':
                        showAlert(l('your_balance_is_refilled'), '.st-container', 'blue');
                        if(credits) {
                            $('em.credits').html(credits);
                        }
                        if(request_uri) {
                            window.location.href = request_uri;
                        }
                        break;
                    case 'impact_mobile':
                        var isPayPage = false;
                        var payPages = ['/upgrade', '/profile_boost', '/refill_credits'];
                        for(var payPage in payPages) {
                            if(window.location.pathname.indexOf(payPages[payPage]) !== -1) {
                                isPayPage = true;
                                break;
                            }
                        }
                        if(isPayPage) {
                            upgradePayment('{"status": 1, "data": {"type": "iap", "data": "", "type_features": "' + (credits ? 'credits' : 'membership') + '"}}');
                        } else {
                            showAlert(l('your_balance_is_refilled'));
                        }
                        break;
                    case 'edge':
                        if(credits) {
                            alertHtml(l('your_balance_is_refilled'), l('menu_refill_credits_edge'));
							$('.st_credits').html(l('credit_balance').replace('{credit}', credits));
							closePopupUpdate('pp_boost_ajax');
                        } else {
                            if(window.location.pathname.indexOf('/upgrade') >= 0) {
                                alertCustomRedirect('./', l('profile_upgraded'), l('menu_upgrade_edge'));
                            } else {
                                alertHtml(l('profile_upgraded'), l('menu_upgrade_edge'));
                            }
                        }
                        break;
                    default:
                        break;
                }
            }
		}, 100);
	});
}

function upgradeButtonRestoreLabel()
{
	$(function(){
        if(typeof tmplCurrent !== 'undefined') {
            switch(tmplCurrent) {
                case 'urban_mobile':
                    if(typeof idLoaderPaymentButton !== 'undefined') {
                        hideLoaderCl(idLoaderPaymentButton);
                        $('#perform_action_upgrade').html(l('continue'));
                    }
                    break;
                case 'impact_mobile':
                    $('#btn_action_upgrade').prop('disabled', false).removeLoader();
                    break;
                case 'edge':
                    $('.btn_submit_upgrade').prop('disabled', false).removeChildrenLoader();
                    $('#pp_payment_proceed_boost').prop('disabled', false).removeChildrenLoader();
                    break;
                default:
                    break;
            }
		}
	});
}

/* Init editor image */
var $ppImageEditor;
var imageEditorInfo = {};
function initEditorImage(tmpl){
	return;
	tmpl=tmpl||'admin_default';
	var _configEditor = {
		//colorScheme: 'light',
		elementId: 'fl_image_editor',
		language: 'en',
		tools : ['crop', 'adjust', 'effects', 'filters', 'rotate', 'text'],
		/*reduceBeforeEdit: {
			mode: 'manual',
			widthLimit: 2000,
			heightLimit: 2000
		},*/
		minCropAreaWidth: 217,
		minCropAreaHeight: 217,
		//isLowQualityPreview: true,
		//replaceCloseWithBackButton: true,
		showInModal: true,
		translations: {
			en: {
				'header.image_editor_title': l('editor_header_image_editor_title'),
				'header.toggle_fullscreen': l('editor_header_toggle_fullscreen'),
				'header.close': l('editor_header_close'),
				'header.close_modal': l('editor_header_close_modal'),
				'toolbar.download': l('editor_toolbar_download'),
				'toolbar.save': l('editor_toolbar_save'),
				'toolbar.apply': l('editor_toolbar_apply'),
				'toolbar.cancel': l('editor_toolbar_cancel'),
				'footer.undo': l('editor_footer_undo'),
				'footer.redo': l('editor_footer_redo'),
				'footer.reset': l('editor_footer_reset'),
				'warning.too_big_resolution': l('editor_warning_too_big_resolution'),
				'pre_resize.title': l('editor_pre_resize_title'),
				'pre_resize.keep_original_resolution': l('editor_pre_resize_keep_original_resolution'),
				'pre_resize.resize_n_continue': l('editor_pre_resize_resize_n_continue'),
				'toolbar.adjust': l('editor_toolbar_adjust'),
				'toolbar.effects': l('editor_toolbar_effects'),
				'toolbar.filters': l('editor_toolbar_filters'),
				'toolbar.orientation': l('editor_toolbar_orientation'),
				'toolbar.crop': l('editor_toolbar_crop'),
				'toolbar.resize': l('editor_toolbar_resize'),
				'toolbar.saveAsNewImage': l('editor_toolbar_save_as_new_image'),
				'toolbar.go_back': l('editor_toolbar_go_back'),
				'toolbar.focus_point': l('editor_toolbar_focus_point'),
				'toolbar.shapes': l('editor_toolbar_shapes'),
				'toolbar.image': l('editor_toolbar_image'),
				'toolbar.text': l('editor_toolbar_text'),
				'adjust.brightness': l('editor_adjust_brightness'),
				'adjust.contrast': l('editor_adjust_contrast'),
				'adjust.exposure': l('editor_adjust_exposure'),
				'adjust.saturation': l('editor_adjust_saturation'),
				'orientation.rotate_l': l('editor_orientation_rotate_left'),
				'orientation.rotate_r': l('editor_orientation_rotate_right'),
				'orientation.flip_h': l('editor_orientation_flip_horizontally'),
				'orientation.flip_v': l('editor_orientation_flip_vertically'),
				'spinner.label': l('editor_label_processing'),
				'common.width': l('editor_common_width'),
				'common.height': l('editor_common_height'),
				'common.custom': l('editor_common_custom'),
				'common.original': l('editor_common_original'),
				'common.square': l('editor_common_square'),
				'common.opacity': l('editor_common_opacity'),
			}
		}
	};

	/*
	'common.x': 'x',
	'common.y': 'y',
	'common.url': 'URL',
	'common.upload': 'Upload',
	'common.gallery': 'Gallery',
	'common.apply_watermark': 'Apply watermark',
	'toolbar.watermark': 'Watermark',
	*/
	var alertFn=function(msg, btn){
		if (tmpl == 'admin_default') {
			alert(msg);
		} else if(tmpl == 'edge'){
			alertCustom(msg, l('alert_html_alert'));
		} else if(tmpl == 'impact'){
			alertCustom(msg, false, l('alert_html_alert'));
		} else if(tmpl == 'impact_mobile'){
			showAlert(msg,false,'fa-info-circle');
		} else if(tmpl == 'urban_mobile'){
			showAlert(msg, '#st-container');
		} else {
			alert(msg);
		}
		btn=btn||[];
		if (btn[0]) {
			removeChildrenLoader(btn);
		}
	}
	var _callBackEditor = {
		onOpen: function(){
			//console.log('Editor image - Open');
		},
		onError: function(error){//triggered on having an error while uploading an image through filerobot or cloudimage.
			console.log('Editor image - Error', error);
			if (error == 'error_load_src') {
				alertFn(l('editor_error_loading_image'));
			}
			imageEditorInfo = {};
		},
		onBeforeComplete: function(data){
			//console.log('Editor image - BeforeComplete', data);
			//return false;
		},
		onComplete: function(file){
			if (file.operations.length) {//Update image
				var imageBase64=file.canvas.toDataURL(file.imageMime, 0.8),
					imageInfo=imageEditorInfo,
					pid=imageEditorInfo.id;
				if (imageInfo.btn[0] && imageInfo.type != 'edit_gallery') {
					addChildrenLoader(imageInfo.btn);
				} else if (imageInfo.btn[0]
							&& (tmpl == 'impact' || tmpl == 'urban' || tmpl == 'impact_mobile' || tmpl == 'urban_mobile')) {
					addChildrenLoader(imageInfo.btn);
				} else if (imageInfo.type == 'edit_list_image') {
					var $layer=$('#list_image_layer_action_'+pid);
					$layer.addClass('to_show').addChildrenLoader();
				}
				var url='ajax.php?cmd=photo_edit_image',
					data={
						photo_id: pid,
						uid: imageInfo.uid,
						image_edit: 1,
						image: imageBase64
					};
				if (tmpl == 'impact_mobile' || tmpl == 'urban_mobile') {
					url=url_ajax+'?cmd=photo_edit_image';
				}
				if (imageInfo.type == 'im_image') {/* Im image */
					url='ajax.php?cmd=upload_image_im_change';
					data['file_url'] = clMessages.fileImageUrl;
					clMessages.processUploadImage = true;
				} else if (imageInfo.type == 'comment_image') {
					url='ajax.php?cmd=upload_image_im_change';
					data['file_url'] = _addCommentImage[pid].file;
					if (imageInfo.file_th) {
						data['file_th'] = imageInfo.file_th;
					}
					_addCommentImage[pid].process=true;
				} else if (imageInfo.type == 'edit_gallery') {
					if (tmpl == 'edge') {
						clProfilePhoto.toggleShowLayerBlocked();
					}
				}

				var refreshImage = function(pid){
					clProfilePhoto.refreshImage(pid, true);
				}

				var updateGalleryImageAfterEdit = function(pid, imageInfo){
					if (tmpl == 'impact_mobile') {
						clPhoto.updateGalleryImageAfterEdit(pid, imageInfo);
					} else if (tmpl == 'urban_mobile') {
						photos.updateGalleryImageAfterEdit(pid, imageInfo);
					} else if (tmpl == 'impact' || tmpl == 'urban') {
						Photo.updateGalleryImageAfterEdit(pid, imageInfo);
					} else {
						clProfilePhoto.updateGalleryImageAfterEdit(pid, imageInfo);
						clProfilePhoto.toggleShowLayerBlocked('hide');
					}
				}

				$.ajax({url:url,
						type:'POST',
						data:data,
						beforeSend: function(){},
						success: function(res){
							if (imageInfo.btn[0]) {
								removeChildrenLoader(imageInfo.btn);
							} else if (imageInfo.type == 'edit_list_image') {
								$layer.removeClass('to_show').removeChildrenLoader();
							}

							if (imageInfo.type == 'im_image') {/* Im image */
								clMessages.processUploadImage=false;
								if (clMessages.isImageLoaded) {
									return;
								}
							} else if (imageInfo.type == 'comment_image') {
								_addCommentImage[pid].process=false;
								if (imageInfo.file_th) {
									clWall.setThumbPostImage(imageInfo.file_th);
								}
								return;
							}
							if (checkDataAjax(res)){
								var v=+new Date; v='?v='+v;
								if (imageInfo.type == 'admin') {
									var $img=$('#photo_'+pid);
									if ($img[0]) {
										var user_id=$img.attr('data-user-id'),
											size=$img.attr('data-photo-size'),
											src=url_files+'/photo/'+user_id+'_'+pid+'_'+size+'.jpg?'+Math.random();
										$img[0].src=src;
										if($img[0].complete)$img.load();
									}
								} else if (imageInfo.type == 'edit_list_image') {
									refreshImage(pid);
								} else if (imageInfo.type == 'edit_gallery') {
									updateGalleryImageAfterEdit(pid, imageInfo);
								} else {//Upload image
									var $img=imageInfo.img;
									if ($img[0]) {
										$img[0].src=imageInfo.preview+v;
										if($img[0].complete)$img.load();
									}
								}
								/* Face detector clear */
								if (imageInfo.type == 'admin'
									|| imageInfo.type == 'edit_list_image'
									|| imageInfo.type == 'edit_gallery') {
									faceDetecionClean(pid, file.operations, imageInfo.type);
								}
								/* Face detector clear */
							}else{
								alertFn(l('editor_error_refresh_image'));
							}
						},
						error: function(){
							if (imageInfo.type == 'im_image') {
								clMessages.processUploadImage = false;
								if (clMessages.isImageLoaded) {
									return;
								}
							} else if (imageInfo.type == 'comment_image') {
								_addCommentImage[pid].process=false;
								return;
							}
							alertFn(l('editor_error_refresh_image'),imageInfo.btn);
						}
				})
				console.log('Editor image - Complete', file.operations);
			} else {
				console.log('Editor image - Complete', file.operations);
			}
			imageEditorInfo = {};

			return false;
		},
		onClose: function(){
			imageEditorInfo = {};
			//console.log('Editor image - Close');
		}
	}
	$ppImageEditor = new FilerobotImageEditor(_configEditor, _callBackEditor);

	//Desctroy $ppImageEditor.unmount()
}

function openEditorImage(pid, uid, type, info, $btn, typeDefaultEffect){
	imageEditorInfo = {
		id: pid,
		uid: uid||0,
		preview: '',
		img: [],
		type: type||'admin',
		info: info||{},
		btn: $btn||[]
	}
	typeDefaultEffect=typeDefaultEffect||'';
	$ppImageEditor.open(urlMain+'get_img_editor.php?photo_id='+pid, typeDefaultEffect);
}
/* Init editor image */

/* Smiles */
function divSetCaretEnd(el) {
	//window.getSelection support https://developer.mozilla.org/ru/docs/Web/API/Window/getSelection
	if (window.getSelection) {
		var sel = window.getSelection(),
			range = document.createRange();
		range.selectNodeContents(el);
		range.collapse(false);
		sel.removeAllRanges();
		sel.addRange(range);
	}
}

function divRestoreCaretRange($el) {
    //$el.focus();
	var el=$el[0],
		caretRange=$el.data('caret-position') == undefined ? null : $el.data('caret-position');
    if (caretRange != null) {
        if (window.getSelection){
            var s = window.getSelection();
            if (s.rangeCount > 0){
				s.removeAllRanges();
			}
            s.addRange(caretRange);
        }/* else if (document.createRange){//non IE and no selection
            window.getSelection().addRange(caretRange);
        } else if (document.selection){//IE
            caretRange.select();
        }*/
    } else {
		divSetCaretEnd(el)
	}
}

function divGetCaretRange() {
	var range = null;
	if (window.getSelection) {//non IE Browsers
		var sel=window.getSelection();
		if (sel.rangeCount>0) {
			range=window.getSelection().getRangeAt(0);
		}
	}/* else if (document.selection) {//IE
		range=document.selection.createRange();
	}*/
	return range;
}

function divGetCharacterPositionCaret(el, before) {
	before=defaultFunctionParamValue(before, true);
    var char = "", sel, range, cRange;
    if (window.getSelection) {
        sel = window.getSelection();
        if (sel.rangeCount > 0) {
            range = sel.getRangeAt(0).cloneRange();
			range.collapse(true);
			if (before) {
				range.setStart(el, 0);
				char = range.toString().slice(-1);
			} else {
				range.setEndAfter(range.endContainer);
				char = range.toString().slice(0,1);
			}
        }
    }/* else if ((sel = document.selection) && sel.type != "Control") {
        range = sel.createRange();
        cRange = range.duplicate();
        cRange.moveToElementText(el);
        cRange.setEndPoint("EndToStart", range);
		char = cRange.text.slice(-1);
    }*/
    return char;
}


function smileInsertManual($smile, $input) {
	smileInsert($smile, $input)
}


function smileInsert($smile, $input) {
	if (typeof $input == 'undefined') {
		$input=$($smile.closest('.emoji_bl').data('input'));
	}

	divRestoreCaretRange($input);

	var	el=$input[0],
		beforeChar=divGetCharacterPositionCaret(el),
		afterChar=divGetCharacterPositionCaret(el,false),
		text=smileText(el.innerHTML);

	var isLSpace=true;
	if(beforeChar == "\u00A0" || beforeChar == " " || !beforeChar
		|| !text || text == $input.data('placeholder')){
		isLSpace=false
	}

	var isRSpace=true;
	if (afterChar == "\u00A0" || afterChar == " ") {
		isRSpace=false;
	}

	//console.log('BEFORE', isLSpace, beforeChar == "\u00A0", beforeChar == " ", beforeChar)
	//console.log('AFTER', isRSpace, afterChar == "\u00A0", afterChar == " ", afterChar);

	var id=$smile.data('id');

	var emoji = document.createElement('img');
		emoji.src=$smile.data('src');
		emoji.classList.add('emoji');
		emoji.dataset.alias='{emoji:'+id+'}';
		emoji.dataset.id=id;
		emoji.dataset.src=$smile.data('src');

	var fnInsertEmoji=function(){
		isLSpace && insertCaretDivContentEditable(el, 'space');
		insertCaretDivContentEditable(el, emoji);
		isRSpace && insertCaretDivContentEditable(el, 'space');
		fnChange();
	}
	if (window.getSelection) {
		var fnChange=$input.data('fn_change');
		if(typeof fnChange!='function')fnChange=function(){};
		sel = window.getSelection();
		if (sel.rangeCount) {
			range = sel.getRangeAt(0);
			if (range.commonAncestorContainer.parentNode == el) {
				fnInsertEmoji();
				return;
			}
		}

        divRestoreCaretRange($input);

		if (el.lastChild && el.lastChild.nodeName.toLowerCase() == "br") {
			el.removeChild(el.lastChild);
		}

		//el.focus();

		fnInsertEmoji();
	}
}

if (window.jQuery) {
    $doc.on('mousedown', '.emoji_bl > .emoji', function(e){
		e.preventDefault();
		smileInsert($(this));
		if (typeof isMobileSite != 'undefined' && isMobileSite) {
			smileBlockRemoveAllWall();
		}
	})
}

function smileTextPrepare(msg) {
	var $msgHtml=$('<div>').append(msg),
		emoji={},i=0;
	$msgHtml.find('.emoji').each(function(){
		var $el=$(this);
		emoji[++i]={alias:$el.data('alias'), id:$el.data('id'), src:$el.data('src')};
		$el.replaceWith('{emoji_indexed:'+i+'}');
	})
	msg=$msgHtml[0].innerHTML.replace(/\n|\t/g, '');
	msg=msg.replace(/&nbsp;/g, ' ');

	msg=trim(br2nl(msg));
	return {msg:msg, emoji:emoji}
}

function smileTextReplace(emoji, msg, msgTmpl) {
	for (var k in emoji) {
		var em=emoji[k];
		msg=msg.replace('{emoji_indexed:'+k+'}', em.alias);
		var rep='<span class="smile sm' + em.id + '"><img src="' + em.src + '" width="26" height="26" alt=""/></span>';
		msgTmpl=msgTmpl.replace('{emoji_indexed:'+k+'}', rep);
	}

	msgTmpl=msgTmpl.replace(/\n|\t/g, '');
	//msg=msg.replace(/\n/g, '');
	return {msg:msg, msgTmpl:msgTmpl}
}

function smileText(msg, noTrim) {
	noTrim=noTrim||false;
	var $msgHtml=$('<div>').append(msg);
	$msgHtml.find('.emoji').each(function(){
		var $el=$(this);
		$el.replaceWith($el.data('alias'));
	})
	var regex = /<(?!(\/\s*)?(br)[>,\s])([^>])*>/g;
	msg=msg.replace(regex, '');
	msg=br2nl($msgHtml[0].innerHTML);
	msg=msg.replace(/&nbsp;/g, ' ');
	if(!noTrim)	msg=trim(msg);
	return msg;
}

function inViewportEl(element, options) {
	var settings = {
		threshold  : 0,
		container  : []
	};
	if(options) {
		$.extend(settings, options);
	}
	if (!element.getBoundingClientRect) return true;
	settings.threshold>>=0;
    var elRect=element.getBoundingClientRect(), top=0, left=0,
        bottom=window.innerHeight||$window.height(), right=window.innerWidth||$window.width();
    if (settings.container) {
        var contRect=settings.container.getBoundingClientRect();
            top = Math.max(0, contRect.top);
            left = Math.max(0, contRect.left);
            bottom = Math.min(bottom, contRect.bottom);
            right = Math.min(right, contRect.right);
    }
    return top   < elRect.bottom + settings.threshold
           && left  < elRect.right + settings.threshold
           && bottom> elRect.top - settings.threshold
           && right > elRect.left - settings.threshold;
}

/* Stickers */
function stickerCollectionScroll($list, left) {
        var $liVisible=$('div.sticker_col:not(.to_hide)', $list).first();

        if (!$liVisible[0]) {
            return;
        }

        var d=$liVisible.eq(0)[0].offsetWidth,
            w=$list[0].offsetWidth,
            n=Math.round(w/d),
            scL=$list[0].scrollLeft;

        var fl=false, m=0, opt={container:$list[0]};
        $('div.sticker_col:not(.to_hide)', $list).each(function(){
            if (left) {
                if (!inViewportEl(this,opt)) {
                    m++;
                } else if (inViewportEl(this,opt)) {
                    return false;
                }
            } else {
                if (inViewportEl(this,opt)) {
                    fl=true;
                } else if (fl) {
                    m++;
                }
            }
        })
        m++;
		if (m>4) {
			m=4;
		}
        var sc=m*d;
        if(left){
           sc = scL - sc;
           if(sc<0)sc=0;
        }else{
           sc += scL;
        }
		var data=$list.data('el');
		data.left[sc>0?'removeClass':'addClass']('disabled');
		data.right[(sc+w) >= data.bl[0].offsetWidth?'addClass':'removeClass']('disabled');

        $list.animate({scrollLeft: sc}, 300, function(){
        })
}

var _stickerData={};
function stickerBlockShow(el, wall) {
	wall=wall||false;
	var $el=$(el),
		$bl=$el.data('stickerBl');

	if (wall) {
		if($el.is('.disabled'))return false;
		stickerBlockRemoveAllWall();
	} else {
		stickerBlockRemove($('#pp_message_sticker_btn'));
	}

	if($bl && $bl[0] && $bl.css('visibility') == 'visible'){
		stickerBlockRemove($el);
	/*if($bl && $bl[0] && (!wall || $bl.css('visibility') == 'visible')){
		if (!wall) {
			$bl[$bl.css('visibility') == 'hidden'?'addClass':'removeClass']('to_show');

		}*/
	}else{
		var $tmplBl=$jq('#stickers_bl_tmpl'),
			colActive=$tmplBl.data('colActive');

		/* Set collection last active */
		if (_stickerData['sticker_col'] === undefined) {
			_stickerData['sticker_col'] = $tmplBl.find('.sticker_col');
		}
		_stickerData['sticker_col'].removeClass('active');

		$tmplBl.find('.sticker_col[data-cid="'+colActive+'"]').addClass('active');

		if (_stickerData['stickers_list_wrap'] === undefined) {
			_stickerData['stickers_list_wrap'] = $tmplBl.find('.stickers_list_wrap');
		}
		_stickerData['stickers_list_wrap'].removeClass('to_show');
		_stickerData['stickers_list_wrap'].filter('.stickers_list_'+colActive).addClass('to_show');
		/* Set collection last active */

		if (wall) {
			var inp=$el.data('input'),
				$inp=$(inp),
				id=el.id;
				btn='#'+id;

			$(".stickers_bl_wall[data-btn='"+id+"']").remove();
			$bl=$jq('#stickers_bl_tmpl').clone()
			.attr('id', id+'_popup')
			.addClass('stickers_bl_wall')
			.data({'input':inp,'btn':btn})
			.appendTo($jq('body'))
			.position({
				my:'right bottom', at:'right-10 top-10',
				of:btn,	collision: 'fit flip'})
			.delay(10).toggleClass('to_show',0);

		} else {
			$('#pp_message_sticker_btn_popup').remove();

			$bl=$jq('#stickers_bl_tmpl').clone()
			.attr('id', el.id+'_popup').appendTo($el.data('append'))
			.delay(10).toggleClass('to_show',0);

		}

		/* Set collection last active */
		var $blCollectionList=$bl.find('.stickers_collection_wrap'),
			opt={container:$blCollectionList[0]},
			$colActive=$bl.find('.sticker_col[data-cid="'+colActive+'"]');
		if ($colActive[0]){
			//setTimeout(function(){
				if (!inViewportEl($colActive[0],opt)) {
					$blCollectionList.scrollTo($colActive,1,{axis:'x', onAfter:function(){
						var wL=this.offsetWidth,
							wL1=$('.stickers_collection_bl',this)[0].offsetWidth;
						$bl.find('.sticker_arrow:not(.left_scroll)')[(this.scrollLeft+wL)<wL1?'removeClass':'addClass']('disabled');
						$bl.find('.left_scroll')[this.scrollLeft?'removeClass':'addClass']('disabled');
					}})
				}
			//},1)
		}
		/* Set collection last active */

		$bl.find('img').on('dragstart',function(){return false});

		var $arrowRight=$bl.find('.sticker_arrow:not(.left_scroll)'),
			$arrowLeft=$bl.find('.left_scroll'),
			data={bl:$blCollectionList.find('.stickers_collection_bl'), left:$arrowLeft, right:$arrowRight};

		$blCollectionList.data('el', data);
		$arrowRight.on('click', function(){
			var $el=$(this);
			if($el.is('.disabled'))return false;
			stickerCollectionScroll($blCollectionList);
			return false;
		})

		$arrowLeft.on('click', function(){
			var $el=$(this);
			if($el.is('.disabled'))return false;
			stickerCollectionScroll($blCollectionList, true);
			return false;
		})

		var $collections=$blCollectionList.find('.sticker_col');
		$blCollectionList.find('.sticker_col').click(function(){
			var $el=$(this);
			if($el.is('.active'))return false;
			$collections.removeClass('active');
			$el.addClass('active');
			var cid=$el.data('cid');
			$jq('#stickers_bl_tmpl').data('colActive',cid);
			$bl.find('.stickers_list_wrap').removeClass('to_show');
			var $list=$bl.find('.stickers_list_'+cid).addClass('to_show');
			if (isMobile()) {

			} else {
				$list.data('plugin_tinyscrollbar').update();
			}
		})
		if (isMobile()) {

		} else {
			$bl.find('.stickers_list_wrap').tinyscrollbar({wheelSpeed:30,thumbSize:45})
		}

		//$bl.find('.stickers_list_bl').on('wheel', function(e){
			//e.stopPropagation();
		//})

		$bl.find('.sticker').click(function(){
			stickerBlockHide($el, function(){updatePopularSticker($bl, $st, aliase)});
			var $st=$(this), data=$st.data(), aliase=data.aliase,
				code='{sticker:'+data.cid+':'+data.img+'}';
			updateCountStickerBlock($bl, data.cid, data.id);

			var data={code:code, html:getStickerHtml(data.src), data:{id:data.id, cid:data.cid}};
			if (wall) {
				$inp.data('sticker', data);
				$inp.nextAll('.comment_action').find('.wall_post_send, .btn_comment_send').click();
			} else {
				clMessages.send(data);
			}
			return false;
		})

		$el.data('stickerBl', $bl);
	}
}

var stickerMaxPopular=12;
function updatePopularSticker($bl, $st, aliase){
	updatePopularStickerOneBlock($st, aliase, $bl);
	updatePopularStickerOneBlock($st, aliase, $jq('#stickers_bl_tmpl'));
}

function updatePopularStickerOneBlock($st, aliase, $list) {
	var $list0=$st.closest('.stickers_list_0'),
		$listPopular=$list.find('.stickers_list_0 .stickers_list_bl'),
		$stTmpl=$listPopular.find('.sticker[data-aliase="' + aliase + '"]');

	$list.closest('.stickers_bl').find('.sticker_col_0').removeClass('to_hide');

	if ($list0[0] || $stTmpl[0]) {
		$listPopular.prepend($listPopular.find('.sticker[data-aliase="' + aliase + '"]'));
	} else {
		$listPopular.prepend($st.clone());
	}

	var $listPopularStickers=$listPopular.find('.sticker'),
		l=$listPopularStickers.length,
		stickerMaxPopular=siteOptions['edge_stickers']['number_popular_show']*1;
	if (l>stickerMaxPopular) {
		$listPopular.find('.sticker').last().remove();
	}
}

function updateCountStickerBlock($bl, cid, id) {
	updateCountStickerOneBlock($jq('#stickers_bl_tmpl').data('cid', cid).addClass('reload'), cid, id);
	updateCountStickerOneBlock($bl.data('cid', cid).addClass('reload'), cid, id);
}

function updateCountStickerOneBlock($bl, cid, id) {
	updateCountStickerEl($bl.find('.sticker_col[data-cid="'+cid+'"]'))
	updateCountStickerEl($bl.find('.sticker[data-id="'+id+'"]'));
}

function updateCountStickerEl($bl) {
	var c=$bl.data('count')*1+1;
	$bl.attr('data-count',c).data('count',c);
}

function updateOrderStickerBlock($bl) {
	if(!$bl.is('.reload')) return;
	$bl.removeClass('reload');

	var cid=$bl.data('cid'),
		$tmpl=$jq('#stickers_bl_tmpl');
	updateOrderStickerOneBlock($tmpl.find('.sticker_col'), $tmpl.find('.stickers_collection_bl'));
	updateOrderStickerOneBlock($bl.find('.sticker_col'), $bl.find('.stickers_collection_bl'));

	var sel='.stickers_list_'+cid;
	updateOrderStickerOneBlock($tmpl.find(sel).find('.sticker'), $tmpl.find(sel).find('.stickers_list_bl'));
	updateOrderStickerOneBlock($bl.find(sel).find('.sticker'), $bl.find(sel).find('.stickers_list_bl'));
}

function updateOrderStickerOneBlock($col, $list) {
	$col.detach().sort(function(el1, el2) {
		var d1=$(el1).data('count'), d2=$(el2).data('count');
		if(d1 < d2) {
			return 1;
		}
		if(d1 > d2) {
			return -1;
		}
		return 0;
	}).appendTo($list);
}

function stickerBlockRemove($el) {
	var $bl=$el.data('stickerBl');
	if($bl && $bl[0]){
		$bl.oneTransEnd(function(){
			$bl.remove();
		}).removeClass('to_show');
		$el.data('stickerBl', []);
	}
}

function stickerBlockHide($el, fn) {
	var $bl=$el.data('stickerBl');
	if($bl && $bl[0]){
		$bl.oneTransEnd(function(){
			updateOrderStickerBlock($bl);
			if(typeof fn=='function')fn();
		}).removeClass('to_show')
	}
}

function getStickerHtml(src) {
	var sticker='<span class="sticker_one">'+
					'<img class="nocontextmenu" src="' + src + '" width="90" height="90" alt=""/>'+
				'</span>';
	return sticker;

}

function stickerBlockShowWall(el) {
	stickerBlockShow(el, true)
}

function stickerBlockRemoveWall($el) {
	if(!$el[0])return;
	var $bl=$el.data('stickerBl');
	if ($bl && $bl[0]) {
		$bl.oneTransEnd(function(){
			$bl.remove();
		}).removeClass('to_show');
		$el.data('stickerBl', []);
	}
}

function stickerBlockRemoveAllWall() {
	$('.stickers_bl_wall:visible').each(function(){
		var $bl=$(this);
		$bl.oneTransEnd(function(){
			$bl.remove();
		}).removeClass('to_show');
		$($bl.data('btn')).data('stickerBl', []);
	})
}

function stickerBlockHideTarget($targ) {
	if (!$targ.is('.stickers_bl')
		&& !$targ.closest('.stickers_bl')[0]
		&& !$targ.is('.comment_sticker_btn')
		&& !$targ.closest('.comment_sticker_btn')[0]
	) {
		stickerBlockRemoveAllWall()
	}
}

function stickerBlockOnResize() {
	$('.stickers_bl_wall:visible').each(function(){
		var $bl=$(this);
		$bl.position({
			my:'right bottom',
			at:'right-10 top-10',
			of:$bl.data('btn'),
			collision: 'fit flip'
		})
	})
}
/* Stickers */

function smileBlockShow(el) {
	var $el=$(el),
		$bl=$el.data('smileBl');
	if($bl && $bl[0]){
		$bl[$bl.css('visibility') == 'hidden'?'addClass':'removeClass']('to_show');
	}else{
		$bl=$jq('#smile_bl_tmpl').clone()
		.attr('id', 'pp_messages_smile_bl')
		.data('input', $el.data('input'))
		.appendTo($el.data('append')).delay(10).toggleClass('to_show',0);
		$el.data('smileBl', $bl);
	}
}

function smileBlockHide($el) {
	var $bl=$el.data('smileBl');
	if($bl && $bl[0]){
		$bl.removeClass('to_show')
	}
}

function smileBlockShowWall(el) {
	var $el=$(el),
		$bl=$el.data('smileBl');

	if($el.is('.disabled'))return false;
	smileBlockRemoveAllWall();

	if($bl && $bl[0] && $bl.css('visibility') == 'visible'){
		//smileBlockRemoveWall($el)
	}else{
		var inp=$el.data('input'),
			id=$el[0].id,
			btn='#'+id;
		$(".emoji_bl_wall[data-btn='"+id+"']").remove();
		$bl=$jq('#smile_bl_tmpl').clone()
			.attr('id', '')
			.addClass('emoji_bl_wall')
			.data({'input':inp,'btn':btn})
			.appendTo($jq('body'))
			.position({
				my:'right bottom',
				at:'right-10 top-10',
				of:btn,
				collision: 'fit flip'})
			.delay(10).toggleClass('to_show',0);

		$el.data('smileBl', $bl);
	}
}

function smileBlockRemoveWall($el) {
	if(!$el[0])return;
	var $bl=$el.data('smileBl');
	if ($bl && $bl[0]) {
		$bl.oneTransEnd(function(){
			$bl.remove();
		}).removeClass('to_show');
		$el.data('smileBl', []);
	}
}

function smileBlockRemoveAllWall() {
	$('.emoji_bl_wall:visible').each(function(){
		var $bl=$(this);
		$bl.oneTransEnd(function(){
			$bl.remove();
		}).removeClass('to_show');
		$($bl.data('btn')).data('smileBl', []);
	})
}

function smileBlockHideTarget($targ) {
	if (!$targ.is('.emoji_bl')
		&& !$targ.closest('.emoji_bl')[0]
		&& !$targ.is('.wall_comment_smile_btn')
		&& !$targ.closest('.wall_comment_smile_btn')[0]
	) {
		smileBlockRemoveAllWall()
	}
}

function smileBlockOnResize() {
	$('.emoji_bl_wall:visible').each(function(){
		var $bl=$(this);
		$bl.position({
			my:'right bottom',
			at:'right-10 top-10',
			of:$bl.data('btn'),
			collision: 'fit flip'
		});
	})
}
/* Smiles */

function initAutoSize($inp,fn) {
	if($inp.is("[contenteditable='true']")){
		$inp.autosize_editable({
			isSetScrollHeight:false,
			callback:function(){},
			callbackSend:fn
		})
	} else {
		$inp.autosize({isSetScrollHeight:false,callback:function(){}}).keydown(doOnEnter(fn))
	}
}

function triggerAutoSize($inp,fn,empty) {
	if(typeof fn!='function'){
		fn=function(){};
	}
	if(empty||false){
		$inp.text('').trigger('autosize',fn)
	} else {
		$inp.trigger('autosize',fn)
	}
}

/* Audio visualizer */
var _audioVisualCount=16,
	_audioVisualRender={};
function createAudioVisualizerElement(id, $sel){
	var $sel=$sel||[],$visualMainElement;
	if($sel[0]){
		$visualMainElement=$sel;
	} else {
		$visualMainElement=$('#'+id);
	}

	var $span=$visualMainElement.find('span');
	if($span[0]){
		_audioVisualRender[id]={el:$span};
		return;
	};

	var i;
	for(i=0; i<_audioVisualCount; ++i){
		$visualMainElement.append($('<span>'));
	}

	_audioVisualRender[id]={
		el:$visualMainElement.find('span'),
		prevValues: false,
		noiseCount: 0
	};

};

function initAudioVisualizer(id, stream){
	var audioContext = new AudioContext();
	_audioVisualRender[id].ctx=audioContext;
	//console.log(_audioVisualRender[id]);
	var $visualMainElement=_audioVisualRender[id].el,
		analyser = audioContext.createAnalyser(),
		source = audioContext.createMediaStreamSource(stream);

	source.connect(analyser);
    analyser.smoothingTimeConstant=0.5;
    analyser.fftSize=32;
    renderLoopAudioVisualizer(id, analyser);
}

function processAudioVisualizer(id, $visualMainElement, data){
	var dataMap={0:15, 1:10, 2:8, 3:9, 4:6, 5:5, 6:2, 7:1, 8:0, 9:4, 10:3, 11:7, 12:11, 13:12, 14:13, 15:14},
		values=Object.values(data), i, val, elmStyles,
		noise=0;

	/* Emulate */
	for(i=0; i<_audioVisualCount; ++i){
		noise +=values[i];
	}
	if (noise) {
		_audioVisualRender[id].noiseCount = 0;
	} else {
		var valuesRandom=[
				[221, 225, 214, 184, 165, 144, 116, 88, 72, 61, 71, 65, 37, 33, 8, 10],
				[255, 250, 217, 171, 150, 125, 106, 89, 79, 50, 63, 64, 43, 36, 26, 17],
				[251, 238, 202, 159, 151, 136, 107, 93, 84, 69, 60, 57, 44, 35, 16, 13],
				//[255, 242, 198, 141, 133, 116, 106, 97, 85, 61, 67, 66, 40, 23, 18, 21],
				[255, 239, 192, 154, 114, 104, 98, 85, 77, 64, 61, 58, 43, 38, 33, 21],
				[255, 255, 206, 144, 111, 116, 102, 86, 87, 77, 71, 66, 46, 36, 37, 30],
				//[255, 251, 202, 131, 98, 102, 89, 103, 88, 84, 83, 72, 55, 27, 36, 28],
				[255, 252, 209, 140, 127, 131, 119, 109, 98, 84, 72, 58, 37, 24, 28, 9],
				//[255, 248, 211, 147, 133, 126, 103, 92, 92, 77, 67, 63, 50, 37, 28, 10],
		]

		var valuesRandom=[
				[109, 125, 107, 84, 85, 79, 76, 68, 57, 46, 41, 55, 35, 23, 18, 18],
				[99, 115, 97, 74, 75, 69, 66, 58, 67, 56, 51, 65, 25, 13, 8, 8],
				[119, 135, 117, 94, 95, 89, 86, 78, 67, 56, 51, 65, 45, 33, 28, 28],
				[114, 130, 113, 89, 90, 84, 81, 73, 62, 51, 46, 60, 40, 28, 23, 23],
				[94, 110, 97, 69, 70, 64, 61, 53, 42, 31, 26, 40, 20, 8, 3, 3],
				[100, 115, 100, 74, 55, 69, 46, 58, 47, 36, 31, 25, 35, 13, 28, 28],
		]

		var valuesRandom=[
				[94, 110, 97, 69, 70, 64, 61, 53, 42, 31, 26, 40, 20, 8, 3, 3],
				[100, 115, 100, 74, 55, 69, 46, 58, 47, 36, 31, 25, 35, 13, 28, 28],
		]//n=10 *

		var valuesRandom=[
				[119, 135, 117, 94, 95, 89, 86, 78, 67, 56, 51, 65, 45, 33, 28, 28],
				[114, 130, 113, 89, 90, 84, 81, 73, 62, 51, 46, 60, 40, 28, 23, 23]
		]//n=8 **

		var valuesRandom1=[
				[119, 135, 117, 94, 95, 89, 86, 78, 67, 56, 51, 65, 45, 33, 28, 28],
				[109, 125, 107, 84, 85, 79, 76, 68, 57, 46, 41, 55, 35, 23, 18, 18],
				[114, 130, 113, 89, 90, 84, 81, 73, 62, 51, 46, 60, 40, 28, 23, 23]
		]//n=8

		if (_audioVisualRender[id].noiseCount >= 8 || !_audioVisualRender[id].noiseCount
				|| _audioVisualRender[id].prevValues === false) {
			_audioVisualRender[id].noiseCount = 0;

			var i=getRandomInt(0,1);
			values=valuesRandom[i];
		} else {
			values= _audioVisualRender[id].prevValues;
		}
		_audioVisualRender[id].prevValues = values;
		_audioVisualRender[id].noiseCount++;
	}
	/* Emulate */

    for(i=0; i<_audioVisualCount; ++i){
		val=values[dataMap[i]]/255;
		elmStyles = $visualMainElement.eq(i)[0].style;
		elmStyles.transform = 'scaleY('+val+')';
		elmStyles.opacity = Math.max(.25,val);
    }
}

function renderLoopAudioVisualizer(id, analyser){
	var $visualMainElement=_audioVisualRender[id].el,
		frequencyData = new Uint8Array(analyser.frequencyBinCount);
    var renderFrame = function(){
		if(typeof _audioVisualRender[id]=='undefined'||!_audioVisualRender[id].el)return;
		//console.log('RENDER');
		analyser.getByteFrequencyData(frequencyData);
		processAudioVisualizer(id, $visualMainElement, frequencyData);
		requestAnimationFrame(renderFrame);
    };
    requestAnimationFrame(renderFrame);
}

function stopAudioVisualizer(id){
	_audioVisualRender[id].ctx.close();
	delete _audioVisualRender[id];
}
/* Audio visualizer */

/* Face detector */
/* Workers */
function faceDetecionGetConfig() {
	var inputSize=parseInt(getSiteOption('face_input_size'));
	if(!inputSize)inputSize=512;
	var scoreThreshold=parseFloat(getSiteOption('face_score_threshold')/10);//0.3 - https://sitesman.com/s/1014/12-11-2021_17-01-24.png
	if(!scoreThreshold)scoreThreshold=0.4;

	return {inputSize:inputSize, scoreThreshold:scoreThreshold};
}

var _faceApiWorker = false,
	_faceWindow = {};
function faceDetecionReInitBrowser(){
	_faceApiWorker.terminate();
	_isFaceDetectionLoad = false;
	initFaceDetectionBrowser();
	_faceApiWorker = false;
}

function faceDetecionWorkerInit() {
	if(!isFaceDetectionEnabled())return false;
	if (window.Worker) {
		if(_faceApiWorker !== false)return;
		_isFaceDetectionLoad = true;
		/*_faceWindow = {};
		Object.getOwnPropertyNames(window).forEach(name => {
			try {
				if (typeof window[name] !== 'function'){
					if (typeof window[name] !== 'object' &&
						name !== 'undefined' &&
						name !== 'NaN' &&
						name !== 'Infinity' &&
						name !== 'event' &&
						name !== 'name'
					) {
						_faceWindow[name] = window[name];
					}
				}
			} catch (ex){
				console.log('Access denied for a window property');
			}
		});*/

		_faceApiWorker = new Worker(urlFaceApi + 'face-api-worker.js');

		_faceApiWorker.onmessage = function(e){
			try {
				var data=e.data
				if (data.type && data.type == 'web_worker_data') {
					var type=data.type, data=data.data, cmd=data.cmd;
					console.log('%cANSWER FROM WEB WORKERS: - ' + cmd, 'background:#97dab0', data);
					switch (cmd) {
						case 'init':
							//faceDetecionWorkerSendMsg('init', {pid:0});
							faceDetecionWorkerCheckImage(url_tmpl+'common/images/1px.png', 0);
						break;
						case 'not_support':
							faceDetecionReInitBrowser();
						break;
						case 'detect_faces':
							if (data.faces && data.pid) {
								var pid=data.pid,
									faceDetection=data.faces;
									console.log('%cWEB WORKERS detect_faces:', 'background:#97dab0', faceDetection);
								if ($.isEmptyObject(faceDetection)) {
									faceDetection={};
								} else {
									if(tmplCurrent == 'edge'){
										clProfilePhoto.darwFace(pid, faceDetection);
									}
								}
								saveDataFaceDetection(pid, faceDetection, true);
							}
						break;
					}
				}
			} catch(e) {
				console.log('%cWEB WORKERS ERROR: ', 'background:red', e);
				faceDetecionReInitBrowser();
				return false;
			}
		}

		faceDetecionWorkerSendMsg('init', {pid:0});//,wnd:_faceWindow

		window.onbeforeunload = function(){
			if(_faceApiWorker) {
				_faceApiWorker.terminate();
			}
		}

		return true;
	} else {
		initFaceDetectionBrowser();
	}
	return false;
}

function faceDetecionWorkerSendMsg(type, data) {
	var options={
		guid: clProfilePhoto.guid,
		tmplCurrent: tmplCurrent,
		urlFaceApi: urlFaceApiWork+'_server/js/face_api/',
		config: faceDetecionGetConfig()
	};
	data = $.extend({}, options, data);
	_faceApiWorker.postMessage({
		type: type,
		data: data
	})
}

function faceDetecionWorkerCheckImage(src, pid) {
	var w, h;
	var $img=$('<img/>').on('load',function(){
		w = this.width; h = this.height;
		createImageBitmap(this, 0, 0, w, h).then(bitmap=>{
			faceDetecionWorkerSendMsg('detect_faces', {bitmap:bitmap, width:w, height:h, pid:pid});
		})
	}).prop('src',src);
	if($img[0].complete) $img.load()
}
/* Workers */
function isFaceDetectionEnabled(){
	return isSiteOptionActive('gallery_photo_face_detection', 'edge_gallery_settings');
}
function isFaceDetectionModelLoaded(){
	return !!faceapi.nets.tinyFaceDetector.params
}

async function initFaceDetectionBrowser(){
	if(!isFaceDetectionEnabled())return;
	_startFaceDetection=new Date().getTime();
	if (!isFaceDetectionModelLoaded()){
		await faceapi.nets.tinyFaceDetector.load('/');
	}
	_endFaceDetection = new Date().getTime();

	debugLog('initFaceDetection TIME:', (_endFaceDetection - _startFaceDetection)+'ms', '#62da02');
}

var _faceDetectorOptions=false,
	_isFaceDetectionLoad=false,
	_startFaceDetection=0,
	_endFaceDetection=0;
function getFaceDetectorOptions(){
	if(_faceDetectorOptions!== false)return _faceDetectorOptions;
	var config=faceDetecionGetConfig();
	_faceDetectorOptions=new faceapi.TinyFaceDetectorOptions(config);
	return _faceDetectorOptions;
}

async function checkImageFaceDetectionInit(src, pid){
	if(!isFaceDetectionEnabled())return;
	var img=$('<img src="'+src+'">')[0],
		options=getFaceDetectorOptions();

	var faceDetection = await faceapi.detectAllFaces(img, options);

	_endFaceDetection = new Date().getTime();

	debugLog('checkImageFaceDetection TIME:', (_endFaceDetection - _startFaceDetection)+'ms', '#0dd9ab');
	if (faceDetection) {
		if (faceDetection.length > 1) {
			if(tmplCurrent == 'edge'){
				clProfilePhoto.darwFace(pid, prepareDataFaceDetection(pid, faceDetection));
			}
		} else {
			faceDetection={};
		}
		saveDataFaceDetection(pid, faceDetection);
	}

	var isNoLoaded=!_isFaceDetectionLoad && clProfilePhoto.isShowGallery;
	if(isNoLoaded){
		clProfilePhoto.toggleShowLayerBlocked('hide')
	}
	_isFaceDetectionLoad=true;
}

function faceDetecionClean(pid, operations, type){
	if(!isFaceDetectionEnabled())return;
	operations=operations.operations;
	var isClearFaceDetection=false;
	for (var i in operations) {
		var operation='';
		if(operations[i].operation!=undefined){
			operation=operations[i].operation;
		}
		if (operation == 'crop' || operation == 'rotate') {
			isClearFaceDetection=true;
			break;
		}
	}
	if(!isClearFaceDetection)return;

	faceDetecionCleanRequest(pid, type);
}

function faceDetecionCleanRequest(pid, type){
	type=type||'edit_gallery';
	$.post(url_ajax+'?cmd=face_detect_clear_friend_all',{pid:pid},
	function(res){
		var data=checkDataAjax(res);
		if(tmplCurrent == 'edge'){
			clProfilePhoto.friendFaceAllClear(pid, data, type);
		}
	})
}

function intersectionBoxFaceDetection(box1, box2, i, m){
	var sB=(box1.height*box1.width)/2;
	//(X1, Y1) - coordinates of the bottom left corner of the first rectangle
	var p1={'y':box1.top*1+box1.height*1, 'x':box1.left*1};
	//(X2, Y2) - coordinates of the top right corner of the first rectangle
	var p2={'y':box1.top*1, 'x':box1.left*1+box1.width*1};
	//(X3, Y3) - coordinates of the bottom left corner of the first rectangle
	var p3={'y':box2.top*1+box2.height*1, 'x':box2.left*1};
	//(X4, Y4) - coordinates of the top right corner of the first rectangle
	var p4={'y':box2.top*1, 'x':box2.left*1+box2.width*1};

	var left=Math.max(p1.x, p3.x),
		right=Math.min(p2.x, p4.x),
		top=Math.min(p1.y, p3.y),
		bottom=Math.max(p2.y, p4.y);

	var width=right-left,
		height=top-bottom,
		s=0, res={intersect:0, s:0};

	if (width<=0 || height<=0) {
		return res;
	}

	s=width*height;
	res.s=s;
	if (s > sB) {
		res.intersect=1;
	}
	return res;
}

function checkIntersectionBoxFaceDetection(faceData){
	if(!isFaceDetectionEnabled())return faceData;
	if (typeof faceData.face == 'undefined') {
		return faceData;
	}

	var face=faceData.face;
	for (var i in face) {
		var box=face[i];
		for (var m in face) {
			var box1=face[m];
			if (m>i) {
				var res=intersectionBoxFaceDetection(box, box1, i, m);
				if(face[m]['intersect']==undefined){
					face[m].intersect = res.intersect;
				} else if (res.intersect) {
					face[m].intersect = res.intersect;
				}
				if (res.s>0) {
					face[m].s1 -= res.s;
					face[i].s1 -= res.s;
				}
			}
		}
	}

	var faceTemp=[],k=0;
	for (var i in face) {
		var box=face[i], s=box.s*1/4.5, s1=box.s1*1;
		if(box.intersect*1 || s > s1){
			//delete face[i];
			//console.log(box.intersect, i);
		} else {
			faceTemp[k]=face[i];
			k++;
		}
	}

	faceData['face'] = faceTemp;
	return faceData;
}

function prepareDataFaceDetection(pid, faceDetection){
	var faceDetectionPrepare={};
	if (faceDetection.length > 1) {
		faceDetectionPrepare = {
			image : {
				width : faceDetection[0]._imageDims._width,
				height : faceDetection[0]._imageDims._height,
				photo_id : pid,
				photo_user_id : clProfilePhoto.guid
			},
			face : []
		}

		for (var i in faceDetection) {
			var box=faceDetection[i]._box,
				s=box._width*box._height,
				data={
					left:box.left,
					top:box.top,
					width:box._width,
					height:box._height,
					s:s,
					s1:s
				};
			faceDetectionPrepare['face'][i] = data;
		}
		faceDetectionPrepare['face'].sort(function(a,b){return a.left - b.left;});
	}
	return faceDetectionPrepare;
}

function saveDataFaceDetection(pid, faceDetection, noPrepare){
	console.log('SAVE FACE DETECTION', pid, faceDetection);
	noPrepare=noPrepare||false;
	if (noPrepare) {
		var faceDetectionPrepare=faceDetection;
	} else {
		var faceDetectionPrepare=prepareDataFaceDetection(pid, faceDetection);
	}

	faceDetectionPrepare=checkIntersectionBoxFaceDetection(faceDetectionPrepare);

	if(tmplCurrent == 'edge'){
		clProfilePhoto.updateDataFace(pid, faceDetectionPrepare, '');
	}
	$.post(url_ajax+'?cmd=face_detect_data_save', {pid:pid,data:faceDetectionPrepare},
		function(res){
            var data=checkDataAjax(res);
			if(data){
				if (data && data!='none') {
					data=JSON.parse(data);
					if (typeof data!='object') {
						data='';
					}
				}
			} else {
				data='';
			}
			if(tmplCurrent == 'edge'){
				clProfilePhoto.updateDataFace(pid, data, '');
			}
	})
	return faceDetectionPrepare;
}

function checkImageFaceDetection(src, pid){
	if(_faceApiWorker === false){
		checkImageFaceDetectionBrowser(src, pid)
	} else {
		faceDetecionWorkerCheckImage(src, pid)
	}
}

async function checkImageFaceDetectionBrowser(src, pid){
	_startFaceDetection=new Date().getTime();
	if (!isFaceDetectionModelLoaded())return;

	var isNoLoaded=!_isFaceDetectionLoad && clProfilePhoto.isShowGallery;
	if (isNoLoaded) {
		await clProfilePhoto.toggleShowLayerBlocked('show');
	}
	checkImageFaceDetectionInit(src, pid);
}
/* Face detector */

/* Record audio message */
var mediaRecorderProcess={},
	mediaRecorderDetect={},
	mediaRecorderStream={},
	mediaRecorderApi={},
	mediaRecorderPlayer={},
	mediaRecorderPlayerBtn={},
	mediaRecorderControl={};

function hideAudioRecorderControl(bl){
	mediaRecorderControl[bl].ppAudioRecord.remove();
	mediaRecorderControl[bl].textarea.removeClass('im_audio_message_enabled');
}

function audioMessageInputAutosizeDelay($textarea){
	setTimeout(function(){
		var fnChange=$textarea.data('fn_change');
		if(typeof fnChange=='function')fnChange();
		$textarea.trigger('autosize');
	},200)
}

function audioMessageDelete(id,bl){
	var $ppAudioRecord=mediaRecorderControl[bl].ppAudioRecord,
		$microphone=mediaRecorderControl[bl].microphone,
		$textarea=mediaRecorderControl[bl].textarea;

		$textarea
		.attr('data-im-audio-message-id', 0)
		.data('im-audio-message-id', 0);
		$ppAudioRecord.addClass('disabled').removeClass('im_audio_message_delete');

		mediaRecorderPlayer[bl].oneTransEnd(function(){
			audioMessageInputAutosizeDelay($textarea)
		}).removeClass('im_audio_message_delete');

		addChildrenLoader($microphone);
		mediaRecorderProcess[bl]=false;

		$.post('ajax.php', {cmd:'im_audio_message_delete', id:id}, function(res){
			var data=checkDataAjax(res);
            if(data!==false){
				setTimeout(function(){
					removeChildrenLoader($microphone);
					mediaRecorderPlayerBtn[bl].data('audio-message-file', '');
					mediaRecorderPlayer[bl].remove();
					$microphone.attr('title', l('record_audio_message'));
					audioMessageInputAutosizeDelay($textarea);
				},200)
			} else {
				removeChildrenLoader($microphone);
				$textarea.attr('data-im-audio-message-id', id).data('im-audio-message-id', id);
				$ppAudioRecord.addClass('im_audio_message_delete');
				mediaRecorderPlayer[bl].oneTransEnd(function(){
					audioMessageInputAutosizeDelay($textarea)
				}).addClass('im_audio_message_delete');
				alertCustom(l('something_went_wrong_please_try_later'));
			}
			$ppAudioRecord.removeClass('disabled');
		})
}

function stopTrakAudioRecorder(bl){
	//$this.mediaRecorderStream.getTracks().forEach(function(track) {
		//track.stop()
	//})

	mediaRecorderStream[bl] && mediaRecorderStream[bl].getAudioTracks()[0].stop();
}

function pushChankAudioRecorder(e,bl){
	var mediaRecorderChunks = e.data,
		formData = new FormData(),
		url=url_ajax+'?cmd=save_im_audio_message',
		$ppAudioRecord=mediaRecorderControl[bl].ppAudioRecord,
		$microphone=mediaRecorderControl[bl].microphone,
		$textarea=mediaRecorderControl[bl].textarea;

		formData.append('im_msg_audio_blob', mediaRecorderChunks);

		var xhr = new XMLHttpRequest();
        xhr.open("POST", url);
        xhr.onreadystatechange = function() {
			if (xhr.readyState == 4) {
				if(xhr.status == 200) {
					var data=xhr.responseText, isErrorResponse = true,
						error=l('something_went_wrong_please_try_later');

					mediaRecorderProcess[bl]=false;
					removeChildrenLoader($microphone);
					$ppAudioRecord.removeClass('disabled');

                    data=checkDataAjax(data);
                    if(data) {
                        if(data.result == 'success' && data.id) {
							$textarea
							.attr('data-im-audio-message-id', data.id)
							.data('im-audio-message-id', data.id);

							var v=trim($textarea.text());
							if(v==$textarea.data('placeholder')&&!$textarea.find('.emoji')[0]){
								$textarea.text('');
							}

							if (mediaRecorderPlayer[bl]!=undefined && mediaRecorderPlayer[bl][0]) {
								mediaRecorderPlayer[bl].remove();
								delete mediaRecorderPlayer[bl];
							}
							mediaRecorderPlayer[bl]=$('#pl_message_send_play').clone()
								.show()
								.attr({'contenteditable':false, id:bl+'_player'});

							mediaRecorderPlayer[bl].find('.fa-times').click(function(){
								runAudioRecorder(bl)
							})

							mediaRecorderPlayerBtn[bl]=mediaRecorderPlayer[bl]
														.find('.im_audio_message_loader')
														.data('audio-message-file', data.url);

							$textarea.prepend(mediaRecorderPlayer[bl].delay(20).oneTransEnd(function(){
								audioMessageInputAutosizeDelay($textarea)
							}).toggleClass('im_audio_message_delete',0));

							$ppAudioRecord.addClass('im_audio_message_delete');
							$microphone.attr('title', l('record_audio_message_delete'));
                            isErrorResponse=false;
						} else if (data.error) {
							error=data.error;
						}
                    }

					if(isErrorResponse) {
						$microphone.attr('title', l('record_audio_message'));
						alertCustom(error);
					}
				}
			}
        };
        xhr.send(formData);
}

function runAudioRecorder(bl){
	var $bl=$(bl);
	if (!$bl[0] || $bl.is('.disabled'))return;
	var $ppAudioRecord=$bl.find('.im_audio_message_recorder');
	if (!$ppAudioRecord[0] || $ppAudioRecord.is('.disabled'))return;

	var $microphone=$ppAudioRecord.find('.im_audio_message_recorder_icon_bl'),
		$textarea=$bl.find('.textarea, textarea'),
		$visualizer=$bl.find('.audio_visualizer');

	mediaRecorderControl[bl]={
		ppAudioRecord:$ppAudioRecord,
		textarea:$textarea,
		microphone:$microphone,
		visualizer:$visualizer
	}

	mediaRecorderProcess[bl]=false;
	if (mediaRecorderDetect[bl] == undefined || mediaRecorderDetect[bl] === false) {
		var ssl=window.location.protocol == 'https:' || window.location.host == 'localhost';
		if(!ssl){
			alertCustom(l('your_browser_needs_ssl_certificate_to_run_audio_record'));
			hideAudioRecorderControl(bl);
			return false;
		}
	}
	mediaRecorderDetect[bl] = true;

	var fnStartRecord=function(){
		if ($ppAudioRecord.is('.im_audio_message_delete')) {//Delete
			confirmCustom(l('are_you_sure'), function(){
				audioMessageDelete($textarea.data('im-audio-message-id'),bl)
			}, l('confirm_delete_audio_message'));
		}else if ($ppAudioRecord.is('.record')) {//Upload
			mediaRecorderProcess[bl]=true;

			$ppAudioRecord.removeClass('record')
						  .addClass('disabled');
			$microphone.attr('title', l('record_audio_message_process'));

			addChildrenLoader($microphone);

			stopTrakAudioRecorder(bl);

			mediaRecorderApi[bl].stop();

			stopAudioVisualizer(bl);
		} else {//Record
			$ppAudioRecord.addClass('disabled');

			createAudioVisualizerElement(bl, $visualizer)

			var optionsDevice = {
				//audio: { sampleSize: 16, channelCount: 1, sampleRate: 16000 } ,
				audio: true,
				video: false
			};
			navigator.mediaDevices.getUserMedia(optionsDevice).then(function(stream){
				mediaRecorderStream[bl] = stream;

				initAudioVisualizer(bl, stream);

				/*var types = ['audio/webm', 'audio/webm\;codecs=opus'];
				for (var i in types) {
					console.log(types[i] + ' - ' + (MediaRecorder.isTypeSupported(types[i]) ? 'Maybe' : 'No'));
				}*/

				optionsDevice = {
					audioBitsPerSecond: 128000,
					//bitsPerSecond:
					//mimeType : 'audio/webm'
				}
				mediaRecorderApi[bl] = new MediaRecorder(stream, optionsDevice);
				mediaRecorderApi[bl].addEventListener('dataavailable',function(e){pushChankAudioRecorder(e,bl)});
				mediaRecorderApi[bl].start();

				mediaRecorderProcess[bl]=true;
				$ppAudioRecord.oneTransEnd(function(){
					$textarea.trigger('autosize');
				}).addClass('record');
				$microphone.attr('title', l('record_audio_message_stop'));
				$ppAudioRecord.removeClass('disabled');
			}).catch(function(error) {
				console.log('Error audio record', error);
				stopTrakAudioRecorder(bl);
				alertCustom(l('error_recording_audio_message'));
				$ppAudioRecord.removeClass('disabled');
			})
		}
	}
	//https://developer.mozilla.org/en-US/docs/Web/API/Navigator/permissions
	if (!isFx && navigator && typeof navigator.permissions == 'object') {
		navigator.permissions.query({name:'microphone'}).then(function(result) {
			if (result.state == 'granted') {
				fnStartRecord();
			} else if (result.state == 'prompt') {
				fnStartRecord();
			} else if (result.state == 'denied') {
				alertCustom(l('microphone_is_not_available'));
				hideAudioRecorderControl(bl);
			}
			result.onchange = function(e) {
				console.log('Microphone permissions', this.state);
			}
		})
	} else {
		//No - Safari, iOS, WebView Android, Internet Explorer
		//FF - Uncaught (in promise) TypeError: 'microphone' (value of 'name' member of PermissionDescriptor) is not a valid value for enumeration PermissionName.
		fnStartRecord();
	}
	/* Check Permissions */
}
/* Record audio message */

/* Upload image comment */
function initPostUploadImageEditor($btn){
	var w=$btn.find('span.btn_wrap')[0].offsetWidth;
	$btn.css('width', w);
}

function initCommentUploadImageEditor($btn){
	return;
	/*var w=$btn.find('span.btn_wrap')[0].offsetWidth;
	$btn.css('width', w);*/
}

function checkPaydCommentUploadImage(id) {
	return false;
    /*if(!userAllowedFeature['upload_image_chat_paid']){
        confirmCustom(l('upload_im_need_upgrade'), function(){
            redirectUrl(urlPagesSite.upgrade)
        },l('alert_html_alert'));
        return true;
    }
    return false;
	*/
}

function initCheckPaydCommentUploadImage(id){
	return true;
    /*if(userAllowedFeature['upload_image_chat_paid']){
		if ($this.addImageIm.is('.no_available')) {
			$this.addImageIm.removeClass('no_available');
		}
		return false;
	} else if(!$this.addImageIm.is('.no_available')) {
		$this.addImageIm.addClass('no_available');
		return true;
	}*/
}

function clickCommentUploadImage($file) {
    $file.next('input[type=reset]').click();
}

function changeCommentUploadImage($file) {
    //if(checkPaydCommentUploadImage())return false;
    $file.parent('form').find('input[type=submit]').click();
}

function clearCommentUploadImage(id, reset, checkPayd, e){
	if(_addCommentImage[id]==undefined)return;
	if(checkPayd||false) {
		if(checkPaydCommentUploadImage(id))return;
	}
	var $bl=_addCommentImage[id].bl;
	if (reset||false) {
		if ($bl.is('.disabled')) {
			return;
		}
	}
	if (e) {
		var $targ=$(e.target);
		if ($targ.is('#'+id+'_edit') || $targ.closest('#'+id+'_edit')[0]) {
			return;
		}
	}
	_addCommentImage[id].file = '';
	$bl.addClass('disabled');
	$bl.removeClass('disable_editor_image');
	$bl.find('.fa-camera').attr('title', l('upload_image'));
	//_addCommentImage[id].textarea.trigger('autosize');

	_addCommentImage[id].process=false;
	_addCommentImage[id].load=0;
	$bl.removeClass('to_hide');
	_addCommentImage[id].loader.removeChildrenLoader();

	checkTextareaCommentUploadImage($bl);

	//if(userAllowedFeature['upload_image_chat_paid']){
		clickCommentUploadImage($bl.find('.comment_upload_img_input_file'));
	//}
}

function clearCommentUploadImageOne(id, $bl){
	delete _addCommentImage[id];

	$bl.addClass('disabled');
	$bl.find('.fa-camera').attr('title', l('upload_image'));
	$bl.removeClass('to_hide');
	$('#'+id+'_loader').removeChildrenLoader();
	clickCommentUploadImage($bl.find('.comment_upload_img_input_file'));
}

function checkTextareaCommentUploadImage($bl){
	var fnChange=$bl.next('.wall_post_send').data('fn_change');
	if(typeof fnChange=='function')fnChange();
}

_addCommentImage={};
function initCommentUploadImage(id, wallPost){
	wallPost=wallPost||false;
	if (_addCommentImage[id] == undefined) {
		_addCommentImage[id] = {};
	}

	_addCommentImage[id].bl=$('#'+id);
	_addCommentImage[id].loader=$('#'+id+'_loader');
	_addCommentImage[id].edit=$('#'+id+'_edit');
	_addCommentImage[id].textarea=_addCommentImage[id].bl.closest('.comment_container_textarea').find('.textarea');
	_addCommentImage[id].process=false;
	_addCommentImage[id].load=0;
	_addCommentImage[id].file='';

	initCheckPaydCommentUploadImage(id);

	_addCommentImage[id].frm=$('#'+id+'_frm').submit(function(e){
		_addCommentImage[id].file = '';
		var indx=+new Date,
            frm = $(this), file = frm.find('input[type=file]'),
            fileName = file.attr('name'), formData = new FormData(),
            error = '';
        $.each(file[0].files, function(i, file){
            if ("image/jpeg,image/png,image/gif".indexOf(file.type) === -1) {
                error = l('accept_file_types');
                return false;
            }else if (file.size > clProfilePhoto.maxphotoFileSize) {
                error = clProfilePhoto.maxphotoFileSizeLimit;
                return false;
            }
            formData.append(fileName, file);
        })

        if (error) {
            alertCustom(error, l('alert_html_alert'));
            return false;
        }

		_addCommentImage[id].process=true;
        _addCommentImage[id].loader.addChildrenLoader();
        _addCommentImage[id].bl.addClass('to_hide')
		_addCommentImage[id].bl.find('.fa-camera').attr('title', '');

		var url;
		if(wallPost){
			url=url_ajax+'?cmd=upload_image_wall&input_name=' + fileName;
		} else {
			url=url_ajax+'?cmd=upload_comment_image&input_name='+fileName+'&ind='+indx+'&type=wall';
		}
		var xhr = new XMLHttpRequest();
		xhr.open("POST", url);
		xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4) {
                        if(xhr.status == 200) {
                            var data = xhr.responseText;
                            data = checkDataAjax(data);
                            if (data) {
                                if (data.status == 'error') {
                                    alertCustom(data.error, l('alert_html_alert'));
                                } else {
									_addCommentImage[id].file=data.file;
                                    _addCommentImage[id].load=indx;

									_addCommentImage[id].bl[data.ext=='gif'?'addClass':'removeClass']('disable_editor_image');
                                    _addCommentImage[id].bl.removeClass('disabled');
                                    _addCommentImage[id].bl.find('.fa-camera').attr('title', l('upload_image_delete'));

									_addCommentImage[id].textarea.trigger('autosize');

									if (wallPost) {
										_addCommentImage[id].file_th=data.file_th;
										clWall.setThumbPostImage(data.file_th);
									} else {
										checkTextareaCommentUploadImage(_addCommentImage[id].bl);
									}
                                }
                            } else {
								alertCustom(l('photo_file_upload_failed'), l('alert_html_alert'));
							}
                            _addCommentImage[id].process=false;
							_addCommentImage[id].bl.removeClass('to_hide');
                            _addCommentImage[id].loader.removeChildrenLoader();

                            //$this.send();
                        }
                    }
		};
		xhr.send(formData);
		return false;
	})
}

function openEditorCommentUploadImage(id, wallPost){
	if (_addCommentImage[id]==undefined||!_addCommentImage[id].file) {
		return;
	}
	imageEditorInfo = {
		id: id,
		uid: 0,
		preview: '',
		img: [],
		type: 'comment_image',
		file_th: wallPost?_addCommentImage[id].file_th:false,
		info: {},
		btn: _addCommentImage[id].edit
	}
	$ppImageEditor.open(_addCommentImage[id].file);
}
/* Upload image comment */