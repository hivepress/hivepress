var hivepress = {

	/**
	 * Gets component selector.
	 */
	getSelector: function (name) {
		return '[data-component="' + name + '"]';
	},

	/**
	 * Gets component object.
	 */
	getComponent: function (name) {
		return jQuery(this.getSelector(name));
	},
};

(function ($) {
	'use strict';

	hivepress.initUI = function (container = null) {
		if (container === null) {
			container = $('body');
		}

		// Link
		container.find(hivepress.getSelector('link')).on('click', function (e) {
			var url = $(this).data('url');

			if (url.indexOf('#') !== 0) {
				window.location.href = url;
			}

			e.preventDefault();
		});

		// URL
		container.find('input[type=url]').focusout(function () {
			var value = $(this).val();

			if (value && !value.startsWith('https://') && !value.startsWith('http://')) {
				$(this).val('https://' + value);
			}
		});

		// Modal
		container.find(hivepress.getSelector('modal')).each(function () {
			var id = $(this).attr('id'),
				url = '#' + id;

			if (id) {
				$('a[href="' + url + '"], button[data-url="' + url + '"]').on('click', function (e) {
					$.fancybox.close();
					$.fancybox.open({
						src: url,
						touch: false,
					});

					e.preventDefault();
				});
			}
		});

		// Number
		container.find(hivepress.getSelector('number')).each(function () {
			var field = $(this),
				disallowedKeys = ['+', 'e'];

			field.on('keypress paste', function (e) {
				if (e.type === 'paste') {
					var text = (e.originalEvent.clipboardData || window.clipboardData).getData('text');

					for (var i = 0; i < text.length; i++) {
						if (disallowedKeys.includes(text[i])) {
							e.preventDefault();

							return;
						}
					}
				} else if (disallowedKeys.includes(e.key)) {
					e.preventDefault();
				}
			});
		});

		// Repeater
		container.find(hivepress.getSelector('repeater')).each(function () {
			var container = $(this),
				itemContainer = container.find('tbody');

			itemContainer.find(':input[required]').removeAttr('required');

			var firstItem = container.find('tr:first'),
				sampleItem = firstItem.clone();

			itemContainer.sortable({
				handle: '[data-sort]',
			});

			if (firstItem.length) {
				container.find('[data-add]').on('click', function () {
					var newItem = sampleItem.clone(),
						index = Math.random().toString(36).slice(2);

					if (index) {
						newItem.find(':input').each(function () {
							var field = $(this),
								name = field.attr('name'),
								pattern = /\[([^\]]+)\]/,
								match = name.match(pattern);

							if (match) {
								field.attr('name', name.replace(match[1], index));
							}

							if (field.attr('type') === 'checkbox') {
								var id = 'a' + Math.random().toString(36).slice(2);

								field.attr('id', id);
								field.closest('label').attr('for', id);
							} else {
								field.val('');
							}
						});

						newItem.appendTo(itemContainer);
					}

					hivepress.initUI(newItem);
				});
			}

			container.on('click', '[data-remove]', function () {
				if (container.find('tr').length > 1) {
					$(this).closest('tr').remove();
				}
			});
		});

		// Select
		container.find(hivepress.getSelector('select')).each(function () {
			var field = $(this),
				settings = {
					width: '100%',
					dropdownAutoWidth: false,
					minimumResultsForSearch: 20,
					templateResult: function (state) {
						if (state.hasOwnProperty('error')) {
							return state.error;
						}

						var template = state.text,
							level = 0;

						if (state.element) {
							level = parseInt($(state.element).data('level'));
							template = $('<div />').css('padding-left', 20 * level + 'px').text(template);
						}

						return template;
					},
				};

			if (field.data('options')) {
				$.extend(settings, field.data('options'));
			}

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
				var template = function (icon) {
					var output = icon.text;

					if (icon.id) {
						output = '<i class="fas fa-fw fa-' + icon.id + '"></i> ' + icon.text;
					}

					return output;
				};

				$.extend(settings, {
					templateResult: template,
					templateSelection: template,
					escapeMarkup: function (output) {
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
						data: function (params) {
							return {
								'search': params.term,
								'context': 'list',
								'parent_value': field.data('parent-value'),
								'_wpnonce': hivepressCoreData.apiNonce,
							};
						},
						processResults: function (response) {
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
					var parentField = field.closest('form').find(':input[name="' + field.data('parent') + '"]');

					if (parentField.length) {
						parentField.on('change', function () {
							field.data('parent-value', $(this).val());
							field.val(null).trigger('change');
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

			if (field.data('multistep')) {
				var options = [];

				field.find('option').each(function () {
					var option = $(this);

					options.push({
						id: parseInt(option.val()),
						text: option.text(),
						parent: parseInt(option.data('parent')),
					});
				});

				var currentID = parseInt(field.val()),
					currentOption = options.find(function (option) {
						return option.id === currentID;
					});

				if (currentOption && currentOption.parent) {
					var currentOptions = options.filter(function (option) {
						return option.id === currentOption.parent || option.parent === currentOption.parent;
					});

					if (currentOptions.length > 1) {
						currentOptions[0] = $.extend({}, currentOptions[0], {
							id: currentOptions[0].parent,
							text: '← ' + currentOptions[0].text,
						});

						field.html('').select2($.extend({}, settings, { data: currentOptions }));

						field.val(currentID).trigger('change');
					}
				} else {
					field.find('option[data-level]').remove();
				}
			}

			field.on('select2:select', function () {
				var field = $(this);

				if (field.data('multistep')) {
					var currentID = parseInt(field.val()),
						currentOptions = options.filter(function (option) {
							return option.id === currentID || option.parent === currentID;
						});

					if (!currentID || currentOptions.length > 1) {
						if (!currentID) {
							currentOptions = options.filter(function (option) {
								return !option.parent;
							});
						} else {
							currentOptions[0] = $.extend({}, currentOptions[0], {
								id: currentOptions[0].parent,
								text: '← ' + currentOptions[0].text,
							});
						}

						field.html('').select2($.extend({}, settings, { data: currentOptions }));

						field.val(null);

						field.select2('open');

						return false;
					}
				}

				if (field.data('render')) {
					var container = field.closest('[data-model]'),
						data = new FormData(field.closest('form').get(0)),
						tinymceSettings = [],
						tinymceIDs = [];

					data.append('_id', container.data('id'));
					data.append('_model', container.data('model'));
					data.delete('_wpnonce');

					container.attr('data-state', 'loading');

					$.ajax({
						url: field.data('render'),
						method: 'POST',
						data: data,
						contentType: false,
						processData: false,
						beforeSend: function (xhr) {
							xhr.setRequestHeader('X-WP-Nonce', hivepressCoreData.apiNonce);

							if (typeof tinyMCE !== 'undefined') {
								$.each(tinymce.editors, function (index, configs) {
									tinymceSettings.push(configs.settings);
									tinymceIDs.push(configs.id);
								});

								$.each(tinymceIDs, function (index, id) {
									tinymce.remove('#' + id);
								});
							}
						},
						complete: function (xhr) {
							var response = xhr.responseJSON;

							if (typeof response !== 'undefined' && response.hasOwnProperty('data') && response.data.hasOwnProperty('html')) {
								var newContainer = $(response.data.html);

								container.replaceWith(newContainer);

								hivepress.initUI(newContainer);

								if (typeof tinyMCE !== 'undefined') {
									$.each(tinymceSettings, function (index, configs) {
										tinymce.init(configs);
									});
								}

								if (typeof grecaptcha !== 'undefined') {
									var captcha = newContainer.find('.g-recaptcha');

									if (captcha.length && captcha.data('sitekey')) {
										grecaptcha.render(captcha.get(0), {
											'sitekey': captcha.data('sitekey'),
										});
									}
								}
							}
						},
					});
				}
			});

			if (!field.data('select2-id')) {
				field.select2(settings);
			}
		});

		// Phone
		container.find(hivepress.getSelector('phone')).each(function () {
			var field = $(this),
				settings = {
					hiddenInput: field.attr('name'),
					preferredCountries: [],
					separateDialCode: true,
					utilsScript: field.data('utils'),
				};

			field.removeAttr('name');

			if (field.data('countries')) {
				settings['onlyCountries'] = field.data('countries');
			}

			if (field.data('country')) {
				settings['initialCountry'] = field.data('country');
			}

			window.intlTelInput(field.get(0), settings);
		});

		// Date
		container.find(hivepress.getSelector('date')).each(function () {
			var field = $(this),
				ranges = field.data('ranges'),
				settings = {
					allowInput: true,
					altInput: true,
					dateFormat: 'Y-m-d',
					altFormat: 'Y-m-d',
					defaultHour: 0,
					disable: [],
					disableMobile: true,
					onReady: function (selectedDates, dateStr, instance) {
						$(instance.altInput).attr('inputmode', 'none');
					},
					onOpen: function (selectedDates, dateStr, instance) {
						$(instance.altInput).prop('readonly', true);

						$(instance.element).find('[data-clear]').show();
					},
					onClose: function (selectedDates, dateStr, instance) {
						$(instance.altInput).prop('readonly', false);
						$(instance.altInput).blur();

						$(instance.element).find('[data-clear]').hide();

						if ($(instance.element).data('reset')) {
							instance.clear();

							$(instance.element).data('reset', false);
						}
					}
				};

			if (field.data('options')) {
				$.extend(settings, field.data('options'));
			}

			if (field.is('div')) {
				settings['wrap'] = true;
				settings['altInputClass'] = '';

				field.find('[data-clear]').on('click', function () {
					field.data('reset', true);
				});
			}

			if (field.data('format')) {
				settings['dateFormat'] = field.data('format');
			}

			if (field.data('display-format')) {
				settings['altFormat'] = field.data('display-format');
			}

			if (field.data('time')) {
				settings['enableTime'] = true;
			}

			if (field.is('[data-offset]')) {
				settings['minDate'] = new Date().fp_incr(field.data('offset'));
			}

			if (field.data('min-date')) {
				settings['minDate'] = field.data('min-date');
			}

			if (field.is('[data-window]')) {
				settings['maxDate'] = new Date().fp_incr(field.data('window'));
			}

			if (field.data('max-date')) {
				settings['maxDate'] = field.data('max-date');
			}

			if (field.data('enabled-dates')) {
				settings['enable'] = field.data('enabled-dates');
			}

			if (field.data('disabled-dates')) {
				settings['disable'] = field.data('disabled-dates');
			}

			if (field.data('disabled-days')) {
				var disabledDays = field.data('disabled-days');

				if (disabledDays.length) {
					function disableDate(date) {
						return disabledDays.indexOf(date.getDay()) !== -1;
					}

					settings['disable'].push(disableDate);
				}
			}

			if (ranges) {
				settings['onDayCreate'] = function (dObj, dStr, fp, dayElem) {
					if (dayElem.className.includes('disabled')) {
						return;
					}

					var time = Math.floor(dayElem.dateObj.getTime() / 1000) - dayElem.dateObj.getTimezoneOffset() * 60;

					$.each(ranges, function (index, range) {
						if (range.start <= time && time < range.end) {
							dayElem.innerHTML += '<span class="flatpickr-day-label">' + range.label + '</span>';
							dayElem.className += ' flatpickr-status';

							if (range.hasOwnProperty('status')) {
								dayElem.className += ' flatpickr-status--' + range.status;
							}

							return false;
						}
					});
				};
			}

			if (field.data('mode')) {
				settings['mode'] = field.data('mode');

				if (field.data('mode') === 'range') {
					var fields = field.parent().find('input[type="hidden"]').not(field),
						minLength = field.data('min-length'),
						maxLength = field.data('max-length');

					function disableDates(selectedDates, instance) {
						if (selectedDates.length !== 1 || !ranges) {
							return;
						}

						var time = Math.floor(selectedDates[0].getTime() / 1000) - selectedDates[0].getTimezoneOffset() * 60,
							prevTime = null,
							nextTime = null;

						$.each(ranges, function (index, range) {
							if (range.hasOwnProperty('status') && range.status === 'error') {
								if (range.start <= time && time < range.end) {
									instance.clear();

									prevTime = null;
									nextTime = null;

									return false;
								} else if (time >= range.end) {
									prevTime = range.end;
								} else if (time < range.start) {
									nextTime = range.start;

									return false;
								}
							}
						});

						if (!prevTime && !nextTime) {
							return;
						}

						$.each(instance.days.children, function (index, dayElem) {
							var dayTime = Math.floor(dayElem.dateObj.getTime() / 1000) - dayElem.dateObj.getTimezoneOffset() * 60;

							if ((prevTime && dayTime < prevTime) || (nextTime && dayTime > nextTime)) {
								dayElem.className += ' flatpickr-disabled';
							}
						});
					}

					$.extend(settings, {
						defaultDate: [fields.eq(0).val(), fields.eq(1).val()],
						errorHandler: function (error) { },
						onChange: function (selectedDates, dateStr, instance) {
							disableDates(selectedDates, instance);

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

								var formattedDates = selectedDates.map(function (date) {
									return hivepress.dateFormatter.formatDate(date, settings['dateFormat']);
								});

								fields.eq(0).val(formattedDates[0]);
								fields.eq(1).val(formattedDates[1]);
							} else {
								fields.eq(0).val('');
								fields.eq(1).val('');
							}
						},
						onMonthChange: function (selectedDates, dateStr, instance) {
							disableDates(selectedDates, instance);
						},
					});
				}
			}

			$.extend(settings, {
				time_24hr: settings['altFormat'].indexOf('a') === -1 && settings['altFormat'].indexOf('A') === -1,
				parseDate: function (date, format) {
					var parsedDate = hivepress.dateFormatter.parseDate(date, format);

					if (format === 'U') {
						parsedDate = new Date(parsedDate.toLocaleString('en-US', {
							timeZone: 'UTC',
						}));
					}

					return parsedDate;
				},
				formatDate: function (date, format) {
					var formattedDate = hivepress.dateFormatter.formatDate(date, format);

					if (format === 'U') {
						formattedDate = parseInt(formattedDate) - date.getTimezoneOffset() * 60;
					}

					return formattedDate;
				},
			});

			field.flatpickr(settings);
		});

		// Time
		container.find(hivepress.getSelector('time')).each(function () {
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
					parseDate: function (date, format) {
						var parsedDate = hivepress.dateFormatter.parseDate(date, format);

						if (format === 'U') {
							parsedDate = new Date(parsedDate.toLocaleString('en-US', {
								timeZone: 'UTC',
							}));
						}

						return parsedDate;
					},
					formatDate: function (date, format) {
						if (format === 'U') {
							return date.getHours() * 3600 + date.getMinutes() * 60 + date.getSeconds();
						}

						return hivepress.dateFormatter.formatDate(date, format);
					},
					onReady: function (selectedDates, dateStr, instance) {
						$(instance.altInput).attr('inputmode', 'none');
					},
					onOpen: function (selectedDates, dateStr, instance) {
						$(instance.altInput).prop('readonly', true);

						$(instance.element).find('[data-clear]').show();
					},
					onClose: function (selectedDates, dateStr, instance) {
						$(instance.altInput).prop('readonly', false);
						$(instance.altInput).blur();

						$(instance.element).find('[data-clear]').hide();

						if ($(instance.element).data('reset')) {
							instance.clear();

							$(instance.element).data('reset', false);
						}
					}
				};

			if (field.data('options')) {
				$.extend(settings, field.data('options'));
			}

			if (field.is('div')) {
				settings['wrap'] = true;
				settings['altInputClass'] = '';

				field.find('[data-clear]').on('click', function () {
					field.data('reset', true);
				});
			}

			if (field.data('display-format')) {
				settings['altFormat'] = field.data('display-format');
			}

			if (settings['altFormat'].indexOf('a') === -1 && settings['altFormat'].indexOf('A') === -1) {
				settings['time_24hr'] = true;
			}

			field.flatpickr(settings);
		});

		// File upload
		container.find(hivepress.getSelector('file-upload')).each(function () {
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
				limitConcurrentUploads: 2,
				formData: {
					'parent_model': container.data('model'),
					'parent_field': field.data('name'),
					'parent': container.data('id'),
					'render': true,
					'_wpnonce': hivepressCoreData.apiNonce,
				},
				start: function () {
					field.prop('disabled', true);

					selectButton.prop('disabled', true);
					selectButton.attr('data-state', 'loading');

					if (submitButton.length) {
						submitButton.prop('disabled', true);
						submitButton.attr('data-state', 'loading');
					}

					messageContainer.hide().html('');
				},
				stop: function () {
					field.prop('disabled', false);

					selectButton.prop('disabled', false);
					selectButton.attr('data-state', '');

					if (submitButton.length) {
						submitButton.prop('disabled', false);
						submitButton.attr('data-state', '');
					}
				},
				always: function (e, data) {
					var response = data.jqXHR.responseJSON;

					if (response.hasOwnProperty('data')) {
						if (field.prop('multiple')) {
							responseContainer.append(response.data.html);
						} else {
							responseContainer.html(response.data.html);
						}
					} else if (response.hasOwnProperty('error')) {
						if (response.error.hasOwnProperty('errors')) {
							$.each(response.error.errors, function (index, error) {
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

		// Sortable
		container.find(hivepress.getSelector('sortable')).each(function () {
			var container = $(this);

			container.sortable({
				stop: function () {
					if (container.children().length > 1) {
						container.children().each(function (index) {
							$.ajax({
								url: $(this).data('url'),
								method: 'POST',
								data: {
									'sort_order': index,
								},
								beforeSend: function (xhr) {
									xhr.setRequestHeader('X-WP-Nonce', hivepressCoreData.apiNonce);
								},
							});
						});
					}
				},
			});
		});

		// Chart
		container.find(hivepress.getSelector('chart')).each(function () {
			var canvas = $(this),
				chart = new Chart(canvas, {
					type: 'line',
					options: {
						scales: {
							y: {
								beginAtZero: true,
								ticks: {
									stepSize: 1,
								},
							},
							x: {
								type: 'time',
								time: {
									tooltipFormat: 'MMM D, YYYY',
									unit: 'week',
									displayFormats: {
										'week': 'MMM D, YYYY',
									},
								},
							},
						},
					},
					data: {
						labels: canvas.data('labels'),
						datasets: canvas.data('datasets'),
					},
				});
		});

		// Interval
		container.find('[data-render]').each(function () {
			var renderSettings = $(this).data('render');

			if (renderSettings && renderSettings.hasOwnProperty('interval')) {
				renderSettings = $.extend({ type: 'replace' }, renderSettings);

				var renderInterval = setInterval(function () {
					var url = new URL(renderSettings.url),
						container = $('[data-block=' + renderSettings.block + ']');

					if (!container.length) {
						clearInterval(renderInterval);

						return;
					}

					container.attr('data-state', 'loading');

					url.searchParams.set('_render', true);

					$.ajax({
						url: url,
						method: 'GET',
						contentType: false,
						processData: false,
						beforeSend: function (xhr) {
							if ($('body').hasClass('logged-in')) {
								xhr.setRequestHeader('X-WP-Nonce', hivepressCoreData.apiNonce);
							}
						},
						complete: function (xhr) {
							var response = xhr.responseJSON;

							if (typeof response !== 'undefined' && response.hasOwnProperty('data') && response.data.hasOwnProperty('html')) {
								var newContainer = $(response.data.html);

								if ('append' === renderSettings.type) {
									container.attr('data-state', '');

									container.append(newContainer);
								} else {
									container.replaceWith(newContainer);
								}

								hivepress.initUI(newContainer);
							}
						},
					});
				}, renderSettings.interval * 1000);
			}
		});

		// Form
		var forms = container.find(hivepress.getSelector('form'));

		if (container.is('form')) {
			forms = container;
		}

		forms.each(function () {
			var form = $(this),
				captcha = form.find('.g-recaptcha'),
				captchaId = $('.g-recaptcha').index(captcha.get(0)),
				submitButton = form.find(':submit'),
				renderSettings = form.data('render');

			if (form.data('autosubmit') === true) {
				form.on('change', function () {
					form.submit();
				});
			}

			if (renderSettings) {
				renderSettings = $.extend({ event: 'change', type: 'replace' }, renderSettings);

				if ('change' === renderSettings.event) {
					form.on('change', function () {
						var container = $('[data-block=' + renderSettings.block + ']'),
							data = new FormData(form.get(0)),
							request = form.data('renderRequest');

						if (!container.length) {
							return;
						}

						if (container.attr('data-state') === 'loading') {
							request.abort();
						}

						container.attr('data-state', 'loading');

						data.append('_render', true);
						data.delete('_wpnonce');

						form.data('renderRequest', $.ajax({
							url: renderSettings.url,
							method: 'POST',
							data: data,
							contentType: false,
							processData: false,
							beforeSend: function (xhr) {
								if ($('body').hasClass('logged-in')) {
									xhr.setRequestHeader('X-WP-Nonce', hivepressCoreData.apiNonce);
								}
							},
							complete: function (xhr) {
								var response = xhr.responseJSON;

								if (typeof response !== 'undefined' && response.hasOwnProperty('data') && response.data.hasOwnProperty('html')) {
									var newContainer = $(response.data.html);

									if ('append' === renderSettings.type) {
										container.attr('data-state', '');

										container.append(newContainer);
									} else {
										container.replaceWith(newContainer);
									}

									hivepress.initUI(newContainer);
								}
							},
						}));
					});
				}
			}

			form.on('submit', function () {
				submitButton.prop('disabled', true);
				submitButton.attr('data-state', 'loading');
			});

			if (form.data('action')) {
				var messageContainer = form.find(hivepress.getSelector('messages')).first(),
					messageClass = messageContainer.attr('class').split(' ')[0];

				form.on('submit', function (e) {
					var formData = new FormData(form.get(0));

					messageContainer.hide().html('').removeClass(messageClass + '--success ' + messageClass + '--error');

					if (typeof tinyMCE !== 'undefined') {
						tinyMCE.triggerSave();
					}

					if (renderSettings && renderSettings.event === 'submit') {
						var renderContainer = $('[data-block=' + renderSettings.block + ']');

						if (renderContainer.length) {
							renderContainer.attr('data-state', 'loading');

							formData.append('_render', true);
						}
					}

					$.ajax({
						url: form.data('action'),
						method: 'POST',
						data: formData,
						contentType: false,
						processData: false,
						beforeSend: function (xhr) {
							var method = form.data('method') ? form.data('method') : form.attr('method');

							if (method !== 'POST') {
								xhr.setRequestHeader('X-HTTP-Method-Override', method);
							}

							if ($('body').hasClass('logged-in') || $('body').hasClass('wp-admin')) {
								xhr.setRequestHeader('X-WP-Nonce', hivepressCoreData.apiNonce);
							}
						},
						complete: function (xhr) {
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

									form.find(hivepress.getSelector('file-upload')).each(function () {
										var field = $(this),
											selectLabel = field.closest('label'),
											responseContainer = selectLabel.parent().children('div').first();

										responseContainer.html('');
									});
								}
							} else if (response.hasOwnProperty('error')) {
								if (response.error.hasOwnProperty('errors')) {
									$.each(response.error.errors, function (index, error) {
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

							if (renderSettings && renderSettings.event === 'submit' && renderContainer.length) {
								renderContainer.attr('data-state', '');

								if (typeof response !== 'undefined' && response.hasOwnProperty('data') && response.data.hasOwnProperty('html')) {
									var newContainer = $(response.data.html);

									if ('append' === renderSettings.type) {
										renderContainer.append(newContainer);
									} else {
										renderContainer.replaceWith(newContainer);
									}

									hivepress.initUI(newContainer);
								}
							}
						},
					});

					e.preventDefault();
				});
			}

			form.find('input[readonly], textarea[readonly]').on('click', function () {
				this.select();

				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(this.value).catch(() => {
						document.execCommand('copy');
					});
				} else {
					document.execCommand('copy');
				}
			});
		});

		// Field
		container.find(hivepress.getSelector('field')).each(function () {
			var field = $(this);

			if (field.data('parent')) {
				var parentField = field.closest('form').find(':input[name="' + field.data('parent') + '"]');

				if (field.parent().is('td')) {
					field = field.closest('tr');
				} else if (field.is(':input')) {
					field = field.closest('div');
				}

				if (parentField.length) {
					if (!parentField.val() || (parentField.is(':checkbox, :radio') && !parentField.prop('checked'))) {
						field.hide();
					}

					parentField.on('change', function () {
						if (!$(this).val() || ($(this).is(':checkbox, :radio') && !$(this).prop('checked'))) {
							field.hide();
						} else {
							field.show();
						}
					});
				}
			}
		});

		$(document).trigger('hivepress:init', [container]);
	}

	$(document).ready(function () {

		// Date formatter
		hivepress.dateFormatter = new DateFormatter();

		// Date picker
		var language = hivepressCoreData.language;

		if (language === 'el') {
			language = 'gr';
		}

		if (flatpickr.l10ns.hasOwnProperty(language)) {
			var dateSettings = flatpickr.l10ns[language];

			flatpickr.localize(dateSettings);

			hivepress.dateFormatter = new DateFormatter({
				dateSettings: {
					days: dateSettings.weekdays.longhand,
					daysShort: dateSettings.weekdays.shorthand,
					months: dateSettings.months.longhand,
					monthsShort: dateSettings.months.shorthand,
					meridiem: dateSettings.hasOwnProperty('amPM') ? dateSettings.amPM : ['AM', 'PM'],
				},
			});
		}

		// File delete
		$(document).on('click tap touchstart', hivepress.getSelector('file-delete'), function (e) {
			var container = $(this).parent();

			$.ajax({
				url: $(this).data('url'),
				method: 'DELETE',
				beforeSend: function (xhr) {
					xhr.setRequestHeader('X-WP-Nonce', hivepressCoreData.apiNonce);
				},
			});

			container.remove();

			e.preventDefault();
		});

		// Initialize UI
		hivepress.initUI();
	});
})(jQuery);
