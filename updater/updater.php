<?php

if (!defined('ABSPATH')) exit;

/**
 * License manager module
 */
function cf7ic_updater_utility() {
    $prefix = 'CF7IC_';
    $settings = [
        'prefix' => $prefix,
        'get_base' => CF7IC_PLUGIN_BASENAME,
        'get_slug' => CF7IC_PLUGIN_DIR,
        'get_version' => CF7IC_BUILD,
        'get_api' => 'https://download.geekcodelab.com/',
        'license_update_class' => $prefix . 'Update_Checker'
    ];

    return $settings;
}

register_activation_hook(__FILE__, 'cf7ic_updater_activate');
function cf7ic_updater_activate() {

    // Refresh transients
    delete_site_transient('update_plugins');
    delete_transient('cf7ic_plugin_updates');
    delete_transient('cf7ic_plugin_auto_updates');
}

require_once(CF7IC_PLUGIN_DIR_PATH . 'updater/class-update-checker.php');
