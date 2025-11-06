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
  new LazyLoad({
    elements_selector: '.lazy',
  });
};

const initNavigation = () => {
  const toggler = document.querySelector('.navbar-toggler');
  const menu = document.querySelector('.navigation-mobile');

  if (!toggler || !menu) {
    return;
  }

  toggler.addEventListener('click', () => {
    toggler.classList.toggle('closed');
    menu.classList.toggle('visible');
    document.body.classList.toggle('no-scroll', menu.classList.contains('visible'));
  });

  document
    .querySelectorAll('.navigation-mobile a, .close-menu')
    .forEach((element) => {
      element.addEventListener('click', () => {
        document.body.classList.remove('no-scroll');
        menu.classList.remove('visible');
        toggler.classList.remove('closed');
      });
    });
};

const initSwiper = () => {
  const slider = document.querySelector('.block-cards-list');

  if (!slider) {
    return;
  }

  // eslint-disable-next-line no-new
  new Swiper('.block-cards-list', {
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

const initCollapseLinks = () => {
  document.querySelectorAll('.collapse-link').forEach((link) => {
    link.addEventListener('click', () => {
      // Placeholder for collapse interaction
      // eslint-disable-next-line no-console
      console.log('collapse');
    });
  });
};

document.addEventListener('DOMContentLoaded', () => {
  window.Swiper = Swiper;
  initFilters();
  initNavigation();
  initSwiper();
  initLazyLoad();
  initCollapseLinks();
});
