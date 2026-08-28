<?php
//ob_start();
//session_start(); 
include "../common.php";
$reseller_id=$_SESSION['reseller_id'];

$total=substr($_GET['total_gateway'],2);

//
$totrecord=explode("||",$total);

foreach($totrecord as $val)
{
$val5=explode(":",$val);
$id=$val5[0];
$cardno=$val5[1];

$sqlchk="select * from reseller_payment_gateway where resl_pg_resellerid='".$reseller_id."' and resl_pg_gateway='".$id."'";
$reschk=mysqli_query($con, $sqlchk);
$rowchk=mysqli_fetch_object($reschk);
if(mysqli_num_rows($reschk)<=0)
{    
$sql="insert into reseller_payment_gateway set resl_pg_resellerid='".$reseller_id."', resl_pg_cardno='".$cardno."', resl_pg_gateway='".$id."' ";
mysqli_query($con, $sql);
}
 else {
$sql="update reseller_payment_gateway set resl_pg_resellerid='".$reseller_id."', resl_pg_cardno='".$cardno."', resl_pg_gateway='".$id."' where resl_pg_id='".$rowchk->resl_pg_id."'";
mysqli_query($con, $sql);  
}
}
?>