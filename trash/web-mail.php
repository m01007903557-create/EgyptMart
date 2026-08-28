<?php

// Secure SSL/TLS Settings
// (Recommended)
// Username:	info@arabyos.com
// Password:	Use the email account’s password.
// Incoming Server: secureus187.sgcpanel.com
// IMAP Port: 993
// POP3 Port: 995
// Outgoing Server: secureus187.sgcpanel.com
// SMTP Port: 465
// Authentication is required for IMAP, POP3, and SMTP.
// ; 
// Non-SSL Settings
// (This is NOT recommended.)
// Username:	info@arabyos.com
// Password:	Use the email account’s password.
// Incoming Server: mail.arabyos.com
// IMAP Port: 143
// POP3 Port: 110
// Outgoing Server: mail.arabyos.com
// SMTP Port: 2525
// Authentication is required for IMAP, POP3, and SMTP.
// password: info@arabyos.com

include "./common.php";



sendSMTPMail("mobawab1@yahoo.com", "Rachel Recipient","testing mail");


?>