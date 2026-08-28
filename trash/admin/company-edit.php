<?php 
include "../common.php";

check_user_login();

class editCompany
{

	var $msg;
	var $bnsprof_id;
	var $bnsprof_uid;
	var $bnsprof_compname;
	var $bnsprof_owntype;
	var $bnsprof_ceoprefix;
	var $bnsprof_ceofname;
	var $bnsprof_ceolname;
	var $bnsprof_address1;
	var $bnsprof_address2;
	var $bnsprof_state;
	var $bnsprof_city;
	var $bnsprof_zipcode;
	var $bnsprof_phcode1;
	var $bnsprof_ph1;
	var $bnsprof_phcode2;
	var $bnsprof_ph2;
	var $bnsprof_phcode3;
	var $bnsprof_ph3;
	var $bnsprof_phcode4;
	var $bnsprof_ph4;
	var $bnsprof_mobile2;
	var $bnsprof_mobile3;	
	var $bnsprof_mobile4;
	var $bnsprof_faxcode1;
	var $bnsprof_fax1;
	var $bnsprof_faxcode2;
	var $bnsprof_fax2;
	var $bnsprof_emailalt1;
	var $bnsprof_emailalt2;
	var $bnsprof_emailalt3;
	var $bnsprof_website_alt;	
	var $bnsprof_businesstype;
	var $bnsprof_yoe;
	var $bnsprof_comemp;
	var $bnsprof_turnover;
	var $bnsprof_regno;
	var $bnsprof_regauthority;
	var $bnsprof_cin_no;
	var $bnsprof_tan_no;
	var $bnsprof_pan_no;
	var $bnsprof_svtax_no;
	var $bnsprof_excisereg_no;
	var $bnsprof_vat_no;
	var $bnsprof_ie_code;
	var $bnsprof_cst_no;
	var $bnsprof_msme_no;
	var $bnsprof_epf_no;
	var $bnsprof_esi_no;
	var $bnsprof_sct_no;
	var $bnsprof_dnb_no;
	var $bnsprof_rbi_no;
	var $bnsprof_fssailic_no;
	var $bnsprof_nsic_no;
	var $bnsprof_sst_no;
	var $bnsprof_complogo;
	
	

	function __construct($bnsprof_id){
		$this->bnsprof_id=$bnsprof_id;
	}
	function detailsObj(){
		global $con;
		$sql="select * from user,business_profile where bnsprof_uid=usr_id and md5(bnsprof_id)='".$this->bnsprof_id."'";
		$res=mysqli_query($con, $sql);
		return mysqli_fetch_object($res);
	}
	function valid()
	{
		$valid=true;
		
		$filename = $_FILES['bnsprof_complogo']['name'];
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		
				
		return $valid;
	}
	
