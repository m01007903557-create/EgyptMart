function admin_validate()
{
	username = document.getElementById('username').value;	
	password = document.getElementById('password').value;   

	

	if(username == "")
	{
		document.getElementById('err').innerHTML = 'Please enter your name';
		document.getElementById('username').className = 'inp_box_main inp_box_err';
		return false;
	}
	
	else if(password == "")
	{	
		document.getElementById('err').innerHTML = 'Please enter your password';
		document.getElementById('password').className = 'inp_box_main inp_box_err';
		document.getElementById('username').className = 'inp_box_main';		
		document.getElementById('password').focus();
		return false;
	}



		
}