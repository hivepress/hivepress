var hivepress = {

	/**
	 * Gets prefixed selector.
	 */
	getSelector: function(name) {
		return '.hp-js-' + name;
	},

	/**
	 * Gets jQuery object.
	 */
	getObject: function(name) {
		return jQuery(this.getSelector(name));
	},
};

(function($) {
	'use strict';

	/**
	 * Serializes jQuery object.
	 */
	$.fn.serializeObject = function() {
		var data = {};

		$.each(this.serializeArray(), function() {
			if (this.name.slice(-1) == ']') {
				var name = this.name.split('[')[0];

				if (!data.hasOwnProperty(name)) {
					data[name] = [];
				}

				data[name].push(this.value);
			} else {
				data[this.name] = this.value;
			}
		});

		return data;
	}

	// Button
	$(document).on('click', hivepress.getSelector('button'), function(e) {
		var button = $(this),
			type = [];

		if (typeof button.data('type') !== 'undefined') {
			type = button.data('type').split(' ');
		}

		if (type.includes('remove')) {
			button.parent().remove();
		}

		// todo.
		//if (type.includes('submit')) {
		$.post(hpCoreFrontendData.apiURL + 'hivepress/v1/forms/' + button.data('name'), $.extend(button.data('values'), {
			'nonce': button.data('nonce'),
			'_wpnonce': hpCoreFrontendData.apiNonce,
		}));
		//}

		e.preventDefault();
	});

	// Form
	hivepress.getObject('form').each(function() {
		var form = $(this),
			type = [];

		if (typeof form.data('type') !== 'undefined') {
			type = form.data('type').split(' ');
		}

		if (type.includes('autosubmit')) {
			form.on('change', function() {
				form.submit();
			});
		}

		if (form.attr('method') === 'POST') {
			var captcha = form.find('.g-recaptcha'),
				captchaId = $('.g-recaptcha').index(captcha.get(0)),
				submitButton = form.find(':submit');

			form.on('submit', function(e) {
				// $.post(form.attr('action'), $.extend(form.serializeObject(), {
				// 	'_wpnonce': hpCoreFrontendData.apiNonce,
				// }), function(response) {
				// 	if (response) {
				// 		// todo.
				// 		// if (response.success) {
				// 		// 	if (response.redirect) {
				// 		// 		window.location.reload(true);
				// 		// 	} else {
				// 		// 		if (typeof grecaptcha !== 'undefined' && captcha.length) {
				// 		// 			grecaptcha.reset(captchaId);
				// 		// 		}
				// 		// 	}
				// 		// }
				// 	}
				//
				// 	console.log(response);
				// });

				$.ajax({
					url: form.attr('action'),
					method: 'DELETE',
					beforeSend: function(xhr) {
						xhr.setRequestHeader('X-WP-Nonce', hpCoreFrontendData.apiNonce);
					},
					data: form.serializeObject(),
					complete: function(xhr) {
						console.log(xhr.responseText);
					},
				});

				e.preventDefault();
			});
		}
	});

	// File upload
	hivepress.getObject('file-upload').each(function() {
		var field = $(this),
			selectLabel = field.closest('label'),
			selectButton = selectLabel.find('button').first(),
			messageContainer = $('<div />').insertBefore(selectLabel),
			responseContainer = selectLabel.parent().children('div').first();

		field.fileupload({
			url: hpCoreFrontendData.apiURL + 'hivepress/v1/forms/file_upload',
			formData: {
				'form_name': field.closest('form').data('name'),
				'field_name': field.attr('name'),
				'nonce': field.data('nonce'),
				'_wpnonce': hpCoreFrontendData.apiNonce,
			},
			dataType: 'json',
			start: function() {
				field.prop('disabled', true);

				selectButton.prop('disabled', true);
				selectButton.attr('data-state', 'loading');
			},
			stop: function() {
				field.prop('disabled', false);

				selectButton.prop('disabled', false);
				selectButton.attr('data-state', '');
			},
			done: function(e, data) {
				if (data.result) {
					if (data.result.success) {
						if (field.prop('multiple')) {
							responseContainer.append(data.result.response);
						} else {
							responseContainer.html(data.result.response);
						}
					} else {
						// todo.
					}
				}

				console.log(data.result);
			}
		});
	});

	// Sortable
	hivepress.getObject('sortable').each(function() {
		var container = $(this),
			form = container.closest('form');

		container.sortable({
			stop: function() {
				if (container.children().length > 1) {
					$.post(hpCoreFrontendData.apiURL + 'hivepress/v1/forms/file_sort', $.extend(form.serializeObject(), {
						'nonce': container.data('nonce'),
						'_wpnonce': hpCoreFrontendData.apiNonce,
					}));
				}
			},
		});
	});
})(jQuery);
