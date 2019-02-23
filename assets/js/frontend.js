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
					if (response.hasOwnProperty('success')) {
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
})(jQuery);
