<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head><title>Your are now a domain admin on Reatha.de</title></head>
<body>
<div style="max-width: 800px; margin: 0; padding: 30px 0;">
<table width="80%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td width="5%"></td>
<td align="left" width="95%" style="font: 13px/18px Arial, Helvetica, sans-serif;">
<h2 style="font: normal 20px/23px Arial, Helvetica, sans-serif; margin: 0; padding: 0 0 18px; color: black;">Your are now a domain admin on Reatha.de</h2>
Hi <?php echo $user->username; ?>, you have been assigned a domain under management on Reatha.de<br/><br/>
<b>Domain Name: </b><?php echo $domain->name; ?><br/><br/>
Log in to <a href="<?php echo $site_url; ?>"><?php echo $site_name; ?></a> with your usernamse and password to start managing this domain.

<br /><br />


Have fun!<br />
The <?php echo $site_name; ?> Team
</td>
</tr>
</table>
</div>
</body>
</html>