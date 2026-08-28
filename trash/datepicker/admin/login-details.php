<?php session_start(); 
include "common.php";
check_user_login();
?>
<?php include "includes/admin-top.php" ?>

<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>
<link href="style/style.css" type="text/css" rel="stylesheet"/>

 <div class="control_Panel">
<?php include "includes/admin-left-con.php" ?>
	<div class="bodyRightCon">
        <div class="bodyRightightCon_inner">
  <div class="bcMenuCon">
    <div class="bcMenu">
      <ul>
        <li>&rsaquo;&nbsp;&nbsp;Admin Management&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Login Details</li>
      </ul>
      <!--<ul class="right">
        <li><a href="#">Add Admin</a></li>
      </ul>-->
      <div class="clr"></div>
    </div>
    <div class="pagicon">
<!--    	<div class="dlt-btn"><input name="Delete" type="button" value="Delete" class="delete-btn" /></div>--> 
	    <div class="item-no">1 - 4 of 4 items</div>
        <div class="page-rslt">Result per page: <select name="limit">
        	<option>10</option>
            <option>20</option>
            <option>30</option>
        </select></div>
        <div class="page-no">
        	<div class="prev"><a href="#"><img src="images/prev.jpg" alt="Previous"/></a> </div>
          	<div class="pagenmbr"><input name="page" value="1" type="text" style="width:16px; text-align:center;"> of 1</div>
          	<div class="next"><a href="#"><img src="images/next.jpg" alt="Next"/></a></div>
            <div class="clr"></div>
        </div>
        <div class="clr"></div>
    </div>
    	<br clear="all"/>
 </div>
   <div>
<div class="admin-hdr-bg">
<!--	<div class="checkbox"><input name="check_all" value="" id="check_all" type="checkbox"></div>
    <div class="usr-name"><strong>Username</strong></div>-->    
    <div class="eID"><strong>Name</strong></div>
    <div class="eID"><strong>Last Login</strong></div>
    <div class="eID"><strong>Login IP</strong></div>
    <div class="eID" style="border-right:none;"><strong>Login Duration</strong></div>
    <div class="clr"></div>
</div>
<div class="admin-dtls">
	<ul>
    	<li>
            <div class="eID">manas@hashlive.com</div>
            <div class="eID">Aug 17, 2010 03:33 pm</div>
            <div class="eID">192.54.1.5</div>
            <div class="eID" style="border-right:none;">02:14:30 | Online</div>
            <div class="clr"></div>
        </li>
        <li class="row-clr">
            <div class="eID">manas@hashlive.com</div>
            <div class="eID">Aug 17, 2010 03:33 pm</div>
            <div class="eID">192.54.1.5</div>
            <div class="eID" style="border-right:none;">02:14:30 | Online</div>
            <div class="clr"></div>
        </li>
        <li>
            <div class="eID">manas@hashlive.com</div>
            <div class="eID">Aug 17, 2010 03:33 pm</div>
            <div class="eID">192.54.1.5</div>
            <div class="eID" style="border-right:none;">02:14:30 | Online</div>
            <div class="clr"></div>
        </li>
        <li class="row-clr">
            <div class="eID">manas@hashlive.com</div>
            <div class="eID">Aug 17, 2010 03:33 pm</div>
            <div class="eID">192.54.1.5</div>
            <div class="eID" style="border-right:none;">02:14:30 | Online</div>
            <div class="clr"></div>
        </li>
        <li>
            <div class="eID">manas@hashlive.com</div>
            <div class="eID">Aug 17, 2010 03:33 pm</div>
            <div class="eID">192.54.1.5</div>
            <div class="eID" style="border-right:none;">02:14:30 | Online</div>
            <div class="clr"></div>
        </li>
        <li class="row-clr">
            <div class="eID">manas@hashlive.com</div>
            <div class="eID">Aug 17, 2010 03:33 pm</div>
            <div class="eID">192.54.1.5</div>
            <div class="eID" style="border-right:none;">02:14:30 | Online</div>
            <div class="clr"></div>
        </li>
        
    </ul>
    
    
</div>

    </div>

 </div>

 <br clear="all"/>
 </div>
 </div>
  <br clear="all" />
 </div>
    <?php include "includes/footer.php" ?>

</body>
</html>
