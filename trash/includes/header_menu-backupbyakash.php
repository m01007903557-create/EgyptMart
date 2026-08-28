<script>
function downMenu(id)
{
	$("#dropmenu"+id).css('visibility','visible');	
}
function upMenu(id)
{
	$("#dropmenu"+id).css('visibility','hidden');	
}
</script>
<!--feedback widget:ends-->


<div class="n-nmz1_exm1 n-nmz2 bx pns14 ml2" id="chromemenu" style="width:100%">
	<a name="addproduct"></a>
	<a name="inbox"></a>
	<a href="my-dashboard.php" class="n-nmz1 bnr fl"><img src="sitelogo/<?php echo get_page_settings(22); ?>" height="41px" width="130px"/></a>
	<ul class="n-hdrn">
		<li class="f2 buss-n fw" rel="dropmenu6"  onMouseOver="downMenu(6);" onMouseOut="upMenu(6);" style="position:static;">Account Details<span class="n-hdrn2">&nbsp;</span>
        <!--business offers for you--><div style="height: auto; overflow: hidden; visibility: hidden; right: 0px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu6" class="ddm" onMouseOver="downMenu(6);" onMouseOut="upMenu(6);">
		<a href="my-contactdetails.php">Contact Details</a>
		<a href="change-password.php">Change Password</a>
        <?php if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm']!=''){	?>
        <a href="logout.php">Sign Out</a>
        <?php } ?>
		</div>
         </li>
		<li rel="dropmenu1" class="" style="margin-left:0px" onMouseOver="downMenu(1);" onMouseOut="upMenu(1);" style="position:static;">Company Profile <span>&nbsp;</span>
        
        <!--company profile-->
        <div style=" position:absolute; height: auto; overflow: hidden; visibility: hidden; left: 150px; <?php if($file == 'post-buy-req'){?>top: 253px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu1" class="ddm" onMouseOver="downMenu(1);" onMouseOut="upMenu(1);">
                <a href="my-contactdetails.php">Contact Details</a>
		<a href="business-details.php">Business Details</a>
   		<span>Website Pages</span>
		<a href="my-homepage.php">My Home Page</a>
		<a href="statutory-details.php">Statutory Details</a>
		<a href="myprofile.php">Profile & News</a>
		</div>        
         </li>
         
		<li rel="dropmenu2" class="" onMouseOver="downMenu(2);" onMouseOut="upMenu(2);" style="position:static;">Enquiries &amp; Contacts <span>&nbsp;</span>
        <!--Enquiries & Contacts--><div style=" position:absolute; width: 190px; height: auto; overflow: hidden; visibility: hidden; left: 300px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu2" class="ddm" onMouseOver="downMenu(2);" onMouseOut="upMenu(2);">
		<a href="my-enquiries.php">Inbox</a>
		<a href="my-enquiries.php">Sent Box</a>
		<span>Address Book</span>
		<a href="my-addressbook.php">Contacts List</a>
		<a href="manage-purchased-buyleads.php">Purchased Buy Requests</a>
		</div>
        </li>
        
		<li rel="dropmenu3" class="" onMouseOver="downMenu(3);" onMouseOut="upMenu(3);" style="position:static;">Buy Requets<span>&nbsp;</span>
        <!--buy leads-->
		<div style=" position:absolute; height: auto; overflow: hidden; visibility: hidden; left: 480px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu3" class="ddm" onMouseOver="downMenu(3);" onMouseOut="upMenu(3);">
			<span onMouseOver="downMenu(3);" onMouseOut="upMenu(3);">Buy Requests Purchases</span>
			<a href="manage-purchased-buyleads.php">Purchased Buy Requests</a>
			<a href="transaction_history.php">Transaction History</a>
		</div>
        </li>
        
		<li rel="dropmenu4" class="buyercss1 firfox-css" style="padding-right:4px" onMouseOver="downMenu(4);" onMouseOut="upMenu(4);" id="myproducts" style="position:static;">Seller Tools <span>&nbsp;</span>
        <!-- Seller Tools --><div style=" position:absolute; height: auto; overflow: hidden; visibility: hidden; left: 590px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu4" class="ddm" onMouseOver="downMenu(4);" onMouseOut="upMenu(4);">
		<span>Products/Services</span>
			<?php if(get_membership_expired()!=false){ ?>
		 <a href="product-add.php">Add New Products</a>
					<?php }else{ ?>
		 <a href="membership_plans.php">Add New Products</a>

					<?php } ?>
       
        <a href="product-list.php">Manage Products</a> 
		<span>Buy Leads</span>
		<a href="manage-purchased-buyleads.php">Purchased Buy Requests</a>
        <a href="manage-buylead-alert.php">Manage Buy Leads Alerts</a>
		<span>Sell Offers</span>
		<a href="post-sell-offer.php">Post a Sale Offer</a>
		<a href="manage-sell-offer.php">Manage Sale Offer</a>
               <span> Regular Sell Products</span>
          <a href="myproduct-sell.php">Products We Sell</a>
		</div>
         </li>
		
        <li rel="dropmenu5" class="" style="padding-right:4px;" onMouseOver="downMenu(5);" onMouseOut="upMenu(5);">Buyer Tools <span>&nbsp;</span>
        <!--Buyer Tools--><div style=" position:absolute; height: auto; overflow: hidden; visibility: hidden; left: 710px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu5" class="ddm" onMouseOver="downMenu(5);" onMouseOut="upMenu(5);">
       
			<a href="post-buy-req.php">Post a Buy Requirement</a>
			<a href="manage-buy-requirement.php">Manage Buy Requirements</a>
			<a href="manage-selloffer-alert.php">Subscribe Sell Offers Alerts</a>
			<a href="search_adv.php">Search Products &amp; Suppliers</a>
                   
                  <a href="myproduct-buy.php">Products We Buy</a>

		</div>
        </li>
        
        <li rel="dropmenu7" class="" style="padding-right:2px;" onMouseOver="downMenu(7);" onMouseOut="upMenu(7);">Tenders / Auctions <span>&nbsp;</span>
        <!--Tender--><div style=" position:absolute; height: auto; overflow: hidden; visibility: hidden; left: 825px; <?php if($file == 'post-buy-req'){?>top: 146px;<?php }else{?>top: 253px;<?php }?>" id="dropmenu7" class="ddm" onMouseOver="downMenu(7);" onMouseOut="upMenu(7);">
			<a href="post-tender.php">Post Tender FREE</a>
			<a href="manage-tenders.php">Manage Tenders</a>
             <a href="manage-purchased-tenders.php">Purchased Tenders</a>
             <a href="manage-tender-alert.php">Manage Tender Alert</a>
             

		</div>
        </li>
        
       
		</div>
        </li>
		<!--[if IE 8]><style>.buyercss1{padding-right:1px!important;}</style><![endif]-->
	</ul>
</div>
