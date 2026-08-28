<?php
	//include "common.php";
	
	$referer=basename($_SERVER['PHP_SELF']);
	$lang = $_GET['lang'];
	$sql="SELECT * FROM seo WHERE page_name='".$referer."'";
	$result=mysqli_query($con, $sql) or die(mysql_error());
	$row=mysqli_fetch_array( $result);
	
	#################### Getting values from database to show in the tpl ############################
	$name=$row['page_name'];	
	
	if($referer == 'portfolio_details.php')
	{
		$sqlTitle="select * from ".TB_PORTFOLIO." where pt_status=1 and pt_title_url='".addslashes($_GET['title'])."'";
		$resTitle=mysqli_query($con, $sqlTitle);
		$rowTitle=mysqli_fetch_array( $resTitle);
		
		$titlename=explode("-", stripslashes($rowTitle['pt_title_url']));	
		if(count($titlename) > 1)	
		{
			for($i=0; $i<count($titlename); $i++)
			{
				$titlename1=$titlename1.' '.ucfirst($titlename[$i]);
			}
			$title=$row['title'].' '.$titlename1;
		}
		else
		{
			$title=$row['title'].' '.ucfirst($titlename[0]);
		}
	}
	else
	{	
		$title=$row['title'];	
	}
	$keyword=$row['keyword'];
	$description=$row['description'];	
 	$language=$row['language'];		
	$author=$row['author'];	
	$copyright=$row['copyright'];
	$contact=$row['contact'];
	$robots=$row['robots'];
	$googlebot=$row['googlebot'];
	$last_modified=$row['last_modified'];
	$expires=$row['expires'];
	$updated_date=$row['updated_date']; 
	//$copyright=$row['copyright']; 
	//$contact=$row['contact'];	 	 	 	 	 	 	 	 	 	 	
	
	############################# Creating meta tags ##############################################
	$seo="<title>"."$title"."</title>"."\n";
	$seo.="<meta name=\"description\" content=\"$description\" />"."\n";
	$seo.="<meta name=\"keywords\" content=\"$keyword\" />"."\n";
	$seo.="<meta name=\"robots\" content=\"$robots\" />"."\n";
	$seo.="<meta name=\"googlebot\" content=\"$googlebot\" />"."\n";	
	$seo.="<meta name=\"language\" content=\"$language\" />"."\n";
	/*$seo.="<meta name=\"distribution\" content=\"$distribution\" />"."\n";	
	$seo.="<meta name=\"rating\" content=\"$rating\" />"."\n";
	$seo.="<meta name=\"revisit-after\" content=\"$revisit_after\" />"."\n";*/
	$seo.="<meta name=\"author\" content=\"$author\" />"."\n";
	$seo.="<meta name=\"copyright\" content=\"$copyright\" />"."\n";	
	$seo.="<meta name=\"contact\" content=\"$contact\" />"."\n";	
	$seo.="<meta name=\"expires\" content=\"$expires\" />"."\n";
	$seo.="<meta name=\"last_modified\" content=\"$last_modified\" />"."\n";
	echo $seo;
?>