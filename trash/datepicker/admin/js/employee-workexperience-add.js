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
function validExperience()
{
	var ew_company = document.getElementById('ew_company');
	var ew_jobTitle = document.getElementById('ew_jobTitle');
	var ew_fromDate = document.getElementById('ew_fromDate');
	var ew_toDate = document.getElementById('ew_toDate');

	fromdate=new Date(ew_fromDate.value);
	var df=fromdate.getDate();
	var mf = fromdate.getMonth()+1;
	var yf = fromdate.getFullYear();	
	
	todate=new Date(ew_toDate.value);
	var dt=todate.getDate();
	var mt = todate.getMonth()+1;
	var yt = todate.getFullYear();		
	
	var today = new Date();
	var d = today.getDate();
	var m = today.getMonth()+1;
	var y = today.getFullYear();
	
	var msg="";
	var valid=true;	
	
	if (ew_company.value == '' || ew_company.value == null)
    {
		msg='Please enter company name';
		ew_company.value="";
		ew_company.focus();
		valid = false;		
    }
	else if (ew_jobTitle.value == '' || ew_jobTitle.value == null)
    {
		msg='Please enter job title';
		ew_jobTitle.value="";
		ew_jobTitle.focus();
		valid = false;		
    }
	else if (!allLetter(ew_jobTitle.value))
    {
		msg='Please enter valid job title';
		ew_jobTitle.value="";
		ew_jobTitle.focus();
		valid = false;		
    }
	else if (ew_fromDate.value == '' || ew_fromDate.value == null)
    {
		msg='Please enter joining date';
		ew_fromDate.value="";
		ew_fromDate.focus();
		valid = false;		
    }
	else if(y==yf && m==mf && d<df)
	{
		msg='Please enter valid joining date';
		ew_fromDate.value="";
        ew_fromDate.focus();
        valid = false;		
	}
	else if(y==yf && m<mf)
	{
		msg='Please enter valid joining date';
		ew_fromDate.value="";
        ew_fromDate.focus();
        valid = false;		
	}
	else if(y<yf)
	{
		msg='Please enter valid joining date';
		ew_fromDate.value="";
        ew_fromDate.focus();
        valid = false;		
	}
	
	
	else if (ew_toDate.value == '' || ew_toDate.value == null)
    {
		msg='Please enter termination date';
		ew_toDate.value="";
		ew_toDate.focus();
		valid = false;		
    }
	else if(yt==yf && mt==mf && dt<df)
	{
		msg='Please enter valid termination date';
		ew_toDate.value="";
        ew_toDate.focus();
        valid = false;		
	}
	else if(yt==yf && mt<mf)
	{
		msg='Please enter valid termination date';
		ew_toDate.value="";
        ew_toDate.focus();
        valid = false;		
	}
	else if(yt<yf)
	{
		msg='Please enter valid termination date';
		ew_toDate.value="";
        ew_toDate.focus();
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