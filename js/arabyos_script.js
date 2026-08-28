$(document).ready(function() {
			// The regex matches a series of characters in the given range.
// (Double-check this, I believe there's a second Arabic range in
// the Unicode standard, but I know next to nothing about Arabic.)
//alert('test');
arabicwalk(document.body, /([\u0600-\u060B\u060D-\u06FF][\u0600-\u060B\u060D-\u06FF\s]+[\u0600-\u060B\u060D-\u06FF])/g);

function arabicwalk(node, targetRe) {
	//alert('test1');
  var child;
 //console.log('nodetype'+node.nodeType);
  switch (node.nodeType) {
    case 1: // Element
      for (child = node.firstChild;
           child;
           child = child.nextSibling) {
        arabicwalk(child, targetRe);
      }
      break;

    case 3: // Text node
      handleText(node, targetRe);
      break;
  }
}

function handleText(node, targetRe) {
  var match, targetNode, followingNode, wrapper;
  //console.log(node.nodeValue);
  // Does the text contain our target string?
  match = targetRe.exec(node.nodeValue);
  if (match) {
    // Split at the beginning of the match
    targetNode = node.splitText(match.index);

    // Split at the end of the match.
    // match[0] is the full text that was matched.
    followingNode = targetNode.splitText(match[0].length);

    // Wrap the target in an `span` element with an `arabic` class.
    // First we create the wrapper and insert it in front
    // of the target text. We use the first capture group
    // as the `href`.
    wrapper = document.createElement('span');
    wrapper.className = "arabic";
	wrapper.dir="rtl";
	wrapper.lang="ar";
    targetNode.parentNode.insertBefore(wrapper, targetNode);

    // Now we move the target text inside it
    wrapper.appendChild(targetNode);

    // Clean up any empty nodes (in case the target text
    // was at the beginning or end of a text node)
    if (node.nodeValue.length == 0) {
      node.parentNode.removeChild(node);
    }
    if (followingNode.nodeValue.length == 0) {
      followingNode.parentNode.removeChild(followingNode);
    }

    // Continue with the next match in the node, if any
    match = followingNode
      ? targetRe.exec(followingNode.nodeValue)
      : null;
  }
}
});

var left=1;
(function($) {

  $.fn.menumaker = function(options) {
      
      var menu = $(this), settings = $.extend({
        title: "Menu",
        format: "dropdown",
        sticky: false
      }, options);

      return this.each(function() {
        menu.prepend('<div id="menu-button">' + settings.title + '</div>');
        $(this).find("#arabyos-menu-button").on('click', function(){
          $(this).toggleClass('menu-opened');
          var mainmenu = $(this).next('ul');
          if (mainmenu.hasClass('open')) { 
            mainmenu.hide().removeClass('open');
          }
          else {
            mainmenu.show().addClass('open');
            if (settings.format === "dropdown") {
              mainmenu.find('ul').show();
            }
          }
        });

        menu.find('li ul').parent().addClass('has-sub');

        multiTg = function() {
          menu.find(".has-sub").prepend('<span class="submenu-button"></span>');
          menu.find('.submenu-button').on('click', function() {
            $(this).toggleClass('submenu-opened');
            if ($(this).siblings('ul').hasClass('open')) {
              $(this).siblings('ul').removeClass('open').hide();
            }
            else {
              $(this).siblings('ul').addClass('open').show();
            }
          });
        };

        if (settings.format === 'multitoggle') multiTg();
        else menu.addClass('dropdown');

        if (settings.sticky === true) menu.css('position', 'fixed');

        resizeFix = function() {
          if ($( window ).width() > 1150) {
            menu.find('ul').show();
          }

          if ($(window).width() <= 1150) {
            menu.find('ul').hide().removeClass('open');
          }
        };
        resizeFix();
        return $(window).on('resize', resizeFix);

      });
  };
}(jQuery));

(function($){


$(document).ready(function() {
	

  $("#arabyos-menu").menumaker({
    title: "Menu",
    format: "multitoggle"
  });

  $("#arabyos-menu").prepend("<div id='menu-line'></div>");

var foundActive = false, activeElement, linePosition = 0, menuLine = $("#arabyos-menu #arabyos-menu-line"), lineWidth, defaultPosition, defaultWidth;

$("#arabyos-menu > ul > li").each(function() {
  if ($(this).hasClass('active')) {
    activeElement = $(this);
    foundActive = true;
  }
});

if (foundActive === false) {
  activeElement = $("#arabyos-menu > ul > li").first();
}

defaultWidth = lineWidth = activeElement.width();

defaultPosition = linePosition = activeElement.position().left-1;

menuLine.css("width", lineWidth);
menuLine.css("left", linePosition);

$("#arabyos-menu > ul > li").hover(function() {
  activeElement = $(this);
  lineWidth = activeElement.width();
  linePosition = activeElement.position().left;
  menuLine.css("width", lineWidth);
  menuLine.css("left", linePosition);
}, 
function() {
  menuLine.css("left", defaultPosition);
  menuLine.css("width", defaultWidth);
});

});

})(jQuery);

// script to for right image sidebar section 
$(window).bind('scroll', function() {
                if ($(window).scrollTop() > 210) {
                    
                   
                    $('#right-image').css('position', 'fixed');					
					$('#right-image').css('z-index', '9');
					$('#right-image').css('right', '30px');
					$('#right-image').css('top', '170px');
					$('#right-image').css('max-width', '283px');
					
                } else {
                    
					$('#right-image').css('position', 'static');
                }
            });
			
			$(window).bind('scroll', function() {
                if ($(window).scrollTop() > 1250) {
             		$('#right-image').css('position', 'static');
					
                } else {
             }
            });


// scroll to top and feedback button 
$(window).bind('scroll', function() {
if ($(window).scrollTop() > 800) {

$('.fixed-div').css('display', 'block');

} else {

$('.fixed-div').css('display', 'none');

}
});			

// script to for right image sidebar section 
$(window).bind('scroll', function() {
                if ($(window).scrollTop() > 210) {
                    
                   
                    $('.top-bar').css('position', 'fixed');
					$('.top-bar').css('top', '0px');
					$('.top-bar').css('left', '0px');
					$('.top-bar').css('z-index', '9999');
					//$('.maincontainertop').css('position', 'fixed');	
					//$('.maincontainertop').css('z-index', '999');
					//$('.maincontainertop').css('top', '35px');
					/*$('.maincontainertop').css('left', '3%');		*/
					/*$('.maincontainertop').css('display', 'none');	*/	
					
                } else {
                    $('.top-bar').css('position', 'static');
					$('.maincontainertop').css('position', 'static');
                }
            });