/**
 * ACF Carousel JavaScript
 * assets/js/acf-carousel.js
 */

(function($) {
    'use strict';

    class ACFCarousel {
        constructor(element, options = {}) {
            this.element = element;
            this.emblaNode = element.querySelector('.embla');
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
            if (!this.emblaNode || typeof EmblaCarousel === 'undefined') {
                console.warn('ACF Carousel: Embla Carousel library not found or carousel node missing');
                return;
            }

            // Initialize Embla Carousel
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
            if (this.options.autoplay && typeof EmblaCarouselAutoplay !== 'undefined') {
                plugins.push(EmblaCarouselAutoplay({
                    delay: this.options.autoplayDelay || 4000,
                    stopOnInteraction: true,
                    stopOnMouseEnter: true
                }));
            }

            // Initialize carousel
            this.embla = EmblaCarousel(this.emblaNode, this.options, plugins);
            
            // Add event listeners
            this.embla.on('init', this.onInit.bind(this));
            this.embla.on('select', this.onSelect.bind(this));
            this.embla.on('resize', this.onResize.bind(this));
            this.embla.on('reInit', this.onReInit.bind(this));
        }

        setupNavigation() {
            this.prevBtn = this.element.querySelector('.embla__button--prev');
            this.nextBtn = this.element.querySelector('.embla__button--next');

            if (this.prevBtn && this.nextBtn) {
                this.prevBtn.addEventListener('click', this.scrollPrev.bind(this));
                this.nextBtn.addEventListener('click', this.scrollNext.bind(this));
                
                // Update button states
                this.embla.on('select', this.updateNavButtons.bind(this));
                this.embla.on('init', this.updateNavButtons.bind(this));
            }
        }

        setupDots() {
            this.dotsNode = this.element.querySelector('.embla__dots');
            
            if (this.dotsNode) {
                this.embla.on('init', this.generateDots.bind(this));
                this.embla.on('reInit', this.generateDots.bind(this));
                this.embla.on('select', this.updateDots.bind(this));
            }
        }

        setupResponsive() {
            // Apply responsive slides on init and resize
            this.embla.on('init', this.applyResponsiveSlides.bind(this));
            this.embla.on('reInit', this.applyResponsiveSlides.bind(this));
            
            // Debounced resize handler
            let resizeTimeout;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    this.embla.reInit();
                }, 250);
            });
        }

        setupAccessibility() {
            // Add ARIA labels
            const slides = this.emblaNode.querySelectorAll('.embla__slide');
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
            }
        }

        scrollNext() {
            if (this.embla) {
                this.embla.scrollNext();
            }
        }

        scrollTo(index) {
            if (this.embla) {
                this.embla.scrollTo(index);
            }
        }

        updateNavButtons() {
            if (!this.prevBtn || !this.nextBtn) return;

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
            if (!this.dotsNode) return;

            const scrollSnaps = this.embla.scrollSnapList();
            this.dotsNode.innerHTML = scrollSnaps
                .map((_, index) => `
                    <button class="embla__dot" type="button" aria-label="Go to slide ${index + 1}">
                        <span class="sr-only">Slide ${index + 1}</span>
                    </button>
                `)
                .join('');

            this.dotNodes = Array.from(this.dotsNode.querySelectorAll('.embla__dot'));
            this.dotNodes.forEach((dotNode, index) => {
                dotNode.addEventListener('click', () => this.scrollTo(index));
            });
        }

        updateDots() {
            if (!this.dotNodes.length) return;

            const selectedIndex = this.embla.selectedScrollSnap();
            
            this.dotNodes.forEach((dot, index) => {
                if (index === selectedIndex) {
                    dot.classList.add('embla__dot--selected');
                    dot.setAttribute('aria-pressed', 'true');
                } else {
                    dot.classList.remove('embla__dot--selected');
                    dot.setAttribute('aria-pressed', 'false');
                }
            });
        }

        applyResponsiveSlides() {
            const slides = this.emblaNode.querySelectorAll('.embla__slide');
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
            
            const slideWidth = (100 / currentSlidesToShow) + '%';
            slides.forEach(slide => {
                slide.style.flex = `0 0 ${slideWidth}`;
                slide.style.minWidth = '0';
            });
        }

        updateSlideStates() {
            const slides = this.emblaNode.querySelectorAll('.embla__slide');
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
                    this.scrollTo(this.embla.scrollSnapList().length - 1);
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
        const carouselElements = document.querySelectorAll('.acf-carousel-wrapper');
        
        carouselElements.forEach(element => {
            // Skip if already initialized
            if (element.classList.contains('acf-carousel-initialized')) {
                return;
            }
            
            // Get settings from data attributes or widget settings
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
        });
    }

    // jQuery document ready
    $(document).ready(function() {
        initCarousels();
    });

    // Elementor frontend hooks
    if (typeof elementorFrontend !== 'undefined') {
        elementorFrontend.hooks.addAction('frontend/element_ready/acf-carousel.default', function($scope) {
            const carouselElement = $scope.find('.acf-carousel-wrapper')[0];
            if (carouselElement && !carouselElement.classList.contains('acf-carousel-initialized')) {
                initCarousels();
            }
        });
    }

    // Expose ACFCarousel class globally
    window.ACFCarousel = ACFCarousel;

    // Additional utilities
    window.ACFCarouselUtils = {
        // Reinitialize all carousels
        reinitAll: function() {
            document.querySelectorAll('.acf-carousel-wrapper.acf-carousel-initialized').forEach(element => {
                if (element.acfCarousel) {
                    element.acfCarousel.reInit();
                }
            });
        },

        // Destroy all carousels
        destroyAll: function() {
            document.querySelectorAll('.acf-carousel-wrapper.acf-carousel-initialized').forEach(element => {
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
        }
    };

})(jQuery);