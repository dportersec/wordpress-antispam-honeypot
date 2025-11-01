<?php
/**
 * Plugin Name: T709 Anti-Spam (Honeypot + Time Trap)
 * Description: Invisible honeypot + time-trap + IP rate limiting for WPForms & Contact Form 7. Lightweight, no-UX-friction anti-spam.
 * Version: 1.1.0
 * Author: Dillon Porter
 * License: MIT
 * Requires at least: 5.5
 * Requires PHP: 7.4
 * Text Domain: t709-antispam
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'T709_AS_VERSION', '1.1.0' );
define( 'T709_AS_FILE', __FILE__ );
define( 'T709_AS_DIR', plugin_dir_path( __FILE__ ) );
define( 'T709_AS_URL', plugin_dir_url( __FILE__ ) );

require_once T709_AS_DIR . 'includes/helpers.php';
require_once T709_AS_DIR . 'includes/class-settings.php';
require_once T709_AS_DIR . 'includes/class-core.php';
require_once T709_AS_DIR . 'includes/class-integrations.php';

add_action( 'plugins_loaded', function () {
    // Init settings
    T709\Antispam\Settings::init();

    // Core loader
    $settings = T709\Antispam\Settings::get();
    $core     = new T709\Antispam\Core( $settings );
    $core->init();

    // Form integrations
    $int = new T709\Antispam\Integrations( $settings );
    $int->init();
} );

// Activation: set defaults once
register_activation_hook( __FILE__, function(){
    if ( ! get_option( 't709_as_settings' ) ) {
        update_option( 't709_as_settings', [
            'enabled'        => 1,
            'min_seconds'    => 3,
            'rate_limit_max' => 3,      // submissions
            'rate_limit_win' => 5,      // minutes
            'honeypot_name'  => 'website_url',
            'timestamp_name' => 't709_ts',
            'keyword_block'  => '',     // comma-separated
        ] );
    }
} );
