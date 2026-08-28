<?php
ob_start();
session_start(); 
$uid=$_SESSION['uid_indm'];
include "../common.php";
$pid=$_GET['id'];

$pdsql=mysqli_query($con, "select * from products where pd_id='".$pid."' and pd_status ='1'");
$pdrow=mysqli_fetch_object($pdsql);
?>

		<div class="c3 topbox">
        <?php if($pdrow->pd_pushed_top=='1'){ ?>
        <p id="p2tp" class="pued f1" style="">Pushed to Top</p>
        <?php } else { ?>
      <a onclick="pushedtotop(<?php echo $pid;?>);" class="b-img pht f2" title="Push this product to top and get better visibilty on <?php echo get_page_settings(4);?> platform." id="p2t_46536582" style="cursor:pointer;"></a>
        <?php } ?>
        <br>
		<p id="p2tw_46536582" class="pw f2" style="display: none;">Please Wait...</p>
		<p id="p2tp_46536582" class="pued f1" style="display: none;">Pushed to Top</p>
		<input class="mark cpr" id="hotpd" name="hotpd" type="checkbox" onClick="markhot(<?php echo $pid;?>)" <?php if($pdrow->pd_hot=='1'){ ?> checked ="checked" <?php } ?>>
        <?php if($pdrow->pd_hot=='1'){ ?>
        <label for="hot_47089835" class="hp htgr cpr" id="hotmsg_47089835">HOT Product</label>
        <?php } else { ?>
        <label for="hot_46536582" class="hp cpr" id="hotmsg_46536582">Mark as HOT</label>        
        <?php }  ?>
        </div>