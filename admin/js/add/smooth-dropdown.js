$(document).ready(function(){
		/*KEYWORD SEARCH DIV START*/
	$('div#searchCat').hide();
	$("#selected_cat_h").val($(".ddMenuContent li").attr('id'));
		$('#txt_search').click(function(){
			$('#searchCat').slideDown();
		});
		$('div.dvsearch_close').click(function(){
			$('#searchCat').slideUp('slow');
		});
		/*KEYWORD SEARCH DIV END*/

		/*SEARCH CAT START*/
		$('#selected_cat').click(function(){
			$(this).parent().find("ul.ddMenuContent").slideDown('slow').show();
			$(this).parent().hover(function() {
		}, function(){
			$(this).parent().find("ul.ddMenuContent").slideUp('slow');
		});
		}).hover(function() {
			$(this).addClass("subhover");
		}, function(){
			$(this).removeClass("subhover");
		 $(".ddMenuContent li a").click(function()
		 {
		 		var selected_cat = $(this).parent().attr('id');
 				$(".ddMenuContent").slideUp('slow');
				$("#selected_cat").html($(this).html());
				$("#selected_cat_h").val(selected_cat);
		});
		});
	$("ul.ddMenuContent").parent().append("<span></span>");
	$("ul.ddMenu li span").click(function() {
		$(this).parent().find("ul.ddMenuContent").slideDown('slow').show();

		$(this).parent().hover(function() {
		}, function(){
			$(this).parent().find("ul.ddMenuContent").slideUp('slow');
		});
		}).hover(function() {
			$(this).addClass("subhover");
		}, function(){
			$(this).removeClass("subhover");
		 $(".ddMenuContent li a").click(function()
		 {
		 		var selected_cat = $(this).parent().attr('id');
 				$(".ddMenuContent").slideUp('slow');
				$("#selected_cat").html($(this).html());
				$("#selected_cat_h").val(selected_cat);
		});
	});
});
		/*SEARCH CAT END*/

		/*LANG DROPDOWN START*/
$(document).ready(function(){
	$("#list_lang").parent().append("<span></span>");

	$("#selected_lang" ).click(function() {
		$(this).parent().find("#list_lang").slideDown('slow').show();
		$(this).parent().hover(function() {
		}, function(){
			$(this).parent().find("#list_lang").slideUp('slow');
		});
		}).hover(function() {
			$(this).addClass("subhover");
		}, function(){
			$(this).removeClass("subhover");
		 $("#list_lang li a").click(function() {
		 	var selected_cat = $(this).html();
 				$("#list_lang").slideUp('slow');
				$("#selected_lang").html(selected_cat);});
	});

	$("#select_lang li span" ).click(function() {
		$(this).parent().find("#list_lang").slideDown('slow').show();
		$(this).parent().hover(function() {
		}, function(){
			$(this).parent().find("#list_lang").slideUp('slow');
		});
		}).hover(function() {
			$(this).addClass("subhover");
		}, function(){
			$(this).removeClass("subhover");
		 $("#list_lang li a").click(function() {
		 	var selected_cat = $(this).html();
 				$("#list_lang").slideUp('slow');
				$("#selected_lang").html(selected_cat);});
	});
});
		/*LANG DROPDOWN END*/

		/*PAGE DROPDOWN START*/
$(document).ready(function(){
	$("#list_page").parent().append("<span></span>");
	$("#selected_page").click(function() {
		$(this).parent().find("#list_page").slideDown('slow').show();
		$(this).parent().hover(function() {
		}, function(){
			$(this).parent().find("#list_page").slideUp('slow');
		});
		}).hover(function() {
			$(this).addClass("subhover");
		}, function(){
			$(this).removeClass("subhover");
		 $("#list_page li a").click(function() {
		 	var selected_cat = $(this).html();
 				$("#list_page").slideUp('slow');
				$("#selected_page").html(selected_cat);});
	});

	$("#select_page li span").click(function() {
		$(this).parent().find("#list_page").slideDown('slow').show();
		$(this).parent().hover(function() {
		}, function(){
			$(this).parent().find("#list_page").slideUp('slow');
		});
		}).hover(function() {
			$(this).addClass("subhover");
		}, function(){
			$(this).removeClass("subhover");
		 $("#list_page li a").click(function() {
		 	var selected_cat = $(this).html();
 				$("#list_page").slideUp('slow');
				$("#selected_page").html(selected_cat);});
	});
});
		/*PAGE DROPDOWN END*/
		/*Tooltips - Search Page*/
$(document).ready(function() {
	// Position the tooltip.


                    var positionTooltip = function(event) {
					var $tipparent = $(this).parent();
					var wd=$tipparent.contents('.inner_tooltips').width();
                    var ttwd=wd+event.pageX;
					if(ttwd > $(window).width()){
						//alert('BIG');
						var tPosX = $(window).width()- wd - 10;
					}
					else{
						var tPosX = event.pageX - 10;
					}
					
                    var tPosY = event.pageY + 20;
                    $('div.tooltip').css({top: tPosY, left: tPosX});
                    };
                    // Show (create) the tooltip.
                    var showTooltip = function(event) {
                            $('div.tooltip').remove();


                            if($('#display_prev_img').attr('checked')==true)
                                {

                               // if($.browser.msie && $.browser.version=="6.0") $('select').hide();

                            var $tipparent = $(this).parent();
                            var $tipchild = $tipparent.contents('.inner_tooltips').html();
                            $('<div class="tooltip">' + $tipchild + '</div>').appendTo('body');
                            positionTooltip(event);
							var wd=$tipparent.contents('.inner_tooltips').width();
							$('div.tooltip').css({'width':wd-2,'z-index':100});
							}
                    };
                    // Hide (remove) the tooltip.
                    var hideTooltip = function() {
                    $('div.tooltip').remove();
                    // if($.browser.msie && $.browser.version=="6.0") $('select').show();
                    };
                    $("div.data a[rel='htmltool']")
                    .hover(showTooltip, hideTooltip)
                    .mousemove(positionTooltip);




});
/*Tooltips - Search Page*/