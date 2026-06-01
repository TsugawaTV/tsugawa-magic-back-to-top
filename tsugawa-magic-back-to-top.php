<?php
/*
Plugin Name: Tsugawa Magic Back to Top
Description: A simple, lightweight, and beautiful Back to Top button. Features a pixel-perfect floating UI that seamlessly adapts to all screens.
Version: 1.0.0
Author: Toru Tsugawa
Text Domain: tsugawa-magic-back-to-top
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Arrow icons: Lucide Icons (https://lucide.dev/) - MIT License
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require 'lib/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/TsugawaTV/tsugawa-magic-back-to-top/',
    __FILE__,
    'tsugawa-magic-back-to-top'
);

class Magic_Back_To_Top {
    private $option_name = 'mbtt_settings';

    private $defaults = array(
        'position_x'       => 'right',
        'size_sp'          => 44,
        'pos_x_sp'         => 20,
        'pos_bottom_sp'    => 20,
        'size_pc'          => 54,
        'pos_x_pc'         => 30,
        'pos_bottom_pc'    => 30,
        'color_btn_bg'     => '#ffffff',
        'color_bg'         => '#e2e8f0',
        'color_progress'   => '#3b82f6',
        'color_arrow'      => '#475569',
        'color_hover'      => '#3b82f6',
        'arrow_type'       => 'type_a',
        'opacity_normal'   => 0.8,
        'opacity_hover'    => 1.0,
        'bg_only_opacity'  => 0,
        'z_index'          => 99,
        'scroll_threshold' => 100,
        'scroll_duration'  => 600,
        'hide_mobile'      => 0,
        'exclude_pages'    => '',
        'my_presets'       => array( 1 => array(), 2 => array(), 3 => array() ),
    );

    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_init', array( $this, 'handle_reset' ) );
        add_action( 'wp_head', array( $this, 'output_css_variables' ) );
        add_action( 'wp_footer', array( $this, 'output_frontend' ) );
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_action_links' ) );
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'tsugawa-magic-back-to-top', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    private function get_options() {
        $options = get_option( $this->option_name, array() );
        if ( isset($options['pos_right_sp']) && !isset($options['pos_x_sp']) ) $options['pos_x_sp'] = $options['pos_right_sp'];
        if ( isset($options['pos_right_pc']) && !isset($options['pos_x_pc']) ) $options['pos_x_pc'] = $options['pos_right_pc'];
        return wp_parse_args( $options, $this->defaults );
    }

    public function add_admin_menu() {
        add_menu_page( __( 'Tsugawa Magic Back to Top', 'tsugawa-magic-back-to-top' ), __( 'Magic B2T', 'tsugawa-magic-back-to-top' ), 'manage_options', 'tsugawa-magic-back-to-top', array( $this, 'render_admin_page' ), 'dashicons-arrow-up-alt2', 80 );
    }

    public function add_action_links( $links ) {
        array_unshift( $links, '<a href="admin.php?page=tsugawa-magic-back-to-top">' . esc_html__( 'Settings', 'tsugawa-magic-back-to-top' ) . '</a>' );
        return $links;
    }

    public function register_settings() {
        register_setting( 'mbtt_settings_group', $this->option_name, array( $this, 'sanitize_options' ) );
    }

    public function sanitize_options( $input ) {
        $sanitized = array();
        $sanitized['position_x']     = ( isset($input['position_x']) && $input['position_x'] === 'left' ) ? 'left' : 'right';
        $sanitized['size_sp']        = absint( $input['size_sp'] );
        $sanitized['pos_x_sp']       = absint( $input['pos_x_sp'] );
        $sanitized['pos_bottom_sp']  = absint( $input['pos_bottom_sp'] );
        $sanitized['size_pc']        = absint( $input['size_pc'] );
        $sanitized['pos_x_pc']       = absint( $input['pos_x_pc'] );
        $sanitized['pos_bottom_pc']  = absint( $input['pos_bottom_pc'] );
        
        $sanitized['color_btn_bg']   = sanitize_hex_color( $input['color_btn_bg'] );
        $sanitized['color_bg']       = sanitize_hex_color( $input['color_bg'] );
        $sanitized['color_progress'] = sanitize_hex_color( $input['color_progress'] );
        $sanitized['color_arrow']    = sanitize_hex_color( $input['color_arrow'] );
        $sanitized['color_hover']    = sanitize_hex_color( $input['color_hover'] );
        
        $valid_types = array( 'type_a', 'type_b', 'type_c', 'type_d', 'type_e' );
        $sanitized['arrow_type']     = in_array( $input['arrow_type'], $valid_types ) ? $input['arrow_type'] : 'type_a';
        
        $sanitized['opacity_normal'] = max( 0, min( 1, floatval( $input['opacity_normal'] ) ) );
        $sanitized['opacity_hover']  = max( 0, min( 1, floatval( $input['opacity_hover'] ) ) );
        $sanitized['bg_only_opacity']= !empty( $input['bg_only_opacity'] ) ? 1 : 0;
        $sanitized['z_index']        = intval( $input['z_index'] );
        $sanitized['scroll_threshold'] = max( 0, absint( $input['scroll_threshold'] ?? 100 ) );
        $mbtt_duration_input = isset( $input['scroll_duration'] ) && $input['scroll_duration'] !== '' ? $input['scroll_duration'] : 600;
        $sanitized['scroll_duration']  = max( 0, absint( $mbtt_duration_input ) );
        $sanitized['hide_mobile']      = !empty( $input['hide_mobile'] ) ? 1 : 0;
        $sanitized['exclude_pages']  = sanitize_text_field( $input['exclude_pages'] ?? '' );

        $sanitized['my_presets'] = array( 1 => array(), 2 => array(), 3 => array() );
        for ( $i = 1; $i <= 3; $i++ ) {
            if ( !empty( $input['my_presets'][$i]['color_btn_bg'] ) ) {
                $sanitized['my_presets'][$i] = array(
                    'color_btn_bg'   => sanitize_hex_color($input['my_presets'][$i]['color_btn_bg']),
                    'color_bg'       => sanitize_hex_color($input['my_presets'][$i]['color_bg']),
                    'color_progress' => sanitize_hex_color($input['my_presets'][$i]['color_progress']),
                    'color_arrow'    => sanitize_hex_color($input['my_presets'][$i]['color_arrow']),
                    'color_hover'    => sanitize_hex_color($input['my_presets'][$i]['color_hover']),
                    'opacity_normal' => max( 0, min( 1, floatval( $input['my_presets'][$i]['opacity_normal'] ?? 0.8 ) ) ),
                    'opacity_hover'  => max( 0, min( 1, floatval( $input['my_presets'][$i]['opacity_hover'] ?? 1.0 ) ) ),
                    'bg_only_opacity'=> !empty( $input['my_presets'][$i]['bg_only_opacity'] ) ? 1 : 0,
                );
            }
        }
        return $sanitized;
    }

    public function handle_reset() {
        if ( isset( $_POST['mbtt_reset'] ) ) {
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'tsugawa-magic-back-to-top' ) );
            }
            check_admin_referer( 'mbtt_reset_nonce', 'mbtt_reset_nonce_field' );

            $current_options = get_option( $this->option_name, array() );
            $my_presets = isset($current_options['my_presets']) ? $current_options['my_presets'] : $this->defaults['my_presets'];

            $new_options = $this->defaults;
            $new_options['my_presets'] = $my_presets;
            update_option( $this->option_name, $new_options );
            
            wp_safe_redirect( admin_url( 'admin.php?page=tsugawa-magic-back-to-top&reset=true' ) );
            exit;
        }
    }

    public function render_admin_page() {
        $options = $this->get_options();
        $svgs = $this->get_svgs();
        $export_code = $this->generate_export_code( $options );

        $presets = array(
            array('name' => _x( 'Clear', 'Color Preset', 'tsugawa-magic-back-to-top' ), 'btnBg' => '#ffffff', 'bg' => '#e2e8f0', 'prog' => '#3b82f6', 'arrow' => '#475569', 'hover' => '#3b82f6'),
            array('name' => __( 'Dark', 'tsugawa-magic-back-to-top' ), 'btnBg' => '#1e293b', 'bg' => '#334155', 'prog' => '#38bdf8', 'arrow' => '#94a3b8', 'hover' => '#ffffff'),
            array('name' => __( 'Gold', 'tsugawa-magic-back-to-top' ), 'btnBg' => '#ffffff', 'bg' => '#f3f4f6', 'prog' => '#d97706', 'arrow' => '#4b5563', 'hover' => '#b45309'),
            array('name' => __( 'Solid Blue', 'tsugawa-magic-back-to-top' ), 'btnBg' => '#3b82f6', 'bg' => '#2563eb', 'prog' => '#ffffff', 'arrow' => '#bfdbfe', 'hover' => '#ffffff'),
            array('name' => __( 'Black', 'tsugawa-magic-back-to-top' ), 'btnBg' => '#000000', 'bg' => '#333333', 'prog' => '#ffffff', 'arrow' => '#aaaaaa', 'hover' => '#ffffff'),
        );
        ?>
        <style>
            .mbtt-wrapper-centered { max-width: 1050px; margin-left: auto; margin-right: auto; padding-top: 10px; }
            .mbtt-layout { display: flex; gap: 40px; align-items: flex-start; margin-top: 20px; }
            .mbtt-main { flex: 1; min-width: 0; z-index: 1; }
            .mbtt-sidebar { width: 320px; position: sticky; top: 40px; z-index: 10; }
            
            .mbtt-preview-title-badge { display: block; text-align: center; margin-bottom: 10px; transition: all 0.3s ease; }
            .mbtt-preview-title-badge strong { font-size: 14px; font-weight: 600; color: #1e293b; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 20px; padding: 6px 16px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.05), inset 0 1px 0 rgba(255,255,255,0.8); background-image: linear-gradient(180deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 100%); transition: all 0.3s ease; }
            .mbtt-preview-title-badge strong::before { content: ''; display: inline-block; width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2); animation: mbtt-pulse 2s infinite; }
            @keyframes mbtt-pulse { 0% { box-shadow: 0 0 0 0px rgba(16, 185, 129, 0.4); } 70% { box-shadow: 0 0 0 5px rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0px rgba(16, 185, 129, 0); } }

            .mbtt-preview-container { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; transition: all 0.3s ease; }
            .mbtt-preview-desc { font-size: 12px; color: #666; text-align: center; margin-bottom: 15px; margin-top: 0;}
            .mbtt-preview-box { background: url('data:image/svg+xml;utf8,<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg"><rect width="10" height="10" fill="%23e2e8f0"/><rect x="10" y="10" width="10" height="10" fill="%23e2e8f0"/></svg>') repeat; border: 1px solid #ccc; border-radius: 8px; padding: 40px; text-align: center; display: flex; justify-content: center; align-items: center; min-height: 200px; box-shadow: inset 0 2px 10px rgba(0,0,0,0.05); background-color: #f8fafc; margin-bottom: 20px; transition: all 0.3s ease; }
            
            #mbtt-live-btn { position: relative; border-radius: 50%; border: none; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); cursor: pointer; transition: all 0.3s ease; width: var(--mbtt-size); height: var(--mbtt-size); background: var(--mbtt-actual-bg-normal); opacity: var(--mbtt-actual-opacity-normal); padding: 0; margin: 0; }
            #mbtt-live-btn:hover { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2); opacity: var(--mbtt-actual-opacity-hover); background: var(--mbtt-actual-bg-hover); }
            #mbtt-live-btn .mbtt-ring { position: absolute; top: 0; left: 0; width: 100%; height: 100%; transform: rotate(-90deg); margin: 0; padding: 0; }
            #mbtt-live-btn .mbtt-ring circle { fill: transparent; stroke-width: 3; }
            #mbtt-live-btn .mbtt-ring-bg { stroke: var(--mbtt-color-bg); }
            #mbtt-live-btn .mbtt-ring-progress { stroke: var(--mbtt-color-progress); stroke-dasharray: 150; stroke-dashoffset: 40; stroke-linecap: round; }
            #mbtt-live-btn .mbtt-arrow { width: 45%; height: 45%; color: var(--mbtt-color-arrow); position: relative; z-index: 2; transition: color 0.3s ease; margin: 0 !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; }
            #mbtt-live-btn:hover .mbtt-arrow { color: var(--mbtt-color-hover); }

            .mbtt-section-box { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
            .mbtt-section-title { margin-top: 0; margin-bottom: 15px; font-size: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; }
            
            .mbtt-preset-grid { display: flex; gap: 15px; flex-wrap: wrap; margin-top: 10px; }
            .mbtt-preset-item { display: flex; flex-direction: column; align-items: center; gap: 8px; cursor: pointer; padding: 10px; border: 2px solid transparent; border-radius: 8px; transition: all 0.2s; }
            .mbtt-preset-item:hover { background: #f1f5f9; border-color: #cbd5e1; }
            
            .mbtt-my-preset-box { border: 1px solid #cbd5e1; padding: 15px 10px; border-radius: 8px; background: #f8fafc; display: flex; flex-direction: column; align-items: center; gap: 8px; width: 90px; }
            .mbtt-preset-visual { width: 44px; height: 44px; border-radius: 50%; position: relative; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); transition: all 0.3s ease;}
            .mbtt-preset-visual.empty { box-shadow: none; border: 2px dashed #cbd5e1; background: transparent !important; }
            .mbtt-preset-visual svg { width: 45%; height: 45%; position: relative; z-index: 2; }
            .mbtt-preset-visual::before { content: ''; position: absolute; top: 2px; left: 2px; right: 2px; bottom: 2px; border-radius: 50%; border: 3px solid var(--my-track, transparent); opacity: 1; }
            .mbtt-preset-visual::after { content: ''; position: absolute; top: 2px; left: 2px; right: 2px; bottom: 2px; border-radius: 50%; border: 3px solid transparent; border-top-color: var(--my-prog, transparent); border-right-color: var(--my-prog, transparent); transform: rotate(45deg); }

            .mbtt-input-group label { display: inline-block; margin-right: 15px; margin-bottom: 8px; white-space: nowrap; }
            .mbtt-color-group { display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 15px; }
            .mbtt-color-group label { display: flex; align-items: center; gap: 5px; cursor: pointer; }
            .mbtt-btn-save-sticky { width: 100%; height: 50px; font-size: 16px !important; font-weight: bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            
            .mbtt-mobile-only-save { display: none; }

            /* =====================================================================
               Mobile floating UI: scroll-triggered & fully floating fixed preview
               ===================================================================== */
            @media screen and (max-width: 782px) {
                .mbtt-layout { flex-direction: column; }
                .mbtt-main { order: 2; width: 100%; z-index: 1; margin-top: 10px; }
                
                .mbtt-sidebar { display: contents; }
                
                /* Full-spec state before scrolling (flows naturally upward with scroll) */
                .mbtt-sidebar-preview {
                    order: 1;
                    position: relative; /* Natural document flow, not fixed */
                    width: 100%;
                    box-sizing: border-box; /* Prevent horizontal wobble */
                    z-index: 998;
                }

                .mbtt-preview-container {
                    background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
                    padding: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
                    width: 100%;
                    box-sizing: border-box;
                    display: flex; flex-direction: column; align-items: center;
                }

                /* --- Scroll-triggered floating (fixed top-right) state --- */
                .mbtt-sidebar-preview.is-floating .mbtt-preview-container {
                    position: fixed; /* Fixed position resilient to iOS UI resize */
                    top: 56px; /* WP admin bar height */
                    right: 15px; /* Snap to top-right corner */
                    z-index: 99999;
                    background: transparent; border: none; box-shadow: none;
                    padding: 0; width: auto; pointer-events: none; /* Allow interaction with content below */
                    align-items: flex-end; /* Align right */
                }

                .mbtt-sidebar-preview.is-floating .mbtt-preview-title-badge {
                    margin-bottom: 5px; pointer-events: auto; /* Only this element is tappable */
                }
                .mbtt-sidebar-preview.is-floating .mbtt-preview-title-badge strong {
                    font-size: 11px; padding: 4px 10px;
                    background: rgba(255, 255, 255, 0.85); /* Glass cushion effect */
                    border: 1px solid rgba(0,0,0,0.05); border-radius: 20px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1); color: #333;
                }

                /* Remove checkerboard background and gray border completely */
                .mbtt-sidebar-preview.is-floating .mbtt-preview-box {
                    background: none !important; border: none !important; box-shadow: none !important;
                    padding: 0 !important; min-height: auto !important; margin-bottom: 0 !important;
                    pointer-events: auto;
                }
                
                .mbtt-sidebar-preview.is-floating .mbtt-preview-desc { display: none; }
                /* ----------------------------------------------------------- */

                .mbtt-desktop-only-save { display: none; }
                
                .mbtt-preset-grid { justify-content: center; gap: 10px; }
                .mbtt-color-group { flex-direction: column; gap: 12px; }
                .mbtt-color-group label { background: #f8fafc; padding: 10px; border-radius: 6px; border: 1px solid #e2e8f0; }
                
                .mbtt-mobile-only-save {
                    display: block; position: fixed; bottom: 0; left: 0; right: 0;
                    background: #ffffff; padding: 12px 20px; box-shadow: 0 -4px 15px rgba(0,0,0,0.1);
                    z-index: 1000; border-top: 1px solid #e2e8f0;
                }
                .mbtt-mobile-only-save .button { width: 100%; height: 48px; font-size: 16px; }
                .mbtt-wrapper-centered { padding-bottom: 80px; position: relative; } 
            }

            /* Handle very small mobile screens where WP admin bar disappears (iOS notch consideration) */
            @media screen and (max-width: 600px) {
                .mbtt-sidebar-preview.is-floating .mbtt-preview-container {
                    /* Ultimate positioning with full iOS safe area consideration */
                    top: max(15px, env(safe-area-inset-top)); 
                }
            }
        </style>

        <div class="wrap mbtt-wrapper-centered">
            <h1 class="mbtt-admin-title"><?php esc_html_e( 'Tsugawa Magic Back to Top', 'tsugawa-magic-back-to-top' ); ?></h1>
            
            <?php
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 'reset' parameter originates from handle_reset() which verifies nonce via check_admin_referer().
            if ( isset( $_GET['reset'] ) && 'true' === sanitize_text_field( wp_unslash( $_GET['reset'] ) ) ) {
                echo '<div class="notice notice-info is-dismissible"><p><strong>' . esc_html__( 'Settings have been reset to defaults.', 'tsugawa-magic-back-to-top' ) . '</strong><br><small>' . esc_html__( 'Your My Colors are preserved.', 'tsugawa-magic-back-to-top' ) . '</small></p></div>';
            } elseif ( isset( $_GET['mbtt-action'], $_GET['_mbtt_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_mbtt_nonce'] ) ), 'mbtt_preset_action' ) ) {
                $mbtt_action = sanitize_text_field( wp_unslash( $_GET['mbtt-action'] ) );
                $slot = isset( $_GET['slot'] ) ? intval( $_GET['slot'] ) : 0;
                if ( 'save-slot' === $mbtt_action ) {
                    /* translators: %s: slot number */
                    echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html( sprintf( __( 'Colors and opacity saved to slot %s.', 'tsugawa-magic-back-to-top' ), $slot ) ) . '</strong></p></div>';
                } elseif ( 'delete-slot' === $mbtt_action ) {
                    /* translators: %s: slot number */
                    echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html( sprintf( __( 'Slot %s settings have been cleared.', 'tsugawa-magic-back-to-top' ), $slot ) ) . '</strong></p></div>';
                }
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 'settings-updated' parameter is set by WordPress core options.php after save.
            } elseif ( isset( $_GET['settings-updated'] ) && 'true' === sanitize_text_field( wp_unslash( $_GET['settings-updated'] ) ) ) {
                echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Settings saved.', 'tsugawa-magic-back-to-top' ) . '</strong></p></div>';
            }
            ?>
            
            <form method="post" action="options.php" id="mbtt-form">
                <?php settings_fields( 'mbtt_settings_group' ); ?>
                
                <div class="mbtt-layout">
                    <div class="mbtt-main">
                        
                        <div class="mbtt-section-box">
                            <h3 class="mbtt-section-title"><?php esc_html_e( 'Color Presets', 'tsugawa-magic-back-to-top' ); ?></h3>
                            <div class="mbtt-preset-grid">
                                <?php foreach ($presets as $p): ?>
                                <div class="mbtt-preset-item" onclick="setMbttColors('<?php echo esc_attr( $p['btnBg'] ); ?>', '<?php echo esc_attr( $p['bg'] ); ?>', '<?php echo esc_attr( $p['prog'] ); ?>', '<?php echo esc_attr( $p['arrow'] ); ?>', '<?php echo esc_attr( $p['hover'] ); ?>')">
                                    <div class="mbtt-preset-visual" style="background: <?php echo esc_attr( $p['btnBg'] ); ?>; --my-track: <?php echo esc_attr( $p['bg'] ); ?>; --my-prog: <?php echo esc_attr( $p['prog'] ); ?>;">
                                        <svg viewBox="0 0 24 24" aria-hidden="true" style="color: <?php echo esc_attr( $p['arrow'] ); ?>;">
                                            <path d="m17 16-5-8-5 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </div>
                                    <span style="font-size:12px; font-weight:600; color:#475569;"><?php echo esc_html( $p['name'] ); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mbtt-section-box" style="border-left: 4px solid #3b82f6;">
                            <h3 class="mbtt-section-title"><?php esc_html_e( 'Color Settings', 'tsugawa-magic-back-to-top' ); ?></h3>
                            <div class="mbtt-color-group">
                                <label><input type="color" class="mbtt-live-input" id="mbtt_color_btn_bg" name="<?php echo esc_attr( $this->option_name ); ?>[color_btn_bg]" value="<?php echo esc_attr( $options['color_btn_bg'] ); ?>"> <?php esc_html_e( 'Button Background', 'tsugawa-magic-back-to-top' ); ?></label>
                                <label><input type="color" class="mbtt-live-input" id="mbtt_color_bg" name="<?php echo esc_attr( $this->option_name ); ?>[color_bg]" value="<?php echo esc_attr( $options['color_bg'] ); ?>"> <?php esc_html_e( 'Ring Track', 'tsugawa-magic-back-to-top' ); ?></label>
                                <label><input type="color" class="mbtt-live-input" id="mbtt_color_progress" name="<?php echo esc_attr( $this->option_name ); ?>[color_progress]" value="<?php echo esc_attr( $options['color_progress'] ); ?>"> <?php esc_html_e( 'Progress', 'tsugawa-magic-back-to-top' ); ?></label>
                                <label><input type="color" class="mbtt-live-input" id="mbtt_color_arrow" name="<?php echo esc_attr( $this->option_name ); ?>[color_arrow]" value="<?php echo esc_attr( $options['color_arrow'] ); ?>"> <?php esc_html_e( 'Arrow', 'tsugawa-magic-back-to-top' ); ?></label>
                                <label><input type="color" class="mbtt-live-input" id="mbtt_color_hover" name="<?php echo esc_attr( $this->option_name ); ?>[color_hover]" value="<?php echo esc_attr( $options['color_hover'] ); ?>"> <?php esc_html_e( 'Arrow (Hover)', 'tsugawa-magic-back-to-top' ); ?></label>
                            </div>

                            <div style="margin-bottom: 15px;">
                                <label style="display:inline-block; width:150px;"><?php esc_html_e( 'Normal Opacity:', 'tsugawa-magic-back-to-top' ); ?> <input type="range" class="mbtt-live-input" name="<?php echo esc_attr( $this->option_name ); ?>[opacity_normal]" value="<?php echo esc_attr( $options['opacity_normal'] ); ?>" min="0.1" max="1.0" step="0.1" oninput="this.nextElementSibling.value = this.value"> <output><?php echo esc_attr( $options['opacity_normal'] ); ?></output></label>
                                <label style="display:inline-block; width:150px;"><?php esc_html_e( 'Hover Opacity:', 'tsugawa-magic-back-to-top' ); ?> <input type="range" class="mbtt-live-input" name="<?php echo esc_attr( $this->option_name ); ?>[opacity_hover]" value="<?php echo esc_attr( $options['opacity_hover'] ); ?>" min="0.1" max="1.0" step="0.1" oninput="this.nextElementSibling.value = this.value"> <output><?php echo esc_attr( $options['opacity_hover'] ); ?></output></label>
                            </div>
                            <label style="font-weight:bold; color:#0369a1; background:#e0f2fe; padding:5px 10px; border-radius:4px; display:inline-block; margin-bottom:20px;">
                                <input type="checkbox" class="mbtt-live-input" id="mbtt_bg_only_opacity" name="<?php echo esc_attr( $this->option_name ); ?>[bg_only_opacity]" value="1" <?php checked($options['bg_only_opacity'], 1); ?>>
                                <?php esc_html_e( 'Transparent background only (keep icon and ring fully opaque)', 'tsugawa-magic-back-to-top' ); ?>
                            </label>

                            <div style="background:#f1f5f9; padding: 15px; border-radius: 8px;">
                                <h4 style="margin-top:0; margin-bottom:10px; font-size:14px;"><?php esc_html_e( 'Save Current Colors & Opacity to My Colors', 'tsugawa-magic-back-to-top' ); ?></h4>
                                <div class="mbtt-preset-grid" style="margin-top:0;">
                                    <?php for ($i = 1; $i <= 3; $i++): 
                                        $mp = $options['my_presets'][$i];
                                        $is_empty = empty($mp['color_btn_bg']);
                                    ?>
                                    <div class="mbtt-my-preset-box">
                                        <?php if (!$is_empty): ?>
                                            <div id="mbtt-visual-slot-<?php echo esc_attr( $i ); ?>" class="mbtt-preset-visual" style="background: <?php echo esc_attr( $mp['color_btn_bg'] ); ?>; --my-track: <?php echo esc_attr( $mp['color_bg'] ); ?>; --my-prog: <?php echo esc_attr( $mp['color_progress'] ); ?>; cursor: pointer;" 
                                                 onclick="setMbttColors('<?php echo esc_attr( $mp['color_btn_bg'] ); ?>', '<?php echo esc_attr( $mp['color_bg'] ); ?>', '<?php echo esc_attr( $mp['color_progress'] ); ?>', '<?php echo esc_attr( $mp['color_arrow'] ); ?>', '<?php echo esc_attr( $mp['color_hover'] ); ?>', '<?php echo esc_attr( $mp['opacity_normal'] ?? 0.8 ); ?>', '<?php echo esc_attr( $mp['opacity_hover'] ?? 1.0 ); ?>', '<?php echo esc_attr( $mp['bg_only_opacity'] ?? 0 ); ?>')" title="<?php esc_attr_e( 'Load this preset', 'tsugawa-magic-back-to-top' ); ?>">
                                                <svg viewBox="0 0 24 24" aria-hidden="true" style="color: <?php echo esc_attr( $mp['color_arrow'] ); ?>;">
                                                    <path d="m17 16-5-8-5 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                            </div>
                                            <div style="display:flex; gap:5px; margin-top:5px;">
                                                <button type="button" class="button button-small" onclick="saveToMyPreset(<?php echo esc_attr( $i ); ?>)" style="font-size:10px; padding:0 5px;" title="<?php esc_attr_e( 'Overwrite with current settings', 'tsugawa-magic-back-to-top' ); ?>"><?php esc_html_e( 'Save', 'tsugawa-magic-back-to-top' ); ?></button>
                                                <button type="button" class="button button-small" onclick="deleteMyPreset(<?php echo esc_attr( $i ); ?>)" style="font-size:10px; padding:0 5px; color:#dc2626; border-color:#fca5a5;" title="<?php esc_attr_e( 'Clear this slot', 'tsugawa-magic-back-to-top' ); ?>"><?php esc_html_e( 'Clear', 'tsugawa-magic-back-to-top' ); ?></button>
                                            </div>
                                        <?php else: ?>
                                            <div id="mbtt-visual-slot-<?php echo esc_attr( $i ); ?>" class="mbtt-preset-visual empty">
                                                <span style="font-size:10px; color:#94a3b8;"><?php esc_html_e( 'Empty', 'tsugawa-magic-back-to-top' ); ?></span>
                                            </div>
                                            <button type="button" class="button button-small" onclick="saveToMyPreset(<?php echo esc_attr( $i ); ?>)" style="font-size:10px; margin-top:5px;"><?php esc_html_e( 'Save Here', 'tsugawa-magic-back-to-top' ); ?></button>
                                        <?php endif; ?>
                                        
                                        <input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[my_presets][<?php echo esc_attr( $i ); ?>][color_btn_bg]" id="my_preset_<?php echo esc_attr( $i ); ?>_color_btn_bg" value="<?php echo esc_attr($mp['color_btn_bg'] ?? ''); ?>">
                                        <input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[my_presets][<?php echo esc_attr( $i ); ?>][color_bg]" id="my_preset_<?php echo esc_attr( $i ); ?>_color_bg" value="<?php echo esc_attr($mp['color_bg'] ?? ''); ?>">
                                        <input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[my_presets][<?php echo esc_attr( $i ); ?>][color_progress]" id="my_preset_<?php echo esc_attr( $i ); ?>_color_progress" value="<?php echo esc_attr($mp['color_progress'] ?? ''); ?>">
                                        <input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[my_presets][<?php echo esc_attr( $i ); ?>][color_arrow]" id="my_preset_<?php echo esc_attr( $i ); ?>_color_arrow" value="<?php echo esc_attr($mp['color_arrow'] ?? ''); ?>">
                                        <input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[my_presets][<?php echo esc_attr( $i ); ?>][color_hover]" id="my_preset_<?php echo esc_attr( $i ); ?>_color_hover" value="<?php echo esc_attr($mp['color_hover'] ?? ''); ?>">
                                        <input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[my_presets][<?php echo esc_attr( $i ); ?>][opacity_normal]" id="my_preset_<?php echo esc_attr( $i ); ?>_opacity_normal" value="<?php echo esc_attr($mp['opacity_normal'] ?? ''); ?>">
                                        <input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[my_presets][<?php echo esc_attr( $i ); ?>][opacity_hover]" id="my_preset_<?php echo esc_attr( $i ); ?>_opacity_hover" value="<?php echo esc_attr($mp['opacity_hover'] ?? ''); ?>">
                                        <input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[my_presets][<?php echo esc_attr( $i ); ?>][bg_only_opacity]" id="my_preset_<?php echo esc_attr( $i ); ?>_bg_only_opacity" value="<?php echo esc_attr($mp['bg_only_opacity'] ?? ''); ?>">
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mbtt-section-box">
                            <h3 class="mbtt-section-title"><?php esc_html_e( 'Size, Position & Shape', 'tsugawa-magic-back-to-top' ); ?></h3>
                            <table class="form-table" style="margin-top:0;">
                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Button Position', 'tsugawa-magic-back-to-top' ); ?></th>
                                    <td>
                                        <select name="<?php echo esc_attr( $this->option_name ); ?>[position_x]" id="mbtt_position_x" style="min-width: 150px;">
                                            <option value="right" <?php selected($options['position_x'], 'right'); ?>><?php esc_html_e( 'Right', 'tsugawa-magic-back-to-top' ); ?></option>
                                            <option value="left" <?php selected($options['position_x'], 'left'); ?>><?php esc_html_e( 'Left', 'tsugawa-magic-back-to-top' ); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Size & Position (Mobile)', 'tsugawa-magic-back-to-top' ); ?></th>
                                    <td class="mbtt-input-group">
                                        <label><?php esc_html_e( 'Size:', 'tsugawa-magic-back-to-top' ); ?> <input type="number" class="mbtt-live-input" name="<?php echo esc_attr( $this->option_name ); ?>[size_sp]" value="<?php echo esc_attr( $options['size_sp'] ); ?>" style="width:70px;"> px</label>
                                        <label><?php esc_html_e( 'Bottom:', 'tsugawa-magic-back-to-top' ); ?> <input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[pos_bottom_sp]" value="<?php echo esc_attr( $options['pos_bottom_sp'] ); ?>" style="width:70px;"> px</label>
                                        <label><?php esc_html_e( 'Side:', 'tsugawa-magic-back-to-top' ); ?> <input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[pos_x_sp]" value="<?php echo esc_attr( $options['pos_x_sp'] ); ?>" style="width:70px;"> px</label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[hide_mobile]" value="1" <?php checked($options['hide_mobile'], 1); ?>>
                                            <?php esc_html_e( 'Hide button on mobile (768px and below)', 'tsugawa-magic-back-to-top' ); ?>
                                        </label>
                                        <p class="description"><?php esc_html_e( 'Check this if the button interferes with fixed footer menus on mobile.', 'tsugawa-magic-back-to-top' ); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Size & Position (Desktop)', 'tsugawa-magic-back-to-top' ); ?></th>
                                    <td class="mbtt-input-group">
                                        <label><?php esc_html_e( 'Size:', 'tsugawa-magic-back-to-top' ); ?> <input type="number" class="mbtt-live-input" name="<?php echo esc_attr( $this->option_name ); ?>[size_pc]" value="<?php echo esc_attr( $options['size_pc'] ); ?>" style="width:70px;"> px</label>
                                        <label><?php esc_html_e( 'Bottom:', 'tsugawa-magic-back-to-top' ); ?> <input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[pos_bottom_pc]" value="<?php echo esc_attr( $options['pos_bottom_pc'] ); ?>" style="width:70px;"> px</label>
                                        <label><?php esc_html_e( 'Side:', 'tsugawa-magic-back-to-top' ); ?> <input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[pos_x_pc]" value="<?php echo esc_attr( $options['pos_x_pc'] ); ?>" style="width:70px;"> px</label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Arrow Shape', 'tsugawa-magic-back-to-top' ); ?></th>
                                    <td>
                                        <div style="display:flex; gap:15px; flex-wrap:wrap;">
                                            <?php foreach ( $svgs as $key => $svg ) : ?>
                                            <label style="display:flex; flex-direction:column; align-items:center; cursor:pointer;">
                                                <svg viewBox="0 0 24 24" style="width:40px; height:40px; border:1px solid #ccc; border-radius:8px; padding:5px; margin-bottom:5px; background:#fff;">
                                                    <?php echo wp_kses( $svg, $this->get_svg_allowed_tags() ); ?>
                                                </svg>
                                                <input type="radio" class="mbtt-live-input" name="<?php echo esc_attr( $this->option_name ); ?>[arrow_type]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $options['arrow_type'], $key ); ?>>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Display Trigger', 'tsugawa-magic-back-to-top' ); ?></th>
                                    <td><label><input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[scroll_threshold]" value="<?php echo esc_attr( $options['scroll_threshold'] ); ?>" style="width:100px;" min="0"> <?php esc_html_e( 'px scrolled to show (default: 100)', 'tsugawa-magic-back-to-top' ); ?></label></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Scroll Speed', 'tsugawa-magic-back-to-top' ); ?></th>
                                    <td>
                                        <select name="<?php echo esc_attr( $this->option_name ); ?>[scroll_duration]">
                                            <option value="200" <?php selected( $options['scroll_duration'], 200 ); ?>><?php esc_html_e( 'Fast (0.2s)', 'tsugawa-magic-back-to-top' ); ?></option>
                                            <option value="400" <?php selected( $options['scroll_duration'], 400 ); ?>><?php esc_html_e( 'Slightly Fast (0.4s)', 'tsugawa-magic-back-to-top' ); ?></option>
                                            <option value="600" <?php selected( $options['scroll_duration'], 600 ); ?>><?php esc_html_e( 'Normal (0.6s)', 'tsugawa-magic-back-to-top' ); ?></option>
                                            <option value="1000" <?php selected( $options['scroll_duration'], 1000 ); ?>><?php esc_html_e( 'Slow (1s)', 'tsugawa-magic-back-to-top' ); ?></option>
                                            <option value="1500" <?php selected( $options['scroll_duration'], 1500 ); ?>><?php esc_html_e( 'Very Slow (1.5s)', 'tsugawa-magic-back-to-top' ); ?></option>
                                        </select>
                                        <p class="description"><?php esc_html_e( 'Animation speed when scrolling back to top.', 'tsugawa-magic-back-to-top' ); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Z-index', 'tsugawa-magic-back-to-top' ); ?></th>
                                    <td><label><input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[z_index]" value="<?php echo esc_attr( $options['z_index'] ); ?>" style="width:100px;"> <?php esc_html_e( '(default: 99)', 'tsugawa-magic-back-to-top' ); ?></label></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Exclude Pages', 'tsugawa-magic-back-to-top' ); ?></th>
                                    <td>
                                        <input type="text" name="<?php echo esc_attr( $this->option_name ); ?>[exclude_pages]" value="<?php echo esc_attr( $options['exclude_pages'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. 123, about, contact (use "front" for front page)', 'tsugawa-magic-back-to-top' ); ?>">
                                        <p class="description">
                                            <?php
                                            echo wp_kses(
                                                __( 'Enter <strong>page IDs</strong> or <strong>slugs</strong> separated by commas to hide the button on those pages.', 'tsugawa-magic-back-to-top' ),
                                                array( 'strong' => array() )
                                            );
                                            ?><br>
                                            <?php
                                            echo wp_kses(
                                                __( 'To exclude the front page, enter <code>front</code>.', 'tsugawa-magic-back-to-top' ),
                                                array( 'code' => array() )
                                            );
                                            ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mbtt-sidebar">
                        <div class="mbtt-sidebar-preview" id="mbtt-mobile-preview-area">
                            <div class="mbtt-preview-container">
                                <span class="mbtt-preview-title-badge">
                                    <strong><?php esc_html_e( 'Live Preview', 'tsugawa-magic-back-to-top' ); ?></strong>
                                </span>
                                
                                <div class="mbtt-preview-box" id="mbtt-preview-wrap">
                                    <div id="mbtt-live-btn">
                                        <svg class="mbtt-ring" viewBox="0 0 54 54" aria-hidden="true">
                                            <circle class="mbtt-ring-bg" cx="27" cy="27" r="24"></circle>
                                            <circle class="mbtt-ring-progress" cx="27" cy="27" r="24"></circle>
                                        </svg>
                                        <svg class="mbtt-arrow" id="mbtt-live-arrow" viewBox="0 0 24 24" aria-hidden="true">
                                        </svg>
                                    </div>
                                </div>
                                
                                <p class="mbtt-preview-desc"><?php esc_html_e( 'Changes are reflected in real time.', 'tsugawa-magic-back-to-top' ); ?></p>
                                
                                <div class="mbtt-desktop-only-save" style="margin-top: 15px;">
                                    <?php submit_button( __( 'Save Settings', 'tsugawa-magic-back-to-top' ), 'primary mbtt-btn-save-sticky', 'submit', false ); ?>
                                </div>
                            </div>
                            <div id="mbtt-scroll-anchor" style="height: 1px; width: 100%;"></div>
                        </div>
                    </div>
                </div>

                <div class="mbtt-mobile-only-save">
                    <?php submit_button( __( 'Save Settings', 'tsugawa-magic-back-to-top' ), 'primary mbtt-btn-save-sticky', 'submit', false ); ?>
                </div>

            </form>
            
            <div class="mbtt-bottom-section" style="padding-top: 30px;">
                <div class="mbtt-warnings" style="margin-bottom: 20px; padding: 15px; background: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 4px; font-size: 13px; color: #78350f; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    <strong style="display:flex; align-items:center; gap:5px;"><span class="dashicons dashicons-warning" style="color:#f59e0b;"></span> <?php esc_html_e( 'If changes are not reflected', 'tsugawa-magic-back-to-top' ); ?></strong>
                    <span style="display:block; margin-top:8px; line-height:1.5;"><?php
                        echo wp_kses(
                            sprintf(
                                /* translators: %s: "cache plugin" in bold */
                                __( 'If the button does not appear after saving, please clear your %s or server cache.', 'tsugawa-magic-back-to-top' ),
                                '<strong style="color:#b45309;">' . esc_html__( 'cache plugin', 'tsugawa-magic-back-to-top' ) . '</strong>'
                            ),
                            array( 'strong' => array( 'style' => array() ) )
                        );
                    ?></span>
                </div>

                <form method="post" action="" style="margin-bottom: 30px;">
                    <?php wp_nonce_field( 'mbtt_reset_nonce', 'mbtt_reset_nonce_field' ); ?>
                    <input type="submit" name="mbtt_reset" value="<?php esc_attr_e( 'Reset to Defaults (My Colors preserved)', 'tsugawa-magic-back-to-top' ); ?>" class="button button-secondary" onclick="return confirm('<?php echo esc_js( __( 'Reset all settings to defaults? Your My Colors will be preserved.', 'tsugawa-magic-back-to-top' ) ); ?>');">
                </form>

                <hr>
                <h2><?php esc_html_e( 'Export Code for External Sites', 'tsugawa-magic-back-to-top' ); ?></h2>
                <p><?php
                    echo wp_kses(
                        sprintf(
                            /* translators: %s: </body> tag in code format */
                            __( 'The code below reflects your saved settings. Paste it just before the %s tag in non-WordPress static HTML pages.', 'tsugawa-magic-back-to-top' ),
                            '<code>&lt;/body&gt;</code>'
                        ),
                        array( 'code' => array() )
                    );
                ?></p>
                <textarea readonly style="width: 100%; height: 120px; font-family: monospace; background: #f1f5f9; padding: 15px; border-radius: 4px;" onclick="this.select();"><?php echo esc_textarea( $export_code ); ?></textarea>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const refererInput = document.querySelector('input[name="_wp_http_referer"]');
            if (refererInput) {
                let val = refererInput.value;
                val = val.replace(/&?mbtt-action=[^&]*/g, '');
                val = val.replace(/&?slot=[^&]*/g, '');
                val = val.replace(/&?_mbtt_nonce=[^&]*/g, '');
                val = val.replace(/&?reset=true/g, ''); 
                refererInput.value = val;
            }

            if (window.history && window.history.replaceState) {
                const url = new URL(window.location.href);
                if (url.searchParams.has('mbtt-action') || url.searchParams.has('reset')) {
                    url.searchParams.delete('mbtt-action');
                    url.searchParams.delete('slot');
                    url.searchParams.delete('_mbtt_nonce');
                    url.searchParams.delete('reset'); 
                    window.history.replaceState({}, document.title, url.toString());
                }
            }

            updateLivePreview();

            // Mobile scroll-based floating UI
            if (window.innerWidth <= 782) {
                const previewArea = document.getElementById('mbtt-mobile-preview-area');
                const anchor = document.getElementById('mbtt-scroll-anchor');
                
                if (previewArea && anchor) {
                    const observer = new IntersectionObserver((entries) => {
                        const entry = entries[0];
                        // Float when anchor scrolls above the viewport (top < 0)
                        if (!entry.isIntersecting && entry.boundingClientRect.top < 0) {
                            previewArea.classList.add('is-floating');
                        } else {
                            // Restore full-spec frame when anchor is visible in viewport
                            previewArea.classList.remove('is-floating');
                        }
                    }, { threshold: 0 });

                    observer.observe(anchor);
                }
            }
        });

        const svgs = <?php echo wp_json_encode($svgs); ?>;
        const mbttPresetNonce = <?php echo wp_json_encode( wp_create_nonce( 'mbtt_preset_action' ) ); ?>;
        const mbttI18n = {
            confirmSave: '<?php
                /* translators: %s: Slot number */
                echo esc_js( __( 'Save current colors and opacity to slot %s?', 'tsugawa-magic-back-to-top' ) );
            ?>',
            confirmDelete: '<?php
                /* translators: %s: Slot number */
                echo esc_js( __( 'Clear slot %s and remove its settings?', 'tsugawa-magic-back-to-top' ) );
            ?>',
            emptySlot: '<?php echo esc_js( __( 'Empty', 'tsugawa-magic-back-to-top' ) ); ?>'
        };
        
        function setMbttColors(btnBg, bg, progress, arrow, hover, opNormal, opHover, bgOnly) {
            document.getElementById('mbtt_color_btn_bg').value = btnBg;
            document.getElementById('mbtt_color_bg').value = bg;
            document.getElementById('mbtt_color_progress').value = progress;
            document.getElementById('mbtt_color_arrow').value = arrow;
            document.getElementById('mbtt_color_hover').value = hover;

            if (opNormal !== undefined) {
                document.querySelector('input[name*="[opacity_normal]"]').value = opNormal;
                document.querySelector('input[name*="[opacity_normal]"]').nextElementSibling.value = opNormal;
                document.querySelector('input[name*="[opacity_hover]"]').value = opHover;
                document.querySelector('input[name*="[opacity_hover]"]').nextElementSibling.value = opHover;
                document.getElementById('mbtt_bg_only_opacity').checked = (bgOnly == 1);
            }
            updateLivePreview();
        }

        function setCustomReferer(action, slot) {
            const refererInput = document.querySelector('input[name="_wp_http_referer"]');
            if (refererInput) {
                let val = refererInput.value;
                val = val.replace(/&?mbtt-action=[^&]*/g, '');
                val = val.replace(/&?slot=[^&]*/g, '');
                val = val.replace(/&?settings-updated=[^&]*/g, '');
                val = val.replace(/&?_mbtt_nonce=[^&]*/g, '');
                val = val.replace(/&?reset=true/g, ''); 
                val += (val.indexOf('?') !== -1 ? '&' : '?') + 'mbtt-action=' + action + '&slot=' + slot + '&_mbtt_nonce=' + mbttPresetNonce;
                refererInput.value = val;
            }
        }

        function triggerFormSubmit() {
            document.querySelector('#mbtt-form input[type="submit"]').click();
        }

        function saveToMyPreset(slot) {
            if(confirm(mbttI18n.confirmSave.replace('%s', slot))) {
                const btnBg = document.getElementById('mbtt_color_btn_bg').value;
                const bg = document.getElementById('mbtt_color_bg').value;
                const progress = document.getElementById('mbtt_color_progress').value;
                const arrow = document.getElementById('mbtt_color_arrow').value;
                const hover = document.getElementById('mbtt_color_hover').value;
                const opNormal = document.querySelector('input[name*="[opacity_normal]"]').value;
                const opHover = document.querySelector('input[name*="[opacity_hover]"]').value;
                const bgOnly = document.getElementById('mbtt_bg_only_opacity').checked ? 1 : 0;

                const visual = document.getElementById('mbtt-visual-slot-' + slot);
                if(visual) {
                    visual.className = 'mbtt-preset-visual';
                    visual.style.background = btnBg;
                    visual.style.setProperty('--my-track', bg);
                    visual.style.setProperty('--my-prog', progress);
                    visual.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" style="color: ' + arrow + ';">' + 
                                       '<path d="m17 16-5-8-5 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>' + 
                                       '</svg>';
                }

                document.getElementById('my_preset_' + slot + '_color_btn_bg').value = btnBg;
                document.getElementById('my_preset_' + slot + '_color_bg').value = bg;
                document.getElementById('my_preset_' + slot + '_color_progress').value = progress;
                document.getElementById('my_preset_' + slot + '_color_arrow').value = arrow;
                document.getElementById('my_preset_' + slot + '_color_hover').value = hover;
                document.getElementById('my_preset_' + slot + '_opacity_normal').value = opNormal;
                document.getElementById('my_preset_' + slot + '_opacity_hover').value = opHover;
                document.getElementById('my_preset_' + slot + '_bg_only_opacity').value = bgOnly;

                setCustomReferer('save-slot', slot);
                triggerFormSubmit();
            }
        }

        function deleteMyPreset(slot) {
            if(confirm(mbttI18n.confirmDelete.replace('%s', slot))) {
                const visual = document.getElementById('mbtt-visual-slot-' + slot);
                if(visual) {
                    visual.className = 'mbtt-preset-visual empty';
                    visual.style.cssText = '';
                    visual.innerHTML = '<span style="font-size:10px; color:#94a3b8;">' + mbttI18n.emptySlot + '</span>';
                }

                document.getElementById('my_preset_' + slot + '_color_btn_bg').value = '';
                document.getElementById('my_preset_' + slot + '_color_bg').value = '';
                document.getElementById('my_preset_' + slot + '_color_progress').value = '';
                document.getElementById('my_preset_' + slot + '_color_arrow').value = '';
                document.getElementById('my_preset_' + slot + '_color_hover').value = '';
                document.getElementById('my_preset_' + slot + '_opacity_normal').value = '';
                document.getElementById('my_preset_' + slot + '_opacity_hover').value = '';
                document.getElementById('my_preset_' + slot + '_bg_only_opacity').value = '';

                setCustomReferer('delete-slot', slot);
                triggerFormSubmit();
            }
        }

        function updateLivePreview() {
            const wrap = document.getElementById('mbtt-preview-wrap');
            if(!wrap) return;
            const btnBg = document.getElementById('mbtt_color_btn_bg').value;
            const bgOnly = document.getElementById('mbtt_bg_only_opacity').checked;
            const opNormal = document.querySelector('input[name*="[opacity_normal]"]').value;
            const opHover = document.querySelector('input[name*="[opacity_hover]"]').value;

            if (bgOnly) {
                wrap.style.setProperty('--mbtt-actual-opacity-normal', '1');
                wrap.style.setProperty('--mbtt-actual-opacity-hover', '1');
                wrap.style.setProperty('--mbtt-actual-bg-normal', `color-mix(in srgb, ${btnBg} ${opNormal * 100}%, transparent)`);
                wrap.style.setProperty('--mbtt-actual-bg-hover', `color-mix(in srgb, ${btnBg} ${opHover * 100}%, transparent)`);
            } else {
                wrap.style.setProperty('--mbtt-actual-opacity-normal', opNormal);
                wrap.style.setProperty('--mbtt-actual-opacity-hover', opHover);
                wrap.style.setProperty('--mbtt-actual-bg-normal', btnBg);
                wrap.style.setProperty('--mbtt-actual-bg-hover', btnBg);
            }
            
            wrap.style.setProperty('--mbtt-color-bg', document.getElementById('mbtt_color_bg').value);
            wrap.style.setProperty('--mbtt-color-progress', document.getElementById('mbtt_color_progress').value);
            wrap.style.setProperty('--mbtt-color-arrow', document.getElementById('mbtt_color_arrow').value);
            wrap.style.setProperty('--mbtt-color-hover', document.getElementById('mbtt_color_hover').value);

            const size = document.querySelector('input[name*="[size_pc]"]').value;
            wrap.style.setProperty('--mbtt-size', size + 'px');

            const checkedArrow = document.querySelector('input[name*="[arrow_type]"]:checked');
            if(checkedArrow) {
                document.getElementById('mbtt-live-arrow').innerHTML = svgs[checkedArrow.value];
            }
        }

        document.querySelectorAll('.mbtt-live-input').forEach(input => {
            input.addEventListener('input', updateLivePreview);
            input.addEventListener('change', updateLivePreview);
        });
        </script>
        <?php
    }

    private function get_svgs() {
        return array(
            // Arrow icon SVG paths: Lucide Icons (https://lucide.dev/) - MIT License.
            'type_a' => '<path d="m17 16-5-8-5 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>',
            'type_b' => '<path d="M12 5L5 12h4v8h6v-8h4z" fill="currentColor" stroke="none"></path>',
            'type_c' => '<path d="M5 3h14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="m18 13-6-6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 7v14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>',
            'type_d' => '<path d="m6 11 6-7 6 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 21V4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>',
            'type_e' => '<path d="m16 12-4-4-4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="m16 17-4-4-4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>'
        );
    }

    /**
     * SVG allowed tags for wp_kses().
     */
    private function get_svg_allowed_tags() {
        return array(
            'polyline' => array(
                'points'          => true,
                'fill'            => true,
                'stroke'          => true,
                'stroke-width'    => true,
                'stroke-linecap'  => true,
                'stroke-linejoin' => true,
            ),
            'path' => array(
                'd'               => true,
                'fill'            => true,
                'stroke'          => true,
                'stroke-width'    => true,
                'stroke-linecap'  => true,
                'stroke-linejoin' => true,
            ),
            'line' => array(
                'x1'              => true,
                'y1'              => true,
                'x2'              => true,
                'y2'              => true,
                'stroke'          => true,
                'stroke-width'    => true,
                'stroke-linecap'  => true,
            ),
        );
    }

    /**
     * Check if the current page matches the exclusion settings.
     */
    private function mbtt_is_excluded_page() {
        $options = $this->get_options();
        $mbtt_exclude_raw = trim( $options['exclude_pages'] );

        if ( empty( $mbtt_exclude_raw ) ) {
            return false;
        }

        $mbtt_excludes = array_map( 'trim', explode( ',', $mbtt_exclude_raw ) );
        $mbtt_excludes = array_filter( $mbtt_excludes, 'strlen' );

        if ( empty( $mbtt_excludes ) ) {
            return false;
        }

        // Safely get the current queried object for slug/title comparison.
        $mbtt_queried = get_queried_object();
        $mbtt_current_slug  = '';
        $mbtt_current_title = '';

        if ( $mbtt_queried instanceof WP_Post ) {
            // post_name may be URL-encoded, so decode and normalize.
            $mbtt_current_slug  = function_exists( 'mb_strtolower' )
                ? mb_strtolower( urldecode( $mbtt_queried->post_name ), 'UTF-8' )
                : strtolower( urldecode( $mbtt_queried->post_name ) );
            $mbtt_current_title = function_exists( 'mb_strtolower' )
                ? mb_strtolower( $mbtt_queried->post_title, 'UTF-8' )
                : strtolower( $mbtt_queried->post_title );
        }

        foreach ( $mbtt_excludes as $mbtt_item ) {
            // "front" keyword -> front page or home page.
            if ( strtolower( $mbtt_item ) === 'front' ) {
                if ( is_front_page() || is_home() ) {
                    return true;
                }
                continue;
            }

            // Numeric -> match by page ID.
            if ( is_numeric( $mbtt_item ) ) {
                $mbtt_page_id = intval( $mbtt_item );
                if ( is_page( $mbtt_page_id ) || is_single( $mbtt_page_id ) ) {
                    return true;
                }
                continue;
            }

            // --- String matching (3-layer fallback) ---

            // Layer 1: WordPress core functions (ASCII slugs).
            if ( is_page( $mbtt_item ) || is_single( $mbtt_item ) ) {
                return true;
            }

            // Layer 2-3: Multibyte support — direct slug & title comparison.
            if ( ! empty( $mbtt_current_slug ) ) {
                $mbtt_item_lower = function_exists( 'mb_strtolower' )
                    ? mb_strtolower( $mbtt_item, 'UTF-8' )
                    : strtolower( $mbtt_item );

                // Layer 2: input vs decoded slug.
                if ( $mbtt_item_lower === $mbtt_current_slug ) {
                    return true;
                }

                // Layer 3: input vs page title.
                if ( ! empty( $mbtt_current_title ) && $mbtt_item_lower === $mbtt_current_title ) {
                    return true;
                }
            }
        }

        return false;
    }

    public function output_css_variables() {
        if ( $this->mbtt_is_excluded_page() ) {
            return;
        }
        $options = $this->get_options();
        $bg_only = $options['bg_only_opacity'];
        echo "\n\n";
        echo "<style>\n:root {\n";
        echo "  --mbtt-size-sp: " . esc_attr( $options['size_sp'] ) . "px;\n";
        echo "  --mbtt-x-sp: " . esc_attr( $options['pos_x_sp'] ) . "px;\n";
        echo "  --mbtt-bottom-sp: " . esc_attr( $options['pos_bottom_sp'] ) . "px;\n";
        echo "  --mbtt-size-pc: " . esc_attr( $options['size_pc'] ) . "px;\n";
        echo "  --mbtt-x-pc: " . esc_attr( $options['pos_x_pc'] ) . "px;\n";
        echo "  --mbtt-bottom-pc: " . esc_attr( $options['pos_bottom_pc'] ) . "px;\n";
        
        $hex_bg = $options['color_btn_bg'];
        $op_n_pct = $options['opacity_normal'] * 100;
        $op_h_pct = $options['opacity_hover'] * 100;
        
        if ( $bg_only ) {
            echo '  --mbtt-actual-bg-normal: color-mix(in srgb, ' . esc_attr( $hex_bg ) . ' ' . esc_attr( $op_n_pct ) . '%, transparent);' . "\n";
            echo '  --mbtt-actual-bg-hover: color-mix(in srgb, ' . esc_attr( $hex_bg ) . ' ' . esc_attr( $op_h_pct ) . '%, transparent);' . "\n";
            echo "  --mbtt-actual-opacity-normal: 1;\n";
            echo "  --mbtt-actual-opacity-hover: 1;\n";
        } else {
            echo '  --mbtt-actual-bg-normal: ' . esc_attr( $hex_bg ) . ";\n";
            echo '  --mbtt-actual-bg-hover: ' . esc_attr( $hex_bg ) . ";\n";
            echo "  --mbtt-actual-opacity-normal: " . esc_attr( $options['opacity_normal'] ) . ";\n";
            echo "  --mbtt-actual-opacity-hover: " . esc_attr( $options['opacity_hover'] ) . ";\n";
        }
        
        echo "  --mbtt-color-bg: " . esc_attr( $options['color_bg'] ) . ";\n";
        echo "  --mbtt-color-progress: " . esc_attr( $options['color_progress'] ) . ";\n";
        echo "  --mbtt-color-arrow: " . esc_attr( $options['color_arrow'] ) . ";\n";
        echo "  --mbtt-color-hover: " . esc_attr( $options['color_hover'] ) . ";\n";
        echo "  --mbtt-z-index: " . esc_attr( $options['z_index'] ) . ";\n";
        echo "}\n</style>\n";
    }

    public function output_frontend() {
        if ( $this->mbtt_is_excluded_page() ) {
            return;
        }
        $options = $this->get_options();
        $svgs = $this->get_svgs();
        $svg_path = isset( $svgs[$options['arrow_type']] ) ? $svgs[$options['arrow_type']] : $svgs['type_a'];
        echo "\n\n";
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_frontend_html() returns a self-contained HTML block with all variables individually escaped via esc_attr() and wp_kses().
        echo $this->get_frontend_html( $options, $svg_path );
    }

    private function get_frontend_html( $options, $svg_path ) {
        $pos_side = ( $options['position_x'] === 'left' ) ? 'left' : 'right';
        ob_start();
        ?>
<style>
.scroll-to-top-btn { position: fixed; bottom: var(--mbtt-bottom-sp, 20px); <?php echo esc_attr( $pos_side ); ?>: var(--mbtt-x-sp, 20px); width: var(--mbtt-size-sp, 44px); height: var(--mbtt-size-sp, 44px); border-radius: 50%; background: var(--mbtt-actual-bg-normal, #ffffff); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); z-index: var(--mbtt-z-index, 99); opacity: 0; visibility: hidden; transform: translateY(15px); transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease; padding: 0; margin: 0; outline: none; -webkit-tap-highlight-color: transparent; }
@media (min-width: 769px) { .scroll-to-top-btn { bottom: var(--mbtt-bottom-pc, 30px); <?php echo esc_attr( $pos_side ); ?>: var(--mbtt-x-pc, 30px); width: var(--mbtt-size-pc, 54px); height: var(--mbtt-size-pc, 54px); } }
.scroll-to-top-btn:focus-visible { box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.4), 0 4px 12px rgba(0, 0, 0, 0.15); }
.scroll-to-top-btn.is-visible { opacity: var(--mbtt-actual-opacity-normal, 0.8); visibility: visible; transform: translateY(0); }
.scroll-to-top-btn:hover { box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2); transform: translateY(-3px); opacity: var(--mbtt-actual-opacity-hover, 1.0); background: var(--mbtt-actual-bg-hover, #ffffff); }
.scroll-to-top-btn .mbtt-ring { position: absolute; top: 0; left: 0; width: 100%; height: 100%; transform: rotate(-90deg); margin: 0; padding: 0; }
.scroll-to-top-btn .mbtt-ring circle { fill: transparent; stroke-width: 3; }
.scroll-to-top-btn .mbtt-ring-bg { stroke: var(--mbtt-color-bg, #e2e8f0); transition: stroke 0.3s ease; }
.scroll-to-top-btn .mbtt-ring-progress { stroke: var(--mbtt-color-progress, #3b82f6); stroke-linecap: round; transition: stroke-dashoffset 0.1s linear, stroke 0.3s ease; }
.scroll-to-top-btn .mbtt-arrow { width: 45%; height: 45%; color: var(--mbtt-color-arrow, #475569); position: relative; z-index: 2; transition: color 0.3s ease; margin: 0 !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; }
.scroll-to-top-btn:hover .mbtt-arrow { color: var(--mbtt-color-hover, #3b82f6); }
<?php if ( $options['hide_mobile'] ) : ?>
@media (max-width: 768px) { .scroll-to-top-btn { display: none !important; } }
<?php endif; ?>
</style>
<button id="cspScrollBtn" class="scroll-to-top-btn" aria-label="<?php echo esc_attr__( 'Back to top', 'tsugawa-magic-back-to-top' ); ?>" data-mbtt-threshold="<?php echo esc_attr( $options['scroll_threshold'] ); ?>" data-mbtt-duration="<?php echo esc_attr( $options['scroll_duration'] ); ?>">
  <svg class="mbtt-ring" viewBox="0 0 54 54" aria-hidden="true"><circle class="mbtt-ring-bg" cx="27" cy="27" r="24"></circle><circle class="mbtt-ring-progress" cx="27" cy="27" r="24"></circle></svg>
  <svg class="mbtt-arrow" viewBox="0 0 24 24" aria-hidden="true"><?php echo wp_kses( $svg_path, $this->get_svg_allowed_tags() ); ?></svg>
</button>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('cspScrollBtn'); if (!btn) return;
  const circle = btn.querySelector('.mbtt-ring-progress');
  const circ = 24 * 2 * Math.PI; circle.style.strokeDasharray = circ; circle.style.strokeDashoffset = circ;
  let ticking = false;
  const update = () => {
    const scrollCurrent = window.scrollY || document.documentElement.scrollTop;
    const scrollTotal = document.documentElement.scrollHeight - window.innerHeight;
    if (scrollTotal > 0) circle.style.strokeDashoffset = circ - (Math.min(scrollCurrent / scrollTotal, 1) * circ);
    const mbttThreshold = isNaN(parseInt(btn.dataset.mbttThreshold, 10)) ? 100 : parseInt(btn.dataset.mbttThreshold, 10);
    if (scrollCurrent >= mbttThreshold) btn.classList.add('is-visible'); else btn.classList.remove('is-visible');
    ticking = false;
  };
  const onScroll = () => { if (!ticking) { window.requestAnimationFrame(update); ticking = true; } };
  window.addEventListener('scroll', onScroll, { passive: true }); window.addEventListener('resize', onScroll, { passive: true });
  btn.addEventListener('click', () => {
    const mbttDuration = isNaN(parseInt(btn.dataset.mbttDuration, 10)) ? 600 : parseInt(btn.dataset.mbttDuration, 10);
    if (mbttDuration <= 0) { window.scrollTo(0, 0); return; }
    const mbttStart = window.scrollY || document.documentElement.scrollTop;
    if (mbttStart <= 0) return;
    let mbttStartTime = null;
    const mbttAnimate = (ts) => {
      if (!mbttStartTime) mbttStartTime = ts;
      const mbttProgress = Math.min((ts - mbttStartTime) / mbttDuration, 1);
      const mbttEase = mbttProgress < 0.5 ? 4 * mbttProgress * mbttProgress * mbttProgress : 1 - Math.pow(-2 * mbttProgress + 2, 3) / 2;
      window.scrollTo(0, mbttStart * (1 - mbttEase));
      if (mbttProgress < 1) requestAnimationFrame(mbttAnimate);
    };
    requestAnimationFrame(mbttAnimate);
  }); update();
});
</script>
        <?php
        return ob_get_clean();
    }

    private function generate_export_code( $options ) {
        $svgs = $this->get_svgs();
        $svg_path = isset( $svgs[$options['arrow_type']] ) ? $svgs[$options['arrow_type']] : $svgs['type_a'];
        $bg_only = $options['bg_only_opacity'];
        
        $vars = "<style>\n:root {\n";
        $vars .= "  --mbtt-size-sp: " . esc_attr( $options['size_sp'] ) . "px;\n";
        $vars .= "  --mbtt-x-sp: " . esc_attr( $options['pos_x_sp'] ) . "px;\n";
        $vars .= "  --mbtt-bottom-sp: " . esc_attr( $options['pos_bottom_sp'] ) . "px;\n";
        $vars .= "  --mbtt-size-pc: " . esc_attr( $options['size_pc'] ) . "px;\n";
        $vars .= "  --mbtt-x-pc: " . esc_attr( $options['pos_x_pc'] ) . "px;\n";
        $vars .= "  --mbtt-bottom-pc: " . esc_attr( $options['pos_bottom_pc'] ) . "px;\n";
        
        $hex_bg = $options['color_btn_bg'];
        $op_n_pct = $options['opacity_normal'] * 100;
        $op_h_pct = $options['opacity_hover'] * 100;
        if ( $bg_only ) {
            $vars .= '  --mbtt-actual-bg-normal: color-mix(in srgb, ' . esc_attr( $hex_bg ) . ' ' . esc_attr( $op_n_pct ) . '%, transparent);' . "\n";
            $vars .= '  --mbtt-actual-bg-hover: color-mix(in srgb, ' . esc_attr( $hex_bg ) . ' ' . esc_attr( $op_h_pct ) . '%, transparent);' . "\n";
            $vars .= "  --mbtt-actual-opacity-normal: 1;\n";
            $vars .= "  --mbtt-actual-opacity-hover: 1;\n";
        } else {
            $vars .= '  --mbtt-actual-bg-normal: ' . esc_attr( $hex_bg ) . ";\n";
            $vars .= '  --mbtt-actual-bg-hover: ' . esc_attr( $hex_bg ) . ";\n";
            $vars .= "  --mbtt-actual-opacity-normal: " . esc_attr( $options['opacity_normal'] ) . ";\n";
            $vars .= "  --mbtt-actual-opacity-hover: " . esc_attr( $options['opacity_hover'] ) . ";\n";
        }
        
        $vars .= "  --mbtt-color-bg: " . esc_attr( $options['color_bg'] ) . ";\n";
        $vars .= "  --mbtt-color-progress: " . esc_attr( $options['color_progress'] ) . ";\n";
        $vars .= "  --mbtt-color-arrow: " . esc_attr( $options['color_arrow'] ) . ";\n";
        $vars .= "  --mbtt-color-hover: " . esc_attr( $options['color_hover'] ) . ";\n";
        $vars .= "  --mbtt-z-index: " . esc_attr( $options['z_index'] ) . ";\n";
        $vars .= "}\n</style>\n";
        return "\n" . $vars . $this->get_frontend_html( $options, $svg_path );
    }
}
new Magic_Back_To_Top();