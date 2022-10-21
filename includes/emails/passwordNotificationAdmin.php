<?php
	include_once("../functions.php");
?>
<html>
<head>
<style type="text/css">
.title {
	font-family: Arial, Helvetica, sans-serif;
	padding: 5px;
	font-weight:bold;
	color: #FFFFFF;
	font-size: 16px;
}

.header {
    background: none repeat scroll 0% 0% #0E3F97;
    border-bottom: 3px solid #F0C237;
}

.title2 {
	font-family: Arial, Helvetica, sans-serif;
	padding: 5px;
	font-weight:bold;
	color: #000000;
	font-size: 14px;
}
.messege {
	font-family: Arial, Helvetica, sans-serif;
	padding: 5px;
	font-weight:bold;
	color: #000000;
	font-size: 12px;
}
.logoThumb{
	float:left;
	padding: 2px;
	margin: 3px;
	/*border: 1px solid #F0F0F0;*/
	text-align: center;
	vertical-align: middle;
}
.logoThumb img{border:0px}
body,td,th {
	font-family: tahoma;
	font-size: 11px;
	color: #FFFFFF;
}
.text {
	font-family: tahoma;
	font-size: 11px;
	color: #000000;
	padding: 5px;
}
</style>
<title><?php echo $common->get_prep($_REQUEST['subject']); ?></title>
</head>
<body>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td bgcolor="#0099FF" > 
    <div class="logoThumb"><img src="<?php echo URL; ?>images/logo.png" width="45"></div>
      
     </td>
  </tr>

  <tr>
    <td><p><span class="title2">Dear <?php echo $common->get_prep($_REQUEST['last_name']); ?>,</span><br>
      <span class="text">You recently modified your login details or you requested a new copy of your password, please see a copy of these details below for future reference<br>
        <br>
        Email: <strong><?php echo $common->get_prep($_REQUEST['email']); ?></strong><br>
        Username: <strong><?php echo $common->get_prep($_REQUEST['username']); ?></strong><br>
        Temporary Password: <strong><?php echo $common->get_prep($_REQUEST['password']); ?></strong><em> (some characters may be hidden)</em><br>
        URL: <strong><?php echo URLAdmin; ?></strong></span>
      <p>
        <span class="text">Regards,</span><br>
        <span class="text">LekkiHill</span>
      </p>
<p class="text">This email is intended for <?php echo $common->get_prep($_REQUEST['last_name']); ?>, please do not reply directly to this email. This email was sent from a notification-only address that cannot accept incoming email. If you have questions or need assistance,please use the contact information above</p>
<p class="text"><strong>Protect Your Password</strong><br>
  LekkiHill staff will NEVER ask you for your password via email. The only places you are asked for your password are when you sign in to your application. You will always sign in via a secure connection, and we ask you to ensure that the address in your browser begins exactly like this <strong>https://LekkiHill.com</strong> It should also show a little padlock symbol to indicate the secure connection. </p>
<p class="text"> Be alert to emails that request account information or urgent action.  Be cautious of websites with irregular addresses or those that offer unofficial payments to LekkiHill Administrator or other privates accounts.<br>
</p></td>
  </tr>
  <tr>
    <td bgcolor="#0099FF">&copy; <?php echo date("Y"); ?> LekkiHill Administrator All Rights Reserved</td>
  </tr>
</table>

<div class="header">
</div>
</body>
</html>