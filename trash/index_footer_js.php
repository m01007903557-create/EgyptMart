<!--<script src="js/ctjquery.js"></script>-->
<script src="js/bootstrap.min.js"></script>
<script src="js/zoom-master/jquery.zoom.js"></script>
<!--<script src="zomImage/js/jquery-photo-enlarger/jquery-photo-enlarger.js"></script>
<script src="zomImage/js/EventEmitter.js"></script>
<script src="zomImage/js/eventie.js"></script>
<script src="zomImage/js/imageloader.js"></script>
<script src="zomImage/js/main.js"></script>-->
<script>
    var my_view = 'list';
    $(document).ready(function(){
        $( ".zoomthis" ).hover(
            function() {
              $( this ).parent().find('.ribbon').hide();
              $(this).css('cursor','crosshair');
            }, function() {
              $( this ).parent().find('.ribbon').show();
            }
          );
        $('.zoomthis').zoom();

        //$('.thumb').PhotoEnlarger();
  <!----------------------------------------------------------------------------list/grid vew---------------->
      $("body").on ('click', '.my_list_btn', function(){
         my_view = 'list';
        $(".my_list").css('display', 'block');
        $(".my_grid").css('display', 'none');
      });

      $("body").on ('click', '.my_grid_btn', function(){
         my_view = 'grid';
         $(".my_list").css('display', 'none');
        $(".my_grid").css('display', 'block');
      });

  <!--------------------------------------------------------------------GOPI KISHAN FEV BTN---------------->
     $("body").on('click', '.product_fav_btn', function(){
        var pid = $(this).attr('data');
        var temp = $(this);

          $.ajax({
            type: "post",
            url: "fevrate.php",
            data: {data:pid},
            success: function(response){
                $(temp).children().css('color', '#E48F23');
            }
          });
      });

	<!-------------------------------------------------------------------GOPI KISHAN SARAN--------->
 	  //comp_btn  p_comp
		$("body").on('click', '.product_compare', function(){
			var prod_id = $(this).attr('data-prod_id');
			var title = $(this).attr('title');
			var prod_img = $(this).attr('data-prod_img');
			//alert(prod_id+" - "+title+" - "+prod_img);
      var count = $(".prod_comp_list").children().length;

      if(count == 8){
        console.log("Sorry you have already at max limit to compare");
      }
      else{
        $(".prod_comp_list").append("<div class='pro row' data-productid="+prod_id+">"+
					"<div class='col-lg-5 p_image'>"+
						"<img src="+prod_img+" height='70' width='45' />"+
					"</div>"+
					"<div class='col-lg-7' style='padding-left: 0px;'>"+
						//"<img class='remove_prod pull-right' src='http://b12984e4d8c82ca48867-a8f8a87b64e178f478099f5d1e26a20d.r85.cf1.rackcdn.com/product_tile_cross.png' />"+
						"<div class='p_title' style='padding-top: 10px;'>"+title+"</div>"+
					"</div></div>");
			$(".p_comp").css('display', 'block');
      var url = product_compare_btn(this);
      var u = '/EgyptMART1/compare.php?products='+url;
      $(".comp_btn>a").attr('href', u);
      }

		});

		$("body").on ('click', '.remove_prod', function(){
      var prod_id = $(this).parent().parent().attr('data-productid');
			$(this).parent().parent().remove();//css('display', 'none');
      var count = $(this).parent().parent().parent().children().length;
      console.log(count);
      var url = product_compare_re(this);
      var u = '/EgyptMART1/compare.php?products='+url;
      //console.log(u);
      $(".comp_btn>a").attr('href', u);
      if(count === 0){
        $(this).parent().parent().parent().css('display', 'none');
      }
		});
    });
	 var global_comp_url = new Object;
   var urltemp = '';
	function product_compare_btn(e){
		// if(global_comp_url.first === undefined){
      // global_comp_url.first = $(e).attr('data-prod_id');
    // }
    // else{
      // global_comp_url.second = $(e).attr('data-prod_id');
    // }
    urltemp+= $(e).attr('data-prod_id')+',';
    return urltemp; //global_comp_url.first+","+global_comp_url.second;
	}
  function product_compare_re(e){

    if(global_comp_url.first == $(e).parent().parent().attr('data-productid')){
      global_comp_url.first = undefined;
    }
    else if(global_comp_url.second == $(e).parent().parent().attr('data-productid')){
      global_comp_url.second = undefined;
    }
    return global_comp_url.first+","+global_comp_url.second;
  }
