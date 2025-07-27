<?php
/**
 * ACF Carousel Widget for Elementor
 * widgets/acf-carousel-widget.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class ACF_Carousel_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'acf-carousel';
    }
    
    public function get_title() {
        return esc_html__('ACF Custom Post Carousel', 'acf-carousel-elementor');
    }
    
    public function get_icon() {
        return 'eicon-posts-carousel';
    }
    
    public function get_categories() {
        return ['basic'];
    }
    
    public function get_keywords() {
        return ['acf', 'carousel', 'posts', 'custom', 'fields'];
    }
    
    public function get_script_depends() {
        return ['embla-carousel', 'embla-carousel-autoplay', 'acf-carousel-widget'];
    }
    
    public function get_style_depends() {
        return ['acf-carousel-widget'];
    }
    
    protected function register_controls() {
        
        // Content Tab
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'acf-carousel-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        // Post Type Selection
        $post_types = get_post_types(['public' => true], 'objects');
        $post_type_options = [];
        foreach ($post_types as $post_type) {
            $post_type_options[$post_type->name] = $post_type->label;
        }
        
        $this->add_control(
            'post_type',
            [
                'label' => esc_html__('Post Type', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'post',
                'options' => $post_type_options,
            ]
        );
        
        $this->add_control(
            'posts_per_page',
            [
                'label' => esc_html__('Posts Count', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 6,
                'min' => 1,
                'max' => 50,
            ]
        );
        
        $this->add_control(
            'order_by',
            [
                'label' => esc_html__('Order By', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date' => esc_html__('Date', 'acf-carousel-elementor'),
                    'title' => esc_html__('Title', 'acf-carousel-elementor'),
                    'menu_order' => esc_html__('Menu Order', 'acf-carousel-elementor'),
                    'rand' => esc_html__('Random', 'acf-carousel-elementor'),
                ],
            ]
        );
        
        $this->add_control(
            'order',
            [
                'label' => esc_html__('Order', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'ASC' => esc_html__('Ascending', 'acf-carousel-elementor'),
                    'DESC' => esc_html__('Descending', 'acf-carousel-elementor'),
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Carousel Settings
        $this->start_controls_section(
            'carousel_settings_section',
            [
                'label' => esc_html__('Carousel Settings', 'acf-carousel-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'slides_to_show',
            [
                'label' => esc_html__('Slides to Show', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 3,
                'min' => 1,
                'max' => 8,
            ]
        );
        
        $this->add_control(
            'slides_to_scroll',
            [
                'label' => esc_html__('Slides to Scroll', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 1,
                'min' => 1,
                'max' => 5,
            ]
        );
        
        $this->add_control(
            'autoplay',
            [
                'label' => esc_html__('Autoplay', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'acf-carousel-elementor'),
                'label_off' => esc_html__('No', 'acf-carousel-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );
        
        $this->add_control(
            'autoplay_delay',
            [
                'label' => esc_html__('Autoplay Delay (ms)', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 4000,
                'min' => 1000,
                'max' => 10000,
                'step' => 500,
                'condition' => [
                    'autoplay' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'loop',
            [
                'label' => esc_html__('Infinite Loop', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'acf-carousel-elementor'),
                'label_off' => esc_html__('No', 'acf-carousel-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'draggable',
            [
                'label' => esc_html__('Drag & Drop', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'acf-carousel-elementor'),
                'label_off' => esc_html__('No', 'acf-carousel-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_dots',
            [
                'label' => esc_html__('Show Dots', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'acf-carousel-elementor'),
                'label_off' => esc_html__('No', 'acf-carousel-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_arrows',
            [
                'label' => esc_html__('Show Arrows', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'acf-carousel-elementor'),
                'label_off' => esc_html__('No', 'acf-carousel-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->end_controls_section();
        
        // Card Content Settings
        $this->start_controls_section(
            'card_content_section',
            [
                'label' => esc_html__('Card Content', 'acf-carousel-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'show_title',
            [
                'label' => esc_html__('Show Title', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'acf-carousel-elementor'),
                'label_off' => esc_html__('No', 'acf-carousel-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_excerpt',
            [
                'label' => esc_html__('Show Excerpt', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'acf-carousel-elementor'),
                'label_off' => esc_html__('No', 'acf-carousel-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'excerpt_length',
            [
                'label' => esc_html__('Excerpt Length', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 20,
                'min' => 5,
                'max' => 100,
                'condition' => [
                    'show_excerpt' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'show_featured_image',
            [
                'label' => esc_html__('Show Featured Image', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'acf-carousel-elementor'),
                'label_off' => esc_html__('No', 'acf-carousel-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'image_size',
            [
                'label' => esc_html__('Image Size', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'medium',
                'options' => [
                    'thumbnail' => esc_html__('Thumbnail', 'acf-carousel-elementor'),
                    'medium' => esc_html__('Medium', 'acf-carousel-elementor'),
                    'large' => esc_html__('Large', 'acf-carousel-elementor'),
                    'full' => esc_html__('Full', 'acf-carousel-elementor'),
                ],
                'condition' => [
                    'show_featured_image' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'show_read_more',
            [
                'label' => esc_html__('Show Read More Button', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'acf-carousel-elementor'),
                'label_off' => esc_html__('No', 'acf-carousel-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'read_more_text',
            [
                'label' => esc_html__('Read More Text', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Read More', 'acf-carousel-elementor'),
                'condition' => [
                    'show_read_more' => 'yes',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Tab - Card Styles
        $this->start_controls_section(
            'style_card_section',
            [
                'label' => esc_html__('Card', 'acf-carousel-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_responsive_control(
            'card_padding',
            [
                'label' => esc_html__('Padding', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .acf-carousel-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'card_margin',
            [
                'label' => esc_html__('Margin', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .acf-carousel-slide' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'card_background',
                'label' => esc_html__('Background', 'acf-carousel-elementor'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .acf-carousel-card',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'card_border',
                'label' => esc_html__('Border', 'acf-carousel-elementor'),
                'selector' => '{{WRAPPER}} .acf-carousel-card',
            ]
        );
        
        $this->add_responsive_control(
            'card_border_radius',
            [
                'label' => esc_html__('Border Radius', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .acf-carousel-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow',
                'label' => esc_html__('Box Shadow', 'acf-carousel-elementor'),
                'selector' => '{{WRAPPER}} .acf-carousel-card',
            ]
        );
        
        $this->end_controls_section();
        
        // Navigation Styles
        $this->start_controls_section(
            'style_navigation_section',
            [
                'label' => esc_html__('Navigation', 'acf-carousel-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'arrows_size',
            [
                'label' => esc_html__('Arrows Size', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 20,
                        'max' => 80,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 40,
                ],
                'selectors' => [
                    '{{WRAPPER}} .embla__button' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'arrows_color',
            [
                'label' => esc_html__('Arrows Color', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333',
                'selectors' => [
                    '{{WRAPPER}} .embla__button' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'dots_size',
            [
                'label' => esc_html__('Dots Size', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 8,
                        'max' => 30,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 12,
                ],
                'selectors' => [
                    '{{WRAPPER}} .embla__dot' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'dots_color',
            [
                'label' => esc_html__('Dots Color', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ccc',
                'selectors' => [
                    '{{WRAPPER}} .embla__dot' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'dots_active_color',
            [
                'label' => esc_html__('Active Dot Color', 'acf-carousel-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333',
                'selectors' => [
                    '{{WRAPPER}} .embla__dot--selected' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $query_args = [
            'post_type' => $settings['post_type'],
            'posts_per_page' => $settings['posts_per_page'],
            'orderby' => $settings['order_by'],
            'order' => $settings['order'],
            'post_status' => 'publish',
        ];
        
        $posts_query = new WP_Query($query_args);
        
        if ($posts_query->have_posts()) :
            $carousel_id = 'acf-carousel-' . $this->get_id();
            ?>
            <div class="acf-carousel-wrapper" id="<?php echo esc_attr($carousel_id); ?>">
                <?php if ($settings['show_arrows'] === 'yes') : ?>
                <div class="embla__buttons">
                    <button class="embla__button embla__button--prev" type="button">
                        <svg class="embla__button__svg" viewBox="0 0 532 532">
                            <path fill="currentColor" d="m355.66 11.354c13.793-13.805 36.208-13.805 50.001 0 13.785 13.804 13.785 36.238 0 50.034L201.22 266l204.442 204.61c13.785 13.805 13.785 36.239 0 50.044-13.793 13.796-36.208 13.796-50.002 0a5994246.277 5994246.277 0 0 0-229.332-229.454 35.065 35.065 0 0 1-10.326-25.126c0-9.2 3.393-18.26 10.326-25.2C172.192 194.973 332.731 34.31 355.66 11.354Z"/>
                        </svg>
                    </button>
                    <button class="embla__button embla__button--next" type="button">
                        <svg class="embla__button__svg" viewBox="0 0 532 532">
                            <path fill="currentColor" d="M176.34 520.646c-13.793 13.805-36.208 13.805-50.001 0-13.785-13.804-13.785-36.238 0-50.034L330.78 266 126.34 61.391c-13.785-13.805-13.785-36.239 0-50.044 13.793-13.796 36.208-13.796 50.002 0 22.928 22.947 206.395 206.507 229.332 229.454a35.065 35.065 0 0 1 10.326 25.126c0 9.2-3.393 18.26-10.326 25.2C359.808 337.027 199.269 497.69 176.34 520.646Z"/>
                        </svg>
                    </button>
                </div>
                <?php endif; ?>
                
                <div class="embla">
                    <div class="embla__container">
                        <?php
                        while ($posts_query->have_posts()) :
                            $posts_query->the_post();
                            ?>
                            <div class="embla__slide acf-carousel-slide">
                                <div class="acf-carousel-card">
                                    <?php if ($settings['show_featured_image'] === 'yes' && has_post_thumbnail()) : ?>
                                        <div class="acf-carousel-image">
                                            <a href="<?php the_permalink(); ?>">
                                                <?php the_post_thumbnail($settings['image_size']); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="acf-carousel-content">
                                        <?php if ($settings['show_title'] === 'yes') : ?>
                                            <h3 class="acf-carousel-title">
                                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                            </h3>
                                        <?php endif; ?>
                                        
                                        <?php if ($settings['show_excerpt'] === 'yes') : ?>
                                            <div class="acf-carousel-excerpt">
                                                <?php 
                                                $excerpt = get_the_excerpt();
                                                if (str_word_count($excerpt) > $settings['excerpt_length']) {
                                                    $excerpt = implode(' ', array_slice(str_word_split($excerpt), 0, $settings['excerpt_length'])) . '...';
                                                }
                                                echo esc_html($excerpt);
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php
                                        // Display ACF fields if they exist
                                        $fields = get_fields();
                                        if ($fields) :
                                            ?>
                                            <div class="acf-carousel-fields">
                                                <?php foreach ($fields as $field_name => $field_value) :
                                                    if (is_string($field_value) && !empty($field_value)) :
                                                        ?>
                                                        <div class="acf-field acf-field-<?php echo esc_attr($field_name); ?>">
                                                            <strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $field_name))); ?>:</strong>
                                                            <span><?php echo esc_html($field_value); ?></span>
                                                        </div>
                                                    <?php endif;
                                                endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($settings['show_read_more'] === 'yes') : ?>
                                            <div class="acf-carousel-read-more">
                                                <a href="<?php the_permalink(); ?>" class="acf-carousel-btn">
                                                    <?php echo esc_html($settings['read_more_text']); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                </div>
                
                <?php if ($settings['show_dots'] === 'yes') : ?>
                <div class="embla__dots"></div>
                <?php endif; ?>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                if (typeof EmblaCarousel !== 'undefined') {
                    const emblaNode = document.querySelector('#<?php echo esc_js($carousel_id); ?> .embla');
                    const options = {
                        slidesToScroll: <?php echo intval($settings['slides_to_scroll']); ?>,
                        loop: <?php echo $settings['loop'] === 'yes' ? 'true' : 'false'; ?>,
                        dragFree: <?php echo $settings['draggable'] === 'yes' ? 'true' : 'false'; ?>,
                        breakpoints: {
                            '(max-width: 768px)': { slidesToScroll: 1 },
                            '(min-width: 769px) and (max-width: 1024px)': { slidesToScroll: Math.min(2, <?php echo intval($settings['slides_to_scroll']); ?>) }
                        }
                    };
                    
                    const plugins = [];
                    <?php if ($settings['autoplay'] === 'yes') : ?>
                    if (typeof EmblaCarouselAutoplay !== 'undefined') {
                        plugins.push(EmblaCarouselAutoplay({ delay: <?php echo intval($settings['autoplay_delay']); ?> }));
                    }
                    <?php endif; ?>
                    
                    const embla = EmblaCarousel(emblaNode, options, plugins);
                    
                    // Setup navigation buttons
                    <?php if ($settings['show_arrows'] === 'yes') : ?>
                    const prevBtn = document.querySelector('#<?php echo esc_js($carousel_id); ?> .embla__button--prev');
                    const nextBtn = document.querySelector('#<?php echo esc_js($carousel_id); ?> .embla__button--next');
                    
                    prevBtn.addEventListener('click', embla.scrollPrev);
                    nextBtn.addEventListener('click', embla.scrollNext);
                    <?php endif; ?>
                    
                    // Setup dots navigation
                    <?php if ($settings['show_dots'] === 'yes') : ?>
                    const dotsNode = document.querySelector('#<?php echo esc_js($carousel_id); ?> .embla__dots');
                    
                    const addDotBtnsAndClickHandlers = (embla, dotsNode) => {
                        let dotNodes = [];
                        
                        const addDotBtnsWithClickHandlers = () => {
                            dotsNode.innerHTML = embla.scrollSnapList()
                                .map(() => '<button class="embla__dot" type="button"></button>')
                                .join('');
                            
                            dotNodes = Array.from(dotsNode.querySelectorAll('.embla__dot'));
                            dotNodes.forEach((dotNode, index) => {
                                dotNode.addEventListener('click', () => embla.scrollTo(index));
                            });
                        };
                        
                        const toggleDotBtnsActive = () => {
                            const previous = embla.previousScrollSnap();
                            const selected = embla.selectedScrollSnap();
                            dotNodes[previous].classList.remove('embla__dot--selected');
                            dotNodes[selected].classList.add('embla__dot--selected');
                        };
                        
                        embla.on('init', addDotBtnsWithClickHandlers);
                        embla.on('reInit', addDotBtnsWithClickHandlers);
                        embla.on('init', toggleDotBtnsActive);
                        embla.on('reInit', toggleDotBtnsActive);
                        embla.on('select', toggleDotBtnsActive);
                        
                        return () => {
                            dotsNode.innerHTML = '';
                        };
                    };
                    
                    const removeDotBtnsAndClickHandlers = addDotBtnsAndClickHandlers(embla, dotsNode);
                    embla.on('destroy', removeDotBtnsAndClickHandlers);
                    <?php endif; ?>
                    
                    // Apply responsive slides settings
                    const applyResponsiveSlides = () => {
                        const slidesToShow = <?php echo intval($settings['slides_to_show']); ?>;
                        const slides = emblaNode.querySelectorAll('.embla__slide');
                        const containerWidth = emblaNode.offsetWidth;
                        
                        let currentSlidesToShow = slidesToShow;
                        if (window.innerWidth <= 768) {
                            currentSlidesToShow = 1;
                        } else if (window.innerWidth <= 1024) {
                            currentSlidesToShow = Math.min(2, slidesToShow);
                        }
                        
                        const slideWidth = (100 / currentSlidesToShow) + '%';
                        slides.forEach(slide => {
                            slide.style.flex = '0 0 ' + slideWidth;
                        });
                    };
                    
                    applyResponsiveSlides();
                    window.addEventListener('resize', applyResponsiveSlides);
                    embla.on('reInit', applyResponsiveSlides);
                }
            });
            </script>
            <?php
        else :
            echo '<p>' . esc_html__('No posts found.', 'acf-carousel-elementor') . '</p>';
        endif;
    }
    
    protected function content_template() {
        ?>
        <#
        var carousel_id = 'acf-carousel-' + view.getID();
        #>
        <div class="acf-carousel-wrapper" id="{{ carousel_id }}">
            <# if ( settings.show_arrows === 'yes' ) { #>
            <div class="embla__buttons">
                <button class="embla__button embla__button--prev" type="button">
                    <svg class="embla__button__svg" viewBox="0 0 532 532">
                        <path fill="currentColor" d="m355.66 11.354c13.793-13.805 36.208-13.805 50.001 0 13.785 13.804 13.785 36.238 0 50.034L201.22 266l204.442 204.61c13.785 13.805 13.785 36.239 0 50.044-13.793 13.796-36.208 13.796-50.002 0a5994246.277 5994246.277 0 0 0-229.332-229.454 35.065 35.065 0 0 1-10.326-25.126c0-9.2 3.393-18.26 10.326-25.2C172.192 194.973 332.731 34.31 355.66 11.354Z"/>
                    </svg>
                </button>
                <button class="embla__button embla__button--next" type="button">
                    <svg class="embla__button__svg" viewBox="0 0 532 532">
                        <path fill="currentColor" d="M176.34 520.646c-13.793 13.805-36.208 13.805-50.001 0-13.785-13.804-13.785-36.238 0-50.034L330.78 266 126.34 61.391c-13.785-13.805-13.785-36.239 0-50.044 13.793-13.796 36.208-13.796 50.002 0 22.928 22.947 206.395 206.507 229.332 229.454a35.065 35.065 0 0 1 10.326 25.126c0 9.2-3.393 18.26-10.326 25.2C359.808 337.027 199.269 497.69 176.34 520.646Z"/>
                    </svg>
                </button>
            </div>
            <# } #>
            
            <div class="embla">
                <div class="embla__container">
                    <# for ( var i = 0; i < settings.posts_per_page; i++ ) { #>
                    <div class="embla__slide acf-carousel-slide">
                        <div class="acf-carousel-card">
                            <# if ( settings.show_featured_image === 'yes' ) { #>
                            <div class="acf-carousel-image">
                                <img src="https://via.placeholder.com/300x200/cccccc/666666?text=Image" alt="Preview Image">
                            </div>
                            <# } #>
                            
                            <div class="acf-carousel-content">
                                <# if ( settings.show_title === 'yes' ) { #>
                                <h3 class="acf-carousel-title">
                                    <a href="#">Sample Post Title {{ i + 1 }}</a>
                                </h3>
                                <# } #>
                                
                                <# if ( settings.show_excerpt === 'yes' ) { #>
                                <div class="acf-carousel-excerpt">
                                    This is a sample excerpt for the post. It shows how the content will appear in the carousel.
                                </div>
                                <# } #>
                                
                                <div class="acf-carousel-fields">
                                    <div class="acf-field">
                                        <strong>Custom Field:</strong>
                                        <span>Sample ACF Value</span>
                                    </div>
                                </div>
                                
                                <# if ( settings.show_read_more === 'yes' ) { #>
                                <div class="acf-carousel-read-more">
                                    <a href="#" class="acf-carousel-btn">{{ settings.read_more_text }}</a>
                                </div>
                                <# } #>
                            </div>
                        </div>
                    </div>
                    <# } #>
                </div>
            </div>
            
            <# if ( settings.show_dots === 'yes' ) { #>
            <div class="embla__dots">
                <# for ( var i = 0; i < Math.ceil(settings.posts_per_page / settings.slides_to_show); i++ ) { #>
                <button class="embla__dot <# if ( i === 0 ) { #>embla__dot--selected<# } #>" type="button"></button>
                <# } #>
            </div>
            <# } #>
        </div>
        <?php
    }
}
                                                            