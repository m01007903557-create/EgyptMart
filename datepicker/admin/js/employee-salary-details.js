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
function validSalary()
{
	
	var $es_pg_id;
	var $es_salComponent;
	var $es_payFrequency;
	var $es_amount;
	
	var es_pg_id = document.getElementById('es_pg_id');
	var es_salComponent = document.getElementById('es_salComponent');
	var es_payFrequency = document.getElementById('es_payFrequency');
	var es_amount = document.getElementById('es_amount');

	var msg="";
	var valid=true;	
	
	if (es_pg_id.value == '' || es_pg_id.value == null)
    {
		msg='Please select Pay Grade';
		es_pg_id.value="";
		es_pg_id.focus();
		valid = false;		
    }
	else if (es_salComponent.value == "" || es_salComponent.value == null)
    {
		msg='Please enter salary component';
		es_salComponent.value="";
        es_salComponent.focus();
        valid = false;		
    }
	else if (es_payFrequency.value == "" || es_payFrequency.value == null)
    {
		msg='Please select pay frequency';
		es_payFrequency.value="";
        es_payFrequency.focus();
        valid = false;		
    }	
	else if (es_amount.value == "" || es_amount.value == null)
    {
		msg='Please enter salary amount';
		es_amount.value="";
        es_amount.focus();
        valid = false;		
    }
	else if ( isNaN(es_amount.value))
    {
		msg='Please enter valid salary amount';
		es_amount.value="";
        es_amount.focus();
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