import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, EffectFade } from 'swiper/modules';
import AOS from 'aos';
import GLightbox from 'glightbox';
// CSS pustaka ikut di-bundle (versi sama dengan JS-nya) — sebelumnya dimuat
// dari CDN dengan versi berbeda (Swiper CSS v11 vs JS v12) dan halaman rusak
// bila CDN tidak bisa diakses.
import 'swiper/css/bundle';
import 'aos/dist/aos.css';
import 'glightbox/dist/css/glightbox.min.css';

// Alpine.js plugins
Alpine.plugin(collapse);
Alpine.plugin(focus);

window.Alpine = Alpine;
Alpine.start();

// ===== LOADING SCREEN =====
document.addEventListener('DOMContentLoaded', () => {
    const loadingScreen = document.getElementById('loading-screen');
    if (loadingScreen) {
        // Tanpa jeda buatan: sebelumnya layar loading ditahan 1,8 detik di
        // SETIAP halaman sehingga situs terasa lambat.
        loadingScreen.style.transition = 'opacity 0.3s ease';
        loadingScreen.style.opacity = '0';
        loadingScreen.style.pointerEvents = 'none';
        setTimeout(() => {
            loadingScreen.style.display = 'none';
        }, 300);
    }
});

// ===== HERO SLIDER =====
window.initHeroSlider = function() {
    const el = document.querySelector('.hero-swiper');
    if (!el) return;
    const slideCount = el.querySelectorAll('.swiper-slide').length;
    return new Swiper('.hero-swiper', {
        modules: [Navigation, Pagination, Autoplay, EffectFade],
        effect: 'fade',
        loop: slideCount > 1,
        autoplay: slideCount > 1 ? { delay: 5000, disableOnInteraction: false } : false,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
};

// ===== BANNER SLIDER =====
window.initBannerSlider = function() {
    const el = document.querySelector('.banner-swiper');
    if (!el) return;
    const slideCount = el.querySelectorAll('.swiper-slide').length;
    return new Swiper('.banner-swiper', {
        modules: [Navigation, Pagination, Autoplay],
        loop: slideCount > 1,
        autoplay: slideCount > 1 ? { delay: 4000 } : false,
        pagination: {
            el: '.banner-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.banner-next',
            prevEl: '.banner-prev',
        },
    });
};

// ===== SARPRAS CAROUSEL =====
window.initSarprasSlider = function() {
    const el = document.querySelector('.sarpras-swiper');
    if (!el) return;
    const slideCount = el.querySelectorAll('.swiper-slide').length;
    return new Swiper('.sarpras-swiper', {
        modules: [Navigation, Pagination, Autoplay],
        slidesPerView: 1,
        spaceBetween: 20,
        loop: slideCount >= 4,
        autoplay: { delay: 3500 },
        breakpoints: {
            640: { slidesPerView: 2 },
            1024: { slidesPerView: 3 },
        },
        pagination: {
            el: '.sarpras-pagination',
            clickable: true,
        },
    });
};

// ===== TESTIMONI CAROUSEL =====
window.initTestimoniSlider = function() {
    const el = document.querySelector('.testimoni-swiper');
    if (!el) return;
    const slideCount = el.querySelectorAll('.swiper-slide').length;
    return new Swiper('.testimoni-swiper', {
        modules: [Navigation, Pagination, Autoplay],
        slidesPerView: 1,
        spaceBetween: 24,
        loop: slideCount >= 4,
        autoplay: { delay: 4500 },
        breakpoints: {
            768: { slidesPerView: 2 },
            1280: { slidesPerView: 3 },
        },
        pagination: {
            el: '.testimoni-pagination',
            clickable: true,
        },
    });
};

// ===== LIGHTBOX =====
window.initLightbox = function() {
    return GLightbox({
        touchNavigation: true,
        loop: true,
        autoplayVideos: true,
    });
};

// ===== BACK TO TOP =====
const backToTop = document.getElementById('back-to-top');
if (backToTop) {
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    });

    backToTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

// ===== DARK MODE =====
window.themeManager = {
    init() {
        const saved = localStorage.getItem('theme') || 'system';
        this.apply(saved);
    },
    apply(theme) {
        const root = document.documentElement;
        if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            root.classList.add('dark');
        } else {
            root.classList.remove('dark');
        }
        localStorage.setItem('theme', theme);
    },
    toggle() {
        const current = localStorage.getItem('theme') || 'system';
        const themes = ['light', 'dark', 'system'];
        const next = themes[(themes.indexOf(current) + 1) % themes.length];
        this.apply(next);
        return next;
    }
};

window.themeManager.init();

