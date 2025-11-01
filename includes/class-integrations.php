<?php
namespace T709\Antispam;

if ( ! defined( 'ABSPATH' ) ) exit;

class Integrations {

    private array $settings;
    private Core  $core;

    public function __construct( array $settings ) {
        $this->settings = $settings;
        $this->core     = new Core( $settings ); // helper use only
    }

    public function init() {
        if ( empty($this->settings['enabled']) ) return;

        // Inject fields into WPForms DOM (extra reliability)
        add_action( 'wp_footer', [ $this, 'wpforms_inject' ] );

        // WPForms server-side validation
        add_action( 'wpforms_process', [ $this, 'wpforms_validate' ], 10, 3 );

        // CF7: add fields into HTML + spam validation
        add_filter( 'wpcf7_form_elements', [ $this, 'cf7_add_fields' ], 10, 1 );
        add_filter( 'wpcf7_spam', [ $this, 'cf7_validate' ], 10, 2 );
    }

    /* ---------- WPForms ---------- */

    public function wpforms_inject() { ?>
        <script>
          document.querySelectorAll('form.wpforms-form').forEach(function(f){
            if (!f.querySelector('input[name="<?php echo esc_js($this->settings['honeypot_name']); ?>"]')) {
              var hp=document.createElement('input'); hp.type='text'; hp.className='t709-hp';
              hp.name='<?php echo esc_js($this->settings['honeypot_name']); ?>'; hp.autocomplete='off'; hp.tabIndex='-1';
              var ts=document.createElement('input'); ts.type='hidden';
              ts.name='<?php echo esc_js($this->settings['timestamp_name']); ?>';
              ts.value=(typeof window.T709_ANTISPAM_TS!=='undefined'?window.T709_ANTISPAM_TS:Math.floor(Date.now()/1000));
              f.appendChild(hp); f.appendChild(ts);
            }
          });
        </script>
    <?php }

    public function wpforms_validate( $fields, $entry, $form_data ) {
        $post = $_POST;

        if ( $this->core->violates_honeypot_or_time( $post )
            || $this->core->is_rate_limited()
            || $this->core->is_blocked_by_keywords( $post ) ) {

            if ( function_exists('wpforms') ) {
                wpforms()->process->errors[ $form_data['id'] ]['footer']
                    = esc_html__( 'Spam detected. Please try again later.', 't709-antispam' );
            }
        }
    }

    /* ---------- Contact Form 7 ---------- */

    public function cf7_add_fields( $html ) {
        $ts = time();
        $hp = esc_attr( $this->settings['honeypot_name'] );
        $tn = esc_attr( $this->settings['timestamp_name'] );
        $inject = '<span class="t709-hp" aria-hidden="true">
                    <input type="text" name="'.$hp.'" value="" tabindex="-1" autocomplete="off" />
                    <input type="hidden" name="'.$tn.'" value="'.$ts.'" />
                   </span>';
        return preg_replace( '/<\/form>/i', $inject . '</form>', $html, 1 );
    }

    public function cf7_validate( $is_spam, $submission ) {
        if ( $is_spam || ! $submission ) return $is_spam;

        $data = $submission->get_posted_data();
        if ( ! is_array( $data ) ) return $is_spam;

        if ( $this->core->violates_honeypot_or_time( $data )
            || $this->core->is_rate_limited()
            || $this->core->is_blocked_by_keywords( $data ) ) {
            return true; // mark as spam
        }
        return false;
    }
}
