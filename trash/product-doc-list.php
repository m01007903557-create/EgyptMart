<?php
include "common.php";
$uid=$_SESSION['uid_indm'];
$pid=$_GET['pid']; 
$targetFolder = 'upload/productdoc'; 

$sql="SELECT * FROM products WHERE pd_id ='".$pid."' "; 
$recObj=mysqli_query($con, $sql) or die(mysql_error());
$timage_num=mysqli_num_rows($recObj);
$rowk=mysqli_fetch_object($recObj);
if($rowk->pd_pdf_attach!=""){ 
?>
<span id="old_doc_form0" style="width:100px;">
<p id="filename" class="margin" style="font-family: arial; font-size: 12px;"><b><img src="images/att.gif" width="16" height="17">
<a href="product-doc-download.php?pid=<?php echo $pid; ?>" target="_new"><?php echo substr($rowk->pd_pdf_attach,0,27);?>.pdf</a></b>&nbsp;&nbsp;&nbsp;
<a onclick="DelTempdoc(<?php echo $rowk->pd_id; ?>)" style="text-decoration:none;text-align: center; cursor:pointer;">
<img src="images/remove.gif" align="absmiddle" width="44" height="10"></a></p>
</span>
<?php } ?>