	function update() 
	{
		global $con;
		if($_FILES["bnsprof_complogo"]["name"] != '')
		{
			if ($_FILES["bnsprof_complogo"]["error"] > 0)
			{
				$msg = "Return Code: " . $_FILES["bnsprof_complogo"]["error"] . "<br />";
			}
			else
			{	
				/*$imgSImage = new SimpleImage();
				$imgSImage->load($_FILES['bnsprof_complogo']['tmp_name']);
				$imgSImage->resize($this->adv_imagewidth,$this->adv_imageheight);
				$imgSImage->save("../upload/companylogo/".$this->bnsprof_complogo);*/
				
				$filename = $_FILES['bnsprof_complogo']['name'];
				$ext = pathinfo($filename, PATHINFO_EXTENSION);
				
				$vid=$this->bnsprof_uid.date("YmdHis");
				
		        $this->bnsprof_complogo=$vid.'.'.$ext;

				$ds = move_uploaded_file($_FILES["bnsprof_complogo"]["tmp_name"], "../upload/companylogo/".$this->bnsprof_complogo) or die('error');
				
				if($ds)
				{
					/** Thumb image creation **/
					$imgSImage = new SimpleImage();			
					$imgSImage->load("../upload/companylogo/".$this->bnsprof_complogo);			
					$imgSImage->resize(90,90);//width,height
				
					$imgSImage->save("../upload/companylogo/thumb/".$this->bnsprof_complogo);
					/** Thumb image creation **/
					
		
					$sqlImg="select * from business_profile where bnsprof_id='".$this->bnsprof_id."'";
					$resImg=mysqli_query($con, $sqlImg);
					$rowImg=mysqli_fetch_object($resImg);
				
					$pathLrg="../upload/companylogo/".$rowImg->bnsprof_complogo;
					if(is_file($pathLrg))
					{
						unlink($pathLrg);
					}
					
					$pathThumb="../upload/companylogo/thumb/".$rowImg->bnsprof_complogo;
					if(is_file($pathThumb))
					{
						unlink($pathThumb);
					}
					
					$btype1="";
					foreach($this->bnsprof_businesstype as $val)
					{
						$btype1=$val.",".$btype1;	
					}
					$btype=substr($btype1,0,-1);
										
					$sql="update business_profile
							set
								bnsprof_compname='".$this->bnsprof_compname."',
								bnsprof_owntype='".$this->bnsprof_owntype."',
								bnsprof_ceoprefix='".$this->bnsprof_ceoprefix."',
								bnsprof_ceofname='".$this->bnsprof_ceofname."',
								bnsprof_ceolname='".$this->bnsprof_ceolname."',
								bnsprof_address1='".$this->bnsprof_address1."',
								bnsprof_address2='".$this->bnsprof_address2."',
								bnsprof_state='".$this->bnsprof_state."',
								bnsprof_city='".$this->bnsprof_city."',
								bnsprof_zipcode='".$this->bnsprof_zipcode."',
								bnsprof_phcode1='".$this->bnsprof_phcode1."',
								bnsprof_ph1='".$this->bnsprof_ph1."',
								bnsprof_phcode2='".$this->bnsprof_phcode2."',
								bnsprof_ph2='".$this->bnsprof_ph2."',
								bnsprof_phcode3='".$this->bnsprof_phcode3."',
								bnsprof_ph3='".$this->bnsprof_ph3."',
								bnsprof_phcode4='".$this->bnsprof_phcode4."',
								bnsprof_ph4='".$this->bnsprof_ph4."',
								bnsprof_mobile2='".$this->bnsprof_mobile2."',
								bnsprof_mobile3='".$this->bnsprof_mobile3."',
								bnsprof_mobile4='".$this->bnsprof_mobile4."',
								bnsprof_faxcode1='".$this->bnsprof_faxcode1."',
								bnsprof_fax1='".$this->bnsprof_fax1."',
								bnsprof_faxcode2='".$this->bnsprof_faxcode2."',
								bnsprof_fax2='".$this->bnsprof_fax2."',
								bnsprof_emailalt1='".$this->bnsprof_emailalt1."',
								bnsprof_emailalt2='".$this->bnsprof_emailalt2."',
								bnsprof_emailalt3='".$this->bnsprof_emailalt3."',
								bnsprof_website_alt='".$this->bnsprof_website_alt."',
								bnsprof_businesstype='".$btype."',
								bnsprof_yoe='".$this->bnsprof_yoe."',
								bnsprof_comemp='".$this->bnsprof_comemp."',
								bnsprof_turnover='".$this->bnsprof_turnover."',
								bnsprof_regno='".$this->bnsprof_regno."',
								bnsprof_regauthority='".$this->bnsprof_regauthority."',
								bnsprof_cin_no='".$this->bnsprof_cin_no."',
								bnsprof_tan_no='".$this->bnsprof_tan_no."',
								bnsprof_pan_no='".$this->bnsprof_pan_no."',
								bnsprof_svtax_no='".$this->bnsprof_svtax_no."',
								bnsprof_excisereg_no='".$this->bnsprof_excisereg_no."',
								bnsprof_vat_no='".$this->bnsprof_vat_no."',
								bnsprof_ie_code='".$this->bnsprof_ie_code."',
								bnsprof_cst_no='".$this->bnsprof_cst_no."',
								bnsprof_msme_no='".$this->bnsprof_msme_no."',
								bnsprof_epf_no='".$this->bnsprof_epf_no."',
								bnsprof_esi_no='".$this->bnsprof_esi_no."',
								bnsprof_sct_no='".$this->bnsprof_sct_no."',
								bnsprof_dnb_no='".$this->bnsprof_dnb_no."',
								bnsprof_rbi_no='".$this->bnsprof_rbi_no."',
								bnsprof_fssailic_no='".$this->bnsprof_fssailic_no."',
								bnsprof_nsic_no='".$this->bnsprof_nsic_no."',
								bnsprof_sst_no='".$this->bnsprof_sst_no."',
								bnsprof_complogo ='".$this->bnsprof_complogo."'
							where
								bnsprof_id='".$this->bnsprof_id."'";
								
					mysqli_query($con, $sql) or die(mysql_error());
														
					$this->msg='<div class="alert alert-success"><i class="icon-ok"></i> Company updated successfully</div>';	
				}
			}		
		}
		else
		{
			$btype1="";
			foreach($this->bnsprof_businesstype as $val)
			{
				$btype1=$val.",".$btype1;
			}
			$btype=substr($btype1,0,-1);
			
			$sql="update business_profile
							set
								bnsprof_compname='".$this->bnsprof_compname."',
								bnsprof_owntype='".$this->bnsprof_owntype."',
								bnsprof_ceoprefix='".$this->bnsprof_ceoprefix."',
								bnsprof_ceofname='".$this->bnsprof_ceofname."',
								bnsprof_ceolname='".$this->bnsprof_ceolname."',
								bnsprof_address1='".$this->bnsprof_address1."',
								bnsprof_address2='".$this->bnsprof_address2."',
								bnsprof_state='".$this->bnsprof_state."',
								bnsprof_city='".$this->bnsprof_city."',
								bnsprof_zipcode='".$this->bnsprof_zipcode."',
								bnsprof_phcode1='".$this->bnsprof_phcode1."',
								bnsprof_ph1='".$this->bnsprof_ph1."',
								bnsprof_phcode2='".$this->bnsprof_phcode2."',
								bnsprof_ph2='".$this->bnsprof_ph2."',
								bnsprof_phcode3='".$this->bnsprof_phcode3."',
								bnsprof_ph3='".$this->bnsprof_ph3."',
								bnsprof_phcode4='".$this->bnsprof_phcode4."',
								bnsprof_ph4='".$this->bnsprof_ph4."',
								bnsprof_mobile2='".$this->bnsprof_mobile2."',
								bnsprof_mobile3='".$this->bnsprof_mobile3."',
								bnsprof_mobile4='".$this->bnsprof_mobile4."',
								bnsprof_faxcode1='".$this->bnsprof_faxcode1."',
								bnsprof_fax1='".$this->bnsprof_fax1."',
								bnsprof_faxcode2='".$this->bnsprof_faxcode2."',
								bnsprof_fax2='".$this->bnsprof_fax2."',
								bnsprof_emailalt1='".$this->bnsprof_emailalt1."',
								bnsprof_emailalt2='".$this->bnsprof_emailalt2."',
								bnsprof_emailalt3='".$this->bnsprof_emailalt3."',
								bnsprof_website_alt='".$this->bnsprof_website_alt."',
								bnsprof_businesstype='".$btype."',
								bnsprof_yoe='".$this->bnsprof_yoe."',
								bnsprof_comemp='".$this->bnsprof_comemp."',
								bnsprof_turnover='".$this->bnsprof_turnover."',
								bnsprof_regno='".$this->bnsprof_regno."',
								bnsprof_regauthority='".$this->bnsprof_regauthority."',
								bnsprof_cin_no='".$this->bnsprof_cin_no."',
								bnsprof_tan_no='".$this->bnsprof_tan_no."',
								bnsprof_pan_no='".$this->bnsprof_pan_no."',
								bnsprof_svtax_no='".$this->bnsprof_svtax_no."',
								bnsprof_excisereg_no='".$this->bnsprof_excisereg_no."',
								bnsprof_vat_no='".$this->bnsprof_vat_no."',
								bnsprof_ie_code='".$this->bnsprof_ie_code."',
								bnsprof_cst_no='".$this->bnsprof_cst_no."',
								bnsprof_msme_no='".$this->bnsprof_msme_no."',
								bnsprof_epf_no='".$this->bnsprof_epf_no."',
								bnsprof_esi_no='".$this->bnsprof_esi_no."',
								bnsprof_sct_no='".$this->bnsprof_sct_no."',
								bnsprof_dnb_no='".$this->bnsprof_dnb_no."',
								bnsprof_rbi_no='".$this->bnsprof_rbi_no."',
								bnsprof_fssailic_no='".$this->bnsprof_fssailic_no."',
								bnsprof_nsic_no='".$this->bnsprof_nsic_no."',
								bnsprof_sst_no='".$this->bnsprof_sst_no."'
							where
								bnsprof_id='".$this->bnsprof_id."'";

											
				mysqli_query($con, $sql) or die(mysql_error());
						
			$this->msg='<div class="alert alert-success"><i class="icon-ok"></i> Company updated successfully</div>';
		}	
			   	
	}	
}

if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg']; unset($_SESSION['msg']); }else{ $msg=""; }

$ob=new editCompany($_GET['id']);
$row=$ob->detailsObj();

