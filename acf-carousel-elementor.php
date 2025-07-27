<?php
/**
 * Plugin Name: ACF Custom Post Carousel for Elementor
 * Description: Elementor widget para mostrar Custom Post Types con ACF en formato carousel usando Embla Carousel
 * Version: 1.0.0
 * Author: Tu Nombre
 * Text Domain: acf-carousel-elementor
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ACF_CAROUSEL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ACF_CAROUSEL_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ACF_CAROUSEL_VERSION', '1.0.0');

/**
 * Main Plugin Class
 */
class ACF_Carousel_Elementor {
    
    private static $_instance = null;
    
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function __construct() {
        add_action('init', [$this, 'init']);
    }
    
    public function init() {
        // Check if Elementor is installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }
        
        // Check if ACF is installed and activated
        if (!class_exists('ACF')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_acf']);
            return;
        }
        
        // Include debug class if WP_DEBUG is enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            require_once(ACF_CAROUSEL_PLUGIN_PATH . 'includes/class-acf-carousel-debug.php');
        }
        
        // Add Plugin actions
        add_action('elementor/widgets/widgets_registered', [$this, 'init_widgets']);
        add_action('elementor/frontend/after_register_scripts', [$this, 'widget_scripts']);
        add_action('elementor/frontend/after_register_styles', [$this, 'widget_styles']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        // Load text domain
        add_action('plugins_loaded', [$this, 'load_textdomain']);
    }
    
    public function load_textdomain() {
        load_plugin_textdomain(
            'acf-carousel-elementor',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
    public function admin_notice_missing_elementor() {
        if (isset($_GET['activate'])) unset($_GET['activate']);
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'acf-carousel-elementor'),
            '<strong>' . esc_html__('ACF Custom Post Carousel', 'acf-carousel-elementor') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'acf-carousel-elementor') . '</strong>'
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }
    
    public function admin_notice_missing_acf() {
        if (isset($_GET['activate'])) unset($_GET['activate']);
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'acf-carousel-elementor'),
            '<strong>' . esc_html__('ACF Custom Post Carousel', 'acf-carousel-elementor') . '</strong>',
            '<strong>' . esc_html__('Advanced Custom Fields', 'acf-carousel-elementor') . '</strong>'
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }
    
    public function init_widgets() {
        // Verificar que Elementor esté disponible
        if (!class_exists('\Elementor\Widget_Base')) {
            return;
        }
        
        // Include Widget files
        $widget_file = ACF_CAROUSEL_PLUGIN_PATH . 'widgets/acf-carousel-widget.php';
        
        if (file_exists($widget_file)) {
            require_once($widget_file);
            
            // Verificar que la clase del widget existe antes de registrarla
            if (class_exists('ACF_Carousel_Widget')) {
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \ACF_Carousel_Widget());
            }
        }
    }
    
    public function widget_scripts() {
        wp_register_script(
            'embla-carousel',
            'https://unpkg.com/embla-carousel@8.0.0/embla-carousel.umd.js',
            [],
            '8.0.0',
            true
        );
        
        wp_register_script(
            'embla-carousel-autoplay',
            'https://unpkg.com/embla-carousel-autoplay@8.0.0/embla-carousel-autoplay.umd.js',
            ['embla-carousel'],
            '8.0.0',
            true
        );
        
        wp_register_script(
            'acf-carousel-widget',
            ACF_CAROUSEL_PLUGIN_URL . 'assets/js/acf-carousel.js',
            ['embla-carousel', 'embla-carousel-autoplay', 'jquery'],
            ACF_CAROUSEL_VERSION,
            true
        );
    }
    
    public function widget_styles() {
        wp_register_style(
            'acf-carousel-widget',
            ACF_CAROUSEL_PLUGIN_URL . 'assets/css/acf-carousel.css',
            [],
            ACF_CAROUSEL_VERSION
        );
        
        wp_register_style(
            'acf-hero-carousel-widget',
            ACF_CAROUSEL_PLUGIN_URL . 'assets/css/hero-carousel.css',
            [],
            ACF_CAROUSEL_VERSION
        );
    }
    
    public function enqueue_scripts() {
        if (\Elementor\Plugin::$instance->preview->is_preview_mode()) {
            wp_enqueue_script('embla-carousel');
            wp_enqueue_script('embla-carousel-autoplay');
            wp_enqueue_script('acf-carousel-widget');
            wp_enqueue_style('acf-carousel-widget');
            wp_enqueue_style('acf-hero-carousel-widget');
        }
    }
}

// Initialize the plugin
ACF_Carousel_Elementor::instance();