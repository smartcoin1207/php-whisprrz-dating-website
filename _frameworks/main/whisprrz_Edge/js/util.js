var MSG_INVALID_DATE_FORMAT = 'Invalid date format.  Must be of the form MM/DD/YYYY.';
var MSG_INVALID_MONTH       = 'Invalid date: %1 month must be in the range 1 - 12';
var MSG_INVALID_DAY         = 'Invalid date: %1 day not valid.';
var MSG_INVALID_YEAR        = 'Invalid date: %1 year must be greater than 1900.';
var MSG_INVALID_EMAIL       = 'Invalid email address: %1';
var MSG_INVALID_DOMAIN      = 'Invalid domain name: %1. The value must be of the form \'mydomain.com\'.';
var MSG_INVALID_PASSWORD    = 'Passwords must be at 6-10 alphanumeric characters!';
var MSG_INVALID_USERID      = 'Usernames may only contain alphanumeric characters and \'_\'!';
var MSG_INVALID_ALPHANUMERIC= 'This field may only contain alphanumeric characters, \'_\' and \'-\'!';
var MSG_INVALID_ALPHANUMERIC_WS= 'This field may only contain alphanumeric characters, \'_\', \'-\' and spaces!';
var MSG_INVALID_URL         = 'URL cannot contain *, " or \'';
var MSG_REQ_FIELD           = '%1 is a required field.';
var MSG_AT_LEAST_ONE_FIELD  = 'At least one of %1 or %2 must be specified.';
var MSG_AT_LEAST_ONE_FIELD_CHANGED  = 'At least one of %1 or %2 must be changed.';
var MSG_MAX_LENGTH			= '%1 may only be a maximum of %2 characters long.';
var MSG_MIN_LENGTH			= '%1 must be a minimum of %2 characters long.';
var MSG_ALPHA_NUMERIC		= '%1 may only contain alphanumeric characters.';
var MSG_ALPHA 				= '%1 may only contain alphabetic characters.';
var MSG_NAME				= '%1 may only contain alphanumeric characters, periods and exclaimation marks.';
var MSG_NUMERIC             = '%1 may only contain numeric characters!';
var MSG_LONG             = '%1 may only contain numeric characters!';
var MSG_TWO_FIELDS			= '%1 and %2 must be the same.';
var MSG_CONFIRM_TWO_FIELDS  = 'Your new password is the same as the old one. Do you want to proceed?';
var MSG_NOT_TWO_FIELDS      = '%1 and %2 may not have the same value.';
var MSG_REQUIRED_SELECT		= 'Please select a value for %1.';
var MSG_FIELD_INVALID       = '%1 is invalid.';
var MSG_TO_AGE_MUST_BIGGER  = 'Please select an Age Range from lowest to highest.';
var MSG_CONFIRM_TRANSACTION = "You are about to process a secure transaction that may take a minute to complete.Please do not click 'Back' on your web browser during the processing,as you will not receive confirmation of your transaction. Do you want to proceed?"
var MSG_INVALID_ZIP_CODE_FORMAT = 'Invalid Zip Code';
var MSG_INVALID_POSTAL_CODE_FORMAT = 'Invalid Postal code';
var MSG_ZIP_5_OR_9='Zip Code must be 5 or 9 numbers.';
var MSG_ZIP_5='Zip Code must be 5 numbers.';
var MSG_TOO_MANY_EMAILS_ADDRESSES='%1 may only contain a maximum of %2 email addresses';
var MSG_PHONE_NUMBER_VALID_CHARACTERS = '%1 may only contain the following the digits 0-9';
var MSG_PHONE_NUMBER_NORTH_AMERICA_INVALID_FORMAT = '%1 must be of the format NXX-NXX-XXXX where N is the digits 2-9 and X is any digit.';
var MSG_PHONE_NUMBER_NORTH_AMERICA_RESERVED_AREA_CODE =  'Reserved area codes, such as 800, 888, 900, etc,  and emergency service numbers, such as 411, 911, etc,  are not permitted.';
var MSG_PHONE_NUMBER_VALID_CHARACTERS = '%1 may only contain the following characters: 0-9()-.';
var MSG_PHONE_NUMBER_NORTH_AMERICA  = '%1 must contain 10 digits.';
var MSG_MOBILE_NUMBER_VALID_CHARACTERS = '%1 may only contain the following the digits 0-9 (Note: The Date Mobile section is optional and can be left blank)';
var MSG_MOBILE_NUMBER_NORTH_AMERICA_INVALID_FORMAT = '%1 must be of the format NXX-NXX-XXXX where N is the digits 2-9 and X is any digit. (Note: The Date Mobile section is optional and can be left blank)';
var MSG_MOBILE_NUMBER_NORTH_AMERICA_RESERVED_AREA_CODE =  'Reserved area codes, such as 800, 888, 900, etc,  and emergency service numbers, such as 411, 911, etc,  are not permitted. (Note: The Date Mobile section is optional and can be left blank)';
var MSG_TOO_MANY_DOMAINS='%1 may only contain a maximum of %2 email addresses';
var MSG_MOBILE_NUMBER_VALID_CHARACTERS = '%1 may only contain the following characters: 0-9()-. (Note: The Date Mobile section is optional and can be left blank)';
var MSG_MOBILE_NUMBER_NORTH_AMERICA  = '%1 must contain 10 digits. (Note: The Date Mobile section is optional and can be left blank)';
var MSG_NUMERIC_MIN          = '%1 must be greater than or equal to %2'
var MSG_NUMERIC_MAX          = '%1 must be less than or equal to %2'
var MSG_MAX_LENGTH_COUNTER	= 'You have reached the maximum of %1 characters for this field.';
var MSG_NON_EMPTY_DEPENDENCY = '%1 has been completed. %2 cannot be empty if %1 has been completed.';
var MSG_SAVED_SEARCH_EMPTY = 'You have selected to save this search but failed to provide a saved search name. Please provide a saved search name.';
var MSG_REGISTRATION_UPLOAD_PHOTO_SKIP_STEP = 'You have filled out the form to upload a photo. Are you sure you want to skip this step?'


var popup = null;
function setFocus(form,field)
{
	if (form != '') {
		try	{document.forms[form][field].focus();} catch(e) {}
	}
	else {
		try	{document.forms[0][field].focus();} catch(e) {}
	}
}

function winpop(loc,w,h,scroll) {
	var name = loc.replace(/\W/g, "");
	window.open(loc,name,'width='+w+', height='+h+', location=no, directories=no, menubar=no, scrollbars='+scroll+', resizable=no, status=no, toolbar=no');
}

function newpop(url,name, style) {
	var win=window.open(url,name,style);
	try{ win.focus();} catch(e) {}
}

/* Add an onload function */
var gOnload = new Array();
function addOnload(f)
{

	if (window.onload)
	{
		if (window.onload != runOnload)
		{
			gOnload[0] = window.onload;
			window.onload = runOnload;
		}
		gOnload[gOnload.length] = f;
	}
	else
		window.onload = f;
}
function runOnload()
{
	for (var i=0;i<gOnload.length;i++)
		gOnload[i]();
}

/* Trim the whitespace from beginning and the end of the given string. */
function trim(str)
{
	str = new String(str);
	return str.replace(/^\s+/,'').replace(/\s+$/,'');
}

/* Add a trim method to String's. */
function strtrim()
{
	return trim(this);
}
String.prototype.trim = strtrim;

