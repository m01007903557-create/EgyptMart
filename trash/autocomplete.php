<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

header("Cache-Control: post-check=0, pre-check=0", false);

header("Pragma: no-cache");

include "common.php";

if(!session_id()){

  session_start();

}

$type="";

$keywords= mysqli_real_escape_string($con,$_GET['q']);

$type=trim($_GET['type']);

//$my_data=mysql_real_escape_string($q);

$sql_prd="";

$sql_br="";

$sql_sup="";



    $reffer = ($_SERVER['HTTP_REFERER']);

    $refferr = explode("-alert",$reffer);

    $refferr1 = explode("product-sel-cat",$reffer);

    $refferr12 = explode("post-",$reffer);
	$refferr123=explode("myproduct-",$reffer);






    ///this condition was used if there is post attr filter thing



if(count($refferr)>1 || count($refferr1)>1 || count($refferr12)>1 || count($refferr123)>1){

  /*      $sql = "SELECT p3.`pc_name` as `Grandparentname`,p3.`pc_id` as `GrandparentId`, p2.* , p1.pd_title

FROM products  p1

LEFT JOIN product_category_arabyos p2 on p1.`pd_subcat_id` = p2.`pc_id`

LEFT JOIN product_category_arabyos p3 on p2.`pc_parent_id` = p3.`pc_id` where p1.pd_title ='".$row_prd->pd_title."' ";*/

$sql = "select s.pc_id as pd_subcat_id, s.pc_name as pd_title, s.pc_sort_name, c.pc_name as childname,c.pc_id as childid, m.pc_id as GrandparentId, m.pc_name as Grandparentname from product_category_arabyos s, product_category_arabyos c, product_category_arabyos m where s.pc_parent_id=c.pc_id and c.pc_parent_id=m.pc_id and m.pc_parent_id='0' and m.pc_status='1' and c.pc_status='1' and s.pc_status='1' and (s.pc_name LIKE '%".$keywords."%') order by s.pc_id desc";

 //   echo $sql;

 $res=mysqli_query($con,$sql) or die(mysqli_error());

while($row=mysqli_fetch_object($res)){

 echo $row->Grandparentname.">>".$row->childname.">>".$row->pd_title."\n";

$_SESSION[$row->Grandparentname] = $row->GrandparentId;

//$dataarray[$row->Grandparentname] = $row->GrandparentId;

$_SESSION[$row->childname] = $row->childid;

//$dataarray[$row->childname] = $row->childid;

	  $dataarray[$row->pd_title] = $row->pd_subcat_id;

}

        $_SESSION['searchedproducts'] = $dataarray;

exit;

mysqli_close();

}

     $getD = mysqli_real_escape_string($con,$_POST['keywordsAjax']);

	 $sql_prd="select  pd_title ,pd_subcat_id from products where (pd_title LIKE '".$getD."%') and pd_status='1' group by  pd_title order by pd_title";

	$result_prd = mysqli_query($con,$sql_prd);







	if(mysqli_num_rows($result_prd)>0 && $_POST['Products']=='Products')

	{
	echo "<ul>";
		while($row_prd=mysqli_fetch_object($result_prd))

		{



				$query="SELECT * FROM `product_category_arabyos` WHERE pc_id='".$row_prd->pd_subcat_id."' ORDER BY `pc_id` DESC";
	
				$querySub = mysqli_query($con,$query);
				$ResultSub = mysqli_fetch_object($querySub);
				
				  $queryParent ="SELECT * FROM `product_category_arabyos` WHERE `pc_id` = ".$ResultSub->pc_parent_id." ORDER BY `pc_id` DESC";
				$queryParents = mysqli_query($con,$queryParent);
				$ResultParent = mysqli_fetch_object($queryParents);

				 $queryMain ="SELECT * FROM `product_category_arabyos` WHERE `pc_id` = ".$ResultParent->pc_parent_id." ORDER BY `pc_id` DESC";
				$queryMain = mysqli_query($con,$queryMain);
				$ResultMain = mysqli_fetch_object($con,$queryMain);

		if($row_prd->pd_title!=""){
			
		
						echo "<li>";
						echo "<a id='getSearchText' href='javascript:void(0);' class='".$row_prd->pd_title."' style='font-size:13px;'>";
									echo $ResultMain->pc_name." >> ".$ResultParent->pc_name." >> "."<p style='float: right;' class='txt-red'>".$ResultSub->pc_name."</p>";
						echo "</a>";
						echo "<li>";
		
		}
 // echo ucfirst($row_prd->pd_title)."\n";

		 /// print_r($row_prd);

		  $dataarray[$row_prd->pd_title] = $row_prd->pd_subcat_id;

		  //

		}
	echo "</ul>";	
        $_SESSION['searchedproducts'] = $dataarray;

	}



?>