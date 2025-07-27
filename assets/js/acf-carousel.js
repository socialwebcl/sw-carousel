/**
 * ACF Carousel JavaScript
 * assets/js/acf-carousel.js
 */

(function($) {
    'use strict';

    // Verificar que las dependencias estén disponibles
    function waitForDependencies(callback) {
        let attempts = 0;
        const maxAttempts = 100; // 10 segundos máximo
        
        function checkDependencies() {
            attempts++;
            
            console.log(`ACF Carousel: Checking dependencies (attempt ${attempts}/${maxAttempts})`);
            
            // Verificar si EmblaCarousel está disponible
            if (typeof window.EmblaCarousel !== 'undefined') {
                console.log('ACF Carousel: ✅ EmblaCarousel found, proceeding with initialization');
                callback();
                return;
            }
            
            // Si estamos en los primeros intentos, esperar un poco más
            if (attempts < maxAttempts) {
                setTimeout(checkDependencies, 100);
                return;
            }
            
            // Si llegamos aquí, EmblaCarousel no se cargó
            console.warn('ACF Carousel: ⚠️ EmblaCarousel not found after waiting, attempting to load from CDN...');
            loadEmblaFromCDN(callback);
        }
        
        checkDependencies();
    }
    
    // Cargar Embla desde CDN como fallback
    function loadEmblaFromCDN(callback) {
        console.log('ACF Carousel: 📥 Loading Embla Carousel from CDN...');
        
        // Verificar si ya se está cargando
        if (window.emblaLoading) {
            console.log('ACF Carousel: Already loading Embla, waiting...');
            setTimeout(() => waitForDependencies(callback), 1000);
            return;
        }
        
        window.emblaLoading = true;
        
        const emblaScript = document.createElement('script');
        emblaScript.src = 'https://unpkg.com/embla-carousel@8.0.0/embla-carousel.umd.js';
        emblaScript.onload = function() {
            console.log('ACF Carousel: ✅ Embla Carousel loaded from CDN');
            
            // Cargar plugin de autoplay
            const autoplayScript = document.createElement('script');
            autoplayScript.src = 'https://unpkg.com/embla-carousel-autoplay@8.0.0/embla-carousel-autoplay.umd.js';
            autoplayScript.onload = function() {
                console.log('ACF Carousel: ✅ Embla Carousel Autoplay loaded from CDN');
                window.emblaLoading = false;
                callback();
            };
            autoplayScript.onerror = function() {
                console.warn('ACF Carousel: ⚠️ Failed to load Embla Autoplay, continuing without it');
                window.emblaLoading = false;
                callback();
            };
            document.head.appendChild(autoplayScript);
        };
        emblaScript.onerror = function() {
            console.error('ACF Carousel: ❌ Failed to load Embla Carousel from CDN');
            window.emblaLoading = false;
            
            // Intentar inicializar de todos modos por si hay una instancia local
            callback();
        };
        document.head.appendChild(emblaScript);
    }

    class ACFCarousel {
        constructor(element, options = {}) {
            this.element = element;
            this.emblaNode = element.querySelector('.embla, .hero-embla');
            this.options = {
                loop: true,
                dragFree: true,
                slidesToScroll: 1,
                containScroll: 'trimSnaps',
                ...options
            };
            
            this.embla = null;
            this.prevBtn = null;
            this.nextBtn = null;
            this.dotsNode = null;
            this.dotNodes = [];
            
            this.init();
        }

        init() {
            if (!this.emblaNode) {
                console.warn('ACF Carousel: Carousel node not found');
                return;
            }

            // Inicializar Embla Carousel
            this.initEmblaCarousel();
            
            // Setup navigation
            this.setupNavigation();
            
            // Setup dots
            this.setupDots();
            
            // Setup responsive behavior
            this.setupResponsive();
            
            // Setup accessibility
            this.setupAccessibility();
        }

        initEmblaCarousel() {
            const plugins = [];
            
            // Add autoplay plugin if enabled
            if (this.options.autoplay && typeof window.EmblaCarouselAutoplay !== 'undefined') {
                plugins.push(window.EmblaCarouselAutoplay({
                    delay: this.options.autoplayDelay || 4000,
                    stopOnInteraction: true,
                    stopOnMouseEnter: true
                }));
            }

            // Initialize carousel
            try {
                this.embla = window.EmblaCarousel(this.emblaNode, this.options, plugins);
                
                // Add event listeners
                this.embla.on('init', this.onInit.bind(this));
                this.embla.on('select', this.onSelect.bind(this));
                this.embla.on('resize', this.onResize.bind(this));
                this.embla.on('reInit', this.onReInit.bind(this));
                
                console.log('ACF Carousel: Successfully initialized');
            } catch (error) {
                console.error('ACF Carousel: Error initializing Embla:', error);
            }
        }

        setupNavigation() {
            // Para template hero
            this.prevBtn = this.element.querySelector('.embla__button--prev, .hero-arrow-prev');
            this.nextBtn = this.element.querySelector('.embla__button--next, .hero-arrow-next');

            if (this.prevBtn && this.nextBtn && this.embla) {
                this.prevBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.scrollPrev();
                });
                
                this.nextBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.scrollNext();
                });
                
                // Update button states
                this.embla.on('select', this.updateNavButtons.bind(this));
                this.embla.on('init', this.updateNavButtons.bind(this));
                
                console.log('ACF Carousel: Navigation buttons setup complete');
            }
        }

        setupDots() {
            this.dotsNode = this.element.querySelector('.embla__dots, .hero-dots-container');
            
            if (this.dotsNode && this.embla) {
                this.embla.on('init', this.generateDots.bind(this));
                this.embla.on('reInit', this.generateDots.bind(this));
                this.embla.on('select', this.updateDots.bind(this));
            }
        }

        setupResponsive() {
            // Apply responsive slides on init and resize
            if (this.embla) {
                this.embla.on('init', this.applyResponsiveSlides.bind(this));
                this.embla.on('reInit', this.applyResponsiveSlides.bind(this));
            }
            
            // Debounced resize handler
            let resizeTimeout;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    if (this.embla) {
                        this.embla.reInit();
                    }
                }, 250);
            });
        }

        setupAccessibility() {
            // Add ARIA labels
            const slides = this.emblaNode.querySelectorAll('.embla__slide, .hero-embla__slide');
            slides.forEach((slide, index) => {
                slide.setAttribute('aria-label', `Slide ${index + 1} of ${slides.length}`);
                slide.setAttribute('role', 'group');
            });

            // Add keyboard navigation
            this.emblaNode.addEventListener('keydown', this.handleKeydown.bind(this));
            this.emblaNode.setAttribute('tabindex', '0');
            this.emblaNode.setAttribute('role', 'region');
            this.emblaNode.setAttribute('aria-label', 'Carousel');
        }

        onInit() {
            this.applyResponsiveSlides();
            this.updateSlideStates();
        }

        onSelect() {
            this.updateSlideStates();
            this.updateSlideCounter();
            this.updateBackgroundImage();
        }

        onResize() {
            this.applyResponsiveSlides();
        }

        onReInit() {
            this.applyResponsiveSlides();
            this.updateSlideStates();
        }

        scrollPrev() {
            if (this.embla) {
                this.embla.scrollPrev();
                console.log('ACF Carousel: Scrolled to previous slide');
            }
        }

        scrollNext() {
            if (this.embla) {
                this.embla.scrollNext();
                console.log('ACF Carousel: Scrolled to next slide');
            }
        }

        scrollTo(index) {
            if (this.embla) {
                this.embla.scrollTo(index);
            }
        }

        updateNavButtons() {
            if (!this.prevBtn || !this.nextBtn || !this.embla) return;

            if (this.embla.canScrollPrev()) {
                this.prevBtn.removeAttribute('disabled');
                this.prevBtn.setAttribute('aria-disabled', 'false');
            } else {
                this.prevBtn.setAttribute('disabled', 'disabled');
                this.prevBtn.setAttribute('aria-disabled', 'true');
            }

            if (this.embla.canScrollNext()) {
                this.nextBtn.removeAttribute('disabled');
                this.nextBtn.setAttribute('aria-disabled', 'false');
            } else {
                this.nextBtn.setAttribute('disabled', 'disabled');
                this.nextBtn.setAttribute('aria-disabled', 'true');
            }
        }

        generateDots() {
            if (!this.dotsNode || !this.embla) return;

            const scrollSnaps = this.embla.scrollSnapList();
            this.dotsNode.innerHTML = scrollSnaps
                .map((_, index) => `
                    <button class="embla__dot hero-dot" type="button" aria-label="Go to slide ${index + 1}">
                        <span class="sr-only">Slide ${index + 1}</span>
                    </button>
                `)
                .join('');

            this.dotNodes = Array.from(this.dotsNode.querySelectorAll('.embla__dot, .hero-dot'));
            this.dotNodes.forEach((dotNode, index) => {
                dotNode.addEventListener('click', () => this.scrollTo(index));
            });
        }

        updateDots() {
            if (!this.dotNodes.length || !this.embla) return;

            const selectedIndex = this.embla.selectedScrollSnap();
            
            this.dotNodes.forEach((dot, index) => {
                if (index === selectedIndex) {
                    dot.classList.add('embla__dot--selected', 'hero-dot-active');
                    dot.setAttribute('aria-pressed', 'true');
                } else {
                    dot.classList.remove('embla__dot--selected', 'hero-dot-active');
                    dot.setAttribute('aria-pressed', 'false');
                }
            });
        }

        updateSlideCounter() {
            const currentSlideEl = this.element.querySelector('.current-slide');
            if (currentSlideEl && this.embla) {
                const current = this.embla.selectedScrollSnap() + 1;
                currentSlideEl.textContent = current;
            }
        }

        updateBackgroundImage() {
            // Solo para template hero
            const currentIndex = this.embla ? this.embla.selectedScrollSnap() : 0;
            const slides = this.emblaNode.querySelectorAll('.hero-card img');
            const bgImage = this.element.querySelector('.hero-bg-image');
            
            if (slides[currentIndex] && bgImage) {
                bgImage.style.opacity = '0';
                setTimeout(() => {
                    bgImage.src = slides[currentIndex].src;
                    bgImage.style.opacity = '1';
                }, 300);
            }
        }

        applyResponsiveSlides() {
            const slides = this.emblaNode.querySelectorAll('.embla__slide, .hero-embla__slide');
            const slidesToShow = this.options.slidesToShow || 3;
            
            let currentSlidesToShow = slidesToShow;
            const windowWidth = window.innerWidth;
            
            // Responsive breakpoints
            if (windowWidth <= 480) {
                currentSlidesToShow = 1;
            } else if (windowWidth <= 768) {
                currentSlidesToShow = Math.min(2, slidesToShow);
            } else if (windowWidth <= 1024) {
                currentSlidesToShow = Math.min(slidesToShow - 1, slidesToShow);
            }
            
            // Solo aplicar para carrusel default, no para hero
            if (!this.element.classList.contains('acf-hero-carousel-wrapper')) {
                const slideWidth = (100 / currentSlidesToShow) + '%';
                slides.forEach(slide => {
                    slide.style.flex = `0 0 ${slideWidth}`;
                    slide.style.minWidth = '0';
                });
            }
        }

        updateSlideStates() {
            if (!this.embla) return;
            
            const slides = this.emblaNode.querySelectorAll('.embla__slide, .hero-embla__slide');
            const inViewSlides = this.embla.slidesInView();
            
            slides.forEach((slide, index) => {
                if (inViewSlides.includes(index)) {
                    slide.classList.add('is-snapped');
                    slide.setAttribute('aria-hidden', 'false');
                } else {
                    slide.classList.remove('is-snapped');
                    slide.setAttribute('aria-hidden', 'true');
                }
            });
        }

        handleKeydown(event) {
            switch (event.key) {
                case 'ArrowLeft':
                    event.preventDefault();
                    this.scrollPrev();
                    break;
                case 'ArrowRight':
                    event.preventDefault();
                    this.scrollNext();
                    break;
                case 'Home':
                    event.preventDefault();
                    this.scrollTo(0);
                    break;
                case 'End':
                    event.preventDefault();
                    if (this.embla) {
                        this.scrollTo(this.embla.scrollSnapList().length - 1);
                    }
                    break;
            }
        }

        destroy() {
            if (this.embla) {
                this.embla.destroy();
            }
        }

        reInit(newOptions = {}) {
            this.options = { ...this.options, ...newOptions };
            if (this.embla) {
                this.embla.reInit(this.options);
            }
        }
    }

    // Auto-initialize carousels
    function initCarousels() {
        console.log('ACF Carousel: Starting initialization...');
        
        const carouselElements = document.querySelectorAll('.acf-carousel-wrapper, .acf-hero-carousel-wrapper');
        console.log(`ACF Carousel: Found ${carouselElements.length} carousel(s)`);
        
        carouselElements.forEach((element, index) => {
            // Skip if already initialized
            if (element.classList.contains('acf-carousel-initialized')) {
                return;
            }
            
            console.log(`ACF Carousel: Initializing carousel ${index + 1}`);
            
            // Get settings from data attributes or default values
            const settings = {
                loop: element.dataset.loop !== 'false',
                dragFree: element.dataset.draggable !== 'false',
                slidesToScroll: parseInt(element.dataset.slidesToScroll) || 1,
                slidesToShow: parseInt(element.dataset.slidesToShow) || 3,
                autoplay: element.dataset.autoplay === 'true',
                autoplayDelay: parseInt(element.dataset.autoplayDelay) || 4000,
                containScroll: 'trimSnaps'
            };
            
            // Initialize carousel
            const carousel = new ACFCarousel(element, settings);
            
            // Store instance on element
            element.acfCarousel = carousel;
            element.classList.add('acf-carousel-initialized');
            
            console.log(`ACF Carousel: Carousel ${index + 1} initialized successfully`);
        });
    }

    // Initialize when dependencies are ready
    function initializeCarousels() {
        console.log('ACF Carousel: Starting initialization process...');
        waitForDependencies(() => {
            initCarousels();
        });
    }

    // Múltiples puntos de inicialización para máxima compatibilidad
    
    // 1. jQuery document ready
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function() {
            console.log('ACF Carousel: jQuery DOM ready, initializing...');
            initializeCarousels();
        });
    }

    // 2. Vanilla JS DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('ACF Carousel: Native DOM ready, initializing...');
            initializeCarousels();
        });
    } else {
        console.log('ACF Carousel: DOM already ready, initializing immediately...');
        initializeCarousels();
    }

    // 3. Window load como fallback
    window.addEventListener('load', function() {
        console.log('ACF Carousel: Window loaded, checking for uninitialized carousels...');
        
        const uninitializedCarousels = document.querySelectorAll('.acf-carousel-wrapper:not(.acf-carousel-initialized), .acf-hero-carousel-wrapper:not(.acf-carousel-initialized)');
        
        if (uninitializedCarousels.length > 0) {
            console.log(`ACF Carousel: Found ${uninitializedCarousels.length} uninitialized carousels, initializing...`);
            initializeCarousels();
        }
    });

    // 4. Inicialización tardía para casos extremos
    setTimeout(function() {
        const uninitializedCarousels = document.querySelectorAll('.acf-carousel-wrapper:not(.acf-carousel-initialized), .acf-hero-carousel-wrapper:not(.acf-carousel-initialized)');
        
        if (uninitializedCarousels.length > 0) {
            console.log('ACF Carousel: Late initialization check - found uninitialized carousels');
            initializeCarousels();
        }
    }, 3000);

    // Elementor frontend hooks - con verificación de disponibilidad
    function setupElementorHooks() {
        if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
            console.log('ACF Carousel: Setting up Elementor hooks');
            
            elementorFrontend.hooks.addAction('frontend/element_ready/acf-carousel.default', function($scope) {
                console.log('ACF Carousel: Elementor element ready');
                const carouselElement = $scope.find('.acf-carousel-wrapper, .acf-hero-carousel-wrapper')[0];
                if (carouselElement && !carouselElement.classList.contains('acf-carousel-initialized')) {
                    waitForDependencies(() => {
                        const settings = {
                            loop: true,
                            dragFree: true,
                            slidesToScroll: 1,
                            containScroll: 'trimSnaps'
                        };
                        
                        const carousel = new ACFCarousel(carouselElement, settings);
                        carouselElement.acfCarousel = carousel;
                        carouselElement.classList.add('acf-carousel-initialized');
                    });
                }
            });
        } else {
            console.log('ACF Carousel: elementorFrontend not available, skipping Elementor hooks');
        }
    }

    // Intentar configurar hooks de Elementor cuando esté disponible
    if (typeof elementorFrontend !== 'undefined') {
        setupElementorHooks();
    } else {
        // Esperar a que Elementor esté disponible
        document.addEventListener('DOMContentLoaded', function() {
            // Intentar varias veces con intervalos
            let attempts = 0;
            const maxAttempts = 10;
            
            const checkElementor = setInterval(function() {
                attempts++;
                
                if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
                    clearInterval(checkElementor);
                    setupElementorHooks();
                } else if (attempts >= maxAttempts) {
                    clearInterval(checkElementor);
                    console.log('ACF Carousel: Elementor frontend not found after waiting');
                }
            }, 500);
        });
    }

    // Expose ACFCarousel class globally
    window.ACFCarousel = ACFCarousel;

    // Additional utilities
    window.ACFCarouselUtils = {
        // Reinitialize all carousels
        reinitAll: function() {
            document.querySelectorAll('.acf-carousel-wrapper.acf-carousel-initialized, .acf-hero-carousel-wrapper.acf-carousel-initialized').forEach(element => {
                if (element.acfCarousel) {
                    element.acfCarousel.reInit();
                }
            });
        },

        // Destroy all carousels
        destroyAll: function() {
            document.querySelectorAll('.acf-carousel-wrapper.acf-carousel-initialized, .acf-hero-carousel-wrapper.acf-carousel-initialized').forEach(element => {
                if (element.acfCarousel) {
                    element.acfCarousel.destroy();
                    element.classList.remove('acf-carousel-initialized');
                    delete element.acfCarousel;
                }
            });
        },

        // Get carousel instance
        getInstance: function(element) {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }
            return element ? element.acfCarousel : null;
        },

        // Force reinitialize
        forceInit: function() {
            console.log('ACF Carousel: Force reinitializing...');
            document.querySelectorAll('.acf-carousel-wrapper, .acf-hero-carousel-wrapper').forEach(element => {
                element.classList.remove('acf-carousel-initialized');
                if (element.acfCarousel) {
                    element.acfCarousel.destroy();
                    delete element.acfCarousel;
                }
            });
            
            setTimeout(() => {
                initializeCarousels();
            }, 100);
        }
    };

})(jQuery);