<?php
if(empty($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
}

// Strip off query string so dirname() doesn't get confused
$url = preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);
if($url=''){
$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.ltrim(dirname($url), '/').'';
}
if($url==''){
$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.ltrim(dirname($url), '').'';
}
$sql_pg="select * from payment_gateway where id='1'";
$res_pg=mysqli_query($con, $sql_pg);
$row_pg=mysqli_fetch_object($res_pg);
?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
<input type="hidden" name="cmd" value=_xclick>
<input type="hidden" name="business" value="<?php echo $row_pg->pg_id; ?>">
<input type="hidden" name="item_name" value="Subscription">
<input type="hidden" name="amount" value="<?php echo $tot; ?>">
<input type="hidden" name="no_note" value=1>
<input type="hidden" name="currency_code" value="<?php echo getCurrencyCode();?>">
<input type="hidden" name="rm" value=2>
<input type="hidden" name="custom" value="<?php echo $_SESSION['uid_indm'];?>||<?php echo $plan_id; ?>">
<input type="hidden" name="return" value="<?php echo "http://www.www.arabyos.com/paymentgateway/paypal_annual_subscription.php"; ?>">
<input type="hidden" name="cancel_return" value="<?php echo $url;?>">
<input type="image" src="images/payment-gateway/<?php echo $row_pg->pg_logo; ?>">
</form>