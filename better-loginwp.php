<?php
/**
 * Plugin name: Better Login WP
 * Description: Just make it harder WordPress !
 * Version: 0.1
 * Author: Julien Maury
 * Text Domain: blw
 * Domain Path:  /languages
 */

defined( 'ABSPATH' )
or die ( 'No' );

add_action( 'init', '_blw_load_textdomain' );
function _blw_load_textdomain() {
	load_plugin_textdomain( 'blw', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

add_filter( 'login_errors', '_blw_error_messages' );
function _blw_error_messages() {
	return __( 'Wait ! Your credentials are wrong !', 'blw' );
}

/**
 * Set a list of the world's weakest passwords
 * Feel free to add yours by using the magic WP filter
 * @source https://13639-presscdn-0-80-pagely.netdna-ssl.com/wp-content/uploads/2017/12/Top-100-Worst-Passwords-of-2017a.pdf
 * @return array
 * @author Julien Maury
 */
function _blw_get_list() {
	$list = (array) apply_filters( 'blw_weak_passwd_list', [
		'123456',
		'password',
		'12345678',
		'qwerty',
		'12345',
		'123456789',
		'letmein',
		'1234567',
		'football',
		'iloveyou',
		'admin',
		'welcome',
		'monkey',
		'login',
		'abc123',
		'starwars',
		'123123',
		'dragon',
		'passw0rd',
		'master',
		'hello',
		'freedom',
		'whatever',
		'qazwsx',
		'trustno1',
	] );

	return array_unique( $list );
}

/**
 * Expose bad passwords
 * @author Julien Maury
 */
add_action( 'user_profile_update_errors', '_blw_check_password_on_update', 1, 3 );
add_action( 'validate_password_reset', '_blw_check_password', 10, 2 );

/**
 * Run check on update profile
 *
 * @param $errors
 * @param $update
 * @param $user
 *
 * @author Julien Maury
 * @return mixed
 */
function _blw_check_password_on_update( $errors, $update, $user ) {
	return _blw_check_password( $errors, $user );
}

/**
 * @param $errors
 * @param $user
 *
 * @author Julien Maury
 * @return mixed
 */
function _blw_check_password( $errors, $user ) {

	if ( isset( $user->user_pass ) ) {

		/**
		 * use built in password checker
		 * to bail update if user wants a weak password
		 * despite the first warning by WP
		 */
		if ( isset( $_POST['pw_weak'] ) ) {
			$errors->add( 'ignoring_wp_warning', __( '<strong>Error</strong>: You are actually ignoring the WordPress warning "weak password". We cannot allow you to do it. Please retry with a stronger password.', 'blw' ) );
		}

		/**
		 * But that's not enough
		 * it's easy to disable js
		 * and the bad game begins
		 */
		if ( mb_strlen( $user->user_pass ) < 8 ) {
			$errors->add( 'password_too_short', __( '<strong>Error</strong>: Your password is too short ! At least use 8 chars please.', 'blw' ) );
		}

		/**
		 * I could have added some other checking such as including numbers and letters
		 * but I DO NOT believe in this stuffs, "pipo21" has both numbers and letters
		 * and it's a poor password !
		 */
		if ( in_array( $user->user_pass, _blw_get_list(), true ) ) {
			$errors->add( 'password_weak', __( '<strong>Error</strong>: Your password is in the list of the world\'s weakest passwords. We cannot allow you to use it. Please retry with a stronger password.', 'blw' ) );
		}

	}

	return $errors;
}

add_filter( 'wp_authenticate_user', '_blw_check_password_on_login', 11011, 2 );
/**
 * @param $user
 * @param $password
 *
 * @author Julien Maury
 * @return WP_Error
 */
function _blw_check_password_on_login( $user, $password ) {

	if ( empty( $password ) ) { // the impossible case...
		return $user;
	}
	
	/** wrong passwd check **/
	if ( ! wp_check_password( $password, $user->data->user_pass, $user->ID )  ) {
		return $user;
	}

	if ( mb_strlen( $password ) < 8 ) {
		remove_filter( 'login_errors', '_blw_error_messages' );// we need to remove our overriding text
		new WP_Error('password_too_short', __( '<strong>Error</strong>: Your password is too short and has been deactivated by the administrator. You have to use the "Lost Password" function to create a new password that is more secure.', 'blw' ) );
	}

	if ( in_array( $password, _blw_get_list(), true ) ) {
		remove_filter( 'login_errors', '_blw_error_messages' );// we need to remove our overriding text

		return new WP_Error('password_not_allowed', __( '<strong>ERROR</strong>: Your password is in the list of the world\'s weakest passwords, and has been deactivated by the administrator. You have to use the "Lost Password" function to create a new password that is more secure.' ) );
	}

	return $user;
}
