<?php
/**
 * Hero Carousel Template
 * templates/hero-carousel-template.php
 * 
 * Template especializado para mostrar carrusel estilo hero section
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables disponibles:
// $posts_query - Query de posts
// $settings - Configuraciones del widget
// $carousel_id - ID único del carousel

if ($posts_query->have_posts()) :
    $post_count = 0;
    $total_posts = $posts_query->post_count;
?>

<div class="acf-hero-carousel-wrapper" id="<?php echo esc_attr($carousel_id); ?>" 
     data-loop="<?php echo esc_attr($settings['loop']); ?>"
     data-draggable="<?php echo esc_attr($settings['draggable']); ?>"
     data-slides-to-scroll="<?php echo esc_attr($settings['slides_to_scroll']); ?>"
     data-slides-to-show="<?php echo esc_attr($settings['slides_to_show']); ?>"
     data-autoplay="<?php echo esc_attr($settings['autoplay']); ?>"
     data-autoplay-delay="<?php echo esc_attr($settings['autoplay_delay']); ?>">
    
    <!-- Background Layer -->
    <div class="hero-background-layer">
        <?php 
        // Usar la primera imagen como fondo
        $posts_query->the_post();
        if (has_post_thumbnail()) {
            the_post_thumbnail('full', ['class' => 'hero-bg-image']);
        }
        $posts_query->rewind_posts();
        ?>
        <div class="hero-overlay"></div>
    </div>
    
    <!-- Content Section -->
    <div class="hero-content-section">
        
        <!-- Left Content Area -->
        <div class="hero-text-content">
            <?php if (!empty($settings['hero_category'])): ?>
                <div class="hero-category">
                    <?php echo esc_html($settings['hero_category']); ?>
                    <div class="hero-category-line"></div>
                </div>
            <?php endif; ?>
            
            <h1 class="hero-main-title">
                <?php echo esc_html($settings['hero_title'] ?: 'Discover Amazing Destinations'); ?>
            </h1>
            
            <?php if (!empty($settings['hero_subtitle'])): ?>
                <p class="hero-subtitle">
                    <?php echo esc_html($settings['hero_subtitle']); ?>
                </p>
            <?php endif; ?>
            
            <?php if ($settings['show_hero_cta'] === 'yes'): ?>
                <div class="hero-cta-section">
                    <a href="<?php echo esc_url($settings['hero_cta_link']['url'] ?? '#'); ?>" 
                       class="hero-cta-button"
                       <?php echo ($settings['hero_cta_link']['is_external'] ?? false) ? 'target="_blank" rel="noopener"' : ''; ?>>
                        <?php echo esc_html($settings['hero_cta_text'] ?: 'Book Your Destination'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Right Carousel Area -->
        <div class="hero-carousel-area">
            
            <!-- Navigation Arrows -->
            <?php if ($settings['show_arrows'] === 'yes'): ?>
                <div class="hero-nav-arrows">
                    <button class="hero-arrow hero-arrow-prev" type="button" aria-label="Previous">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15,18 9,12 15,6"></polyline>
                        </svg>
                    </button>
                    <button class="hero-arrow hero-arrow-next" type="button" aria-label="Next">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9,6 15,12 9,18"></polyline>
                        </svg>
                    </button>
                </div>
            <?php endif; ?>
            
            <!-- Carousel Container -->
            <div class="hero-embla">
                <div class="hero-embla__container">
                    <?php
                    while ($posts_query->have_posts()) :
                        $posts_query->the_post();
                        $post_count++;
                        
                        // Obtener campos ACF
                        $fields = get_fields();
                        $location = $fields['location'] ?? '';
                        $rating = $fields['rating'] ?? '';
                        $price = $fields['price'] ?? '';
                        $duration = $fields['duration'] ?? '';
                        ?>
                        
                        <div class="hero-embla__slide">
                            <div class="hero-card" data-post-id="<?php echo esc_attr(get_the_ID()); ?>">
                                
                                <!-- Card Image -->
                                <div class="hero-card-image">
                                    <?php if (has_post_thumbnail()): ?>
                                        <img src="<?php echo esc_url(get_the_post_thumbnail_url(null, 'large')); ?>" 
                                             alt="<?php echo esc_attr(get_the_title()); ?>"
                                             loading="lazy">
                                    <?php endif; ?>
                                    
                                    <!-- Image Overlay -->
                                    <div class="hero-card-overlay">
                                        <div class="hero-card-content">
                                            
                                            <!-- Card Title -->
                                            <h3 class="hero-card-title">
                                                <?php the_title(); ?>
                                            </h3>
                                            
                                            <!-- Card Meta Info -->
                                            <div class="hero-card-meta">
                                                <?php if ($location): ?>
                                                    <div class="hero-meta-item">
                                                        <svg class="hero-meta-icon" viewBox="0 0 24 24" fill="currentColor">
                                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                                            <circle cx="12" cy="9" r="2.5"/>
                                                        </svg>
                                                        <span><?php echo esc_html($location); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($duration): ?>
                                                    <div class="hero-meta-item">
                                                        <svg class="hero-meta-icon" viewBox="0 0 24 24" fill="currentColor">
                                                            <circle cx="12" cy="12" r="10"/>
                                                            <polyline points="12,6 12,12 16,14"/>
                                                        </svg>
                                                        <span><?php echo esc_html($duration); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Rating -->
                                            <?php if ($rating): ?>
                                                <div class="hero-card-rating">
                                                    <div class="hero-stars">
                                                        <?php 
                                                        $rating_num = floatval($rating);
                                                        for ($i = 1; $i <= 5; $i++): 
                                                            $star_class = $i <= $rating_num ? 'star-filled' : 'star-empty';
                                                        ?>
                                                            <svg class="hero-star <?php echo esc_attr($star_class); ?>" viewBox="0 0 24 24" fill="currentColor">
                                                                <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/>
                                                            </svg>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <span class="hero-rating-text"><?php echo esc_html($rating); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Price -->
                                            <?php if ($price): ?>
                                                <div class="hero-card-price">
                                                    <span class="hero-price-amount"><?php echo esc_html($price); ?></span>
                                                    <span class="hero-price-unit">per person</span>
                                                </div>
                                            <?php endif; ?>
                                            
                                        </div>
                                        
                                        <!-- Card Action Button -->
                                        <div class="hero-card-action">
                                            <a href="<?php the_permalink(); ?>" 
                                               class="hero-card-button"
                                               aria-label="<?php echo esc_attr(sprintf('View details for %s', get_the_title())); ?>">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="m9 18 6-6-6-6"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        
                    <?php endwhile; ?>
                </div>
            </div>
            
        </div>
        
    </div>
    
    <!-- Bottom Info Bar -->
    <div class="hero-info-bar">
        <div class="hero-slide-counter">
            <span class="current-slide">1</span>
            <span class="slide-separator">/</span>
            <span class="total-slides"><?php echo esc_html($total_posts); ?></span>
        </div>
        
        <?php if ($settings['show_dots'] === 'yes'): ?>
            <div class="hero-dots-container">
                <!-- Dots will be generated by JavaScript -->
            </div>
        <?php endif; ?>
    </div>
    
</div>

<script>
jQuery(document).ready(function($) {
    console.log('🎠 Hero Carousel: Starting initialization...');
    
    function initHeroCarousel() {
        if (typeof EmblaCarousel === 'undefined') {
            console.log('⚠️ EmblaCarousel not found, loading from CDN...');
            
            // Cargar Embla desde CDN
            const script1 = document.createElement('script');
            script1.src = 'https://unpkg.com/embla-carousel@8.0.0/embla-carousel.umd.js';
            script1.onload = function() {
                const script2 = document.createElement('script');
                script2.src = 'https://unpkg.com/embla-carousel-autoplay@8.0.0/embla-carousel-autoplay.umd.js';
                script2.onload = function() {
                    setTimeout(setupHeroCarousel, 100);
                };
                document.head.appendChild(script2);
            };
            document.head.appendChild(script1);
        } else {
            setupHeroCarousel();
        }
    }
    
    function setupHeroCarousel() {
        console.log('🔧 Setting up Hero Carousel...');
        
        const heroCarouselId = '<?php echo esc_js($carousel_id); ?>';
        const heroEmblaNode = document.querySelector('#' + heroCarouselId + ' .hero-embla');
        
        if (!heroEmblaNode) {
            console.error('❌ Hero embla node not found');
            return;
        }
        
        console.log('✅ Hero embla node found:', heroEmblaNode);
        
        const heroOptions = {
            slidesToScroll: 1,
            loop: <?php echo $settings['loop'] === 'yes' ? 'true' : 'false'; ?>,
            dragFree: <?php echo $settings['draggable'] === 'yes' ? 'true' : 'false'; ?>,
            align: 'start',
            containScroll: 'trimSnaps'
        };
        
        const heroPlugins = [];
        <?php if ($settings['autoplay'] === 'yes'): ?>
        if (typeof EmblaCarouselAutoplay !== 'undefined') {
            heroPlugins.push(EmblaCarouselAutoplay({ 
                delay: <?php echo intval($settings['autoplay_delay']); ?>,
                stopOnInteraction: true,
                stopOnMouseEnter: true
            }));
            console.log('✅ Autoplay plugin added');
        }
        <?php endif; ?>
        
        let heroEmbla;
        try {
            heroEmbla = EmblaCarousel(heroEmblaNode, heroOptions, heroPlugins);
            console.log('✅ Hero Embla instance created successfully');
        } catch (error) {
            console.error('❌ Error creating Hero Embla instance:', error);
            return;
        }
        
        // Setup navigation
        const prevBtn = document.querySelector('#' + heroCarouselId + ' .hero-arrow-prev');
        const nextBtn = document.querySelector('#' + heroCarouselId + ' .hero-arrow-next');
        
        console.log('🔍 Navigation buttons:', {prev: !!prevBtn, next: !!nextBtn});
        
        if (prevBtn && nextBtn) {
            // Limpiar eventos existentes clonando los elementos
            const newPrevBtn = prevBtn.cloneNode(true);
            const newNextBtn = nextBtn.cloneNode(true);
            
            prevBtn.parentNode.replaceChild(newPrevBtn, prevBtn);
            nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);
            
            // Agregar nuevos eventos
            newPrevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                heroEmbla.scrollPrev();
                console.log('⬅️ Hero: Previous clicked');
            });
            
            newNextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                heroEmbla.scrollNext();
                console.log('➡️ Hero: Next clicked');
            });
            
            // Forzar estilos clickeables
            [newPrevBtn, newNextBtn].forEach(btn => {
                btn.style.cursor = 'pointer';
                btn.style.pointerEvents = 'all';
                btn.style.zIndex = '9999';
            });
            
            console.log('✅ Hero navigation buttons configured');
        }
        
        // Update slide counter
        const updateSlideCounter = () => {
            const current = heroEmbla.selectedScrollSnap() + 1;
            const currentSlideEl = document.querySelector('#' + heroCarouselId + ' .current-slide');
            if (currentSlideEl) {
                currentSlideEl.textContent = current;
            }
        };
        
        heroEmbla.on('select', updateSlideCounter);
        heroEmbla.on('init', updateSlideCounter);
        
        // Generate and handle dots
        <?php if ($settings['show_dots'] === 'yes'): ?>
        const dotsContainer = document.querySelector('#' + heroCarouselId + ' .hero-dots-container');
        const addDots = () => {
            if (!dotsContainer) return;
            
            dotsContainer.innerHTML = heroEmbla.scrollSnapList()
                .map((_, index) => `<button class="hero-dot" data-index="${index}" type="button"></button>`)
                .join('');
                
            const dots = dotsContainer.querySelectorAll('.hero-dot');
            dots.forEach((dot, index) => {
                dot.style.cursor = 'pointer';
                dot.style.pointerEvents = 'all';
                
                dot.addEventListener('click', function(e) {
                    e.preventDefault();
                    heroEmbla.scrollTo(index);
                    console.log(`🎯 Hero: Dot ${index + 1} clicked`);
                });
            });
        };
        
        const updateDots = () => {
            const dots = dotsContainer?.querySelectorAll('.hero-dot');
            if (!dots) return;
            
            dots.forEach((dot, index) => {
                if (index === heroEmbla.selectedScrollSnap()) {
                    dot.classList.add('hero-dot-active');
                } else {
                    dot.classList.remove('hero-dot-active');
                }
            });
        };
        
        heroEmbla.on('init', addDots);
        heroEmbla.on('select', updateDots);
        heroEmbla.on('init', updateDots);
        console.log('✅ Hero dots configured');
        <?php endif; ?>
        
        // Background image changer
        const updateBackgroundImage = () => {
            const currentIndex = heroEmbla.selectedScrollSnap();
            const slides = heroEmblaNode.querySelectorAll('.hero-card img');
            const bgImage = document.querySelector('#' + heroCarouselId + ' .hero-bg-image');
            
            if (slides[currentIndex] && bgImage) {
                bgImage.style.transition = 'opacity 0.5s ease';
                bgImage.style.opacity = '0.8';
                
                setTimeout(() => {
                    bgImage.src = slides[currentIndex].src;
                    bgImage.style.opacity = '1';
                }, 250);
            }
        };
        
        heroEmbla.on('select', updateBackgroundImage);
        console.log('✅ Hero background changer configured');
        
        // Marcar como inicializado
        const carouselWrapper = document.querySelector('#' + heroCarouselId);
        if (carouselWrapper) {
            carouselWrapper.classList.add('hero-carousel-initialized');
            carouselWrapper.heroEmblaInstance = heroEmbla;
        }
        
        console.log('🎉 Hero Carousel initialized successfully!');
        
        // Test automático
        setTimeout(() => {
            console.log('🧪 Hero: Auto test - clicking next button');
            const testBtn = document.querySelector('#' + heroCarouselId + ' .hero-arrow-next');
            if (testBtn) testBtn.click();
        }, 2000);
    }
    
    // Inicializar
    initHeroCarousel();
    
    // También intentar después de un delay por si hay problemas de timing
    setTimeout(function() {
        const heroWrapper = document.querySelector('#<?php echo esc_js($carousel_id); ?>');
        if (heroWrapper && !heroWrapper.classList.contains('hero-carousel-initialized')) {
            console.log('🔄 Hero: Retry initialization...');
            initHeroCarousel();
        }
    }, 3000);
    
    // Exponer función global para test manual
    window.testHeroCarousel = function() {
        console.log('🧪 Manual hero carousel test');
        setupHeroCarousel();
    };
});
</script>

<?php 
wp_reset_postdata();
endif;
?>