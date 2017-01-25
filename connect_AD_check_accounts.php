<?php
/*
 SCRIPT  : ldap_notify_expire_pass.php
 AUTHOR  : Marin Nedea										
 WEBSITE : http://sysadmins.tech								
 BLOG    : http://sysadmins.tech								
 CREATED : 09-11-2016										
 COMMENT : Script to notify AD users via e-mail when 
		   their password is about to expire or to 
		   notify the admins team via e-mail/ticket.
 LICENSE : Copyright (C) 2016 - Marin Nedea @ http://sysadmins.tech

		   This program is free software: you can redistribute it and/or modify
		   it under the terms of the GNU General Public License as published by
		   the Free Software Foundation, either version 3 of the License, or
		   at your option) any later version.

  		   This program is distributed in the hope that it will be useful,
		   but WITHOUT ANY WARRANTY; without even the implied warranty of
		   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		   GNU General Public License for more details.

		   You should have received a copy of the GNU General Public License
		   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

set_time_limit(30);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',0);

// LDAP server config
$ldapserver = 'example.com';
$ldapuser   = 'serviceuser'; 
$ldappass   = 'password_here';
$base_dn    = 'CN=Users,DC=example,DC=com';


// Connect to AD
$ldapconn = ldap_connect($ldapserver) or die('Could not connect to LDAP server.');
// Using LDAP version 3. Change the version to 2 if needed
ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
// If you try to perform the searches on Windows 2003 Server Active Directory or above, it seems that you have to set the LDAP_OPT_REFERRALS option to 0.
// Without this, you will get "Operations error" if you try to search the whole AD (using root of the domain as a $base_dn).
ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

if($ldapconn) {
    // Binding to AD
    $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass) or die ('Error trying to bind: '.ldap_error($ldapconn));
	// If bind was successful
	if ($ldapbind) {
        // echo 'Connected to LDAP server<br /><br />';
		
		/*	
		I know that (objectCategory=person) and (objectClass=user) are kind of redundant, but the result will be correct;
		Remove from the search the default Guest account, the Adminstrator Account, the group containing the Service
		Accounts  and the accounts marked for deletion.
		*/ 
		
		//Set a search filter
		$filter='(&(objectCategory=person)(objectClass=user)(!(sAMAccountName=Guest))(!(sAMAccountName=Administrator)))';
		
		// Set an array of attributes you wish to retrieve for each entry
		$attrs = array('cn', 'displayname', 'sn', 'givenname', 'mail', 'samaccountname', 'pwdlastset', 'useraccountcontrol', 'memberof');
		// Set the search
        $result = ldap_search($ldapconn, $base_dn, $filter, $attrs) or die ('Error in search query: '.ldap_error($ldapconn));  
		
		// Get an array containg the results
		$data = ldap_get_entries($ldapconn, $result);
	  	  
		// Function to remove [count] from the ldap_get_entries result
		// See http://stackoverflow.com/questions/5997815/php-strip-count-value-from-array for details.
		
		function rCountRemover($arr) {
		  foreach($arr as $key=>$val) {
			// (int)0 == "count", so we need to use ===
			if($key === "count")
			  unset($arr[$key]);
			elseif(is_array($val))
			  $arr[$key] = rCountRemover($arr[$key]);
		  }
		  return $arr;
		}

		// Renew $data array using the function above
		$data = rCountRemover($data);
		
		// Print number of entries found
		// echo 'Results: ' . ldap_count_entries($ldapconn, $result) . '<br />;
		 
    
		// Get the data parsed for each entry
		foreach($data as $key => $value) {
		
			$user_display_name = ucfirst($userinfo['displayname'][0];
			$username = $value['samaccountname'][0]; 
			$user_email = $value['mail'][0];
			  
			// Get the time since last password change 
			$fileTime 		= $value['pwdlastset'][0];
			$winSecs      	= (int)($fileTime / 10000000); // divide by 10 000 000 to get seconds
			$unixTimestamp 	= ($winSecs - 11644473600); // 1.1.1600 -> 1.1.1970 difference in seconds	
			$timestamp = date(DateTime::RFC822, $unixTimestamp);  // time passed since Last Password Change
			
			$date2 = date_create($timestamp);
			$datetime2 = date_format($date2, 'Y-m-d');
			
			$since = strtotime($datetime2);
			$today = time();
			$difference = $today - $since;
			
			// Get the number of days till the password will expire
			$expire_days =  floor($difference / (60 * 60 * 24));  
						
			// Get the remaining days till the password expire
			$remaining_days = 90 - $expire_days;
			
			/*
			In order to avoid spam ( you don't wanna send an e-mail every day, do you?), I decided 
			to send e-mails when the remainig days until the password will expire are 1, 3 or 7. 
			So .. I'm making a simple array containg the numbers 1,3 7 
			*/
			
			$a = array(1,3,7);
		
			// checking if the value of $expire_days will match values in our array $a
			if (in_array(''.$expire_days.'', $a)){			
				
				// Preparing to send e-mail. We need to check if we have an e-amil address to send to.
				// Also, please check https://css-tricks.com/sending-nice-html-email-with-php/ for information
				// on sending HTML formated e-mail.
			
				if(empty($user_email)){					
					
					/*
					If there's no e-mail address set for the user, we need to notify someone (an admin member, or 
					a ticketing system) by e-mail, to update the account for that user with an e-mail address.
					We are using OTRS as ticketing system here, so I set an e-mail account on our e-mail server 
					just for the OTRS, and I'm fetching in OTRS the e-mails every 5 minutes.
					The advantage of this is that, in case the e-mail address of your recipient is set wrong in 
					the Active Directory, when sending the e-mail you will also get an error reply mail on your 
					inbox. Since OTRS is fetching the e-mails in that inbox, it will creat a ticket and let you know
					that a specific e-mail address is incorrect/doesn't exists.
					If you are using a different method, you could use, with similar result, a service e-mail account that
					everyone in your team is reading frequently.
					*/
					
					// Setting the $user_email to a hardcoded value, corresponding to our ticketing system/service e-maila ccount
					$user_email = 'ticketing@example.com';
					
					// Including the formated e-mail to create a ticket.
					include('ticketing_mail.php');
					/*
					echo '<pre>';
					echo $username.'<br />';
					echo $user_email.'<br />';
					echo $remaining_days.'<br />';
					echo 'creating ticket - email empty<br />';
					echo '</pre>';
					*/ 
				} else {						
			
					// Including the script to send e-mail to the user 
					include('reset_pass_mail.php');	
					/*
					echo '<pre>';
					echo $username.'<br />';
					echo $user_email.'<br />';
					echo $remaining_days.'<br />';
					echo 'notify user - will expire pass<br />';
					echo '</pre>';
					*/
				}
				
				
							
			} else if($expire_days > 90){  // If password already expired
				
				if(empty($user_email)){				

					// Setting the $user_email to a hardcoded value, corresponding to our ticketing system/service e-maila ccount
					$user_email = 'ticketing@example.com';
					
					// Including the formated e-mail to create a ticket.
					include('ticketing_expired_no_email_mail.php');
					/*
					echo '<pre>';
					echo $username.'<br />';
					echo $user_email.'<br />';
					echo $remaining_days.'<br />';
					echo 'creating ticket - email empty and password expired<br />';
					echo '</pre>';
					*/
							
				} else {
					
					// Create a ticket in ticketing system, letting know the admins the user
					// didn't renew his password.
					include('expire_password_ticketing_mail.php');					
					
					/*
					echo '<pre>';
					echo $username.'<br />';
					echo $user_email.'<br />';
					echo $remaining_days.'<br />';
					echo 'creating ticket - password expired<br />';
					echo '</pre>';
					*/
				} 
			
			} /*  // Uncomment this if you wish to list the accounts without password problems.
			
				else { // Close if password already expired  
				
				echo '<pre>';
				echo $username.'<br />';
				echo $user_email.'<br />';
				echo $remaining_days.'<br />';
				echo 'All OK<br />';	
				echo '</pre>';
							
			}
			*/
		
			// NOTE:  
			// You need to replace "-fserver_email@example.com" with your actual e-mail account used to send 
			// e-mails. 
			// Please make sure you keep the "-f" (envelope FROM: ) in front of the e-mail address! If your e-mail
			// is, let's say, no-reply@example.com, in the script you should have "-fno-reply@example.com". Please check
			// http://stackoverflow.com/questions/179014/how-to-change-envelope-from-address-using-php-mail for more.
		
			// Sending the actual mail
			mail($to, $subject, $message, $headers, '-fserver_email@example.com');

			
		} // close foreach

	} else { // Close if bind 
			
        echo 'LDAP bind failed...';
    
	}

} // close LDAP connection

ldap_close($ldapconn);
?>
