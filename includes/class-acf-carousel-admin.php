<?php
/**
 * Admin Panel Class
 * includes/class-acf-carousel-admin.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class ACF_Carousel_Admin {
    
    private $plugin_name;
    private $version;
    private $menu_slug = 'acf-carousel-settings';
    
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'init_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('admin_notices', [$this, 'show_admin_notices']);
        add_filter('plugin_action_links_' . plugin_basename(ACF_CAROUSEL_PLUGIN_PATH . 'acf-carousel-elementor.php'), [$this, 'add_plugin_action_links']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            esc_html__('ACF Carousel Settings', 'acf-carousel-elementor'),
            esc_html__('ACF Carousel', 'acf-carousel-elementor'),
            'manage_options',
            $this->menu_slug,
            [$this, 'render_admin_page']
        );
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting(
            'acf_carousel_settings_group',
            'acf_carousel_elementor_settings',
            [$this, 'sanitize_settings']
        );
        
        // General Settings Section
        add_settings_section(
            'acf_carousel_general_section',
            esc_html__('General Settings', 'acf-carousel-elementor'),
            [$this, 'render_general_section'],
            $this->menu_slug
        );
        
        // Performance Settings Section
        add_settings_section(
            'acf_carousel_performance_section',
            esc_html__('Performance Settings', 'acf-carousel-elementor'),
            [$this, 'render_performance_section'],
            $this->menu_slug
        );
        
        // Default Settings Section
        add_settings_section(
            'acf_carousel_defaults_section',
            esc_html__('Default Carousel Settings', 'acf-carousel-elementor'),
            [$this, 'render_defaults_section'],
            $this->menu_slug
        );
        
        // Advanced Settings Section
        add_settings_section(
            'acf_carousel_advanced_section',
            esc_html__('Advanced Settings', 'acf-carousel-elementor'),
            [$this, 'render_advanced_section'],
            $this->menu_slug
        );
        
        // Add settings fields
        $this->add_settings_fields();
    }
    
    /**
     * Add all settings fields
     */
    private function add_settings_fields() {
        $settings = get_option('acf_carousel_elementor_settings', []);
        
        // General Settings Fields
        add_settings_field(
            'load_embla_from_cdn',
            esc_html__('Load Embla from CDN', 'acf-carousel-elementor'),
            [$this, 'render_checkbox_field'],
            $this->menu_slug,
            'acf_carousel_general_section',
            [
                'name' => 'load_embla_from_cdn',
                'value' => $settings['load_embla_from_cdn'] ?? true,
                'description' => esc_html__('Load Embla Carousel library from CDN. Disable to use local version.', 'acf-carousel-elementor')
            ]
        );
        
        add_settings_field(
            'enable_global_styles',
            esc_html__('Enable Global Styles', 'acf-carousel-elementor'),
            [$this, 'render_checkbox_field'],
            $this->menu_slug,
            'acf_carousel_general_section',
            [
                'name' => 'enable_global_styles',
                'value' => $settings['enable_global_styles'] ?? true,
                'description' => esc_html__('Load default CSS styles. Disable if you want to use custom styles only.', 'acf-carousel-elementor')
            ]
        );
        
        add_settings_field(
            'enable_rtl_support',
            esc_html__('Enable RTL Support', 'acf-carousel-elementor'),
            [$this, 'render_checkbox_field'],
            $this->menu_slug,
            'acf_carousel_general_section',
            [
                'name' => 'enable_rtl_support',
                'value' => $settings['enable_rtl_support'] ?? false,
                'description' => esc_html__('Enable Right-to-Left language support for Arabic, Hebrew, etc.', 'acf-carousel-elementor')
            ]
        );
        
        // Performance Settings Fields
        add_settings_field(
            'enable_lazy_loading',
            esc_html__('Enable Lazy Loading', 'acf-carousel-elementor'),
            [$this, 'render_checkbox_field'],
            $this->menu_slug,
            'acf_carousel_performance_section',
            [
                'name' => 'enable_lazy_loading',
                'value' => $settings['enable_lazy_loading'] ?? true,
                'description' => esc_html__('Enable lazy loading for carousel images to improve page speed.', 'acf-carousel-elementor')
            ]
        );
        
        add_settings_field(
            'cache_duration',
            esc_html__('Cache Duration (hours)', 'acf-carousel-elementor'),
            [$this, 'render_number_field'],
            $this->menu_slug,
            'acf_carousel_performance_section',
            [
                'name' => 'cache_duration',
                'value' => $settings['cache_duration'] ?? 12,
                'min' => 1,
                'max' => 168,
                'description' => esc_html__('How long to cache carousel data. Higher values improve performance but may delay content updates.', 'acf-carousel-elementor')
            ]
        );
        
        add_settings_field(
            'preload_slides',
            esc_html__('Preload Slides', 'acf-carousel-elementor'),
            [$this, 'render_number_field'],
            $this->menu_slug,
            'acf_carousel_performance_section',
            [
                'name' => 'preload_slides',
                'value' => $settings['preload_slides'] ?? 2,
                'min' => 0,
                'max' => 10,
                'description' => esc_html__('Number of slides to preload for smoother navigation. 0 disables preloading.', 'acf-carousel-elementor')
            ]
        );
        
        // Default Settings Fields
        add_settings_field(
            'default_slides_to_show',
            esc_html__('Default Slides to Show', 'acf-carousel-elementor'),
            [$this, 'render_number_field'],
            $this->menu_slug,
            'acf_carousel_defaults_section',
            [
                'name' => 'default_slides_to_show',
                'value' => $settings['default_slides_to_show'] ?? 3,
                'min' => 1,
                'max' => 8,
                'description' => esc_html__('Default number of slides to show in new carousels.', 'acf-carousel-elementor')
            ]
        );
        
        add_settings_field(
            'default_slides_to_scroll',
            esc_html__('Default Slides to Scroll', 'acf-carousel-elementor'),
            [$this, 'render_number_field'],
            $this->menu_slug,
            'acf_carousel_defaults_section',
            [
                'name' => 'default_slides_to_scroll',
                'value' => $settings['default_slides_to_scroll'] ?? 1,
                'min' => 1,
                'max' => 5,
                'description' => esc_html__('Default number of slides to scroll per navigation action.', 'acf-carousel-elementor')
            ]
        );
        
        add_settings_field(
            'default_autoplay_delay',
            esc_html__('Default Autoplay Delay (ms)', 'acf-carousel-elementor'),
            [$this, 'render_number_field'],
            $this->menu_slug,
            'acf_carousel_defaults_section',
            [
                'name' => 'default_autoplay_delay',
                'value' => $settings['default_autoplay_delay'] ?? 4000,
                'min' => 1000,
                'max' => 15000,
                'step' => 500,
                'description' => esc_html__('Default delay between slides when autoplay is enabled.', 'acf-carousel-elementor')
            ]
        );
        
        add_settings_field(
            'enable_autoplay_globally',
            esc_html__('Enable Autoplay by Default', 'acf-carousel-elementor'),
            [$this, 'render_checkbox_field'],
            $this->menu_slug,
            'acf_carousel_defaults_section',
            [
                'name' => 'enable_autoplay_globally',
                'value' => $settings['enable_autoplay_globally'] ?? false,
                'description' => esc_html__('Enable autoplay by default in new carousels.', 'acf-carousel-elementor')
            ]
        );
        
        // Advanced Settings Fields
        add_settings_field(
            'custom_css',
            esc_html__('Custom CSS', 'acf-carousel-elementor'),
            [$this, 'render_textarea_field'],
            $this->menu_slug,
            'acf_carousel_advanced_section',
            [
                'name' => 'custom_css',
                'value' => $settings['custom_css'] ?? '',
                'rows' => 10,
                'description' => esc_html__('Add custom CSS that will be applied to all carousels. Use with caution.', 'acf-carousel-elementor')
            ]
        );
        
        add_settings_field(
            'enable_debug_mode',
            esc_html__('Enable Debug Mode', 'acf-carousel-elementor'),
            [$this, 'render_checkbox_field'],
            $this->menu_slug,
            'acf_carousel_advanced_section',
            [
                'name' => 'enable_debug_mode',
                'value' => $settings['enable_debug_mode'] ?? false,
                'description' => esc_html__('Enable debug mode to log carousel events in browser console.', 'acf-carousel-elementor')
            ]
        );
        
        add_settings_field(
            'allowed_post_types',
            esc_html__('Allowed Post Types', 'acf-carousel-elementor'),
            [$this, 'render_post_types_field'],
            $this->menu_slug,
            'acf_carousel_advanced_section',
            [
                'name' => 'allowed_post_types',
                'value' => $settings['allowed_post_types'] ?? [],
                'description' => esc_html__('Select which post types can be used in carousels. Leave empty to allow all.', 'acf-carousel-elementor')
            ]
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        if (isset($_GET['tab'])) {
            $active_tab = sanitize_text_field($_GET['tab']);
        } else {
            $active_tab = 'general';
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php $this->render_tabs($active_tab); ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('acf_carousel_settings_group');
                
                switch ($active_tab) {
                    case 'general':
                        do_settings_sections($this->menu_slug);
                        submit_button();
                        break;
                    case 'tools':
                        $this->render_tools_tab();
                        break;
                    case 'system':
                        $this->render_system_info_tab();
                        break;
                    default:
                        do_settings_sections($this->menu_slug);
                        submit_button();
                        break;
                }
                ?>
            </form>
            
            <?php if ($active_tab === 'general'): ?>
                <div class="acf-carousel-admin-sidebar">
                    <?php $this->render_sidebar(); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .wrap {
            display: flex;
            gap: 20px;
        }
        .wrap > form {
            flex: 1;
        }
        .acf-carousel-admin-sidebar {
            width: 300px;
            flex-shrink: 0;
        }
        .acf-carousel-widget {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .acf-carousel-widget h3 {
            margin-top: 0;
            color: #23282d;
        }
        .acf-carousel-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 15px 0;
        }
        .acf-carousel-stat {
            text-align: center;
            padding: 10px;
            background: #f1f1f1;
            border-radius: 4px;
        }
        .acf-carousel-stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #0073aa;
        }
        .acf-carousel-stat-label {
            font-size: 12px;
            color: #666;
        }
        </style>
        <?php
    }
    
    /**
     * Render navigation tabs
     */
    private function render_tabs($active_tab) {
        $tabs = [
            'general' => esc_html__('General', 'acf-carousel-elementor'),
            'tools' => esc_html__('Tools', 'acf-carousel-elementor'),
            'system' => esc_html__('System Info', 'acf-carousel-elementor')
        ];
        
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $tab_key => $tab_label) {
            $active_class = ($active_tab === $tab_key) ? ' nav-tab-active' : '';
            printf(
                '<a href="?page=%s&tab=%s" class="nav-tab%s">%s</a>',
                esc_attr($this->menu_slug),
                esc_attr($tab_key),
                esc_attr($active_class),
                esc_html($tab_label)
            );
        }
        echo '</h2>';
    }
    
    /**
     * Render sidebar widgets
     */
    private function render_sidebar() {
        // Statistics Widget
        $this->render_statistics_widget();
        
        // Quick Actions Widget
        $this->render_quick_actions_widget();
        
        // Support Widget
        $this->render_support_widget();
    }
    
    /**
     * Render statistics widget
     */
    private function render_statistics_widget() {
        global $wpdb;
        
        // Count carousels in use
        $carousel_count = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key LIKE '%_elementor_data%' 
            AND meta_value LIKE '%acf-carousel%'
        ");
        
        // Count total posts that could be in carousels
        $post_types = get_post_types(['public' => true], 'names');
        $total_posts = 0;
        foreach ($post_types as $post_type) {
            $total_posts += wp_count_posts($post_type)->publish;
        }
        
        ?>
        <div class="acf-carousel-widget">
            <h3><?php esc_html_e('Statistics', 'acf-carousel-elementor'); ?></h3>
            <div class="acf-carousel-stats">
                <div class="acf-carousel-stat">
                    <div class="acf-carousel-stat-number"><?php echo esc_html($carousel_count); ?></div>
                    <div class="acf-carousel-stat-label"><?php esc_html_e('Active Carousels', 'acf-carousel-elementor'); ?></div>
                </div>
                <div class="acf-carousel-stat">
                    <div class="acf-carousel-stat-number"><?php echo esc_html($total_posts); ?></div>
                    <div class="acf-carousel-stat-label"><?php esc_html_e('Available Posts', 'acf-carousel-elementor'); ?></div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render quick actions widget
     */
    private function render_quick_actions_widget() {
        ?>
        <div class="acf-carousel-widget">
            <h3><?php esc_html_e('Quick Actions', 'acf-carousel-elementor'); ?></h3>
            <p>
                <a href="#" class="button" id="clear-carousel-cache">
                    <?php esc_html_e('Clear Cache', 'acf-carousel-elementor'); ?>
                </a>
            </p>
            <p>
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=elementor_library&elementor_library_type=page')); ?>" class="button">
                    <?php esc_html_e('Manage Templates', 'acf-carousel-elementor'); ?>
                </a>
            </p>
            <p>
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=acf-field-group')); ?>" class="button">
                    <?php esc_html_e('Manage ACF Fields', 'acf-carousel-elementor'); ?>
                </a>
            </p>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#clear-carousel-cache').on('click', function(e) {
                e.preventDefault();
                
                $.post(ajaxurl, {
                    action: 'acf_carousel_clear_cache',
                    nonce: '<?php echo wp_create_nonce('acf_carousel_clear_cache'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('<?php esc_html_e('Cache cleared successfully!', 'acf-carousel-elementor'); ?>');
                    } else {
                        alert('<?php esc_html_e('Error clearing cache.', 'acf-carousel-elementor'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render support widget
     */
    private function render_support_widget() {
        ?>
        <div class="acf-carousel-widget">
            <h3><?php esc_html_e('Support', 'acf-carousel-elementor'); ?></h3>
            <p><?php esc_html_e('Need help? Check out these resources:', 'acf-carousel-elementor'); ?></p>
            <ul>
                <li><a href="#" target="_blank"><?php esc_html_e('Documentation', 'acf-carousel-elementor'); ?></a></li>
                <li><a href="#" target="_blank"><?php esc_html_e('Video Tutorials', 'acf-carousel-elementor'); ?></a></li>
                <li><a href="#" target="_blank"><?php esc_html_e('Support Forum', 'acf-carousel-elementor'); ?></a></li>
                <li><a href="#" target="_blank"><?php esc_html_e('Report Bug', 'acf-carousel-elementor'); ?></a></li>
            </ul>
            <p>
                <strong><?php esc_html_e('Plugin Version:', 'acf-carousel-elementor'); ?></strong>
                <?php echo esc_html(ACF_CAROUSEL_VERSION); ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * Render tools tab
     */
    private function render_tools_tab() {
        ?>
        <div class="acf-carousel-tools">
            <h2><?php esc_html_e('Tools', 'acf-carousel-elementor'); ?></h2>
            
            <div class="acf-carousel-tool-section">
                <h3><?php esc_html_e('Cache Management', 'acf-carousel-elementor'); ?></h3>
                <p><?php esc_html_e('Clear all cached carousel data to force refresh.', 'acf-carousel-elementor'); ?></p>
                <button type="button" class="button" id="clear-all-cache">
                    <?php esc_html_e('Clear All Cache', 'acf-carousel-elementor'); ?>
                </button>
            </div>
            
            <div class="acf-carousel-tool-section">
                <h3><?php esc_html_e('Reset Settings', 'acf-carousel-elementor'); ?></h3>
                <p><?php esc_html_e('Reset all plugin settings to default values.', 'acf-carousel-elementor'); ?></p>
                <button type="button" class="button button-secondary" id="reset-settings">
                    <?php esc_html_e('Reset to Defaults', 'acf-carousel-elementor'); ?>
                </button>
            </div>
            
            <div class="acf-carousel-tool-section">
                <h3><?php esc_html_e('Export/Import Settings', 'acf-carousel-elementor'); ?></h3>
                <p><?php esc_html_e('Export your settings or import from another site.', 'acf-carousel-elementor'); ?></p>
                <p>
                    <button type="button" class="button" id="export-settings">
                        <?php esc_html_e('Export Settings', 'acf-carousel-elementor'); ?>
                    </button>
                    <button type="button" class="button" id="import-settings">
                        <?php esc_html_e('Import Settings', 'acf-carousel-elementor'); ?>
                    </button>
                </p>
                <input type="file" id="import-file" accept=".json" style="display: none;">
            </div>
        </div>
        
        <style>
        .acf-carousel-tool-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .acf-carousel-tool-section h3 {
            margin-top: 0;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Tool actions would go here
            $('#export-settings').on('click', function() {
                // Export functionality
                window.location.href = ajaxurl + '?action=acf_carousel_export_settings&nonce=' + 
                    '<?php echo wp_create_nonce('acf_carousel_export'); ?>';
            });
            
            $('#import-settings').on('click', function() {
                $('#import-file').click();
            });
            
            $('#reset-settings').on('click', function() {
                if (confirm('<?php esc_html_e('Are you sure you want to reset all settings?', 'acf-carousel-elementor'); ?>')) {
                    // Reset functionality
                    $.post(ajaxurl, {
                        action: 'acf_carousel_reset_settings',
                        nonce: '<?php echo wp_create_nonce('acf_carousel_reset'); ?>'
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render system info tab
     */
    private function render_system_info_tab() {
        global $wp_version;
        
        $system_info = [
            'WordPress Version' => $wp_version,
            'PHP Version' => PHP_VERSION,
            'Elementor Version' => defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : 'Not installed',
            'ACF Version' => defined('ACF_VERSION') ? ACF_VERSION : 'Not installed',
            'Theme' => wp_get_theme()->get('Name'),
            'Active Plugins' => count(get_option('active_plugins', [])),
            'Memory Limit' => ini_get('memory_limit'),
            'Max Upload Size' => size_format(wp_max_upload_size()),
        ];
        
        ?>
        <div class="acf-carousel-system-info">
            <h2><?php esc_html_e('System Information', 'acf-carousel-elementor'); ?></h2>
            
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Setting', 'acf-carousel-elementor'); ?></th>
                        <th><?php esc_html_e('Value', 'acf-carousel-elementor'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($system_info as $label => $value): ?>
                        <tr>
                            <td><strong><?php echo esc_html($label); ?></strong></td>
                            <td><?php echo esc_html($value); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <h3><?php esc_html_e('Plugin Status', 'acf-carousel-elementor'); ?></h3>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Component', 'acf-carousel-elementor'); ?></th>
                        <th><?php esc_html_e('Status', 'acf-carousel-elementor'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong><?php esc_html_e('Elementor', 'acf-carousel-elementor'); ?></strong></td>
                        <td>
                            <?php if (did_action('elementor/loaded')): ?>
                                <span style="color: green;">✓ <?php esc_html_e('Active', 'acf-carousel-elementor'); ?></span>
                            <?php else: ?>
                                <span style="color: red;">✗ <?php esc_html_e('Not Active', 'acf-carousel-elementor'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e('Advanced Custom Fields', 'acf-carousel-elementor'); ?></strong></td>
                        <td>
                            <?php if (class_exists('ACF')): ?>
                                <span style="color: green;">✓ <?php esc_html_e('Active', 'acf-carousel-elementor'); ?></span>
                            <?php else: ?>
                                <span style="color: red;">✗ <?php esc_html_e('Not Active', 'acf-carousel-elementor'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Render field types
     */
    public function render_checkbox_field($args) {
        $name = $args['name'];
        $value = $args['value'];
        $description = $args['description'] ?? '';
        
        printf(
            '<label><input type="checkbox" name="acf_carousel_elementor_settings[%s]" value="1" %s> %s</label>',
            esc_attr($name),
            checked($value, true, false),
            esc_html($description)
        );
    }
    
    public function render_number_field($args) {
        $name = $args['name'];
        $value = $args['value'];
        $min = $args['min'] ?? '';
        $max = $args['max'] ?? '';
        $step = $args['step'] ?? '';
        $description = $args['description'] ?? '';
        
        printf(
            '<input type="number" name="acf_carousel_elementor_settings[%s]" value="%s" min="%s" max="%s" step="%s" class="small-text">',
            esc_attr($name),
            esc_attr($value),
            esc_attr($min),
            esc_attr($max),
            esc_attr($step)
        );
        
        if ($description) {
            printf('<p class="description">%s</p>', esc_html($description));
        }
    }
    
    public function render_textarea_field($args) {
        $name = $args['name'];
        $value = $args['value'];
        $rows = $args['rows'] ?? 5;
        $description = $args['description'] ?? '';
        
        printf(
            '<textarea name="acf_carousel_elementor_settings[%s]" rows="%d" class="large-text">%s</textarea>',
            esc_attr($name),
            esc_attr($rows),
            esc_textarea($value)
        );
        
        if ($description) {
            printf('<p class="description">%s</p>', esc_html($description));
        }
    }
    
    public function render_post_types_field($args) {
        $name = $args['name'];
        $selected_types = $args['value'];
        $description = $args['description'] ?? '';
        
        $post_types = get_post_types(['public' => true], 'objects');
        
        echo '<fieldset>';
        foreach ($post_types as $post_type) {
            $checked = in_array($post_type->name, $selected_types) ? 'checked' : '';
            printf(
                '<label><input type="checkbox" name="acf_carousel_elementor_settings[%s][]" value="%s" %s> %s</label><br>',
                esc_attr($name),
                esc_attr($post_type->name),
                esc_attr($checked),
                esc_html($post_type->label)
            );
        }
        echo '</fieldset>';
        
        if ($description) {
            printf('<p class="description">%s</p>', esc_html($description));
        }
    }
    
    /**
     * Render section descriptions
     */
    public function render_general_section() {
        echo '<p>' . esc_html__('Configure general plugin settings and behavior.', 'acf-carousel-elementor') . '</p>';
    }
    
    public function render_performance_section() {
        echo '<p>' . esc_html__('Optimize carousel performance and loading behavior.', 'acf-carousel-elementor') . '</p>';
    }
    
    public function render_defaults_section() {
        echo '<p>' . esc_html__('Set default values for new carousel widgets.', 'acf-carousel-elementor') . '</p>';
    }
    
    public function render_advanced_section() {
        echo '<p>' . esc_html__('Advanced settings for developers and power users.', 'acf-carousel-elementor') . '</p>';
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = [];
        
        // Boolean fields
        $boolean_fields = [
            'load_embla_from_cdn',
            'enable_global_styles',
            'enable_rtl_support',
            'enable_lazy_loading',
            'enable_autoplay_globally',
            'enable_debug_mode'
        ];
        
        foreach ($boolean_fields as $field) {
            $sanitized[$field] = isset($input[$field]) && $input[$field] == '1';
        }
        
        // Number fields
        $sanitized['cache_duration'] = absint($input['cache_duration'] ?? 12);
        $sanitized['preload_slides'] = absint($input['preload_slides'] ?? 2);
        $sanitized['default_slides_to_show'] = absint($input['default_slides_to_show'] ?? 3);
        $sanitized['default_slides_to_scroll'] = absint($input['default_slides_to_scroll'] ?? 1);
        $sanitized['default_autoplay_delay'] = absint($input['default_autoplay_delay'] ?? 4000);
        
        // Text fields
        $sanitized['custom_css'] = wp_strip_all_tags($input['custom_css'] ?? '');
        
        // Array fields
        $sanitized['allowed_post_types'] = isset($input['allowed_post_types']) 
            ? array_map('sanitize_text_field', $input['allowed_post_types'])
            : [];
        
        return $sanitized;
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ('settings_page_' . $this->menu_slug !== $hook) {
            return;
        }
        
        wp_enqueue_script(
            'acf-carousel-admin',
            ACF_CAROUSEL_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            $this->version,
            true
        );
        
        wp_enqueue_style(
            'acf-carousel-admin',
            ACF_CAROUSEL_PLUGIN_URL . 'assets/css/admin.css',
            [],
            $this->version
        );
        
        wp_localize_script('acf-carousel-admin', 'acfCarouselAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('acf_carousel_admin_nonce'),
            'strings' => [
                'confirmReset' => esc_html__('Are you sure you want to reset all settings?', 'acf-carousel-elementor'),
                'cacheCleared' => esc_html__('Cache cleared successfully!', 'acf-carousel-elementor'),
                'error' => esc_html__('An error occurred. Please try again.', 'acf-carousel-elementor'),
                'importing' => esc_html__('Importing settings...', 'acf-carousel-elementor'),
                'importComplete' => esc_html__('Settings imported successfully!', 'acf-carousel-elementor')
            ]
        ]);
    }
    
    /**
     * Show admin notices
     */
    public function show_admin_notices() {
        // Check for missing dependencies
        $dependencies = get_option('acf_carousel_missing_dependencies', []);
        
        if (!empty($dependencies)) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <strong><?php esc_html_e('ACF Carousel:', 'acf-carousel-elementor'); ?></strong>
                    <?php esc_html_e('The following required plugins are missing:', 'acf-carousel-elementor'); ?>
                </p>
                <ul>
                    <?php foreach ($dependencies as $dependency): ?>
                        <li>
                            <strong><?php echo esc_html($dependency['name']); ?></strong>
                            <?php if ($dependency['required']): ?>
                                <span style="color: red;"><?php esc_html_e('(Required)', 'acf-carousel-elementor'); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p>
                    <a href="<?php echo esc_url(admin_url('plugins.php')); ?>" class="button">
                        <?php esc_html_e('Go to Plugins', 'acf-carousel-elementor'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
        
        // Show update notice if needed
        $current_version = get_option('acf_carousel_elementor_version', '0.0.0');
        if (version_compare($current_version, ACF_CAROUSEL_VERSION, '<')) {
            ?>
            <div class="notice notice-info is-dismissible">
                <p>
                    <strong><?php esc_html_e('ACF Carousel:', 'acf-carousel-elementor'); ?></strong>
                    <?php 
                    printf(
                        esc_html__('Plugin updated to version %s. Check the changelog for new features!', 'acf-carousel-elementor'),
                        esc_html(ACF_CAROUSEL_VERSION)
                    ); 
                    ?>
                </p>
            </div>
            <?php
        }
        
        // Show cache notice if cache is disabled
        $settings = get_option('acf_carousel_elementor_settings', []);
        if (isset($settings['cache_duration']) && $settings['cache_duration'] == 0) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php esc_html_e('ACF Carousel:', 'acf-carousel-elementor'); ?></strong>
                    <?php esc_html_e('Caching is disabled. This may impact performance on sites with many carousels.', 'acf-carousel-elementor'); ?>
                </p>
            </div>
            <?php
        }
    }
    
    /**
     * Add plugin action links
     */
    public function add_plugin_action_links($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('options-general.php?page=' . $this->menu_slug)),
            esc_html__('Settings', 'acf-carousel-elementor')
        );
        
        $docs_link = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url('https://your-website.com/docs'),
            esc_html__('Documentation', 'acf-carousel-elementor')
        );
        
        array_unshift($links, $settings_link, $docs_link);
        
        return $links;
    }
    
    /**
     * AJAX handlers
     */
    public function init_ajax_handlers() {
        add_action('wp_ajax_acf_carousel_clear_cache', [$this, 'ajax_clear_cache']);
        add_action('wp_ajax_acf_carousel_reset_settings', [$this, 'ajax_reset_settings']);
        add_action('wp_ajax_acf_carousel_export_settings', [$this, 'ajax_export_settings']);
        add_action('wp_ajax_acf_carousel_import_settings', [$this, 'ajax_import_settings']);
        add_action('wp_ajax_acf_carousel_get_post_types', [$this, 'ajax_get_post_types']);
        add_action('wp_ajax_acf_carousel_get_acf_fields', [$this, 'ajax_get_acf_fields']);
    }
    
    /**
     * AJAX: Clear cache
     */
    public function ajax_clear_cache() {
        check_ajax_referer('acf_carousel_clear_cache', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'acf-carousel-elementor'));
        }
        
        // Clear all carousel-related transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_acf_carousel_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_acf_carousel_%'");
        
        // Clear object cache if available
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        wp_send_json_success([
            'message' => esc_html__('Cache cleared successfully!', 'acf-carousel-elementor')
        ]);
    }
    
    /**
     * AJAX: Reset settings
     */
    public function ajax_reset_settings() {
        check_ajax_referer('acf_carousel_reset', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'acf-carousel-elementor'));
        }
        
        // Reset to default settings
        $default_settings = [
            'load_embla_from_cdn' => true,
            'enable_global_styles' => true,
            'enable_rtl_support' => false,
            'enable_lazy_loading' => true,
            'cache_duration' => 12,
            'preload_slides' => 2,
            'default_slides_to_show' => 3,
            'default_slides_to_scroll' => 1,
            'default_autoplay_delay' => 4000,
            'enable_autoplay_globally' => false,
            'custom_css' => '',
            'enable_debug_mode' => false,
            'allowed_post_types' => []
        ];
        
        update_option('acf_carousel_elementor_settings', $default_settings);
        
        wp_send_json_success([
            'message' => esc_html__('Settings reset successfully!', 'acf-carousel-elementor')
        ]);
    }
    
    /**
     * AJAX: Export settings
     */
    public function ajax_export_settings() {
        check_ajax_referer('acf_carousel_export', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'acf-carousel-elementor'));
        }
        
        $settings = get_option('acf_carousel_elementor_settings', []);
        $export_data = [
            'plugin_version' => ACF_CAROUSEL_VERSION,
            'export_date' => current_time('mysql'),
            'site_url' => get_site_url(),
            'settings' => $settings
        ];
        
        $filename = 'acf-carousel-settings-' . date('Y-m-d-H-i-s') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        
        echo wp_json_encode($export_data, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * AJAX: Import settings
     */
    public function ajax_import_settings() {
        check_ajax_referer('acf_carousel_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => esc_html__('Permission denied.', 'acf-carousel-elementor')]);
        }
        
        if (!isset($_FILES['import_file'])) {
            wp_send_json_error(['message' => esc_html__('No file uploaded.', 'acf-carousel-elementor')]);
        }
        
        $file = $_FILES['import_file'];
        $file_content = file_get_contents($file['tmp_name']);
        $import_data = json_decode($file_content, true);
        
        if (!$import_data || !isset($import_data['settings'])) {
            wp_send_json_error(['message' => esc_html__('Invalid import file.', 'acf-carousel-elementor')]);
        }
        
        // Sanitize and update settings
        $sanitized_settings = $this->sanitize_settings($import_data['settings']);
        update_option('acf_carousel_elementor_settings', $sanitized_settings);
        
        wp_send_json_success([
            'message' => esc_html__('Settings imported successfully!', 'acf-carousel-elementor')
        ]);
    }
    
    /**
     * AJAX: Get available post types
     */
    public function ajax_get_post_types() {
        check_ajax_referer('acf_carousel_admin_nonce', 'nonce');
        
        $post_types = get_post_types(['public' => true], 'objects');
        $formatted_types = [];
        
        foreach ($post_types as $post_type) {
            $formatted_types[] = [
                'value' => $post_type->name,
                'label' => $post_type->label,
                'count' => wp_count_posts($post_type->name)->publish
            ];
        }
        
        wp_send_json_success($formatted_types);
    }
    
    /**
     * AJAX: Get ACF fields for post type
     */
    public function ajax_get_acf_fields() {
        check_ajax_referer('acf_carousel_admin_nonce', 'nonce');
        
        $post_type = sanitize_text_field($_POST['post_type'] ?? '');
        
        if (!$post_type) {
            wp_send_json_error(['message' => esc_html__('Post type is required.', 'acf-carousel-elementor')]);
        }
        
        $field_groups = acf_get_field_groups(['post_type' => $post_type]);
        $fields = [];
        
        foreach ($field_groups as $field_group) {
            $group_fields = acf_get_fields($field_group);
            foreach ($group_fields as $field) {
                $fields[] = [
                    'key' => $field['key'],
                    'name' => $field['name'],
                    'label' => $field['label'],
                    'type' => $field['type']
                ];
            }
        }
        
        wp_send_json_success($fields);
    }
}

// Initialize admin class
if (is_admin()) {
    $acf_carousel_admin = new ACF_Carousel_Admin('acf-carousel-elementor', ACF_CAROUSEL_VERSION);
    $acf_carousel_admin->init_ajax_handlers();
}