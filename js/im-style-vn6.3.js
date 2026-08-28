/** Head JS     The only script in your <HEAD>
    Copyright   Tero Piirainen (tipiirai)
    License     MIT / http://bit.ly/mit-license
    Version     0.96
    http://headjs.com */
(function(a){function z(){d||(d=!0,s(e,function(a){p(a)}))}function y(c,d){var e=a.createElement("script");e.type="text/"+(c.type||"javascript"),e.src=c.src||c,e.async=!1,e.onreadystatechange=e.onload=function(){var a=e.readyState;!d.done&&(!a||/loaded|complete/.test(a))&&(d.done=!0,d())},(a.body||b).appendChild(e)}function x(a,b){if(a.state==o)return b&&b();if(a.state==n)return k.ready(a.name,b);if(a.state==m)return a.onpreload.push(function(){x(a,b)});a.state=n,y(a.url,function(){a.state=o,b&&b(),s(g[a.name],function(a){p(a)}),u()&&d&&s(g.ALL,function(a){p(a)})})}function w(a,b){a.state===undefined&&(a.state=m,a.onpreload=[],y({src:a.url,type:"cache"},function(){v(a)}))}function v(a){a.state=l,s(a.onpreload,function(a){a.call()})}function u(a){a=a||h;var b;for(var c in a){if(a.hasOwnProperty(c)&&a[c].state!=o)return!1;b=!0}return b}function t(a){return Object.prototype.toString.call(a)=="[object Function]"}function s(a,b){if(!!a){typeof a=="object"&&(a=[].slice.call(a));for(var c=0;c<a.length;c++)b.call(a,a[c],c)}}function r(a){var b;if(typeof a=="object")for(var c in a)a[c]&&(b={name:c,url:a[c]});else b={name:q(a),url:a};var d=h[b.name];if(d&&d.url===b.url)return d;h[b.name]=b;return b}function q(a){var b=a.split("/"),c=b[b.length-1],d=c.indexOf("?");return d!=-1?c.substring(0,d):c}function p(a){a._done||(a(),a._done=1)}var b=a.documentElement,c,d,e=[],f=[],g={},h={},i=a.createElement("script").async===!0||"MozAppearance"in a.documentElement.style||window.opera,j=window.head_conf&&head_conf.head||"head",k=window[j]=window[j]||function(){k.ready.apply(null,arguments)},l=1,m=2,n=3,o=4;i?k.js=function(){var a=arguments,b=a[a.length-1],c={};t(b)||(b=null),s(a,function(d,e){d!=b&&(d=r(d),c[d.name]=d,x(d,b&&e==a.length-2?function(){u(c)&&p(b)}:null))});return k}:k.js=function(){var a=arguments,b=[].slice.call(a,1),d=b[0];if(!c){f.push(function(){k.js.apply(null,a)});return k}d?(s(b,function(a){t(a)||w(r(a))}),x(r(a[0]),t(d)?d:function(){k.js.apply(null,b)})):x(r(a[0]));return k},k.ready=function(b,c){if(b==a){d?p(c):e.push(c);return k}t(b)&&(c=b,b="ALL");if(typeof b!="string"||!t(c))return k;var f=h[b];if(f&&f.state==o||b=="ALL"&&u()&&d){p(c);return k}var i=g[b];i?i.push(c):i=g[b]=[c];return k},k.ready(a,function(){u()&&s(g.ALL,function(a){p(a)}),k.feature&&k.feature("domloaded",!0)});if(window.addEventListener)a.addEventListener("DOMContentLoaded",z,!1),window.addEventListener("load",z,!1);else if(window.attachEvent){a.attachEvent("onreadystatechange",function(){a.readyState==="complete"&&z()});var A=1;try{A=window.frameElement}catch(B){}!A&&b.doScroll&&function(){try{b.doScroll("left"),z()}catch(a){setTimeout(arguments.callee,1);return}}(),window.attachEvent("onload",z)}!a.readyState&&a.addEventListener&&(a.readyState="loading",a.addEventListener("DOMContentLoaded",handler=function(){a.removeEventListener("DOMContentLoaded",handler,!1),a.readyState="complete"},!1)),setTimeout(function(){c=!0,s(f,function(a){a()})},300)})(document)
browserName=navigator.appName;browserVer=parseInt(navigator.appVersion); condition=!(browserName.indexOf("Explorer")>=0&&browserVer<4||browserName.indexOf("Netscape")>=0&&browserVer<2);CanAnimate=condition==!0?!0:!1;
version = parseFloat(navigator.appVersion.split("MSIE")[1]);
/* variables */
var webAddress=location.hostname;
var UrlPri =webAddress.match(/^dev/)?"dev-":(webAddress.match(/^stg/))?"stg-":"";
var UrlPri2 =webAddress.match(/^dev/)?"dev.":(webAddress.match(/^stg/))?"stg.":""; 

/* imlogin.js ondemand*/
$(window).load(function()
{if(typeof(_Login_initial) === "undefined")
{ _Login_initial = true;$.ajaxSetup({ cache: true });$.getScript('http://'+UrlPri+'utils.imimg.com/header/js/imlogin.js', function() { FreeWebPopup();});} 
$(".image_grid li").load(function() {}).css('background-image', 'url(http://hm.imimg.com/gifs/big-buyers-logo-vn4.jpg)');
$(".ctrBx li").load(function() {}).css('background-image', 'url(http://hm.imimg.com/gifs/ctry-img.jpg)');
$(".custImg").load(function() {}).css('background-image', 'url(http://hm.imimg.com/gifs/1lac-paid-customers-v1.png)');
$(".bg2").load(function() {}).css('background-image', 'url(http://hm.imimg.com/gifs/hm-slr-bg1.jpg)');
$(".bg3").load(function() {}).css('background-image', 'url(http://hm.imimg.com/gifs/hm-slr-bg2.jpg)');
$(".bg4").load(function() {}).css('background-image', 'url(http://hm.imimg.com/gifs/hm-slr-bg3.jpg)');
$(".bg2,.bg3,.bg4").load(function() {}).css('background-size', 'cover');

});
function user_signIn(type)
{if(type==null){type=="";}
if(typeof(_Login_initial) === "undefined"){
	  _Login_initial = true;$.ajaxSetup({ cache: true });$.getScript('http://'+UrlPri+'utils.imimg.com/header/js/imlogin.js', function()
	  {signIn('','','',type);FreeWebPopup();});} else { signIn('','','',type); }}
function user_register()
{if(typeof(_Login_initial) === "undefined"){ _Login_initial = true;$.ajaxSetup({ cache: true }); $.getScript('http://'+UrlPri+'utils.imimg.com/header/js/imlogin.js', function(){
register();FreeWebPopup();});}else{ register();}}

$(function () { 
$('.hwrk').click(function () {
$('#vdo').html('<iframe width=\'780\' height=\'439\' id=\'iiv\' src=\'https://www.youtube.com/embed/Xfz8BkuHpSY\'></iframe> <div class=\'fs16 tc pdtb\'><a href=\'https://www.youtube.com/embed/90cNKXS6bHo\' target=\'_blank\' class=\'cf\'>Watch Hindi version &#187; </a></div>'); 
});
});

