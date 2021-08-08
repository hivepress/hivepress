var hivepress = {

	/**
	 * Gets component selector.
	 */
	getSelector: function(name) {
		return '[data-component="' + name + '"]';
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

			if (field.data('placeholder')) {
				settings['placeholder'] = field.data('placeholder');
			}

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
					ajax: {
						url: field.data('source'),
						dataType: 'json',
						delay: 250,
						cache: true,
						data: function(params) {
							return {
								'search': params.term,
								'context': 'list',
								'parent_value': field.data('parent-value'),
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

				if (field.data('parent')) {
					var parentField = $(':input[name="' + field.data('parent') + '"]');

					if (parentField.length) {
						parentField.on('change', function() {
							field.data('parent-value', $(this).val());
						});
					}
				} else {
					settings['minimumInputLength'] = 3;
				}
			}

			if (field.data('input')) {
				$.extend(settings, {
					tags: true,
					tokenSeparators: [','],
				});
			}

			field.select2(settings);
		});

		// Date
		var dateFormatter = new DateFormatter();

		if (flatpickr.l10ns.hasOwnProperty(hivepressCoreData.language)) {
			var dateSettings = flatpickr.l10ns[hivepressCoreData.language];

			flatpickr.localize(dateSettings);

			dateFormatter = new DateFormatter({
				dateSettings: {
					days: dateSettings.weekdays.longhand,
					daysShort: dateSettings.weekdays.shorthand,
					months: dateSettings.months.longhand,
					monthsShort: dateSettings.months.shorthand,
					meridiem: dateSettings.amPM,
				},
			});
		}

		hivepress.getComponent('date').each(function() {
			var field = $(this),
				settings = {
					allowInput: true,
					altInput: true,
					dateFormat: 'Y-m-d',
					altFormat: 'Y-m-d',
					defaultHour: 0,
					disable: [],
					disableMobile: true,
					onOpen: function(selectedDates, dateStr, instance) {
						$(instance.altInput).prop('readonly', true);
					},
					onClose: function(selectedDates, dateStr, instance) {
						$(instance.altInput).prop('readonly', false);
						$(instance.altInput).blur();
					}
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

			if (field.data('disabled-dates')) {
				settings['disable'].concat(field.data('disabled-dates'));
			}

			if (field.data('disabled-days')) {
				var disabledDates = field.data('disabled-days');

				if (disabledDates.length) {
					function disableDates(date) {
						return disabledDates.indexOf(date.getDay()) !== -1;
					}

					settings['disable'].push(disableDates);
				}
			}

			if (field.is('[data-offset]')) {
				settings['minDate'] = new Date().fp_incr(field.data('offset'));
			}

			if (field.is('[data-window]')) {
				settings['maxDate'] = new Date().fp_incr(field.data('window'));
			}

			if (field.data('time')) {
				settings['enableTime'] = true;
			}

			if (field.data('mode')) {
				settings['mode'] = field.data('mode');

				if (field.data('mode') === 'range') {
					var fields = field.parent().find('input[type="hidden"]').not(field),
						minLength = field.data('min-length'),
						maxLength = field.data('max-length');

					$.extend(settings, {
						defaultDate: [fields.eq(0).val(), fields.eq(1).val()],
						errorHandler: function(error) {},
						onChange: function(selectedDates, dateStr, instance) {
							if (selectedDates.length === 2) {
								if (minLength || maxLength) {
									var length = Math.floor((selectedDates[1].getTime() - selectedDates[0].getTime()) / (1000 * 86400)),
										shift = 0;

									if (length < minLength) {
										shift = minLength - length;
									} else if (length > maxLength) {
										shift = maxLength - length;
									}

									if (shift) {
										selectedDates[1].setDate(selectedDates[1].getDate() + shift);

										instance.setDate(selectedDates);
										instance.open();
									}
								}

								var formattedDates = selectedDates.map(function(date) {
									return dateFormatter.formatDate(date, settings['dateFormat']);
								});

								fields.eq(0).val(formattedDates[0]);
								fields.eq(1).val(formattedDates[1]);
							}
						},
					});
				}
			}

			$.extend(settings, {
				time_24hr: settings['altFormat'].indexOf('a') === -1 && settings['altFormat'].indexOf('A') === -1,
				parseDate: function(date) {
					var parsedDate = dateFormatter.parseDate(date, settings['dateFormat']);

					if (settings['dateFormat'] === 'U') {
						parsedDate = new Date(parsedDate.toLocaleString('en-US', {
							timeZone: 'UTC',
						}));
					}

					return parsedDate;
				},
				formatDate: function(date, format) {
					var formattedDate = dateFormatter.formatDate(date, format);

					if (format === 'U') {
						formattedDate = parseInt(formattedDate) - date.getTimezoneOffset() * 60;
					}

					return formattedDate;
				},
			});

			field.flatpickr(settings);
		});

		// Time
		hivepress.getComponent('time').each(function() {
			var field = $(this),
				settings = {
					allowInput: true,
					altInput: true,
					noCalendar: true,
					enableTime: true,
					dateFormat: 'U',
					altFormat: 'g:i A',
					defaultHour: 0,
					disableMobile: true,
					parseDate: function(time) {
						var date = new Date();

						date.setHours(Math.floor(time / 3600));
						date.setMinutes(Math.floor((time % 3600) / 60));
						date.setSeconds(time % 60);

						return date;
					},
					formatDate: function(date, format) {
						if (format === 'U') {
							return date.getHours() * 3600 + date.getMinutes() * 60 + date.getSeconds();
						}

						return dateFormatter.formatDate(date, format);
					},
					onOpen: function(selectedDates, dateStr, instance) {
						$(instance.altInput).prop('readonly', true);
					},
					onClose: function(selectedDates, dateStr, instance) {
						$(instance.altInput).prop('readonly', false);
						$(instance.altInput).blur();
					}
				};

			if (field.data('display-format')) {
				settings['altFormat'] = field.data('display-format');
			}

			if (settings['altFormat'].indexOf('a') === -1 && settings['altFormat'].indexOf('A') === -1) {
				settings['time_24hr'] = true;
			}

			field.flatpickr(settings);
		});

		// File upload
		hivepress.getComponent('file-upload').each(function() {
			var field = $(this),
				container = field.parents('[data-model]:first'),
				submitButton = field.closest('form').find(':submit'),
				selectLabel = field.closest('label'),
				selectButton = selectLabel.find('button').first(),
				messageContainer = selectLabel.parent().find(hivepress.getSelector('messages')).first(),
				responseContainer = selectLabel.parent().children('div').first();

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

					if (submitButton.length) {
						submitButton.prop('disabled', true);
						submitButton.attr('data-state', 'loading');
					}

					messageContainer.hide().html('');
				},
				stop: function() {
					field.prop('disabled', false);

					selectButton.prop('disabled', false);
					selectButton.attr('data-state', '');

					if (submitButton.length) {
						submitButton.prop('disabled', false);
						submitButton.attr('data-state', '');
					}
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
