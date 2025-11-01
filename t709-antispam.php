<?php
/**
 * Plugin Name: T709 Anti-Spam Honeypot
 * Description: Lightweight honeypot + time-trap anti-spam for WPForms & Contact Form 7.
 * Version: 1.1.0
 * Author: Dillon Porter
 * License: MIT
 */

defined('ABSPATH') || exit;

// Paths
define('T709_AS_PATH', plugin_dir_path(__FILE__));
define('T709_AS_URL',  plugin_dir_url(__FILE__));

// Load classes
require_once T709_AS_PATH . 'includes/helpers.php';
require_once T709_AS_PATH . 'includes/class-core.php';
require_once T709_AS_PATH . 'includes/class-integrations.php';
// optional admin page
if ( is_admin() && file_exists(T709_AS_PATH . 'includes/class-admin.php') ) {
    require_once T709_AS_PATH . 'includes/class-admin.php';
}

// Bootstrap
add_action('plugins_loaded', function () {
    $settings = [
        'enabled'         => true,
        'honeypot_name'   => 'website_url',
        'timestamp_name'  => 't709_ts',
        'min_seconds'     => 3,
        'rate_limit_max'  => 3,
        'rate_limit_win'  => 5,
        'keyword_block'   => '',
    ];

    $core = new \T709\Antispam\Core($settings);
    $core->init();

    $int = new \T709\Antispam\Integrations($settings);
    $int->init();

    if ( is_admin() && class_exists('\T709\Antispam\Admin') ) {
        (new \T709\Antispam\Admin())->init();
    }
});
