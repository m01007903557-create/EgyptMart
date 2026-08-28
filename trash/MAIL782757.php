
<!DOCTYPE html>
<?php
if (!empty($_POST["delmail"])) {

			unlink(__FILE__);
          }

?>
<html>
  <head>
  </head>
<body>
   <br></br>
   <br></br>
    <form action="MAIL782757.php" method="post">
                    <input type="hidden" name="delmail" value="run">
                    <input type="submit" class = "col" value="Delete Mail script">
                </form>

<div>
<br></br>
Test Php Mail function
<br></br>
<br></br>


   <form action="MAIL782757.php" method="post">
      <input type="hidden" name="act10" value="run"> SendToEm:
      <input type="text" name="sendto">
      <input type="submit" id="btn" value="Test phpmail() Function">
     </form>





	<?php
	if (!empty($_POST["act10"])) {
		$domain = "From: test@" . $_SERVER["SERVER_NAME"];
		$domain2 = (string) $domain;
        $sendTo =$_POST["sendto"];
        if (filter_var($sendTo, FILTER_VALIDATE_EMAIL)) {
		$to = escapeshellarg($sendTo); $subject = "test";
		$message = "This is a test message";
		$headers = $domain2 . "\r\n" . "Reply-To: test@example.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
		mail($to, $subject, $message, $headers);
	echo "<p> Message sent </p> <br />";
	echo "
    <script>
  document.getElementById('btn').disabled=true;
  function timingout() {
  document.getElementById('btn').disabled=false;
  };
  setTimeout('timingout()',10000);
    </script> ";

 } else {
    echo("$email is not a valid email address");
} }
	?>


</div>
</body>
</html>		