function readCookie(name){
var search = name + "="
	if (document.cookie.length > 0)
	{ 
		offset = document.cookie.indexOf(search)
		if (offset != -1) // if cookie exists
		{ 
			offset += search.length
			end = document.cookie.indexOf(";", offset)	// set index of beginning of value
			if (end == -1) end = document.cookie.length	// set index of end of cookie value
			return unescape(document.cookie.substring(offset, end))
		}
	}

	if (name == 'v4iilex')
	{
			return readCookie('v4iil');
	}

	return "";
}

function getparam(key)
{
	if( (cookie = readCookie("v4iilex") || readCookie("ImeshVisitor"))  > "")	
	{
		var val = "|"+cookie+"|";
		var pattern = new RegExp(".*?\\|"+key+"=([^|]*).*|.*");
		return val.replace(pattern, "$1");
	}
}

function getparamVal(cookieStr, key)
{
	if( cookieStr > "")	
	{
		var val = "|"+cookieStr+"|";
		var pattern = new RegExp(".*?\\|"+key+"=([^|]*).*|.*");
		return val.replace(pattern, "$1");
	}
	else 
	{
		return "";
	}
}

/* Buylead not me merged with top not me link*/
var page = {};page.notMeQ = new Array();
page.notMe = function () {for (var j=0; j< page.notMeQ.length; j++){page.notMeQ[j].apply();}}
function notmep(){deleteCookie('ImeshVisitor');deleteCookie('xnHist');deleteCookie('v4iilex');getLoginStringv1();header_dropdown();datacoming=0;paid_user();$('#joinf').css('display','block');$('.blstp').css('display','block');}
page.notMeQ.push(notmep);
/* Buylead not me merged with top not me link*/

function deleteCookie(name) {document.cookie = name+"=;expires=;domain=.indiamart.com;path=/;";}
function getLoginStringv1(){
/* To read user type ex- S, B, SB,NA start*/
if( (cookie = readCookie("ImeshVisitor")) != ""){var userType='_'+getparamVal(cookie, "int")}else{userType = ''}
var value = readCookie('v4iilex');
if( (cookie = readCookie("ImeshVisitor")) != ""){var useremail=getparamVal(cookie, "em")}else{useremail = ''}
/* To read user type ex- S, B, SB,NA end
var log_user ="";*/

var emplogin = "";emplogin =readCookie('adminiil');
var value = "";
value = readCookie('v4iilex');
cookie = readCookie("ImeshVisitor");

if(value == "" && cookie == ''){$('#loading_hp1').css('display','none');}
if((cookie!= "" && value=="")){var fullname='<span class="pp1 nme"><span class="tlc">Hi </span>'+getparamVal(cookie, "fn")+'</span>';log_user=getparamVal(cookie, "fn");$('#joinf').css('display','none');$('#notification').css('display','block');$('.hdr').addClass("afterlogin");}
else if((cookie== "" && value!="")){var fullname='<span class="pp1 nme"><span class="tlc">Hi </span>Guest</span>';log_user=getparamVal(cookie, "fn");}
else if((cookie!= "" && value!="")){var fullname='<span class="pp1 nme"><span class="tlc">Hi </span>'+getparamVal(cookie, "fn")+'</span>';log_user=getparamVal(cookie, "fn");$('#joinf').css('display','none');$('#notification').css('display','block');$('.hdr').addClass("afterlogin");}
else{fullname = '';log_user=''}
var url = document.URL;
var redirect = "";
if(url.match('/cgi/')){redirect = '/';}else{if(url.match('signout.html'))
{redirect = '/';}else{redirect = escape(url);}}

if(emplogin!=""){var signout = '<li class="signout last cp" > <A onclick="deleteCookie(\'v4iilex\');deleteCookie(\'adminiil\');deleteCookie(\'ImeshVisitor\');deleteCookie(\'xnHist\');gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header_Dropdown_Signin\', \'Sign_Out\', 0);location.reload();"  rel="nofollow">Sign Out</A></li>'; }
else if(value != ""){var signout = '<li class="signout last cp" > <A onclick="deleteCookie(\'v4iilex\');gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header_Dropdown_Signin\', \'Sign_Out\', 0);location.reload();"  rel="nofollow" >Sign Out</A></li>';}else{signout = '';}

var upg_mem='<li><a id="ch_upg_mem" href="http://www.indiamart.com/seller/?services=paid" rel="nofollow" onClick="gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header_Dropdown_Sell\', \'Upgrade_Your_Membership\', 0);">Upgrade Your Membership</a></li>';

if(value == "" && fullname != ""){var notme = '<li class="signout last cp" ><A HREF="javascript:" target="_top" rel="nofollow" class="last" onclick="gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header_Dropdown_Signin\', \'Not me\', 0);page.notMe();">Not Me</A></li>';}else{notme='';} 

var sell='<a href="http://www.indiamart.com/seller/" onClick="gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header_Dropdown_Sell\', \'Sell\', 0);" style="margin-top:1px"><span>Sell</span>';
var selld='<span class="arwd">&#9662;</span></a><ul class="mr-2" style="left:125px"><li><a rel="nofollow" href="http://indiamart.com/free-website/" onClick="gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header_Dropdown_Sell\', \'Create Free Website\', 0);">Create Free Website</a><a rel="nofollow" href="http://my.indiamart.com/product/manageproduct/addnew" onClick="gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header_Dropdown_Sell\', \'Display Free Products\', 0);" class="ch_dis_pro">Display Free Products</a><a rel="nofollow" href="http://www.indiamart.com/seller/?services=paid" onClick="gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header_Dropdown_Sell\', \'View Paid Memberships\', 0);" class="last">View Paid Memberships</a></li></ul>';

var comn_links='<li><A HREF="http://my.indiamart.com/" class="ch_my_dash" onclick="gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header_Dropdown_Signin\', \'My_Dashboard\', 0);">My Dashboard</a></li><li><a href="http://my.indiamart.com/enquiry/inbox/" class="ch_my_enq" rel="nofollow" onclick="gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header_Dropdown_Signin\', \'My_Inbox\', 0);">My Enquiries </a></li><li><a href="http://my.indiamart.com/bltxn/latestbl?modid=IMHOME" class="bl_log_link" rel="nofollow" onclick="gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header_Dropdown_Signin\', \'Buy_Leads\', 0);">Latest Buy leads For Me</a></li><li><a href="http://my.indiamart.com/tenders/tender" rel="nofollow"  class="ch_lst_tend" onclick="gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header_Dropdown_Signin\', \'Latest Tenders\', 0);">Latest Tenders For Me</a></li>';

var sign_wot_fcp='<span class="arwd">&#9662;</span><ul class="snt" id="sntid"><li class="hd_menu"> <b>Buy</b></li><li><a id="ch_mang_buy_req" class="pdl" href="http://my.indiamart.com/blgen/managebl/" rel="nofollow" onclick="gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header_Dropdown_Signin\', \'Manage_Buy_Requirements\', 0);">Manage Buy Requirements</a></li><li><a id="ch_mang_buy_req" class="pdl" href="http://my.indiamart.com/companyprofile/myproductbuy?modid=IMHOME" rel="nofollow" onclick="gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header_Dropdown_Signin\', \'Products We Buy\', 0);">Products We Buy</a></li><li class="hd_menu"> <b>Sell</b></li>'+comn_links+'<li><a class="ch_free_web pdl" href="http://indiamart.com/free-website/" onClick="gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header_Dropdown_Signin\', \'Create Free Website\', 0);">Create Free Website</a></li><li><a class="pdl ch_dis_pro" href="http://my.indiamart.com/product/manageproduct/addnew/" rel="nofollow" onClick="gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header_Dropdown_Signin\', \'Display Free Products\', 0);">Display Free Products</a></li>'+signout+notme+'</ul>';

var sign_w_fcp='<span class="arwd">&#9662;</span><ul class="snt wfcp" id="sntid">'+comn_links+upg_mem+signout+notme+'</ul>';

var fcp_imurl = getparamVal(decodeURIComponent(cookie),'imurl');
if(fcp_imurl != ""){sign_wot_fcp='';var pd1='pdl ';}else{sign_w_fcp='';pd1='';;}

if(value == "" && fullname == ''){var newuser = '<A HREF="javascript:user_signIn();" target="_top" rel="nofollow" onclick="gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header_Dropdown_Signin\', \'Sign_In\', 0);">Sign In</A>';
$('.bl_log_link').attr('href','http://trade.indiamart.com/buy.html?modid=IMHOME');$('#notification').css('display','none');$('.hdr').removeClass("afterlogin");}
else{newuser= '';selld='';$('.bl_log_link').attr('href','http://my.indiamart.com/bltxn/latestbl?modid=IMHOME');$('#notification').css('display','block');$('.hdr').addClass("afterlogin");}

var dropddn="";
if(fcp_imurl != ""){dropddn=sign_w_fcp;} else if(fcp_imurl == "" && cookie != ""){dropddn=sign_wot_fcp;}else{}

$('#lshead').html(''+newuser+fullname+''+dropddn+'</li>');
$('#selld').html(''+sell+selld+'</li>');
$('#joinf').html('<a href="javascript:user_register();" id="join" onclick="gatrack(this, \'IM_Homepage'+userType+'\', \'Top_Header\', \'Join Free\', 0);">Join Free</a>');

$('#fullname').html('Name : '+fullname+'');

header_dropdown();}

