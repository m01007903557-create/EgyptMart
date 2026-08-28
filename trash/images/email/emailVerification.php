<?php
$message1='<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #ececec;">
	<tbody><tr>
		<td align="center" bgcolor="#ececec">
        	<table style="margin:0 10px;" border="0" cellpadding="0" cellspacing="0" width="640">
            	<tbody><tr><td height="20" width="640"></td></tr>
				<tr>
                <td class="w640" align="center" bgcolor="#438eb9" width="640">
        <table class="" border="0" cellpadding="0" cellspacing="0" width="640">
        <tbody><tr><td class="w30" width="30"></td><td class="" height="30" width="580"></td><td class="" width="30"></td></tr>
        <tr>
            <td class="" width="30"></td>
            <td class="" width="580">
                <div align="center">
                    <p style="font-size: 30px !important;color: #edf7f7; font-family: HelveticaNeue, sans-serif; font-size: 36px; text-align: left; margin-top:0px; margin-bottom:30px;">
                        <strong><singleline label="Title"><a style="color: #edf7f7; text-decoration: none;" href="http://'.$_SERVER['HTTP_HOST'].'" target="_blank">'.getWebSiteName().'</a></singleline></strong>
                    </p>
                </div>
            </td>
            <td class="w30" width="30"></td>
        </tr>
    </tbody></table>
</td>
           </tr>
                <tr><td class="" bgcolor="#ffffff" height="30" width="640"></td></tr>
                <tr id="simple-content-row"><td class="" bgcolor="#ffffff" width="640">
<table class="" align="left" border="0" cellpadding="0" cellspacing="0" width="640">
	<tbody><tr>
    	<td class="" width="30"></td>
        <td class="" width="580">
        	<repeater>
                <layout label="Text only">
                    <table class="" border="0" cellpadding="0" cellspacing="0" width="580">
                        <tbody><tr>
                           <td class="" width="580">
                               <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:18px; font-family: HelveticaNeue, sans-serif;" align="left"><singleline label="Title">Dear '.ucfirst(user_info($_SESSION['uid_indm'],'fname')).' '.ucfirst(user_info($_SESSION['uid_indm'],'lname')).',</singleline></p>
							   <p></p>
							   <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:18px; font-family: HelveticaNeue, sans-serif;" align="left">
							   <singleline label="Title">We are happy you joined. Please click on folowing link to verify your email with us : <a href=http://'.$_SERVER['SERVER_NAME'].'/verifyUser.php?token='.rand(1000,9999).md5($_SESSION['uid_indm']).'>Verify</a></singleline>
							   </p>
								   <p></p>
									<p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:8px; font-family: HelveticaNeue, sans-serif;" align="left"><singleline label="Title">'.get_page_settings(4).' Team.</singleline></p>
                                </td>
                            </tr>
                            <tr><td class="w580" height="10" width="580"></td></tr>
                        </tbody></table>
                    </layout>
                    
                </repeater>
            </td>
            <td class="w30" width="30"></td>
        </tr>
    </tbody></table>
</td></tr>
                <tr><td class="w640" bgcolor="#ffffff" height="15" width="640"></td></tr>
                <tr>
                <td class="w640" align="center" bgcolor="#438eb9" width="640">
        <table class="" border="0" cellpadding="0" cellspacing="0" width="640">
        <tbody>
        <tr>
            <td class="" width="30"></td>
            <td class="" width="580">
                <p><span class="tmu1" style="color:#FFF">Copyright &copy; '.date("Y").' '.getWebSiteName().'. All rights reserved.</span></p>
            </td>
            <td class="w30" width="30"></td>
        </tr>
    </tbody></table>
</td>
           </tr>
                <tr><td class="w640" height="60" width="640"></td></tr>
            </tbody></table>
        </td>
	</tr>
</tbody></table>';

$message2='<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #ececec;">
	<tbody><tr>
		<td align="center" bgcolor="#ececec">
        	<table style="margin:0 10px;" border="0" cellpadding="0" cellspacing="0" width="640">
            	<tbody><tr><td height="20" width="640"></td></tr>
				<tr>
                <td class="w640" align="center" bgcolor="#438eb9" width="640">
        <table class="" border="0" cellpadding="0" cellspacing="0" width="640">
        <tbody><tr><td class="w30" width="30"></td><td class="" height="30" width="580"></td><td class="" width="30"></td></tr>
        <tr>
            <td class="" width="30"></td>
            <td class="" width="580">
                <div align="center">
                    <p style="font-size: 30px !important;color: #edf7f7; font-family: HelveticaNeue, sans-serif; font-size: 36px; text-align: left; margin-top:0px; margin-bottom:30px;">
                        <strong><singleline label="Title"><a style="color: #edf7f7; text-decoration: none;" href="http://'.$_SERVER['HTTP_HOST'].'" target="_blank">'.getWebSiteName().'</a></singleline></strong>
                    </p>
                </div>
            </td>
            <td class="w30" width="30"></td>
        </tr>
    </tbody></table>
</td>
           </tr>
                <tr><td class="" bgcolor="#ffffff" height="30" width="640"></td></tr>
                <tr id="simple-content-row"><td class="" bgcolor="#ffffff" width="640">
