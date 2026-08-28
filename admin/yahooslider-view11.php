<?php
/**
 * File: yahooslider-view.php
 * Description: عرض وإدارة شرائح السلايدر
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../common.php";
require_once dirname(__DIR__) . "/lib/pagination.php";

check_admin_login();



class sliderviewlist{
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
	    $chquesql="select * from yahoo_slider where adv_id ='".$adid."' ";
		$chqueres=mysqli_query($con, $chquesql);
		$chquerow=mysqli_fetch_array( $chqueres);
		$path="../upload/yahoo_slider/".$chquerow['adv_img'] ;
		unlink($path);
		$sql_del="delete from yahoo_slider where adv_id='".$adid."'";
		mysqli_query($con, $sql_del);
	}
	function deletelink($id){
		if($_SERVER['QUERY_STRING']==""){
			$dellink="?action=del&fid=".$id;
		}
		else{
			$dellink="yahooslider-view.php?".$_SERVER['QUERY_STRING']."&action=del&fid=".$id;
		}
		return $dellink;
	}
}

$pagination = new Pagination();
$page = $pagination->getCurrentPage();

$al=new sliderviewlist;
/********************delete record*********************/
	if(isset($_GET['action']) && $_GET['action']=="del"){
		$al->deleterecord($_GET['fid']);
		header("location:yahooslider-view.php");
		//header("location:yahooslider-view.php?".rtrim($_SERVER['QUERY_STRING'],"&action=del&id=".$_GET['id']));
		}
/***********************************************/

$al->limit=$pagination->getLimit(20);
$al->setsql("select * from yahoo_slider order by adv_updated_date desc");
$totalitems=$al->totalrecord();
$limit=$al->limit;
$al->start=$pagination->getStart($page,$limit,$totalitems);
$adjacents=1;
$targetpage = "yahooslider-view.php";

$pagestring ="?limit=".$limit."&page=";

$recObj=$al->listview();

$showitems=$al->start+1 ." - ";
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
		header("location:yahooslider-view.php");
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
					<a href="yahooslider-view.php">Manage Slider</a>
				</li>
				<li class="active">View Slider</li>
		</ul><!-- .breadcrumb -->
					<!-- #nav-search -->
	</div>
<div class="page-content">
<form name="myform" id="myform" method="post"> 
<div class="row">
<div class="col-xs-12">
<div class="table-header">
<button class="btn btn-xs btn-danger" name="btnDelete" type="submit" onClick="return confirm('Are you sure to delete the record?')" ><i class="icon-trash bigger-120"></i>Delete</button>
 <button type="button" class="btn btn-xs btn-success" onClick="window.location='yahooslider-add.php' "><i class="icon-pencil align-top bigger-120"></i>Add Slider</button>
 </div>
 <div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <!-- ... -->
     </table>
</div>
<div class="table-responsive">
    <table id="sample-table-2" class="table table-striped table-bordered table-hover">
        <thead>
             <th class="center"><input type="checkbox" class="ace" id="selectAll"><span class="lbl"></span></th>
             <th><strong>Date</strong></th>
             <th><strong>Image</strong></th>
             <th><strong>Type</strong></th>
             <th><strong>By</strong></th>
             <th><strong>Company</strong></th>
             <th><strong>Email</strong></th>
             <th><strong>Mobile</strong></th>
             <th><strong>Country</strong></th>
             <th><strong>Action</strong></th>
        </thead>
        <tbody>
            <?php
            $count = $recObj ? mysqli_num_rows($recObj) : 0;
            if ($count > 0):
                while ($row = mysqli_fetch_object($recObj)):
            ?>
            <tr id="row_<?php echo (int)$row->adv_id; ?>">
                <td class="center">
                    <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row->adv_id; ?>">
                    <span class="lbl"></span>
                </td>
                <td><?php echo date('d M, y', strtotime($row->adv_updated_date ?? 'now')); ?></td>
                <td><img src="../upload/yahoo_slider/<?php echo htmlspecialchars($row->adv_img ?? ''); ?>" style="width: 80px; height: 60px; object-fit: cover;"></td>
                <td><?php echo htmlspecialchars(ucfirst($row->adv_type ?? '')); ?></td>
                <td><?php echo htmlspecialchars($row->adv_title ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row->adv_company ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row->adv_email ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row->adv_mobile ?? ''); ?></td>
                <td><?php echo get_country_name((int)($row->adv_country ?? 0)); ?></td>
                <td class="action" style="text-align:center;">
                    <a href="yahooslider-edit.php?id=<?php echo (int)$row->adv_id; ?>" title="Edit">
                        <img alt="edit" src="images/edit.jpg">
                    </a>
                    <a href="<?php echo $al->deletelink((int)$row->adv_id); ?>" title="delete" onclick="return confirm('Are you sure to delete the record?')">
                        <img alt="delete" src="images/delete.jpg" border="0">
                    </a>
                </td>
            </tr>
            <?php 
                endwhile;
            endif;
            ?>
        </tbody>
    </table>
