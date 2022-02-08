(function($) {
	'use strict';

	$(document).ready(function() {

		// Notice
		hivepress.getComponent('notice').each(function() {
			var notice = $(this);

			notice.find('button').on('click', function() {
				$.ajax({
					url: notice.data('url'),
					method: 'POST',
					data: {
						'dismissed': true,
					},
					beforeSend: function(xhr) {
						xhr.setRequestHeader('X-WP-Nonce', hivepressCoreData.apiNonce);
					},
				});
			});
		});

		// Field
		hivepress.getComponent('field').each(function() {
			var field = $(this);

			if (field.data('parent')) {
				var parentField = $(':input[name="' + field.data('parent') + '"]');

				if (field.parent().is('td')) {
					field = field.closest('tr');
				}

				if (parentField.length) {
					if (!parentField.val() || (parentField.is(':checkbox') && !parentField.prop('checked'))) {
						field.hide();
					}

					parentField.on('change', function() {
						if (!parentField.val() || (parentField.is(':checkbox') && !parentField.prop('checked'))) {
							field.hide();
						} else {
							field.show();
						}
					});
				}
			}
		});

		// File select
		hivepress.getComponent('file-select').on('click', function(e) {
			var button = $(this),
				container = button.parent().children('div').clone(),
				frame = wp.media({
					title: button.text(),
					button: {
						text: button.text(),
					},
					library: {
						type: ['image'],
					},
					multiple: false,
				});

			frame.on('select', function() {
				var attachment = frame.state().get('selection').first().toJSON();

				container.find('img').remove();
				$('<img />').attr('src', attachment.url).prependTo(container);

				button.parent().children('div').remove();
				container.prependTo(button.parent());

				container.find('input[type="hidden"]').val(attachment.id);
			});

			frame.open();

			e.preventDefault();
		});

		// File remove
		$(document).on('click', hivepress.getSelector('file-remove'), function(e) {
			var container = $(this).parent();

			container.find('img').remove();
			container.find('input[type="hidden"]').val('');

			e.preventDefault();
		});

		// Read-only field
		hivepress.getComponent('form').find('input[readonly], textarea[readonly]').on('click', function() {
			this.select();
			document.execCommand('copy');
		});

		// Detect modal.
        $('form').each(function() {
            $(this).find('[data-component="modal"]').detach().insertAfter(this);
        });
	});
})(jQuery);
