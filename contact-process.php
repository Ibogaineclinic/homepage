<?php
session_start();
if( isset($_POST['name']) )
{
	$to = 'inquiry@ibogaineclinic.com'; // Replace with your email	
	$subject = 'Message from website'; // Replace with your $subject
	$headers = 'From: ' . $_POST['email'] . "\r\n" . 'Reply-To: ' . $_POST['email'];	
	
	$message = 'Name: ' . $_POST['name'] . "\n" .
	           'E-mail: ' . $_POST['email'] . "\n" .
               'Phone: ' . $_POST['phone'] . "\n" .  
	           'Message: ' . $_POST['message'];
	
	mail($to, $subject, $message, $headers);	
	if( $_POST['copy'] == 'on' )
	{
		mail($_POST['email'], $subject, $message, $headers);
	}
}
?>
