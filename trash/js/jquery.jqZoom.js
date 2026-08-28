/**
 * &#25918;&#22823;&#38236;&#25554;&#20214;     v1.1.0
 * @mail cj_zheng1023@hotmail.com
 * @author AfterWin
 *
 *
 *
 * update log
 *
 * 2017.5.30    &#20462;&#25913;&#36890;&#36807;&#35831;&#27714;&#33719;&#21462;&#30340;&#22270;&#29255;&#33719;&#21462;&#19981;&#21040;&#39640;&#23485;&#30340;&#38382;&#39064;   v.1.1.0
 *
 */
(function($){

    var SPACING = 15;
    //var ZOOM_TIMES = 10;





    $.fn.jqZoom = function(options){
        $(this).each(function(i, dom){
            var me = $(dom);
            _initZoom(me, options.selectorWidth, options.selectorHeight);
            var imgUrl = options&&options.zoomImgUrl?options.zoomImgUrl:me.attr("src");
            _initViewer(me, imgUrl, options.viewerWidth, options.viewerHeight);
        })
    }

    /**
     * &#21021;&#22987;&#21270;&#32858;&#28966;&#26694;
     * @param target     &#22270;&#29255;jquery&#23545;&#35937;
     * @param sWidth     &#32858;&#28966;&#21306;&#22495;&#23485;&#24230;
     * @param sHeight    &#32858;&#28966;&#21306;&#22495;&#38271;&#24230;
     * @private
     */
    var _initZoom = function(target, sWidth, sHeight){
        var $zoom = $("<div />").addClass("zoom-selector").width(sWidth).height(sHeight);
        target.after($zoom);
        target.closest(".zoom-box").on({
            mousemove: function(e){
                var mouseX=e.pageX-$(this).offset().left;
                var mouseY=e.pageY-$(this).offset().top;
                var halfSWidth = sWidth/ 2,halfSHeight = sHeight/2;
                var realX, realY;
                if(mouseX < halfSWidth){
                    realX = 0;
                }else if(mouseX + halfSWidth > target.width()){
                    realX = target.width() - sWidth;
                }else{
                    realX = mouseX - halfSWidth;
                }
                if(mouseY < halfSHeight){
                    realY = 0;
                }else if(mouseY + halfSHeight > target.height()){
                    realY = target.height() - sHeight;
                }else{
                    realY = mouseY - halfSHeight;
                }
                $zoom.css({
                    left: realX,
                    top: realY
                })
                var viewerX = realX*($(this).find(".viewer-box>img").width() - $(this).find(".viewer-box").width())/(target.width() - sWidth);
                var viewerY = realY*($(this).find(".viewer-box>img").height() - $(this).find(".viewer-box").height())/(target.height() - sHeight);
                $(this).find(".viewer-box>img").css({
                    left: -viewerX,
                    top: -viewerY
                })
            },
            mouseenter: function(){
                $zoom.css("display", "block");
                $(this).find(".viewer-box").css("display", "block");
            },
            mouseleave: function(){
                $zoom.css("display", "none");
                $(this).find(".viewer-box").css("display", "none");
            }
        })
    }
    /**
     *&#21021;&#22987;&#21270;&#25918;&#22823;&#21306;&#22495;
     * @param target       &#22270;&#29255;jquery&#23545;&#35937;
     * @param imgUrl      &#21407;&#22987;&#22270;&#29255;URL
     * @param vWidth      &#25918;&#22823;&#21306;&#22495;&#23485;&#24230;
     * @param vHeight     &#25918;&#22823;&#21306;&#22495;&#38271;&#24230;
     * @private
     */
    var _initViewer = function(target, imgUrl, vWidth, vHeight){
        var $viewer = $("<div />").addClass("viewer-box").width(vWidth).height(vHeight);
        var $zoomBox = target.closest(".zoom-box");
        $viewer.css({
            left: target.width() + SPACING,
            top: 0
        })
        _setOriginalSize(target, function(oWidth, oHeight){
            var $img = $("<img src='"+imgUrl+"' />").width(oWidth).height(oHeight);
            $viewer.append($img);
            target.after($viewer);
        });
    }
    /**
     * &#35774;&#32622;&#22270;&#29255;&#21407;&#22987;&#23485;&#39640;
     * @param target       &#22270;&#29255;jquery&#23545;&#35937;
     * @param callback     &#36890;&#36807;&#22238;&#35843;&#20989;&#25968;&#35774;&#32622;&#21407;&#22987;&#23485;&#39640;
     * @returns {{oWidth: Number, oHeight: Number}}
     * @private
     */
    var _setOriginalSize = function(target, callback){
        var newImg = new Image();
        newImg.src = target.attr("src")+"?date="+new Date();
        $(newImg).on("load", function(){
            callback(newImg.width, newImg.height);
        })
    }

})(jQuery);