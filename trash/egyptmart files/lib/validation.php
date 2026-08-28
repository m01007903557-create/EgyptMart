<?php 

class validate
{	
/****************Name Validation********************/
	public static function is_name($str)
	{
		$pattern="/^([A-Za-z_\ ]*)$/";
 		return	preg_match($pattern, $str);
	}
/***************Email Validation*****************/
	public static function is_email($str)
	{
		$pattern="/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/";
 		return	preg_match($pattern, $str);
	}
/***************Phone No validation***************************/
	public static function is_phone($str)
	{
		$pattern="/^([0-9_\ \.\+]{10,18})$/";
 		return	preg_match($pattern, $str);
	}
/***************Website validation***************************/
	public static function is_website($str)
	{
		$pattern='/^(ht|f)tps?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/';
 		return	preg_match($pattern, $str);
	}
/**************User Name Validation************************/
	public static function is_username($str)
	{
		$pattern="/^([A-Za-z0-9_\]*)$/";
 		return	preg_match($pattern, $str);	
	}
/**************Website Link Validation************************/
	public static function is_weblink($str)
	{
		$pattern='/^(ht|f)tps?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/';
 		return	preg_match($pattern, $str);	
	}
/**************Website Link Validation************************/
	
	public static function send_mail($mailto, $from_mail, $from_name, $replyto, $cc, $subject, $message, $filename =  NULL, $path = NULL)
	{		
		//$message .= nl2br($message);

		$uid = md5(uniqid(time()));
		$header = "From: ".$from_name." <".$from_mail.">\r\n";
		$header .= "Reply-To: ".$replyto."\r\n";
		$header .= "CC: ".$cc."\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
		$header .= "--".$uid."\r\n";
		$header .= "Content-Type: text/html; charset=utf-8\r\n";
		$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		
		//$message .= "Content-Type: application/msword; name=\"my attachment\"\n";
		//$message .= "Content-Transfer-Encoding: base64\n";
		//$header .= "Content-Disposition: attachment; filename=\"$filename\"\n\n"; 
		
		//$attachment = chunk_split(base64_encode(file_get_contents("geekology.zip")));
//		$header .= "Content-Disposition: attachment; ";
		$header .= $message."\r\n\r\n";
		$header .= "--".$uid."\r\n";
		$header .= "--".$uid."--";
		
		
		/*$headers  .= "Content-Type:".$strresume_type." ";
       $headers  .= "name=\"".$strresume_name."\"r\n";
       $headers  .= "Content-Transfer-Encoding: base64\r\n";
       $headers  .= "Content-Disposition: attachment; ";
       $headers  .= "filename=\"".$strresume_name."\"\r\n\n";
       $headers  .= "".$file."\r\n";
       $headers  .= "--".$num."--"; */
		
		if (mail($mailto, $subject, "", $header))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public static function formatInput($ip){
		return trim(addslashes(htmlentities($ip)));
	}
	public static function formatOutput($op){
		return stripslashes(html_entity_decode($op));
	}
} 

?>
