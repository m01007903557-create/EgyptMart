<?php
ob_start();

$path=$_SERVER['SCRIPT_NAME'];
$pos=strrpos($path,'/');
$file=substr($path,($pos+1));
$file = strstr($file, '.', true);
?>
<div id="sidebar-left-container">
<div id="sidebar-left"> 
<?php if($_SESSION['username']=='admin'){	?>
<div class="portlet" id="top-contacts">
<div class="portlet-decoration">
<div class="portlet-title">Manage Employee</div>
</div>
<div class="portlet-content">
	<ul>
        <li><a <?php if($file=="employee-personal-details"){ ?><?php } ?> href="employee-personal-details.php?aid=<?php echo rand(1000,9999).$empid; ?>">Personal Details</a></li>	
        <li><a <?php if($file=="employee-contact-details"){ ?><?php } ?> href="employee-contact-details.php?aid=<?php echo rand(1000,9999).$empid; ?>">Contact Details</a></li>
        <li><a <?php if($file=="employee-emergency-contacts" || $file=="employee-emergency-contact-add" || $file=="employee-emergency-contact-edit"){ ?><?php } ?> href="employee-emergency-contacts.php?aid=<?php echo rand(1000,9999).$empid; ?>">Emergency Contacts</a></li>
        <li><a <?php if($file=="employee-dependents" || $file=="employee-dependent-add" || $file=="employee-dependent-edit"){ ?><?php } ?> href="employee-dependents.php?aid=<?php echo rand(1000,9999).$empid; ?>">Dependents</a></li>
        <li><a <?php if($file=="employee-immegration" || $file=="employee-immegration-add" || $file=="employee-immegration-edit"){ ?><?php } ?> href="employee-immegration.php?aid=<?php echo rand(1000,9999).$empid; ?>">Immigration</a></li>
      
        <li><a <?php if($file=="employee-job-details"){ ?><?php } ?> href="employee-job-details.php?aid=<?php echo rand(1000,9999).$empid; ?>">Job</a></li>
        <li><a <?php if($file=="employee-salary-details"){ ?><?php } ?> href="employee-salary-details.php?aid=<?php echo rand(1000,9999).$empid; ?>">Salary</a></li>
        <li><a <?php if($file=="employee-report_to" || $file=="employee-report_to-add" || $file=="employee-report_to-edit"){ ?><?php } ?> href="employee-report_to.php?aid=<?php echo rand(1000,9999).$empid; ?>">Report-to</a></li>
             </ul>
</div>
</div>  

<div class="portlet" id="top-contacts">
<div class="portlet-decoration">
<div class="portlet-title">Qualifications</div>
</div>
<div class="portlet-content">
	<ul>
            	<li><a href="employee-workexperience.php?aid=<?php echo rand(1000,9999).$empid; ?>">Work Experience</a></li>
            	<li><a href="employee-education.php?aid=<?php echo rand(1000,9999).$empid; ?>">Education</a></li>
                <li><a href="employee-skill.php?aid=<?php echo rand(1000,9999).$empid; ?>">Skills</a></li>
                <li><a href="employee-language.php?aid=<?php echo rand(1000,9999).$empid; ?>">Languages</a></li>
                <li><a href="employee-license.php?aid=<?php echo rand(1000,9999).$empid; ?>">License</a></li>
            </ul>
</div>
</div>
 
 <div class="portlet" id="top-contacts">
<div class="portlet-decoration">
<div class="portlet-title">Membership</div>
</div>
<div class="portlet-content">
	<ul>
        <li><a <?php if($file=="employee-membership" || $file=="employee-membership-add" || $file=="employee-membership-edit"){ ?><?php } ?> href="employee-membership.php?aid=<?php echo rand(1000,9999).$empid; ?>">Membership</a></li>
 
		<li><a href="logout.php">Log out</a></li>
            </ul>
</div>
</div>
 
<?php } else { ?>
<div class="portlet" id="top-contacts">
<div class="portlet-decoration">
<div class="portlet-title">Manage Employee</div>
</div>
<div class="portlet-content">
	<ul>
        <li><a <?php if($file=="employee-personal-details"){ ?><?php } ?> href="employee-personal-details.php">Personal Details</a></li>	
        <li><a <?php if($file=="employee-contact-details"){ ?><?php } ?> href="employee-contact-details.php">Contact Details</a></li>
        <li><a <?php if($file=="employee-emergency-contacts" || $file=="employee-emergency-contact-add" || $file=="employee-emergency-contact-edit"){ ?><?php } ?> href="employee-emergency-contacts.php">Emergency Contacts</a></li>
        <li><a <?php if($file=="employee-dependents" || $file=="employee-dependent-add" || $file=="employee-dependent-edit"){ ?><?php } ?> href="employee-dependents.php">Dependents</a></li>
        <li><a <?php if($file=="employee-immegration" || $file=="employee-immegration-add" || $file=="employee-immegration-edit"){ ?><?php } ?> href="employee-immegration.php">Immigration</a></li>
           </ul>
</div>
</div>
 
 <div class="portlet" id="top-contacts">
<div class="portlet-decoration">
          <?php
                 $sql = "select * from message where msg_to_id='".$_SESSION['id']."' and msg_read = '0'";
				 $res = mysqli_query($con, $sql);
				 $chk_no_of_msg = mysqli_num_rows($res);
				 ?>
<div class="portlet-title">
<?php if($file=="message-inbox" || $file=="message-compose"){ ?><?php } ?>Messages<?php if($chk_no_of_msg > 0){ echo " (".$chk_no_of_msg.")" ;} ?>

</div>
</div>
<div class="portlet-content">
       	<ul>                
            	<li><a href="message-inbox.php">Inbox<?php if($chk_no_of_msg > 0){ echo " (".$chk_no_of_msg.")" ;} ?></a></li>
            	<li><a href="message-compose.php">Compose Message</a></li>
                <li><a href="message-sent.php">Sent Items</a></li>
                <li><a href="message-trash.php">Trash</a></li>
                <li><a href="message-archive.php">Archive</a></li>
            </ul>
 </div>
</div>
       
<div class="portlet" id="top-contacts">
<div class="portlet-decoration">

<div class="portlet-title">Job</div>
</div>
<div class="portlet-content">
       	<ul>                
            	<li><a <?php if($file=="employee-job-details"){ ?><?php } ?> href="employee-job-details.php">Job</a></li>
            </ul>
 </div>
</div>    
<div class="portlet" id="top-contacts">
<div class="portlet-decoration">

<div class="portlet-title">Salary</div>
</div>
<div class="portlet-content">
       	<ul>                
            	<li><a <?php if($file=="employee-salary-details"){ ?><?php } ?> href="employee-salary-details.php">Salary</a></li>
            </ul>
 </div>
</div>
    
  <div class="portlet" id="top-contacts">
<div class="portlet-decoration">

<div class="portlet-title">Report-to</div>
</div>
<div class="portlet-content">
       	<ul>                
            	<li><a <?php if($file=="employee-report_to" || $file=="employee-report_to-add" || $file=="employee-report_to-edit"){ ?><?php } ?> href="employee-report_to.php">Report-to</a></li>
            </ul>
 </div>
</div>

<div class="portlet" id="top-contacts">
<div class="portlet-decoration">

<div class="portlet-title">
<?php if($file=="employee-workexperience" || $file=="employee-workexperience-add" || $file=="employee-workexperience-edit" || $file=="employee-education" || $file=="employee-education-add" || $file=="employee-education-edit" || $file=="employee-skill" || $file=="employee-skill-add" || $file=="employee-skill-edit" || $file=="employee-language" || $file=="employee-language-add" || $file=="employee-language-edit" || $file=="employee-license" || $file=="employee-license-add" || $file=="employee-license-edit"){ ?>style="background:#444"<?php } ?>Qualifications
</div>
</div>
<div class="portlet-content">
       	        	<ul>
            	<li><a href="employee-workexperience.php">Work Experience</a></li>
            	<li><a href="employee-education.php">Education</a></li>
                <li><a href="employee-skill.php">Skills</a></li>
                <li><a href="employee-language.php">Languages</a></li>
                <li><a href="employee-license.php">License</a></li>
            </ul>
 </div>
</div>

<div class="portlet" id="top-contacts">
<div class="portlet-decoration">

<div class="portlet-title">
Membership
</div>
</div>
<div class="portlet-content">
       	        	<ul>
            	<li><a <?php if($file=="employee-membership" || $file=="employee-membership-add" || $file=="employee-membership-edit"){ ?>style="background:#444"<?php } ?> href="employee-membership.php">Membership</a></li>
            </ul>
 </div>
</div>

<div class="portlet" id="top-contacts">
<div class="portlet-decoration">

<div class="portlet-title">
Leave
</div>
</div>
<div class="portlet-content">
       	        				<ul>				
				<li><a href="leave-apply.php">Apply</a></li>
                <li><a href="employee-leave-summary.php">Summary</a></li>
           </ul>

 </div>
</div>

<div class="portlet" id="top-contacts">
<div class="portlet-decoration">

<div class="portlet-title">
Punch In/Out
</div>
</div>
<div class="portlet-content">
<ul>				
<li><a <?php if($file=="employee-attendance"){ ?><?php } ?> href="employee-attendance.php">Punch In/Out</a></li>	
           </ul>

 </div>
</div>

<div class="portlet" id="top-contacts">
<div class="portlet-decoration">

<div class="portlet-title">
Log out
</div>
</div>
<div class="portlet-content">
<ul>				
<li><a href="logout.php">Log out</a></li>
           </ul>

 </div>
</div>
<?php } ?>
</div></div>
