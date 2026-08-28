<?php
include '../common.php';
$cid = $_POST['cid'];
if($cid!="")
{
$subcatsqlk="select * from product_category where pc_parent_id  = '".$cid."' and pc_status = '1'";
$subcatresk=mysqli_query($con, $subcatsqlk);
if(mysqli_num_rows($subcatresk)>1) 
{
?>
			<div class="fs1 f1">
            <p><span style="line-height: 12px;">*</span>Sub Category</p>
            <select name="pd_subcat_id" id="pd_subcat_id" class="a_f pf1" style="width:280px;">
            <option value="">Select</option>
            <?php
            while($subcatrwk=mysqli_fetch_array( $subcatresk)){
            ?>
            <option value="<?php echo $subcatrwk['pc_id']?>" ><?php echo $subcatrwk['pc_name']?></option>
            <?php } ?>
            </select><br>
            <span>Please choose the Product subcategory here.</span>
            </div>
<?php } } ?>






