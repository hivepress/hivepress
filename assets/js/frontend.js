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

		if (type.includes('request')) {
			$.ajax({
				url: button.data('url'),
				method: button.data('method'),
				data: button.data('params'),
				beforeSend: function(xhr) {
					xhr.setRequestHeader('X-WP-Nonce', hpCoreFrontendData.apiNonce);
				},
				complete: function(xhr) {
					console.log(xhr.responseText);
				},
			});
		}

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

		// todo.
		if (form.attr('method') === 'POST') {
			var url = form.attr('action'),
				method = form.attr('method'),
				captcha = form.find('.g-recaptcha'),
				captchaId = $('.g-recaptcha').index(captcha.get(0)),
				submitButton = form.find(':submit');

			if (form.data('url')) {
				url = form.data('url');
			}

			if (form.data('method')) {
				method = form.data('method');
			}

			form.on('submit', function(e) {
				$.ajax({
					url: url,
					method: method,
					data: form.serializeObject(),
					beforeSend: function(xhr) {
						xhr.setRequestHeader('X-WP-Nonce', hpCoreFrontendData.apiNonce);
					},
					complete: function(xhr) {
						if (xhr.responseJSON.hasOwnProperty('data')) {
							if (typeof grecaptcha !== 'undefined' && captcha.length) {
								grecaptcha.reset(captchaId);
							}
						}

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
			url: field.data('url'),
			dataType: 'json',
			paramName: 'file',
			formData: {
				'form_name': field.closest('form').data('name'),
				'field_name': field.attr('name'),
				// todo
				'parent_id': 163,
				'render': true,
				'_wpnonce': hpCoreFrontendData.apiNonce,
			},
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
				if (data.result.hasOwnProperty('data')) {
					if (field.prop('multiple')) {
						responseContainer.append(data.result.data.html);
					} else {
						responseContainer.html(data.result.data.html);
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
							complete: function(xhr) {
								console.log(xhr.responseText);
							},
						});
					});
				}
			},
		});
	});
})(jQuery);