function hd_tollFree(){var toll_free='';var myDate = new Date();var gmt = -myDate.getTimezoneOffset()/60;if(gmt == 5.5){
toll_free = '096-9696-9696';$(".appps").append(" <a class=\"hd-dw-apps cp\" onClick=\"gatrack(this, 'IM_Homepage'+userType+'', 'Top_Header','Download_App', 0);\"> <span class=\"capp\"></span>Download App</a>");}
else{toll_free = '+91-96-9696-9696';$(".appps").append(" <a class=\"hd-dw-apps cp\" onClick=\"gatrack(this, 'IM_Homepage'+userType+'', 'Top_Header','Download_App', 0);\" href=\"http://www.indiamart.com/mobile\" target=\"_blank\"> <span class=\"capp\"></span>Download App</a>");}
toll_free = $('<div />').html(toll_free).text();$('#hd_tollFree').html(toll_free).text();$('#hd_tollFree1').html(toll_free).text();}

function CheckDataSearch(Form)
{
try{sugg.recentSearches(Form.ss.value)}catch(e){}
var re = /(Enter\s+.*?Search)/i;
if (Form.ss.value.match( re ))
{	
	var found = Form.ss.value.replace('...', ' ');
	found = found.replace(/\s+$/g, '');
	alert("Please enter a valid text to search.");
	Form.ss.focus();
	return false;
}
while(Form.ss.value.indexOf('  ')>0){
Form.ss.value = Form.ss.value.replace('  ',' ');
}
if (Form.ss.value.replace(/\s/g, '').match(/^[^0-9a-zA-Z ]+$/)){
	alert("Enter at least one alphanumeric characters for search.");
	Form.ss.focus();
	return false;
}
var temp=Form.ss.value.replace(/\s/g, '');
if (temp.length < 1){
	alert("Please enter a valid text to search.");
	Form.ss.focus();
	return false;
}
else if (Form.ss.value.replace(/\s/g, '').length > 119){
		alert("Enter less than 120 characters for search.");
		Form.ss.focus();
		return false;
}
else
{
	var str = Form.ss.value.replace(/\s/g, '+');

	if(Form.txv.value == "Suppliers" || Form.txv.value == "Products"){
	str ='ss='+escape(str);
	str = "http://dir.indiamart.com/search.mp?"+str;
	str = myReplace(str,"\\\\?\\\\&","?");
	window.location = str;
	return false;
} 
	else if(Form.txv.value == "Tenders"){
	str ='ss='+escape(str);
	str = "http://tenders.indiamart.com/search.cgi?"+str;
	str = myReplace(str,"\\\\?\\\\&","?");
	window.location = str;
	return false;
}
	else if(Form.txv.value == "Buy Leads"){
	str ='search='+escape(str);
	str = "http://trade.indiamart.com/buyersearch.mp?"+str;
	str = myReplace(str,"\\\\?\\\\&","?");
	window.location = str;
	return false;
}
}}
function myReplace(str, a, b) {var re = new RegExp(a, "g");var ret = str.replace(re,b);return ret;}

/*********** New JS Start::************/
$(document).ready(function() { $("#dd dt").click(function() { $("#dd dd ul").toggle(); $("#dd dt").toggleClass('act'); return false }); $("#dd dd ul li a").click(function() { var text = $(this).html(); $("#dd dt").html(text); $("#hdr_frm input[name=txv]").val(text);$("#dd dd ul").hide(); $("#dd dt").removeClass('act'); $("#search_string").focus();}); $("#dd").mouseleave(function() { $("#dd dd ul").hide(); $("#dd dt").removeClass('act'); }); });


function header_dropdown(){
$(function () { $('.tnb ul li').hover( function () { $('.sub_menu', this).stop(true, true).slideDown(0); if($.browser.msie && $.browser.version=="6.0"){$('.sub_menu', this).css('top', '25')} }, function () { $('.sub_menu', this).stop(true, true).slideUp(50); });});
}
$(document).ready(function(){header_dropdown();});

$(function () { $('#hdr_frm, #hdr_frm1').attr('autocomplete', 'off');});

function inreplc(idd,textt)
{textt=textt.replace(/&li;/g,'<');
textt=textt.replace(/&gt;/g,'>');
document.getElementById(idd).innerHTML=textt;
}

