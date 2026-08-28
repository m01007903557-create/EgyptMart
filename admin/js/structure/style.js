/**
 * OrangeHRM Enterprise is a closed sourced comprehensive Human Resource Management (HRM)
 * System that captures all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM Inc is the owner of the patent, copyright, trade secrets, trademarks and any
 * other intellectual property rights which subsist in the Licensed Materials. OrangeHRM Inc
 * is the owner of the media / downloaded OrangeHRM Enterprise software files on which the
 * Licensed Materials are received. Title to the Licensed Materials and media shall remain
 * vested in OrangeHRM Inc. For the avoidance of doubt title and all intellectual property
 * rights to any design, new software, new protocol, new interface, enhancement, update,
 * derivative works, revised screen text or any other items that OrangeHRM Inc creates for
 * Customer shall remain vested in OrangeHRM Inc. Any rights not expressly granted herein are
 * reserved to OrangeHRM Inc.
 *
 * You should have received a copy of the OrangeHRM Enterprise  proprietary license file along
 * with this program; if not, write to the OrangeHRM Inc. 538 Teal Plaza, Secaucus , NJ 0709
 * to get the file.
 *
 */


/* Style related JavaScript for orange theme */

/**
 * Draw a round border around div's with the given css class 
 * @param className Class Name of div's to be given a round border
 */
function roundBorder(className) {
	var innerClass = 'maincontent';
        var divContent = '';
    var elements = document.getElementsByTagName('div');
    for (i=0;i<getElementLength(elements.length);i++) {
        div = elements[i];
        if (div.className == className) {

			divContent = div.innerHTML;
			div.innerHTML = "";
			
            topdiv = document.createElement('div');
            topdiv.className = "top";
			div.appendChild(topdiv);
            topleft = document.createElement('div');
            topleft.className = "left";
            topdiv.appendChild(topleft);           
            topright = document.createElement('div');
            topright.className = "right";
            topdiv.appendChild(topright);            
            topmiddle = document.createElement('div');
            topmiddle.className = "middle";
            topdiv.appendChild(topmiddle);            			
			
			innerDiv = document.createElement('div');
			innerDiv.className = innerClass;
			innerDiv.innerHTML = divContent;			
			div.appendChild(innerDiv);
						
            bottomdiv = document.createElement('div');
            bottomdiv.className = "bottom";
            div.appendChild(bottomdiv);            
            bottomleft = document.createElement('div');
            bottomleft.className = "left";
            bottomdiv.appendChild(bottomleft);
            bottomright = document.createElement('div');
            bottomright.className = "right";
            bottomdiv.appendChild(bottomright);
            bottommiddle = document.createElement('div');
            bottommiddle.className = "middle";
            bottomdiv.appendChild(bottommiddle);                                    
        }
    }
}

/*
 * Function run when mouse moves over a button
 * Sets className of button to "className classNamehov" 
 */
function moverButton(button) {
	var btnClass = button.className;
	button.className =  btnClass + " " + btnClass + "hov"; 
}

/*
 * Function run when moves moves out of a button
 * Removes the 'hov' className added in moverButton function
 */
function moutButton(button) {
    var classes = button.className.split(" ");
    if (classes.length > 1) {
        button.className = classes[0];
    }
}

/*
 * Function will run when adding rounded borders to the template div tags
 * Reduce the nuber of div counts from 1 when browser version is IE
 */
function getElementLength(length){
    if(ieVersion()==9){
        return length-1;
    }
    return length;   
}

/*
 * Function will run when adding rounded borders to the template div tags
 * Function will return the current version of Internet Explorer in client side 
 */
function ieVersion(){
    var ua = window.navigator.userAgent;
    var msie = ua.indexOf ("MSIE ");
    if (msie > 0){
        return parseInt(ua.substring(msie+5, ua.indexOf(".", msie)));
    }
    return 0;         
}