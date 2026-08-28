<script src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/js/jquery.colorbox.js"></script>
<link href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/css/colorbox.css" type="text/css" rel="stylesheet">
<style>.zoomthis >img{
    width: 250px !important;
    height: 250px;
}
@media (min-width: 992px) {
	.compared-box {
		width: 22% !important;
    	margin-left: 18px;
	}
}
</style>
 <script>
	$(document).ready(function(){
		$('body').on('click', '.ajax', function() {
		  $.colorbox(
		  {
			  href:$(this).attr('href'), open:true, width: '750px',
			  onClosed:function(){
				  window.location.reload();
			  }
		  }
		  );
		  return false;
		});
		$('#contactAllSupplier').click(function(){
			//var suppliersCheckbox = $('input[name="suppliersChecks"]:checked').attr('rel');
			var allVals = [];
			var allSuppId = [];
			var checkBoxesCheck = 1;
			$('input:checkbox[name=suppliersChecks]').each(function(){    
				if($(this).is(':checked')){
					var suppliersCheckbox = $(this).attr('rel');
					var suppliersId = $(this).attr('rev');
					allVals.push(suppliersCheckbox);
					allSuppId.push(suppliersId);
				}else{
					alert('Please select suppliers for multi enquery!!');
					checkBoxesCheck = 0;
					return false;
				}
			});
			if(checkBoxesCheck == 1){
				$.ajax({
				  type: 'POST',
				  url: 'company/sendmultienqueryform.php?action=sendEnquery&productId='+allVals+'&suppId='+allSuppId,
				  data: allVals,
				  dataType: "html",
				  success: function(resultData) {
				  	if(resultData < 1){
						window.location.href = "http://<?php echo $_SERVER['HTTP_HOST']; ?>/sign-in.php";
					}else{
						createCookie("productids",'');
						$("#thankYouBlock").show();
						//alert("Thanks, Supplier will contact you soon!!"); 
						window.location.href = "http://<?php echo $_SERVER['HTTP_HOST']; ?>";
					}
				  }
			});
			}
		})
	});
</script>
<?php
//echo "<pre>"; print_r($_COOKIE['productids']);echo "</pre>";
$compareidstr=str_replace("null,","",$_COOKIE['productids']);
$compareids=explode(",",$compareidstr);
if ($compareids[0] == 'null') {
	$compareidscount = 0;
}
else {
	$compareidscount=count($compareids);
}
//$compareidscount=(count($compareids)>0)?count($compareids):0;
if($_COOKIE['productids'] == ''){?>
	<script>
	window.location.href = "http://<?php echo $_SERVER['HTTP_HOST']; ?>";
	</script>
<?php }
$view_product ="SELECT * FROM `business_type` WHERE 1";
          $userArray = mysql_query($view_product);
               $userArrayRow_Type = array();
             while( $userArrayRow =mysql_fetch_array($userArray, MYSQL_ASSOC)){
				 
                $userArrayRow_Type[$userArrayRow['bsntyp_id']] = $userArrayRow['bsntyp_title'];
               // $userArrayRow_Result[$userArrayRow['usr_id']]['user_type'] = $userArrayRow['user_type'];
              }