/*Survey only S*/
$(document).ready(function() {	
if (screen.width<1335) {
$('#modal').hide();
}
else{$('#modal').show();}
$('a[name=modal]').click(function(e) {
e.preventDefault();
var id = $(this).attr('href');
var maskHeight = $(document).height();
var maskWidth = $(window).width();
$('#mask').css({'width':maskWidth,'height':maskHeight});
$('#mask').fadeIn(0);	
$('#mask').fadeTo("fast",0.7);	
$('#boxes').show();
$('#div-gpt-ad-1390300604014-0').hide();
$(function () { $('#ss-form').attr('autocomplete', 'off');});
var winH = $(window).height();
var winW = $(window).width();
$(id).css('top',' 30px');
$(id).css('left', winW/2-$(id).width()/2);
$(id).fadeIn(0); 
});
$('.close').click(function (e) {
e.preventDefault();
$('#mask').hide();
$('.window').hide();
$('#div-gpt-ad-1390300604014-0').show();
$('#div3').empty();$('#div4').empty();
});	
	
$('#mask').click(function () {
$(this).hide();
$('.window').hide();
$('#div3').empty();$('#div4').empty();
$('#div-gpt-ad-1390300604014-0').show();
});		

$(window).resize(function () {
var box = $('#boxes .window');
var maskHeight = $(document).height();
var maskWidth = $(window).width();
$('#mask').css({'width':maskWidth,'height':maskHeight});
var winH = $(window).height();
var winW = $(window).width();
box.css('top',  winH/2 - box.height()/2);
box.css('left', winW/2 - box.width()/2);
});
$(document).keyup(function(e) {if (e.keyCode == 27){$('#div3').empty();$('#div4').empty();}});
});

/*Survey only E*/

function fs_banner()
{
onResize = function() { 
var winW = $(window).width()-1020;
$('.bnr_lft, .bnr_ryt').css('width', winW/2);
$('.bnr_lft').css('margin-left', -winW/2);
 }

$(document).ready(onResize);

$(window).bind('resize', onResize);

if(screen.width>=1335)
{document.getElementById('fs_banner').style.display="block";}
else
{document.getElementById('fs_banner').style.display="none";}
}

$(function () { $('.g-page1').html('<div class="g-page"  data-href="https://plus.google.com/110629529291071094871" data-layout="landscape" data-rel="publisher"></div>'); 
});

$(window).scroll(function(){
    if ($(window).scrollTop() >= 300) {
		$('.bnr_lft, .bnr_ryt').addClass('fix');
    }
    else {
		$('.bnr_lft, .bnr_ryt').removeClass('fix');
    }
});

(function(aaa){aaa.fn.vTicker=function(bbb){var ccc={speed:700,pause:4000,showItems:3,animation:"",mousePause:true,isPaused:false,direction:"up",height:0};var bbb=aaa.extend(ccc,bbb);moveUp=function(ggg,ddd,eee){if(eee.isPaused){return}var fff=ggg.children("ul");var hh=fff.children("li:first").clone(true);if(eee.height>0){ddd=fff.children("li:first").height()}fff.animate({top:"-="+ddd+"px"},eee.speed,function(){aaa(this).children("li:first").remove();aaa(this).css("top","0px")});if(eee.animation=="fade"){fff.children("li:first").fadeOut(eee.speed);if(eee.height==0){fff.children("li:eq("+eee.showItems+")").hide().fadeIn(eee.speed)}}hh.appendTo(fff)};moveDown=function(ggg,ddd,eee){if(eee.isPaused){return}var fff=ggg.children("ul");var hh=fff.children("li:last").clone(true);if(eee.height>0){ddd=fff.children("li:first").height()}fff.css("top","-"+ddd+"px").prepend(hh);fff.animate({top:0},eee.speed,function(){aaa(this).children("li:last").remove()});if(eee.animation=="fade"){if(eee.height==0){fff.children("li:eq("+eee.showItems+")").fadeOut(eee.speed)}fff.children("li:first").hide().fadeIn(eee.speed)}};return this.each(function(){var fff=aaa(this);var eee=0;fff.css({overflow:"hidden",position:"relative"}).children("ul").css({position:"absolute",margin:0,padding:0}).children("li").css({margin:0,padding:0});if(bbb.height==0){fff.children("ul").children("li").each(function(){if(aaa(this).height()>eee){eee=aaa(this).height()}});fff.children("ul").children("li").each(function(){aaa(this).height(eee)});fff.height(eee*bbb.showItems)}else{fff.height(bbb.height)}var ddd=setInterval(function(){if(bbb.direction=="up"){moveUp(fff,eee,bbb)}else{moveDown(fff,eee,bbb)}},bbb.pause);if(bbb.mousePause){fff.bind("mouseenter",function(){bbb.isPaused=true}).bind("mouseleave",function(){bbb.isPaused=false})}})}})(jQuery);

$(window).load(function() {
setTimeout(function () {
  $('#latest_bl').vTicker({
       speed: 500,
       pause: 3000,
       animation: 'fade',
       mousePause: true,
       showItems: 	3
   });

 }, 2000);
 
});

/*App banner
//New JS FOR App Fix Banner*/
function setCookie(key, value) {
var date = new Date();var hours = date.getHours();var minutes = date.getMinutes();var seconds = date.getSeconds();var total_sec_in_a_day = 24*60*60;var current_sec = (hours*60*60)+(minutes*60)+seconds;var rest_sec = total_sec_in_a_day-current_sec;var expire1 = new Date();expire1.setTime(date.getTime() + rest_sec * 1000);
document.cookie = key + '=' + value + ';expires=' + expire1.toGMTString();
}

