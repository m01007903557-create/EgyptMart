<?php
ob_start();
session_start(); 
$uid=$_SESSION['uid_indm'];
include "../common.php";

$sqlchk="select * from company_contact where comp_cnt_user='".$uid."'";
$reschk=mysqli_query($con, $sqlchk);
while($rowchk=mysqli_fetch_object($reschk))
{	
$divisionres=mysqli_query($con, "select * from division where dvtn_id='".$rowchk->comp_cnt_division."'");	
$divisionrow=mysqli_fetch_object($divisionres);
?>
<div id="sort_contact">
    <!--All Additional Conctact details:start--><!--<additonal contact display id:146204>-->
            <div class="mp1 tl bx" id="cd_146204">
			<div class="mp10"> <div class="mz"> <div class="f1 ac1"><h2 id="divsion_146204"><?php echo $divisionrow->dvtn_title;?></h2></div>
            
  <div class="fr" id="sedsvbt<?php echo $rowchk->comp_cnt_id?>" style="display: none; width: 30%;">
  <a class="sl f2 bnr c close mt" onclick="cnctdiscard(<?php echo $rowchk->comp_cnt_id?>);" style="cursor:pointer;">Discard</a>
  <a class="sl f2 bnr c sav mt" onclick="editContact(<?php echo $rowchk->comp_cnt_id;?>); " style="cursor:pointer;">Save</a>
  </div>
  
   <a onclick="delete_contact(<?php echo $rowchk->comp_cnt_id?>);" class="sl f2 del mt c bnr" id="sdel<?php echo $rowchk->comp_cnt_id?>" style="display: block; cursor:pointer;">Delete</a>
   <a onclick="stedit(<?php echo $rowchk->comp_cnt_id?>);" class="sl f2 edit mt c bnr" id="seditbt<?php echo $rowchk->comp_cnt_id?>" style="display: block; cursor:pointer;">Edit</a>

			<div class="clb"></div> </div> 
            
            <div style="display: block;" class="mp10 abc" id="cnctlist<?php echo $rowchk->comp_cnt_id?>"> 
            <div class="mp8">Contact Person</div>
            <div class="mp7"><strong>
            <label id="lbl_salute_146204"><?php echo $rowchk->comp_cnt_prefix ;?></label>
             <label id="lbl_first_name_146204"><?php echo $rowchk->comp_cnt_fname ;?></label>
              <label id="lbl_last_name_146204"><?php echo $rowchk->comp_cnt_lname ;?></label>
              </strong></div> 
              <div class="mp8">Address</div><div class="mp7">
              <label id="lbl_contact_address_146204"><?php echo $rowchk->comp_cnt_address;?> <br><?php echo $rowchk->comp_cnt_address1;?></label></div><div class="mp8">Country</div><div class="mp7"><label id="lbl_country_name_146204"><?php echo get_country_name($rowchk->comp_cnt_country);?></label></div>
              <div class="mp8">Telephone</div>
              <div class="mp7">
              <label id="lbl_phone_146204"><?php if($rowchk->comp_cnt_telephone!=""){ echo $rowchk->comp_cnt_phcntode."-".$rowchk->comp_cnt_phareacode."-".$rowchk->comp_cnt_telephone; } ?></label></div> 
              <div class="mp8">Mobile/Cell Phone</div>
              <div class="mp7">
              <label id="lbl_mobile_146204"><?php if($rowchk->comp_cnt_mobile!=""){ echo $rowchk->comp_cnt_phcntode."-".$rowchk->comp_cnt_mobile; } ?></label></div>
               <div class="mp8">Fax</div>
               <div class="mp7"><label id="lbl_fax_146204"><?php if($rowchk->comp_cnt_fax!=""){ echo $rowchk->comp_cnt_phcntode."-".$rowchk->comp_cnt_faxareacode."-".$rowchk->comp_cnt_fax; } ?></label></div>
			<div class="mp8">Email</div><div class="mp7"><label id="lbl_email_146204"><?php echo $rowchk->comp_cnt_email ;?></label>
            </div>
			</div>
            <!--<additonal contact display ends id:146204>-->

		<!--<additonal contact edit id:146204>-->
		<div class="mp10 ct1 hideup" style="display:none;" id="cnctedit<?php echo $rowchk->comp_cnt_id?>">
        <form name="frm_146204" method="post">
        <div> 
        <table align="left" border="0" cellpadding="4" cellspacing="0" width="490"> <tbody>
        <tr> <td class="label" width="160">Division</td> 
        <td>
        <select name="comp_cnt_division1<?php echo $rowchk->comp_cnt_id?>" id="comp_cnt_division1<?php echo $rowchk->comp_cnt_id?>" class="a_f" tabindex="1">
		<option value="">Select a Division</option>
		<?php
        $divisionres=mysqli_query($con, "select * from division where dvtn_status='1' ");	
        while($divisionrow=mysqli_fetch_object($divisionres))
		{
        ?>
        <option value="<?php echo $divisionrow->dvtn_id; ?>"  <?php if($divisionrow->dvtn_id == $rowchk->comp_cnt_division) { ?> selected="selected" <?php } ?> >
		<?php echo $divisionrow->dvtn_title; ?></option>
        <?php } ?>
		</select> 
        </td> 
        </tr>
        
         <tr> <td class="label" width="160">Contact Person</td> <td> <table border="0" cellpadding="0" cellspacing="0" width="100%"> <tbody><tr> 
         <td width="53">
<select gtbfieldid="9" name="comp_cnt_prefix1<?php echo $rowchk->comp_cnt_id?>" id="comp_cnt_prefix1<?php echo $rowchk->comp_cnt_id?>" class="s_s a_f" style="width: 59px;" tabindex="2">
			<?php
            $arr=array("Mr.","Ms.","Mrs.","Dr.");
            foreach($arr as $val)
            {
            ?>
            <option value="<?php echo $val;?>" <?php if($val==$rowchk->comp_cnt_prefix) { ?> selected="selected" <?php } ?> ><?php echo $val;?> </option>
            <?php } ?>
			</select>
            
            </td> 
            <td width="125">
            <input gtbfieldid="10" maxlength="20" name="comp_cnt_fname1<?php echo $rowchk->comp_cnt_id?>" id="comp_cnt_fname1<?php echo $rowchk->comp_cnt_id?>" tabindex="3" class="a_f f_n_wid ml8" value="<?php echo $rowchk->comp_cnt_fname ;?>">
            </td> 
            <td width="125">
     <input gtbfieldid="11" maxlength="20" size="11" name="comp_cnt_lname1<?php echo $rowchk->comp_cnt_id?>" id="comp_cnt_lname1<?php echo $rowchk->comp_cnt_id?>" tabindex="4" class="a_f f_n_wid ml8" value="<?php echo $rowchk->comp_cnt_lname ;?>">
            </td> </tr> </tbody></table> </td> </tr> 
            
      <tr> 
      <td class="label" width="160">Address</td> 
      <td><input maxlength="190" name="comp_cnt_address1<?php echo $rowchk->comp_cnt_id?>" id="comp_cnt_address1<?php echo $rowchk->comp_cnt_id?>" class="a_f rf" tabindex="5" type="text" value="<?php echo $rowchk->comp_cnt_address ;?>"></td> 
      </tr>
       
      <tr> 
      <td class="label" width="160">&nbsp;</td> 
      <td>
      <input maxlength="200" name="comp_cnt_address2<?php echo $rowchk->comp_cnt_id?>" id="comp_cnt_address2<?php echo $rowchk->comp_cnt_id?>" class="a_f rf" tabindex="6" type="text" value="<?php echo $rowchk->comp_cnt_address1 ;?>">
      </td>
      </tr>
       
       <tr> 
       <td class="label" width="160"><span>*</span>&nbsp;Country</td> 
       <td><div id="a47" class="tbp tbm10" style="display:none"><div class="t1a" align="left">Please select your country.</div></div>
<input name="comp_cnt_country1" readonly="readonly" id="comp_cnt_country1" class="a_f rf" tabindex="7" maxlength="100" type="text" value="<?php echo get_country_name($rowchk->comp_cnt_country);?>"> </td> 
       </tr> 
       
       <tr> <td class="label" width="160">&nbsp;Telephone</td> 
       <td> <table border="0" cellpadding="0" cellspacing="0" width="100%"> 
       <tbody><tr> 
       <td width="50">
       <input gtbfieldid="15" maxlength="6" name="comp_cnt_phcntode1" readonly="readonly" class="ron c_c" value="<?php echo $rowchk->comp_cnt_phcntode ;?>" id="comp_cnt_phcntode" tabindex="8"></td> 
       <td width="60">
<input gtbfieldid="16" class="a_f ml8 a_c" maxlength="6" name="comp_cnt_phareacode1<?php echo $rowchk->comp_cnt_id?>" id="comp_cnt_phareacode1<?php echo $rowchk->comp_cnt_id?>" tabindex="9" value="<?php echo $rowchk->comp_cnt_phareacode ;?>">
</td> 
  <td><input gtbfieldid="17" maxlength="35" name="comp_cnt_telephone1<?php echo $rowchk->comp_cnt_id?>" id="comp_cnt_telephone1<?php echo $rowchk->comp_cnt_id?>" class="a_f ml8 ph_n" tabindex="10" type="text" value="<?php echo $rowchk->comp_cnt_telephone ;?>"></td>
   </tr> </tbody></table> </td> </tr> 
       
       <tr> <td class="label" width="160">Mobile/Cell Phone</td> 
       <td> <table border="0" cellpadding="0" cellspacing="0" width="100%"> <tbody><tr> 
  <td width="50">
  <input gtbfieldid="15" maxlength="6" name="comp_cnt_phcntode1" readonly="readonly" value="<?php echo $rowchk->comp_cnt_phcntode ;?>" id="comp_cnt_phcntode1" class="ron c_c" tabindex="11"></td> 
  <td>
 <input gtbfieldid="17" maxlength="40" name="comp_cnt_mobile1<?php echo $rowchk->comp_cnt_id?>" id="comp_cnt_mobile1<?php echo $rowchk->comp_cnt_id?>" class="a_f ml8 mo_n" tabindex="12" type="text" value="<?php echo $rowchk->comp_cnt_mobile ;?>">
  </td> 
  </tr> </tbody></table> </td> </tr> 
       
  <tr> <td class="label" width="160">Fax</td> <td> <table border="0" cellpadding="0" cellspacing="0" width="100%"> <tbody><tr> 
  <td width="50">
  <input gtbfieldid="15" maxlength="6" readonly="readonly" name="comp_cnt_phcntode1<?php echo $rowchk->comp_cnt_id?>" value="<?php echo $rowchk->comp_cnt_phcntode ;?>" id="comp_cnt_phcntode1<?php echo $rowchk->comp_cnt_id?>" class="ron c_c" tabindex="13"></td> 
  
 <td width="60">
 <input gtbfieldid="16" class="a_f ml8 a_c" name="comp_cnt_faxareacode1<?php echo $rowchk->comp_cnt_id?>" maxlength="6" id="comp_cnt_faxareacode1<?php echo $rowchk->comp_cnt_id?>" tabindex="14" value="<?php echo $rowchk->comp_cnt_faxareacode ;?>">
 </td> 
  <td>
  <input gtbfieldid="17" maxlength="35" name="comp_cnt_fax1<?php echo $rowchk->comp_cnt_id?>" id="comp_cnt_fax1<?php echo $rowchk->comp_cnt_id?>" class="a_f ml8 ph_n" tabindex="15" type="text" value="<?php echo $rowchk->comp_cnt_fax ;?>">
  </td> 
  </tr> </tbody></table> </td> </tr> 
  
  <tr><td class="label" width="160"> E-mail</td> 
  <td>
  <input name="comp_cnt_email1<?php echo $rowchk->comp_cnt_id?>" id="comp_cnt_email1<?php echo $rowchk->comp_cnt_id?>" class="a_f rf" maxlength="200" tabindex="16" type="text" value="<?php echo $rowchk->comp_cnt_email ;?>">
  </td> 
  </tr> 
  <tr> 
  <td width="160">&nbsp;</td> 
  <td align="left">
  <input name="save" id="save" class="saps" value="Save" onclick="editContact(<?php echo $rowchk->comp_cnt_id;?>);" tabindex="17" type="button">&nbsp;  </td> 
  </tr> </tbody></table> </div> 
  </form> 
  </div>
			<!--<additonal contact display ends id:146204>-->
		</div><div class="clb"></div><div class="clb">&nbsp;</div> </div>
    <!--All Additional Conctact details:ends-->
	</div>
    <?php } ?>