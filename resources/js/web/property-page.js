import { webCustom } from "./global-custom.js";

class PropertyPage {
    constructor() {
        this.handleGallerySlider();
        this.handleGoogleMaps();
    }

    handleGallerySlider() {
        const slider = document.querySelector('#slider');

        if (!slider) {
            // console.log('Slider not found');
            return;
        }

        window.addEventListener('load', function () {
            const swiperParent = new Swiper('.swiper-parent', {
                slidesPerView: 1,
                pagination: {
                    clickable: false
                },
                grabCursor: true,
                navigation: {
                    prevEl: '.slider-arrow-left',
                    nextEl: '.slider-arrow-right'
                },
                breakpoints: {
                    768: {
                        slidesPerView: 2
                    },
                    992: {
                        slidesPerView: 3
                    },
                    1200: {
                        slidesPerView: 3
                    }
                }
            });

            const swiperNested1 = new Swiper('.swiper-nested-1', {
                slidesPerView: 1,
                direction: 'vertical',
                pagination: {
                    clickable: false
                },
                navigation: {
                    prevEl: '#sw1-arrow-top',
                    nextEl: '#sw1-arrow-bottom'
                },
                breakpoints: {
                    768: {
                        slidesPerView: 1
                    },
                    992: {
                        slidesPerView: 1
                    }
                }
            });
        });
    }

    handleGoogleMaps() {
        const gMap = document.querySelector('#g-maps');

        if (!gMap) {
            // console.log('Slider not found');
            return;
        }

        const staticMap = document.querySelector('.static-map');
        const displayMap = document.querySelector('.display-gmap');

        displayMap.style.display = 'none';

        staticMap.querySelector('.button')
            .addEventListener('click', () => {
                staticMap.style.display = 'none';

                displayMap.classList.add('gmap');
                CNVS.GoogleMaps.init('.gmap');

                displayMap.style.display = 'block';
            });
    }
}

const propertyPage = new PropertyPage();
