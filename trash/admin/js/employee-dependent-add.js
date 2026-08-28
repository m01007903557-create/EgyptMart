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
function validDependent()
{
	var ed_name = document.getElementById('ed_name');
	var ed_relationship = document.getElementById('ed_relationship');
	var ed_dateOfBirth = document.getElementById('ed_dateOfBirth');

	dateOfBirth=new Date(ed_dateOfBirth.value);
	var db=dateOfBirth.getDate();
	var mb = dateOfBirth.getMonth()+1;
	var yb = dateOfBirth.getFullYear();	
	
	
	var today = new Date();
	var d = today.getDate();
	var m = today.getMonth()+1;
	var y = today.getFullYear();
	
	var msg="";
	var valid=true;	
	
	if (ed_name.value == '' || ed_name.value == null)
    {
		msg='Please enter name';
		ed_name.value="";
		ed_name.focus();
		valid = false;		
    }
	else if (!allLetter(ed_name.value))
    {
		msg='Please enter valid name';
		ed_name.value="";
        ed_name.focus();
        valid = false;		
    }
	else if (ed_relationship.value == '' || ed_relationship.value == null)
    {
		msg='Please enter relationship';
		ed_relationship.value="";
		ed_relationship.focus();
		valid = false;		
    }
	else if (!allLetter(ed_relationship.value))
    {
		msg='Please enter valid relationship';
		ed_relationship.value="";
        ed_relationship.focus();
        valid = false;		
    }
	else if (ed_dateOfBirth.value == '' || ed_dateOfBirth.value == null)
    {
		msg='Please enter date of birth';
		ed_dateOfBirth.value="";
		ed_dateOfBirth.focus();
		valid = false;		
    }
	else if(y<yb)
	{
		msg= 'Please enter valid date of birth';
		ed_dateOfBirth.value="";
        ed_dateOfBirth.focus();
        valid = false;		
	}
	else if(y==yb && m<mb)
	{
		msg= 'Please enter valid date of birth';
		ed_dateOfBirth.value="";
        ed_dateOfBirth.focus();
        valid = false;		
	}
	else if(y==yb && m==mb && d<db)
	{
		msg= 'Please enter valid date of birth';
		ed_dateOfBirth.value="";
        ed_dateOfBirth.focus();
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