<table class="" align="left" border="0" cellpadding="0" cellspacing="0" width="640">
	<tbody><tr>
    	<td class="" width="30"></td>
        <td class="" width="580">
        	<repeater>
                <layout label="Text only">
                    <table class="" border="0" cellpadding="0" cellspacing="0" width="580">
                        <tbody><tr>
                           <td class="" width="580">
                               <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:18px; font-family: HelveticaNeue, sans-serif;" align="left"><singleline label="Title">Dear '.ucfirst(user_info($_SESSION['uid_indm'],'fname')).' '.ucfirst(user_info($_SESSION['uid_indm'],'lname')).',</singleline></p>
							   <p></p>
							   <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:18px; font-family: HelveticaNeue, sans-serif;" align="left">
							   <singleline label="Title">We are happy you joined.</singleline>
							   </p>
								   <p></p>
									<p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:8px; font-family: HelveticaNeue, sans-serif;" align="left"><singleline label="Title">'.get_page_settings(4).' Team.</singleline></p>
                                </td>
                            </tr>
                            <tr><td class="w580" height="10" width="580"></td></tr>
                        </tbody></table>
                    </layout>
                    
                </repeater>
            </td>
            <td class="w30" width="30"></td>
        </tr>
    </tbody></table>
</td></tr>
                <tr><td class="w640" bgcolor="#ffffff" height="15" width="640"></td></tr>
                <tr>
                <td class="w640" align="center" bgcolor="#438eb9" width="640">
        <table class="" border="0" cellpadding="0" cellspacing="0" width="640">
        <tbody>
        <tr>
            <td class="" width="30"></td>
            <td class="" width="580">
                <p><span class="tmu1" style="color:#FFF">Copyright &copy; '.date("Y").' '.getWebSiteName().'. All rights reserved.</span></p>
            </td>
            <td class="w30" width="30"></td>
        </tr>
    </tbody></table>
</td>
           </tr>
                <tr><td class="w640" height="60" width="640"></td></tr>
            </tbody></table>
        </td>
	</tr>
</tbody></table>';
$message_admin='<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #ececec;">
	<tbody><tr>
		<td align="center" bgcolor="#ececec">
        	<table style="margin:0 10px;" border="0" cellpadding="0" cellspacing="0" width="640">
            	<tbody><tr><td height="20" width="640"></td></tr>
				<tr>
                <td class="w640" align="center" bgcolor="#438eb9" width="640">
        <table class="" border="0" cellpadding="0" cellspacing="0" width="640">
        <tbody><tr><td class="w30" width="30"></td><td class="" height="30" width="580"></td><td class="" width="30"></td></tr>
        <tr>
            <td class="" width="30"></td>
            <td class="" width="580">
                <div align="center">
                    <p style="font-size: 30px !important;color: #edf7f7; font-family: HelveticaNeue, sans-serif; font-size: 36px; text-align: left; margin-top:0px; margin-bottom:30px;">
                        <strong><singleline label="Title"><a style="color: #edf7f7; text-decoration: none;" href="http://'.$_SERVER['HTTP_HOST'].'" target="_blank">'.getWebSiteName().'</a></singleline></strong>
                    </p>
                </div>
            </td>
            <td class="w30" width="30"></td>
        </tr>
    </tbody></table>
</td>
           </tr>
                <tr><td class="" bgcolor="#ffffff" height="30" width="640"></td></tr>
                <tr id="simple-content-row"><td class="" bgcolor="#ffffff" width="640">
<table class="" align="left" border="0" cellpadding="0" cellspacing="0" width="640">
	<tbody><tr>
    	<td class="" width="30"></td>
        <td class="" width="580">
        	<repeater>
                <layout label="Text only">
                    <table class="" border="0" cellpadding="0" cellspacing="0" width="580">
                        <tbody><tr>
                           <td class="" width="580">
                               <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:18px; font-family: HelveticaNeue, sans-serif;" align="left"><singleline label="Title">Dear Administrator,</singleline></p>
							   <p></p>
							   <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:4px; font-family: HelveticaNeue, sans-serif;" align="left">
							   <singleline label="Title">A new user has registered on your website. Please check the details as below:</singleline>
							   </p>
							   <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:4px;margin-left:10px; font-family: HelveticaNeue, sans-serif;" align="left">
							   <singleline label="Title">Name: <b>'.$name_prefix.' '.$fname.' '.$lname.'</b></singleline>
							   </p>
							   <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:4px;margin-left:10px; font-family: HelveticaNeue, sans-serif;" align="left">
							   <singleline label="Title">Email: <b>'.$email.'</b></singleline>
							   </p>
							   <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:4px;margin-left:10px; font-family: HelveticaNeue, sans-serif;" align="left">
							   <singleline label="Title">Country: <b>'.$row_cn->cn_name.'</b></singleline>
							   </p>
							   <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:8px;margin-left:10px; font-family: HelveticaNeue, sans-serif;" align="left">
							   <singleline label="Title">Mobile / Cell Phone: <b>'.$mobile1.'</b></singleline>
							   </p>
								   <p></p>
									
                                </td>
                            </tr>
                            <tr><td class="w580" height="10" width="580"></td></tr>
                        </tbody></table>
                    </layout>
                    
                </repeater>
            </td>
            <td class="w30" width="30"></td>
        </tr>
    </tbody></table>
</td></tr>
                <tr><td class="w640" bgcolor="#ffffff" height="15" width="640"></td></tr>
                <tr>
                <td class="w640" align="center" bgcolor="#438eb9" width="640">
        <table class="" border="0" cellpadding="0" cellspacing="0" width="640">
        <tbody>
        <tr>
            <td class="" width="30"></td>
            <td class="" width="580">
                <p><span class="tmu1" style="color:#FFF">Copyright &copy; '.date("Y").' '.getWebSiteName().'. All rights reserved.</span></p>
            </td>
            <td class="w30" width="30"></td>
        </tr>
    </tbody></table>
</td>
           </tr>
                <tr><td class="w640" height="60" width="640"></td></tr>
            </tbody></table>
        </td>
	</tr>
</tbody></table>';
?>