(function ($) {
	'use strict';

	$(document).ready(function () {

		// Toggle
		hivepress.getComponent('toggle').each(function () {
			var button = $(this);

			button.on('click', function (e) {
				var caption = button.attr('data-caption'),
					iconClass = button.attr('data-icon'),
					icon = button.find('i'),
					label = button.find('span');

				button.attr('data-icon', icon.attr('class').split(' fa-')[1]);

				icon.attr('class', function (i, c) {
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
					beforeSend: function (xhr) {
						if ($('body').hasClass('logged-in')) {
							xhr.setRequestHeader('X-WP-Nonce', hivepressCoreData.apiNonce);
						}
					},
				});

				e.preventDefault();
			});
		});

		// Range slider
		hivepress.getComponent('range-slider').each(function () {
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
				step: Number(minField.attr('step')),
				values: [Number(minField.val()), Number(maxField.val())],
				slide: function (e, ui) {
					minField.val(ui.values[0]);
					maxField.val(ui.values[1]);
				},
			});

			slider.wrap('<div />');

			fields.on('change', function () {
				if (!minField.val()) {
					minField.val(minField.attr('min'));
				}

				if (!maxField.val()) {
					maxField.val(maxField.attr('max'));
				}

				slider.slider('values', [Number(minField.val()), Number(maxField.val())]);
			});
		});

		// Carousel slider
		function initSliders(container) {
			container.find(hivepress.getSelector('carousel-slider')).each(function () {
				var container = $(this),
					images = container.find('img, video'),
					url = container.data('url'),
					isPreview = container.data('preview') !== false;

				if (images.length && !url) {
					var zoomAttribute = images.first().is('[data-zoom]') ? 'zoom' : 'src';

					if (images.first().data(zoomAttribute)) {
						var imageURLs = [];

						images.each(function () {
							imageURLs.push({
								src: $(this).data(zoomAttribute),
							});
						});

						container.on('click', 'img, video', function (e) {
							var index = container.find('img, video').index($(this).get(0));

							if (index < imageURLs.length) {
								$.fancybox.open(imageURLs, {
									loop: true,
									buttons: ['close'],
								}, index);
							}

							e.preventDefault();
						});
					}
				}

				if (images.length > 1) {
					container.imagesLoaded(function () {
						var containerClass = container.attr('class').split(' ')[0],
							slider = images.wrap('<div />').parent().wrapAll('<div />').parent(),
							settings = {
								slidesToShow: 1,
								slidesToScroll: 1,
								infinite: false,
								adaptiveHeight: true,
							};

						container.html('');

						if (url) {
							slider.find('img, video').wrap('<a href="' + url + '" />');
						}

						slider.appendTo(container);

						if (isPreview) {
							var carousel = slider.clone();

							carousel.find('video').removeAttr('controls');
							carousel.appendTo(container);

							$.extend(settings, {
								asNavFor: carousel,
								arrows: false,
							});
						} else {
							$.extend(settings, {
								prevArrow: '<div class="slick-arrow slick-prev"><i class="hp-icon fas fa-chevron-left"></i></div>',
								nextArrow: '<div class="slick-arrow slick-next"><i class="hp-icon fas fa-chevron-right"></i></div>',
							});
						}

						slider.addClass(containerClass + '-slider').slick(settings);

						var observer = new MutationObserver(function () {
							slider.slick('resize');
						}).observe(slider.get(0), {
							subtree: true,
							attributeFilter: ['src'],
						});

						if (isPreview) {
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
				}
			});
		}

		initSliders($('body'));

		var observer = new MutationObserver(function (mutations) {
			for (const { addedNodes } of mutations) {
				for (const node of addedNodes) {
					if (node.tagName === 'DIV' && node.classList.contains('slick-cloned')) {
						initSliders($(node));
					}
				}
			}
		}).observe(document, {
			subtree: true,
			childList: true,
		});

		// Buttons
		$(window).on('pageshow', function (e) {
			if (e.originalEvent.persisted) {
				var buttons = $('input[type=submit], button[type=submit]');

				buttons.prop('disabled', false);
				buttons.attr('data-state', '');
			}
		});
	});

	$('body').imagesLoaded(function () {

		// Sticky
		hivepress.getComponent('sticky').each(function () {
			var container = $(this),
				spacing = 32;

			if ($('#wpadminbar').length) {
				spacing = spacing + $('#wpadminbar').height();
			}

			if (container.height() === 0) {
				container.hide();
			} else if ($(window).width() >= 768) {
				container.wrapInner('<div />');

				var sidebar = container.children('div').stickySidebar({
					topSpacing: spacing,
					bottomSpacing: spacing,
				});

				var observer = new ResizeObserver(function () {
					sidebar.stickySidebar('updateSticky');
				}).observe(container.get(0));
			}
		});
	});
})(jQuery);
