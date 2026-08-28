<?php
include 'common.php';

$currencysql=mysql_query("select * from country where cn_status='1'");
echo "<pre>";
while($currencyrow=mysql_fetch_object($currencysql))
{
	print_r($currencyrow);
	echo $currencyrow->cn_id."-".$currencyrow->cn_currency;;
	echo "<hr>";
	//
}
echo "</pre>";
?>