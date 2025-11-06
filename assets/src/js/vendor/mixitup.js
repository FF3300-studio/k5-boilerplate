class MixitupMixer {
  constructor(container, config = {}) {
    this.container = typeof container === 'string'
      ? document.querySelector(container)
      : container;
    this.config = config;

    if (!this.container) {
      console.warn('mixitup: container not found');
      return;
    }

    this.targets = Array.from(this.container.children);
    this.activeFilters = new Set();
  }

  filter(selector) {
    if (!this.container) {
      return;
    }

    if (!selector || selector === 'all') {
      this.showAll();
      return;
    }

    const filters = selector.split(',').map((item) => item.trim()).filter(Boolean);

    this.targets.forEach((target) => {
      const shouldShow = filters.some((filter) => target.matches(filter));
      target.style.display = shouldShow ? '' : 'none';
    });
  }

  showAll() {
    if (!this.container) {
      return;
    }

    this.targets.forEach((target) => {
      target.style.display = '';
    });
  }

  destroy() {
    this.showAll();
  }
}

function mixitup(container, config = {}) {
  return new MixitupMixer(container, config);
}

mixitup.use = () => {};

export default mixitup;
