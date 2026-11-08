import Alpine from 'alpinejs';
import Swiper from 'swiper';
import { Autoplay, Navigation, Pagination, Keyboard, A11y } from 'swiper/modules';

if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}

function initCarousels() {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Hero Carousels
    document.querySelectorAll('[data-hero-carousel]').forEach((container) => {
        if (container.dataset.swiperInitialized) return;
        container.dataset.swiperInitialized = 'true';

        const swiperEl = container.querySelector('.swiper');
        const dotsContainer = container.querySelector('[data-hero-dots]');
        const slidesCount = swiperEl ? swiperEl.querySelectorAll('.swiper-slide').length : 0;

        if (!swiperEl || slidesCount === 0) return;

        const updateDots = (activeIndex) => {
            if (!dotsContainer) return;
            const dots = dotsContainer.querySelectorAll('[data-slide-index]');
            dots.forEach((dot, idx) => {
                if (idx === activeIndex) {
                    dot.classList.remove('w-1.5', 'bg-gray-400', 'hover:bg-gray-500');
                    dot.classList.add('w-6', 'bg-[#16a34a]');
                    dot.setAttribute('aria-current', 'true');
                } else {
                    dot.classList.remove('w-6', 'bg-[#16a34a]');
                    dot.classList.add('w-1.5', 'bg-gray-400', 'hover:bg-gray-500');
                    dot.removeAttribute('aria-current');
                }
            });
        };

        const swiper = new Swiper(swiperEl, {
            modules: [Autoplay, Keyboard, A11y],
            loop: slidesCount > 1,
            speed: prefersReducedMotion ? 0 : 500,
            autoplay: slidesCount > 1 && !prefersReducedMotion
                ? {
                      delay: 5000,
                      disableOnInteraction: false,
                      pauseOnMouseEnter: true,
                  }
                : false,
            keyboard: {
                enabled: true,
                onlyInViewport: true,
            },
            on: {
                init(s) {
                    updateDots(s.realIndex);
                },
                slideChange(s) {
                    updateDots(s.realIndex);
                },
            },
        });

        // Pause on focus inside hero carousel for accessibility
        swiperEl.addEventListener('focusin', () => {
            if (swiper.autoplay && swiper.autoplay.running) {
                swiper.autoplay.stop();
            }
        });
        swiperEl.addEventListener('focusout', () => {
            if (swiper.autoplay && !prefersReducedMotion && slidesCount > 1) {
                swiper.autoplay.start();
            }
        });

        if (dotsContainer) {
            dotsContainer.querySelectorAll('[data-slide-index]').forEach((dot) => {
                dot.addEventListener('click', () => {
                    const index = parseInt(dot.getAttribute('data-slide-index') || '0', 10);
                    if (slidesCount > 1) {
                        swiper.slideToLoop(index);
                    } else {
                        swiper.slideTo(index);
                    }
                });
            });
        }
    });

    // Popular News Carousels (/berita)
    document.querySelectorAll('[data-popular-carousel]').forEach((container) => {
        if (container.dataset.swiperInitialized) return;
        container.dataset.swiperInitialized = 'true';

        const swiperEl = container.querySelector('.swiper');
        const prevBtn = container.querySelector('[data-popular-prev]');
        const nextBtn = container.querySelector('[data-popular-next]');
        const dotsContainer = container.querySelector('[data-popular-dots]');
        const slidesCount = swiperEl ? swiperEl.querySelectorAll('.swiper-slide').length : 0;

        if (!swiperEl || slidesCount === 0) return;

        const updateDots = (activeIndex) => {
            if (!dotsContainer) return;
            const dots = dotsContainer.querySelectorAll('[data-popular-dot]');
            dots.forEach((dot, idx) => {
                if (idx === activeIndex) {
                    dot.classList.remove('bg-gray-300');
                    dot.classList.add('bg-[#16a34a]');
                    dot.setAttribute('aria-current', 'true');
                } else {
                    dot.classList.remove('bg-[#16a34a]');
                    dot.classList.add('bg-gray-300');
                    dot.removeAttribute('aria-current');
                }
            });
        };

        const swiper = new Swiper(swiperEl, {
            modules: [Navigation, Pagination, Keyboard, A11y],
            slidesPerView: 1,
            spaceBetween: 20,
            speed: prefersReducedMotion ? 0 : 400,
            rewind: true,
            keyboard: {
                enabled: true,
                onlyInViewport: true,
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 24,
                },
            },
            on: {
                init(s) {
                    updateDots(s.realIndex);
                },
                slideChange(s) {
                    updateDots(s.realIndex);
                },
            },
        });

        if (prevBtn) {
            prevBtn.addEventListener('click', () => swiper.slidePrev());
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', () => swiper.slideNext());
        }
        if (dotsContainer) {
            dotsContainer.querySelectorAll('[data-popular-dot]').forEach((dot) => {
                dot.addEventListener('click', () => {
                    const index = parseInt(dot.getAttribute('data-popular-dot') || '0', 10);
                    swiper.slideTo(index);
                });
            });
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCarousels);
} else {
    initCarousels();
}
