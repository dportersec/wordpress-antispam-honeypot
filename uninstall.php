<?php
// Remove settings on plugin deletion (not just deactivation).
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;
delete_option( 't709_as_settings' );
