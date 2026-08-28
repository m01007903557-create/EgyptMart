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
/**************User Name Validation************************/
	public static function is_username($str)
	{
		$pattern="/^([A-Za-z0-9_\]*)$/";
 		return	preg_match($pattern, $str);	
	}
	
	public static function send_mail($mailto, $from_mail, $from_name, $replyto, $cc, $subject, $message, $filename =  NULL, $path = NULL)
	{		
		/*$message = '
			<html>
				<head>
				  <title>'.$subject.'</title>
				</head>
				<table width="500px" border="0" cellspacing="0" cellpadding="1" bgcolor="#fff6e7">
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				  <tr>
					<td colspan="2" align ="center"><img src="http://64.191.66.18/cupcake/images/logo.gif"  /></td>					
				  </tr>
				  <tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				   <tr  bgcolor="#CCFFFF">
					<td colspan="2" align="center" height="30px"><b>'.$subject.' </b></td>
					
				  </tr>
				  <tr>
					<th colspan="2" scope="col">&nbsp;</th>
				   </tr>
				   
				  <tr bgcolor="#ffffff">
					
					<td width="496" valign = "top" colspan="2">'.$message.'</td>
				  </tr>
				   <tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				   <tr bgcolor="#ffffff">
					
					<td colspan="2" valign = "top"><b>Email :</b>'.$from_mail.'</td>
				  </tr>
				   <tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				  <tr>
					<td>&nbsp;&nbsp;&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				 
				</table>
				</html>
			';
		*/
		
		$uid = md5(uniqid(time()));
		$header = "From: ".$from_name." <".$from_mail.">\r\n";
		$header .= "Reply-To: ".$replyto."\r\n";
		$header .= "CC: ".$cc."\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
		$header .= "--".$uid."\r\n";
		$header .= "Content-Type: text/html; charset=utf-8\r\n";
		$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$header .= $message."\r\n\r\n";
		$header .= "--".$uid."\r\n";
		$header .= "--".$uid."--";
		if (mail($mailto, $subject, "", $header))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public static function mail_attachment($mailto, $from_mail, $from_name, $replyto, $subject, $message,$filename, $path) {
	
	$file = $path.$filename;
    $file_size = filesize($file);
    $handle = fopen($file, "r");
    $content = fread($handle, $file_size);
    fclose($handle);
    $content = chunk_split(base64_encode($content));
    $uid = md5(uniqid(time()));
    $name = basename($file);
	
	$message = '
			<html>
				<head>
				  <title>'.$subject.'</title>
				</head>
				<table width="500px" border="0" cellspacing="0" cellpadding="1" bgcolor="#fff6e7">
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				  <tr>
					<td colspan="2" align ="center"><img src="http://hashlive.com/images/logo.gif"  /></td>
					
				  </tr>
				  <tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				   <tr  bgcolor="#CCFFFF">
					<td colspan="2" align="center" height="30px"><b>New Resume Submission </b></td>
					
				  </tr>
				  <tr>
					<th colspan="2" scope="col">&nbsp;</th>
				   </tr>
				   
				  <tr bgcolor="#ffffff">
					
					<td width="496" colspan="2" valign = "top">'.$message.'</td>
				  </tr>
				   <tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				   <tr bgcolor="#ffffff">
					
					<td colspan="2" valign = "top"><b>Email :</b>'.$from_mail.'</td>
				  </tr>
				   <tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				  <tr>
					<td>&nbsp;&nbsp;&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				 
				</table>
				</html>
			';
	
    $header = "From: ".$from_name." <".$from_mail.">\r\n";
    $header .= "Reply-To: ".$replyto."\r\n";
    $header .= "MIME-Version: 1.0\r\n";
    $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
    $header .= "This is a multi-part message in MIME format.\r\n";
    $header .= "--".$uid."\r\n";
    $header .= "Content-type:text/html; charset=iso-8859-1\r\n";
    $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $header .= $message."\r\n\r\n";
    $header .= "--".$uid."\r\n";
    $header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
    $header .= "Content-Transfer-Encoding: base64\r\n";
    $header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
    $header .= $content."\r\n\r\n";
    $header .= "--".$uid."--";
    if (mail($mailto, $subject, "", $header)) {
		unlink($file);
        return true; // or use booleans here
    } else {
        return false;
    }
	
	}		
} 

?>