if(isset($_POST['btnUpdate'])){

	/*[bnsprof_id] => 1 [bnsprof_compname] => Wipro Ltd [bnsprof_owntype] => 2 [bnsprof_ceoprefix] => Mr. [bnsprof_ceofname] => avinab [bnsprof_ceolname] => das [bnsprof_address1] => kolkata, kolkata - 89 [bnsprof_address2] => kolkata - 89 [bnsprof_state] => 16 [bnsprof_city] => 817 [bnsprof_zipcode] => 700005 [bnsprof_businesstype] => Array ( [0] => 1 [1] => 4 [2] => 8 [3] => 9 [4] => 11 [5] => 12 [6] => 13 ) [bnsprof_yoe] => 2012 [bnsprof_comemp] => 5 [bnsprof_turnover] => 8 [bnsprof_regno] => 001000065 [bnsprof_regauthority] => 55698559 [bnsprof_cin_no] => 0096855 [bnsprof_tan_no] => 95AKJ5555 [bnsprof_pan_no] => 69895566 [bnsprof_svtax_no] => 88HJKLKK5665 [bnsprof_excisereg_no] => 9822MBNGIGO5565 [bnsprof_vat_no] => 665256HKKK95 [bnsprof_ie_code] => H985223632 [bnsprof_cst_no] => HKKKLKKGFK [bnsprof_msme_no] => 9866KGG655 [bnsprof_epf_no] => 98233655JGKG [bnsprof_esi_no] => 98665UJHJJHH [bnsprof_sct_no] => 98665UJHJJHH [bnsprof_dnb_no] => 6555655 [bnsprof_rbi_no] => 9456262626 [bnsprof_fssailic_no] => 626326265 [bnsprof_nsic_no] => 069895496569 [bnsprof_sst_no] => 665656559 [btnUpdate] */
	

	$ob->bnsprof_id=addslashes(trim($_POST['bnsprof_id']));
	$ob->bnsprof_uid=addslashes(trim($_POST['bnsprof_uid']));
	$ob->bnsprof_compname=addslashes(trim($_POST['bnsprof_compname']));
	$ob->bnsprof_owntype=addslashes(trim($_POST['bnsprof_owntype']));
	$ob->bnsprof_ceoprefix=addslashes(trim($_POST['bnsprof_ceoprefix']));
	$ob->bnsprof_ceofname=addslashes(trim($_POST['bnsprof_ceofname']));
	$ob->bnsprof_ceolname=addslashes(trim($_POST['bnsprof_ceolname']));
	$ob->bnsprof_address1=addslashes(trim($_POST['bnsprof_address1']));
	$ob->bnsprof_address2=addslashes(trim($_POST['bnsprof_address2']));
	$ob->bnsprof_state=addslashes(trim($_POST['bnsprof_state']));
	$ob->bnsprof_city=addslashes(trim($_POST['bnsprof_city']));
	$ob->bnsprof_zipcode=addslashes(trim($_POST['bnsprof_zipcode']));
	$ob->bnsprof_phcode1=addslashes(trim($_POST['bnsprof_phcode1']));
	$ob->bnsprof_ph1=addslashes(trim($_POST['bnsprof_ph1']));
	$ob->bnsprof_phcode2=addslashes(trim($_POST['bnsprof_phcode2']));
	$ob->bnsprof_ph2=addslashes(trim($_POST['bnsprof_ph2']));
	$ob->bnsprof_phcode3=addslashes(trim($_POST['bnsprof_phcode3']));
	$ob->bnsprof_ph3=addslashes(trim($_POST['bnsprof_ph3']));
	$ob->bnsprof_phcode4=addslashes(trim($_POST['bnsprof_phcode4']));
	$ob->bnsprof_ph4=addslashes(trim($_POST['bnsprof_ph4']));
	$ob->bnsprof_mobile2=addslashes(trim($_POST['bnsprof_mobile2']));
	$ob->bnsprof_mobile3=addslashes(trim($_POST['bnsprof_mobile3']));
	$ob->bnsprof_mobile4=addslashes(trim($_POST['bnsprof_mobile4']));
	$ob->bnsprof_faxcode1=addslashes(trim($_POST['bnsprof_faxcode1']));
	$ob->bnsprof_fax1=addslashes(trim($_POST['bnsprof_fax1']));
	$ob->bnsprof_faxcode2=addslashes(trim($_POST['bnsprof_faxcode2']));
	$ob->bnsprof_fax2=addslashes(trim($_POST['bnsprof_fax2']));
	$ob->bnsprof_emailalt1=addslashes(trim($_POST['bnsprof_emailalt1']));
	$ob->bnsprof_emailalt2=addslashes(trim($_POST['bnsprof_emailalt2']));
	$ob->bnsprof_emailalt3=addslashes(trim($_POST['bnsprof_emailalt3']));
	$ob->bnsprof_website_alt=addslashes(trim($_POST['bnsprof_website_alt']));
	$ob->bnsprof_businesstype=$_POST['bnsprof_businesstype'];
	$ob->bnsprof_yoe=addslashes(trim($_POST['bnsprof_yoe']));
	$ob->bnsprof_comemp=addslashes(trim($_POST['bnsprof_comemp']));
	$ob->bnsprof_turnover=addslashes(trim($_POST['bnsprof_turnover']));
	$ob->bnsprof_regno=addslashes(trim($_POST['bnsprof_regno']));
	$ob->bnsprof_regauthority=addslashes(trim($_POST['bnsprof_regauthority']));
	$ob->bnsprof_cin_no=addslashes(trim($_POST['bnsprof_cin_no']));
	$ob->bnsprof_tan_no=addslashes(trim($_POST['bnsprof_tan_no']));
	$ob->bnsprof_pan_no=addslashes(trim($_POST['bnsprof_pan_no']));
	$ob->bnsprof_svtax_no=addslashes(trim($_POST['bnsprof_svtax_no']));
	$ob->bnsprof_excisereg_no=addslashes(trim($_POST['bnsprof_excisereg_no']));
	$ob->bnsprof_vat_no=addslashes(trim($_POST['bnsprof_vat_no']));
	$ob->bnsprof_ie_code=addslashes(trim($_POST['bnsprof_ie_code']));
	$ob->bnsprof_cst_no=addslashes(trim($_POST['bnsprof_cst_no']));
	$ob->bnsprof_msme_no=addslashes(trim($_POST['bnsprof_msme_no']));
	$ob->bnsprof_epf_no=addslashes(trim($_POST['bnsprof_epf_no']));
	$ob->bnsprof_esi_no=addslashes(trim($_POST['bnsprof_esi_no']));
	$ob->bnsprof_sct_no=addslashes(trim($_POST['bnsprof_sct_no']));
	$ob->bnsprof_dnb_no=addslashes(trim($_POST['bnsprof_dnb_no']));
	$ob->bnsprof_rbi_no=addslashes(trim($_POST['bnsprof_rbi_no']));
	$ob->bnsprof_fssailic_no=addslashes(trim($_POST['bnsprof_fssailic_no']));
	$ob->bnsprof_nsic_no=addslashes(trim($_POST['bnsprof_nsic_no']));
	$ob->bnsprof_sst_no=addslashes(trim($_POST['bnsprof_sst_no']));
	$ob->bnsprof_complogo=trim($_FILES['bnsprof_complogo']['name']);
	
	

		
	if($ob->valid()){
		$ob->update();
	}
	$_SESSION['msg']=$ob->msg;
	
	header("Location:company-edit.php?id=".md5($ob->bnsprof_id));
}


?>
<?php include "includes/admin-top.php" ?>
<div class="main-container" id="main-container">
	<script type="text/javascript">
		try{ace.settings.check('main-container' , 'fixed')}catch(e){}
	</script>

	<div class="main-container-inner">
		<a class="menu-toggler" id="menu-toggler" href="#">
			<span class="menu-text"></span>
		</a>