</div>
<?php $j=0;
$count=mysqli_num_rows($recObj);
	if($count >0)
	{
		while($row=mysqli_fetch_object($recObj)){?>
        <tr>
	        <td class="center"><label><input name="cb[]" class="ace" type="checkbox" value="<?php echo $row->adv_id; ?>" /><span class="lbl"></span></label></td>
			<td style="text-align:center;"><img src="../upload/yahoo_slider/<?php echo $row->adv_img; ?>" width="200px;" height="150px;"/></td>
			<td style="text-align:center">
			<?php echo '<strong>Link:</strong> '.$row->adv_link;?>
			<?php echo '<br/><br/><strong>Title:</strong> '.$row->adv_title;?>
			<?php 
			  echo '<br/><strong>Description:</strong> '.$row->adv_description;
			 $country = $row->adv_country;
			 if($country!="")
			 {
				 $countrylist = explode(",",$country);
				 $country2show="";
				 foreach($countrylist as $snglecntry)
				 {
					 if($country2show=='')
					 {
						 $country2show = get_country_name((int)$snglecntry);
					 }
					 else
					 {
						 $country2show .= ",".get_country_name((int)$snglecntry); 
					 }
				 }
				 echo '<br/><strong>Country:</strong> '.$country2show;
			 }
			?>
			</td>
			<td style="text-align:center"><?php echo $row->adv_imagewidth." x ".$row->adv_imageheight;?></td>
            <td style="width:90px; text-align:center"><?php if($row->adv_status == '1'){ ?><font color=green>Active</font><?php }else if($row->adv_status == '0'){ ?><font color=red>Inactive</font><?php } ?></td>   
			<td style="text-align:center;">
		     <select id="" onchange="changeStatus(this.value,'<?php echo $row->adv_id;?>')" >
    	     <option value="">Select</option>
        	  <?php if($row->adv_status == '1'){?>
	          <option value="0">Deactivate</option>
    	      <?php }else{ ?>
	          <option value="1">Activate</option>
    	      <?php }?>
          <script>
          	function changeStatus(stat,id)
			{
				$.post("ajax-file/yahooslider-change-status.php", {stat:stat,id:id},
         		function(data){
				location.reload();		
        		});
			}
		  </script>
	   	</td>  
            
            <td align="center">
     <a href="yahooslider-edit.php?aid=<?php echo $row->adv_id; ?>" title="edit"><img alt="edit" src="images/edit.jpg" border="0"></a>
     <a href="<?php echo $al->deletelink($row->adv_id)?>" title="delete" onclick="return confirm('Are you sure to delete the record?')"><img alt="delete" src="images/delete.jpg" border="0"></a>
            </td>
        </tr>
        <?php $j++; } }?>
</tbody>
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
				"aoColumns": [
			      { "bSortable": false },{ "bSortable": false },
			      null,null,null,
				  { "bSortable": false },{ "bSortable": false }
				] } );
				
				
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
			})
		</script>
		<style>
<style>
.table-responsive {
    overflow-x: auto;
}
.table {
    min-width: 1000px;
}
.table img {
    max-width: 80px;
    height: auto;
}
.table th, .table td {
    white-space: nowrap;
    padding: 8px 6px;
}
.table td:first-child, .table th:first-child {
    width: 40px;
}
.table td:last-child, .table th:last-child {
    width: 80px;
}
</style>
<style>
.table-responsive {
    overflow-x: auto;
}
.table {
    min-width: 1000px;
}
.table img {
    max-width: 80px;
    height: auto;
    object-fit: cover;
}
.table th, .table td {
    white-space: nowrap;
    padding: 8px 6px;
    vertical-align: middle;
}
.table td:first-child, .table th:first-child {
    width: 40px;
}
.table td:last-child, .table th:last-child {
    width: 80px;
}
</style>
</body>
</html>