(function($) {
	'use strict';

	$(document).ready(function() {

		// Modal
		hivepress.getComponent('modal').each(function() {
			var url = '#' + $(this).attr('id');

			$('a[href="' + url + '"], button[data-url="' + url + '"]').on('click', function(e) {
				$.fancybox.close();
				$.fancybox.open({
					src: url,
				});

				e.preventDefault();
			});
		});

		// Form
		hivepress.getComponent('form').each(function() {
			var form = $(this),
				messageContainer = form.find(hivepress.getSelector('messages')).first(),
				messageClass = messageContainer.attr('class').split(' ')[0],
				captcha = form.find('.g-recaptcha'),
				captchaId = $('.g-recaptcha').index(captcha.get(0)),
				submitButton = form.find(':submit');

			if (form.data('autosubmit') === true) {
				form.on('change', function() {
					form.submit();
				});
			}

			form.on('submit', function(e) {
				messageContainer.hide().html('').removeClass(messageClass + '--success ' + messageClass + '--error');
				submitButton.prop('disabled', true);
				submitButton.attr('data-state', 'loading');

				if (typeof tinyMCE !== 'undefined') {
					tinyMCE.triggerSave();
				}

				if (form.data('action')) {
					$.ajax({
						url: form.data('action'),
						method: form.data('method') ? form.data('method') : form.attr('method'),
						data: form.serializeJSON(),
						beforeSend: function(xhr) {
							if ($('body').hasClass('logged-in')) {
								xhr.setRequestHeader('X-WP-Nonce', hivepressCoreData.apiNonce);
							}
						},
						complete: function(xhr) {
							var response = xhr.responseJSON,
								redirect = form.data('redirect');

							submitButton.prop('disabled', false);
							submitButton.attr('data-state', '');

							if (typeof grecaptcha !== 'undefined' && captcha.length) {
								grecaptcha.reset(captchaId);
							}

							if (response == null || response.hasOwnProperty('data')) {
								if (form.data('message') && xhr.status !== 307) {
									messageContainer.addClass(messageClass + '--success').html('<div>' + form.data('message') + '</div>').show();
								}

								if (redirect || xhr.status === 307) {
									if (typeof redirect === 'string') {
										window.location.replace(redirect);
									} else {
										window.location.reload(true);
									}
								} else if (form.data('reset') || !form.is('[data-id]')) {
									form.trigger('reset');
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
						},
					});

					e.preventDefault();
				}
			});
		});

		// Toggle
		hivepress.getComponent('toggle').each(function() {
			var button = $(this);

			button.on('click', function(e) {
				var caption = button.attr('data-caption'),
					iconClass = button.attr('data-icon'),
					icon = button.find('i'),
					label = button.find('span');

				button.attr('data-icon', icon.attr('class').split(' fa-')[1]);

				icon.attr('class', function(i, c) {
					return c.replace(/ fa-[a-z0-9-]+/g, '');
				}).addClass('fa-' + iconClass);

				if (label.length) {
					button.attr('data-caption', label.text());
					label.text(caption);
				} else {
					button.attr('data-caption', button.attr('title'));
					button.attr('title', caption);
				}

				if (button.attr('data-state') !== 'active') {
					button.attr('data-state', 'active');
				} else {
					button.attr('data-state', '');
				}

				$.ajax({
					url: button.data('url'),
					method: 'POST',
					beforeSend: function(xhr) {
						if ($('body').hasClass('logged-in')) {
							xhr.setRequestHeader('X-WP-Nonce', hivepressCoreData.apiNonce);
						}
					},
				});

				e.preventDefault();
			});
		});

		// Range slider
		hivepress.getComponent('range-slider').each(function() {
			var container = $(this),
				fields = $(this).find('input[type="number"]'),
				minField = fields.first(),
				maxField = fields.last(),
				slider = null;

			if (!minField.val()) {
				minField.val(minField.attr('min'));
			}

			if (!maxField.val()) {
				maxField.val(maxField.attr('max'));
			}

			slider = $('<div />').appendTo(container).slider({
				range: true,
				min: Number(minField.attr('min')),
				max: Number(maxField.attr('max')),
				values: [Number(minField.val()), Number(maxField.val())],
				slide: function(e, ui) {
					minField.val(ui.values[0]);
					maxField.val(ui.values[1]);
				},
			});

			slider.wrap('<div />');

			fields.on('change', function() {
				if (!minField.val()) {
					minField.val(minField.attr('min'));
				}

				if (!maxField.val()) {
					maxField.val(maxField.attr('max'));
				}

				slider.slider('values', [Number(minField.val()), Number(maxField.val())]);
			});
		});

		// Sticky
		$(window).on('load', function() {
			hivepress.getComponent('sticky').each(function() {
				var container = $(this),
					spacing = 32;

				if ($('#wpadminbar').length) {
					spacing = spacing + $('#wpadminbar').height();
				}

				if (container.height() === 0) {
					container.hide();
				} else if ($(window).width() >= 768) {
					container.wrapInner('<div />');

					container.children('div').stickySidebar({
						topSpacing: spacing,
						bottomSpacing: spacing,
					});
				}
			});
		});

		// Carousel slider
		hivepress.getComponent('carousel-slider').each(function() {
			var container = $(this);

			if (container.find('img').length > 1) {
				container.imagesLoaded(function() {
					var containerClass = container.attr('class').split(' ')[0],
						images = container.find('img'),
						slider = images.wrap('<div />').parent().wrapAll('<div />').parent(),
						carousel = slider.clone();

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
				});
			}
		});
	});
})(jQuery);
