<?php include "includes/header.php"; 


$sql_own="select * from user,business_profile where usr_id='".$_SESSION['uid_indm']."' and bnsprof_uid=usr_id limit 1";
$res_own=mysql_query($sql_own);
$row_own=mysql_fetch_object($res_own);

$image1=$_SESSION['image1'];
$image2=$_SESSION['image2'];
$image3=$_SESSION['image3'];
$image4=$_SESSION['image4'];
$image5=$_SESSION['image5'];
$image6=$_SESSION['image6'];
$image7=$_SESSION['image7'];
$image8=$_SESSION['image8'];
$image9=$_SESSION['image9'];
$image10=$_SESSION['image10'];
$image11=$_SESSION['image11'];
$image12=$_SESSION['image12'];
$image13=$_SESSION['image13'];
$image14=$_SESSION['image14'];
$image15=$_SESSION['image15'];
$image16=$_SESSION['image16'];
$image17=$_SESSION['image17'];
$image18=$_SESSION['image18'];
$image19=$_SESSION['image19'];
$image20=$_SESSION['image20'];

$id1=$_SESSION['id1'];
$id2=$_SESSION['id2'];
$id3=$_SESSION['id3'];
$id4=$_SESSION['id4'];
$id5=$_SESSION['id5'];
$id6=$_SESSION['id6'];
$id7=$_SESSION['id7'];
$id8=$_SESSION['id8'];
$id9=$_SESSION['id9'];
$id10=$_SESSION['id10'];
$id11=$_SESSION['id11'];
$id12=$_SESSION['id12'];
$id13=$_SESSION['id13'];
$id14=$_SESSION['id14'];
$id15=$_SESSION['id15'];
$id16=$_SESSION['id16'];
$id17=$_SESSION['id17'];
$id18=$_SESSION['id18'];
$id19=$_SESSION['id19'];
$id20=$_SESSION['id20'];

if($id1=='')
{
    $id1=0;
}
if ($id2=='') {
    $id2=0;
}
if ($id3=='') {
    $id3=0;
}
if ($id4=='') {
    $id4=0;
}
if($id5=='') {
    $id5=0;
}
if ($id6=='') {
    $id6=0;
}
if ($id7=='') {
    $id7=0;
}
if ($id8=='') {
    $id8=0;
}
if ($id9==''){
    $id9=0;
}
if ($id10==''){
    $id10=0;
}
if($id11=='')
{
    $id11=0;
}
if ($id12=='') {
    $id12=0;
}
if ($id13=='') {
    $id13=0;
}
if ($id14=='') {
    $id14=0;
}
if($id15=='') {
    $id15=0;
}
if ($id16=='') {
    $id16=0;
}
if ($id17=='') {
    $id17=0;
}
if ($id18=='') {
    $id18=0;
}
if ($id19==''){
    $id19=0;
}
if ($id20==''){
    $id20=0;
}





$sel_product=array();
$sel_pro="select * from products where pd_id IN ($id1,$id2,$id3,$id4,$id5,$id6,$id7,$id8,$id9,$id10,$id11,$id12,$id13,$id14,$id15,$id16,$id17,$id18,$id19,$id20)";

$s_prod=mysql_query($sel_pro);
while($select_product=mysql_fetch_object($s_prod))
{
    $sel_product[]=$select_product;
    $sel_product_image[]=$select_product->pd_image;
}