function setCookie1(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toGMTString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}
/*Script for login popup*/
function userDataCookie() {
    this.get = function(name) {
        name = name || 'ImeshVisitor';
        var iMesh;
        var ca = new Array();
        ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var item = ca[i];
            if (item.replace(/^\s+|\s+$/g, "")
                .split('=')[0] == name) {
                item = unescape(item);
				var pos = item.indexOf('ImeshVisitor=');
				iMesh = item.substring(pos+13);
            };
        }
        if (iMesh) {
            return strToObj(iMesh);
        } else {
            return "";
        }
    }
    this.set = function(userObj, name) {
        name = name || 'ImeshVisitor';
        var cookObj;
        var myObj;
        if (name == 'xnHist') {
            cookObj = {
                pv: '1',
                city: '1',
		        cvstate: '1',
		        ss: '1',
		        popupshown: '1'
            };
            myObj = new userDataCookie().get(name);
            for (var key in cookObj) {
                if (cookObj[key] && userObj[key] != '' && userObj[key] != 0) 
                cookObj[key] = (userObj[key] || myObj[key]);
                else 
                cookObj[key] = userObj[key];
            }
        } else if (name == 'ImeshVisitor') {
             cookObj = {fn: '1',ln: '1',em: '1',ph1: '1',ph2: '1',phcc: '1',phac: '1',co: '1',url: '1',cn: '1',iso: '1',
mb1: '1',mb2: '1',ad: '1', ct: '1',ctid: '1',st: '1',stid: '1',zp: '1',glid: '1', nm: '1',int: '1',cd: '1'};
            myObj = new userDataCookie().get();
            for (var key in cookObj) {
                if (cookObj[key]) 
                cookObj[key] = (userObj[key] || myObj[key]) || '';
            }
            if (cookObj.nm) {
                var flname = new Array();
                var str = new Array();
                str = cookObj.nm.split(/\s+/);
                flname.push(str.shift());
                flname.push(str.join(' '));
                if (!cookObj.fn) {
                    cookObj['fn'] = flname[0];
                }
                if (!cookObj.ln) {
                    cookObj['ln'] = flname[1];
                }
            } else {
                if (cookObj['fn'] && cookObj['ln']) 
                cookObj['nm'] = cookObj['fn'] + ' ' + cookObj['ln'];
                else {
                    if (!cookObj['ln']) cookObj['nm'] = cookObj['fn'];
                    else cookObj['nm'] = cookObj['ln'];
                }
            }
            cookObj['cd'] = new Date();
        }
        newCookie = objToStr(cookObj);
        expires = new Date();
        expires.setTime(expires.getTime() + 24 * 60 * 60 * 180 * 1000);
        document.cookie = "" + name + "=" + escape(newCookie) + ";" + "expires=" + expires.toGMTString() + ";" + "domain=.indiamart.com;path=/;";
    }

    function objToStr(userObj) {
        var newCookie = new Array();
        for (var key in userObj) {
            newCookie.push(key + '=' + userObj[key]);
        }
        newCookie = newCookie.join('|');
        return newCookie;
    }

    function strToObj(str) {
        var arr = new Array();
        arr = str.split('|');
        var obj = {};
        for (i = 0; i < arr.length; i++) {
            var item = arr[i];
            obj[item.split('=')[0]] = item.split('=')[1];
        }
        return obj;
    }
    this.remove = function() {
        document.cookie = "ImeshVisitor=;expires=;domain=.indiamart.com;path=/;";
        document.cookie = "v4iilex=;expires=;domain=.indiamart.com;path=/;";
    }
}

/* For paid user section -----------------------------------------------------------------------------------*/

