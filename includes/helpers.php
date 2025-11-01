<?php
namespace T709\Antispam;

if ( ! defined( 'ABSPATH' ) ) exit;

class Helpers {
    public static function ip() : string {
        foreach ( ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k ) {
            if ( ! empty( $_SERVER[$k] ) ) {
                $ip = is_string($_SERVER[$k]) ? explode(',', $_SERVER[$k])[0] : '';
                return sanitize_text_field( trim($ip) );
            }
        }
        return '0.0.0.0';
    }

    public static function now() : int { return time(); }

    public static function csv_to_array( string $csv ) : array {
        $out = array_filter( array_map( 'trim', explode( ',', $csv ) ) );
        return array_values( $out );
    }
}
