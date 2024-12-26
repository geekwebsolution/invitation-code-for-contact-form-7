<?php
/*
Plugin Name: Invitation Code For Contact Form 7
Description: Create an invitation code for users with contact form 7 to get the confirmed subscribers.
Author: Geek Code Lab
Version: 1.8.0
Author URI: https://geekcodelab.com/
Text Domain : invitation-code-for-contact-form-7
*/

if (!defined('ABSPATH')) exit;

define('CF7IC_BUILD','1.8.0');

if (!defined( 'CF7IC_PLUGIN_DIR_PATH' ))
	define( 'CF7IC_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__) );

if (!defined( 'CF7IC_PLUGIN_URL' ))
	define( 'CF7IC_PLUGIN_URL', plugins_url() . '/' . basename(dirname(__FILE__)) );

if (!defined("CF7IC_PLUGIN_BASENAME"))
define("CF7IC_PLUGIN_BASENAME", plugin_basename(__FILE__));

if (!defined("CF7IC_PLUGIN_DIR"))
	define("CF7IC_PLUGIN_DIR", plugin_basename(__DIR__));


require(CF7IC_PLUGIN_DIR_PATH . 'updater/updater.php');

register_activation_hook(__FILE__, 'cf7ic_updater_activate');
add_action( 'admin_init', 'cf7ic_plugin_load' );
add_action('upgrader_process_complete', 'cf7ic_updater_activate'); // remove  transient  on plugin  update



function cf7ic_plugin_load(){
	if ( ! ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) ) {
		add_action( 'admin_notices', 'cf7ic_install_contact_form_7_admin_notice' );
		deactivate_plugins("invitation-code-for-contact-form-7/invitation-code-for-contact-form-7.php");
		return;
	}
}

function cf7ic_install_contact_form_7_admin_notice(){ ?>
	<div class="error">
		<p>
			<?php
			// translators: %s is the plugin name.
			echo esc_html( sprintf( __( '%s is enabled but not effective. It requires Contact Form 7 in order to work.', 'Invitation Code For Contact Form 7' ), 'invitation-code-for-contact-form-7' ) );
			?>
		</p>
	</div>
	<?php

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