var datacoming = 0;
/*var nodata =1;*/
function paid_user()
{
/* To read user type ex- S, B, SB,NA start*/
if( (cookie = readCookie("ImeshVisitor")) != ""){var userType='_'+getparamVal(cookie, "int")}else{userType = ''}
/* To read user type ex- S, B, SB,NA end*/

var value = readCookie('v4iilex');
if( (cookie = readCookie("ImeshVisitor")) != ""){var useremail=getparamVal(cookie, "em")}else{useremail = ''}
var supp_typ='';
if(useremail != ''){
gatrack(this, 'IM_SupplierDashboard_DBoardMade', 'ViewCount');
$( "#bnr" ).css('display','none');$( ".sldrBx" ).css('display','none');
$.ajax(
{
url: "http://my.indiamart.com/dashboard/imhomedb?mail="+useremail+"",
dataType: "jsonp",
jsonpCallback:'Supplier_im_home',
	error: function(xhr, textStatus, errorThrown){ 
		$('#bnr').css('display','block');$('#frm').css('display','none');$('#tst').css('display','none');$('.blstp').css('display','none');$('#ctry,.t12_frmTx').css('margin-top','0');$( ".sldrBx" ).css('display','block');
	$('.sdw').css('box-shadow','0 7px 10px #ccc');$('.t12_frmTx').css('padding','0'); 

	$('#loading_hp1').css('display','none');
		},
success: function (data){
if(!(cookie = readCookie("ImeshVisitor")))
 {
 $('#pu_sec').css('display','none');$('#bnr').css('display','block');$('#frm').css('display','block');$('#tst').css('display','block');$('.blstp').css('display','none');$('#ctry,.t12_frmTx').css('margin-top','20px');$('.sdw').css('box-shadow','0 7px 10px #ccc');$('.t12_frmTx').css('padding','17px 0 0');$('.hwrk').css('display','block');$('.lgn_srh').css('display','none');$('.hdlft').removeClass( "lghd" );$('.pstBuy').css('display','none');$( ".sldrBx" ).css('display','block');

   return;
 }
datacoming = 1;
//json_obj = data;

if(JSON.stringify(data) != "No Data") {
	gatrack(this, 'IM_SupplierDashboard_DBoardServed'+userType, 'ViewCount');
	$('#pu_sec').css('display','block');$('#bnr').css('display','none');$('#frm').css('display','none');$('#tst').css('display','none');$('.blstp').css('display','none');$('#ctry,.t12_frmTx').css('margin-top','0');
	$('.sdw').css('box-shadow','0 7px 10px #ccc');$('.t12_frmTx').css('padding','0');$('.hwrk').css('display','none');$('.lgn_srh').css('display','block');$('.hdlft').addClass( "lghd" );$('.pstBuy').css('display','block');$( ".sldrBx" ).css('display','none');
	
	$('#loading_hp1').css('display','none');
}
var html = '';

$("#pstBuy").append("<a onClick=\"gatrack(this, 'IM_Homepage'+userType+'', 'Top_Header','Get Quotes Now', 0);\" class=\"ih-pbr ch_post_buy pstBuy sellNow1\">Get Quotes Now</a>");

	
	
//conditions start for showing supplier dashboard

var numcnt=Math.min(data.enquiry.enquiry_data.length,data.buy_lead.buylead_data.length);

if(numcnt==2)
{
$('#mweb').css('display','none');$('#mpro').removeClass('bdot');$('.imesh_view').css('padding-top','60px');$('.imesh_view').css('min-height','175px');

}
else if(numcnt==3)
{
$('#mweb').css('display','block');$('#mpro').addClass('bdot');

}

//conditions ending for showing supplier dashboard


/*My buylead_data section*/

$("#my_buylead").append("<a onclick=\"gatrack(this, 'IM_SupplierDashboard_LatestBLPage_"+data.supplier_type+""+userType+"', '"+data.buy_lead.buylead_rdlink+"');\"  href=\""+data.buy_lead.buylead_rdlink+"\" class=\"cp lkc dhd bbtm db\"> <span>Latest Buy Leads </span><span class=\"cm\" id=\"lbl\"></span></a>"); 


$("#lbl").append("("+data.buy_lead.total_bl+")");

var j;
for (j = 0; j < numcnt; j++) { 
var city = data.buy_lead.buylead_data[j].GLUSR_CITY;
var state =" , "+data.buy_lead.buylead_data[j].GLUSR_STATE;
var country = "";
if(data.buy_lead.buylead_data[j].GLUSR_CITY == null)
{
city="";
}
if(data.buy_lead.buylead_data[j].GLUSR_STATE == null)
{
state="";
}
if((data.buy_lead.buylead_data[j].GLUSR_CITY == null) && (data.buy_lead.buylead_data[j].GLUSR_STATE == null))
{
country=data.buy_lead.buylead_data[j].GLUSR_COUNTRY;
}

/* for hiding devider line in case of city and state= null*/
var city_state = "<span class=\"cm bo\">"+city+state+"</span>";
if((data.buy_lead.buylead_data[j].GLUSR_CITY == null) && (data.buy_lead.buylead_data[j].GLUSR_STATE == null) && (data.buy_lead.buylead_data[j].GLUSR_COUNTRY != null))
{
city_state = "<span class=\"cm bo\">"+country+"</span>";
}
if((data.buy_lead.buylead_data[j].GLUSR_CITY == null) && (data.buy_lead.buylead_data[j].GLUSR_STATE == null) && (data.buy_lead.buylead_data[j].GLUSR_COUNTRY == null)){
city_state ="<span class=\"cm bo\">"+city+state+"</span>";
}
var quanty=data.buy_lead.buylead_data[j].ETO_OFR_QTY;
var quant = "<span class=\"fl db\"><b>Quantity :</b> "+quanty+"</span><br>";
if(quanty!=null){quanty=quanty.replace(/\s+/g,'');} 
if((quanty == null) || quanty == ''){quant= "";}

/* condition for verified icon*/

var verified = "";
if((data.buy_lead.buylead_data[j].ETO_OFR_VERIFIED==1) || (data.buy_lead.buylead_data[j].ETO_OFR_VERIFIED==2) || (data.buy_lead.buylead_data[j].ETO_OFR_VERIFIED==3))
{verified="<img src=\"http://hm.imimg.com/gifs/z.gif\" height=\"32\" class=\"vlg bxs_img\" width=\"32\" alt=\"\">";}

var bl_tlt ="";
if(verified)
{bl_tlt="<span class=\"bo fz4 fl tt_c nmcut w150\" rel=\"f_pk\" >"+data.buy_lead.buylead_data[j].ETO_OFR_TITLE+"</span>"+verified+"";}
else{bl_tlt="<span class=\"bo fz4 db tt_c nmcut w150\" rel=\"f_pk\" >"+data.buy_lead.buylead_data[j].ETO_OFR_TITLE+"</span>"+verified+"";}

var b_line1="";
if(j!=numcnt-1)
{b_line1="bdot";
}
$("#buy_leads_list_sd").append("<a class=\""+b_line1+" fl ptb cp\" onclick=\"gatrack(this, 'IM_SupplierDashboard_ViewBL_"+data.supplier_type+""+userType+"', '"+data.buy_lead.buylead_data[j].ETO_OFR_TITLE+"')\" href=\""+data.buy_lead.buylead_rdlink+"&offer="+data.buy_lead.buylead_data[j].ETO_OFR_ID+"\"><span class=\"fz1 cg db tr\"><img src=\"http://hm.imimg.com/gifs/z.gif\" height=\"12\" class=\"bxs_img dicn tr mb2\" width=\"12\" alt=\"\" style=\"float: none;\"><span class=\"dtt\">"+data.buy_lead.buylead_data[j].ETO_OFR_AGE+"</span></span><img src=\"http://hm.imimg.com/gifs/z.gif\" height=\"40\" class=\"bg bxs_img bl_icn\" width=\"40\" alt=\"Enquiry icon\" align=\"left\"><span style=\"margin-left:47px\" class=\"db\"><span class=\"db\">"+bl_tlt+"</span>"+quant+"<span class=\"db fl lh\">"+city_state+"</span><p class=\"cb bd\"></p></span></a>");
}

/* My Dashboard section */

$("#dashb").append("<a onclick=\"gatrack(this, 'IM_SupplierDashboard_Dashboard_"+data.supplier_type+""+userType+"','My Dashboard');\" href=\""+data.quick_links[0].link_href_1+"\"  class=\"cp lkc dhd bbtm db\">"+data.quick_links[0].link_name_1+"</a>"); 

$("#apro").append("<a onclick=\"gatrack(this, 'IM_SupplierDashboard_add_products_"+data.supplier_type+""+userType+"','Add Products');\" class=\"on sdb tdn bo db fl\" href=\""+data.quick_links[1].link_href_2+"\"><span class=\"arg fr\"><img src=\"http://hm.imimg.com/gifs/z.gif\" class=\"bxs_img arww p0 \"></span><span class=\"pt8 db\">"+data.quick_links[1].link_name_2+"</span><span class=\"sl fl\">Add more products to your Catalog</span></a>");

$("#mpro").append("<a onclick=\"gatrack(this, 'IM_SupplierDashboard_manage_products_"+data.supplier_type+""+userType+"','Manage Products');\" class=\"on sdb tdn bo db fl\" href=\"http://my.indiamart.com/product/manageproduct/\"><span class=\"arg fr\"><img src=\"http://hm.imimg.com/gifs/z.gif\" class=\"bxs_img arww p0\"></span><span class=\"pt8 db\">"+data.quick_links[2].link_name_3+"  <span id=\"mp_count\"></span></span><span class=\"sl fl\">Manage the products you Sell</span></a>");

var create_free = data.quick_links[3].link_href_4;
if(create_free == null)
{create_free="http://www.indiamart.com/free-website/";}

$("#mp_count").append("<span class=\"cm bo\">("+data.quick_links[4].Total_prod+")</span>");
$("#mweb").append("<a onclick=\"gatrack(this, 'IM_SupplierDashboard_My_Website_"+data.supplier_type+""+userType+"','My Website');\" class=\"on sdb tdn bo db fl\" href=\""+create_free+"\" target=\"blank\"><span class=\"arg fr\"><img src=\"http://hm.imimg.com/gifs/z.gif\" class=\"bxs_img arww p0 \"></span><span class=\"pt8 db\">"+data.quick_links[3].link_name_4+"</span><span class=\"sl fl\">View your company's Website</span></a>");

/* My Enquiry section*/
var myenq = "("+data.enquiry.unread_enq+")";
	//{myenq="";}
if((data.enquiry.unread_enq == 0) || (data.enquiry.unread_enq == '')||(value=="")||(value==null)){myenq= "";}

$("#my_enq").append("<a onclick=\"gatrack(this, 'IM_SupplierDashboard_EnqInbox_"+data.supplier_type+""+userType+"', '"+data.enquiry.enquiry_rdlink+"');\" href=\""+data.enquiry.enquiry_rdlink+"\" class=\"cp lkc dhd bbtm db\"><span>My Enquiries </span><span class=\"cm\" id=\"tenq\">"+myenq+"</span></a>"); 

$(".imesh_view").append("<span style=\"font-size:20px;\">You do not have permission to view this section.</span><br><A target=\"_top\" rel=\"nofollow\" HREF=\"javascript:user_signIn();\" class=\"sellNow1\" onClick=\"gatrack(this, 'IM_Homepage'+userType+'', 'Top_Header','Login To Unlock', 0);\">Login To Unlock</A>"); 

for (j = 0; j < numcnt; j++) {
/*for my enquiry first letter colorbox class*/
var send_nm = data.enquiry.enquiry_data[j].SENDERNAME;
var send_mob = data.enquiry.enquiry_data[j].MOB;
var send_email = data.enquiry.enquiry_data[j].SENDEREMAIL;
var subj=data.enquiry.enquiry_data[j].SUBJECT;

if( !(/^\d+$/.test(send_nm)) )
{	
	if(send_nm!= null)
	{
	send_nm = send_nm; 
	}
	else if (send_mob != null)
	{
	send_nm = send_mob; 
	}
	else{
	send_nm = send_email; 
	}
}
if((data.enquiry.enquiry_data[j].QTYPE=='P')&&((value=="")||(value==null)))
{
send_nm="Buyer";
subj="Call from Buyer on your Preferred Number"
}

/*for read status;*/
var rd_st = "<span class=\"fl lh fz4 bo tt_c nmcut w187\">"+send_nm+"</span>"; 
if(data.enquiry.enquiry_data[j].READ_STATUS == -1)
{
	rd_st = "<span class=\"fl lh fz4 tt_c nmcut w187\">"+send_nm+"</span>";
}

var rd_st_desc = "<span class=\"g9 cg on bo\" style=\"clear:right;\">"+subj+"</span>";
if(data.enquiry.enquiry_data[j].READ_STATUS == -1)
{
	rd_st_desc = "<span class=\"g9 cg on\" style=\"clear:right;\">"+subj+"</span>";
}

var bg_color = "<img src=\"http://hm.imimg.com/gifs/z.gif\" height=\"40\" class=\"bg bxs_img enq_icn\" width=\"40\" alt=\"Enquiry icon\" align=\"left\">"; 

var dte=data.enquiry.enquiry_data[j].DATE_RE.substr(0, 6);

var b_line="";
if(j!=numcnt-1)
{b_line="bdot";
}
$("#total_enq").append("<a class=\" "+b_line+" fl ptb cp\" onclick=\"gatrack(this, 'IM_SupplierDashboard_ViewEnq_"+data.supplier_type+""+userType+"','"+data.enquiry.enquiry_data[j].QUERY_LINK+"')\" href=\""+data.enquiry.enquiry_data[j].QUERY_LINK+"\"><span class=\"fz1 cg db tr mb2\"><img src=\"http://hm.imimg.com/gifs/z.gif\" height=\"12\" class=\"bxs_img dicn tr\" width=\"12\" alt=\"\" style=\"float: none;\"><span class=\"dtt dty\">"+dte+"</span></span>"+bg_color+"<span style=\"margin-left:47px\" class=\"db\"><span class=\"db\">"+rd_st+"</span>"+rd_st_desc+"</span></a>");
}


if((cookie!= "" && value=="")){
$('#total_enq').css('display','none');
$('.imesh_view').css('display','block');
}
else{
$('#total_enq').css('display','block');
$('.imesh_view').css('display','none');
}


/* End My Enquiry section*/

}
});
setTimeout(function(){if(document.getElementById('pu_sec').style.display=="none"){$('#bnr').css('display','block');$('#frm').css('display','block');$('#tst').css('display','block');$('.blstp').css('display','none');$('#ctry,.t12_frmTx').css('margin-top','20px');$('.sdw').css('box-shadow','0 7px 10px #ccc');$('.t12_frmTx').css('padding','17px 0 0');$('.hwrk').css('display','block');$('.lgn_srh').css('display','none');$('.hdlft').removeClass( "lghd" );$('.pstBuy').css('display','none'); $( ".sldrBx" ).css('display','block');}},5000);
}
else
{
$('#pu_sec').css('display','none');$('#bnr').css('display','block');
$('#frm').css('display','block');$('#tst').css('display','block');$('.blstp').css('display','block');$('#ctry,.t12_frmTx').css('margin-top','20px');$('.sdw').css('box-shadow','none');$('.t12_frmTx').css('padding','17px 0 0');$('.hwrk').css('display','block');$('.lgn_srh').css('display','none');$('.hdlft').removeClass( "lghd" );$('.pstBuy').css('display','none');$( ".sldrBx" ).css('display','block');
datacoming=0;
$('#loading_hp1').css('display','none');
}
}