</script>
<script>
	 $(window).bind('scroll', function() {
                if ($(window).scrollTop() > 800) {


                    $('.fixed-div').css('display', 'block');




                } else {


                    $('.fixed-div').css('display', 'none');

                }
            });
			$(window).bind('scroll', function() {
                if ($(window).scrollTop() > 450) {


                    $('#business-alert').css('display', 'block');
					$('#business-alert').css('position', 'fixed');
					$('#business-alert').css('width', '200px');
					$('#business-alert').css('z-index', '9');
					$('#business-alert').css('top', '160px');




                } else {
                    $('#business-alert').css('position', 'static');

                }
            });

			 $(window).bind('scroll', function() {
                if ($(window).scrollTop() > 150) {

                   	$('#right-image').css('display', 'block');
                    $('#right-image').css('position', 'fixed');
					$('#right-image').css('width', '278px');
					$('#right-image').css('z-index', '9');
					$('#right-image').css('right', '15px');
					$('#right-image').css('top', '160px');




                } else {


                    $('#right-image').css('position', 'static');


                }
            });


function hide_modal(){
    $('.postRequirement').modal('hide');
}


$(document).on('click', '.modal-btn', function(){
	 $('.postRequirement').modal('show');
   	 window.setTimeout(hide_modal, 180000);
});
</script>
<script>
    var data = {};
	$( document).on('click','#table-input',function(){
		$("#sideAdTable").hide();
		$("body").click(function(){
			$("#sideAdTable").show();
			});
		});
                //for filtering the data according to country and category

    $(document).ready(function(){
        $('input[name="min_qty"]').change(function(){
            get_data();

        });
        $('input.search_filter').click(function(){
            get_data();
        });

    });

    function get_data(){
         data.min_qty = [];
         data.keywords = '';
         data.category_id = [];
         data.state_id = [];
         data.business_type = [];
         data.membership_type = [];
         data.country_id = [];
         data.status = 0;
         data.sql = "<?php echo urlencode($sql_so); ?>";

        $('input[name="country_id[]"]:checked').each(function(){
                data.country_id.push($(this).val());
                data.status = 1;
            });
        $('input[name="state_id[]"]:checked').each(function(){
                data.state_id.push($(this).val());
                data.status = 1;
            });
                data.keywords = ($("#serachWallkeyword").val());

/*        $('input[name="category_id[]"]:checked').each(function(){
                data.category_id.push($(this).val());
                data.status = 1;
            });*/
        $('input[name="bsn_type[]"]:checked').each(function(){  //class="search_filter" name="bsn_type[]"
                data.business_type.push($(this).val());
                data.status = 1;
            });
$('input[name="mst_type[]"]:checked').each(function(){
                data.membership_type.push($(this).val());
                data.status = 1;
            });
            data.min_qty.push($('input[name="min_qty"]').val());
            //$('#search_result').html('<img src="images/flags/loading.gif" height="50" width="50">  Loading ...');

        $.ajax({
                type: "POST",
                url: "ajax-file/search_filter.php",
                data : data,

               success : function(result){
              //   console.log(result);
               $('#search_result').html(result);
                $('html,body').animate({
        scrollTop: $("#search_result").offset().top},
        'slow');
             //  $("#").scrollTop( 20 );
               return ;
               var html = '';
                    if (result == undefined || result == null || result.length == 0){
                        html = '<h3> No Result Found </h3>';
                    }
                   $.each(result, function (i, e){
                     if(my_view == 'grid'){
                        html += '<div class="col-md-3 compared-box compared-box1 style_prevu_kit">';
                        html += '<header style="padding:5px;">';
                        html += '<a href="#" class="h4">'+e['pd_title']+'</a>';
                        html += '</header>';
                        html += '<figure class="img-box" >';
                        html += '<div class="ribbon"><img src="images/sponsor.png"/></div>';
                        html += '<div class="ara-links">';
                        html += '<a href="#" class="product_fav_btn" data="'+e['pd_id']+'" class="ar-star"><i class="fa fa-star star" ></i> Favourite</a>';
                        html += '<a href="#" class="ar-star product_compare" data-prod_img="upload/myproduct/'+e['pd_image']+'" data-prod_id="'+e['pd_id']+'" title="'+e['pd_title']+'" ><i class="fa fa-plus star"></i> Compare</a>';
                        html += '</div>';
                        html += '<div class="zoomthis"><img height="100%" width="100%" src="upload/myproduct/'+e['pd_image']+'"> </div>';
                        html += '</figure>';
                        html += '<section>';
                        html += '<table><tr>';
                        html += '<td><img src="images/4.png"/></td>';
                        html += '<td colspan="2"><a href="#" class="h5">'+e['bnsprof_compname']+'</a></td></tr><tr>';
                        html += '<td><img src="images/flags/'+e['cn_flag']+'"/></td>';
                        html += '<td colspan="2"><a href="#" class="h5">'+e['cn_name']+'</a></td></tr><tr>';
                        html += '<td></td><td colspan="2"><span class="txt-blue h5">Wholesaler</span></td></tr><tr><td></td>';
                        html += '<td colspan="2"><span class="txt-bold txt-red" style="font-size:16px;">'+e['pd_fob_price']+'</span> '+e['cn_currency']+'</td></tr><tr>';
                        html += '<td></td>';
                        html += '<td colspan="2"><span class="txt-bold txt-red" style="font-size:16px;">'+e['pd_min_order_qty']+'</span> '+e['mu_name']+' ( Min Order )</td>';
                        html += '</tr><tr><td><img src="images/mobile.png"/></td>';
                        html += '<td colspan="2"><a href="#" class="txt-black h4">+20-1220974444</a> </td></tr><tr><td></td>';
                        html += '<td><button class="btn btn-sm btn-default btn-enquiry1 border-radius-0"><span>Send Enquiry</span></button></td>';
                        html += '<td>Chat<img src="images/chat.png" style="width:20px; height:20px; margin-left:5px;"/></td>';
                        html += '</tr></table></section></div>';
                     }
                     else{
                        html += '<div class="row ar-mid-box">';
                        html += '<div class="col-lg-12 ar-box-1  margin-top-10 ">';
                        html += '<div class="row">';
                        html += '<div class="col-lg-3 big-img-box box-1">';
                        html += '<header> <a href="#" class="ar-star"><i class="fa fa-star star"></i> Favourite</a>';
                        html += '<a href="#" class="ar-star"><i class="fa fa-plus star"></i> Compare</a> </header>';
                        html += '<figure class="box" >';
                        html += '<div class="ribbon">  <img src="images/sponsor.png"/> </div>';
                        html += '<div class="zoomthis"><img src="upload/myproduct/' +e['pd_image']+ '"></div>';
                        html += '</figure>';
                        html += '</div>';
                        html += '<div class="col-lg-5 box-2"><ul>';
                        html += '<li class="margin-bottom-10">';
                        html += '<h4 class="txt-blue">'+e['pd_title']+'</h4></li><li>'+e['pd_desc'].substring(0, 150);
                        html += '</li> <li class="text-right"> <a href="#">+  More</a> </li>';
                        html += '<li> Min Order &nbsp;<big class="txt-bold txt-red">'+e['pd_min_order_qty'] +'</big>&nbsp; '+e['mu_name']+' </li>';
                        html += '<li> Fob Price  &nbsp; <big class="txt-bold txt-red">'+e['pd_fob_price']+' </big> &nbsp; '+e['cn_currency']+' <a href="#" class="txt-bold txt-black pull-right">(Get Letest Price)</a> </li>';
                        html += ' <li class="margin-top-5"><table class="table"><tr><td style="padding-left:0px;"><a href="#" class="txt-blue txt-bold"><img src="images/users.png" width="25px"/> About Us</a></td>';
                        html += '<td class=""><a href="#" class="txt-blue  txt-bold"><img src="images/icon.png" width="20px"/> View Products</a></td>';
                        html += ' <td class=""><a href="#" class="txt-black txt-bold"><img src="images/chat.png" width="20px"/> Chat Now</a></td>';
                        html += '</tr> </table></li><li><table class="table margin-bottom-0"><tr class="bg-gray">';
                        html += '<td class="padding-0"><big class=""><img src="images/mobile.png" width="25px"/> &nbsp;<a href="#" class="txt-black txt-lg"><b> +20-1220974444 </b></a></big></td>';
                        html += '<td class="text-right padding-0"><button type="button" class="btn btn-sm btn-warning border-radius-0 btn-enquiry">Send Enquiry</button></td>';
                        html += '</tr></table></li></ul></div><div class="col-lg-4 box-3">';
                        html += '<div class="ar-box-1 ar-box padding-5 margin-bottom-5 bg-gray">';
                        html += '<header class="sub-box"> <img src="images/4.png" width="25px" height="25px"/> <b class="txt-dark-gray"> '+e['bnsprof_compname']+'</b> </header>';
                        html += '<img src="images/flags/'+e['cn_flag']+'" alt="Egypt_flag" style=" width:21.6px; height:21.6px;"/> <b class="txt-bold" style="color:#302670; margin-left:10px;"> '+e['cn_name']+'</b>';
                        html += '<table class="table margin-top-5"><tr ><td class="txt-light-gray padding-0"> Business Type : </td>';
                        html += '<td class="padding-0 txt-bold"> Manufucturer </td></tr><tr><td class="txt-light-gray padding-0"> Trade Location : </td>';
                        html += '<td class="padding-0 txt-bold"> '+e['ct_name']+' </td> </tr><tr><td class="txt-light-gray padding-0"> Member Since : </td>';
                        html += '<td class="padding-0 txt-bold"> '+e['bnsprof_yoe']+' </td> </tr><tr> <td class="txt-light-gray " colspan="2" ><a href="'+e['bnsprof_website_alt']+'"';
                        html += ' class="padding-0"> '+e['bnsprof_website_alt']+'</td></tr></table></div><div class="small-box">';
                        html += '<table class="table margin-bottom-0"><tr>\n '+e['releted_prod']+' \n</tr>\n</table>\n</div>\n</div>\n<div class="clearfix"> </div>\n</div>\n</div>\n</div>';
                     }
                   });
                   if(my_view == 'grid'){
                     $('.my_grid').html('<div class="row fond">'+html+'</div>');
                   }else{
                     $('#search_result').html(html);
                   }
               }       
            });
    }
