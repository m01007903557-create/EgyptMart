<?php
   session_start();
   $_SESSION["popup"] = $_POST["id"];
   // Add the rest of the post-variables to session-variables in the same manner
?>