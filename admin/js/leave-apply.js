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
function validLeaveApply()
{
	var la_lt_id = document.getElementById('la_lt_id');
	var la_from_date = document.getElementById('la_from_date');
	var la_to_date = document.getElementById('la_to_date');

	fromdate=new Date(la_from_date.value);
	var df=fromdate.getDate();
	var mf = fromdate.getMonth()+1;
	var yf = fromdate.getFullYear();	
	
	todate=new Date(la_to_date.value);
	var dt=todate.getDate();
	var mt = todate.getMonth()+1;
	var yt = todate.getFullYear();		
	
	var today = new Date();
	var d = today.getDate();
	var m = today.getMonth()+1;
	var y = today.getFullYear();
	
	var msg="";
	var valid=true;	
	
	if (la_lt_id.value == '')
    {
		msg='Please select Leave Type';
		la_lt_id.value="";
		la_lt_id.focus();
		valid = false;		
    }
	else if (la_from_date.value == '' || la_from_date.value == null)
    {
		msg='Please enter leave starting date';
		la_from_date.value="";
		la_from_date.focus();
		valid = false;		
    }
	else if(y>yf)
	{
		msg= 'Please enter valid leave starting date';
		la_from_date.value="";
        la_from_date.focus();
        valid = false;		
	}
	else if(y==yf && m>mf)
	{
		msg= 'Please enter valid leave starting date';
		la_from_date.value="";
        la_from_date.focus();
        valid = false;		
	}
	else if(y==yf && m==mf && d>df)
	{
		msg= 'Please enter valid leave starting date';
		la_from_date.value="";
        la_from_date.focus();
        valid = false;		
	}
	else if (la_to_date.value == '' || la_to_date.value == null)
    {
		msg='Please enter leave ending date';
		la_to_date.value="";
		la_to_date.focus();
		valid = false;		
    }
	else if(yt<yf)
	{
		msg= 'Please enter valid leave ending date';
		la_to_date.value="";
        la_to_date.focus();
        valid = false;		
	}
	else if(yt==yf && mt<mf)
	{
		msg= 'Please enter valid leave ending date';
		la_to_date.value="";
        la_to_date.focus();
        valid = false;		
	}
	else if(yt==yf && mt==mf && dt<df)
	{
		msg= 'Please enter valid leave ending date';
		la_to_date.value="";
        la_to_date.focus();
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