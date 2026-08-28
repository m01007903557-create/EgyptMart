// JavaScript Document
// JavaScript Document
function allLetter(name)  
{   
	var letters = /^[A-Za-z]+$/;  
	if(name.match(letters))  
	{  
		return true;  
	}  
	else  
	{  
		return false;  
	}  
} 
function validContact()
{

	var empc_city = document.getElementById('empc_city');
	var empc_state = document.getElementById('empc_state');
	var empc_zip = document.getElementById('empc_zip');
	var empc_homePhone = document.getElementById('empc_homePhone');
	var empc_mobilePhone = document.getElementById('empc_mobilePhone');
	var empc_workPhone = document.getElementById('empc_workPhone');
	var empc_workEmail = document.getElementById('empc_workEmail');
	var empc_otherEmail = document.getElementById('empc_otherEmail');
	
	var at = "@";
	var dot = ".";
	var lat = empc_workEmail.value.indexOf(at);
	var lstr = empc_workEmail.value.length;
	var ldot = empc_workEmail.value.indexOf(dot);
	
	var lat2 = empc_otherEmail.value.indexOf(at);
	var lstr2 = empc_otherEmail.value.length;
	var ldot2 = empc_otherEmail.value.indexOf(dot);


	var msg="";
	var valid=true;	
	
	
	if (empc_city.value != '' || empc_city.value != null)
    {
		if(!allLetter(empc_city.value))
		{
			msg='Please enter valid city name';
			empc_city.value="";
			empc_city.focus();
			valid = false;		
		}
    }
	else if ((empc_state.value != "" || empc_state.value != null) && !allLetter(empc_state.value))
    {
		msg='Please enter valid state';
		empc_state.value="";
        empc_state.focus();
        valid = false;		
    }
	else if ((empc_zip.value != "" || empc_zip.value != null) && isNaN(empc_zip.value) && empc_zip.value.length>6)
    {
		msg='Please enter valid zip code';
		empc_zip.value="";
        empc_zip.focus();
        valid = false;		
    }	
	else if ((empc_homePhone.value != "" || empc_homePhone.value != null) && isNaN(empc_homePhone.value))
    {
		msg='Please enter valid phone number of home';
		empc_homePhone.value="";
        empc_homePhone.focus();
        valid = false;		
    }
	else if ((empc_mobilePhone.value != "" || empc_mobilePhone.value != null) && isNaN(empc_mobilePhone.value))
    {
		msg='Please enter valid mobile number';
		empc_mobilePhone.value="";
        empc_mobilePhone.focus();
        valid = false;		
    }  
	else if ((empc_workPhone.value != "" || empc_workPhone.value != null) && isNaN(empc_workPhone.value))
    {
		msg='Please enter valid work phone number';
		empc_workPhone.value="";
        empc_workPhone.focus();
        valid = false;		
    }  
	
	else if (empc_workEmail.value != "" || empc_workEmail.value != null)
    {

		// check if '@' is at the first position or at last position or absent in given email 
		if (empc_workEmail.value.indexOf(at) == -1 || empc_workEmail.value.indexOf(at) == 0 || empc_workEmail.value.indexOf(at) == lstr)
		{	
			msg="Please enter valid email address";
			empc_workEmail.value="";
			empc_workEmail.focus();
			valid = false;	
				
		}
		// check if '.' is at the first position or at last position or absent in given email
		else if (empc_workEmail.value.indexOf(dot) == -1 || empc_workEmail.value.indexOf(dot) == 0 || empc_workEmail.value.indexOf(dot) == lstr)
		{
			msg="Please enter valid email address";
			empc_workEmail.value="";
			empc_workEmail.focus();
			valid = false;
			
		}
		// check if '@' is used more than one times in given email
		else if (empc_workEmail.value.indexOf(at,(lat+1)) != -1)
		{
			msg="Please enter valid email address";
			empc_workEmail.value="";
			empc_workEmail.focus();
			valid = false;	
		}  
		// check for the position of '.'
		else if (empc_workEmail.value.substring(lat-1,lat) == dot || empc_workEmail.value.substring(lat+1,lat+2) == dot)
		{
			msg="Please enter valid email address";
			empc_workEmail.value="";
			empc_workEmail.focus();
			valid = false;	
		}
		// check if '.' is present after two characters from location of '@'
		else if (empc_workEmail.value.indexOf(dot,(lat+2)) == -1)
		{
			msg="Please enter valid email address";
			empc_workEmail.value="";
			empc_workEmail.focus();
			valid = false;	
		}	
		// check for blank spaces in given email
		else if (empc_workEmail.value.indexOf(" ") != -1)
		{	
			msg="Please enter valid email address";
			empc_workEmail.value="";
			empc_workEmail.focus();
			valid = false;	
		}	
	}
	else if (empc_otherEmail.value != "" || empc_otherEmail.value != null)
    {

		// check if '@' is at the first position or at last position or absent in given email 
		if (empc_otherEmail.value.indexOf(at) == -1 || empc_otherEmail.value.indexOf(at) == 0 || empc_otherEmail.value.indexOf(at) == lstr)
		{	
			msg="Please enter valid email address";
			empc_otherEmail.value="";
			empc_otherEmail.focus();
			valid = false;	
				
		}
		// check if '.' is at the first position or at last position or absent in given email
		else if (empc_otherEmail.value.indexOf(dot) == -1 || empc_otherEmail.value.indexOf(dot) == 0 || empc_otherEmail.value.indexOf(dot) == lstr)
		{
			msg="Please enter valid email address";
			empc_otherEmail.value="";
			empc_otherEmail.focus();
			valid = false;
			
		}
		// check if '@' is used more than one times in given email
		else if (empc_otherEmail.value.indexOf(at,(lat2+1)) != -1)
		{
			msg="Please enter valid email address";
			empc_otherEmail.value="";
			empc_otherEmail.focus();
			valid = false;	
		}  
		// check for the position of '.'
		else if (empc_otherEmail.value.substring(lat2-1,lat2) == dot || empc_otherEmail.value.substring(lat2+1,lat2+2) == dot)
		{
			msg="Please enter valid email address";
			empc_otherEmail.value="";
			empc_otherEmail.focus();
			valid = false;	
		}
		// check if '.' is present after two characters from location of '@'
		else if (empc_otherEmail.value.indexOf(dot,(lat2+2)) == -1)
		{
			msg="Please enter valid email address";
			empc_otherEmail.value="";
			empc_otherEmail.focus();
			valid = false;	
		}	
		// check for blank spaces in given email
		else if (empc_otherEmail.value.indexOf(" ") != -1)
		{	
			msg="Please enter valid email address";
			empc_otherEmail.value="";
			empc_otherEmail.focus();
			valid = false;	
		}	
	}
	else
	{		
		valid=true;
	}	
	
	if(!valid)
	{
		document.getElementById("msg").style.color = "red";
		document.getElementById('msg').innerHTML = msg;			 				
	}
    return valid;

}