<script type="text/javascript">
function showCity(id)
{
	$.get("showCity.php",{id:id},	function(data){	$('#bnsprof_city').html(data); }); 
}
function checkEmail(eml)
{
	var at = "@";
	var dot = ".";
	var lat = eml.indexOf(at);
	var lstr = eml.length;
	var ldot = eml.indexOf(dot);
	
	if(eml.indexOf(at) == -1 || eml.indexOf(at) == 0 || eml.indexOf(at) == lstr || eml.indexOf(dot) == -1 || eml.indexOf(dot) == 0 || eml.indexOf(dot) == lstr || eml.indexOf(at,(lat+1)) != -1 || eml.substring(lat-1,lat) == dot || eml.substring(lat+1,lat+2) == dot || eml.indexOf(dot,(lat+2)) == -1 || eml.indexOf(" ") != -1)
	{	
		return true;
	}
	else
	{
		return false;	
	}
}
function validForm()
{
	var bnsprof_compname=document.getElementById('bnsprof_compname');
	var bnsprof_ceofname=document.getElementById('bnsprof_ceofname');
	var bnsprof_ceolname=document.getElementById('bnsprof_ceolname');
	var bnsprof_address1=document.getElementById('bnsprof_address1');
	var bnsprof_state=document.getElementById('bnsprof_state');
	var bnsprof_city=document.getElementById('bnsprof_city');
	var bnsprof_zipcode=document.getElementById('bnsprof_zipcode');
	
	var bnsprof_phcode1=document.getElementById('bnsprof_phcode1');
	var bnsprof_ph1=document.getElementById('bnsprof_ph1');
	var bnsprof_phcode2=document.getElementById('bnsprof_phcode2');
	var bnsprof_ph2=document.getElementById('bnsprof_ph2');
	var bnsprof_phcode3=document.getElementById('bnsprof_phcode3');
	var bnsprof_ph3=document.getElementById('bnsprof_ph3');
	var bnsprof_phcode4=document.getElementById('bnsprof_phcode4');
	var bnsprof_ph4=document.getElementById('bnsprof_ph4');
	
	var bnsprof_mobile2=document.getElementById('bnsprof_mobile2');
	var bnsprof_mobile3=document.getElementById('bnsprof_mobile3');
	var bnsprof_mobile4=document.getElementById('bnsprof_mobile4');
	
	var bnsprof_faxcode1=document.getElementById('bnsprof_faxcode1');
	var bnsprof_fax1=document.getElementById('bnsprof_fax1');
	var bnsprof_faxcode2=document.getElementById('bnsprof_faxcode2');
	var bnsprof_fax2=document.getElementById('bnsprof_fax2');
	
	var bnsprof_emailalt1=document.getElementById('bnsprof_emailalt1');
	var bnsprof_emailalt2=document.getElementById('bnsprof_emailalt2');
	var bnsprof_emailalt3=document.getElementById('bnsprof_emailalt3');
	var bnsprof_yoe=document.getElementById('bnsprof_yoe');

	var fup = document.getElementById('bnsprof_complogo');
	var fileName = fup.value;
    var ext = fileName.substring(fileName.lastIndexOf('.') + 1);

	var message="";
	var valid=true;

	if(bnsprof_compname.value=='' || bnsprof_compname.value == null)
	{
		message='Please enter Company Name.';
		bnsprof_compname.focus();
		valid=false;
	}
	else if(!isNaN(bnsprof_compname.value))
	{
		message='Please enter valid Company Name.';
		bnsprof_compname.value='';
		bnsprof_compname.focus();
		valid=false;
	}
	else if(bnsprof_ceofname.value!="" && !isNaN(bnsprof_ceofname.value))
	{
		message="Please enter valid First Name of CEO.";
		bnsprof_ceofname.value='';
		bnsprof_ceofname.focus();
		valid=false;	
	}
	else if(bnsprof_ceolname.value!="" && !isNaN(bnsprof_ceolname.value))
	{
		message="Please enter valid Last Name of CEO.";
		bnsprof_ceolname.value='';
		bnsprof_ceolname.focus();
		valid=false;	
	}
	else if(bnsprof_address1.value=='' || bnsprof_address1.value == null)
	{
		message='Please enter Address.';
		bnsprof_address1.focus();
		valid=false;
	}
	else if(bnsprof_state.value=='' || bnsprof_state.value == null || bnsprof_state.value=='0')
	{
		message='Please select State.';
		bnsprof_state.focus();
		valid=false;
	}
	else if(bnsprof_city.value=='' || bnsprof_city.value == null || bnsprof_city.value=='0')
	{
		message='Please select City.';
		bnsprof_city.focus();
		valid=false;
	}
	else if(bnsprof_zipcode.value!="" && isNaN(bnsprof_zipcode.value))
	{
		message="Please enter valid Postal/Zip Code.";
		bnsprof_zipcode.value='';
		bnsprof_zipcode.focus();
		valid=false;	
	}
	else if(bnsprof_phcode1.value=='' || bnsprof_phcode1.value == null || bnsprof_phcode1.value=='0')
	{
		message='Please enter Area Telephone Code.';
		bnsprof_phcode1.value='';
		bnsprof_phcode1.focus();
		valid=false;
	}
	else if(isNaN(bnsprof_phcode1.value))
	{
		message='Please enter valid Area Telephone Code.';
		bnsprof_phcode1.value='';
		bnsprof_phcode1.focus();
		valid=false;
	}
	else if(bnsprof_ph1.value=='' || bnsprof_ph1.value == null || bnsprof_ph1.value=='0')
	{
		message='Please enter Phone Number.';
		bnsprof_ph1.value='';
		bnsprof_ph1.focus();
		valid=false;
	}
	else if(isNaN(bnsprof_ph1.value))
	{
		message='Please enter valid Phone Number.';
		bnsprof_ph1.value='';
		bnsprof_ph1.focus();
		valid=false;
	}
	else if((bnsprof_phcode2.value!='' && bnsprof_phcode2.value != null) && isNaN(bnsprof_phcode2.value))
	{
		message='Please enter valid Area Telephone Code.';
		bnsprof_phcode2.value='';
		bnsprof_phcode2.focus();
		valid=false;
	}
	else if((bnsprof_ph2.value!='' && bnsprof_ph2.value != null) && isNaN(bnsprof_ph2.value))
	{
		message='Please enter valid Phone Number.';
		bnsprof_ph2.value='';
		bnsprof_ph2.focus();
		valid=false;
	}
	else if((bnsprof_phcode3.value!='' && bnsprof_phcode3.value != null) && isNaN(bnsprof_phcode3.value))
	{
		message='Please enter valid Area Telephone Code.';
		bnsprof_phcode3.value='';
		bnsprof_phcode3.focus();
		valid=false;
	}
	else if((bnsprof_ph3.value!='' && bnsprof_ph3.value != null) && isNaN(bnsprof_ph3.value))
	{
		message='Please enter valid Phone Number.';
		bnsprof_ph3.value='';
		bnsprof_ph3.focus();
		valid=false;
	}
	else if((bnsprof_phcode4.value!='' && bnsprof_phcode4.value != null) && isNaN(bnsprof_phcode4.value))
	{
		message='Please enter valid Area Telephone Code.';
		bnsprof_phcode4.value='';
		bnsprof_phcode4.focus();
		valid=false;
	}
	else if((bnsprof_ph4.value!='' && bnsprof_ph4.value != null) && isNaN(bnsprof_ph4.value))
	{
		message='Please enter valid Phone Number.';
		bnsprof_ph4.value='';
		bnsprof_ph4.focus();
		valid=false;
	}
	else if((bnsprof_mobile2.value!='' && bnsprof_mobile2.value!=null) && (isNaN(bnsprof_mobile2.value) || bnsprof_mobile2.value.length!=10))
	{
		message='Please enter valid Mobile Number.';
		bnsprof_mobile2.value='';
		bnsprof_mobile2.focus();
		valid=false;
	}
	else if((bnsprof_mobile3.value!='' && bnsprof_mobile3.value!=null) && (isNaN(bnsprof_mobile3.value) || bnsprof_mobile3.value.length!=10))
	{
		message='Please enter valid Mobile Number.';
		bnsprof_mobile3.value='';
		bnsprof_mobile3.focus();
		valid=false;
	}
	else if((bnsprof_mobile4.value!='' && bnsprof_mobile4.value!=null) && (isNaN(bnsprof_mobile4.value) || bnsprof_mobile4.value.length!=10))
	{
		message='Please enter valid Mobile Number.';
		bnsprof_mobile4.value='';
		bnsprof_mobile4.focus();
		valid=false;
	}
	else if((bnsprof_faxcode1.value!='' && bnsprof_faxcode1.value!=null) && isNaN(bnsprof_faxcode1.value))
	{
		message='Please enter valid Fax Code Number.';
		bnsprof_faxcode1.value='';
		bnsprof_faxcode1.focus();
		valid=false;
	}
	else if((bnsprof_fax1.value!='' && bnsprof_fax1.value!=null) && isNaN(bnsprof_fax1.value))
	{
		message='Please enter valid Fax Number.';
		bnsprof_fax1.value='';
		bnsprof_fax1.focus();
		valid=false;
	}
	else if((bnsprof_faxcode2.value!='' && bnsprof_faxcode2.value!=null) && isNaN(bnsprof_faxcode2.value))
	{
		message='Please enter valid Fax Code Number.';
		bnsprof_faxcode2.value='';
		bnsprof_faxcode2.focus();
		valid=false;
	}
	else if((bnsprof_fax2.value!='' && bnsprof_fax2.value!=null) && isNaN(bnsprof_fax2.value))
	{
		message='Please enter valid Fax Number.';
		bnsprof_fax2.value='';
		bnsprof_fax2.focus();
		valid=false;
	}
	else if(bnsprof_emailalt1.value!='' && bnsprof_emailalt1.value!=null && checkEmail(bnsprof_emailalt1.value))
	{
		message='Please enter valid Email Address.';
		bnsprof_emailalt1.value='';
		bnsprof_emailalt1.focus();
		valid=false;
	}
	else if(bnsprof_emailalt2.value!='' && bnsprof_emailalt2.value!=null && checkEmail(bnsprof_emailalt2.value))
	{
		message='Please enter valid Email Address.';
		bnsprof_emailalt2.value='';
		bnsprof_emailalt2.focus();
		valid=false;
	}
	else if(bnsprof_emailalt3.value!='' && bnsprof_emailalt3.value!=null && checkEmail(bnsprof_emailalt3.value))
	{
		message='Please enter valid Email Address.';
		bnsprof_emailalt3.value='';
		bnsprof_emailalt3.focus();
		valid=false;
	}
	else if((bnsprof_yoe.value!='' && bnsprof_yoe.value!=null) && (isNaN(bnsprof_yoe.value) || parseInt(bnsprof_yoe.value.length)!=4))
	{
		message='Please enter valid Year.';
		bnsprof_yoe.value='';
		bnsprof_yoe.focus();
		valid=false;
	}
	if(fileName!='' && ext !="GIF" && ext!="gif" && ext !="JPG" && ext!="jpg" && ext !="PNG" && ext!="png" && ext !="JPEG" && ext!="jpeg")
    {
		message='Please upload valid File.';
		fup.value='';
		fup.focus();
		valid=false;
    }
	
	
	if(!valid)
	{
		document.getElementById('msg').innerHTML = "<i class='icon-remove'></i> "+message;
		document.getElementById('msg').className="alert alert-danger";
	}
	return valid;
}
</script>
<?php include "includes/admin-left-con.php" ?>
<div class="main-content">
	<div class="breadcrumbs" id="breadcrumbs">
		<script type="text/javascript">
			try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
		</script>

		<ul class="breadcrumb">
			<li>
				<i class="icon-home home-icon"></i>
				<a href="welcome.php">Home</a>
			</li>
			<li>
				<a href="company-list.php">Manage Company</a>
			</li>
			<li class="active">Company Details</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
				