/** Changes the action for the given form and calls submit. */
function submitForm(form, action)
{
	form.action = action;
	form.submit();
}
/** Replace the current page in the browser history with a call to the page with
	form params encoded as GET parameters */
function locationReplaceForm(form, url) {
	url += '?';
	var tot = form.elements.length;
	for (var i = 0; i < tot; i++) {
		var e = form.elements[i];
		if (! isEmpty(e)) {
			url += e.name + '=' + e.value;
			if (i < tot -1) {
				url+='&';
			}
		}
	}
	location.replace(url);
}


function isEmpty(field) {

	//if the field is disabled treat it as an empty field.
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
		field.value = field.value.trim();
	}
	catch(e) {}
	if (field.value.length == 0) {
		return true;
	}
}

function isCheckBoxChecked(field) {
	if (field[0]) {
		for (i = 0;i<field.length;i++) {
			theField = field[i];
			if (theField.checked) {
				return true;
			}
		}
		return false;
	} else {
		if (!field.checked) {
			return false;
		}
	}
	return true;
}
function validateRequiredField(field, name)
{
  return validateRequiredField(field, name, '');
}

/** Validates required field */
function validateRequiredField(field, name, dv)
{
	//Try trim - will fail for input type="file".
	try
	{
		field.value = field.value.trim();
		dv = dv.trim();
	}
	catch(e) {}

	if (field.value.length == 0 || field.value == dv)
	{
		alert(MSG_REQ_FIELD.replace('%1', name));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

/** Validates that at least one checkbox in the form with the input field name is checked. */
function validateRequiredCheckbox(field, name, msg) {
	if (!isCheckBoxChecked(field)) {
		alert(msg.replace('%1', name));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

/** Validates required drop down */
function validateRequiredSelect(field, name, defaultValue) {
	if (field.value == null || field.value == '' || field.value == defaultValue) {
		alert(MSG_REQUIRED_SELECT.replace('%1', name));
		try{field.focus();}catch(e){}
		return false;
	}
	else {
		return true;
	}
}

function validateRequiredMultiSelect(field, name)
{
  var selected = false;
  for (i=0; i<field.length; i++) {
    if (field.options[i].selected) {
      selected = true;
      break;
    }
  }
  if (selected) return true;
	alert(MSG_REQUIRED_SELECT.replace('%1', name));
	try{field.focus();}catch(e){}
	return false;
}

/** Validate that a field is less than or equal to the max length.
	If the value of that field is too large, chop off the extraneous characters. */
function validateMaxLength(field, name, maxLength)
{
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

/** Validate that a field is greater than or equal to the min length. */
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

/** Validates the email field. If the field is not valid, focus is given to that field. */
function validateEmailField(emailField, name)
{
	emailField.value = emailField.value.trim();
	if (isEmpty(emailField)) return true;

	if (!checkEmail(emailField.value)) {
		alert(MSG_INVALID_EMAIL.replace('%1', emailField.value));
		try{emailField.focus();}catch(e){}
		return false;
	}

	return true;
}

/** Validates the email field without checking for bad domains/users etc. */
function validateAnyEmailField(emailField, name)
{
	emailField.value = emailField.value.trim();
	if (isEmpty(emailField)) return true;

	if (!checkAnyEmail(emailField.value)) {
		alert(MSG_INVALID_EMAIL.replace('%1', emailField.value));
		try{emailField.focus();}catch(e){}
		return false;
	}

	return true;
}

/** Validates multiple email fields. If the field is not valid, focus is given to that field. */
function validateMultipleEmailField(field, name, max) {
	field.value = field.value.trim();
	field.value = field.value.replace(/;/g, ',');
	field.value = field.value.replace(/,+/g, ',');
	field.value = field.value.replace(/^,/, '');
	field.value = field.value.replace(/,$/, '');
	//TODO: could check for duplicates...
	var array = field.value.split(",");
	if (array.length > max) {
		alert(MSG_TOO_MANY_EMAILS_ADDRESSES.replace('%1', field.name).replace('%2', max));
		try{field.focus();}catch(e){}
		return false;
	}

	for (var i = 0 ; i < array.length ; i++) {
		array[i] = array[i].trim();
		if (!checkEmail(array[i])) {
			alert(MSG_INVALID_EMAIL.replace('%1', array[i]));
			try{field.focus();}catch(e){}
			return false;
		}
	}
	return true;
}

function validateDomainField(domainField, name){
 domainField.value = domainField.value.trim();
 if (!checkDomain(domainField.value)) {
  alert(MSG_INVALID_DOMAIN.replace('%1', domainField.value));
  try{domainField.focus();}catch(e){}
  return false;
 }
 return true;
}
function validateMultipleDomainField(field, name, max) {
 field.value=field.value.trim();
 field.value=field.value.replace(/;/g, ',');
 field.value=field.value.replace(/,+/g, ',');
 field.value=field.value.replace(/^,/, '');
 field.value=field.value.replace(/,$/, '');
 //TODO: could check for duplicates...
 var array=field.value.split(",");
 if (array.length > max) {
	alert(MSG_TOO_MANY_DOMAINS.replace('%1', field.name).replace('%2', max));
	try{field.focus();}catch(e){}
	return false;
 }
 for (var i=0 ; i < array.length ; i++) {
	array[i]=array[i].trim();
	if (!checkDomain(array[i])) {
	 alert(MSG_INVALID_DOMAIN.replace('%1', array[i]));
	 try{field.focus();}catch(e){}
	 return false;
	}
 }
 return true;
}

/** Checks that a field contains only alphanumeric values */
function validateAlphaNumeric(field, name)
{
	var mask = /^[_0-9a-zA-Z-]*[_0-9a-zA-Z-]$/
	if (!mask.test(field.value)) {
		alert(MSG_ALPHA_NUMERIC.replace('%1', name));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

function validateAlphaNumeric_search(field, name)
{
	var mask = /^[_0-9a-zA-Z-*]*[_0-9a-zA-Z-*]$/
	if (!mask.test(field.value)) {
		alert(MSG_ALPHA_NUMERIC.replace('%1', name));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

/** Checks that a field contains only alphanumeric values */
function validateAlphaNumericWS(field, name)
{
	var mask = /^[_0-9a-zA-Z-\s]*[_0-9a-zA-Z-\s]$/
	if (!mask.test(field.value)) {
		alert(MSG_ALPHA_NUMERIC.replace('%1', name));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

/** Checks that a field contains only alphanumeric values and dot*/
function validateAlphaNumericDot(field, name)
{
	var mask = /^[_0-9a-zA-Z-\.]*[_0-9a-zA-Z-\.]$/
	if (!mask.test(field.value)) {
		alert(MSG_ALPHA_NUMERIC.replace('%1', name));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

/** Checks that a field contains only numeric values */
function validateNumeric(field, name)
{
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

/** Checks that a field contains only numeric values */
function validateLong(field, name)
{
	var val = trim(field.value);
	field.value = val;
	if (isEmpty(field)) return true;
	var mask = /^-?[0-9]*[0-9\s]$/
	if (!mask.test(val)) {
		alert(MSG_LONG.replace('%1', name));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

/** Checks that a field contains only numeric values and is > a minimum value. */
function validateMinNumeric(field, name, minValue)
{
	if (isEmpty(field)) return true;
	if (!validateNumeric(field, name)) return false;
	var value = trim(field.value);
	if (value < minValue) {
		alert(MSG_NUMERIC_MIN.replace('%1', name).replace('%2', minValue));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

/** Checks that a field contains only numeric values and is < a maximum value. */
function validateMaxNumeric(field, name, maxValue)
{
	if (isEmpty(field)) return true;
	if (!validateNumeric(field, name)) return false;
	var value = trim(field.value);
	if (value > maxValue) {
		alert(MSG_NUMERIC_MAX.replace('%1', name).replace('%2', maxValue));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

/** Checks that a field contains only numeric values */
function validateNumericWS(field, name)
{
	var val = trim(field.value);
	if (val == null || val == '') return true;
	var mask = /^[0-9-\s]*[0-9-\s]$/
	if (!mask.test(val)) {
		alert(MSG_NUMERIC.replace('%1', name));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

/** Checks that a field contains only alphanumeric values */
function validateAlpha(field, name)
{
	var mask = /^[_a-zA-Z-\s~]*[_a-zA-Z-\s~]$/
	if (!mask.test(field.value)) {
		alert(MSG_ALPHA.replace('%1', name));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

/** Checks that a field contains alphanumeric values, periods and exclamation marks only */
function validateName(field, name)
{
	var mask = /^[_a-zA-Z-\s~\.!']*[_a-zA-Z-\s~\.!']$/
	if (!mask.test(field.value)) {
		alert(MSG_NAME.replace('%1', name));
		try{field.focus();}catch(e){}
		return false;
	}
	return true;

}

/** Validates the first field is greater than or equal to the second field*/
function confirmNoLess(field,name,field2,name2)
{
	if (Number(field.value) < Number(field2.value)){
		var msg = MSG_TO_AGE_MUST_BIGGER.replace('%1',name);
		msg = msg.replace('%2',name2);
		alert(msg);
		field.focus();
		return false;
	}else{
		return true
	}
}

/** Validates that two fields have the same value and ask for confirmation*/
function confirmTwoFields(field,name,field2,name2)
{
	if (field.value == field2.value)
	{
		var msg = MSG_CONFIRM_TWO_FIELDS;
		field.focus();
		return confirm(msg);
	}else
	{
		return true
	}

}

/** Validates that at least one of the specified fields is present. Displays default message. */
function validateAtLeastOneField(field,name,field2,name2) {
  var msg = MSG_AT_LEAST_ONE_FIELD.replace('%1', name);
  msg = msg.replace('%2', name2);
	return validateAtLeastOneField(field,name,field2,name2,msg);
}

/** Validates that at least one of the specified fields is present */
function validateAtLeastOneField(field,name,field2,name2,msg) {
	if (isEmpty(field)&&isEmpty(field2))
	{
		alert(msg);
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

/** Validates that two fields have the same value */
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

/** Validates that two fields have the same value, disregarding case */
function validateTwoFieldsIgnoreCase(field,name,field2,name2) {
	if (field.value.toLowerCase() != field2.value.toLowerCase()){
		var msg = MSG_TWO_FIELDS.replace('%1', name);
		msg = msg.replace('%2', name2);
		alert(msg);
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

/** Validates that two fields do not have the same value */
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

/**
 * This checks to make sure that field1 is non-empty. If it is non-empty then
 * field2 must be non-empty. If field1 is empty then we don't care
 * if field2 is empty or not. Basically, a check of field2 is only
 * dependent on field1 being empty or not.
 */
function nonEmptyDependency(field1, field1Name, field2, field2Name, message){
	if(!isEmpty(field1) && isEmpty(field2)){
		alert(message);
		return false;
	}else{
		return true;
	}
}

/**
 * Reference: Sandeep V. Tamhankar (stamhankar@hotmail.com),
 * http://javascript.internet.com
 */
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

	var mask=/@(date.com|date.net|matchmaker.com|matchmaker.net|matchmaker.org|matchmaker.biz|mm.org|gay.com|wellsfargo.com|spamhole.com|mailinator.com|klassmaster.com|fakeinformation.com|sogetthis.com|spambob.com|spamgourmet.com|spamex.com)/i;
	if (mask.test(emailStr.toLowerCase())) {
		return false;
	}
	/*mask=/^(root|abuse|webmaster|help|postmaster|sales|resumes|contact|advertising|spam|spamtrap|nospam|noc|admin|support|daemon|listserve|listserver|autoreply)@/i;
	if (mask.test(emailStr.toLowerCase())) {
		return false;
	}*/

	return true;
}

/**
 * Reference: Sandeep V. Tamhankar (stamhankar@hotmail.com),
 * http://javascript.internet.com
 */
function checkAnyEmail(emailStr) {
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

	return true;
}

function checkDomain(domain) {
    if (/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/.test(domain)) {
        return true;
    }
    return false;

	var specialChars="\\(\\)><@,;:\\\\\\\"\\.\\[\\]!%";
	var validChars="\[^\\s" + specialChars + "\]";
	var ipDomainPat=/^\[(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\]$/;
	var atom=validChars + '+';
	var domainPat=new RegExp("^" + atom + "(\\." + atom +")*$");

	for (i=0; i<domain.length; i++) {
		if (domain.charCodeAt(i)>127) {
			return false;
	   }
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

	if (len!=2) {
		return false;
	}

	var mask=/date.com/i;
	if (mask.test(domain.toLowerCase())) {
		return false;
	}
	mask=/date.net/i;
	if (mask.test(domain.toLowerCase())) {
		return false;
	}
	mask=/date.info/i;
	if (mask.test(domain.toLowerCase())) {
		return false;
	}

	return true;
}

/** Validates a URL. */
function validateURL(field)
{
	var str = field.value;
	if ( str.indexOf("*") != -1 || str.indexOf('"') != -1 || str.indexOf("'") != -1 ) {
		alert(MSG_INVALID_URL);
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

/** Returns true if the string only contains digits. */
function validateNumber(str, scale, precision)
{
	if (precision == 0) {
		var format = new RegExp('^\\s*\\d{0,'+scale+'}\\s*$');
		return format.test(str);
	}
	var format = new RegExp('^\\s*\\d{0,'+(scale-precision)+'}\\.\\d{0,'+precision+'}\\s*$');
	if ( format.test(str) ) {
		return true;
  	} else {
		format = new RegExp('^\\s*\\d{0,'+scale+'}\\s*$');
		return format.test(str);
  	}
}

/** THE FOLLOWING ARE NOT CURRENTLY IN USE **/
/** Validates a date string returns true if it is of the form MM/DD/YYYY. */
function validateDate(str)
{
  // Validate format
  var dateformat = /^\s*\d{1,2}\/\d{1,2}\/\d{2,4}\s*$/;  // MM/DD/YYYY

  if ( !dateformat.test(str) ) {
	alert(MSG_INVALID_DATE_FORMAT);
	return false;
  }

  var elements = str.split('/');

  // check month
  var month = elements[0];
  if (month < 1 || month > 12) {
	alert(MSG_INVALID_MONTH.replace('%1', str));
	return false;
  }

  // check day
  var day = elements[1];
  if (!validDate(month, day)) {
	alert(MSG_INVALID_DAY.replace('%1', str));
	return false;
  }

  // check year
  var year = elements[2];
  if (year < 100 && year > 50) year += 1900;
  else if (year > 0 && year < 50) year += 2000;

  if (year < 1900) {
	alert(MSG_INVALID_YEAR.replace('%1', str));
	return false;
  }


  return true;
}

/** Checks to make sure the number of days in the month is correct.  This does
	not work in all cases (ie February) */
function validDate(month, value)
{
  var monthMax = new Array (31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31) ;
  month = month - 1;

  var top = monthMax[month];
  if (value > top) return false; // value is greater than highest for the month
  else return true;
}

var bname = navigator.appName;
var bver = parseInt(navigator.appVersion);

// provides focus for specific form element on page load
function giveFocus(frm, elm)
{
  eval("document."+frm+"."+elm+".focus()");
}

/** Toggles the highlighting for a row table. */
function toggleRowHighlight(cb)
{
  var e = cb;
  if (document.all?1:0) {  // IE vs Netscape
	while (e.tagName!="TR") {
	  e=e.parentElement;
	}
  }
  else {
	return;  // row highlight not supported for Netscape
	//while (e.tagName!="TR") {
	//  e=e.parentNode;
	//}
  }

  if (cb.checked) e.className = "H";
  else e.className = "";
}

/** Given a select list, this will sort the options in alphabetical order. */
function sortOptions(src)
{
  var list = new Array();
  for (var i = 0; i < src.length; i++) {
	var opt = new Option(src[i].text, src[i].value, false, true);
	opt.selected = src[i].selected;
	list[i] = opt;
  }

  list.sort(compareOptions);

  src.options.length=0;
  for (var i = 0; i < list.length; i++) {
	//var opt = new Option(list[i].text, list[i].value, false, true);
	src.options[src.length] = list[i];
  }
}

/** Compares to options. */
function compareOptions(a,b)
{
  if (a.text < b.text) return -1;
  if (a.text > b.text) return 1;
  return 0;
}

function getById(tag)
{
  if (document.getElementById) //  Netscape, Mozilla, etc.
  {
	return document.getElementById(tag);
  }
  else if (document.all)      //  IE, Konqueror, etc.
  {
	return document.all[tag];
  }
}

function getByIdFromParent(tag)
{
  if (parent.document.getElementById) //  Netscape, Mozilla, etc.
  {
	return parent.document.getElementById(tag);
  }
  else if (parent.document.all)      //  IE, Konqueror, etc.
  {
	return parent.document.all[tag];
  }
}

function displaydiv(div1_id, div2_id, form)
{
	if (document.getElementById){
		if(!document.getElementById(div1_id)) return ;
		if(!(document.getElementById(div1_id).style)) return ;
		if(!(document.getElementById(div1_id).style.display)) return ;

		var state1 = document.getElementById(div1_id).style.display;
		if(state1=="none") {
				document.getElementById(div1_id).style.display="block";
				document.getElementById(div2_id).style.display="none";
				if (form != null)
				{
				   form[div1_id].value='true';
				   form[div2_id].value='false';
				 }
		 }
	}
	else if (document.all)	{
		if(!document.all[div1_id]) return ;
		if(!(document.all[div1_id].style)) return ;
		if(!(document.all[div1_id].style.display)) return ;

		var state1 = document.all[div1_id].style.display;
		if(state1=="none") {
				document.all[div1_id].style.display = "block";
				document.all[div2_id].style.display = "none";
				if (form != null)
				{
				   form[div1_id].value='true';
				   form[div2_id].value='false';
				}
		}
	}
}


function showDiv(div_id, show)
{
  var div = getRefToDiv(div_id)
  if (show) div.style.display='block';
  else div.style.display='none';
}

function switchDivs(div1_id, div2_id, show)
{
  if (show) switchdiv(div1_id, div2_id);
  else switchdiv(div2_id, div1_id);
}

function switchdiv(div1_id, div2_id, form)
{
	if (document.getElementById)	{
		if(!document.getElementById(div1_id)) return ;
		if(!(document.getElementById(div1_id).style)) return ;
		if(!(document.getElementById(div1_id).style.display)) return ;

		var state1 = document.getElementById(div1_id).style.display;
		if(state1=="none") {
				document.getElementById(div1_id).style.display="block";
				document.getElementById(div2_id).style.display="none";
				if (form != null)
				{
				   form[div1_id].value='true';
				   form[div2_id].value='false';
				 }
		 }
		if(state1=="block") {
				document.getElementById(div2_id).style.display="block";
				document.getElementById(div1_id).style.display="none";
				if (form != null)
				{
				   form[div1_id].value='false';
				   form[div2_id].value='true';
				 }
		 }
	}
	else if (document.all)	{
		if(!document.all[div1_id]) return ;
		if(!(document.all[div1_id].style)) return ;
		if(!(document.all[div1_id].style.display)) return ;

		var state1 = document.all[div1_id].style.display;
		if(state1=="none") {
				document.all[div1_id].style.display = "block";
				document.all[div2_id].style.display = "none";
				if (form != null)
				{
				   form[div1_id].value='true';
				   form[div2_id].value='false';
				 }
		}
		if(state1=="block") {
				document.getElementById(div1_id).style.display="none";
				document.getElementById(div2_id).style.display="block";
				if (form != null)
				{
				   form[div1_id].value='false';
				   form[div2_id].value='true';
				 }
		 }
	}
}

function getRefToDiv(divID) {
	if( document.layers ) { //Netscape layers
		return document.layers[divID]; }
	if( document.getElementById ) { //DOM; IE5, NS6, Mozilla, Opera
		return document.getElementById(divID); }
	if( document.all ) { //Proprietary DOM; IE4
		return document.all[divID]; }
	if( document[divID] ) { //Netscape alternative
		return document[divID]; }
	return false;
}

function selectAll(field, state)
{
	if(field == null)
	{
		return;
	}

	if(field.length == null)
	{
		field.selected = field.checked = state;
	}

	for (var i = 0 ; i < field.length ; i++)
	{
		field[i].selected = field[i].checked = state;
	}
}

function checkedCount(field)
{
	var	checked	= 0;

	if (field != null) {
		if (field.length == null)
		{
			if (field.checked == true)
			{
				checked++;
			}
		}
		else
		{
			for	(var i = 0 ; i < field.length	; i++)
			{
				if (field[i].checked == true)
				{
					checked++;
				}
			}
		}
	}

	return checked;
}

function isChecked(field)
{
	if (checkedCount(field) == 0)
	{
		return false;
	}
	return true;
}

function selectedCheck(field){
	var checkCounter = 0;
	if(!field.length) return field.value;
	for (i = 0; i < field.length; i++)
	{
		if (field[i].checked){
			return field[i].value;
			break;
		}
	}
}

function isOneChecked(field)
{
	if (checkedCount(field) == 1)
	{
		return true;
	}
	return false;
}

function addToDate(formName,yearName,monthName,dayName,offset)
{
	var form = document.forms[formName];
	var yearSelect = form[yearName];
	var monthSelect = form[monthName];
	var daySelect = form[dayName];
	var year = yearSelect[yearSelect.selectedIndex].value;
	var month = monthSelect[monthSelect.selectedIndex].value;
	var day = daySelect[daySelect.selectedIndex].value;
	var date = new Date(year,month-1,day);
	date.setDate(date.getDate()+offset);
	daySelect[daySelect.selectedIndex].value = date.getDate();
	monthSelect[monthSelect.selectedIndex].value = date.getMonth()+1;
	yearSelect[yearSelect.selectedIndex].value = date.getYear();
}

function updateDay(change,formName,yearName,monthName,dayName)
{
	var form = document.forms[formName];
	var yearSelect = form[yearName];
	var monthSelect = form[monthName];
	var daySelect = form[dayName];
	var year = yearSelect[yearSelect.selectedIndex].value;
	var month = monthSelect[monthSelect.selectedIndex].value;
	var day = daySelect[daySelect.selectedIndex].value;

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
		while(j < i)
		{
			daySelect[j] = new Option(j+1,j+1);
			j = j + 1;
		}
		if (day <= i)
		{
			daySelect.selectedIndex = day - 1;
		}
		else
		{
			daySelect.selectedIndex = daySelect.length - 1;
		}
	}
}

function checkCR(formName,e)
{
	if (e.keyCode == 13) {
		if(eval('validate'+formName+'()'))
		document.forms[formName].submit();
	}
}

function checkCRPro(formName,e,custFunc)
{
	if (e.keyCode == 13) {
		if(eval('validate'+formName+'()'))
		{
			eval(custFunc+'()');
			document.forms[formName].submit();
		}
	}
}

function checkIt(obj){
	if(obj == null) return;
	obj.checked='true';
}


//pop-under
var flag = "1";

function clearFlag()
{
	flag = "0";
}

function pop()
{
	if(flag == '0')
	{
		winme=window.open('/jsp/common/popad.jsp','','toolbar=no,location=no,scrollbars=no,resizable=no');
	}
}

/**
 * Function for DA-7088, popup survey for when people exit the
 * second join page of the registration process.
 */
function surveyPop(url){
	winme=window.open(url);
}

function setFlag()
{
	flag++;
}

// disable right-click
//var mes_disable_right="Function not supported.";
var mes_disable_right="Function not supported.";
function clickIE4()
{
	if (event.button==2)
	{
		alert(mes_disable_right);
		return false;
	}
}

function clickNS4(e)
{
	if (document.layers||document.getElementById&&!document.all)
	{
		if (e.which==2||e.which==3)
		{
			alert(mes_disable_right);
			return false;
		}
	}
}

function disable_right_click()
{
	if (document.layers)
	{
		document.captureEvents(Event.MOUSEDOWN);
		document.onmousedown=clickNS4;
	}else if (document.all&&!document.getElementById)
	{
		document.onmousedown=clickIE4;
	}
	document.oncontextmenu=new Function("alert(mes_disable_right);return false")
}

/*
 * Returns true if the field (a state or city required field) is valid
 * (if the location/zip radio has chosen to specify the location by
 * location rather than zip).
 */
function validateLocationStateCity(field, name, defaultValue, radioField) {
	if (typeof radioField == 'undefined') {
		return validateRequiredSelect(field, name, defaultValue);
	}
	else {
		if (radioField[0].checked) { // Zip field checked.
			return true;
		}
		else {
			return validateRequiredSelect(field, name, defaultValue);
		}
	}
}

function validateLocationZip(field, name, countryField, radioField) {
	// Zip field may not be present.
	if (typeof field == 'undefined') {
		return true;
	}
	if (typeof radioField == 'undefined') {
		return validateZip(field, name, countryField);
	}
	else {
		if (radioField[0].checked) {
			var zip = field.value.replace(/[-\s]+/,"");
			for(i = 0; i < zip.length; ++i) {
				zip = zip.replace(/[-\s]+/,"");
			}
			if (trim(zip).length == 0) {
				alert(MSG_REQ_FIELD.replace('%1', name));
				try {
					field.focus();
				}
				catch(e) {
					// Do nothing.
				}
				return false;
			}
			else {
				return validateZip(field, name, countryField);
			}
		}
		else {
			return true;
		}
	}
}

function validateZip(field, name, countryField)
{
  return validateZipReq(field, name, countryField, true)
}

function validateZipReq(field, name, countryField, required)
{
  var country;
  if (countryField.type == "select-one") {
	country = countryField[countryField.selectedIndex].value;
  }
  else {
	country = countryField.value;
  }

  // Only validate Canada and US
  if ( country!='CA' && country != 'US' && country!='Canada' && country!='USA') {
	return true;
  }

  var value = field.value.replace(/[-\s]+/,"");

  // Required Field for US and Canada
  value = trim(value);
  if (value.length == 0) {
    if(required) {
    	alert(MSG_REQ_FIELD.replace('%1', name));
	    try{field.focus();}catch(e){}
	    return false;
	  }
	  else return true;
  }

  if(country=='CA' || country=='Canada') {
	return validatePostalCode(field, name)
  } else if(country=='US' || country=='USA') {
	return validateZipCode(field, name)
  }
}

function validateZipCode(field, name) {
	// Remove hyphen and white space
	var value = field.value.replace(/[-\s]+/,"");

	for (i=0;i<value.length;++i) {
		value= value.replace(/[-\s]+/,"");
	}
	field.value = value;

	if(value.length==5){
		var expr = new RegExp("^[0123456789]{5}");
	} else if(value.length==9){
		var expr = new RegExp("^[0123456789]{9}");
	} else {
		alert(MSG_ZIP_5);
		errormessage=true;
		try{field.focus();}catch(e){}
		return false;
	}

	if (!expr.test(value)){
		alert(MSG_INVALID_ZIP_CODE_FORMAT);
		errormessage=true;
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

function validatePostalCode(field, name) {
	// Remove hyphen and white space
	var value = field.value.replace(/[-\s]+/,"");
	for (i=0;i<value.length;++i) {
		value = value.replace(/[-\s]+/,"");
	}
	field.value = value;

	if (value.length == 6) {
		expr = new RegExp("^[a-zA-Z]{1}[0123456789]{1}[a-zA-Z]{1}[0123456789]{1}[a-zA-Z]{1}[0123456789]{1}");
	} else {
		alert(MSG_INVALID_POSTAL_CODE_FORMAT);
		errormessage=true;
		try{field.focus();}catch(e){}
		return false;
	}

	if (!expr.test(value)){
		alert(MSG_INVALID_POSTAL_CODE_FORMAT);
		errormessage=true;
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

function validateTaxID(field, name, countryField)
{
  var country;
  if (countryField.type == "select-one") {
	  country = countryField[countryField.selectedIndex].value;
  }
  else {
	  country = countryField.value;
  }

  // Only validate US
  if ( country != 'US' && country!='USA') {
	  return true;
  }

  var value = field.value.replace(/[-\s]+/,"");

  // Required Field for US
  value = trim(value);
  if (value.length == 0) {
	alert(MSG_REQ_FIELD.replace('%1', name));
	try{field.focus();}catch(e){}
	  return false;
  }
	return true;
}


//IM related DHTML
  IE4 = (document.all) ? 1 : 0; // initialize browser..
  NS4 = (document.layers) ? 1 : 0; // identification and...
  NS6 = (document.getElementById) ? 1 : 0;
  ver4 = (IE4 || NS4 || NS6) ? 1 : 0; // DHTML variables
  if(NS4){
   layerRef = "parent.document.layers";
   styleSwitch = "";
  }else if (IE4){
   layerRef = "parent.document.all";
   styleSwitch = ".style";
  }else if (NS6) {
   layerRef = "parent.document.getElementById";
   styleSwitch = ".style";
  }


  var win_width = 300, win_height = 200;
  if( typeof( window.innerWidth ) == 'number' ) {
	  //Non-IE
	   win_width = window.innerWidth;
	   win_height = window.innerHeight;
  }else if( document.documentElement &&
	 ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
	   //IE 6+ in 'standards compliant mode'
		win_width = document.documentElement.clientWidth;
		win_height = document.documentElement.clientHeight;
  }else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		//IE 4 compatible
		win_width = document.body.clientWidth;
		win_height = document.body.clientHeight;
  }
  x = (win_width-230)/2;
  y = (win_height-160)/2;
	if(x<0) x=0
	if(y<0) y=0


  function showHideLayer(layerName, visibility){
	try{
	  if (NS6)
		 eval('parent.document.getElementById("' +layerName + '")' +styleSwitch+'.visibility = "'+visibility+'"');
		else
		   eval(layerRef+'["'+layerName+'"]'+styleSwitch+'.visibility = "'+visibility+'"');
		   }catch(e) {}
  }
  function closeIMPopup(otherhandle){
	  if (NS6)
		 eval('parent.document.getElementById("closeimpopup")'+'.src="/UpdatePendingIMStatus.do?status=8&otherhandle='+otherhandle+'"');
	  else
		   eval(layerRef+'["closeimpopup"]'+'.src="/UpdatePendingIMStatus.do?status=8&otherhandle='+otherhandle+'"');
		  showHideLayer('imalert', 'hidden');
  }
  imBegin = '<iframe id="closeimpopup" name="closeimpopup" src="" scrolling=no width=1 height=1 frameborder=0 marginheight=0 marginwidth=0 hspace=0 vspace=0></iframe>' +
				'<DIV ID="imalert" ALIGN=CENTER STYLE="POSITION: ABSOLUTE; TOP:'+y+'; LEFT:'+x+';WIDTH:230; HEIGHT:160; BORDER: solid 1px #000000; BACKGROUND:#4C6E89;VISIBILITY:hidden; Z-INDEX:10;">'+
				'<DIV style="HEIGHT: 5px; width: 230;"><img src="images/spacer.gif" border=0></DIV>'+
				'<DIV align=center style="width: 217px; border: solid 1px #395266; border-right: solid 2px #395266; border-bottom: solid 2px #395266; background: #FFFFCC; padding-top: 10px; padding-bottom: 11px;">'+
				'  <img src="/themes/default/dyn-images/datelogo_im.gif" border=0 style="margin-bottom: 7px;">'+
				'  <div style="height: 1px; width: 74px; background: #E5E5E5;"><img src="images/spacer.gif" border=0></div>' +
				'<div ID="imdhtml" style="padding-top: 9px; padding-bottom: 13px; font-family: Arial; font-size: 9pt; color: #000000;">';

  function imMiddle(handle, otherhandle) {
	return  '<span class="head2">'+otherhandle+'</span> is requesting an IM conversation.'+
			'   <table><tr>' +
			'      <td align=center><div style="border: solid 1px #8F0100; width: 75px;"><div style="width: 73px; padding-top: 2px; padding-bottom: 2px; border: solid 1px #FFFFFF; background: #8F0100;"><a href="javascript:responseIC(\''+handle+'\',\''+otherhandle+'\')" style="font-family: Verdana; font-size: 7.5pt; text-decoration: none; text-transform: uppercase; color: #FFFFFF; font-weight: bold;">Accept IM</a></div></div></td>'+
			'      <td with=10></td>' +
			'      <td align=center><div style="border: solid 1px #8F0100; width: 75px;"><div style="width: 73px; padding-top: 2px; padding-bottom: 2px; border: solid 1px #FFFFFF; background: #8F0100;"><a href="javascript:closeIMPopup(\''+otherhandle+'\')" style="font-family: Verdana; font-size: 7.5pt; text-decoration: none; text-transform: uppercase; color: #FFFFFF; font-weight: bold;">Close</a></div></div></td>'+
			'    </tr></table>';
   }

  imEnd =   '</div></div><div align=right style="height: 18px; width: 230; padding-top: 1px; padding-right: 10px;"><a href="/EditSettings.do#imsettings" style="font-family: Arial; font-size: 8pt; color: #FFFFFF; text-decoration: none;">Settings</a>'+
			' | <a href="/SupportTopic.do?topic=im" style="font-family: Arial; font-size: 8pt; color: #FFFFFF; text-decoration: none;">Help</a>'+
			'</div>'+
			'</DIV>';

  function imAlert(){
	if (parent.document != null) parent.document.write(imBegin+imEnd);
	else document.write(imBegin+imEnd);
  }
  function writeTabLayer(name, w, h,z, handle, otherhandle){
	  if(handle.length>0&&otherhandle.length>0)
		document.write(imBegin+imMiddle(handle, otherhandle)+imEnd);
	  else
		document.write(imBegin+imEnd);
  }


	function requestIC( userID, destinationUserID )
	{
		var popupWindowTest = window.open( "/StartIM.do?otherhandle=" + destinationUserID+"&winOpener=main&recheck=true", "ICWindow_" + replaceAlpha(userID) + "_" + replaceAlpha(destinationUserID), "width=360,height=420,toolbar=0,directories=0,menubar=0,status=0,location=0,scrollbars=0,resizable=0" );
		if( popupWindowTest == null )
		{
		showHideLayer('imalert', 'visible');
		}else{
			popupWindowTest.focus();
		}
	}
	function showNewIM(handle, otherhandle) {
	  try{
		 parent.document.getElementById('imdhtml').innerHTML=imMiddle(handle, otherhandle);
		}catch(e) {}

	}

	function responseIC( userID, destinationUserID )
	{
		var popupWindowTest = window.open( "/ResponseIM.do?response=accept&otherhandle=" + destinationUserID+"&winOpener=iframe", "ICWindow_" + replaceAlpha(userID) + "_" + replaceAlpha(destinationUserID), "width=360,height=420,toolbar=0,directories=0,menubar=0,status=0,location=0,scrollbars=0,resizable=0" );
		if( popupWindowTest == null )
		{
			showNewIM(userID, destinationUserID);
			showHideLayer('imalert', 'visible');
		}else{
			showHideLayer('imalert', 'hidden');
			popupWindowTest.focus();
		}
	}
	function replaceAlpha( strIn )
	{
		var strOut = "";
		for( var i = 0 ; i < strIn.length ; i++ )
		{
			var cChar = strIn.charAt(i);
			if( ( cChar >= 'A' && cChar <= 'Z' )
				|| ( cChar >= 'a' && cChar <= 'z' )
				|| ( cChar >= '0' && cChar <= '9' ) )
			{
				strOut += cChar;
			}
			else
			{
				strOut += "_";
			}
		}

		return strOut;
	}

  // determine if is Windows IE (up_is_win_ie)
  var up_agt 			= navigator.userAgent.toLowerCase();
  var up_appVer 		= navigator.appVersion.toLowerCase();
  var up_is_mac 		= up_agt.indexOf('mac') != -1;
  var up_is_safari 	= up_agt.indexOf('safari') != -1 && up_is_mac;
  var up_is_khtml  	= up_is_safari || up_agt.indexOf('konqueror') != -1;
  var up_is_ie  	 	= up_appVer.indexOf('msie') != -1 && up_agt.indexOf("opera") == -1 && !up_is_khtml;
  var up_is_win   	= up_is_mac ? false : (up_agt.indexOf("win") != -1 || up_agt.indexOf("16bit") != -1);
  var up_is_win_ie 	= up_is_win && up_is_ie;


  var imrecheck=0;
  var up_iCheckSeconds = 15;
  var up_icCheckImage = null;
  var up_timeoutID = null;
  var newim='0';

  function up_checkIC()
  {
	  if( up_is_win_ie )
	  {
		imrecheck++;

		  up_icCheckImage = new Image();
		  up_icCheckImage.onLoad = up_onImageLoad();
		  up_icCheckImage.src = "/CheckPendingIM.do?recheck=" + imrecheck+"&rand="+Math.floor( Math.random() * 100000000000) ;
	  }
	  else
	  {
		getById("IM").src="/ListPendingIM.do?redirect=true";
	  }
  }

  function initIM() {
	//Preload images
	pixel1 = new Image();
	pixel1.src="/images/pixel1.jpg";
	pixel2 = new Image();
	pixel2.src="/images/pixel2.jpg";
	pixel3 = new Image();
	pixel3.src="/images/pixel3.jpg";

	if(newim=='0'){//No new IM
		  setTimeout("up_checkIC()", 1000 * up_iCheckSeconds);
	}else{ //New IM
	  up_checkIC();
	}
  }

  function up_onImageLoad()
  {
	  clearTimeout( up_timeoutID );

	  if (!up_icCheckImage.complete)
	  {
		  up_timeoutID = setTimeout("up_onImageLoad()", 250);
	  }
	  else
	  {
		  if( up_icCheckImage.height == 2)
		  {
			 getById("IM").src="/ListPendingIM.do?redirect=false";
			 up_timeoutID = setTimeout("up_checkIC()", 1000 * up_iCheckSeconds);
		  }
		  else if( up_icCheckImage.height == 3 )
		  {
		  try{
			document.getElementById('IM-interface').style.display='none';
			document.getElementById('temponline').style.display='none';
			document.getElementById('tempoffline').style.display='block';
		  }catch(e) {}
			 //Stop redirect
		  }
		  else
		  {
		  try{
			document.getElementById('IM-interface').style.display='none';
		  }catch(e) {}

			up_timeoutID = setTimeout("up_checkIC()", 1000 * up_iCheckSeconds);
		  }
	 }
  }

/** Validates Mobile Number. */
function validatePhoneNumberField(form, field, name, countryField) {
	var value = field.value;
	try {
		value = trim(value);
	} catch(e) {}
	field.value = value;

	if (value.length != 0) {
		var country = countryField.value;

		if ("US" == country || "CA" == country) {
			return validatePhoneNANPANumber(form, field, name);
		}

		var mask = /^[0-9]*$/
		if (!mask.test(value)) {
			alert(MSG_PHONE_NUMBER_VALID_CHARACTERS.replace('%1', name));
			try{field.focus();}catch(e){}
			return false;
		}

		if (value.length < 10) {
			var msg = MSG_MIN_LENGTH.replace('%1', name);
			msg = msg.replace('%2', 10);
			alert(msg);
			try{field.focus();}catch(e){}
			return false;
		}
		if (value.length > 26) {
			var msg = MSG_MAX_LENGTH.replace('%1', name);
			msg = msg.replace('%2', 26);
			alert(msg);
			try{field.focus();}catch(e){}
			return false;
		}
	}
	return true;
}

function validatePhoneNANPANumber(form, field, name) {

	var numPat=/^1[2-9][0-9][0-9][2-9][0-9][0-9][0-9][0-9][0-9][0-9]$/;
	// easily remembered area codes (ERCs) are reserved; this happens
	// to also cover emerg. numbers such as 911
	var erc = /^[2-9]([0-9])\1$/;
	// three blocks of area codes are reserved for future purposes
	var reservedAreaCodes =/^([2-9]9[0-9])|(37[0-9])|(96[0-9])$/;

	var num = field.value;
	if (!num.match(numPat)){
		alert(MSG_PHONE_NUMBER_NORTH_AMERICA_INVALID_FORMAT.replace('%1', name));
		var focusField = form[field.name + 0];
		try{focusField.focus();}catch(e){}
		return false;
	}
	var areaCode =num.substring(1,4);
	if (areaCode.match(reservedAreaCodes) || areaCode.match(erc)) {
		alert(MSG_PHONE_NUMBER_NORTH_AMERICA_RESERVED_AREA_CODE.replace('%1', name));
		var focusField = form[field.name + 0];
		try{focusField.focus();}catch(e){}
		return false;
	}
	return true;
}

/** Validates Mobile Number. */
function validateMobileNumberField(form, field, name, countryField) {
	var value = field.value;
	try {
		value = trim(value);
	} catch(e) {}
	field.value = value;

	if (value.length != 0) {
		var country = countryField.value;

		if ("US" == country || "CA" == country) {
			return validateNANPANumber(form, field, name);
		}

		var mask = /^[0-9]*$/
		if (!mask.test(value)) {
			alert(MSG_MOBILE_NUMBER_VALID_CHARACTERS.replace('%1', name));
			try{field.focus();}catch(e){}
			return false;
		}

		if (value.length < 10) {
			var msg = MSG_MIN_LENGTH.replace('%1', name);
			msg = msg.replace('%2', 10);
			alert(msg);
			try{field.focus();}catch(e){}
			return false;
		}
		if (value.length > 26) {
			var msg = MSG_MAX_LENGTH.replace('%1', name);
			msg = msg.replace('%2', 26);
			alert(msg);
			try{field.focus();}catch(e){}
			return false;
		}
	}
	return true;
}

function validateNANPANumber(form, field, name) {

	var numPat=/^1[2-9][0-9][0-9][2-9][0-9][0-9][0-9][0-9][0-9][0-9]$/;
	// easily remembered area codes (ERCs) are reserved; this happens
	// to also cover emerg. numbers such as 911
	var erc = /^[2-9]([0-9])\1$/;
	// three blocks of area codes are reserved for future purposes
	var reservedAreaCodes =/^([2-9]9[0-9])|(37[0-9])|(96[0-9])$/;

	var num = field.value;
	if (!num.match(numPat)){
		alert(MSG_MOBILE_NUMBER_NORTH_AMERICA_INVALID_FORMAT.replace('%1', name));
		var focusField = form[field.name + 0];
		try{focusField.focus();}catch(e){}
		return false;
	}
	var areaCode =num.substring(1,4);
	if (areaCode.match(reservedAreaCodes) || areaCode.match(erc)) {
		alert(MSG_MOBILE_NUMBER_NORTH_AMERICA_RESERVED_AREA_CODE.replace('%1', name));
		var focusField = form[field.name + 0];
		try{focusField.focus();}catch(e){}
		return false;
	}
	return true;

}


/** Validates Mobile Carrier. */
function validateMobileCarrierField(field, name, dv, numberField) {
	if (!isEmpty(numberField)) return validateRequiredSelect(field, name, dv)
	else return true;
}

/** Validates that at least one of the specified fields is changed */
function validateAtLeastOneFieldChanged(field,name,field2,name2) {
	if (!isTextChanged(field) && !isTextChanged(field2))
	{
			var msg = MSG_AT_LEAST_ONE_FIELD_CHANGED.replace('%1', name);
			msg = msg.replace('%2', name2)
		alert(msg);
		try{field.focus();}catch(e){}
		return false;
	}
	return true;
}

function isTextChanged(obj) {
	return(obj.value!=obj.defaultValue);
}

function characterCounter(fieldName, maxLength, elementName) {
	var field = getById(fieldName);
	var value = field.value.replace(/\n/g,'**'); // bug #4830 when the javascript validates it sees \n's and java validates it sees \r\n's so a string may pass javascript validation but fail java validation, solution validate on a copy of the string with all \n's replaced with 2 characters to simulate the java length
	getById(elementName).innerHTML = value.length;
}

function LTrim(str){
	if(str==null){
		return null;
	}
	for(var i=0;str.charAt(i)==" ";i++)
		;
	return str.substring(i,str.length);
}

function RTrim(str){
	if(str==null){
		return null;
	}
	for(var i=str.length-1;str.charAt(i)==" ";i--)
		;
	return str.substring(0,i+1);
}

function Trim(str){
	return LTrim(RTrim(str));
}

function isBlank(val){
	if(val==null){
		return true;
	}
	for(var i=0;i<val.length;i++){
		if((val.charAt(i)!=' ')
			&&(val.charAt(i)!="\t")
			&&(val.charAt(i)!="\n")
			&&(val.charAt(i)!="\r")){
			return false;
		}
	}
	return true;
}

function isArray(obj){
	return(typeof(obj.length)=="undefined")?false:true;
}

function commifyArray(obj,delimiter){
	if(typeof(delimiter)=="undefined" || delimiter==null){
		delimiter = ",";
	}
	var s="";
	if(obj==null||obj.length<=0){
		return s;
	}
	for(var i=0;i<obj.length;i++){
		s=s+((s=="")?"":delimiter)+obj[i].toString();
	}
	return s;
}


function getSingleInputValue(obj,use_default,delimiter){
	switch(obj.type){
		case 'radio':
		case 'checkbox':
			return(((use_default)?obj.defaultChecked:obj.checked)?obj.value:null);
		case 'text':
		case 'hidden':
		case 'textarea':
			return(use_default)?obj.defaultValue:obj.value;
		case 'password':
			return((use_default)?null:obj.value);
		case 'select-one':
			if(obj.options==null){
				return null;
			}
			if(use_default){
				var o=obj.options;
				for(var i=0;i<o.length;i++){
					if(o[i].defaultSelected){
						return o[i].value;
					}
				}
				return o[0].value;
			}
			if(obj.selectedIndex<0){
				return null;
			}
			return(obj.options.length>0)?obj.options[obj.selectedIndex].value:null;
		case 'select-multiple':
			if(obj.options==null){
				return null;
			}
			var values=new Array();
			for(var i=0;i<obj.options.length;i++){
				if((use_default&&obj.options[i].defaultSelected)||(!use_default&&obj.options[i].selected)){
					values[values.length]=obj.options[i].value;
				}
			}
			return(values.length==0)?null:commifyArray(values,delimiter);
	}
	return null;
}

function getInputValue(obj,delimiter){
	var use_default=(arguments.length>2)?arguments[2]:false;
	if(isArray(obj) &&(typeof(obj.type)=="undefined")){
		var values=new Array();
		for(var i=0;i<obj.length;i++){
			var v=getSingleInputValue(obj[i],use_default,delimiter);
			if(v!=null){
				values[values.length]=v;
			}
		}
		return commifyArray(values,delimiter);
	}
	return getSingleInputValue(obj,use_default,delimiter);
}

function getInputText(obj,delimiter){
	var use_default=(arguments.length>2)?arguments[2]:false;
	if(isArray(obj) &&(typeof(obj.type)=="undefined")){
		var values=new Array();
		for(var i=0;i<obj.length;i++){
			var v=getSingleInputText(obj[i],use_default,delimiter);
			if(v!=null){
				values[values.length]=v;
			}
		}
		return commifyArray(values,delimiter);
	}
	return getSingleInputText(obj,use_default,delimiter);
}

function getInputDefaultValue(obj,delimiter){
	return getInputValue(obj,delimiter,true);
}

function isChanged(obj) {
	return(getInputValue(obj)!=getInputDefaultValue(obj));
}

function isFormModified(theform,hidden_fields,ignore_fields) {
	if(hidden_fields==null){
		hidden_fields="";
	}
	if(ignore_fields==null){
		ignore_fields="";
	}
	var hiddenFields=new Object();
	var ignoreFields=new Object();
	var i,field;
	var hidden_fields_array=hidden_fields.split(',');
	for(i=0;i<hidden_fields_array.length;i++){
		hiddenFields[Trim(hidden_fields_array[i])]=true;
	}
	var ignore_fields_array=ignore_fields.split(',');
	for(i=0;i<ignore_fields_array.length;i++){
		ignoreFields[Trim(ignore_fields_array[i])]=true;
	}
	for(i=0;i<theform.elements.length;i++){
		var changed=false;
		var name=theform.elements[i].name;
		if(!isBlank(name)){
			var type=theform[name].type;
			if(!ignoreFields[name]){
				if(type=="hidden"&&hiddenFields[name]){
					changed=isChanged(theform[name]);
				} else if(type=="hidden"){
					changed=false;
				}else{
					changed=isChanged(theform[name]);
				}
			}
		}
		if(changed){
			return true;
		}
	}
	return false;
}

function updateMessageCounts(imcount,mailcount,interestcount) {
	try	{getByIdFromParent('imcount').innerHTML=imcount;} catch(e) {}
	try	{getByIdFromParent('mailcount').innerHTML=mailcount;} catch(e) {}
	try	{getByIdFromParent('interestcount').innerHTML=interestcount;} catch(e) {}
	try	{getByIdFromParent('homeimcount').innerHTML=imcount;} catch(e) {}
	try	{getByIdFromParent('homemailcount').innerHTML=mailcount;} catch(e) {}
	try	{getByIdFromParent('homeinterestcount').innerHTML=interestcount;} catch(e) {}
}

/*
 * uncheck multi-checkbox
 * if checked box is "Doesn't matter", uncheck all other checkbox
 * if checked box is other checkbox, uncheck "Doesn't matter"
 */
function groupUncheck (field) {
  if(field.checked == true) {
    var groupFields = document.getElementsByName(field.name);
    if(field.value == '00') {  // "Doesn't matter"
	  for(i=0; i< groupFields.length; i++) {
	    if(groupFields[i].value != '00') {
	      // Uncheck all other checkboxes
          groupFields[i].checked = false;
        }
      }
    } else { // Others
	  for(i=0; i< groupFields.length; i++) {
	    if(groupFields[i].value == '00') {
	      // Uncheck "Doesn't matter" checkbox
	      groupFields[i].checked = false;
        }
      }
    }
  }
}

function constructMobileNumber(form, fieldName, countryDialCode, numFields) {

	var field = form[fieldName];
	field.value = countryDialCode;
	for (var i = 0; i < numFields; i++) {
		field.value = field.value + trim(form[fieldName + i].value);
	}
	// if the fields are empty, unset the main form
	if (field.value.length == countryDialCode.length) {
		field.value = '';
	}
}











function blocking(tag)
{
	if (document.getElementById) //  Netscape, Mozilla, etc.
	{
		var state = document.getElementById(tag).style.display;
	}
	else if (document.all)      //  IE, Konqueror, etc.
	{
		var state = document.all[tag].style.display;
	}

	var newState = "";

	if ( state == "block")
	{
		newState = "none";
	} else {
		newState = "block";
	}
	if (document.getElementById)
	{
		document.getElementById(tag).style.display = newState;
	}
	else if (document.all)
	{
		document.all[tag].style.display = newState;
	}
}

function blockingall(tag)	{
	if (document.getElementById)	{
		var state = document.getElementById(tag).style.display;		}
	else if (document.all)	{
		var state = document.all[tag].style.display;	}
	var newState = "block";
	if (document.getElementById)	{
		document.getElementById(tag).style.display = newState;	}
	else if (document.all)	{
		document.all[tag].style.display = newState;	}
}

function hidingall(tag)	{
	if (document.getElementById)	{
		var state = document.getElementById(tag).style.display;		}
	else if (document.all)	{
		var state = document.all[tag].style.display;	}
	var newState = "none";
	if (document.getElementById)	{
		document.getElementById(tag).style.display = newState;	}
	else if (document.all)	{
		document.all[tag].style.display = newState;	}
}
