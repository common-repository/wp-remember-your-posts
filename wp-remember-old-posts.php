<?php
/*
Plugin Name: WP Remember Your Posts
Description: This plugin enable you to remind your old posts on the same day.
Author: hondamarlboro
Author URI: http://daisukeblog.com
License: GPLv2 or later
Version: 0.1
*/

// (De)Activation hooks
register_activation_hook( __FILE__, 'schedule_cron');
register_deactivation_hook( __FILE__, 'clear_cron');

// Hooks
add_action( 'wp_remember_your_posts', 'wp_remember_your_posts_do', 10, 0 );

// Schedule the daily emails
function schedule_cron() {
	return wp_schedule_event( time(), 'daily',  'wp_remember_your_posts' );
}

// Remove the scheduled daily email
function clear_cron() {
	wp_clear_scheduled_hook( 'wp_remember_your_posts' );
}

function wp_remember_your_posts_do() {

	$to = get_option('admin_email');
	$subject = 'WP Remember Your Posts';
	$headers= "MIME-Version: 1.0\n" .
			"From: $to\n" .
			"Content-Type: text/html; charset=\"" .
			get_option('blog_charset') . "\"\n";

	$yday = date('Y',current_time('timestamp',0));
	$mday = date('m',current_time('timestamp',0));
	$dday = date('d',current_time('timestamp',0));

	$result = query_posts( 'monthnum='.$mday.'&day='.$dday );

	if(count($result) != 0){
		$body = "You wrote on the same day:<br><br>";

		for($i=0; $i < count($result); $i++){
			if(date('Y', strtotime($result[$i]->post_date)) != $yday){
				$body .= date('Y/m/d H:i', strtotime($result[$i]->post_date))."<br>".esc_html($result[$i]->post_title)."<br>".get_permalink($result[$i]->ID)."<br><br>";
			}
		}
	
	} else {
		$body = "On the same day you had a break from writing:-)<br>";
	}

	mail( $to, $subject, $body, $headers);
}
