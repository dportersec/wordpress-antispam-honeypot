<?php
namespace T709\Antispam;

if ( ! defined( 'ABSPATH' ) ) exit;

class Admin {

    private string $log_file;

    public function __construct() {
        $this->log_file = WP_CONTENT_DIR . '/uploads/t709-antispam-log.jsonl';
    }

    public function init() : void {
        add_action( 'admin_menu', [ $this, 'menu' ] );
        add_action( 'admin_post_t709_as_clear', [ $this, 'handle_clear' ] );
        add_action( 'admin_post_t709_as_download', [ $this, 'handle_download' ] );
    }

    /* ---------- Menu ---------- */

    public function menu() : void {
        add_menu_page(
            __( 'T709 Anti-Spam', 't709-antispam' ),
            __( 'T709 Anti-Spam', 't709-antispam' ),
            'manage_options',
            't709-antispam',
            [ $this, 'render' ],
            'dashicons-shield',
            66
        );
    }

    /* ---------- Actions ---------- */

    public function handle_clear() : void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
        check_admin_referer( 't709_as_clear' );

        if ( file_exists( $this->log_file ) ) {
            // truncate
            $h = @fopen( $this->log_file, 'w' );
            if ( $h ) { fclose( $h ); }
        }
        wp_safe_redirect( admin_url( 'admin.php?page=t709-antispam&cleared=1' ) );
        exit;
    }

    public function handle_download() : void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
        check_admin_referer( 't709_as_download' );

        $filename = 't709-antispam-log-' . date('Ymd-His') . '.jsonl';
        header( 'Content-Type: application/json' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        if ( file_exists( $this->log_file ) ) {
            readfile( $this->log_file );
        }
        exit;
    }

    /* ---------- View ---------- */

    public function render() : void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );

        $entries = $this->read_tail( 200 ); // show last 200 lines
        $cleared = ! empty( $_GET['cleared'] );

        ?>
        <div class="wrap">
            <h1>🛡️ T709 Anti-Spam</h1>
            <p>Viewing the most recent blocked submissions. Log path:
                <code><?php echo esc_html( str_replace( ABSPATH, '/', $this->log_file ) ); ?></code>
            </p>

            <?php if ( $cleared ): ?>
                <div class="notice notice-success is-dismissible"><p>Log cleared.</p></div>
            <?php endif; ?>

            <p>
                <a class="button button-primary"
                   href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=t709_as_download' ), 't709_as_download' ) ); ?>">
                    Download Full Log
                </a>
                <a class="button button-secondary"
                   href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=t709_as_clear' ), 't709_as_clear' ) ); ?>"
                   onclick="return confirm('Clear the log? This cannot be undone.');">
                    Clear Log
                </a>
            </p>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th style="width:160px">Time</th>
                        <th style="width:130px">IP</th>
                        <th style="width:140px">Reason</th>
                        <th>User Agent (truncated)</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ( empty( $entries ) ): ?>
                    <tr><td colspan="4">No entries yet.</td></tr>
                <?php else: foreach ( $entries as $row ):
                    $d = json_decode( $row, true );
                    if ( ! is_array( $d ) ) continue; ?>
                    <tr>
                        <td><?php echo esc_html( $d['time'] ?? '' ); ?></td>
                        <td><?php echo esc_html( $d['ip'] ?? '' ); ?></td>
                        <td><code><?php echo esc_html( $d['reason'] ?? '' ); ?></code></td>
                        <td><?php
                            $ua = (string) ( $d['ua'] ?? '' );
                            echo esc_html( mb_strimwidth( $ua, 0, 160, '…' ) );
                        ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /** Read last N lines from the JSONL file efficiently */
    private function read_tail( int $lines = 200 ) : array {
        if ( ! file_exists( $this->log_file ) ) return [];
        $fp = @fopen( $this->log_file, 'r' );
        if ( ! $fp ) return [];

        $buffer = '';
        $chunk  = 4096;
        $pos    = -1;
        $count  = 0;

        fseek( $fp, 0, SEEK_END );
        $filesize = ftell( $fp );

        while ( $filesize > 0 && $count < $lines ) {
            $read = min( $chunk, $filesize );
            $filesize -= $read;
            fseek( $fp, $filesize );
            $buffer = fread( $fp, $read ) . $buffer;

            // count lines in buffer
            while ( ( $pos = strrpos( $buffer, PHP_EOL ) ) !== false ) {
                $line = substr( $buffer, $pos + 1 );
                $buffer = substr( $buffer, 0, $pos );
                if ( $line !== '' ) {
                    $out[] = $line;
                    $count++;
                    if ( $count >= $lines ) break 2;
                }
            }
            if ( $filesize === 0 && $buffer !== '' ) {
                $out[] = $buffer; // first line
            }
        }

        fclose( $fp );
        $out = isset( $out ) ? array_reverse( $out ) : [];
        return $out;
    }
}
