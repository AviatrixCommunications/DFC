import Swiper from 'swiper';
import { Scrollbar, A11y } from 'swiper/modules';

export default function initPanelSlider() {
  const sliders = document.querySelectorAll('.aviatrix-block--panel-slider .panel-slider__swiper');

  sliders.forEach(el => {
    new Swiper(el, {
      modules: [Scrollbar, A11y],
      slidesPerView: 1,
      spaceBetween: 0,
      scrollbar: {
        el: el.querySelector('.swiper-scrollbar'),
        draggable: true,
        hide: false,
      },
      breakpoints: {
        782: {
          slidesPerView: 2,
        },
        1024: {
          slidesPerView: 3,
        },
      },
    });
  });
}