?>
    <div class="col-lg-12 compared-container">
     	 <header style="margin-bottom:30px; border-bottom:1px solid #000; padding-bottom:5px;">
      		<h5>Compared Results (<?php echo $compareidscount;?>)</h5>
     	</header>
  <?php if($compareidscount>0) { 
  $slideblocks=ceil($compareidscount/4);
	$count=1;
	$qry="SELECT * FROM products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid INNER JOIN user on user.usr_id = products.pd_uid INNER JOIN country
 AS c ON user.country = c.cn_id  WHERE products.pd_id in (".$compareidstr.")";
	//echo $qry;
	//echo $qry="select * from products,business_profile where business_profile.bnsprof_uid=products.pd_uid and products.pd_id in (".$compareidstr.")";exit;
	$resq=mysql_query($qry);
	while($rowq=mysql_fetch_object($resq)){
		$temp_prod_data[$rowq->pd_id]=$rowq;
	}
	//echo "<pre>"; print_r($temp_prod_data);echo "</pre>";
  ?>
 
 <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
  <!-- Indicators -->
  

  <!-- Wrapper for slides -->
  <div class="carousel-inner" role="listbox">
    <div class="item active">
<?php 
foreach ($compareids as $k=>$v){ ?>
      		<div class="col-md-3 compared-box" id="prod_block<?php echo $v;?>">
			<div class="text-right"><a onclick="delprod(<?php echo $v;?>)" class="closeCls"><i class="fa fa-times"></i> </a></div>
			<header style="padding:5px;" class="titleLim">
				<a class="h4" style="font-weight:bold;" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/company/product-details.php?token=<?php echo rand(1000,9999).md5($temp_prod_data[$v]->pd_id) ?>&c=<?php echo rand(1000,9999).md5($temp_prod_data[$v]->bnsprof_id); ?>" target="_blank" title="<?php echo $temp_prod_data[$v]->pd_title;?>">
					<?php echo $temp_prod_data[$v]->pd_title;?>
				</a>
			</header>
			<figure class="img-box" >
				<?php if($temp_prod_data[$v]->bnsprof_id){

				/* webcast start */
				$sql_icon1 = "select icon_id, p_id from plan_member_id where b_id = ".$temp_prod_data[$v]->bnsprof_id;
						
                $get_icon1 = mysql_query($sql_icon1) or die(mysql_error());
                $fevrow_icon1=mysql_fetch_array($get_icon1, MYSQL_ASSOC);
                        
                $sql_icon2 = "select * from smembership_icon_plan where mp_id = ".$fevrow_icon1['icon_id'];
                $get_icon2 = mysql_query($sql_icon2) or die(mysql_error());
                $fevrow_icon2 = mysql_fetch_array($get_icon2, MYSQL_ASSOC);

                $sql_icon3 = "select * from smembership_plan where mp_id = ".$fevrow_icon1['p_id'];
                $get_icon3 = mysql_query($sql_icon3) or die(mysql_error());
                //var_dump($fevrow_icon3);
				/* webcast end */
                $sql_icon = "select smembership_plan.mst_icon as sponsericon , plan_member_id.* , smembership_icon_plan.mst_icon as producticon
                 from smembership_plan,plan_member_id , smembership_icon_plan where smembership_icon_plan.mp_id =plan_member_id.p_id and smembership_plan.mp_id =plan_member_id.p_id  and plan_member_id.b_id = ".$temp_prod_data[$v]->bnsprof_id;
                        $get_icon = mysql_query($sql_icon) or die(mysql_error());          
          if(mysql_num_rows($get_icon)){
           $fevrow_icon=mysql_fetch_array($get_icon, MYSQL_ASSOC);
        //  print_r($fevrow_icon);
            ?>				<div class="ribbon">

				<img src="./admin/images/<?php echo $fevrow_icon['sponsericon']; ?>"/>
				
				</div>
				<?php } elseif(mysql_num_rows($get_icon3)){
					$fevrow_icon3 = mysql_fetch_array($get_icon3, MYSQL_ASSOC);
					//var_dump($fevrow_icon3);
				?>
				<div class="ribbon">
				<img src="./admin/images/<?php echo $fevrow_icon3['mst_icon']; ?>"/>
				</div>
				<?php
				}
				}?>
                            <?php $pimg1= explode(',',$temp_prod_data[$v]->pd_image);  ?>
                                  <div class="zoomthis"> <?php  echo "<img src='/upload/myproduct/".$pimg1[0]."' alt='".$temp_prod_data[$v]->pd_title."' title='".$temp_prod_data[$v]->pd_title."'/>"; ?></div>
                                  <?php if(!empty($temp_prod_data[$v]->pd_imagelogo)){ 
                                  	$limg1=explode(',',$temp_prod_data[$v]->pd_imagelogo);?>
       <div class="zk" style=" border: 1px solid #267abf;height: auto;width: 100px;position: absolute;bottom: 3px;left: 1px;">
       <?php  echo "<img style='/*width: 77px; */height: 45px;' src='/upload/myproduct/".$limg1[0]."'>"; ?></div>
                 <?php  } /* ?> 
			<div class="zoomthis"> <img src="upload/myproduct/<?php echo (empty($temp_prod_data[$v]->pd_large_image))?$temp_prod_data[$v]->pd_image:$temp_prod_data[$v]->pd_large_image;?>" class="zoomthis" alt="<?php echo $temp_prod_data[$v]->pd_title;?>" title="<?php echo $temp_prod_data[$v]->pd_title;?>"/> </div>
			*/ ?></figure>
			<section>
				<table>
					<tr>
						<td>
						<img src="<?php if($fevrow_icon['sponsericon'] && $fevrow_icon3 == NULL){?>admin/images/<?php echo $fevrow_icon['producticon']; ?><?php }elseif($fevrow_icon2){?>admin/images/<?php echo $fevrow_icon2['mst_icon']; } 
						else {?>images/4.png<?php } ?>"/>
						</td>
						<td><span style="text-overflow:ellipsis; overflow:hidden;" class="titleLim"><a href="http://arabyos.com/company/profile.php?c=<?php echo rand(1000,9999).md5($temp_prod_data[$v]->bnsprof_id); ?>" target="_blank" class="h5" style="font-weight:bold;" title="<?php echo $temp_prod_data[$v]->bnsprof_compname;?>"><?php echo ucfirst(substr($temp_prod_data[$v]->bnsprof_compname,0,20).'...');?></a></span></td>
						<td></td>
					</tr>
					<tr>
							<td><img src="images/country_flag/<?php echo $temp_prod_data[$v]->cn_flag;?>"/></td>
						<td><a href="javascript:void(0)" class="h5"><?php echo $temp_prod_data[$v]->cn_name;?></a></td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td><span class=" h5">
						<?php
						$bnsprof_businesstype = $temp_prod_data[$v]->bnsprof_businesstype;
						$dataC =  explode(",",$bnsprof_businesstype);
							if($bnsprof_businesstype!=''){
								$i=1;
								foreach($dataC as $r){
						echo $userArrayRow_Type[$r];
						if($i<count($dataC)){
						echo ", " ;
						}
						$i++;
						}
						}
						else{
							echo "Not available";
						}
                                        
                        ?></span></td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td><span class="txt-bold txt-red" style="font-size:16px;"><?php echo $temp_prod_data[$v]->pd_fob_price;?> - <?php echo $temp_prod_data[$v]->pd_fob_price2;?></span> <?php $d=getCurrency($temp_prod_data[$v]->pd_currency);
$locale='en-US'; //browser or user locale
$currency=$d;
$fmt = new NumberFormatter( $locale."@currency=$currency", NumberFormatter::CURRENCY );
$symbol = $fmt->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
header("Content-Type: text/html; charset=UTF-8;");
 echo $symbol;?></td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td><span class="txt-bold txt-red" style="font-size:16px;"><?php echo $temp_prod_data[$v]->pd_min_order_qty;?> </span> <?php echo measurement_unit($temp_prod_data[$v]->pd_unit); ?> ( Min Order )</td>
						<td></td>
					</tr>
					<tr>
						<td><img src="images/mobile.png"/></td>
						<td><a href="javascript:void(0)" class="txt-black h4" style="font-weight:bold;"><?php $Countryphone = mysql_fetch_array(mysql_query("SELECT * FROM `country` where cn_id = ".$data['country'])); echo  $temp_prod_data[$v]->cn_ph;?>-<?php echo user_info($temp_prod_data[$v]->bnsprof_uid,'mobile1');?><?php //echo $temp_prod_data[$v]->bnsprof_mobile2;?></a> </td>
						<td></td>
					</tr>
					<tr>
						<td><input type="checkbox" value="<?php echo $v;?>" class="checkbox" name="suppliersChecks" id="suppliers<?php echo $v;?>" rel="<?php echo $v;?>" class="supplierCheck" rev="<?php echo $temp_prod_data[$v]->bnsprof_uid; ?>"/></td>
					   <td><a  id="btn_ajax_send<?php echo $temp_prod_data[$v]->pd_id; ?>" data-enquiry=""  class="ajax" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/company/comp_quotationRequest.php?id=<?php echo rand(1000, 9999) . md5($temp_prod_data[$v]->bnsprof_id); ?>&pid=<?php echo $temp_prod_data[$v]->pd_id; ?>&geo=<?php echo $temp_prod_data[$v]->cn_code;?>&conty=<?php echo $temp_prod_data[$v]->cn_id;?>&search=1"><button type="button" class="btn btn-sm btn-enquiry" style="width:100%; font-weight:bold;" />Send Enquiry</button></a></td>
						<td style="display: none;">Chat<img src="images/chat.png" style="width:20px; height:20px; margin-left:5px;"/></td>
					</tr>
				</table>
			</section>
            </div>
	<?php if($count>1 &&  ($count)%4==0) { 
		if($count<$compareidscount){
	?>
    </div><div class="item">
	<?php } else {echo '</div>';}} ?>
	
<?php $count++;} ?>
	</div>
    </div>
    
  <!-- Controls -->
  <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev" style="text-align:left;">
    <span class="slider-left" aria-hidden="false"> <i class="fa fa-chevron-left"></i> </span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next" style="text-align:right;">
    <span class="slider-right" aria-hidden="false"> <i class="fa fa-chevron-right"></i> </span>
    <span class="sr-only">Next</span>
  </a>
</div>
  <?php } else {
  echo '<div style="text-align:center">No Products Selected</div>';
  }?>
  
  <!--Slider Close-->
  
  <div class="row">
  		<div class="container">
        	<div class="row" style="background-color:#fff; padding:5px;">
            	<div class="col-md-2" style="padding-top:7px;">
                	<span class="h4"> Multi Enquiry </span>
                </div>
                <div class="col-md-2" style="padding-top:7px;">
                	<label>
						<input type="hidden" name="loggedInUser" id="loggedInUser" value="<?php echo $_SESSION['uid_indm']; ?>" />
                    	<input type="checkbox" style="vertical-align:sub;" id="select_all"/> Select All
                    </label>
                </div>
                <div class="col-md-8">
                	<button class="btn btn-sm border-radius-0 btn-warning" id="contactAllSupplier"><span class="h5">Contact All Suppliers</span></button>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
  </div>
 </div>
<div class="clearfix"></div>