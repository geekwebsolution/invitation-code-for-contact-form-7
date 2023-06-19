<?php
/*
Plugin Name: Invitation Code For Contact Form 7
Description: Create an invitation code for users with contact form 7 to get the confirmed subscribers.
Author: Geek Code Lab
Version: 1.6
Author URI: https://geekcodelab.com/
Text Domain : invitation-code-for-contact-form-7
*/

if (!defined('ABSPATH')) exit;

define('CF7IC_BUILD',1.6);

if (!defined( 'CF7IC_PLUGIN_DIR_PATH' ))
	define( 'CF7IC_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__) );

if (!defined( 'CF7IC_PLUGIN_URL' ))
	define( 'CF7IC_PLUGIN_URL', plugins_url() . '/' . basename(dirname(__FILE__)) );

register_activation_hook( __FILE__, 'cf7ic_plugin_activate' );
function cf7ic_plugin_activate() {
	if ( ! ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) ) {
		die( '<b>Invitation Code Contact Form 7</b> plugin is deactivated because it require <b>Contact Form 7</b> plugin installed and activated.' );
	}
}

$plugin = plugin_basename(__FILE__);
add_filter( "plugin_action_links_$plugin", 'cf7ic_add_plugin_link');
function cf7ic_add_plugin_link( $links ) {
	$support_link = '<a href="https://geekcodelab.com/contact/" target="_blank" >' . __( 'Support', 'invitation-code-for-contact-form-7' ) . '</a>';
	array_unshift( $links, $support_link );
	
	$setting_link = '<a href="'. admin_url('edit.php?post_type=cf7ic_invite_codes') .'">' . __( 'Settings', 'invitation-code-for-contact-form-7' ) . '</a>';
	array_unshift( $links, $setting_link );

	return $links;
}
    
require_once(CF7IC_PLUGIN_DIR_PATH . 'functions.php');
require_once(CF7IC_PLUGIN_DIR_PATH . 'class-admin.php');