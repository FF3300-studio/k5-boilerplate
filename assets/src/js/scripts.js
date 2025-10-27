
// /* FILTERS */

// jQuery(document).ready(function(){
//     var filtercontainer = document.querySelector('.filters-container-collection');
//     var filteritems;
//     if (filtercontainer) {
//         filteritems = mixitup(filtercontainer, {
//             controls: {
//                 toggleDefault: 'all',
//                 toggleLogic: 'or'
//             },
//             selectors: {
//                 control: '[data-mixitup-control]'
//             },
//             animation: {
//                 enable: false
//             },
//             multifilter: {
//                 enable: true, 
//                 logicWithinGroup: 'and',
//                 logicBetweenGroups: 'and'
//             },
//         });
//     }
// });

/* LAZY LOADER 

var lazyLoadInstance = new LazyLoad({
    elements_selector: ".lazy",
}); */

/* CONTENT HEIGHT CALCULATION */


/* ANCHORS NAVIGATION */

jQuery( ".collapse-link" ).click(function() {
    console.log("collapse");
});

/* MOBILE NAVIGATION */

jQuery(document).ready(function () {
    jQuery(".navbar-toggler").click(function() {
        jQuery(this).toggleClass("closed");
        jQuery(".navigation-mobile").toggleClass("visible");
    });
});

/* PASS SLIDER */

jQuery(document).ready(function() {
    var swiper = new Swiper('.block-cards-list', {
        spaceBetween: 30,  // Space between slides in px
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        grabCursor: true,  // Allows dragging
        loop: true,        // Optional: Enables continuous loop
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
});

/* ITINERARI SLIDER */
document.addEventListener("DOMContentLoaded", function () {
    const toggler = document.querySelector(".navbar-toggler");
    const menu = document.querySelector(".navigation-mobile");
  
    if (toggler && menu) {
      toggler.addEventListener("click", function () {
        const isVisible = menu.classList.contains("visible");
  
        if (!isVisible) {
          document.body.classList.add("no-scroll");
        } else {
          document.body.classList.remove("no-scroll");
        }
      });
    }
  });

  document.querySelectorAll('.navigation-mobile a, .close-menu').forEach(el => {
    el.addEventListener('click', () => {
      document.body.classList.remove('no-scroll');
    });
  });
  
  