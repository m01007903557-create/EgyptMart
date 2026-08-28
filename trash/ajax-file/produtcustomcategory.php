<?php
error_reporting(0);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
//include "common.php";
if(!session_id()){
  session_start();
}

if(!isset($_POST['keywordsFilter'])){
  echo '<script>alert("Enter  keyword first");</script>';
exit();
}
$searchedproducts = $_SESSION['searchedproducts'];

if(!$searchedproducts && !array_key_exists($_POST['keywordsFilter'],$searchedproducts))  {
    echo '<script>alert("No product found with that name");</script>';
exit();
}
$keywordsFilters = $_POST['keywordsFilter'];
$_POST['keywordsFilter'] = end(explode(">>",$_POST['keywordsFilter']));
$id = $searchedproducts[$_POST['keywordsFilter']];
 include "../common.php";

$sql="insert into temp_selloffer_alert_cat
	set
		tsac_usr_id='".$_SESSION['uid_indm']."',
		tsac_pc_id='".$id."',
		tsac_updated_date=now()";
if(isset($_POST['type']) && $_POST['type']=='addTempAuctionAlertCat')
{
 $sql="insert into temp_auction_alert_cat
	set
		taac_usr_id='".$_SESSION['uid_indm']."',
		taac_pc_id='".$id."',
		taac_updated_date=now()";

}
elseif($_POST['type']=='addTempBuyleadAlertCat')
{
    $sql="insert into temp_buylead_alert_cat
	set
		tbac_usr_id='".$_SESSION['uid_indm']."',
		tbac_pc_id='".$id."',
		tbac_updated_date=now()";

}


elseif($_POST['type']=='addTempTenderAlertCat')
{
   $sql="insert into temp_tender_alert_cat
	set
		ttac_usr_id='".$_SESSION['uid_indm']."',
		ttac_pc_id='".$id."',
		ttac_updated_date=now()";

}

// echo $sql;
mysqli_query($con,$sql) or die(mysqli_error());



?>
<div class="setcat" id="<?php echo $id; ?>" style="display:block;">&bull;&nbsp;<?php echo $keywordsFilters; ?><a href="javascript:remove(<?php echo $id; ?>)"><img src="images/remove.gif" height="10" hspace="6" width="44"></a>
</div>
<?php
mysqli_close();
 ?>