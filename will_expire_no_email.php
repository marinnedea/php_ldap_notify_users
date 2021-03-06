<?php 

$subject = 'The password for the Domain Account ' .$username. ' will expire in '. $expire_days .' days';

// Prepare the e-mail to be sent 
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";	
$headers .= 'To:<tickets@example.com>'."\r\n";
$headers .= 'From: Support Team <support@example.com>' . "\r\n";
$headers .= 'Subject:'.$subject.''."\r\n";

$message = '<html>
				<body>				
					<p>Hello ' .$user_display_name.', <br />
					<br />
					The password for the Domain account ' .$username. ' will expire in ' . $expire_days .' days but there\'s no e-mail address set for this account.
					<br /><br />					
					<p> 
					Please verify if the account and update the e-mail address.<br /><br />			
					
					<strong>Please note this is an automated e-mail and <i>shall</i> not be replied!</strong><br /><br />
					<br />
					Best Regards,<br />
					The Support Team
					</p>
				</body>
			</html>';
?>
