<?php 
include "../common.php";
check_user_login();

class AdminLoginlist{
	/*var $start="";
	var $limit="";*/
	var $sqlList="";
	var $start="";
	var $limit="";
	
	function setsql($sql){
		$this->sqlList=$sql;
	}
	function totalrecord(){
		global $con;
		return mysqli_num_rows(mysqli_query($con, $this->sqlList));
	}
	function listview(){
		global $con;
		$sql=$this->sqlList;
		$res=mysqli_query($con, $sql);
		return $res;
	}
	/*function fetchRecord(){
		return mysqli_fetch_object($this->listview());
	}*/
	function numpage($rowPage){
		 return floor($this->totalrecord()/$rowPage);
	}
	function deleterecord($adid){
		global $con;
		//echo "delete from company where usr_id='".$adid."'";exit;
		mysqli_query($con, "delete from user where usr_id='".$adid."'");
		mysqli_query($con, "delete from business_profile where bnsprof_uid='".$adid."'");
	}
	function deletelink($id){
		if($_SERVER['QUERY_STRING']==""){
			$dellink="?action=del&ad-id=".$id;
		}
		else{
			$dellink=$_SERVER['QUERY_STRING']."&action=del&ad-id=".$id;
		}
		return $dellink;
	}
}


$p=new Pagination;
$page=$p->setpage();

$al=new AdminLoginlist;
/********************delete record*********************/
	if($_GET['action']=="del"){
		echo $_GET['ad-id'];
		$al->deleterecord($_GET['ad-id']);
		header("location:company-list.php");
		//header("location:company-list.php?".rtrim($_SERVER['QUERY_STRING'],"&action=del&id=".$_GET['id']));
		}
/*************************************************/

$al->limit=$p->setlimit(10);
$al->setsql("select * from country,business_profile bf JOIN user u ON u.usr_id = bf.bnsprof_uid LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id where country=cn_id and bnsprof_uid=usr_id and status='1' order by usr_id DESC");
$totalitems=$al->totalrecord();
$limit=$al->limit;
$al->start=$p->setstart($page,$limit,$totalitems);
$adjacents=1;
$targetpage = "company-list.php";

$pagestring ="?limit=".$limit."&page=";

$recObj=$al->listview();

$showitems=$al->start+1 ."-";
if(($al->start+$limit)<$totalitems){
	$showitems.=$al->start+$limit;
}
else{
	$showitems.=$totalitems;
}
	$showitems.= " of ". $al->totalrecord()." items";
	//echo $_SERVER['QUERY_STRING'];
	if(isset($_POST['btnDelete'])){
		foreach($_POST['cb'] as $id){
			$al->deleterecord($id);
		}
		header("location:company-list.php");
	}
	
?>
<?php include "includes/admin-top.php" ?>
<div class="main-container" id="main-container">
	<script type="text/javascript">
		try{ace.settings.check('main-container' , 'fixed')}catch(e){}
	</script>

	<div class="main-container-inner">
		<a class="menu-toggler" id="menu-toggler" href="#">
			<span class="menu-text"></span>
		</a>
<?php include "includes/admin-left-con.php" ?>
<div class="main-content">
	<div class="breadcrumbs" id="breadcrumbs">
		<script type="text/javascript">
			try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
		</script>

		<ul class="breadcrumb">
			<li>
				<i class="icon-home home-icon"></i>
					<a href="welcome.php">Home</a>
				</li>
				<li>
					<a href="company-list.php">Manage Company Profile</a>
				</li>
				<li class="active">View Company</li>
		</ul><!-- .breadcrumb -->
					<!-- #nav-search -->
	</div>
<div class="page-content">
<form name="myform" id="myform" method="post"> 
<div class="row">
<div class="col-xs-12">
<div class="table-header">
<button class="btn btn-xs btn-danger" name="btnDelete" type="submit" onClick="return confirm('Are you sure to delete the record?')" ><i class="icon-trash bigger-120"></i>Delete</button>

<p style="display: inline-block;float: right;">Go to Page No : <input type="number" name="page_no" id="page_no" class="page_no" min="1" /></p>

 <p>
      <input type="text" id="mySearchText" placeholder="Search...">
      <button id="mySearchButton">Search</button>
    </p>
</div>
<div class="table-responsive">
<table id="sample-table-2" class="table table-striped table-bordered table-hover">
<thead>
<tr>
	<th class="center"><label><input type="checkbox" class="ace" ><span class="lbl"></span></label></th>
    <th><strong>Date</strong></th>
    <th><strong>Name</strong></th>
    <th><strong>Web Address</strong></th>
    <th><strong>User</strong></th>
    <th><strong>Mobile Number</strong></th>
    <th><strong>Email</strong></th>
    
    <th><strong>Country</strong></th>
    <th><strong>State</strong></th>
    <th><strong>City</strong></th>
    <th style="text-align:center"><strong>Membership type</strong></th>
	<th style="text-align:center"><strong>Membership Expired On</strong></th>
    <th style="text-align:center"><strong>Status</strong></th>
    <th><strong>&nbsp;</strong></th>
    <th style="text-align:center"><strong>Action</strong></th>