<div class="page-content">
	<div class="page-header">
		<h1>
			Manage Company
			<small>
				<i class="icon-double-angle-right"></i>
                <?php if($row->bnsprof_compname!=''){ ?>
				Details of <strong><?php echo ucfirst($row->bnsprof_compname);?></strong>
                <?php }else{ ?>
                Company Details
                <?php } ?>
			</small>
		</h1>
	</div>
	<div class="row">
		<div class="col-xs-12">
<form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onSubmit="return validForm();">
	<em style="display:block;margin:5px;">Fields with <span style="color:#F00">*</span> are required.</em>
    <input type="hidden" id="bnsprof_id" name="bnsprof_id" value="<?php  echo $row->bnsprof_id;	?>" />
    <input type="hidden" id="bnsprof_uid" name="bnsprof_uid" value="<?php  echo $row->bnsprof_uid;	?>" />
    
    <div id="msg"><?php echo $msg; ?></div>
    
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Company Name: <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
	        <input name="bnsprof_compname" id="bnsprof_compname" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_compname; ?>" />
		</div>
	</div>
   <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Business Ownership Type:</label>
		<div class="col-sm-9">
        <select class="col-sm-5" name="bnsprof_owntype" id="bnsprof_owntype">
            <option value="">---Choose One---</option>
            <?php
			$c=1;
            $owntypesql=mysqli_query($con, "select * from ownership_type where owntyp_status='1'");
			while($owntyperow=mysqli_fetch_object($owntypesql))
			{
			?>
            <option value="<?php echo $owntyperow->owntyp_id;?>" <?php if($row->bnsprof_owntype == $owntyperow->owntyp_id){ ?> selected="selected" <?php } ?>>
			<?php echo $owntyperow->owntyp_title;?></option>
            <?php } ?>
            </select>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">CEO:</label>
		<div class="col-sm-8">
         <select name="bnsprof_ceoprefix" id="bnsprof_ceoprefix" class="col-xs-12 col-sm-2">
        <?php
        $arr=array("Mr.","Ms.","Mrs.","Dr.");
		foreach($arr as $val)
		{
		?>
        <option value="<?php echo $val;?>" <?php if($val==$row->bnsprof_ceoprefix){ ?> selected="selected" <?php } ?> ><?php echo $val;?> </option>
        <?php } ?>
        </select>
        &nbsp;
        <input name="bnsprof_ceofname" id="bnsprof_ceofname" class="col-xs-12 col-sm-3" type="text" value="<?php echo $row->bnsprof_ceofname; ?>" />&nbsp;
        <input name="bnsprof_ceolname" id="bnsprof_ceolname" class="col-xs-12 col-sm-3" type="text" value="<?php echo $row->bnsprof_ceolname; ?>" />
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Username:</label>
		<div class="col-sm-9">
   		   	<label style="padding-top:4px;"><?php echo ucwords($row->name_prefix." ".$row->lname." ".$row->fname);?></label>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Address: <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
        	<textarea id="bnsprof_address1" name="bnsprof_address1" class="col-sm-6"><?php echo $row->bnsprof_address1; ?></textarea>
		</div>
        <label class="col-sm-3 control-label no-padding-right" for="form-field-2"></label>
       	<div class="col-sm-9" style="margin-top:2px;">
        	<textarea id="bnsprof_address2" name="bnsprof_address2" class="col-sm-6"><?php echo $row->bnsprof_address2; ?></textarea>
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">&nbsp;</label>
		<div class="col-sm-9">
        <select name="bnsprof_state" id="bnsprof_state" onchange="showCity(this.value);" title="State">
            <option value="0"> --- Select State --- </option>
            <?php
			$c=1;
            $sql_state="select * from states where state_cn_id='".$row->country."' and state_status='1'";
			$res_state=mysqli_query($con, $sql_state);
			while($row_state=mysqli_fetch_object($res_state))
			{
			?>
			<option value="<?php echo $row_state->state_id;?>" <?php if($row_state->state_id == $row->bnsprof_state){ ?> selected="selected" <?php } ?> ><?php echo $row_state->state_name;	?></option>
            <?php } ?>
		</select>
        
        <select name="bnsprof_city" id="bnsprof_city" title="City">
            <option value=""> --- Select City --- </option>
            <?php
			$c=1;
            $sql_ct="select * from city where ct_status='1'";
			$res_ct=mysqli_query($con, $sql_ct);
			while($row_ct=mysqli_fetch_object($res_ct))
			{
			?>
			<option value="<?php echo $row_ct->ct_id;?>" <?php if($row_ct->ct_id == $row->bnsprof_city){ ?> selected="selected" <?php } ?> ><?php echo $row_ct->ct_name;	?></option>
            <?php } ?>
		</select>
   		   	
		</div>
	</div>
    
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Postal / Zip Code:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_zipcode" id="bnsprof_zipcode" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_zipcode; ?>" />
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Country:</label>
		<div class="col-sm-9">
        <?php
			$sql_cn="select * from country where cn_id='".$row->country."' and cn_status='1'";
			$res_cn=mysqli_query($con, $sql_cn);
			if(mysqli_num_rows($res_cn)>0)
			{
				$row_cn=mysqli_fetch_object($res_cn);
		?>
        	<label style="padding-top:4px;"><?php echo $row_cn->cn_name; ?></label>
        <?php	}	?>
		</div>
	</div>
    
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Phone Number: <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
        	<input name="bnsprof_phcode1" id="bnsprof_phcode1" class="col-xs-12 col-sm-1" type="text" value="<?php echo $row->bnsprof_phcode1; ?>" />
           	<input name="bnsprof_ph1" id="bnsprof_ph1" class="col-xs-12 col-sm-2" type="text" value="<?php echo $row->bnsprof_ph1; ?>" />
        </div>
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2" ></label>
		<div class="col-sm-9" style="margin-top:2px;">
            <input name="bnsprof_phcode2" id="bnsprof_phcode2" class="col-xs-12 col-sm-1" type="text" value="<?php echo $row->bnsprof_phcode2; ?>" />
           	<input name="bnsprof_ph2" id="bnsprof_ph2" class="col-xs-12 col-sm-2" type="text" value="<?php echo $row->bnsprof_ph2; ?>" />
        </div>
   		<label class="col-sm-3 control-label no-padding-right" for="form-field-2"></label>
		<div class="col-sm-9" style="margin-top:2px;">
            <input name="bnsprof_phcode3" id="bnsprof_phcode3" class="col-xs-12 col-sm-1" type="text" value="<?php echo $row->bnsprof_phcode3; ?>" />
           	<input name="bnsprof_ph3" id="bnsprof_ph3" class="col-xs-12 col-sm-2" type="text" value="<?php echo $row->bnsprof_ph3; ?>" />
         </div>
   		<label class="col-sm-3 control-label no-padding-right" for="form-field-2"></label>
		<div class="col-sm-9" style="margin-top:2px;">
            <input name="bnsprof_phcode4" id="bnsprof_phcode4" class="col-xs-12 col-sm-1" type="text" value="<?php echo $row->bnsprof_phcode4; ?>" />
           	<input name="bnsprof_ph4" id="bnsprof_ph4" class="col-xs-12 col-sm-2" type="text" value="<?php echo $row->bnsprof_ph4; ?>" />
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Mobile/Cell Phone: <span style="color:#CC0000">*</span></label>
		<div class="col-sm-9">
        	<input name="" id="" class="col-xs-12 col-sm-1" type="text" value="<?php echo $row->country_ph_code; ?>" readonly="readonly" />
           	<input name="bnsprof_mobile2" id="bnsprof_mobile2" class="col-xs-12 col-sm-2" type="text" value="<?php echo $row->bnsprof_mobile2; ?>" />
        </div>
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2" ></label>
		<div class="col-sm-9" style="margin-top:2px;">
            <input name="" id="" class="col-xs-12 col-sm-1" type="text" value="<?php echo $row->country_ph_code; ?>" readonly="readonly" />
           	<input name="bnsprof_mobile3" id="bnsprof_mobile3" class="col-xs-12 col-sm-2" type="text" value="<?php echo $row->bnsprof_mobile3; ?>" />
        </div>
   		<label class="col-sm-3 control-label no-padding-right" for="form-field-2"></label>
		<div class="col-sm-9" style="margin-top:2px;">
            <input name="" id="" class="col-xs-12 col-sm-1" type="text" value="<?php echo $row->country_ph_code; ?>" readonly="readonly" />
           	<input name="bnsprof_mobile4" id="bnsprof_mobile4" class="col-xs-12 col-sm-2" type="text" value="<?php echo $row->bnsprof_mobile4; ?>" />
         </div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Fax Number:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_faxcode1" id="bnsprof_faxcode1" class="col-xs-12 col-sm-1" type="text" value="<?php echo $row->bnsprof_faxcode1; ?>" />
            <input name="bnsprof_fax1" id="bnsprof_fax1" class="col-xs-12 col-sm-2" type="text" value="<?php echo $row->bnsprof_fax1; ?>" />
        </div>
   		<label class="col-sm-3 control-label no-padding-right" for="form-field-2"></label>
		<div class="col-sm-9" style="padding-top:2px;">
	        <input name="bnsprof_faxcode2" id="bnsprof_faxcode2" class="col-xs-12 col-sm-1" type="text" value="<?php echo $row->bnsprof_faxcode2; ?>" />
            <input name="bnsprof_fax2" id="bnsprof_fax2" class="col-xs-12 col-sm-2" type="text" value="<?php echo $row->bnsprof_fax2; ?>" />
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Email:</label>
		<div class="col-sm-9" style="padding-bottom:2px;">
	        <input name="bnsprof_emailalt1" id="bnsprof_emailalt1" class="col-xs-12 col-sm-5" type="text" value="<?php echo $row->bnsprof_emailalt1; ?>" />
        </div>
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2"></label>
		<div class="col-sm-9" style="padding-bottom:2px;">
   	        <input name="bnsprof_emailalt2" id="bnsprof_emailalt2" class="col-xs-12 col-sm-5" type="text" value="<?php echo $row->bnsprof_emailalt2; ?>" />
        </div>
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2"></label>
		<div class="col-sm-9">
   	        <input name="bnsprof_emailalt3" id="bnsprof_emailalt3" class="col-xs-12 col-sm-5" type="text" value="<?php echo $row->bnsprof_emailalt3; ?>" />
		</div>
	</div>
        
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Website:</label>
		<div class="col-sm-9">
   	        <input name="bnsprof_website_alt" id="bnsprof_website_alt" class="col-xs-12 col-sm-5" type="text" value="<?php echo $row->bnsprof_website_alt; ?>" />
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Business Type:</label>
		<div class="col-sm-8">
        <?php
			$c=1;
			$bstyp = explode(',',$row->bnsprof_businesstype); 
			
            $bstypsql=mysqli_query($con, "select * from business_type where bsntyp_status='1'");
			while($bstyprow=mysqli_fetch_object($bstypsql)){
			
			?>
            <label class="col-sm-4">
            <input name="bnsprof_businesstype[]" id="bnsprof_businesstype" class="ace ace-checkbox-2" value="<?php echo $bstyprow->bsntyp_id; ?>" type="checkbox" <?php if(in_array($bstyprow->bsntyp_id,$bstyp)){ ?> checked="checked" <?php } ?> ><span class="lbl"><?php echo $bstyprow->bsntyp_title; ?></span>
			</label>
             <?php  $c++;}	?>
		</div>
	</div>
        
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Year of Establishment:</label>
		<div class="col-sm-9">
        	<input name="bnsprof_yoe" id="bnsprof_yoe" class="col-xs-10 col-sm-2" type="text" value="<?php echo $row->bnsprof_yoe; ?>" />
		</div>
	</div>
    
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">No of Employees:</label>
		<div class="col-sm-9">
        <select class="col-sm-5" name="bnsprof_comemp" id="bnsprof_comemp">
            <option value="">---Choose One---</option>
            <?php
			$c=1;
            $noempsql=mysqli_query($con, "select * from employee_range where emprange_status='1'");
			while($noemprow=mysqli_fetch_object($noempsql))
			{
			?>
 <option value="<?php echo $noemprow->emprange_id;?>" <?php if($row->bnsprof_comemp == $noemprow->emprange_id){ ?> selected="selected" <?php } ?> >
			<?php echo $noemprow->emprange_type;?></option>
            <?php } ?>
            </select>
   		   	
		</div>
	</div>
       
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Revenue Sales Turnover:</label>
		<div class="col-sm-9">
        <select class="col-sm-5" name="bnsprof_turnover" id="bnsprof_turnover">
            <option value="">---Choose One---</option>
            <?php
			$c=1;
            $turnoversql=mysqli_query($con, "select * from revenue_turnover where revturnover_status='1'");
			while($turnoverow=mysqli_fetch_object($turnoversql))
			{
			?>
            <option value="<?php echo $turnoverow->revturnover_id;?>" <?php if($row->bnsprof_turnover == $turnoverow->revturnover_id){ ?> selected="selected" <?php } ?>>
			<?php echo $turnoverow->revturnover_title;?></option>
            <?php } ?>
            </select>
   		   	
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Registration No.:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_regno" id="bnsprof_regno" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_regno; ?>" />
		</div>
	</div>
                 
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Registration Authority:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_regauthority" id="bnsprof_regauthority" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_regauthority; ?>" />
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">CIN No.:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_cin_no" id="bnsprof_cin_no" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_cin_no; ?>" />
		</div>
	</div>

    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">TAN No.:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_tan_no" id="bnsprof_tan_no" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_tan_no; ?>" />
		</div>
	</div>

    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">PAN No.:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_pan_no" id="bnsprof_pan_no" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_pan_no; ?>" />
		</div>
	</div>      

    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Service Tax No.:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_svtax_no" id="bnsprof_svtax_no" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_svtax_no; ?>" />
		</div>
	</div>      

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Excise Reg. No.:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_excisereg_no" id="bnsprof_excisereg_no" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_excisereg_no; ?>" />
		</div>
	</div>      

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">TIN No. / VAT No.:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_vat_no" id="bnsprof_vat_no" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_vat_no; ?>" />
		</div>
	</div>
    
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">TDGFT/IE Code:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_ie_code" id="bnsprof_ie_code" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_ie_code; ?>" />
		</div>
	</div>      

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">CST No.:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_cst_no" id="bnsprof_cst_no" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_cst_no; ?>" />
		</div>
	</div>      

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">SSI No. / MSME No.:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_msme_no" id="bnsprof_msme_no" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_msme_no; ?>" />
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">EPF No.:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_epf_no" id="bnsprof_epf_no" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_epf_no; ?>" />
		</div>
	</div>
        
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">ESI No.:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_esi_no" id="bnsprof_esi_no" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_esi_no; ?>" />
		</div>
	</div>
        
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">SCT No.:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_sct_no" id="bnsprof_sct_no" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_sct_no; ?>" />
		</div>
	</div>
        
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">DNB No.:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_dnb_no" id="bnsprof_dnb_no" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_dnb_no; ?>" />
		</div>
	</div>
        
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">RBI No.:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_rbi_no" id="bnsprof_rbi_no" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_rbi_no; ?>" />
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">FSSAI-LICENSE No.:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_fssailic_no" id="bnsprof_fssailic_no" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_fssailic_no; ?>" />
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">N.S.I.C No.:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_nsic_no" id="bnsprof_nsic_no" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_nsic_no; ?>" />
		</div>
	</div>
          
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">S.S.T No.:</label>
		<div class="col-sm-9">
	        <input name="bnsprof_sst_no" id="bnsprof_sst_no" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->bnsprof_sst_no; ?>" />
		</div>
	</div>

	<?php if($row->bnsprof_complogo!=''){ ?>
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Company Logo:</label>
		<div class="col-sm-9">
			<img src="../upload/companylogo/<?php echo $row->bnsprof_complogo;?>" width="200px" height="232px"/>
		</div>
	</div>
	<?php } ?>
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">New Company Logo:</label>
		<div class="col-sm-9">
			<div class="ace-file-input" style="width:400px;"><input name="bnsprof_complogo" id="bnsprof_complogo" type="file"/></div>
		</div>
	</div>
    
    <div class="clearfix form-actions">
		<div class="col-md-offset-3 col-md-9">
	        <button class="btn btn-info" type="submit" name="btnUpdate" id="btnUpdate"><i class="icon-ok bigger-110"></i>Update</button>
			            
		</div>
	</div>
 
