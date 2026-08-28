function userr_validate()
{
	useremail = document.getElementById('email').value;	
	password = document.getElementById('pass').value;   

	if(useremail == "")
	{
		document.getElementById('errr').innerHTML = 'Please enter your Email id/Mobile';
		document.getElementById('email').focus();
		return false;
	}
	else if(password == "")
	{	
		document.getElementById('errr').innerHTML = 'Please enter your password';	
		document.getElementById('pass').focus();
		return false;
	}	
}
