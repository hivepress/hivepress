(function($) {
	'use strict';

	/**
	 * Gets jQuery component.
	 */
	function getComponent(name) {
		return $('[data-component=' + name + ']');
	}

	$(document).ready(function() {

		// Sticky
		getComponent('sticky').each(function() {
			var container = $(this),
				spacing = 30 + $('#wpadminbar').height();

			container.wrapInner('<div />');

			container.children('div').stickySidebar({
				topSpacing: spacing,
				bottomSpacing: spacing,
			});
		});

		// Slider
		getComponent('slider').each(function() {
			var container = $(this),
				containerClass = container.attr('class').split(' ')[0],
				images = container.find('img'),
				slider = images.wrap('<div />').parent().wrapAll('<div />').parent(),
				carousel = slider.clone();

			if (images.length > 1) {
				container.html('');

				slider.appendTo(container);
				carousel.appendTo(container);

				slider.addClass(containerClass + '-slider').slick({
					slidesToShow: 1,
					slidesToScroll: 1,
					adaptiveHeight: true,
					infinite: false,
					arrows: false,
					asNavFor: carousel,
				});

				carousel.addClass(containerClass + '-carousel').slick({
					slidesToShow: Math.round(container.width() / 125),
					slidesToScroll: 1,
					infinite: false,
					focusOnSelect: true,
					prevArrow: '<div class="slick-arrow slick-prev"><i class="hp-icon fas fa-chevron-left"></i></div>',
					nextArrow: '<div class="slick-arrow slick-next"><i class="hp-icon fas fa-chevron-right"></i></div>',
					asNavFor: slider,
				});
			}
		});
	});
})(jQuery);