</script>

<script>
    function msgShowDiv(msg_type, msgContent)
    {
        var msg_typeClass = '';
        var msgTypeheadingContent = '';
        if (msg_type == 0)
        {
            msg_typeClass = 'alert-warning';
            msgTypeheadingContent = 'Warning';
        }
        else {
            msg_typeClass = 'alert-success';
            msgTypeheadingContent = 'Success';
        }

        var contentDiv = '';
        contentDiv += '<div class="alert ' + msg_typeClass + ' alert-dismissible" role="alert" style=" padding: 5px 35px 5px 15px ; font-size: 12px;">';
        contentDiv += '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        contentDiv += '<strong>' + msgTypeheadingContent + '!</strong> ' + msgContent;
        contentDiv += '</div>';

        return contentDiv;

    }
</script>
<script>
    
    $("#global_country_select").change(function() {

        var loadig_div_id = "loading_div_status";
        var cn_id = $(this).val();
        //alert(sno_category);
        var _this = $(this);

        console.log("Location change");
        
        if(cn_id!=0)
        {    
        
               $('#supplier_state_text').css('display','block');
               $('#'+loadig_div_id).html('<img src="images/flags/loading.gif" height="50" width="50">  Loading ...');
        
                    $.ajax({
                        type: "POST",
                        url: "ajax-file/ajax_getState.php",
                        data: {cn_id: cn_id},
                        dataType: "json",
                        success: function(d) {
                            //d=$.parseJSON(d);

                            if ((parseInt(d.msg_status) == 0))
                            {
                                //alert(d.msg);
                                // $("#edit_profile_pic_loading").attr('class','error');
                                //$("#edit_profile_pic_loading").html(d.msg);
                                var msg_contentForPrint = msgShowDiv(d.msg_status, d.msg);
                                $('#'+loadig_div_id).html(msg_contentForPrint);

                            }
                            else {
                                //alert(d.msg);
                                console.log(d.state_data);
                               var contentData='';
                               $.each(d.state_data, function(i, e) {
                                   // console.log(i, e);
                                    var state_id = e.state_id; //
                                    var state_name = e.state_name;
                                    contentData+='<div class="checkbox">'
                                     contentData+='<label>';
                                    contentData+='<input type="checkbox" class="search_filter" name="state_id[]" value="'+state_id+'">';
                                     contentData+='<span>'+state_name+'</span>';
                                     contentData+='</label>';
                                     contentData+='</div>';
                                });

                                $("#"+loadig_div_id).html(contentData);

                            }



                        },<!-- success close -->
                        error: function(XMLHttpRequest, textStatus, errorThrown)
                        {
                            if (XMLHttpRequest.readyState == 4) {
                                // HTTP error (can be checked by XMLHttpRequest.status and XMLHttpRequest.statusText)
                                $('#' + loadig_div_id).html('<b class="error">can be checked by ' + XMLHttpRequest.status + ' and ' + XMLHttpRequest.statusText + '</b>');
                            }
                            else if (XMLHttpRequest.readyState == 0) {
                                // Network error (i.e. connection refused, access denied due to CORS, etc.)
                                $('#' + loadig_div_id).html('<b class="error">something weird is happening</b>');
                            }
                            else {
                                // something weird is happening
                                $('#' + loadig_div_id).html('<b class="error">Network error (i.e. connection refused, access denied due to CORS, etc.)</b>');
                            }
                        }


                    });
                    return false;

        }
        else{
            $('#supplier_state_text').css('display','none');
            $('#' + loadig_div_id).html('');
        }


    });




</script>
   <?php if($_GET['keyword_type']!=''){
     echo "<script>get_data();</script>";
   } ?>

