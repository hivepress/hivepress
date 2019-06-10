var hivepress = {

	/**
	 * Gets component selector.
	 */
	getSelector: function(name) {
		return '[data-component=' + name + ']';
	},

	/**
	 * Gets component object.
	 */
	getComponent: function(name) {
		return jQuery(this.getSelector(name));
	},
};

(function($) {
	'use strict';

	$(document).ready(function() {

		// Modal
		hivepress.getComponent('modal').each(function() {
			var url = '#' + $(this).attr('id');

			$('a[href=' + url + '], button[data-url=' + url + ']').on('click', function(e) {
				$.fancybox.close();
				$.fancybox.open({
					src: url,
				});

				e.preventDefault();
			});
		});

		// Link
		hivepress.getComponent('link').on('click', function(e) {
			var url = $(this).data('url');

			if (!url.startsWith('#')) {
				window.location.href = url;
			}

			e.preventDefault();
		});

		// Form
		hivepress.getComponent('form').each(function() {
			var form = $(this),
				messageContainer = form.find('[data-element=messages]'),
				messageClass = messageContainer.attr('class').split(' ')[0],
				captcha = form.find('.g-recaptcha'),
				captchaId = $('.g-recaptcha').index(captcha.get(0)),
				submitButton = form.find(':submit');

			if (form.data('submit') === true) {
				form.on('change', function() {
					form.submit();
				});
			}

			form.on('submit', function(e) {
				messageContainer.hide().html('').removeClass(messageClass + '--success ' + messageClass + '--error');
				submitButton.prop('disabled', true);
				submitButton.attr('data-state', 'loading');
				submitButton.addClass('is-loading');

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
							submitButton.removeClass('is-loading');

							if (typeof grecaptcha !== 'undefined' && captcha.length) {
								grecaptcha.reset(captchaId);
							}

							if (response === null || response.hasOwnProperty('data')) {
								if (form.data('message')) {
									messageContainer.addClass(messageClass + '--success').html('<div>' + form.data('message') + '</div>').show();
								}

								if (form.data('redirect')) {
									if (form.data('redirect') === true) {
										window.location.reload(true);
									} else {
										window.location.replace(form.data('redirect'));
									}
								} else if (!form.is('[data-id]')) {
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
					label = button.find('span');

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
						xhr.setRequestHeader('X-WP-Nonce', hpCoreFrontendData.apiNonce);
					},
				});

				e.preventDefault();
			});
		});

		// File upload
		hivepress.getComponent('file-upload').each(function() {
			var field = $(this),
				selectLabel = field.closest('label'),
				selectButton = selectLabel.find('button').first(),
				messageContainer = $('<div />').insertBefore(selectLabel),
				responseContainer = selectLabel.parent().children('div').first();

			field.fileupload({
				url: field.data('url'),
				dataType: 'json',
				paramName: 'file',
				formData: {
					'parent_model': field.closest('form').data('model'),
					'parent_field': field.attr('name'),
					'parent_id': field.closest('form').data('id'),
					'render': true,
					'_wpnonce': hpCoreFrontendData.apiNonce,
				},
				start: function() {
					field.prop('disabled', true);

					selectButton.prop('disabled', true);
					selectButton.attr('data-state', 'loading');
					selectButton.addClass('is-loading');
				},
				stop: function() {
					field.prop('disabled', false);

					selectButton.prop('disabled', false);
					selectButton.attr('data-state', '');
					selectButton.removeClass('is-loading');
				},
				done: function(e, data) {
					if (data.result.hasOwnProperty('data')) {
						if (field.prop('multiple')) {
							responseContainer.append(data.result.data.html);
						} else {
							responseContainer.html(data.result.data.html);
						}
					}
				}
			});
		});

		// File delete
		$(document).on('click', hivepress.getSelector('file-delete'), function(e) {
			var container = $(this).parent();

			$.ajax({
				url: container.data('url'),
				method: 'DELETE',
				beforeSend: function(xhr) {
					xhr.setRequestHeader('X-WP-Nonce', hpCoreFrontendData.apiNonce);
				},
			});

			container.remove();

			e.preventDefault();
		});

		// Sortable
		hivepress.getComponent('sortable').each(function() {
			var container = $(this),
				form = container.closest('form');

			container.sortable({
				stop: function() {
					if (container.children().length > 1) {
						container.children().each(function(index) {
							$.ajax({
								url: $(this).data('url'),
								method: 'POST',
								data: {
									'order': index,
								},
								beforeSend: function(xhr) {
									xhr.setRequestHeader('X-WP-Nonce', hpCoreFrontendData.apiNonce);
								},
							});
						});
					}
				},
			});
		});

		// Sticky
		$(window).on('load', function() {
			hivepress.getComponent('sticky').each(function() {
				var container = $(this),
					spacing = 32 + $('#wpadminbar').height();

				container.wrapInner('<div />');

				container.children('div').stickySidebar({
					topSpacing: spacing,
					bottomSpacing: spacing,
				});
			});
		});

		// Slider
		hivepress.getComponent('slider').each(function() {
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
