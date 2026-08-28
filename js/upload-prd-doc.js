function upload()
{
 	var a=document.iform.file.value;
	
 	var pattern1 =/\//;
	if(pattern1.test(a))
	{
		var my_array=a.split("/");
		var len=my_array.length;
		var row_value=my_array[len-1];
	}
	else
	{
		var my_array=a.split("\\");
		var len=my_array.length;
		var row_value=my_array[len-1];
	}
	
	doc_value=row_value.replace(/\s+/g,"-");
	doc_value=row_value.toLowerCase();

	st=doc_value;

	if(st.indexOf('\\') > -1)
	{
		st=st.replace(/\\/g,'/');
	}
	if(st.indexOf(' ') > -1)
	{
		st=st.replace(/ /g,'-');
	}

	str=st.substr(st.lastIndexOf("/")+1);

	if(str.search(/^[\w\.\_\-]+$/) == -1)
	{
		alert ("Attachment filename can contain only alphabets (a-z A-Z) , numbers (0-9) , underscore (_) or hiphen (-).");
		return false;
	}
	if(str.length > 65)
	{
		alert ("Attachment filename cannot have more than 65 characters.");
		return false;
	}

	str1=str.split(".");

	if(str1.length>2)
	{
		alert ("Attachment filename can contain only alphabets (a-z A-Z) , numbers (0-9) , underscore (_) or hiphen (-).");
		return false;
	}
	if(str.lastIndexOf(" ") > -1)
	{
		alert ("Attachment filename can contain only alphabets (a-z A-Z) , numbers (0-9) , underscore (_) or hiphen (-).");
		return false;
	}

	var ext = st.substr(st.indexOf("."), st.length)
	ext = ext.toLowerCase();
	if (ext == '.pdf')
	{

	}
	else
	{
		alert("Please attach .pdf files only.");
		document.iform.file.value = '';
		return false;
	}

	eval("document.getElementById('ori_doc').value='"+doc_value+"'");

	eval("parent.document.getElementById('indecator_gif0').innerHTML=\"<IMG SRC='http://my.imimg.com/gifs/indicator.gif'>\"");

	if(parent.document.getElementById('save_additional'))
		eval("parent.document.getElementById('save_additional').innerHTML = '<input name=updateaddi value=\\\"Save Details\\\" class=\\\"sdis awt mt12 m5\\\" type=submit disabled>'");
	var product_name=parent.document.getElementById('item_name').value;
	    product_name = product_name.replace('&','-');
	    document.getElementById('prd_name').value=product_name;
	setTimeout('document.iform.submit()',500);
}