<?php
/**
 * Debug and Verification File
 * includes/class-acf-carousel-debug.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class ACF_Carousel_Debug {
    
    public static function run_diagnostics() {
        $results = [
            'plugin_files' => self::check_plugin_files(),
            'dependencies' => self::check_dependencies(),
            'permissions' => self::check_permissions(),
            'php_version' => self::check_php_version(),
            'wp_version' => self::check_wp_version(),
        ];
        
        return $results;
    }
    
    public static function check_plugin_files() {
        $required_files = [
            'acf-carousel-elementor.php',
            'widgets/acf-carousel-widget.php',
            'templates/hero-carousel-template.php',
            'templates/carousel-card.php',
            'assets/css/acf-carousel.css',
            'assets/css/hero-carousel.css',
            'assets/js/acf-carousel.js',
            'includes/class-acf-carousel-admin.php',
            'includes/class-acf-carousel-installer.php'
        ];
        
        $missing_files = [];
        $existing_files = [];
        
        foreach ($required_files as $file) {
            $full_path = ACF_CAROUSEL_PLUGIN_PATH . $file;
            if (file_exists($full_path)) {
                $existing_files[] = $file;
            } else {
                $missing_files[] = $file;
            }
        }
        
        return [
            'existing' => $existing_files,
            'missing' => $missing_files,
            'status' => empty($missing_files) ? 'complete' : 'incomplete'
        ];
    }
    
    public static function check_dependencies() {
        $dependencies = [
            'WordPress' => [
                'required' => '5.0',
                'current' => get_bloginfo('version'),
                'status' => version_compare(get_bloginfo('version'), '5.0', '>=')
            ],
            'PHP' => [
                'required' => '7.4',
                'current' => PHP_VERSION,
                'status' => version_compare(PHP_VERSION, '7.4', '>=')
            ],
            'Elementor' => [
                'required' => '3.0',
                'current' => defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : 'Not installed',
                'status' => did_action('elementor/loaded')
            ],
            'ACF' => [
                'required' => '5.0',
                'current' => defined('ACF_VERSION') ? ACF_VERSION : 'Not installed',
                'status' => class_exists('ACF')
            ]
        ];
        
        return $dependencies;
    }
    
    public static function check_permissions() {
        $directories = [
            ACF_CAROUSEL_PLUGIN_PATH,
            ACF_CAROUSEL_PLUGIN_PATH . 'assets/',
            ACF_CAROUSEL_PLUGIN_PATH . 'templates/',
            ACF_CAROUSEL_PLUGIN_PATH . 'widgets/',
            ACF_CAROUSEL_PLUGIN_PATH . 'includes/'
        ];
        
        $permissions = [];
        
        foreach ($directories as $dir) {
            $permissions[$dir] = [
                'readable' => is_readable($dir),
                'writable' => is_writable($dir),
                'executable' => is_executable($dir)
            ];
        }
        
        return $permissions;
    }
    
    public static function check_php_version() {
        return [
            'current' => PHP_VERSION,
            'required' => '7.4',
            'compatible' => version_compare(PHP_VERSION, '7.4', '>='),
            'extensions' => [
                'json' => extension_loaded('json'),
                'mbstring' => extension_loaded('mbstring'),
                'curl' => extension_loaded('curl')
            ]
        ];
    }
    
    public static function check_wp_version() {
        return [
            'current' => get_bloginfo('version'),
            'required' => '5.0',
            'compatible' => version_compare(get_bloginfo('version'), '5.0', '>=')
        ];
    }
    
    public static function display_debug_info() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $diagnostics = self::run_diagnostics();
        
        echo '<div class="wrap">';
        echo '<h1>ACF Carousel Debug Information</h1>';
        
        // Dependencies Status
        echo '<h2>Dependencies</h2>';
        echo '<table class="widefat">';
        echo '<thead><tr><th>Dependency</th><th>Required</th><th>Current</th><th>Status</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($diagnostics['dependencies'] as $name => $info) {
            $status_class = $info['status'] ? 'notice-success' : 'notice-error';
            $status_text = $info['status'] ? '✓ OK' : '✗ Missing/Outdated';
            
            echo sprintf(
                '<tr class="%s"><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                esc_attr($status_class),
                esc_html($name),
                esc_html($info['required']),
                esc_html($info['current']),
                esc_html($status_text)
            );
        }
        
        echo '</tbody></table>';
        
        // Plugin Files Status
        echo '<h2>Plugin Files</h2>';
        if ($diagnostics['plugin_files']['status'] === 'complete') {
            echo '<p class="notice notice-success">All required files are present.</p>';
        } else {
            echo '<p class="notice notice-error">Some required files are missing:</p>';
            echo '<ul>';
            foreach ($diagnostics['plugin_files']['missing'] as $file) {
                echo '<li>' . esc_html($file) . '</li>';
            }
            echo '</ul>';
        }
        
        // PHP Information
        echo '<h2>PHP Information</h2>';
        echo '<table class="widefat">';
        echo '<tbody>';
        echo '<tr><td><strong>PHP Version</strong></td><td>' . esc_html($diagnostics['php_version']['current']) . '</td></tr>';
        echo '<tr><td><strong>Compatible</strong></td><td>' . ($diagnostics['php_version']['compatible'] ? '✓ Yes' : '✗ No') . '</td></tr>';
        
        foreach ($diagnostics['php_version']['extensions'] as $ext => $loaded) {
            echo '<tr><td><strong>' . esc_html(ucfirst($ext)) . ' Extension</strong></td><td>' . ($loaded ? '✓ Loaded' : '✗ Missing') . '</td></tr>';
        }
        
        echo '</tbody></table>';
        
        echo '</div>';
    }
    
    public static function add_debug_menu() {
        add_management_page(
            'ACF Carousel Debug',
            'ACF Carousel Debug',
            'manage_options',
            'acf-carousel-debug',
            [self::class, 'display_debug_info']
        );
    }
}

// Agregar menú de debug solo si WP_DEBUG está activado
if (defined('WP_DEBUG') && WP_DEBUG && is_admin()) {
    add_action('admin_menu', ['ACF_Carousel_Debug', 'add_debug_menu']);
}