<?php
/**
 * Plugin Installer Class
 * includes/class-acf-carousel-installer.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class ACF_Carousel_Installer {
    
    public static function install() {
        // Create plugin options
        self::create_options();
        
        // Set default settings
        self::set_default_settings();
        
        // Check dependencies
        self::check_dependencies();
        
        // Set installation flag
        update_option('acf_carousel_elementor_installed', true);
        update_option('acf_carousel_elementor_version', ACF_CAROUSEL_VERSION);
    }
    
    public static function uninstall() {
        // Remove plugin options
        delete_option('acf_carousel_elementor_settings');
        delete_option('acf_carousel_elementor_installed');
        delete_option('acf_carousel_elementor_version');
        
        // Clear any cached data
        wp_cache_flush();
    }
    
    private static function create_options() {
        $default_settings = [
            'load_embla_from_cdn' => true,
            'enable_autoplay_globally' => false,
            'default_slides_to_show' => 3,
            'default_slides_to_scroll' => 1,
            'enable_lazy_loading' => true,
            'cache_duration' => 12, // hours
        ];
        
        add_option('acf_carousel_elementor_settings', $default_settings);
    }
    
    private static function set_default_settings() {
        // Create default carousel settings for new installations
        $carousel_defaults = [
            'card_style' => 'modern',
            'animation_speed' => 300,
            'autoplay_delay' => 4000,
            'responsive_breakpoints' => [
                'mobile' => 480,
                'tablet' => 768,
                'desktop' => 1024
            ]
        ];
        
        update_option('acf_carousel_elementor_defaults', $carousel_defaults);
    }
    
    private static function check_dependencies() {
        $dependencies = [];
        
        // Check if Elementor is active
        if (!is_plugin_active('elementor/elementor.php')) {
            $dependencies[] = [
                'name' => 'Elementor',
                'slug' => 'elementor',
                'required' => true
            ];
        }
        
        // Check if ACF is active
        if (!is_plugin_active('advanced-custom-fields/acf.php') && 
            !is_plugin_active('advanced-custom-fields-pro/acf.php')) {
            $dependencies[] = [
                'name' => 'Advanced Custom Fields',
                'slug' => 'advanced-custom-fields',
                'required' => true
            ];
        }
        
        if (!empty($dependencies)) {
            update_option('acf_carousel_missing_dependencies', $dependencies);
        } else {
            delete_option('acf_carousel_missing_dependencies');
        }
    }
    
    public static function upgrade() {
        $current_version = get_option('acf_carousel_elementor_version', '0.0.0');
        
        if (version_compare($current_version, ACF_CAROUSEL_VERSION, '<')) {
            // Run upgrade procedures
            self::run_upgrades($current_version);
            
            // Update version
            update_option('acf_carousel_elementor_version', ACF_CAROUSEL_VERSION);
        }
    }
    
    private static function run_upgrades($from_version) {
        // Version-specific upgrade procedures
        if (version_compare($from_version, '1.0.0', '<')) {
            // Upgrade to 1.0.0
            self::upgrade_to_1_0_0();
        }
        
        // Add more version upgrades as needed
    }
    
    private static function upgrade_to_1_0_0() {
        // Migration logic for version 1.0.0
        $settings = get_option('acf_carousel_elementor_settings', []);
        
        // Add new settings if they don't exist
        if (!isset($settings['enable_lazy_loading'])) {
            $settings['enable_lazy_loading'] = true;
        }
        
        if (!isset($settings['cache_duration'])) {
            $settings['cache_duration'] = 12;
        }
        
        update_option('acf_carousel_elementor_settings', $settings);
    }
}

// Hook into WordPress activation/deactivation
register_activation_hook(ACF_CAROUSEL_PLUGIN_PATH . 'acf-carousel-elementor.php', ['ACF_Carousel_Installer', 'install']);
register_deactivation_hook(ACF_CAROUSEL_PLUGIN_PATH . 'acf-carousel-elementor.php', ['ACF_Carousel_Installer', 'uninstall']);

// Check for upgrades on admin_init
add_action('admin_init', ['ACF_Carousel_Installer', 'upgrade']);