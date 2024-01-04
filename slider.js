var swiper = new Swiper(".blog-slider", {
  autoplay: {
    delay: 5000,
  },
  spaceBetween: 30,
  effect: "fade",
  loop: true,
  mousewheel: {
    invert: false,
  },
  //autoHeight: true,
  pagination: {
    el: ".blog-slider__pagination",
    clickable: true,
  },
});
