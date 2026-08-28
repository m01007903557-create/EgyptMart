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
function validPersonal()
{
	var emp_firstName = document.getElementById('emp_firstName');
	var emp_lastName = document.getElementById('emp_lastName');
	var emp_gender = document.getElementById('emp_gender');
	var emp_maritalStatus = document.getElementById('emp_maritalStatus');
	var emp_cn_id = document.getElementById('emp_cn_id');
	var emp_dateOfBirth = document.getElementById('emp_dateOfBirth');

	var msg="";
	var valid=true;	

	if (emp_firstName.value == "" || emp_firstName.value == null)
    {
		msg='Please enter first name';
		emp_firstName.value="";
        emp_firstName.focus();
        valid = false;		
    }
	else if (!allLetter(emp_firstName.value))
    {
		msg='Please enter valid first name';
		emp_firstName.value="";
        emp_firstName.focus();
        valid = false;		
    }
	else if (emp_lastName.value == "" || emp_lastName.value == null)
    {
		msg='Please select pay frequency';
		emp_lastName.value="";
        emp_lastName.focus();
        valid = false;		
    }
	else if (!allLetter(emp_lastName.value))
    {
		msg='Please enter valid last name';
		emp_lastName.value="";
        emp_lastName.focus();
        valid = false;		
    }
	else if (emp_gender.value == "" || emp_gender.value == null)
    {
		msg='Please select your gender';
		emp_gender.value="";
        emp_gender.focus();
        valid = false;		
    }
	else if (emp_maritalStatus.value == "" || emp_maritalStatus.value == null)
    {
		msg='Please select marital status';
		emp_maritalStatus.value="";
        emp_maritalStatus.focus();
        valid = false;		
    }
	else if (emp_cn_id.value == "" || emp_cn_id.value == null)
    {
		msg='Please select country';
		emp_cn_id.value="";
        emp_cn_id.focus();
        valid = false;		
    }
	else if (emp_dateOfBirth.value == "" || emp_dateOfBirth.value == null || emp_dateOfBirth.value == "0000-00-00")
    {
		msg='Please enter date of birth';
		emp_dateOfBirth.value="";
        emp_dateOfBirth.focus();
        valid = false;		
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