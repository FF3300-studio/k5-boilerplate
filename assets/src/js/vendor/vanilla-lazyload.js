class VanillaLazyLoad {
  constructor(options = {}) {
    const defaultOptions = {
      elements_selector: '.lazy',
      threshold: 0,
    };

    this.settings = { ...defaultOptions, ...options };
    this.observer = null;
    this.elements = Array.from(document.querySelectorAll(this.settings.elements_selector));

    if ('IntersectionObserver' in window) {
      this.createObserver();
    } else {
      this.loadElementsImmediately();
    }
  }

  createObserver() {
    this.observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          this.loadElement(entry.target);
          this.observer.unobserve(entry.target);
        }
      });
    }, {
      rootMargin: `${this.settings.threshold}px`,
    });

    this.elements.forEach((element) => this.observer.observe(element));
  }

  loadElementsImmediately() {
    this.elements.forEach((element) => this.loadElement(element));
  }

  loadElement(element) {
    if (element.dataset.src) {
      element.src = element.dataset.src;
    }

    if (element.dataset.srcset) {
      element.srcset = element.dataset.srcset;
    }

    element.classList.add('lazy-loaded');
  }
}

export default VanillaLazyLoad;