</form>    
 			</div>		<br clear="all"/>
		</div>
			
	</div>
	<br clear="all" />	
<?php include "includes/footer.php" ?>
</body>
		<script type="text/javascript">
			window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
		</script>

		<!-- <![endif]-->

		<!--[if IE]>
<script type="text/javascript">
 window.jQuery || document.write("<script src='assets/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
</script>
<![endif]-->

		<script type="text/javascript">
			if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
		</script>
		<script src="assets/js/bootstrap.min.js"></script>
		<script src="assets/js/typeahead-bs2.min.js"></script>

		<!-- page specific plugin scripts -->

		<!--[if lte IE 8]>
		  <script src="assets/js/excanvas.min.js"></script>
		<![endif]-->

		<script src="assets/js/jquery-ui-1.10.3.custom.min.js"></script>
		<script src="assets/js/jquery.ui.touch-punch.min.js"></script>
		<script src="assets/js/chosen.jquery.min.js"></script>
		<script src="assets/js/fuelux/fuelux.spinner.min.js"></script>
		<script src="assets/js/date-time/bootstrap-datepicker.min.js"></script>
		<script src="assets/js/date-time/bootstrap-timepicker.min.js"></script>
		<script src="assets/js/date-time/moment.min.js"></script>
		<script src="assets/js/date-time/daterangepicker.min.js"></script>
		<script src="assets/js/bootstrap-colorpicker.min.js"></script>
		<script src="assets/js/jquery.knob.min.js"></script>
		<script src="assets/js/jquery.autosize.min.js"></script>
		<script src="assets/js/jquery.inputlimiter.1.3.1.min.js"></script>
		<script src="assets/js/jquery.maskedinput.min.js"></script>
		<script src="assets/js/bootstrap-tag.min.js"></script>

		<!-- ace scripts -->

		<script src="assets/js/ace-elements.min.js"></script>
		<script src="assets/js/ace.min.js"></script>

		<!-- inline scripts related to this page -->

		<script type="text/javascript">
			jQuery(function($) {
				$('#id-disable-check').on('click', function() {
					var inp = $('#form-input-readonly').get(0);
					if(inp.hasAttribute('disabled')) {
						inp.setAttribute('readonly' , 'true');
						inp.removeAttribute('disabled');
						inp.value="This text field is readonly!";
					}
					else {
						inp.setAttribute('disabled' , 'disabled');
						inp.removeAttribute('readonly');
						inp.value="This text field is disabled!";
					}
				});
			
			
				$(".chosen-select").chosen(); 
				$('#chosen-multiple-style').on('click', function(e){
					var target = $(e.target).find('input[type=radio]');
					var which = parseInt(target.val());
					if(which == 2) $('#form-field-select-4').addClass('tag-input-style');
					 else $('#form-field-select-4').removeClass('tag-input-style');
				});
			
			
				$('[data-rel=tooltip]').tooltip({container:'body'});
				$('[data-rel=popover]').popover({container:'body'});
				
				$('textarea[class*=autosize]').autosize({append: "\n"});
				$('textarea.limited').inputlimiter({
					remText: '%n character%s remaining...',
					limitText: 'max allowed : %n.'
				});
			
				$.mask.definitions['~']='[+-]';
				$('.input-mask-date').mask('99/99/9999');
				$('.input-mask-phone').mask('(999) 999-9999');
				$('.input-mask-eyescript').mask('~9.99 ~9.99 999');
				$(".input-mask-product").mask("a*-999-a999",{placeholder:" ",completed:function(){alert("You typed the following: "+this.val());}});
			
			
			
				$( "#input-size-slider" ).css('width','200px').slider({
					value:1,
					range: "min",
					min: 1,
					max: 8,
					step: 1,
					slide: function( event, ui ) {
						var sizing = ['', 'input-sm', 'input-lg', 'input-mini', 'input-small', 'input-medium', 'input-large', 'input-xlarge', 'input-xxlarge'];
						var val = parseInt(ui.value);
						$('#form-field-4').attr('class', sizing[val]).val('.'+sizing[val]);
					}
				});
			
				$( "#input-span-slider" ).slider({
					value:1,
					range: "min",
					min: 1,
					max: 12,
					step: 1,
					slide: function( event, ui ) {
						var val = parseInt(ui.value);
						$('#form-field-5').attr('class', 'col-xs-'+val).val('.col-xs-'+val);
					}
				});
				
				
				$( "#slider-range" ).css('height','200px').slider({
					orientation: "vertical",
					range: true,
					min: 0,
					max: 100,
					values: [ 17, 67 ],
					slide: function( event, ui ) {
						var val = ui.values[$(ui.handle).index()-1]+"";
			
						if(! ui.handle.firstChild ) {
							$(ui.handle).append("<div class='tooltip right in' style='display:none;left:16px;top:-6px;'><div class='tooltip-arrow'></div><div class='tooltip-inner'></div></div>");
						}
						$(ui.handle.firstChild).show().children().eq(1).text(val);
					}
				}).find('a').on('blur', function(){
					$(this.firstChild).hide();
				});
				
				$( "#slider-range-max" ).slider({
					range: "max",
					min: 1,
					max: 10,
					value: 2
				});
				
				$( "#eq > span" ).css({width:'90%', 'float':'left', margin:'15px'}).each(function() {
					// read initial values from markup and remove that
					var value = parseInt( $( this ).text(), 10 );
					$( this ).empty().slider({
						value: value,
						range: "min",
						animate: true
						
					});
				});
			
				
				$('#id-input-file-1 , #id-input-file-2, #bnsprof_complogo').ace_file_input({
					no_file:'No File ...',
					btn_choose:'Choose',
					btn_change:'Change',
					droppable:false,
					onchange:null,
					thumbnail:false //| true | large
					//whitelist:'gif|png|jpg|jpeg'
					//blacklist:'exe|php'
					//onchange:''
					//
				});
				
				$('#id-input-file-3').ace_file_input({
					style:'well',
					btn_choose:'Drop files here or click to choose',
					btn_change:null,
					no_icon:'icon-cloud-upload',
					droppable:true,
					thumbnail:'small'//large | fit
					//,icon_remove:null//set null, to hide remove/reset button
					/**,before_change:function(files, dropped) {
						//Check an example below
						//or examples/file-upload.html
						return true;
					}*/
					/**,before_remove : function() {
						return true;
					}*/
					,
					preview_error : function(filename, error_code) {
						//name of the file that failed
						//error_code values
						//1 = 'FILE_LOAD_FAILED',
						//2 = 'IMAGE_LOAD_FAILED',
						//3 = 'THUMBNAIL_FAILED'
						//alert(error_code);
					}
			
				}).on('change', function(){
					//console.log($(this).data('ace_input_files'));
					//console.log($(this).data('ace_input_method'));
				});
				
			
				//dynamically change allowed formats by changing before_change callback function
				$('#id-file-format').removeAttr('checked').on('change', function() {
					var before_change
					var btn_choose
					var no_icon
					if(this.checked) {
						btn_choose = "Drop images here or click to choose";
						no_icon = "icon-picture";
						before_change = function(files, dropped) {
							var allowed_files = [];
							for(var i = 0 ; i < files.length; i++) {
								var file = files[i];
								if(typeof file === "string") {
									//IE8 and browsers that don't support File Object
									if(! (/\.(jpe?g|png|gif|bmp)$/i).test(file) ) return false;
								}
								else {
									var type = $.trim(file.type);
									if( ( type.length > 0 && ! (/^image\/(jpe?g|png|gif|bmp)$/i).test(type) )
											|| ( type.length == 0 && ! (/\.(jpe?g|png|gif|bmp)$/i).test(file.name) )//for android's default browser which gives an empty string for file.type
										) continue;//not an image so don't keep this file
								}
								
								allowed_files.push(file);
							}
							if(allowed_files.length == 0) return false;
			
							return allowed_files;
						}
					}
					else {
						btn_choose = "Drop files here or click to choose";
						no_icon = "icon-cloud-upload";
						before_change = function(files, dropped) {
							return files;
						}
					}
					var file_input = $('#id-input-file-3');
					file_input.ace_file_input('update_settings', {'before_change':before_change, 'btn_choose': btn_choose, 'no_icon':no_icon})
					file_input.ace_file_input('reset_input');
				});
			
			
			
			
				$('#spinner1').ace_spinner({value:0,min:0,max:200,step:10, btn_up_class:'btn-info' , btn_down_class:'btn-info'})
				.on('change', function(){
					//alert(this.value)
				});
				$('#spinner2').ace_spinner({value:0,min:0,max:10000,step:100, touch_spinner: true, icon_up:'icon-caret-up', icon_down:'icon-caret-down'});
				$('#spinner3').ace_spinner({value:0,min:-100,max:100,step:10, on_sides: true, icon_up:'icon-plus smaller-75', icon_down:'icon-minus smaller-75', btn_up_class:'btn-success' , btn_down_class:'btn-danger'});
			
			
				
				$('.date-picker').datepicker({autoclose:true}).next().on(ace.click_event, function(){
					$(this).prev().focus();
				});
				$('input[name=date-range-picker]').daterangepicker().prev().on(ace.click_event, function(){
					$(this).next().focus();
				});
				
				$('#timepicker1').timepicker({
					minuteStep: 1,
					showSeconds: true,
					showMeridian: false
				}).next().on(ace.click_event, function(){
					$(this).prev().focus();
				});
				
				$('#colorpicker1').colorpicker();
				$('#simple-colorpicker-1').ace_colorpicker();
			
				
				$(".knob").knob();
				
				
				//we could just set the data-provide="tag" of the element inside HTML, but IE8 fails!
				var tag_input = $('#form-field-tags');
				if(! ( /msie\s*(8|7|6)/.test(navigator.userAgent.toLowerCase())) ) 
				{
					tag_input.tag(
					  {
						placeholder:tag_input.attr('placeholder'),
						//enable typeahead by specifying the source array
						source: ace.variable_US_STATES,//defined in ace.js >> ace.enable_search_ahead
					  }
					);
				}
				else {
					//display a textarea for old IE, because it doesn't support this plugin or another one I tried!
					tag_input.after('<textarea id="'+tag_input.attr('id')+'" name="'+tag_input.attr('name')+'" rows="3">'+tag_input.val()+'</textarea>').remove();
					//$('#form-field-tags').autosize({append: "\n"});
				}
				
				
				
			
				/////////
				$('#modal-form input[type=file]').ace_file_input({
					style:'well',
					btn_choose:'Drop files here or click to choose',
					btn_change:null,
					no_icon:'icon-cloud-upload',
					droppable:true,
					thumbnail:'large'
				})
				
				//chosen plugin inside a modal will have a zero width because the select element is originally hidden
				//and its width cannot be determined.
				//so we set the width after modal is show
				$('#modal-form').on('shown.bs.modal', function () {
					$(this).find('.chosen-container').each(function(){
						$(this).find('a:first-child').css('width' , '210px');
						$(this).find('.chosen-drop').css('width' , '210px');
						$(this).find('.chosen-search input').css('width' , '200px');
					});
				})
				/**
				//or you can activate the chosen plugin after modal is shown
				//this way select element becomes visible with dimensions and chosen works as expected
				$('#modal-form').on('shown', function () {
					$(this).find('.modal-chosen').chosen();
				})
				*/
			
			});
		</script>
	</body>
</html>