// ===== ACCESSIBILITY =====
window.accessibilityManager = {
    fontSize: 16,
    highContrast: false,
    dyslexiaFont: false,
    lineHeight: 1.5,

    init() {
        const saved = JSON.parse(localStorage.getItem('accessibility') || '{}');
        if (saved.fontSize) this.setFontSize(saved.fontSize);
        if (saved.highContrast) this.toggleHighContrast();
        if (saved.dyslexiaFont) this.toggleDyslexiaFont();
    },

    setFontSize(size) {
        this.fontSize = size;
        document.documentElement.style.fontSize = size + 'px';
        this.save();
    },

    increaseFontSize() {
        if (this.fontSize < 22) this.setFontSize(this.fontSize + 2);
    },

    decreaseFontSize() {
        if (this.fontSize > 12) this.setFontSize(this.fontSize - 2);
    },

    resetFontSize() {
        this.setFontSize(16);
    },

    toggleHighContrast() {
        this.highContrast = !this.highContrast;
        document.body.classList.toggle('high-contrast', this.highContrast);
        this.save();
    },

    toggleDyslexiaFont() {
        this.dyslexiaFont = !this.dyslexiaFont;
        document.body.classList.toggle('dyslexia-font', this.dyslexiaFont);
        this.save();
    },

    toggleGrayscale() {
        document.body.style.filter = document.body.style.filter === 'grayscale(100%)' ? '' : 'grayscale(100%)';
    },

    toggleUnderlineLinks() {
        document.body.classList.toggle('underline-links');
    },

    reset() {
        this.fontSize = 16;
        this.highContrast = false;
        this.dyslexiaFont = false;
        document.documentElement.style.fontSize = '16px';
        document.body.classList.remove('high-contrast', 'dyslexia-font');
        document.body.style.filter = '';
        localStorage.removeItem('accessibility');
    },

    save() {
        localStorage.setItem('accessibility', JSON.stringify({
            fontSize: this.fontSize,
            highContrast: this.highContrast,
            dyslexiaFont: this.dyslexiaFont,
        }));
    }
};

window.accessibilityManager.init();

// ===== COUNTER ANIMATION =====
window.animateCounter = function(el, target, duration = 2000, suffix = '') {
    let start = 0;
    const step = target / (duration / 16);
    const timer = setInterval(() => {
        start += step;
        if (start >= target) {
            el.textContent = target.toLocaleString('id-ID') + suffix;
            clearInterval(timer);
        } else {
            el.textContent = Math.floor(start).toLocaleString('id-ID') + suffix;
        }
    }, 16);
};

// ===== OTP INPUT =====
window.initOtpInput = function() {
    const inputs = document.querySelectorAll('.otp-input');
    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            if (e.target.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                inputs[index - 1].focus();
            }
        });
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const paste = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
            paste.split('').forEach((char, i) => {
                if (inputs[i]) inputs[i].value = char;
            });
            if (inputs[paste.length - 1]) inputs[paste.length - 1].focus();
        });
    });
};

// ===== SEARCH =====
window.toggleSearch = function() {
    const overlay = document.getElementById('search-overlay');
    if (overlay) {
        overlay.classList.toggle('open');
        if (overlay.classList.contains('open')) {
            document.getElementById('search-input')?.focus();
        }
    }
};

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.getElementById('search-overlay')?.classList.remove('open');
    }
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        window.toggleSearch();
    }
});

// ===== STICKY HEADER =====
const header = document.getElementById('site-header');
if (header) {
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
        const currentScroll = window.scrollY;
        if (currentScroll > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        lastScroll = currentScroll;
    });
}

// ===== POPUP SWIPER (dipanggil dari komponen popup) =====
window.initPopupSwiper = function(id, itemCount) {
    if (itemCount < 2) return null;
    var el = document.getElementById('popup-swiper-' + id);
    if (!el) return null;
    return new Swiper(el, {
        modules: [Navigation, Pagination, Autoplay],
        loop: true,
        autoplay: { delay: 4000, disableOnInteraction: false },
        pagination: {
            el: document.getElementById('popup-pagination-' + id),
            clickable: true,
        },
        navigation: {
            prevEl: document.getElementById('popup-prev-' + id),
            nextEl: document.getElementById('popup-next-' + id),
        },
    });
};

// ===== INIT ON LOAD =====
document.addEventListener('DOMContentLoaded', () => {
    // AOS init here to avoid forced layout before stylesheets are ready
    AOS.init({
        duration: 700,
        easing: 'ease-out-cubic',
        once: true,
        offset: 80,
    });

    // Init sliders if present
    if (document.querySelector('.hero-swiper')) window.initHeroSlider();
    if (document.querySelector('.banner-swiper')) window.initBannerSlider();
    if (document.querySelector('.sarpras-swiper')) window.initSarprasSlider();
    if (document.querySelector('.testimoni-swiper')) window.initTestimoniSlider();
    if (document.querySelector('.glightbox')) window.initLightbox();
    if (document.querySelector('.otp-input')) window.initOtpInput();

    // Counter animation
    const counters = document.querySelectorAll('[data-counter]');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const raw    = entry.target.dataset.counter;          // e.g. "7000+" atau "1200"
                const num    = parseInt(raw.replace(/[^0-9]/g, ''));  // ambil angka saja
                const suffix = raw.replace(/[0-9,. ]/g, '');         // ambil suffix non-angka ("+", "~", dll)
                window.animateCounter(entry.target, num, 2000, suffix);
                observer.unobserve(entry.target);
            }
        });
    });
    counters.forEach(counter => observer.observe(counter));
});