/* For paid user section end -----------------------------------------------------------------------------------*/

$(document).ready(function() {
$(".ch_help").click(function(){$("#helpBx").slideToggle("slow",function(){});});
var locurl = location.href;
var hostname = locurl.substring(locurl.indexOf('modid=')+6);
if((hostname=="MY") && (cookie = readCookie("ImeshVisitor")) == "" ){
user_signIn();}
});

/* for unmaximize search dropdown issue*/
$('.ui-corner-all').hide();
$(window).resize(function(){$('.ui-corner-all').hide();});

/*XnHist Cookie*/
function FreeWebPopup(){var newCook = new userDataCookie(); var getCook = newCook.get('xnHist'); pv = getCook.pv || 0; var xnHistCity = getCook.city || ''; if (!readCookie('ImeshVisitor')) { pv++; if (pv >= 7) {  } } else { pv = '0'; } var setObj = {'pv': pv, 'city':xnHistCity }; newCook.set(setObj, 'xnHist');}

/* viewportchecker.js for bouncing the page start*/
(function($){ $.fn.viewportChecker = function(useroptions){var options = { classToAdd: 'visible',offset: 100,callbackFunction: function(elem){}};$.extend(options, useroptions);var $elem = this,windowHeight = $(window).height();this.checkElements = function(){var scrollElem = ((navigator.userAgent.toLowerCase().indexOf('webkit') != -1) ? 'body' : 'html'),viewportTop = $(scrollElem).scrollTop(),viewportBottom = (viewportTop + windowHeight);$elem.each(function(){var $obj = $(this);if ($obj.hasClass(options.classToAdd)){  return;}var elemTop = Math.round( $obj.offset().top ) + options.offset,elemBottom = elemTop + ($obj.height());
if ((elemTop < viewportBottom) && (elemBottom > viewportTop)){ $obj.addClass(options.classToAdd);options.callbackFunction($obj);  }} ); };$(window).scroll(this.checkElements);this.checkElements();$(window).resize(function(e){ windowHeight = e.currentTarget.innerHeight;}); };})(jQuery); // viewportchecker.js for bouncing the page end


/* SMS Work*/

$(document).keyup(function(e){if (e.keyCode === 27) {$("#dw_app-content,#dw_back-layer,#hd_container1,#hd_container2,#hd_container3,#hd_container4,#hd_container5,#hd_container6,#dialog,#mask1").css("display","none");}});


function App_Promo()
{
var id = '#dialog';var winH = $(window).height();var winW = $(window).width();
$(id).css('top',  winH/2-$(id).height()/2);$(id).css('left', winW/2-$(id).width()/2);
$(id).fadeIn();
$("#dialog").load(function() {}).css('background', '#fff url("http://hm.imimg.com/gifs/app-dwn2.jpg") no-repeat scroll 0 0');
$('.window .closeb').click(function (e) {e.preventDefault();
$('#mask1').hide();$('.window').hide();
document.app_rgt.txtmobileno.value="";
$("#msg_cont").empty();$("#msg_cont").css("display","none");});		
$('#mask1').click(function () {$(this).hide();$('.window').hide();});
$('#txtmobileno').focus();
if ($('#dialog').css('display') == 'block'){
$("#mask1").css("display","block");
}else {$("#mask1").css("display","none");}
}


