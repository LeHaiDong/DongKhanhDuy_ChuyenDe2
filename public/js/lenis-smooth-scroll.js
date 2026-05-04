/**
 * Lenis Smooth Scrolling Configuration
 * Mượt mà và chuyên nghiệp cho tất cả các trang
 */

// Kiểm tra xem Lenis có sẵn không
if (typeof Lenis !== 'undefined') {
    // Khởi tạo Lenis với cấu hình tối ưu
    const lenis = new Lenis({
        duration: 1.2,
        easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)), // easeOutExpo
        direction: 'vertical',
        gestureDirection: 'vertical',
        smooth: true,
        mouseMultiplier: 1,
        smoothTouch: false, // Tắt trên mobile để tránh xung đột
        touchMultiplier: 2,
        infinite: false,
        autoResize: true,
        syncTouch: false,
        syncTouchLerp: 0.075,
        touchInertiaMultiplier: 35,
        orientation: 'vertical'
    });

    // Function để khởi chạy animation frame
    function raf(time) {
        lenis.raf(time);
        requestAnimationFrame(raf);
    }

    // Bắt đầu animation loop
    requestAnimationFrame(raf);

    // Log để debug
    console.log('🚀 Lenis Smooth Scroll initialized');

    // Event listeners
    lenis.on('scroll', (e) => {
        // Custom scroll events có thể thêm ở đây
        // console.log('Scroll position:', e.scroll);
    });

    // Xử lý window resize
    window.addEventListener('resize', () => {
        lenis.resize();
    });

    // Stop/Start scroll khi cần
    window.addEventListener('beforeunload', () => {
        lenis.destroy();
    });

    // Tích hợp với các anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                lenis.scrollTo(target, {
                    offset: -80, // Để tránh navbar che khuất
                    duration: 1.5
                });
            }
        });
    });

    // Tích hợp với navbar scroll effect
    let lastScrollTop = 0;
    const navbar = document.querySelector('.nike-nav');
    
    if (navbar) {
        lenis.on('scroll', (e) => {
            const scrollTop = e.scroll;
            
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                navbar.style.transform = 'translateY(-100%)';
            } else {
                navbar.style.transform = 'translateY(0)';
            }
            
            lastScrollTop = scrollTop;
        });
    }

    // Parallax effects cho slider nếu có
    const heroSlider = document.querySelector('.hero-slider-section');
    if (heroSlider) {
        lenis.on('scroll', (e) => {
            const scrolled = e.scroll;
            const rate = scrolled * -0.5;
            
            // Subtle parallax effect
            heroSlider.style.transform = `translateY(${rate}px)`;
        });
    }

    // Fade in animation khi scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Áp dụng fade-in cho các elements
    document.querySelectorAll('.fade-in-scroll').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

    // Export lenis instance để sử dụng global
    window.lenis = lenis;

} else {
    console.warn('⚠️ Lenis library not found. Smooth scrolling disabled.');
}

// CSS cho các hiệu ứng smooth
const style = document.createElement('style');
style.textContent = `
    /* Lenis specific optimizations */
    html.lenis {
        height: auto;
    }

    .lenis.lenis-smooth {
        scroll-behavior: auto !important;
    }

    .lenis.lenis-smooth [data-lenis-prevent] {
        overscroll-behavior: contain;
    }

    /* Smooth transitions cho tất cả scroll-related elements */
    .nike-nav {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Parallax container */
    .parallax-container {
        will-change: transform;
    }

    /* Fade in scroll elements */
    .fade-in-scroll {
        will-change: opacity, transform;
    }

    /* Prevent scroll issues on mobile */
    @media (max-width: 768px) {
        body {
            overflow-x: hidden;
        }
    }
`;

document.head.appendChild(style);


