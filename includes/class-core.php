<?php
namespace T709\Antispam;

if ( ! defined( 'ABSPATH' ) ) exit;

class Core {

    private array $settings;

    public function __construct( array $settings ) { $this->settings = $settings; }

    public function init() {
        if ( empty($this->settings['enabled']) ) return;

        // Common assets: hidden fields & CSS + timestamp
        add_action( 'wp_enqueue_scripts', [ $this, 'assets' ], 99 );
        add_action( 'wp_footer', [ $this, 'fallback_fields' ], 1 );
    }

    public function assets() {
        $ts  = Helpers::now();
        wp_register_style( 't709-as', false );
        wp_enqueue_style( 't709-as' );
        wp_add_inline_style( 't709-as', '.t709-hp{position:absolute!important;left:-9999px!important;height:0!important;width:0!important;overflow:hidden!important;}' );
        wp_add_inline_script( 'jquery', 'window.T709_ANTISPAM_TS='.$ts.';', 'before' );
    }

    // Footer fallback so bots/cached pages still see fields
    public function fallback_fields() {
        $hp = esc_attr( $this->settings['honeypot_name'] );
        $tn = esc_attr( $this->settings['timestamp_name'] );
        $ts = esc_attr( Helpers::now() );
        echo '<div class="t709-hp" aria-hidden="true">
                <input type="text" name="'.$hp.'" tabindex="-1" autocomplete="off" />
                <input type="hidden" name="'.$tn.'" value="'.$ts.'" />
              </div>';
    }

    /* ===== Shared validation helpers ===== */

    public function is_blocked_by_keywords( array $post ) : bool {
        $words = Helpers::csv_to_array( $this->settings['keyword_block'] );
        if ( ! $words ) return false;
        $hay = strtolower( wp_json_encode( $post ) );
        foreach ( $words as $w ) {
            if ( $w !== '' && strpos( $hay, strtolower($w) ) !== false ) return true;
        }
        return false;
    }

    public function is_rate_limited() : bool {
        $max = (int) $this->settings['rate_limit_max'];
        $win = (int) $this->settings['rate_limit_win'];
        if ( $max <= 0 ) return false;

        $ip   = Helpers::ip();
        $key  = 't709_as_rl_' . md5( $ip );
        $data = get_transient( $key );
        $now  = Helpers::now();

        if ( ! is_array( $data ) ) $data = [];

        // Purge old
        $data = array_filter( $data, function( $t ) use ( $now, $win ) {
            return ($now - (int)$t) < ( $win * 60 );
        } );

        // Add current hit
        $data[] = $now;
        set_transient( $key, $data, $win * 60 );

        return count( $data ) > $max;
    }

    private function t709_log_block( $reason, $data = [] ) {
    $log_dir  = WP_CONTENT_DIR . '/uploads';
    if ( ! is_dir( $log_dir ) ) { @wp_mkdir_p( $log_dir ); }

    $log_file = $log_dir . '/t709-antispam-log.jsonl'; // one JSON per line
    $entry = [
        'time'   => current_time( 'mysql' ),
        'ip'     => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'reason' => $reason,
        'ua'     => $_SERVER['HTTP_USER_AGENT'] ?? '',
    ];
    if ( ! empty( $data ) ) { $entry['fields'] = array_intersect_key( $data, array_flip(['your-name','your-email']) ); }

    @file_put_contents( $log_file, wp_json_encode( $entry ) . PHP_EOL, FILE_APPEND | LOCK_EX );
}


    public function violates_honeypot_or_time( array $post ) : bool {
        $hp = $this->settings['honeypot_name'] ?? 'website_url';
        $tn = $this->settings['timestamp_name'] ?? 't709_ts';
        $min = (int) $this->settings['min_seconds'];

        $hpv = isset($post[$hp]) ? trim( (string) $post[$hp] ) : '';
        $ts  = isset($post[$tn]) ? (int) $post[$tn] : 0;

        if ( $hpv !== '' ) return true;
        if ( $ts > 0 && ( Helpers::now() - $ts ) < $min ) return true;

        return false;
    }
}
