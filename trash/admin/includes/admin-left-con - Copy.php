<?php ob_start(); ?>
<div class="bodyLeftCon">    
	<ul id="menu">
        <li>
			<a>Manage Admin</a>
			<ul>
            	<li><a href="change-user.php">Change User Name</a></li>
                <li><a href="change-email.php">Change Email</a></li>
				<li><a href="change-pass.php">Change Password</a></li>
           </ul>
		</li>
      
        	
        <li>
			<a>Organization</a>
			<ul>								
				<li><a href="organization-general-info.php">General Information</a></li>
                <li><a href="department-view.php">Department</a></li>
				<li><a href="location-view.php">Locations</a></li>
                <li><a href="structure.php">Structure</a></li>
           </ul>
		</li>
        <li>
			<a>Job</a>
			<ul>								
				<li><a href="job-title-view.php">Job Title</a></li>
				<li><a href="pay-grades.php">Pay Grades</a></li>
				<li><a href="employment_status-view.php">Employment Status</a></li>
                <li><a href="job_category-view.php">Job Categories</a></li>
           </ul>
		</li>
        <li>
			<a>Memberships</a>
			<ul>								
				<li><a href="add-membership.php">Add Memberships</a></li>
                <li><a href="membership-view.php">View Memberships</a></li>									
           </ul>
		</li>
		<li>
			<a>Qualification</a>
			<ul>								
                <li><a href="skill-view.php">Skill</a></li>
                <li><a href="education-view.php">Education</a></li>
                <li><a href="license-view.php">License</a></li>
                <li><a href="language-view.php">Language</a></li>				
           </ul>
		</li>  
         <li>
			<a>Email Notifications</a>
			<ul>								
				<li><a href="email-configuration.php">Configuration</a></li>
           </ul>
		</li>
        
        <li>
			<a>Project Info</a>
			<ul>								
                <li><a href="customer-view.php">Customer</a></li>
                <li><a href="project-view.php">Projects</a></li>
         </ul>          
		</li>
          <li>
          		 <?php
                 $sql = "select * from message where msg_to_id='".$_SESSION['id']."' and msg_to_id != msg_from_id and msg_read = '0'";
				 $res = mysqli_query($con, $sql);
				 $chk_no_of_msg = mysqli_num_rows($res);
				 ?>   
			<a>Messages<?php if($chk_no_of_msg > 0){ echo " (".$chk_no_of_msg.")" ;} ?></a>
			<ul>              	         	
            	<li><a href="message-compose-admin.php">Compose Message</a></li>
                <li><a href="message-inbox.php">Admin Inbox<?php if($chk_no_of_msg > 0){ echo " (".$chk_no_of_msg.")" ;} ?></a></li>
                <li><a href="message-sent.php"> Sent Items</a></li>
                <li><a href="message-trash.php">Trash Items</a></li>
                <li><a href="message-archive.php">Archive Items</a></li>
                <li><a href="message-inbox-admin.php">Employee Messages</a></li>
           </ul>
		</li>
        <li>
			<a>Leave</a>
			<ul>								
				<li><a href="leaveperiod-edit.php">Configure Leave Period</a></li>
                <li><a href="leavetype-view.php">Configure Leave Types</a></li>
                <li><a href="workweek-edit.php">Configure Work Week</a></li>
                <li><a href="holiday-view.php">Configure Holidays</a></li>
                <li><a href="leave-list.php">Leave List</a></li>
                <li><a href="leaveassign-add.php">Leave Assign</a></li>
                <li><a href="leave-calendar.php">Leave Calendar</a></li>
        	</ul>          
		</li>
        <li>
			<a>Time</a>
			<ul>								
				<li><a href="employee-attendance-record.php">Employee Attendance Record</a></li>
                <li><a href="employee-attendance-summary.php">Attendance Summary</a></li>
        	</ul>          
		</li>

        <li>
			<a>Recruitment</a>
			<ul>								
				<li><a href="vacancy-view.php">Vacancies</a></li>
                <li><a href="candidate-view.php">Candidates</a></li>
        	</ul>          
		</li>
       
        <li>
			<a>Performance</a>
			<ul>								
				<li><a href="perform_kpi-view.php">KPI List</a></li>
                <li><a href="perform_kpi-add.php">Add KPI</a></li>
                <li><a href="performance-kpi-copy.php">Copy KPI</a></li>
                <li><a href="performance-review-view.php">Reviews</a></li>
           </ul>
		</li>
        <li>
			<a>Training</a>
			<ul>								
				<li><a href="training-add.php">Add Training</a></li>
                <li><a href="training-view.php">View Training</a></li>
           </ul>
		</li>
		<li>
			<a>Manage News</a>
			<ul>								
				<li><a href="news-add.php">Add News</a></li>
                <li><a href="news-view.php">View News</a></li>
           </ul>
		</li>
        <li>
			<a>Manage Employee</a>
			<ul>								
				<li><a href="employee-add.php">Add Employee</a></li>
                <li><a href="employee-view.php">View Employees</a></li>
           </ul>
		</li>
        <li>
			<a>Manage Salary</a>
			<ul>								
				<li><a href="monthly-salary.php">Monthly Salary</a></li>
				<li><a href="weekly-salary.php">Weekly Salary</a></li>
           </ul>
		</li>
    	<li>
			<a>Manage Assets</a>
			<ul>								
                <li><a href="vendor-view.php">Vendor</a></li>
                <li><a href="brand-view.php">Brands</a></li>
                <li><a href="category-view.php">Categories</a></li>
                <li><a href="asset-view.php">Asset</a></li>
           </ul>
		</li>
		<li>
			<a>Reports</a>
			<ul>				
				<li><a href="report-view.php">User Defined Report</a></li>
				<li><a href="leave-list.php">Employee Leave Report</a></li>
                <li><a href="emp-turnover-report.php">Employee Turnover Hiring Report</a></li>
                 <li><a href="emp-turnover-termination-report.php">Employee Turnover Termination</a></li>
                <li><a href="headcount.php">Head Count Report</a></li>
           </ul>
		</li>
		<li><a href="logout.php">Log out</a></li>
	</ul>
</div>