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
	var eec_name = document.getElementById('eec_name');
	var eec_relationship = document.getElementById('eec_relationship');
	var eec_homePhone = document.getElementById('eec_homePhone');
	var eec_mobilePhone = document.getElementById('eec_mobilePhone');
	var eec_workPhone = document.getElementById('eec_workPhone');

	var msg="";
	var valid=true;	
	
	
	if (eec_name.value == '' || eec_name.value == null)
    {
		msg='Please enter name';
		eec_name.value="";
		eec_name.focus();
		valid = false;		
    }
	else if (!allLetter(eec_name.value))
    {
		msg='Please enter valid name';
		eec_name.value="";
        eec_name.focus();
        valid = false;		
    }
	else if (eec_relationship.value == '' || eec_relationship.value == null)
    {
		msg='Please enter relationship';
		eec_relationship.value="";
		eec_relationship.focus();
		valid = false;		
    }
	else if (!allLetter(eec_relationship.value))
    {
		msg='Please enter valid relationship';
		eec_relationship.value="";
        eec_relationship.focus();
        valid = false;		
    }
	else if(eec_homePhone.value == "" && eec_mobilePhone.value == "" && eec_workPhone.value == "")
	{
		msg= 'Please enter atleast one phone number';
        eec_homePhone.focus();
        valid = false;		
	}
	else if ((empc_homePhone.value != "" || empc_homePhone.value != null) && isNaN(empc_homePhone.value))
	{
		msg= 'Please enter valid phone number';
		eec_homePhone.value="";
        eec_homePhone.focus();
		valid=false;
	}
	else if ((empc_mobilePhone.value != "" || empc_mobilePhone.value != null) && isNaN(empc_mobilePhone.value))
	{
		msg= 'Please enter valid phone number';
		eec_mobilePhone.value="";
        eec_mobilePhone.focus();
		valid=false;
	}
	else if ((empc_workPhone.value != "" || empc_workPhone.value != null) && isNaN(empc_workPhone.value))
	{
		msg= 'Please enter valid phone number';
		eec_workPhone.value="";
        eec_workPhone.focus();
		valid=false;
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