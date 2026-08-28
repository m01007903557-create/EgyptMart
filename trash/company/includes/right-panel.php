<?php
function getParentId($id)
{
	$sql="select pc_parent_id from product_category where pc_id='".$id."'";
	$res=mysql_query($sql);
	$row=mysql_fetch_object($res);
	
	return $row->pc_parent_id;
}
?>
<script type="text/javascript">
function show_list(v)
{
	if($("#pl"+v).hasClass("pr1"))
	{
		$("#pl"+v).removeClass("pr1").addClass("prd");
		$("#link"+v).removeClass("on").addClass("off");
	}
	else
	{
		$("#pl"+v).removeClass("prd").addClass("pr1");
		$("#link"+v).removeClass("off").addClass("on");
	}
}
</script>
<div class="rt1"><p class="sp r2 r"></p><p class="sp r1 fl"></p></div>
<div class="rt2" ID="ms1">
<div class="b c15 fn4 rpd3 sp r8"><a href="products.php?c=<?php echo rand(1000,9999).md5($row->bnsprof_id); ?>" target="_top">Products & Services</a></div>

<?php
if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm']==$row->usr_id){
$sql_pc="select * from product_category where pc_id in(select distinct pc_parent_id from product_category where pc_id in(select distinct pd_subcat_id from products, measurement_unit, country where mu_id=pd_unit and pd_currency=cn_id and pd_uid='".$row->usr_id."' and pd_status='1'))";
}
else
{
$sql_pc="select * from product_category where pc_id in(select distinct pc_parent_id from product_category where pc_id in(select distinct pd_subcat_id from products, measurement_unit, country where mu_id=pd_unit and pd_currency=cn_id and pd_uid='".$row->usr_id."' ".$sql_pd_ck." and pd_status='1'))";	
}
$res_pc=mysql_query($sql_pc);
$i=1;
while($row_pc=mysql_fetch_object($res_pc)){
?>

<p class="cl"></p>
<div class="sp r5 b">
	<div class="nav_g b">
		<DIV CLASS="<?php if(isset($_GET['sc']) && getParentId(substr($_GET['sc'],5))==$row_pc->pc_id){ ?>pr1<?php }else if(!(isset($_GET['sc'])) && $i==1){ ?>pr1<?php }else{ ?>prd<?php  } ?> lh2 c6 a b p9" ID="pl<?php echo $i; ?>">
        <p onClick="show_list('<?php echo $i; ?>')" class="hi1 wi1 fl"></p>
        <p class="fl wn6 c4 r7 b"><A target="_top" onClick="show_list('<?php echo $i; ?>')" style="cursor:pointer;"><?php echo $row_pc->pc_name; ?></A></P>
        <p class="cl"></p>
        </DIV>
	</div>
</DIV>

<DIV ID="link<?php echo $i; ?>" class="<?php if(isset($_GET['sc']) && getParentId(substr($_GET['sc'],5))==$row_pc->pc_id){ ?>on<?php }else if(!(isset($_GET['sc'])) && $i==1){ ?>on<?php }else{ ?>off<?php } ?>">
<?php
if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm']==$row->usr_id){
	$sql_pc_sub="select * from product_category where pc_id in(select distinct pd_subcat_id from products, measurement_unit, country where mu_id=pd_unit and pd_currency=cn_id  and pd_uid='".$row->usr_id."' and pd_status='1' order by pd_subcat_id) and pc_parent_id='".$row_pc->pc_id."'";
}
else
{
	$sql_pc_sub="select * from product_category where pc_id in(select distinct pd_subcat_id from products, measurement_unit, country where mu_id=pd_unit and pd_currency=cn_id  and pd_uid='".$row->usr_id."' ".$sql_pd_ck." and pd_status='1' order by pd_subcat_id) and pc_parent_id='".$row_pc->pc_id."'";
}
	
	$res_pc_sub=mysql_query($sql_pc_sub);
	while($row_pc_sub=mysql_fetch_object($res_pc_sub))
	{
?>
<ul><li><A HREF="products.php?c=<?php echo rand(1000,9999).md5($row->bnsprof_id); ?>&sc=<?php echo rand(10000,99999).$row_pc_sub->pc_id; ?>" ><?php echo $row_pc_sub->pc_name; ?></A></li></ul>
<?php	}	?>

</div>
<?php 
$i++;
} ?>
<!---->     
<p class="cl"></p>
</div>

<div class="rt3 sp"><p class="sp r3 fl"></p><p class="sp r4 r"></p></div>