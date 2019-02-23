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

	// Forms
	hivepress.getObject('form').each(function() {
		var form = $(this),
			type = [];

		if (typeof form.data('type') !== 'undefined') {
			type = form.data('type').split(' ');
		}

		if (form.attr('method') === 'POST') {
			form.on('submit', function(e) {
				$.post(hpCoreFrontendData.apiURL + 'hivepress/v1/forms/' + form.data('name'), form.serializeObject(), function(response) {
					if (response) {
						if (response.success) {
							if (response.redirect) {
								window.location.reload(true);
							}
						}

						console.log(response);
					}
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
			url: hpCoreFrontendData.apiURL + 'hivepress/v1/files',
			formData: {
				'form': field.closest('form').data('name'),
				'field': field.attr('name'),
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

				}

				console.log(data.result);
			}
		});
	});
})(jQuery);
