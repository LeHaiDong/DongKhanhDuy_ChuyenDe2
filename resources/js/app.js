import './bootstrap';

// ===== MODERN LENS STORE JAVASCRIPT ===== //
document.addEventListener('DOMContentLoaded', function() {
    // Advanced Intersection Observer for smooth animations
    const observerOptions = {
        threshold: [0.1, 0.3, 0.5],
        rootMargin: '0px 0px -100px 0px'
    };

    const animationObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                const animationType = element.dataset.animation || 'fade-in-up';
                
                // Add staggered animation delay for multiple elements
                const siblings = Array.from(element.parentElement?.children || []);
                const index = siblings.indexOf(element);
                const delay = index * 150; // 150ms stagger
                
                setTimeout(() => {
                    element.classList.add(`animate-${animationType}`);
                    element.style.opacity = '1';
                    element.style.transform = 'none';
                }, delay);
                
                animationObserver.unobserve(element);
            }
        });
    }, observerOptions);

    // Initialize animations for elements
    document.querySelectorAll('[data-animation]').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        animationObserver.observe(el);
    });

    // Auto-observe common animated elements
    document.querySelectorAll('.card-modern, .card-product, .animate-on-scroll').forEach(el => {
        if (!el.dataset.animation) {
            el.dataset.animation = 'fade-in-scale';
        }
        el.style.opacity = '0';
        el.style.transform = 'scale(0.95)';
        animationObserver.observe(el);
    });

    // Enhanced loading states with ripple effect
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                const originalText = submitBtn.innerHTML;
                
                // Add loading spinner with smooth transition
                submitBtn.innerHTML = '<div class="loading-spinner"></div><span style="margin-left: 8px;">Đang xử lý...</span>';
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
                
                // Re-enable after 5 seconds as fallback
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                }, 5000);
            }
        });
    });

    // Add hover effects to cards
    document.querySelectorAll('.modern-card, .product-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Enhanced mobile menu
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            
            // Animate menu items
            if (!mobileMenu.classList.contains('hidden')) {
                const menuItems = mobileMenu.querySelectorAll('a');
                menuItems.forEach((item, index) => {
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(-10px)';
                    
                    setTimeout(() => {
                        item.style.transition = 'all 0.3s ease';
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, index * 100);
                });
            }
        });
    }

    // Navbar scroll effect with throttling
    let ticking = false;
    function updateNavbar() {
        const navbar = document.getElementById('navbar');
        if (navbar) {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        }
        ticking = false;
    }

    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(updateNavbar);
            ticking = true;
        }
    });

    // Add ripple effect to buttons
    document.querySelectorAll('.btn-modern').forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert-modern').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Enhanced search input with animation
    const searchInputs = document.querySelectorAll('input[type="search"], .input-modern');
    searchInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
            this.style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
            this.style.transform = 'translateY(0)';
        });

        // Add typing animation
        input.addEventListener('input', function() {
            this.style.transform = 'scale(1.02)';
            setTimeout(() => {
                this.style.transform = 'translateY(-2px)';
            }, 150);
        });
    });

    // Parallax effect for hero sections
    const heroElements = document.querySelectorAll('.hero-section, .hero-grid');
    if (heroElements.length > 0) {
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            
            heroElements.forEach(hero => {
                hero.style.transform = `translateY(${rate}px)`;
            });
        });
    }

    // Modern card hover effects with 3D transform
    document.querySelectorAll('.card-modern, .card-product').forEach(card => {
        card.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 10;
            const rotateY = (centerX - x) / 10;
            
            this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-8px)`;
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
        });
    });

    // Advanced ripple effect for buttons
    document.querySelectorAll('.btn-modern, .hover-ripple').forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.6);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // Smooth page transitions
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a[href^="/"]');
        if (link && !link.target && !e.metaKey && !e.ctrlKey) {
            e.preventDefault();
            
            // Add exit animation
            document.body.style.opacity = '0.7';
            document.body.style.transform = 'scale(0.98)';
            
            setTimeout(() => {
                window.location.href = link.href;
            }, 150);
        }
    });

    // Progressive image loading with fade effect
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.style.opacity = '0';
                img.style.transition = 'opacity 0.5s ease';
                
                img.onload = () => {
                    img.style.opacity = '1';
                    img.classList.add('loaded');
                };
                
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));

    // Dynamic theme color based on scroll position
    let lastScrollTop = 0;
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollPercent = scrollTop / (document.documentElement.scrollHeight - window.innerHeight);
        
        // Change navbar transparency based on scroll
        const navbar = document.querySelector('.nav-modern, .navbar-modern');
        if (navbar) {
            const opacity = Math.min(0.95, 0.8 + (scrollPercent * 0.15));
            navbar.style.background = `rgba(255, 255, 255, ${opacity})`;
        }
        
        lastScrollTop = scrollTop;
    });

    // Performance optimization: Debounce resize events
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            // Refresh animations on resize
            document.querySelectorAll('[data-animation]').forEach(el => {
                if (el.classList.contains('animate-fade-in-up')) {
                    el.style.opacity = '1';
                    el.style.transform = 'none';
                }
            });
        }, 250);
    });

    // Add loading class to body during page transitions
    window.addEventListener('beforeunload', function() {
        document.body.classList.add('page-loading');
    });

    // Initialize modern tooltips
    document.querySelectorAll('[data-tooltip]').forEach(element => {
        element.classList.add('tooltip');
    });

    console.log('✨ Modern Lens Store JavaScript initialized with advanced animations and interactions!');
});
