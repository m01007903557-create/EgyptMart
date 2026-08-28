<?php
// تعريف المتغير $file لمنع الخطأ (قيمة افتراضية)
if (!isset($file)) {
    $file = basename($_SERVER['PHP_SELF'], '.php');
}
?>
<div class="left-side1">
    	<div class="left-navigation">
         	<ul>
				<li><a href="my-dashboard.php" <?php if($file=="my-dashboard"){?>class="current"<?php }?>>Home</a></li>
				<li><a href="about_us.php" <?php if($file=="about_us"){?>class="current"<?php }?>>About Us</a></li>
            	<li><a href="contact_us.php" <?php if($file=="contact_us"){?>class="current"<?php }?>>Contact us</a></li>
				<li><a href="help.php" <?php if($file=="help"){?>class="current"<?php }?>>Help Center</a></li>
				<li><a href="terms.php" <?php if($file=="terms"){?>class="current"<?php }?>>Terms & Conditions</a></li>
				<li><a href="privacy.php" <?php if($file=="privacy"){?>class="current"<?php }?>>Privacy & Policy</a></li>
            </ul>
         </div>
        
         
        <div class="clr"></div>
        </div>