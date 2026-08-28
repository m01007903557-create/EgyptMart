<?php 
session_start();
require_once 'common.php';

  $data = $_GET['products'];
  $view_product ='select * from products where pd_id IN ('.$data.')';
  $run_query= mysql_query($view_product);

  while( $row=mysql_fetch_array($run_query, MYSQL_ASSOC)){
    
    //var_dump($row).'-----------<br>';
    echo 'product id :-  '.$row['pd_id'].'<br />';
  }
  
  

// $data = explode(",", $data);
// echo 'Product  1  '.$data[0].'<br>';
// echo 'Product  2  '.$data[1].'<br>';



?>