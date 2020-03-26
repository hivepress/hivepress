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

		// Link
		hivepress.getComponent('link').on('click', function(e) {
			var url = $(this).data('url');

			if (url.indexOf('#') !== 0) {
				window.location.href = url;
			}

			e.preventDefault();
		});

		// Select
		hivepress.getComponent('select').each(function() {
			var field = $(this),
				settings = {
					width: '100%',
					dropdownAutoWidth: false,
					minimumResultsForSearch: 20,
				};

			if (field.data('style') === 'inline') {
				$.extend(settings, {
					containerCssClass: 'select2-selection--inline',
					dropdownCssClass: 'select2-dropdown--inline',
					width: 'resolve',
					dropdownAutoWidth: true,
					minimumResultsForSearch: -1,
				});
			}

			field.select2(settings);
		});

		// Date
		if (flatpickr.l10ns.hasOwnProperty(hivepressCoreData.language)) {
			flatpickr.localize(flatpickr.l10ns[hivepressCoreData.language]);
		}

		hivepress.getComponent('date').each(function() {
			var field = $(this),
				settings = {
					altInput: true,
					dateFormat: 'Y-m-d',
				};

			if (field.data('format')) {
				settings['dateFormat'] = field.data('format');
			}

			if (field.data('display-format')) {
				settings['altFormat'] = field.data('display-format');
			}

			if (field.data('min-date')) {
				settings['minDate'] = field.data('min-date');
			}

			if (field.data('max-date')) {
				settings['maxDate'] = field.data('max-date');
			}

			if (field.data('mode')) {
				settings['mode'] = field.data('mode');

				if (field.data('mode') === 'range') {
					var fields = field.parent().find('input[type=hidden]').not(field);

					$.extend(settings, {
						defaultDate: [fields.eq(0).val(), fields.eq(1).val()],
						errorHandler: function(error) {},
						onChange: function(selectedDates) {
							var formattedDates = selectedDates.map(function(date) {
								return flatpickr.formatDate(date, settings['dateFormat']);
							});

							if (formattedDates.length === 2) {
								fields.eq(0).val(formattedDates[0]);
								fields.eq(1).val(formattedDates[1]);
							}
						},
					});
				}
			}

			field.flatpickr(settings);
		});
	});
})(jQuery);
