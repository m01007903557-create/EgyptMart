/*! jQuery UI - v1.8.21 - 2012-06-05
* https://github.com/jquery/jquery-ui
* Includes: jquery.ui.core.js
* Copyright (c) 2012 AUTHORS.txt; Licensed MIT, GPL */
(function(a,b){function c(b,c){var e=b.nodeName.toLowerCase();if("area"===e){var f=b.parentNode,g=f.name,h;return!b.href||!g||f.nodeName.toLowerCase()!=="map"?!1:(h=a("img[usemap=#"+g+"]")[0],!!h&&d(h))}return(/input|select|textarea|button|object/.test(e)?!b.disabled:"a"==e?b.href||c:c)&&d(b)}function d(b){return!a(b).parents().andSelf().filter(function(){return a.curCSS(this,"visibility")==="hidden"||a.expr.filters.hidden(this)}).length}a.ui=a.ui||{};if(a.ui.version)return;a.extend(a.ui,{version:"1.8.21",keyCode:{ALT:18,BACKSPACE:8,CAPS_LOCK:20,COMMA:188,COMMAND:91,COMMAND_LEFT:91,COMMAND_RIGHT:93,CONTROL:17,DELETE:46,DOWN:40,END:35,ENTER:13,ESCAPE:27,HOME:36,INSERT:45,LEFT:37,MENU:93,NUMPAD_ADD:107,NUMPAD_DECIMAL:110,NUMPAD_DIVIDE:111,NUMPAD_ENTER:108,NUMPAD_MULTIPLY:106,NUMPAD_SUBTRACT:109,PAGE_DOWN:34,PAGE_UP:33,PERIOD:190,RIGHT:39,SHIFT:16,SPACE:32,TAB:9,UP:38,WINDOWS:91}}),a.fn.extend({propAttr:a.fn.prop||a.fn.attr,_focus:a.fn.focus,focus:function(b,c){return typeof b=="number"?this.each(function(){var d=this;setTimeout(function(){a(d).focus(),c&&c.call(d)},b)}):this._focus.apply(this,arguments)},scrollParent:function(){var b;return a.browser.msie&&/(static|relative)/.test(this.css("position"))||/absolute/.test(this.css("position"))?b=this.parents().filter(function(){return/(relative|absolute|fixed)/.test(a.curCSS(this,"position",1))&&/(auto|scroll)/.test(a.curCSS(this,"overflow",1)+a.curCSS(this,"overflow-y",1)+a.curCSS(this,"overflow-x",1))}).eq(0):b=this.parents().filter(function(){return/(auto|scroll)/.test(a.curCSS(this,"overflow",1)+a.curCSS(this,"overflow-y",1)+a.curCSS(this,"overflow-x",1))}).eq(0),/fixed/.test(this.css("position"))||!b.length?a(document):b},zIndex:function(c){if(c!==b)return this.css("zIndex",c);if(this.length){var d=a(this[0]),e,f;while(d.length&&d[0]!==document){e=d.css("position");if(e==="absolute"||e==="relative"||e==="fixed"){f=parseInt(d.css("zIndex"),10);if(!isNaN(f)&&f!==0)return f}d=d.parent()}}return 0},disableSelection:function(){return this.bind((a.support.selectstart?"selectstart":"mousedown")+".ui-disableSelection",function(a){a.preventDefault()})},enableSelection:function(){return this.unbind(".ui-disableSelection")}}),a.each(["Width","Height"],function(c,d){function h(b,c,d,f){return a.each(e,function(){c-=parseFloat(a.curCSS(b,"padding"+this,!0))||0,d&&(c-=parseFloat(a.curCSS(b,"border"+this+"Width",!0))||0),f&&(c-=parseFloat(a.curCSS(b,"margin"+this,!0))||0)}),c}var e=d==="Width"?["Left","Right"]:["Top","Bottom"],f=d.toLowerCase(),g={innerWidth:a.fn.innerWidth,innerHeight:a.fn.innerHeight,outerWidth:a.fn.outerWidth,outerHeight:a.fn.outerHeight};a.fn["inner"+d]=function(c){return c===b?g["inner"+d].call(this):this.each(function(){a(this).css(f,h(this,c)+"px")})},a.fn["outer"+d]=function(b,c){return typeof b!="number"?g["outer"+d].call(this,b):this.each(function(){a(this).css(f,h(this,b,!0,c)+"px")})}}),a.extend(a.expr[":"],{data:function(b,c,d){return!!a.data(b,d[3])},focusable:function(b){return c(b,!isNaN(a.attr(b,"tabindex")))},tabbable:function(b){var d=a.attr(b,"tabindex"),e=isNaN(d);return(e||d>=0)&&c(b,!e)}}),a(function(){var b=document.body,c=b.appendChild(c=document.createElement("div"));c.offsetHeight,a.extend(c.style,{minHeight:"100px",height:"auto",padding:0,borderWidth:0}),a.support.minHeight=c.offsetHeight===100,a.support.selectstart="onselectstart"in c,b.removeChild(c).style.display="none"}),a.extend(a.ui,{plugin:{add:function(b,c,d){var e=a.ui[b].prototype;for(var f in d)e.plugins[f]=e.plugins[f]||[],e.plugins[f].push([c,d[f]])},call:function(a,b,c){var d=a.plugins[b];if(!d||!a.element[0].parentNode)return;for(var e=0;e<d.length;e++)a.options[d[e][0]]&&d[e][1].apply(a.element,c)}},contains:function(a,b){return document.compareDocumentPosition?a.compareDocumentPosition(b)&16:a!==b&&a.contains(b)},hasScroll:function(b,c){if(a(b).css("overflow")==="hidden")return!1;var d=c&&c==="left"?"scrollLeft":"scrollTop",e=!1;return b[d]>0?!0:(b[d]=1,e=b[d]>0,b[d]=0,e)},isOverAxis:function(a,b,c){return a>b&&a<b+c},isOver:function(b,c,d,e,f,g){return a.ui.isOverAxis(b,d,f)&&a.ui.isOverAxis(c,e,g)}})})(jQuery);;/*! jQuery UI - v1.8.21 - 2012-06-05
* https://github.com/jquery/jquery-ui
* Includes: jquery.ui.widget.js
* Copyright (c) 2012 AUTHORS.txt; Licensed MIT, GPL */
(function(a,b){if(a.cleanData){var c=a.cleanData;a.cleanData=function(b){for(var d=0,e;(e=b[d])!=null;d++)try{a(e).triggerHandler("remove")}catch(f){}c(b)}}else{var d=a.fn.remove;a.fn.remove=function(b,c){return this.each(function(){return c||(!b||a.filter(b,[this]).length)&&a("*",this).add([this]).each(function(){try{a(this).triggerHandler("remove")}catch(b){}}),d.call(a(this),b,c)})}}a.widget=function(b,c,d){var e=b.split(".")[0],f;b=b.split(".")[1],f=e+"-"+b,d||(d=c,c=a.Widget),a.expr[":"][f]=function(c){return!!a.data(c,b)},a[e]=a[e]||{},a[e][b]=function(a,b){arguments.length&&this._createWidget(a,b)};var g=new c;g.options=a.extend(!0,{},g.options),a[e][b].prototype=a.extend(!0,g,{namespace:e,widgetName:b,widgetEventPrefix:a[e][b].prototype.widgetEventPrefix||b,widgetBaseClass:f},d),a.widget.bridge(b,a[e][b])},a.widget.bridge=function(c,d){a.fn[c]=function(e){var f=typeof e=="string",g=Array.prototype.slice.call(arguments,1),h=this;return e=!f&&g.length?a.extend.apply(null,[!0,e].concat(g)):e,f&&e.charAt(0)==="_"?h:(f?this.each(function(){var d=a.data(this,c),f=d&&a.isFunction(d[e])?d[e].apply(d,g):d;if(f!==d&&f!==b)return h=f,!1}):this.each(function(){var b=a.data(this,c);b?b.option(e||{})._init():a.data(this,c,new d(e,this))}),h)}},a.Widget=function(a,b){arguments.length&&this._createWidget(a,b)},a.Widget.prototype={widgetName:"widget",widgetEventPrefix:"",options:{disabled:!1},_createWidget:function(b,c){a.data(c,this.widgetName,this),this.element=a(c),this.options=a.extend(!0,{},this.options,this._getCreateOptions(),b);var d=this;this.element.bind("remove."+this.widgetName,function(){d.destroy()}),this._create(),this._trigger("create"),this._init()},_getCreateOptions:function(){return a.metadata&&a.metadata.get(this.element[0])[this.widgetName]},_create:function(){},_init:function(){},destroy:function(){this.element.unbind("."+this.widgetName).removeData(this.widgetName),this.widget().unbind("."+this.widgetName).removeAttr("aria-disabled").removeClass(this.widgetBaseClass+"-disabled "+"ui-state-disabled")},widget:function(){return this.element},option:function(c,d){var e=c;if(arguments.length===0)return a.extend({},this.options);if(typeof c=="string"){if(d===b)return this.options[c];e={},e[c]=d}return this._setOptions(e),this},_setOptions:function(b){var c=this;return a.each(b,function(a,b){c._setOption(a,b)}),this},_setOption:function(a,b){return this.options[a]=b,a==="disabled"&&this.widget()[b?"addClass":"removeClass"](this.widgetBaseClass+"-disabled"+" "+"ui-state-disabled").attr("aria-disabled",b),this},enable:function(){return this._setOption("disabled",!1)},disable:function(){return this._setOption("disabled",!0)},_trigger:function(b,c,d){var e,f,g=this.options[b];d=d||{},c=a.Event(c),c.type=(b===this.widgetEventPrefix?b:this.widgetEventPrefix+b).toLowerCase(),c.target=this.element[0],f=c.originalEvent;if(f)for(e in f)e in c||(c[e]=f[e]);return this.element.trigger(c,d),!(a.isFunction(g)&&g.call(this.element[0],c,d)===!1||c.isDefaultPrevented())}}})(jQuery);;/*! jQuery UI - v1.8.21 - 2012-06-05
* https://github.com/jquery/jquery-ui
* Includes: jquery.ui.position.js
* Copyright (c) 2012 AUTHORS.txt; Licensed MIT, GPL */
(function(a,b){a.ui=a.ui||{};var c=/left|center|right/,d=/top|center|bottom/,e="center",f={},g=a.fn.position,h=a.fn.offset;a.fn.position=function(b){if(!b||!b.of)return g.apply(this,arguments);b=a.extend({},b);var h=a(b.of),i=h[0],j=(b.collision||"flip").split(" "),k=b.offset?b.offset.split(" "):[0,0],l,m,n;return i.nodeType===9?(l=h.width(),m=h.height(),n={top:0,left:0}):i.setTimeout?(l=h.width(),m=h.height(),n={top:h.scrollTop(),left:h.scrollLeft()}):i.preventDefault?(b.at="left top",l=m=0,n={top:b.of.pageY,left:b.of.pageX}):(l=h.outerWidth(),m=h.outerHeight(),n=h.offset()),a.each(["my","at"],function(){var a=(b[this]||"").split(" ");a.length===1&&(a=c.test(a[0])?a.concat([e]):d.test(a[0])?[e].concat(a):[e,e]),a[0]=c.test(a[0])?a[0]:e,a[1]=d.test(a[1])?a[1]:e,b[this]=a}),j.length===1&&(j[1]=j[0]),k[0]=parseInt(k[0],10)||0,k.length===1&&(k[1]=k[0]),k[1]=parseInt(k[1],10)||0,b.at[0]==="right"?n.left+=l:b.at[0]===e&&(n.left+=l/2),b.at[1]==="bottom"?n.top+=m:b.at[1]===e&&(n.top+=m/2),n.left+=k[0],n.top+=k[1],this.each(function(){var c=a(this),d=c.outerWidth(),g=c.outerHeight(),h=parseInt(a.curCSS(this,"marginLeft",!0))||0,i=parseInt(a.curCSS(this,"marginTop",!0))||0,o=d+h+(parseInt(a.curCSS(this,"marginRight",!0))||0),p=g+i+(parseInt(a.curCSS(this,"marginBottom",!0))||0),q=a.extend({},n),r;b.my[0]==="right"?q.left-=d:b.my[0]===e&&(q.left-=d/2),b.my[1]==="bottom"?q.top-=g:b.my[1]===e&&(q.top-=g/2),f.fractions||(q.left=Math.round(q.left),q.top=Math.round(q.top)),r={left:q.left-h,top:q.top-i},a.each(["left","top"],function(c,e){a.ui.position[j[c]]&&a.ui.position[j[c]][e](q,{targetWidth:l,targetHeight:m,elemWidth:d,elemHeight:g,collisionPosition:r,collisionWidth:o,collisionHeight:p,offset:k,my:b.my,at:b.at})}),a.fn.bgiframe&&c.bgiframe(),c.offset(a.extend(q,{using:b.using}))})},a.ui.position={fit:{left:function(b,c){var d=a(window),e=c.collisionPosition.left+c.collisionWidth-d.width()-d.scrollLeft();b.left=e>0?b.left-e:Math.max(b.left-c.collisionPosition.left,b.left)},top:function(b,c){var d=a(window),e=c.collisionPosition.top+c.collisionHeight-d.height()-d.scrollTop();b.top=e>0?b.top-e:Math.max(b.top-c.collisionPosition.top,b.top)}},flip:{left:function(b,c){if(c.at[0]===e)return;var d=a(window),f=c.collisionPosition.left+c.collisionWidth-d.width()-d.scrollLeft(),g=c.my[0]==="left"?-c.elemWidth:c.my[0]==="right"?c.elemWidth:0,h=c.at[0]==="left"?c.targetWidth:-c.targetWidth,i=-2*c.offset[0];b.left+=c.collisionPosition.left<0?g+h+i:f>0?g+h+i:0},top:function(b,c){if(c.at[1]===e)return;var d=a(window),f=c.collisionPosition.top+c.collisionHeight-d.height()-d.scrollTop(),g=c.my[1]==="top"?-c.elemHeight:c.my[1]==="bottom"?c.elemHeight:0,h=c.at[1]==="top"?c.targetHeight:-c.targetHeight,i=-2*c.offset[1];b.top+=c.collisionPosition.top<0?g+h+i:f>0?g+h+i:0}}},a.offset.setOffset||(a.offset.setOffset=function(b,c){/static/.test(a.curCSS(b,"position"))&&(b.style.position="relative");var d=a(b),e=d.offset(),f=parseInt(a.curCSS(b,"top",!0),10)||0,g=parseInt(a.curCSS(b,"left",!0),10)||0,h={top:c.top-e.top+f,left:c.left-e.left+g};"using"in c?c.using.call(b,h):d.css(h)},a.fn.offset=function(b){var c=this[0];return!c||!c.ownerDocument?null:b?a.isFunction(b)?this.each(function(c){a(this).offset(b.call(this,c,a(this).offset()))}):this.each(function(){a.offset.setOffset(this,b)}):h.call(this)}),function(){var b=document.getElementsByTagName("body")[0],c=document.createElement("div"),d,e,g,h,i;d=document.createElement(b?"div":"body"),g={visibility:"hidden",width:0,height:0,border:0,margin:0,background:"none"},b&&a.extend(g,{position:"absolute",left:"-1000px",top:"-1000px"});for(var j in g)d.style[j]=g[j];d.appendChild(c),e=b||document.documentElement,e.insertBefore(d,e.firstChild),c.style.cssText="position: absolute; left: 10.7432222px; top: 10.432325px; height: 30px; width: 201px;",h=a(c).offset(function(a,b){return b}).offset(),d.innerHTML="",e.removeChild(d),i=h.top+h.left+(b?2e3:0),f.fractions=i>21&&i<22}()})(jQuery);;/*! jQuery UI - v1.8.21 - 2012-06-05
* https://github.com/jquery/jquery-ui
* Includes: jquery.ui.autocomplete.js
* Copyright (c) 2012 AUTHORS.txt; Licensed MIT, GPL */
(function(a,b){var c=0;a.widget("ui.autocomplete",{options:{appendTo:"body",autoFocus:!1,delay:300,minLength:1,position:{my:"left top",at:"left bottom",collision:"none"},source:null},pending:0,_create:function(){var b=this,c=this.element[0].ownerDocument,d;this.isMultiLine=this.element.is("textarea"),this.element.addClass("ui-autocomplete-input").attr("autocomplete","off").attr({role:"textbox","aria-autocomplete":"list","aria-haspopup":"true"}).bind("keydown.autocomplete",function(c){if(b.options.disabled||b.element.propAttr("readOnly"))return;d=!1;var e=a.ui.keyCode;switch(c.keyCode){case e.PAGE_UP:b._move("previousPage",c);break;case e.PAGE_DOWN:b._move("nextPage",c);break;case e.UP:b._keyEvent("previous",c);break;case e.DOWN:b._keyEvent("next",c);break;case e.ENTER:case e.NUMPAD_ENTER:b.menu.active&&(d=!0,c.preventDefault());case e.TAB:if(!b.menu.active)return;b.menu.select(c);break;case e.ESCAPE:b.element.val(b.term),b.close(c);break;default:clearTimeout(b.searching),b.searching=setTimeout(function(){b.term!=b.element.val()&&(b.selectedItem=null,b.search(null,c))},b.options.delay)}}).bind("keypress.autocomplete",function(a){d&&(d=!1,a.preventDefault())}).bind("focus.autocomplete",function(){if(b.options.disabled)return;b.selectedItem=null,b.previous=b.element.val()}).bind("blur.autocomplete",function(a){if(b.options.disabled)return;clearTimeout(b.searching),b.closing=setTimeout(function(){b.close(a),b._change(a)},150)}),this._initSource(),this.menu=a("<ul></ul>").addClass("ui-autocomplete").css("position","absolute").appendTo(a(this.options.appendTo||"body",c)[0]).mousedown(function(c){var d=b.menu.element[0];a(c.target).closest(".ui-menu-item").length||setTimeout(function(){a(document).one("mousedown",function(c){c.target!==b.element[0]&&c.target!==d&&!a.ui.contains(d,c.target)&&b.close()})},1),setTimeout(function(){clearTimeout(b.closing)},13)}).menu({focus:function(a,c){var d=c.item.data("item.autocomplete");!1!==b._trigger("focus",a,{item:d})&&/^key/.test(a.originalEvent.type)&&b.element.val(d.value)},selected:function(a,d){var e=d.item.data("item.autocomplete"),f=b.previous;b.element[0]!==c.activeElement&&(b.element.focus(),b.previous=f,setTimeout(function(){b.previous=f,b.selectedItem=e},1)),!1!==b._trigger("select",a,{item:e})&&b.element.val(e.value),b.term=b.element.val(),b.close(a),b.selectedItem=e},blur:function(a,c){b.menu.element.is(":visible")&&b.element.val()!==b.term&&b.element.val(b.term)}}).zIndex(this.element.zIndex()+1).css({top:0,left:0}).hide().data("menu"),a.fn.bgiframe&&this.menu.element.bgiframe(),b.beforeunloadHandler=function(){b.element.removeAttr("autocomplete")},a(window).bind("beforeunload",b.beforeunloadHandler)},destroy:function(){this.element.removeClass("ui-autocomplete-input").removeAttr("autocomplete").removeAttr("role").removeAttr("aria-autocomplete").removeAttr("aria-haspopup"),this.menu.element.remove(),a(window).unbind("beforeunload",this.beforeunloadHandler),a.Widget.prototype.destroy.call(this)},_setOption:function(b,c){a.Widget.prototype._setOption.apply(this,arguments),b==="source"&&this._initSource(),b==="appendTo"&&this.menu.element.appendTo(a(c||"body",this.element[0].ownerDocument)[0]),b==="disabled"&&c&&this.xhr&&this.xhr.abort()},_initSource:function(){var b=this,c,d;a.isArray(this.options.source)?(c=this.options.source,this.source=function(b,d){d(a.ui.autocomplete.filter(c,b.term))}):typeof this.options.source=="string"?(d=this.options.source,this.source=function(c,e){b.xhr&&b.xhr.abort(),b.xhr=a.ajax({url:d,data:c,dataType:"json",success:function(a,b){e(a)},error:function(){e([])}})}):this.source=this.options.source},search:function(a,b){a=a!=null?a:this.element.val(),this.term=this.element.val();if(a.length<this.options.minLength)return this.close(b);clearTimeout(this.closing);if(this._trigger("search",b)===!1)return;return this._search(a)},_search:function(a){this.pending++,this.element.addClass("ui-autocomplete-loading"),this.source({term:a},this._response())},_response:function(){var a=this,b=++c;return function(d){b===c&&a.__response(d),a.pending--,a.pending||a.element.removeClass("ui-autocomplete-loading")}},__response:function(a){!this.options.disabled&&a&&a.length?(a=this._normalize(a),this._suggest(a),this._trigger("open")):this.close()},close:function(a){clearTimeout(this.closing),this.menu.element.is(":visible")&&(this.menu.element.hide(),this.menu.deactivate(),this._trigger("close",a))},_change:function(a){this.previous!==this.element.val()&&this._trigger("change",a,{item:this.selectedItem})},_normalize:function(b){return b.length&&b[0].label&&b[0].value?b:a.map(b,function(b){return typeof b=="string"?{label:b,value:b}:a.extend({label:b.label||b.value,value:b.value||b.label},b)})},_suggest:function(b){var c=this.menu.element.empty().zIndex(this.element.zIndex()+1);this._renderMenu(c,b),this.menu.deactivate(),this.menu.refresh(),c.show(),this._resizeMenu(),c.position(a.extend({of:this.element},this.options.position)),this.options.autoFocus&&this.menu.next(new a.Event("mouseover"))},_resizeMenu:function(){var a=this.menu.element;a.outerWidth(Math.max(a.width("").outerWidth()+1,this.element.outerWidth()))},_renderMenu:function(b,c){var d=this;a.each(c,function(a,c){d._renderItem(b,c)})},_renderItem:function(b,c){return a("<li></li>").data("item.autocomplete",c).append(a("<a></a>").text(c.label)).appendTo(b)},_move:function(a,b){if(!this.menu.element.is(":visible")){this.search(null,b);return}if(this.menu.first()&&/^previous/.test(a)||this.menu.last()&&/^next/.test(a)){this.element.val(this.term),this.menu.deactivate();return}this.menu[a](b)},widget:function(){return this.menu.element},_keyEvent:function(a,b){if(!this.isMultiLine||this.menu.element.is(":visible"))this._move(a,b),b.preventDefault()}}),a.extend(a.ui.autocomplete,{escapeRegex:function(a){return a.replace(/[-[\]{}()*+?.,\\^$|#\s]/g,"\\$&")},filter:function(b,c){var d=new RegExp(a.ui.autocomplete.escapeRegex(c),"i");return a.grep(b,function(a){return d.test(a.label||a.value||a)})}})})(jQuery),function(a){a.widget("ui.menu",{_create:function(){var b=this;this.element.addClass("ui-menu ui-widget ui-widget-content ui-corner-all").attr({role:"listbox","aria-activedescendant":"ui-active-menuitem"}).click(function(c){if(!a(c.target).closest(".ui-menu-item a").length)return;c.preventDefault(),b.select(c)}),this.refresh()},refresh:function(){var b=this,c=this.element.children("li:not(.ui-menu-item):has(a)").addClass("ui-menu-item").attr("role","menuitem");c.children("a").addClass("ui-corner-all").attr("tabindex",-1).mouseenter(function(c){b.activate(c,a(this).parent())}).mouseleave(function(){b.deactivate()})},activate:function(a,b){this.deactivate();if(this.hasScroll()){var c=b.offset().top-this.element.offset().top,d=this.element.scrollTop(),e=this.element.height();c<0?this.element.scrollTop(d+c):c>=e&&this.element.scrollTop(d+c-e+b.height())}this.active=b.eq(0).children("a").addClass("ui-state-hover").attr("id","ui-active-menuitem").end(),this._trigger("focus",a,{item:b})},deactivate:function(){if(!this.active)return;this.active.children("a").removeClass("ui-state-hover").removeAttr("id"),this._trigger("blur"),this.active=null},next:function(a){this.move("next",".ui-menu-item:first",a)},previous:function(a){this.move("prev",".ui-menu-item:last",a)},first:function(){return this.active&&!this.active.prevAll(".ui-menu-item").length},last:function(){return this.active&&!this.active.nextAll(".ui-menu-item").length},move:function(a,b,c){if(!this.active){this.activate(c,this.element.children(b));return}var d=this.active[a+"All"](".ui-menu-item").eq(0);d.length?this.activate(c,d):this.activate(c,this.element.children(b))},nextPage:function(b){if(this.hasScroll()){if(!this.active||this.last()){this.activate(b,this.element.children(".ui-menu-item:first"));return}var c=this.active.offset().top,d=this.element.height(),e=this.element.children(".ui-menu-item").filter(function(){var b=a(this).offset().top-c-d+a(this).height();return b<10&&b>-10});e.length||(e=this.element.children(".ui-menu-item:last")),this.activate(b,e)}else this.activate(b,this.element.children(".ui-menu-item").filter(!this.active||this.last()?":first":":last"))},previousPage:function(b){if(this.hasScroll()){if(!this.active||this.first()){this.activate(b,this.element.children(".ui-menu-item:last"));return}var c=this.active.offset().top,d=this.element.height(),e=this.element.children(".ui-menu-item").filter(function(){var b=a(this).offset().top-c+d-a(this).height();return b<10&&b>-10});e.length||(e=this.element.children(".ui-menu-item:first")),this.activate(b,e)}else this.activate(b,this.element.children(".ui-menu-item").filter(!this.active||this.first()?":last":":first"))},hasScroll:function(){return this.element.height()<this.element[a.fn.prop?"prop":"attr"]("scrollHeight")},select:function(a){this._trigger("selected",a,{item:this.active})}})}(jQuery);;


var cimjsv;
if(typeof(cimjsv) === "undefined")
	cimjsv = new Object();
cimjsv['http://utils.imimg.com/suggest/js/jq-ac-ui.js']=28;

// the suggestion object. it contains meta data of suggestions besides the suggestions
function Suggestions(list, type)
{
	// available types of suggestion
	// DIRECT means - suggestions are fetched for the mentioned term from server
	this.DIRECT = 1;
	// FILTERED means - suggestions are extracted from a superset of cache and more can be fetched from the server
	this.FILTERED = 2;
	// COMPLETE means - all suggestions are fetched for the mentioned term from server and ***NO MORE*** suggestions will come if we append any character on it
	this.COMPLETE = 3;

	this._list = null;
	this.list(list, type);

	return this;
}

Suggestions.prototype.list = function(list, type)
{
	// set list if provided
	if(list != null)
	{
		this._list = list;
		this.type = type || this.DIRECT;
	}

	return this._list;
}

// the cache object. it manages setting and getting from cache
function SuggestionCache()
{
	// this object will keep the keys and sugestion lists for those
	// e.g. { pri: ["printer", "prickly heat powder", "price tags"]};
	this._cache = new Object();
	return this;
}

function cleanString(strVal)
{
	strVal = strVal.replace(/[^a-zA-Z0-9 ]+/g, ' ');
	strVal = strVal.replace(/^ +| +$/g, '');
	strVal = strVal.replace(/\s\s+/g, ' ');
	strVal = strVal.replace(/^\s+/, '').replace(/\s+$/, '');
	return strVal;
}

// sets cache if list is provided as Suggestions object. returns object of Suggestions if cache found, null otherwise
SuggestionCache.prototype.cache = function(key, list)
{
	// set cache only if provided
	if(list != null)
	{
		// setting cache is easy. no extra work
		if(key != ""){
			this._cache[""+key] = list;
		}
	}

	// getting cache is slightly tricky. if we are looking for suggestion for "pri" but we have suggestions only for "pr" and not for "pri", we will try to find those suggestions which have a word starting from "pri" among available suggestions.

	// return if found
	if(key in this._cache)
	{
		if(typeof(this._cache[""+key]) === 'object')
		{
			return this._cache[""+key];
		}
	}

	// check for cache of superset, till there IS a superset
	if(key.length > 0)
	{
		var superCache = this.cache(key.substr(0, key.length - 1));
		if(superCache != null)
		{
			var filteredCache = new Suggestions();
			var filteredList = [];
			list = superCache.list();
			for(var term in list)
			{
				var patt=new RegExp("\\b("+key+")","i");
				if(list[term].value !=null){
					var cTerm = cleanString(list[term].value);
					if(cTerm.match(patt))
					{
						filteredList.push(list[term]);
					}
				}
			}

			if(superCache.type == superCache.COMPLETE)
				filteredCache.list(filteredList, filteredCache.COMPLETE);
			else
				filteredCache.list(filteredList, filteredCache.FILTERED);

			return filteredCache;
		}
	}

	return null;
}


function Suggester(options)
{
	try
	{
		if(!options)
			return;

		// set default values

		// suggestions will be displayed only after these many characters
		if(!options.minStringLengthToDisplaySuggestion)
			options.minStringLengthToDisplaySuggestion = 3;

		// suggestions will be displayed only after these many characters
		if(typeof(options.recentData) === 'undefined')
			options.recentData = true;

		// suggestions will be fetched and cached after these many characters are typed
		if(!options.minStringLengthToFetchSuggestion)
			options.minStringLengthToFetchSuggestion = 1;

		// number of suggestions to display
		if(!options.rowsToDisplay)
			options.rowsToDisplay = 15;

		// number of suggestions to fetch and cache
		if(!options.suggestionsToFetch)
			options.suggestionsToFetch = 200;

		if(!options.classPlaceholder)
			options.classPlaceholder = "ui-placeholder-input";

		// number of maximum characters which will be generating an http reuqest for more suggestions
		if(!options.maxCharForSuggestionRequest)
			options.maxCharForSuggestionRequest = 6;

		// options for highlighting - 'reverse', 'normal', 'none'
		if(typeof(options.highlight) === 'undefined')
			options.highlight = 'normal';

		// country is searched always from start of the country name
		if((typeof(options.type) != 'undefined') && options.type.toLowerCase() === 'country')
			options.method = 'beginString';

		var store = new IMStore();
		this.recentSearches = function(q)
		{
			var rs = store.getData("ims","recent") || [];
			var data = Suggester.getTopN(rs, 5);
			// remove from array if "q" already exists
			var i = $.inArray(q, rs); 
			if(i != -1)
				rs.splice(i,1);
			if(q)
				store.setData("ims","recent",Suggester.getTopN([q].concat(rs), 10));
			else
				return data;
		};
		
		// setting cursor position in textbox
		this.setCursor = function(el,st,end)
		{
			if(el.setSelectionRange) {
				el.focus();
				el.setSelectionRange(st,end);
			} else {
				if(el.createTextRange) {
					range=el.createTextRange();
					range.collapse(true);
					range.moveEnd('character',end);
					range.moveStart('character',st);
					range.select();
				}
			}
		}

		var boxid = '#' + options.element;

		// check if placeholder is supported
		var isPlaceholderSupported = function(){
			var input = document.createElement("input");
			//return ('placeholder' in input);
			return false;
		}
		this.placeholderSupport = isPlaceholderSupported();
                //alert(this.placeholderSupport);
		this.changePlaceholder = function(placeholderMsg)
		{
			$(boxid).attr('placeholder', placeholderMsg);
			if(!this.placeholderSupport)
			{
				// If there is no placeholder support,
				// set the value of the input field to the
				// placeholder value
				if(!$(boxid).val() || $(boxid).hasClass(options.classPlaceholder))
				{  
					$(boxid).val($(boxid).attr('placeholder')).addClass(options.classPlaceholder);
					if($(boxid).is(":focus") === true)
						this.setCursor(document.getElementById($(boxid).attr('id')),0,0);
				}
				var _suggEle = this;
				$(boxid).click(function()
						{    
						if($(this).val() == $(boxid).attr('placeholder'))
						_suggEle.setCursor(document.getElementById($(boxid).attr('id')),0,0);
						}); 
				$(boxid).keydown(function()
						{ 
						if ($(this).val() == $(boxid).attr('placeholder'))
						$(this).val('').removeClass(options.classPlaceholder);
						});
			        $(boxid).on('paste',function()
                                                {
                                                if($(this).val() === $(boxid).attr('placeholder'))
                                                $(this).val('').removeClass(options.classPlaceholder);   
                                                }); 					
				$(boxid).focus(function()
						{ 	
						if((!$(boxid).val()) || ($(this).val() == $(boxid).attr('placeholder')))
						_suggEle.setCursor(document.getElementById($(boxid).attr('id')),0,0);
						});

				$(boxid).blur(function(event)
						{  
						if($(this).val() == '')
						$(this).val($(boxid).attr('placeholder')).addClass(options.classPlaceholder);
				});
			}
			else
			{ 
				$(boxid).attr('placeholder', placeholderMsg);
			}
		}

		// change the placeholder text
		this.changePlaceholder(options.placeholder);
		var mySuggester = this;
		$(function()
		{
			var cache = new SuggestionCache();
			var _ac = $(boxid).autocomplete({
				delay: 5,
				source: function( request, response )
				{
					var term = request.term;
					var filtered = options.source;
					// if source is provided, use it
					if(filtered && term.length >= options.minStringLengthToDisplaySuggestion)
					{
						// if we have a function to check for match/non-match, use it
						if(options.finder)
						{
							filtered = options.finder(filtered, term);
						}
						else
						{
							term = cleanString(request.term);
							// check if source is formatted. i.e. [{label:"Gurgaon", value:"Gurgaon >> Haryana"}]
							$.each(filtered,function(i,v){if(typeof(v) === "string"){filtered[i] = {label:v,value:v};}});

							var patt;
							if(options.method && options.method.toString().toLowerCase() == "beginstring")
								patt = new RegExp("^"+term,"i");
							else
								patt = new RegExp("\\b"+term,"i");
							filtered = $.grep(filtered, function(ele, index){ var label=cleanString(ele.label); return patt.test(label)});
						}
						$.each(filtered,function(i,v){if(typeof(v) === "string"){filtered[i] = {label:v,value:v};}});
						response(Suggester.getTopN(filtered,options.rowsToDisplay));
					}
					else
					{
						// Sending term with cleanup 
						term = cleanString(term);   
						var termSugg = cache.cache(term);

						var displayList = [];
						// check for recent keyword searches
						if(((typeof(options.type) === 'undefined') || (options.type == "keyword") || (options.type == "")) && (options.recentData == true))
						{
							displayList = Suggester.match(term, mySuggester.recentSearches(), options.method);
							// format the list, i.e. [{label:"x", value:"y"}]
							$.each(displayList,function(i,v){displayList[i] = {label:v,value:v,cls:"recent"}});
						}
						if(term.length >= options.minStringLengthToDisplaySuggestion)
						{
							// add to display list if cache found
							if(termSugg)
								displayList = displayList.concat(termSugg.list());
						}
						// display
						response(Suggester.getTopN(displayList, options.rowsToDisplay));

						// return if no more suggestion are expected
						if(termSugg && (
							termSugg.type == termSugg.DIRECT
							|| termSugg.type == termSugg.COMPLETE
							|| termSugg.list().length >= options.rowsToDisplay)
						)
							return;

						if((!termSugg || (termSugg.type == termSugg.FILTERED)) && (term.length <= options.maxCharForSuggestionRequest))
						{
							$.ajax({
								url: options.url || "http://utils.imimg.com/suggest/suggest.mp",
								dataType: "jsonp",
								data:
								{
									q: term,
									limit: options.suggestionsToFetch,
									type: options.type,
									fields: options.fields,
									filters: options.filters,
									method: options.method,
									display_fields: options.displayFields,
									display_separator: options.displaySeparator
								},
								success: function ( data )
								{
									var sugg = new Suggestions();
									var type = sugg.DIRECT;
									// if fetched rows were less than max possible, it means sggestions are exhausted
									if(data.length < options.suggestionsToFetch)
										type = sugg.COMPLETE;
									sugg.list(data, type);
									cache.cache(term, sugg);
									if(term.length >= options.minStringLengthToDisplaySuggestion)
									{
										var displayList = [];
						if(((typeof(options.type) === 'undefined') || (options.type == "keyword") || (options.type == "")) && (options.recentData == true))
										{
											displayList = Suggester.match(term, mySuggester.recentSearches(), options.method);
											// check if source is formatted. i.e. [{label:"Gurgaon", value:"Gurgaon >> Haryana"}]
											$.each(displayList,function(i,v){displayList[i] = {label:v,value:v,cls:"recent"}});
										}
										displayList = displayList.concat(sugg.list());
										response(Suggester.getTopN(displayList,options.rowsToDisplay));
									}
								},
								jsonpCallback:'Suggester_callback_'+options.method+'_'+term,
								cache:true
							});
						}
					}
				},
				minLength: options.minStringLengthToFetchSuggestion,
				select:function(event, ui)
				{
					$(boxid).val(ui.item.value);
					this.onSelectFired = true;
					if(options.onSelect)
						options.onSelect.call(this, event, ui);
				},
				change:function(event ,ui)
				{ 	
					var text =$(this).val();
					if(!ui.item)
					{
					       // if the user moves out of the text box without selecting from the auto-suggest drop down
					       // fire a onBlur event with status (whether exists in options or not) of the text typed

					       if(options.onExplicitChange)
					       {
						       var exactData;
						       // find the result of exact match in cache
						       // if cache does not serve the purpose send an AJAX request
						       // TODO: check the results in cache first
						       exactData == cache.cache(text);
						       if(!exactData)
						       {
								$.ajax(
								{
									url: options.url || "http://utils.imimg.com/suggest/suggest.mp",
									dataType: "jsonp",
									data:
									{
										q: text,
										limit: 1,
										type: options.type,
										filters: options.filters,
										fields: options.fields,
										method: "exact",
										display_fields: options.displayFields,     		
										display_separator: options.displaySeparator
									},
									success: function ( data )
									{
										var ui;
										if(data && data[0])
										{
										ui = {"item":data[0]};
										} else{
										ui = {"item":""};
										}
										options.onExplicitChange.call(this, event, ui);
									},
									jsonpCallback:'Suggester_callback',
									cache:true
								});
						       }
					       }
					} 
				}, 
				open: options.onOpen,
				close: options.onClose
			}).data( "autocomplete" );
			if(options.autocompleteClass)
				$(_ac.menu.element[0]).addClass(options.autocompleteClass);
			_ac._renderItem = function( ul, item )
			{
				var hlTerm = cleanString(this.term);
				var rhlTerm = hlTerm.replace(/ /g,"[^a-zA-Z0-9]+"); // Used for globally replacing with check SpecialCharacter 
				var patt=new RegExp("\\b("+rhlTerm+")","ig");
				var label = item.label || item.value;
				var cls = item.cls || "";
				if(options.highlight === 'normal')
				{
					label = label.replace(patt, "<b>$1</b>");
				}
				else if(options.highlight === 'reverse')
				{
					label = '<b>' + label.replace(patt, "</b>$1<b>") + '</b>';
				}
				if(cls)
					cls = ' class="'+cls+'"';
				return $( "<li"+cls+"></li>" )
					.data( "item.autocomplete", item )
					.append( "<a>" + label + "</a>" )
					.appendTo( ul );
			};
		});
	}
	catch(e)
	{
		// test comment #6 to check caching
	}
}

Suggester.match = function(term, list, method)
{
	if(!method)
		method = "beginword";
	var patt;
	if(method.toString().toLowerCase() == "beginstring")
		patt = new RegExp("^"+term,"i");
	else
		patt = new RegExp("\\b"+term,"i");
	return $.grep(list, function(ele, index){ var label=cleanString(ele); return patt.test(label)});
};

/* get top "n" suggestions
this function works with two data types
1. for simple array of strings (numbers, etc.) it will simply return top N unique results
2. for the array of objects having label attribute (for display list), it only returns top N unique label elements,
   but also adds another attribute "pos" indicating the position of element in the display list
*/
Suggester.getTopN = function(list, n){
	var topList = [];
	var unique = {};
	var position = 1;
	for(var i = 0; i < n && i < list.length; i++)
	{
		if(!unique[""+(list[i].label||list[i])])
		{
			if(list[i].label)
				list[i]["pos"] = position++;
			topList.push(list[i]);
		}
		unique[""+(list[i].label||list[i])]=1;
	}

	return topList;
}
function IMStore() {
	var url = 'http://utils.imimg.com/storage/store.html';
	var frameId = 'storageFrame';
	this.url = document.URL;
	this.childURL = url;
	var message = {modId:"*",key:"*",data:"*",url:this.url};
	try {
		if(typeof(_IMStore_initialized) === "undefined") {
			_IMStore_initialized = true;
			var ifrm = document.createElement("IFRAME");
			ifrm.setAttribute("src", url);
			ifrm.style.visibility = "hidden";
			ifrm.setAttribute("id", frameId);
			ifrm.setAttribute("name", "storageFrame");
			ifrm.style.width = 0+"px";
			ifrm.style.height = 0+"px";
			$(document).ready(function(){
						document.body.appendChild(ifrm);
						$("#"+frameId).load(function(){
							IMStore.msgHandler = ifrm.contentWindow;
							IMStore.msgHandler.postMessage(message, url);
						});
					});
		}
	} catch(e){
	}
}

/* Core getter/setter functions to read/write from/on localStorage.
No other function is supposed to directly interact with localStorage.
All the cross browser functionality is also supposed to be done here
Modid and key must be in lower case
*/

/* getData receives modid and key. Returns the data stored against key for mentioned modid.
Return type could be anything and it depends on what we have stored against a particular modid, key combination.
If Storage is not supported or modId/key is not defined it will return null value
*/
IMStore.localStorage =  {};
IMStore.prototype.getData = function(modId, key)
{
	if(typeof(Storage) === "undefined") return null;
        return $.parseJSON(IMStore.localStorage[modId.toLowerCase()]||'{}')[key.toLowerCase()];
};

IMStore.prototype.setData = function(modId,key,data)
{
	if(typeof(Storage) === "undefined") return null;
	var msg = {modId:modId,key:key,data:data,url:this.url};
	if(typeof(IMStore.msgHandler) != "undefined"){
		IMStore.msgHandler.postMessage(msg, this.childURL);
	}
};
// Receive message will call after storage response.
IMStore.receieveMessage = function(event)
{
	if(event.data){
		if(event.origin.match(/utils.imimg.com/g))
		IMStore.localStorage = event.data;
	}
}
 
if (window.addEventListener)
{
	addEventListener("message", IMStore.receieveMessage, false)
} else {
	attachEvent("onmessage", IMStore.receieveMessage)
}
