import 'bootstrap/dist/js/bootstrap.esm.js';
import mixitup from 'mixitup';
import mixitupMultifilter from 'mixitup-multifilter';
import Swiper from 'swiper/bundle';
import LazyLoad from 'vanilla-lazyload';

mixitup.use(mixitupMultifilter);

const initFilters = () => {
  const filterContainer = document.querySelector('.filters-container-collection');

  if (!filterContainer) {
    return;
  }

  mixitup(filterContainer, {
    controls: {
      toggleDefault: 'all',
      toggleLogic: 'or',
    },
    selectors: {
      control: '[data-mixitup-control]',
    },
    animation: {
      enable: false,
    },
    multifilter: {
      enable: true,
      logicWithinGroup: 'and',
      logicBetweenGroups: 'and',
    },
  });
};

const initLazyLoad = () => {
  if (!document.querySelector('.lazy')) {
    return;
  }

  new LazyLoad({
    elements_selector: '.lazy',
  });
};

const initAnchorNavigation = () => {
  const collapseLinks = document.querySelectorAll('.collapse-link');

  if (!collapseLinks.length) {
    return;
  }

  collapseLinks.forEach((link) => {
    link.addEventListener('click', () => {
      // Manteniamo il comportamento precedente per il debug delle sezioni collapse
      console.log('collapse');
    });
  });
};

const initMobileNavigation = () => {
  const toggler = document.querySelector('.navbar-toggler');
  const menu = document.querySelector('.navigation-mobile');

  if (!toggler || !menu) {
    return;
  }

  const closeMenu = () => {
    menu.classList.remove('visible');
    toggler.classList.remove('closed');
    document.body.classList.remove('no-scroll');
  };

  toggler.addEventListener('click', () => {
    const isVisible = menu.classList.toggle('visible');

    toggler.classList.toggle('closed', isVisible);
    document.body.classList.toggle('no-scroll', isVisible);
  });

  document
    .querySelectorAll('.navigation-mobile a, .close-menu')
    .forEach((element) => {
      element.addEventListener('click', closeMenu);
    });
};

const initPassSlider = () => {
  const sliderElement = document.querySelector('.block-cards-list');

  if (!sliderElement) {
    return;
  }

  new Swiper(sliderElement, {
    spaceBetween: 30,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    grabCursor: true,
    loop: true,
    breakpoints: {
      640: {
        slidesPerView: 1,
        spaceBetween: 20,
      },
      768: {
        slidesPerView: 3,
        spaceBetween: 30,
      },
      1024: {
        slidesPerView: 4,
        spaceBetween: 30,
      },
    },
  });
};

document.addEventListener('DOMContentLoaded', () => {
  window.Swiper = Swiper;
  initFilters();
  initLazyLoad();
  initAnchorNavigation();
  initMobileNavigation();
  initPassSlider();
});
