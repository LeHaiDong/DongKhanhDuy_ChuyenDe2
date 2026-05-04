// Hero Slider JavaScript
class HeroSlider {
    constructor() {
        this.currentSlide = 0;
        this.slides = document.querySelectorAll('.hero-slide');
        this.dots = document.querySelectorAll('.dot');
        this.prevBtn = document.querySelector('.prev-btn');
        this.nextBtn = document.querySelector('.next-btn');
        this.curvePath = document.querySelector('.curve-path');
        this.autoPlayInterval = null;
        this.autoPlayDelay = 5000; // 5 seconds
        
        this.init();
    }
    
    init() {
        if (this.slides.length === 0) return;
        
        // Add event listeners
        this.addEventListeners();
        
        // Start autoplay
        this.startAutoPlay();
        
        // Initialize first slide
        this.updateSlider();
    }
    
    addEventListeners() {
        // Dot navigation
        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                this.goToSlide(index);
                this.resetAutoPlay();
            });
        });
        
        // Navigation buttons
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => {
                this.prevSlide();
                this.resetAutoPlay();
            });
        }
        
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => {
                this.nextSlide();
                this.resetAutoPlay();
            });
        }
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                this.prevSlide();
                this.resetAutoPlay();
            } else if (e.key === 'ArrowRight') {
                this.nextSlide();
                this.resetAutoPlay();
            }
        });
        
        // Touch/swipe support
        this.addTouchSupport();
        
        // Pause autoplay on hover
        const sliderContainer = document.querySelector('.hero-slider-section');
        if (sliderContainer) {
            sliderContainer.addEventListener('mouseenter', () => {
                this.stopAutoPlay();
            });
            
            sliderContainer.addEventListener('mouseleave', () => {
                this.startAutoPlay();
            });
        }
    }
    
    addTouchSupport() {
        let startX = 0;
        let endX = 0;
        
        const sliderContainer = document.querySelector('.hero-slider-container');
        if (!sliderContainer) return;
        
        sliderContainer.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
        });
        
        sliderContainer.addEventListener('touchend', (e) => {
            endX = e.changedTouches[0].clientX;
            this.handleSwipe(startX, endX);
        });
    }
    
    handleSwipe(startX, endX) {
        const threshold = 50; // Minimum swipe distance
        const diff = startX - endX;
        
        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                // Swiped left - next slide
                this.nextSlide();
            } else {
                // Swiped right - previous slide
                this.prevSlide();
            }
            this.resetAutoPlay();
        }
    }
    
    goToSlide(index) {
        if (index < 0 || index >= this.slides.length) return;
        
        this.currentSlide = index;
        this.updateSlider();
    }
    
    nextSlide() {
        this.currentSlide = (this.currentSlide + 1) % this.slides.length;
        this.updateSlider();
    }
    
    prevSlide() {
        this.currentSlide = this.currentSlide === 0 ? this.slides.length - 1 : this.currentSlide - 1;
        this.updateSlider();
    }
    
    updateSlider() {
        // Update slides
        this.slides.forEach((slide, index) => {
            slide.classList.toggle('active', index === this.currentSlide);
        });
        
        // Update dots
        this.dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === this.currentSlide);
        });
        
        // Update background gradient
        this.updateBackground();
        
        // Trigger animation
        this.animateSlideElements();
    }
    
    updateBackground() {
        const currentSlideElement = this.slides[this.currentSlide];
        if (!currentSlideElement || !this.curvePath) return;
        
        const bgGradient = currentSlideElement.dataset.bg;
        if (bgGradient) {
            this.curvePath.setAttribute('fill', `url(#${bgGradient})`);
        }
    }
    
    animateSlideElements() {
        const currentSlideElement = this.slides[this.currentSlide];
        if (!currentSlideElement) return;
        
        // Reset animations
        const animatedElements = currentSlideElement.querySelectorAll('.slide-badge, .slide-title, .slide-description, .slide-buttons');
        animatedElements.forEach(el => {
            el.style.animation = 'none';
            el.offsetHeight; // Trigger reflow
            el.style.animation = null;
        });
        
        // Animate floating product
        const floatingProduct = currentSlideElement.querySelector('.floating-product');
        if (floatingProduct) {
            floatingProduct.style.animation = 'none';
            floatingProduct.offsetHeight; // Trigger reflow
            floatingProduct.style.animation = 'float 6s ease-in-out infinite';
        }
    }
    
    startAutoPlay() {
        this.stopAutoPlay(); // Clear any existing interval
        this.autoPlayInterval = setInterval(() => {
            this.nextSlide();
        }, this.autoPlayDelay);
    }
    
    stopAutoPlay() {
        if (this.autoPlayInterval) {
            clearInterval(this.autoPlayInterval);
            this.autoPlayInterval = null;
        }
    }
    
    resetAutoPlay() {
        this.stopAutoPlay();
        this.startAutoPlay();
    }
    
    // Public methods for external control
    pause() {
        this.stopAutoPlay();
    }
    
    play() {
        this.startAutoPlay();
    }
    
    destroy() {
        this.stopAutoPlay();
        // Remove event listeners if needed
    }
}

// Initialize slider when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const heroSlider = new HeroSlider();
    
    // Make it globally accessible
    window.heroSlider = heroSlider;
});

// Additional utility functions
function createRippleEffect(element, event) {
    const ripple = document.createElement('span');
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;
    
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.classList.add('ripple');
    
    element.appendChild(ripple);
    
    setTimeout(() => {
        ripple.remove();
    }, 600);
}

// Add ripple effect to buttons
document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.btn-primary, .btn-secondary, .nav-btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            createRippleEffect(this, e);
        });
    });
});

// Smooth scroll for anchor links
document.addEventListener('DOMContentLoaded', () => {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Add CSS for ripple effect
const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: scale(0);
        animation: ripple-animation 0.6s linear;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
    
    .btn-primary, .btn-secondary, .nav-btn {
        position: relative;
        overflow: hidden;
    }
`;
document.head.appendChild(rippleStyle);
