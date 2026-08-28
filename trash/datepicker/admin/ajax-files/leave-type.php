<?php 
ob_start();
session_start(); 
include "../common.php";										

	 				 if($_POST['eid'])
	 					{
								
							?>
									<select name="la_lt_id" id="la_lt_id" onchange="ajaxLB();">
                                	 <option value="">Select</option>
                             <?php
							          
		 
											$eid =  $_POST['eid'];
											 $empsql = "select * from  employee_job where ej_emp_id = '".$eid."'";
											$empres = mysqli_query($con, $empsql);
				                            $emprow =   mysqli_fetch_array( $empres);
											 
									        $ltsql =   "select * from leavetype_applicable join leave_type where leavetype_applicable.lta_lt_id = leave_type.lt_id and leavetype_applicable.lta_jt_id = '".$emprow['ej_jt_id']."' and leave_type.lt_status = '1'";
									        $ltres = mysqli_query($con, $ltsql);
									  		while($rowlt = mysqli_fetch_array( $ltres))
											{
									     	 	?> <option value="<?php echo $rowlt['lt_id'];?>" <?php ?>><?php echo ucfirst($rowlt['lt_name']);?></option>										
									<?php		}
										
                                   			 ?> 
     						 			                          
                            		  </select>     
									
				<?php	
                            }

						?>