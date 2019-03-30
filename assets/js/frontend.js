(function($) {
	'use strict';

	/**
	 * Gets jQuery component.
	 */
	function getComponent(name) {
		return $('[data-component=' + name + ']');
	}

	$(document).ready(function() {

		// Modals
		getComponent('modal').each(function() {
			var url = '#' + $(this).attr('id');

			$('a[href=' + url + ']').on('click', function(e) {
				$.fancybox.close();
				$.fancybox.open({
					src: url,
				});

				e.preventDefault();
			});
		});

		// Links
		getComponent('link').on('click', function() {
			window.location.href = $(this).data('url');
		});

		// Forms.
		getComponent('form').each(function() {
			var form = $(this),
				messageContainer = form.children('div').first(),
				messageClass = messageContainer.attr('class').split(' ')[0],
				captcha = form.find('.g-recaptcha'),
				captchaId = $('.g-recaptcha').index(captcha.get(0)),
				submitButton = form.find(':submit');

			form.on('submit', function(e) {
				messageContainer.hide().html('').removeClass(messageClass + '--success ' + messageClass + '--error');
				submitButton.prop('disabled', true);
				submitButton.attr('data-state', 'loading');

				if (form.data('action')) {
					$.ajax({
						url: form.data('action'),
						method: form.data('method') ? form.data('method') : form.attr('method'),
						data: form.serializeJSON(),
						beforeSend: function(xhr) {
							xhr.setRequestHeader('X-WP-Nonce', hpCoreFrontendData.apiNonce);
						},
						complete: function(xhr) {
							var response = xhr.responseJSON;

							submitButton.prop('disabled', false);
							submitButton.attr('data-state', '');

							if (response.hasOwnProperty('data')) {
								if (typeof grecaptcha !== 'undefined' && captcha.length) {
									grecaptcha.reset(captchaId);
								}

								if (form.data('message')) {
									messageContainer.addClass(messageClass + '--success').html('<div>' + form.data('message') + '</div>').show();
								}

								if (form.data('redirect')) {
									if (form.data('redirect') === true) {
										window.location.reload(true);
									} else {
										window.location.replace(form.data('redirect'));
									}
								}
							} else if (response.hasOwnProperty('error')) {
								if (response.error.hasOwnProperty('errors')) {
									$.each(response.error.errors, function(index, error) {
										messageContainer.append('<div>' + error.message + '</div>');
									});
								} else if (response.error.hasOwnProperty('message')) {
									messageContainer.html('<div>' + response.error.message + '</div>');
								}

								if (!messageContainer.is(':empty')) {
									messageContainer.addClass(messageClass + '--error').show();
								}
							}

							if (messageContainer.is(':visible') && form.offset().top < $(window).scrollTop()) {
								$('html, body').animate({
									scrollTop: form.offset().top,
								}, 500);
							}

							console.log(response);
						},
					});

					e.preventDefault();
				}
			});
		});

		// Stickies
		getComponent('sticky').each(function() {
			var container = $(this),
				spacing = 30 + $('#wpadminbar').height();

			container.wrapInner('<div />');

			container.children('div').stickySidebar({
				topSpacing: spacing,
				bottomSpacing: spacing,
			});
		});

		// Sliders
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
					slidesToShow: 6,
					slidesToScroll: 1,
					infinite: false,
					focusOnSelect: true,
					prevArrow: '<div class="slick-arrow slick-prev"><i class="hp-icon fas fa-chevron-left"></i></div>',
					nextArrow: '<div class="slick-arrow slick-next"><i class="hp-icon fas fa-chevron-right"></i></div>',
					asNavFor: slider,
					responsive: [{
							breakpoint: 1025,
							settings: {
								slidesToShow: 5,
							},
						},
						{
							breakpoint: 769,
							settings: {
								slidesToShow: 4,
							},
						},
						{
							breakpoint: 481,
							settings: {
								slidesToShow: 3,
							},
						},
					],
				});
			}
		});
	});
})(jQuery);
