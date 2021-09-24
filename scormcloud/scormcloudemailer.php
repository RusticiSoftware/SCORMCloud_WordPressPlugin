<?php

class ScormCloudEmailer
{
    public static function send_email($to, $subject, $message, $sender_name='SCORM Cloud', $sender_email='cloud@scorm.com')
    {
    	$headers .= "Return-Path: <" . $sender_email . ">\n";
    	$headers .= "X-Mailer: PHP" . phpversion() . "\n";
    
    	$subject = stripslashes($subject);
    	$message = stripslashes($message);
    	
    	$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: " . get_bloginfo('html_type') . "; charset=\"". get_bloginfo('charset') . "\"\n";
		$message = "<html><head><title>" . $subject . "</title></head><body>" . $message . "</body></html>";
    	
    	wp_mail($to->user_email, $subject, $message, $headers);
    }
}