/**
 * SKJ Japan - Custom Interactions
 * Modern smooth animations and UX enhancements
 * No jQuery dependency - uses vanilla JS
 */
(function() {
    'use strict';

    // ---- Intersection Observer for scroll animations ----
    function initScrollAnimations() {
        if (!('IntersectionObserver' in window)) return;

        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('skj-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        // Observe Elementor widgets (if Elementor pages exist)
        document.querySelectorAll('.elementor-widget').forEach(function(el) {
            el.classList.add('skj-animate');
            observer.observe(el);
        });

        // Observe Elementor columns
        document.querySelectorAll('.elementor-column').forEach(function(el) {
            el.classList.add('skj-animate');
            observer.observe(el);
        });
    }

    // ---- Add CSS for scroll animations ----
    function injectAnimationCSS() {
        var css =
            '.skj-animate {' +
            '  opacity: 0; transform: translateY(25px);' +
            '  transition: opacity 0.6s cubic-bezier(0.4,0,0.2,1), transform 0.6s cubic-bezier(0.4,0,0.2,1);' +
            '}' +
            '.skj-animate.skj-visible { opacity: 1; transform: translateY(0); }' +
            '.elementor-column:nth-child(1) .skj-animate { transition-delay: 0s; }' +
            '.elementor-column:nth-child(2) .skj-animate { transition-delay: 0.1s; }' +
            '.elementor-column:nth-child(3) .skj-animate { transition-delay: 0.2s; }' +
            '.elementor-column:nth-child(4) .skj-animate { transition-delay: 0.3s; }';
        var style = document.createElement('style');
        style.textContent = css;
        document.head.appendChild(style);
    }

    // ---- Initialize on DOM ready ----
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // ---- Counter animation for stats ----
    function initCounters() {
        if (!('IntersectionObserver' in window)) return;

        var counters = document.querySelectorAll('[data-count]');
        if (!counters.length) return;

        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        counters.forEach(function(el) { observer.observe(el); });
    }

    function animateCounter(el) {
        var target = parseInt(el.getAttribute('data-count'), 10);
        var suffix = el.getAttribute('data-suffix') || '';
        var prefix = el.getAttribute('data-prefix') || '';
        var duration = 2000;
        var start = 0;
        var startTime = null;

        function easeOutExpo(t) {
            return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
        }

        function formatNumber(n) {
            return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            var eased = easeOutExpo(progress);
            var current = Math.floor(eased * target);
            el.textContent = prefix + formatNumber(current) + suffix;
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = prefix + formatNumber(target) + suffix;
            }
        }

        requestAnimationFrame(step);
    }

    function init() {
        injectAnimationCSS();
        initScrollAnimations();
        initCounters();
    }

})();
