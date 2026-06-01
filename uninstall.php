<?php
// Exit if not called from WordPress uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

// Delete saved settings when the plugin is uninstalled.
delete_option( 'mbtt_settings' );