if (isset($_REQUEST['email']))  {
  
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";




  
  //Email information
 
  $email = $_REQUEST['email'];
  
  $subject = 'EnquiryRequest';
  

  
if($image1!='')
{
$comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image1."'>";
}
if($image2!='')
{
$comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image2."'>";
}
if($image3!='')
{
    $comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image3."'>";
}
if($image4!='')
{
    $comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image4."'>";
}
if($image5!='')
{
    $comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image5."'>";
}
if($image6!='')
{
    $comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image6."'>";
}
if($image7!='')
{
    $comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image7."'>";
}
if($image8!='')
{
    $comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image8."'>";
}
if($image9!='')
{
    $comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image9."'>";
}
if($image10!='')
{
    $comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image10."'>";
}
if($image11!='')
{
$comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image11."'>";
}
if($image12!='')
{
$comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image12."'>";
}
if($image13!='')
{
    $comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image13."'>";
}
if($image14!='')
{
    $comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image14."'>";
}
if($image15!='')
{
    $comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image15."'>";
}
if($image16!='')
{
    $comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image16."'>";
}
if($image17!='')
{
    $comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image17."'>";
}
if($image18!='')
{
    $comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image18."'>";
}
if($image19!='')
{
    $comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image19."'>";
}
if($image20!='')
{
    $comment .="<img src='http://www.greensolz.com/hosted_sites/emh/EgyptMART/upload/myproduct/".$image20."'>";
}

$comment .= $_REQUEST['comment'];   





		



  

  
  //send email
  
  mail($email, "$subject", $comment, $headers);
  
  
  //Email response
  session_unset();
  header("Location: http://www.greensolz.com/hosted_sites/emh/EgyptMART/$row->bnsprof_comp_url/products.php?c=$c");
  
  }
  
  //if "email" variable is not filled out, display the form
  else  {


?>
<div id="body">
	<ul class="cb">
	
	<li id="wideColumn">
	<div id="h1"><h1 style="text-transform: capitalize;">Wholesale Contact</h1></div>
	
	<div id="breadcrumb">
	<ul>
	<li><a href="http://greensolz.com/hosted_sites/emh/EgyptMART/<?php echo $row->bnsprof_comp_url;?>/index.php?c=<?php echo $c; ?>" id="myDiv">Home</a><b>»</b></li>
	<li>Wholesale Contact</li>
	</ul>
	</div>
	<br><br>
        <ul class="contact-image">
            <?php
		foreach($sel_product as $selpro)
		{?>
                	<li style="height:50px; width:33%;"><img style="width:170px; margin-left: 26px;" src="../upload/myproduct/<?php echo $selpro->pd_image; ?>"><p style="text-align: center;"><?php echo $selpro->pd_title; ?></br><b>QTY:</b><input type="text" name="quantity" value="<?php echo $selpro->pd_min_order_qty;?>" style="width: 22px; display: -webkit-inline-box;"/></br><b>Unit:</b><?php echo get_measurement_unit($selpro->pd_unit); ?></p></li> 
                       
                <?php } ?>						
							
        </ul>
   
        
        <style>
            .contact-image li{
                display: inline-block;
                
            }
        </style>
           
            

 <form method="post">
     
    <input type='hidden' name='email' value='<?php echo $row_own->email;?>'>
  <label style="font-size:large;"><b>Message:</b></label><br />
  <div style="width: 555px; height: auto;">
  <textarea name="comment" style="width: 100%; height: 150px; overflow: auto; padding: 10px; box-sizing: border-box;"></textarea>
  
  </div>
  
  <input class="dib b darkbg2 gbibt white bdr darkbdr2 xlarge p7px15px br5px ml5px" id='btn-sub' value="Wholesale Contact" style="margin-left: 195px; height: 21px; width: 141px; padding-left: 4px !important; padding-top: 0px !important; font-size: larger;" type="submit">
  
  </form>	

</li>	

		<?php include "includes/right.php"; ?>
</ul>
</div><BR><BR>
	<?php include "includes/footer.php"; ?>
</body></html>
<style>
    #btn-sub{
        background:#017601;*zoom:1;
	filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#017601', EndColorStr='#059d05');
	background:-webkit-gradient(linear, 0 0, 0 bottom, from(#017601), to(#059d05));
	background:-webkit-linear-gradient(#017601, #059d05);
	background:   -moz-linear-gradient(#017601, #059d05);
	background:    -ms-linear-gradient(#017601, #059d05);
	background:     -o-linear-gradient(#017601, #059d05);
	background:        linear-gradient(#017601, #059d05);
    }
</style>

<?php
  }

  
 
?>
 