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
					templateResult: function(state) {
						var template = state.text,
							level = 0;

						if (state.element) {
							level = parseInt($(state.element).data('level'));
							template = $('<div />').css('padding-left', 20 * level + 'px').text(template);
						}

						return template;
					},
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

			if (field.data('template') === 'icon') {
				var template = function(icon) {
					var output = icon.text;

					if (icon.id) {
						output = '<i class="fas fa-fw fa-' + icon.id + '"></i> ' + icon.text;
					}

					return output;
				};

				$.extend(settings, {
					templateResult: template,
					templateSelection: template,
					escapeMarkup: function(output) {
						return output;
					},
				});
			}

			if (field.data('source')) {
				$.extend(settings, {
					minimumInputLength: 3,
					ajax: {
						url: field.data('source'),
						dataType: 'json',
						delay: 250,
						cache: true,
						data: function(params) {
							return {
								'search': params.term,
								'context': 'list',
								'_wpnonce': hivepressCoreData.apiNonce,
							};
						},
						processResults: function(response) {
							var results = [];

							if (response && response.hasOwnProperty('data')) {
								results = response.data;
							}

							return {
								results: results,
							};
						},
					},
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

			if (field.data('time')) {
				settings['enableTime'] = true;
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

		// File upload
		hivepress.getComponent('file-upload').each(function() {
			var field = $(this),
				container = field.closest('form'),
				selectLabel = field.closest('label'),
				selectButton = selectLabel.find('button').first(),
				messageContainer = selectLabel.parent().find(hivepress.getSelector('messages')).first(),
				responseContainer = selectLabel.parent().children('div').first();

			if (!container.data('model')) {
				container = field.closest('table');
			}

			field.fileupload({
				url: field.data('url'),
				dataType: 'json',
				paramName: 'file',
				formData: {
					'parent_model': container.data('model'),
					'parent_field': field.data('name'),
					'parent': container.data('id'),
					'render': true,
					'_wpnonce': hivepressCoreData.apiNonce,
				},
				start: function() {
					field.prop('disabled', true);

					selectButton.prop('disabled', true);
					selectButton.attr('data-state', 'loading');

					messageContainer.hide().html('');
				},
				stop: function() {
					field.prop('disabled', false);

					selectButton.prop('disabled', false);
					selectButton.attr('data-state', '');
				},
				always: function(e, data) {
					var response = data.jqXHR.responseJSON;

					if (response.hasOwnProperty('data')) {
						if (field.prop('multiple')) {
							responseContainer.append(response.data.html);
						} else {
							responseContainer.html(response.data.html);
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
							messageContainer.show();
						}
					}
				},
			});
		});

		// File delete
		$(document).on('click', hivepress.getSelector('file-delete'), function(e) {
			var container = $(this).parent();

			$.ajax({
				url: $(this).data('url'),
				method: 'DELETE',
				beforeSend: function(xhr) {
					xhr.setRequestHeader('X-WP-Nonce', hivepressCoreData.apiNonce);
				},
			});

			container.remove();

			e.preventDefault();
		});

		// Sortable
		hivepress.getComponent('sortable').each(function() {
			var container = $(this);

			container.sortable({
				stop: function() {
					if (container.children().length > 1) {
						container.children().each(function(index) {
							$.ajax({
								url: $(this).data('url'),
								method: 'POST',
								data: {
									'sort_order': index,
								},
								beforeSend: function(xhr) {
									xhr.setRequestHeader('X-WP-Nonce', hivepressCoreData.apiNonce);
								},
							});
						});
					}
				},
			});
		});
	});
})(jQuery);
