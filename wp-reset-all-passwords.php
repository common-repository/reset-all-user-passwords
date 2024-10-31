<?php
/*
Plugin Name: Reset All User Passwords
Plugin URI: http://www.patrickgarman.com/wordpress-plugins/reset-all-user-passwords/
Description: This plugin will allow you to reset all your user passwords and all users will be notified of the change.
Version: 1.0.0
Author: Patrick Garman
Author URI: http://www.patrickgarman.com/
License: GPLv2
*/

/*  Copyright 2011  Patrick Garman  (email : patrickmgarman@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


register_activation_hook(__FILE__, 'passreset_activation');
register_deactivation_hook(__FILE__, 'passreset_deactivation');

function passreset_activation() {

}

function passreset_deactivation() {

}

if (is_admin()) {
    add_action('admin_menu', 'passreset_admin_menu');
}

function passreset_admin_menu() {
	add_options_page('Reset Passwords', 'Reset Passwords', 'administrator', __FILE__, 'passreset_options_page');
}

function passreset_options_page() {
	global $wpdb;
	$users = $wpdb->get_results("SELECT * FROM $wpdb->users WHERE ID > 1"); // let's do ALL users except the original admin (probably you)
    $tester_text = 'I am absolutely positive I want to reset ALL USER PASSWORDS';
    echo '<div class="wrap">';
        echo '<h2>Reset All User Passwords</h2>';
        if($_POST) {
            if ($_POST['verifier']==$tester_text) {
                echo '<table class="widefat">';
                echo '<thead><tr><th>User ID</th><th>Status</th><th>Username</th><th>Email</th><th>New Activation Key</th></tr></thead>';
                echo '<tfoot><tr><th>User ID</th><th>Status</th><th>Username</th><th>Email</th><th>New Activation Key</th></tr></tfoot>';
                echo '<tbody>';
                foreach($users as $user) {
			if ($user->user_activation_key!='') {
				$new_key = wp_generate_password( 32, false );
				$activation_URL = wp_login_url().'?action=rp&key='.$new_key.'&login='.$user->user_login;
				$update = $wpdb->update( $wpdb->users, array( 'user_activation_key' => $new_key ), array( 'ID'=>$user->ID ) );
				$headers = "From: ".get_option('blogname')." <".get_option('admin_email')."> \r\n\\";
				$message = stripslashes($_POST['message'])."\n\n".$activation_URL;
				if ($update == 1) {
					$email = wp_mail($user->user_email, stripslashes($_POST['subject']), $message, $headers);
					if ($email == 1) { $status = 'Email SENT'; }
					else { $status = 'ERROR CODE: 2'; }
				} else { $status = 'ERROR CODE: 1'; }
				echo '<tr>
				<td width="10%">'.$user->ID.'</td>
				<td width="10%">'.$status.'</td>
				<td width="20%">'.$user->user_login.'</td>
				<td width="30%">'.$user->user_email.'</td>
				<td width="30%"><a href="'.$activation_URL.'">'.$new_key.'</a></td>
				</tr>';
			}
                }
                echo '</tbody></table>';
            } else { echo '<p>Sorry, you did not enter the correct text in the form. Please try again.'; }
            echo '<h4>Error Codes</h4>';
            echo '<p>1: Acitvation Key Update FAILED</p>';
            echo '<p>2: Activation Key Updated BUT Email FAILED</p>';
        } else {
		$blogname=get_bloginfo('name');
		$default_message = "Hello!\n\nSorry to disturb you like this, but unfortunately we have to reset everyone's password. When you have a free minute please click the link below and reset your password for our website. Thank you!";
		$default_subject = '['.$blogname.'] New Account Created';
		echo '<p>Are you SURE you want to do this? You might tick some people off... If you are absolutely sure, you can type a quick message that will be sent to your users with the password reset link. The activation link will be at the bottom of the email.</p>';
		echo '<form method="post" action="">';
			echo '<p>Your Subject Line</p>';
			echo '<p><input type="text" name="subject" value="'.$default_subject.'" style="width:500px;" /></p>';
			echo '<textarea name="message" style="width:498px; height:150px;">'.$default_message.'</textarea>';
			echo '<p>If you are sure... copy and paste the text below into the text box and then click "I AM SURE"</p>';
			echo '<pre>'.$tester_text.'</pre>';
			echo '<p><input type="text" name="verifier" value="" style="width:500px;" /></p>';
			echo '<p><input type="submit" name="submit" value="I AM SURE" /></p>';
		echo '</form>';
		echo '<table class="widefat">';
                echo '<thead><tr><th>User ID</th><th>Status</th><th>Username</th><th>Email</th><th>New Activation Key</th></tr></thead>';
                echo '<tfoot><tr><th>User ID</th><th>Status</th><th>Username</th><th>Email</th><th>New Activation Key</th></tr></tfoot>';
                echo '<tbody>';
		foreach($users as $user) {
			echo '<tr>
				<td width="10%">'.$user->ID.'</td>
				<td width="10%">'.$status.'</td>
				<td width="20%">'.$user->user_login.'</td>
				<td width="30%">'.$user->user_email.'</td>
				<td width="30%">'.$user->user_activation_key.'</td>
			    </tr>';
		}
                echo '</tbody></table>';
        }
    echo '</div>';
}