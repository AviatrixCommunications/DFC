import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, A11y } from 'swiper/modules';

export default function initSliders() {

  document.querySelectorAll('.swiper--with-arrows').forEach(el => {
    new Swiper(el, {
      modules: [Navigation, A11y],
      loop: false,
      navigation: {
        nextEl: el.querySelector('.swiper-button-next'),
        prevEl: el.querySelector('.swiper-button-prev'),
      },
      slidesPerView: 1,
      spaceBetween: 25,
      breakpoints: {
        768: {
          slidesPerView: 2
        },
        1024: {
          slidesPerView: 4
        },
      },
    });
  });

  document.querySelectorAll('.swiper--with-dots').forEach(el => {
    new Swiper(el, {
      modules: [Pagination, A11y],
      loop: false,
      pagination: {
        el: el.querySelector('.swiper-pagination'),
        clickable: true,
      },
      slidesPerView: 1,
    });
  });

  document.querySelectorAll('.project-carousel__swiper').forEach(el => {
    new Swiper(el, {
      modules: [Navigation, Autoplay, A11y],
      loop: true,
      slidesPerView: 2,
      spaceBetween: 16,
      autoplay: {
        delay: 3000,
        disableOnInteraction: true,
        pauseOnMouseEnter: true,
      },
      speed: 600,
      navigation: {
        nextEl: el.querySelector('.swiper-button-next'),
        prevEl: el.querySelector('.swiper-button-prev'),
      },
      breakpoints: {
        500: {
          slidesPerView: 3,
        },
        768: {
          slidesPerView: 4,
          spaceBetween: 20,
        },
        1024: {
          slidesPerView: 6,
          spaceBetween: 25,
        },
      },
    });
  });
}
