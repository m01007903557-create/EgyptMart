<?php
include_once('Mail.php');
include_once('Mail_Mime/mime.php');

$message = new Mail_mime();
 
$message->setTXTBody($text);
 
$message->addAttachment($path_of_uploaded_file);
 
$body = $message->get();
 
$extraheaders = array("From"=>$from, "Subject"=>$subject,"Reply-To"=>$visitor_email);
 
$headers = $message->headers($extraheaders);
 
$mail = Mail::factory("mail");
 
$mail->send($to, $headers, $body);


?>