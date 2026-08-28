<script src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/js/jquery.colorbox.js"></script>
<link href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/css/colorbox.css" type="text/css" rel="stylesheet">
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
		});
</script>

<?php
//print_r($_COOKIE['fpids']);

 $favourites = mysql_query("SELECT * FROM favourites_table where user_id='".$_SESSION['uid_indm']."'");
$compareidstr=array();
while($row=mysql_fetch_object($favourites)){
	$compareidstr[]=$row->user_id.'-'.$row->pro_id;
}
//$compareidstr=explode(",",$_COOKIE['fpids']);
$tempallids='';
$i = 0;
foreach ($compareidstr as $tempid){
	$datacheck  =explode("-",$tempid);
	if($datacheck[0]==$_SESSION['uid_indm']) {
	if($tempid=='null' || $tempid==''){
		//do nothing
	} else {
		$i++;
		$tempallids[$datacheck[1]]=$datacheck[1];
		$x += 1;
		
	}
	}
	
}

 $compareids=$tempallids;
$compareidstr=implode(",",$tempallids);
$compareidscount=(count($compareids)>0)?count($compareids):0;

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
      		<h5>My Favourite Products (<?php if($x==""){ echo '0';}else { echo $x; }?>)</h5>
     	</header>
  <?php if($compareidscount>0) { 
  $slideblocks=ceil($compareidscount/4);
	$count=1;
	$qry="SELECT * FROM products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid INNER JOIN user on user.usr_id = products.pd_uid INNER JOIN country ON user.country = country.cn_id INNER JOIN city ON business_profile.bnsprof_city = city.ct_id WHERE products.pd_id in (".$compareidstr.")";
	//echo $qry="select * from products,business_profile where business_profile.bnsprof_uid=products.pd_uid and products.pd_id in (".$compareidstr.")";exit;
	$resq=mysql_query($qry);
	while($rowq=mysql_fetch_object($resq)){
		$temp_prod_data[$rowq->pd_id]=$rowq;
	}
  ?>
 
 <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
  <!-- Indicators -->
  

  <!-- Wrapper for slides -->
  <div class="carousel-inner" role="listbox">
    <div class="item active">
<?php 
foreach ($compareids as $k=>$v){ ?>
      		<div class="col-md-3 col-sm-6 col-xs-12 compared-box" id="prod_block<?php echo $v;?>">
			<div class="text-right"><a href="javascript:void(0);"  onclick="delprodfav(<?php echo $_SESSION['uid_indm'];?>,<?php echo $v;?>)" class="closeCls" ><i class="fa fa-times"></i> </a></div>
			<header style="padding:5px;" class="titleLim">
				<a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/company/product-details.php?token=<?php echo rand(1000,9999).md5($temp_prod_data[$v]->pd_id) ?>&c=<?php echo rand(1000,9999).md5($temp_prod_data[$v]->bnsprof_id); ?>" target="_blank" class="h4" style="font-weight:bold;" title="<?php echo $temp_prod_data[$v]->pd_title;?>"><?php echo $temp_prod_data[$v]->pd_title;?>
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
				<?php } 
				elseif(mysql_num_rows($get_icon3)){
					$fevrow_icon3 = mysql_fetch_array($get_icon3, MYSQL_ASSOC);
					//var_dump($fevrow_icon3);
				?>
				<div class="ribbon">
				<img src="./admin/images/<?php echo $fevrow_icon3['mst_icon']; ?>"/>
				</div>
				<?php
				}
				}?>
			<div class="zoomthis"> <img src="upload/myproduct/<?php echo (empty($temp_prod_data[$v]->pd_large_image))?$temp_prod_data[$v]->pd_image:$temp_prod_data[$v]->pd_large_image;?>" class="zoomthis" alt="<?php echo $temp_prod_data[$v]->pd_title;?>" title="<?php echo $temp_prod_data[$v]->pd_title;?>"/> </div>
			</figure>
			<section>
				<table>
					<tr>
						<td>
						<img src="<?php if($temp_prod_data[$v]->bnsprof_id && $fevrow_icon['sponsericon']){?>admin/images/<?php echo $fevrow_icon['producticon']; ?><?php } 
						elseif(mysql_num_rows($get_icon2)){
							$fevrow_icon2 = mysql_fetch_array($get_icon2, MYSQL_ASSOC);
						?>
						admin/images/<?php echo $fevrow_icon2['mst_icon']; ?>
						<?php
						}
						else { ?>admin/images/1543744425PROMO-icaon.png<?php } ?>"/></td>
						
						<td><span style="text-overflow:ellipsis; overflow:hidden;" class="titleLim"><a href="https://arabyos.com/company/profile.php?c=<?php echo rand(1000,9999).md5($temp_prod_data[$v]->bnsprof_id); ?>" target="_blank" class="h5" style="font-weight:bold;" title="<?php echo $temp_prod_data[$v]->bnsprof_compname;?>"><?php echo ucfirst(substr($temp_prod_data[$v]->bnsprof_compname,0,20).'...');?></a></span></td>
						<td></td>
					</tr>
					<tr>
							<td><img src="images/country_flag/<?php echo $temp_prod_data[$v]->cn_flag;?>"/></td>
						<td><a href="javascript:void(0)" class="h5"><?php echo $temp_prod_data[$v]->cn_name;?></a></td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td><span class=" h5"><?php
										
                                          $bnsprof_businesstype = $temp_prod_data[$v]->bnsprof_businesstype;
										//  print_r($bnsprof_businesstype); die;
                                        $dataC =  explode(",",$bnsprof_businesstype);
										//print_r($dataC);
										//print_r($userArrayRow_Type); die;
										//if(in_array($dataC)){
											if($bnsprof_businesstype!=''){
												//print_r($dataC);
												$i=1;
												foreach($dataC as $r){
											//echo $r ;
											//if($r!=''){
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
                                        
                                          //$userArrayRow_Type ?></span></td>
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
						<td><a href="javascript:void(0)" class="txt-black h4" style="font-weight:bold;"><?php $Countryphone =mysql_fetch_array(mysql_query("SELECT * FROM `country` where cn_id = ".$data['country'])); echo  $temp_prod_data[$v]->cn_ph;?>-<?php echo $temp_prod_data[$v]->mobile1;?></a> </td>
						<td></td>
					</tr>
					<tr>
						<td><input type="checkbox" id="<?php echo $v;?>" rel="prod_block<?php echo $v;?>" class="checkbox"/></td>
					   <td><a  id="btn_ajax_send<?php echo $temp_prod_data[$v]->pd_id; ?>" data-enquiry=""  class="ajax" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/company/fav_quotationRequest.php?id=<?php echo rand(1000, 9999) . md5($temp_prod_data[$v]->bnsprof_id); ?>&pid=<?php echo $temp_prod_data[$v]->pd_id; ?>&geo=<?php echo $temp_prod_data[$v]->cn_code;?>&conty=<?php echo $temp_prod_data[$v]->cn_id;?>&search=1"><button type="button" class="btn btn-sm btn-enquiry" style="width:100%; font-weight:bold;" onclick="delprod('fav')"/>Send Enquiry</button></a></td>
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
        	<div class="row" style="background-color:#c5e4f8; padding:5px;">
            	<div class="col-md-3" style="padding-top:7px;">
                	<span class="h4"> My Favourite Products </span>
                </div>
                <div class="col-md-2" style="padding-top:7px;">
                	<label>
                    	<input style="vertical-align:sub;" type="checkbox" id="select_all" /> Select All
                    </label>
                </div>
                <div class="col-md-7">
                	<!--<button class="btn btn-sm border-radius-0 btn-default"><span class="h5">Add/Edit Folder</span></button>-->
                    
                    <button class="btn btn-sm border-radius-0 btn-default" onclick="delprod()"><span class="h5">Delete</span></button>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
  </div>
    </div>
    <div class="clearfix"></div>
