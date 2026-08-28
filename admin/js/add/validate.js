// checks that an input string looks like a valid email address.
var isEmail_re       = /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/;
function isEmail (s)
{
   return String(s).search (isEmail_re) != -1;
}

function isInteger(s)
{
	return (s.toString().search(/^[0-9]+$/) == 0);
}

// Check if string is non-blank
var isNonblank_re    = /\S/;
function isNonblank (s)
{
	return String (s).search (isNonblank_re) != -1
}

// Check if string is a whole number(digits only).
var isWhole_re       = /^\s*\d+\s*$/;
function isWhole (s)
{
	return String(s).search (isWhole_re) != -1
}

// check 0-9 digit
function regIsDigit(fData)
{
	var reg = new RegExp("^[0-9]$");
	return (reg.test(fData));
}

function loader(id)
{
	alert(id);
	$('#loader'+id).fadeIn(500);
	$('#loader'+id). animate({height: 'show', width: 'show', opacity: 'show'}, 1000);
	$('#errormsg'+id).fadeIn(500);
	$('#errormsg'+id).css('color','#f00');
	
	if(id>=2)
	{
		for(i=1;i<id;i++)
		{
			$('#loader'+i).fadeOut(500);
			$('#loader'+i). animate({height: 'hide', width: 'hide', opacity: 'hide'}, 1000);
			$('#errormsg'+i).fadeOut(500);
		}
	}
	return false;
}

function keyValid(e, validchars)
{ 
	var key='', keychar='';
	key = getKeyCode(e);
	if(key == null)
		return true;
	keychar = String.fromCharCode(key);
	keychar = keychar.toLowerCase();
	validchars = validchars.toLowerCase();
	if (validchars.indexOf(keychar) != -1)
	  return true;
	if ( key==null || key==0 || key==8 || key==9 || key==13 || key==27 )
	  return true;
	return false;
}
function keyRestrict(e, validchars)
{ 
	var key='', keychar='';
	key = getKeyCode(e);
	if(key == null)
		return true;
	keychar = String.fromCharCode(key);
	keychar = keychar.toLowerCase();
	validchars = validchars.toLowerCase();
	if (validchars.indexOf(keychar) == -1)
	  return true;
	if ( key==null || key==0 || key==8 || key==9 || key==13 || key==27 )
	  return true;
	return false;
}

function getKeyCode(e)
{
	if(window.event)
		return window.event.keyCode;
	else if(e)
		return e.which;
	else
		return null;
}
