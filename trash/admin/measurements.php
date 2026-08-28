
<?php 
ob_start();
session_start(); 
include "../common.php";
//include "lib/pagination.php";

check_user_login();
$recObj = mysqli_query($con, "SELECT * FROM measurement_unit_arabyos ORDER BY mu_id ASC");

?>
<?php include "includes/admin-top.php" ?>
<div class="main-container" id="main-container">
	<script type="text/javascript">
		try{ace.settings.check('main-container' ,'fixed')}catch(e){}
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
			<!--<li>
				<a>Manage Qualification</a>
			</li>-->
			<li class="active">View Admin</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
<div class="page-content">
<div class="row">
	<?php 
	function checkEmpty($str){
	  $check_emty = preg_replace('/\s+/', '', $str);
	  return $check_emty == "" ? true: false;
	}
	if (isset($_GET['delete'])) {
		$id = mysqli_real_escape_string($con, $_GET['delete']);
		if (!checkEmpty($id)) {
			mysqli_query($con, "DELETE FROM measurement_unit_arabyos WHERE mu_id = '$id'");
		}
		header("location: https://arabyos.com/admin/measurements.php");
	}
	if (isset($_POST['save_mes'])) {
		$name = mysqli_real_escape_string($con, $_POST['measurement']);
		if (!checkEmpty($name)) {

			if(mysqli_query($con, "INSERT INTO measurement_unit_arabyos(mu_name, mu_status) VALUES('$name','1')")){
				header("location: https://arabyos.com/admin/measurements.php");	
			}else{
				header("location: https://arabyos.com/admin/measurements.php");
			};
		}else{
			header("location: https://arabyos.com/admin/measurements.php?");
		}
	}
	if(isset($_POST['update_mes'])){
		$name = mysqli_real_escape_string($con, $_POST['measurement']);
		$id = mysqli_real_escape_string($con, $_POST['measurement_id']);
		if (!checkEmpty($name) && !checkEmpty($id)) {
			mysqli_query($con, "UPDATE measurement_unit_arabyos SET mu_name = '$name' WHERE mu_id = '$id'");
		}
		header("location: https://arabyos.com/admin/measurements.php");
	}
	?>
	<div class="col-xs-12">
		<form method="post"> 
			<div class="input-group" style="width: 100%; margin-bottom: 20px;">
				<?php if (isset($_GET['edit'])): ?>
					<?php 
						$id = mysqli_real_escape_string($con, $_GET['edit']);
						$query = mysqli_query($con,"SELECT * FROM measurement_unit_arabyos WHERE mu_id = '$id'");
						$fetch = mysqli_fetch_array($query);
					?>
					<input type="hidden" name="measurement_id" value="<?php echo $fetch['mu_id']; ?>">
					<input type="text" class="form-control" placeholder="Input Measurement Name" name="measurement" value="<?php echo $fetch['mu_name']; ?>">
					<input type="submit" class="form-control btn btn-primary" value="Update" name="update_mes">
				<?php else: ?>
					<input type="text" class="form-control" placeholder="Input Measurement Name" name="measurement">
					<input type="submit" class="form-control btn btn-primary" value="Add New" name="save_mes">
				<?php endif ?>
			</div>
		</form>
	</div>

	<br>
<div class="col-xs-12">
<div class="table-responsive">
	<?php 
	// echo "We have results in measurement : " . mysqli_num_rows($recObj);
	// while($raw = mysqli_fetch_array($recObj)){
	// 	echo $raw[0]. "<br>";
	// }
	?>
<table id="sample-table-2" class="table table-striped table-bordered table-hover">
<thead>
<tr>
    <th><strong>ID</strong></th>
    <th><strong>Name</strong></th>
    <th style="text-align:center"><strong>Status</strong></th>
</thead>
<tbody>
    	<?php $j=0;
		$count=mysqli_num_rows($recObj);
		if($count >0){
			while($row=mysqli_fetch_object($recObj)){?>
		        <tr>
		        	<td>
			        	<?php echo $row->mu_id; ?>		
			        </td>
			        <td>
			        	<?php echo $row->mu_name; ?>		
			        </td>
			        <td align="center">
			        	<button class="btn btn-success"><a href="?edit=<?php echo $row->mu_id ;?>"> Edit</button>
			        	<button class="btn btn-danger"><a href="?delete=<?php echo $row->mu_id ;?>"> Delete</button>
			        </td>
		        </tr>
		        <?php $j++; 
	    	} 
    	}?>
</tbody>
</table>
</div></div></div>
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
				$('#sample-table-2').dataTable();
				
				
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
</body>
</html>