</thead>
<?php /* <tbody>
<?php $j=0;
$count=mysqli_num_rows($recObj);
	if($count >0)
	{
		
		while($row=mysqli_fetch_object($recObj)){ 
                
		?>
        <tr>
			
			
			
			
			<td class="center"><label><input name="cb[]"  class="ace" type="checkbox" value="<?php echo $row->usr_id; ?>" /><span class="lbl"></span></label></td>
            <td><?php echo ucwords($row->bnsprof_compname); ?></td>
            <td><a href="user-details.php?token=<?php echo rand(1000,9999).md5($row->usr_id); ?>" target="_blank"><?php echo ucwords($row->name_prefix." ".$row->lname." ".$row->fname); ?></a></td>
			<td><?php echo $row->email; ?></td>    
           
                        <td><?php echo $row->cn_name; ?></td>

		  <td><?php echo  (($row->expiry_date != '')?(date("d F Y", $row->expiry_date)):'').' '. ((date("Y-m-d", $row->expiry_date) > date("Y-m-d"))?'Active':'Inactive'); ?>
		  </td>
            <td>
	    <?php  if($row->usr_emailVerify == '0'){echo '<font color=red>Email not verified</font>';}elseif($row->usr_emailVerify == '1'){echo '<font color=green>Email verified</font>';} ?>
	    </td>
            <td style="text-align:center">
           	<a href="company-details.php?token=<?php echo md5($row->bnsprof_id); ?>"><img src="images/details.png" /></a>
            </td>
        </tr>
        <?php $j++; } }?>
</tbody> */ ?>
</table>
</div></div></div></form>
	<br clear="all"/>
 </div>

 </div>
 </div>
 <br clear="all" />

 </div>  
  <?php include "includes/footer.php" ?>
<script type="text/javascript">
			window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
		</script>
 <script type="text/javascript">
			if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
		</script>
		<script src="assets/js/bootstrap.min.js"></script>
		<script src="assets/js/typeahead-bs2.min.js"></script>

		<!-- page specific plugin scripts -->

		<script src="assets/js/jquery.dataTables.min.js"></script>
		<script src="assets/js/jquery.dataTables.bootstrap.js"></script>

		<!-- ace scripts -->

		<script src="assets/js/ace-elements.min.js"></script>
		<script src="assets/js/ace.min.js"></script>

		<!-- inline scripts related to this page -->

		<script type="text/javascript">
			jQuery(function($) {
				var oTable1 = $('#sample-table-2').dataTable( {
				"ajax":{
					url :"company-list-response.php", // json datasource
					type: "post",  // type of method  , by default would be get
					error: function(){  // error handling code
					 $("#sample-table-2").css("display","none");
					}
				  },
				"lengthMenu": [ 100,200,500 ],
				  "bProcessing": true,
				bFilter: false,
        		ordering: false,
        		searching: true,
/*
'l' - Length changing
'f' - Filtering input
't' - The table!
'i' - Information
'p' - Pagination
'r' - pRocessing*/
        		dom: 'tpl',         // This shows just the table
				 "serverSide": true,
				"aoColumns": [
			      { "bSortable": false },
			      null,null,null,null,null,null,null,null,null,null,null,
				  { "bSortable": false },{ "bSortable": false },{ "bSortable": false }
				],
					"serverParams": function ( aoData ) {

					},
					"drawCallback": function( settings ) {
						$("#overlay").hide();
					}
				} );
				$("#page_no").on('keyup',function(){
					if($(this).val()!=''){
						oTable1.fnPageChange(parseInt($(this).val())-1);
					}
					else{
						oTable1.fnPageChange(0);
					}
					
				});
				
				$('table th input:checkbox').on('click' , function(){
					var that = this;
					$(this).closest('table').find('tr > td:first-child input:checkbox')
					.each(function(){
						this.checked = that.checked;
						$(this).closest('tr').toggleClass('selected');
					});
						
				});
			
			
				$('[data-rel="tooltip"]').tooltip({placement: tooltip_placement});
				function tooltip_placement(context, source) {
					var $source = $(source);
					var $parent = $source.closest('table')
					var off1 = $parent.offset();
					var w1 = $parent.width();
			
					var off2 = $source.offset();
					var w2 = $source.width();
			
					if( parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2) ) return 'right';
					return 'left';
				}
				
  
  $('#mySearchButton').on( 'keyup click', function () {
  	event.preventDefault();alert(oTable1);console.log(oTable1);
    oTable1.fnFilter($('#mySearchText').val());//.search.draw()
    return false;
  } );
				
			})
			
			function verifyNow(id, elem) {
				$.post('company-list-response.php', {verifyId: id}, function(response) {
    				// Log the response to the console
    				console.log("Response: "+response);//console.log
    				$('#sample-table-2').find('#verify-link-'+id).html('Email sent!');
    				//$(elem).parent().html('Email sent');
				});
			}
		</script>
</body>
</html>