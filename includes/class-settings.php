<?php
namespace T709\Antispam;

if ( ! defined( 'ABSPATH' ) ) exit;

class Settings {

    public static function get() : array {
        return wp_parse_args(
            get_option( 't709_as_settings', [] ),
            [
                'enabled'        => 1,
                'min_seconds'    => 3,
                'rate_limit_max' => 3,
                'rate_limit_win' => 5,
                'honeypot_name'  => 'website_url',
                'timestamp_name' => 't709_ts',
                'keyword_block'  => '',
            ]
        );
    }

    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'register' ] );
        add_action( 'admin_menu', [ __CLASS__, 'menu' ] );
    }

    public static function menu() {
        add_options_page(
            'T709 Anti-Spam',
            'T709 Anti-Spam',
            'manage_options',
            't709-antispam',
            [ __CLASS__, 'render' ]
        );
    }

    public static function register() {
        register_setting( 't709_as', 't709_as_settings', [
            'type'              => 'array',
            'sanitize_callback' => [ __CLASS__, 'sanitize' ],
        ] );

        add_settings_section( 't709_as_main', 'Settings', '__return_null', 't709_as' );

        $fields = [
            'enabled'        => 'Enable protection',
            'min_seconds'    => 'Min seconds before submit',
            'rate_limit_max' => 'Max submissions per IP (window)',
            'rate_limit_win' => 'Rate limit window (minutes)',
            'honeypot_name'  => 'Honeypot field name',
            'timestamp_name' => 'Timestamp field name',
            'keyword_block'  => 'Blocked keywords (comma-separated)',
        ];

        foreach ( $fields as $key => $label ) {
            add_settings_field( $key, $label, [ __CLASS__, 'field' ], 't709_as', 't709_as_main', [ 'key' => $key ] );
        }
    }

    public static function sanitize( $input ) {
        $out = self::get();
        $out['enabled']        = isset( $input['enabled'] ) ? 1 : 0;
        $out['min_seconds']    = max(0, intval( $input['min_seconds'] ?? 3 ));
        $out['rate_limit_max'] = max(0, intval( $input['rate_limit_max'] ?? 3 ));
        $out['rate_limit_win'] = max(1, intval( $input['rate_limit_win'] ?? 5 ));
        $out['honeypot_name']  = sanitize_key( $input['honeypot_name'] ?? 'website_url' );
        $out['timestamp_name'] = sanitize_key( $input['timestamp_name'] ?? 't709_ts' );
        $out['keyword_block']  = sanitize_text_field( $input['keyword_block'] ?? '' );
        return $out;
    }

    public static function field( $args ) {
        $key = $args['key'];
        $s   = self::get();
        if ( $key === 'enabled' ) {
            printf('<label><input type="checkbox" name="t709_as_settings[enabled]" value="1" %s> On</label>',
                checked( $s['enabled'], 1, false )
            );
            return;
        }

        if ( in_array( $key, ['keyword_block'], true ) ) {
            printf('<textarea name="t709_as_settings[%1$s]" rows="3" cols="60">%2$s</textarea>', esc_attr($key), esc_textarea($s[$key]));
        } else {
            printf('<input type="number" %s name="t709_as_settings[%1$s]" value="%2$s" />',
                in_array($key, ['honeypot_name','timestamp_name'], true) ? 'type="text"' : '',
                esc_attr( $s[$key] )
            );
        }
    }

    public static function render() { ?>
        <div class="wrap">
            <h1>T709 Anti-Spam</h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 't709_as' );
                do_settings_sections( 't709_as' );
                submit_button();
                ?>
            </form>
            <p><em>Tip:</em> Use with reCAPTCHA/Turnstile for defense-in-depth.</p>
        </div>
    <?php }
}