function SMS_Status(data2){ 
if(data2.delivery_status=='SMS Triggered')
		{
			$("#hd_container1,#hd_container2,#hd_container4,#hd_container5,#hd_container6").css("display","none");
			$("#dw_app-content,#hd_container3,#dw_back-layer").css("display","block");
		}
 else{
		if(data2.reason=='SMS not sent due to DND')
		{
			$("#hd_container1,#hd_container3,#hd_container4,#hd_container5,#hd_container6").css("display","none");
			$("#dw_app-content,#hd_container2,#dw_back-layer").css("display","block");
		}
		else if(data2.reason=='Sms Sent Attempted Within 30 Minutes')
		{
			$("#hd_container1,#hd_container3,#hd_container2,#hd_container5,#hd_container6").css("display","none");
			$("#dw_app-content,#hd_container4,#dw_back-layer").css("display","block");
		}
		else
		{
			$("#hd_container1,#hd_container3,#hd_container2,#hd_container4,#hd_container6").css("display","none");
			$("#dw_app-content,#hd_container5,#dw_back-layer").css("display","block");
		}
 }
}
function login_callback(data9)
{
if(datacoming==0)
{paid_user();$(".emptydiv div").empty();$(".emptypbr").empty();}
getLoginStringv1();
var pref_mob=getparamVal(decodeURIComponent(cookie),'mb1');
document.app_rgt.txtmobileno.value=pref_mob;
}
function app_sms_status(data3){
	var html="";
	if(data3.delivery_status=='SMS Triggered')
			{
				html="App download link is sent to "+data3.mobile+".";
			}
	 else{
			if(data3.reason=='SMS not sent due to DND')
			{
				html="Oops! "+data3.mobile+" is in <b style='color: red;'>DND</b>.<br/> Please give a Missed call on 1800-200-1848 to install the app.";
			}
			else if(data3.reason=='Sms Sent Attempted Within 30 Minutes')
			{
				html="Please wait for 30 minutes to resend the app link.<br/> Or give a Missed call on 1800-200-1848.";
			}
			else
			{
				html="Oops! We can't reach you at "+data3.mobile+". <br/> To install the app, give a Missed call on 1800-200-1848.";
			}}
			$("#msg_cont").html(html);
			$("#msg_cont").css("display","block");			
	}
function promo_sms_status(mobi){
	var cookie=readCookie('ImeshVisitor'); 
	if(cookie!="" && cookie!=null)
		{
		var name=getparamVal(cookie,"nm");
		var glid=getparamVal(cookie,"glid");
		$.ajax(
				{
				url: "http://mapi.indiamart.com/wservce/apps/sendmsg/mobile/"+mobi+"/gluser/"+glid+"/source/ClickLogIn/subsource//modid/IMHOME/v//receiverName/"+name+"/token/imobile@15061981/",
				type: 'POST',
				dataType: "jsonp",
				jsonpCallback:'app_sms_status'}); 
		}
	else{
	$.ajax(
			{
			url: "http://mapi.indiamart.com/wservce/apps/sendmsg/mobile/"+mobi+"/gluser/0/source/click/subsource//modid/IMHOME/v//receiverName//token/imobile@15061981/",
			type: 'POST',
			dataType: "jsonp",
			jsonpCallback:'app_sms_status'}); 
	$.ajax(
			{
			url: "http://"+UrlPri+"login.indiamart.com/login-check.mp?&user_em="+mobi+"&ph_country=91&action=login&flag=1&modid=IMHOME",
			type: 'POST',
			dataType: "jsonp",
			jsonpCallback:'login_callback'
				}); 
	}
	}
	
var cookie = readCookie('ImeshVisitor');	
var mob=getparamVal(decodeURIComponent(cookie),'mb1');	
	
$(function(){$(".hd-dw-apps").click(function(){
if((cookie) != "")
{
	name=getparamVal(decodeURIComponent(cookie),'fn');
		document.app_rgt.txtmobileno.value=mob;

}
	App_Promo();
});
$(".app_cl,#dw_back-layer").click(function(){$("#dw_back-layer,#dw_app-content,#hd_container1").hide(); });});


/* ----------app banner win iphone6-------------  */

   function invalidmsg(phnum, err_msg, err)
{
	phnum.style.borderColor="red";
	err.innerHTML="<div class='arrow-up1'></div>"+err_msg;
	err.style.display="block";
}
	function validateForm(mbox,errdiv) {
	var err_div=document.getElementById(errdiv);
    var x = mbox.value;
    if (x == null || x == "") {
    	invalidmsg(mbox,"Required",err_div);
        return false;
    }
	else
	{
		if(!RegExp("([6-9]{1})[0-9]{9}").test(x) || x.length!=10)
		{
			invalidmsg(mbox,"Enter 10 digit valid mobile number starting with 9,8,7 or 6",err_div);
		 	return false; 
		}
		else
		{
			mbox.style.borderColor="green";
			err_div.style.display="none";	$("#msg_cont").css("display","block");
			err_div.innerHTML="";
        	return true;
		}
	}
}		
/*SMS Work end*/

// Notification
function notification_onload(){

var logdata = readCookie('ImeshVisitor');
if( (logdata!= "") && (logdata!= null) ){$.ajaxSetup({ cache: true });
   $.getScript( "http://notify.indiamart.com/js/pushstream.js" )
		   .done(function( script, textStatus ) {
			   getcustomnotifyjs();
   })
		   .fail(function( jqxhr, settings, exception ) {                            
   });
   function getcustomnotifyjs(){$.ajaxSetup({ cache: true });
	   $.getScript( "http://notify.indiamart.com/js/PushNotification.js" )
				.done(function( script, textStatus ) {
		})
				.fail(function( jqxhr, settings, exception ) {
					                            
		});
   }            
}}
// Notification end	

/* Download App popup */
$(window).load(function()
		{paid_user();
		var popupshown=false;
		var newCook = new userDataCookie();
		var getCook = newCook.get('xnHist'); pv = getCook.pv || 0; var xnHistCity = getCook.city || '';cvstate=getCook.cvstate||'';popupshown=getCook.popupshown||'';
		var xnh = readCookie('xnHist');	 
		popupshown=getparamVal(decodeURIComponent(xnh),'popupshown'); 
		if(($("#overlay_s").css("display")!="block") && popupshown!='true'){App_Promo();document.app_rgt.txtmobileno.value=mob;
		newCook.set({'popupshown': true}, 'xnHist');}
		setTimeout(function(){notification_onload()}, 50);		
});

$(window).scroll(function(event){$(".ui-autocomplete").css("display","none");});

// for post requirement popup start
FORM_EXPLICIT = 1;
function display_bl_OverlayForm()
{
var loadNewJS='';loadNewJS=document.createElement("script");loadNewJS.src="http://"+UrlPri+"apps.imimg.com/blform/BL_Form.js";document.getElementsByTagName('head')[0].appendChild(loadNewJS);
    if(typeof(open_bl_overlay_form)!="undefined")
	{open_bl_overlay_form(glmodid);}
	else
	{setTimeout(function(){display_bl_OverlayForm()}, 100);}
}
// for post requirement popup end
