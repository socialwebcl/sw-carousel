<?php
/**
 * Carousel Card Template
 * templates/carousel-card.php
 * 
 * Este template puede ser sobrescrito por el tema copiándolo a:
 * your-theme/acf-carousel-elementor/carousel-card.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables disponibles:
// $post - El objeto del post actual
// $settings - Configuraciones del widget
// $fields - Campos ACF del post

?>
<div class="acf-carousel-card" data-post-id="<?php echo esc_attr($post->ID); ?>">
    
    <?php if ($settings['show_featured_image'] === 'yes' && has_post_thumbnail($post->ID)) : ?>
        <div class="acf-carousel-image">
            <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" 
               aria-label="<?php echo esc_attr(sprintf(__('View %s', 'acf-carousel-elementor'), get_the_title($post->ID))); ?>">
                <?php echo get_the_post_thumbnail($post->ID, $settings['image_size'], [
                    'loading' => 'lazy',
                    'alt' => get_the_title($post->ID)
                ]); ?>
                
                <?php if (isset($settings['show_image_overlay']) && $settings['show_image_overlay'] === 'yes') : ?>
                    <div class="acf-carousel-image-overlay">
                        <span class="acf-carousel-overlay-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15 12H9M12 9V15M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                <?php endif; ?>
            </a>
        </div>
    <?php endif; ?>
    
    <div class="acf-carousel-content">
        
        <?php if ($settings['show_title'] === 'yes') : ?>
            <h3 class="acf-carousel-title">
                <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
                    <?php echo esc_html(get_the_title($post->ID)); ?>
                </a>
            </h3>
        <?php endif; ?>
        
        <?php if ($settings['show_date'] === 'yes') : ?>
            <div class="acf-carousel-meta">
                <time datetime="<?php echo esc_attr(get_the_date('c', $post->ID)); ?>" class="acf-carousel-date">
                    <?php echo esc_html(get_the_date('', $post->ID)); ?>
                </time>
                
                <?php if ($settings['show_author'] === 'yes') : ?>
                    <span class="acf-carousel-author">
                        <?php printf(__('by %s', 'acf-carousel-elementor'), get_the_author_meta('display_name', $post->post_author)); ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($settings['show_excerpt'] === 'yes') : ?>
            <div class="acf-carousel-excerpt">
                <?php 
                $excerpt = get_the_excerpt($post->ID);
                if (empty($excerpt)) {
                    $excerpt = wp_trim_words(get_the_content('', false, $post->ID), $settings['excerpt_length'], '...');
                } else {
                    $excerpt = wp_trim_words($excerpt, $settings['excerpt_length'], '...');
                }
                echo wp_kses_post($excerpt);
                ?>
            </div>
        <?php endif; ?>
        
        <?php 
        // Mostrar campos ACF si existen
        if (!empty($fields) && is_array($fields)) : 
            ?>
            <div class="acf-carousel-fields">
                <?php 
                foreach ($fields as $field_name => $field_value) :
                    // Solo mostrar campos que no estén vacíos y sean strings o números
                    if (empty($field_value) || is_array($field_value) || is_object($field_value)) {
                        continue;
                    }
                    
                    // Obtener información del campo ACF
                    $field_object = get_field_object($field_name, $post->ID);
                    $field_label = $field_object ? $field_object['label'] : ucfirst(str_replace('_', ' ', $field_name));
                    
                    ?>
                    <div class="acf-field acf-field-<?php echo esc_attr($field_name); ?>" data-field-type="<?php echo esc_attr($field_object['type'] ?? 'text'); ?>">
                        
                        <?php if ($settings['show_field_labels'] !== 'no') : ?>
                            <span class="acf-field-label">
                                <?php echo esc_html($field_label); ?>:
                            </span>
                        <?php endif; ?>
                        
                        <span class="acf-field-value">
                            <?php 
                            // Manejar diferentes tipos de campos ACF
                            switch ($field_object['type'] ?? 'text') :
                                case 'url':
                                    ?>
                                    <a href="<?php echo esc_url($field_value); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php echo esc_html($field_value); ?>
                                    </a>
                                    <?php
                                    break;
                                    
                                case 'email':
                                    ?>
                                    <a href="mailto:<?php echo esc_attr($field_value); ?>">
                                        <?php echo esc_html($field_value); ?>
                                    </a>
                                    <?php
                                    break;
                                    
                                case 'number':
                                    echo esc_html(number_format_i18n($field_value));
                                    break;
                                    
                                case 'date_picker':
                                    $date = DateTime::createFromFormat('Ymd', $field_value);
                                    echo $date ? esc_html($date->format(get_option('date_format'))) : esc_html($field_value);
                                    break;
                                    
                                default:
                                    echo esc_html($field_value);
                                    break;
                            endswitch;
                            ?>
                        </span>
                    </div>
                    <?php
                endforeach; 
                ?>
            </div>
        <?php endif; ?>
        
        <?php if ($settings['show_categories'] === 'yes') : ?>
            <?php 
            $taxonomies = get_object_taxonomies($post->post_type, 'objects');
            if (!empty($taxonomies)) :
                foreach ($taxonomies as $taxonomy) :
                    if ($taxonomy->public) :
                        $terms = get_the_terms($post->ID, $taxonomy->name);
                        if ($terms && !is_wp_error($terms)) :
                            ?>
                            <div class="acf-carousel-taxonomy acf-carousel-<?php echo esc_attr($taxonomy->name); ?>">
                                <span class="acf-taxonomy-label"><?php echo esc_html($taxonomy->label); ?>:</span>
                                <div class="acf-taxonomy-terms">
                                    <?php foreach ($terms as $term) : ?>
                                        <a href="<?php echo esc_url(get_term_link($term)); ?>" 
                                           class="acf-taxonomy-term"
                                           data-term-id="<?php echo esc_attr($term->term_id); ?>">
                                            <?php echo esc_html($term->name); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php
                        endif;
                    endif;
                endforeach;
            endif;
            ?>
        <?php endif; ?>
        
        <?php if ($settings['show_read_more'] === 'yes') : ?>
            <div class="acf-carousel-read-more">
                <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" 
                   class="acf-carousel-btn"
                   aria-label="<?php echo esc_attr(sprintf(__('Read more about %s', 'acf-carousel-elementor'), get_the_title($post->ID))); ?>">
                    <?php echo esc_html($settings['read_more_text'] ?: __('Read More', 'acf-carousel-elementor')); ?>
                    
                    <?php if ($settings['show_read_more_icon'] === 'yes') : ?>
                        <span class="acf-carousel-btn-icon" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    <?php endif; ?>
                </a>
            </div>
        <?php endif; ?>
        
    </div>
    
    <?php if ($settings['show_hover_effects'] === 'yes') : ?>
        <div class="acf-carousel-hover-overlay">
            <div class="acf-carousel-hover-content">
                <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" 
                   class="acf-carousel-hover-link"
                   aria-label="<?php echo esc_attr(sprintf(__('View %s', 'acf-carousel-elementor'), get_the_title($post->ID))); ?>">
                    <span class="acf-carousel-hover-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 3H21V9M21 3L12 12M9 5H7C5.89543 5 5 5.89543 5 7V17C5 18.1046 5.89543 19 7 19H17C18.1046 19 19 18.1046 19 17V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </a>
            </div>
        </div>
    <?php endif; ?>
    
</div>

<?php
/**
 * Hook para agregar contenido personalizado después de cada card
 * 
 * @param WP_Post $post El objeto del post actual
 * @param array $settings Configuraciones del widget
 * @param array $fields Campos ACF del post
 */
do_action('acf_carousel_after_card', $post, $settings, $fields);
?>