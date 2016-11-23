
<?php 
$subject = 'Your Domain Account password is about to expire in '. $expire_days .' days';

// Prepare the e-mail to be sent 
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";	
$headers .= 'To:<'.$usermailaddress.'>'."\r\n";
$headers .= 'From: Support Team <no-reply@example.com>' . "\r\n";
$headers .= 'Subject:'.$subject.''."\r\n";

$message = '<html>
				<body>				
					<p>Hello ' .$user_display_name.', <br />
					<br />
					The password for your Domain account ' .$username. ' will expire in ' .$expire_days.' days.
					<br /><br />					
					<p> 
					To change your domain Account password, please <a href="#">click here</a> and follow instructions in the page.<br />
					If you are unable to change the password, for whatever reason, please open a ticket to us via the ticketing system.<br /><br />			
					
					<strong>Please note this is an automated e-mail and <i>shall</i> not be replied!</strong><br /><br />
					<br />
					Best Regards,<br />
					The Support Team
					</p>
				</body>
			</html>';
?>
