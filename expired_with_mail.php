<?php
$subject = 'The password for the Domain Account ' .$username. ' expired';

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
					The password for the Domain account ' .$username. ' expired ' . $remaining_days .' days ago.
					<br /><br />					
					<p> 
					Please verify if the account owner still needs this account.<br /><br />			
					
					<strong>Please note this is an automated e-mail and <i>shall</i> not be replied!</strong><br /><br />
					<br />
					Best Regards,<br />
					The Support Team
					</p>
				</body>
			</html>';
?>
