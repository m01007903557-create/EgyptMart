(function($){
	$.fn.Tabbings=function(options){
		var defaults={
			'heads'		:	'> ul:eq(0) li',
			'datas'		:	'> ul:eq(1) > li',
			'open'		:	0
		}
		
		var options=$.extend({},defaults, options)
		var heads=$(options.heads,$(this));
		var datas=$(options.datas,$(this));
		var open=options.open;
		
		heads.eq(open).addClass('show')
		datas.eq(open).show()
		
		return this.each(function(){
			heads.each(function(i){
				$(this).click(function(e){
					e.preventDefault();
					heads.removeClass('show');
					heads.eq(i).addClass('show');
					
					datas.hide();
					datas.eq(i).show();
				})
			})
		})
	}
	
	
	$(function(){ /* document.ready starts */
		var $applytabbingplugin = $('[data-plugin~="tabbing"]');
		for(var i=0, j=$applytabbingplugin.length; i<j; i++){
			$($applytabbingplugin[i]).tabbing();
		} 
	})
	$.fn.tabbing=function(){ /* tabbing plugin starts */
		if(!this.length) return;
		var $this = $(this);
		
		var defaults = {
			headings			:	'> ul:eq(0) > li',
			anchors				:	'> a:first-child',
			datas				:	'> ul:eq(1) > li',
			active				:	0,
			mouseevent			:	'click',
			hideeffect			:	'hide',
			hideduration		:	0,
			showeffect			:	'show',
			showduration		:	0
		};
		
		var options = $.extend({}, defaults, $(this).data('tabbing-settings'));
		
		var headings			=	$(options.headings, $(this)),
			anchors				=	$(options.anchors, headings),
			datas				=	$(options.datas, $(this)),
			active				=	options.active,
			hac					=	$(headings).eq(active).attr('class'), //headingsActiveClasses
			hic					=	$(headings).not(':eq(' + active + ')').attr('class') || false,
			aac					=	$(anchors).eq(active).attr('class'), //anchorsActiveClasses
			aic					=	$(anchors).not(':eq(' + active + ')').attr('class') || false;
		
		if(headings.length < 2) return;
		var headingNode = headings[0].nodeName.toLowerCase();
		
		
		this.each(function(){
			anchors.on(options.mouseevent, function(event){
				event.preventDefault();
				var index = $(this).closest(headingNode).prevAll().length;
				
				headings.removeAttr('class').addClass(hic);
				headings.eq(index).removeAttr('class').addClass(hac)
				anchors.removeAttr('class').addClass(aic);
				$(this).removeAttr('class').addClass(aac);
				
				datas.filter(':visible')[options.hideeffect](options.hideduration).end().eq(index)[options.showeffect](options.showduration)//[showeffect]();
			})
		});
	/* tabbing plugin ends */ }; 
})(jQuery);
