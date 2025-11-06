class SimpleSwiper {
  constructor(elementOrSelector, options = {}) {
    this.element = typeof elementOrSelector === 'string'
      ? document.querySelector(elementOrSelector)
      : elementOrSelector;
    this.options = options;
    this.wrapper = this.element ? this.element.querySelector('.swiper-wrapper') : null;
    this.slides = this.wrapper ? Array.from(this.wrapper.children) : [];
    this.currentIndex = 0;
    this.autoplayTimer = null;

    if (!this.element || !this.wrapper || this.slides.length === 0) {
      console.warn('Swiper: element not found or has no slides');
      return;
    }

    this.settings = {
      loop: Boolean(options.loop),
      navigation: options.navigation || {},
      autoplay: options.autoplay || false,
      breakpoints: options.breakpoints || {},
      spaceBetween: options.spaceBetween || 0,
      slidesPerView: options.slidesPerView || 1,
    };

    this.updateSlidesPerView();
    this.applySpacing();
    this.bindNavigation();
    this.updateVisibility();
    this.handleAutoplay();
    this.boundHandleResize = this.handleResize.bind(this);
    window.addEventListener('resize', this.boundHandleResize);
  }

  handleResize() {
    this.updateSlidesPerView();
    this.applySpacing();
    this.updateVisibility();
  }

  updateSlidesPerView() {
    const width = window.innerWidth;
    const breakpoints = Object.keys(this.settings.breakpoints)
      .map((value) => Number(value))
      .sort((a, b) => a - b);

    let slidesPerView = this.options.slidesPerView || 1;

    breakpoints.forEach((breakpoint) => {
      if (width >= breakpoint) {
        const config = this.settings.breakpoints[breakpoint];
        if (typeof config.slidesPerView === 'number') {
          slidesPerView = config.slidesPerView;
        }
        if (typeof config.spaceBetween === 'number') {
          this.settings.spaceBetween = config.spaceBetween;
        }
      }
    });

    this.settings.slidesPerView = slidesPerView;
  }

  applySpacing() {
    const spacing = this.settings.spaceBetween;

    this.slides.forEach((slide, index) => {
      slide.style.marginRight = index === this.slides.length - 1 ? '' : `${spacing}px`;
    });
  }

  bindNavigation() {
    const { navigation } = this.settings;

    if (navigation.nextEl) {
      const next = document.querySelector(navigation.nextEl);
      if (next) {
        next.addEventListener('click', () => this.slideNext());
      }
    }

    if (navigation.prevEl) {
      const prev = document.querySelector(navigation.prevEl);
      if (prev) {
        prev.addEventListener('click', () => this.slidePrev());
      }
    }
  }

  handleAutoplay() {
    const { autoplay } = this.settings;

    if (!autoplay) {
      return;
    }

    const delay = typeof autoplay === 'object' ? autoplay.delay || 5000 : 5000;

    this.autoplayTimer = window.setInterval(() => {
      this.slideNext();
    }, delay);
  }

  slideNext() {
    const { slidesPerView, loop } = this.settings;
    const maxIndex = Math.max(this.slides.length - slidesPerView, 0);

    if (this.currentIndex >= maxIndex) {
      this.currentIndex = loop ? 0 : maxIndex;
    } else {
      this.currentIndex += 1;
    }

    this.updateVisibility();
  }

  slidePrev() {
    const { slidesPerView, loop } = this.settings;
    const maxIndex = Math.max(this.slides.length - slidesPerView, 0);

    if (this.currentIndex <= 0) {
      this.currentIndex = loop ? maxIndex : 0;
    } else {
      this.currentIndex -= 1;
    }

    this.updateVisibility();
  }

  updateVisibility() {
    const { slidesPerView } = this.settings;

    this.slides.forEach((slide, index) => {
      const shouldShow = index >= this.currentIndex && index < this.currentIndex + slidesPerView;
      slide.style.display = shouldShow ? '' : 'none';
    });
  }

  destroy() {
    window.removeEventListener('resize', this.boundHandleResize);
    if (this.autoplayTimer) {
      window.clearInterval(this.autoplayTimer);
    }
    this.slides.forEach((slide) => {
      slide.style.display = '';
      slide.style.marginRight = '';
    });
  }
}

export default SimpleSwiper;
