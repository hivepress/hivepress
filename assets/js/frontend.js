(function($) {
	'use strict';

	/**
	 * Gets jQuery component.
	 */
	function getComponent(name) {
		return $('[data-component=' + name + ']');
	}

	// Slider
	$(document).ready(function() {
		getComponent('slider').each(function() {
			var slider = $(this),
				images = slider.find('img'),
				image = images.wrap('<div />').parent().wrapAll('<div />').parent(),
				nav = image.clone();
			console.log(image.html());
			if (images.length > 1) {
				slider.html('');

				image.appendTo(slider);
				nav.appendTo(slider);

				image.addClass('todo-image').slick({
					slidesToShow: 1,
					slidesToScroll: 1,
					adaptiveHeight: true,
					infinite: false,
					arrows: false,
					asNavFor: nav,
				});

				nav.addClass('todo-nav').slick({
					slidesToShow: Math.round(slider.width() / 125),
					slidesToScroll: 1,
					infinite: false,
					focusOnSelect: true,
					prevArrow: '<a href="#" class="slick-arrow slick-prev"><i class="hp-icon fas fa-chevron-left"></i></a>',
					nextArrow: '<a href="#" class="slick-arrow slick-next"><i class="hp-icon fas fa-chevron-right"></i></a>',
					asNavFor: image,
				});
			}
		});
	});

	var TodoModel = function() {
		this.renderBlock = function(element) {
			var object = $(element),
				container = $('[data-block=listing_search_results]');

			if (object.is('form')) {
				object.find(':submit').prop('disabled', true).attr('data-state', 'loading');
			}

			container.attr('data-state', 'loading');

			$.ajax({
				url: 'http://localhost/hivepress/wp-json/hivepress/v1/templates/listings-page/blocks/listing-search-results?render=1',
				method: 'GET',
				data: object.serializeJSON(),
				beforeSend: function(xhr) {
					xhr.setRequestHeader('X-WP-Nonce', hpCoreFrontendData.apiNonce);
				},
				complete: function(xhr) {
					if (object.is('form')) {
						object.find(':submit').prop('disabled', false).removeAttr('data-state');
					}

					container.removeAttr('data-state');

					console.log(xhr.responseText);
					window.history.replaceState({}, null, object.attr('action') + '?' + $.param(object.serializeJSON()));
					container.replaceWith(xhr.responseJSON.data.html);
				},
			});
		};
	};

	ko.applyBindings(new TodoModel());
})(jQuery);
