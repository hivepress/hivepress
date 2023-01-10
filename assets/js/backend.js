(function($) {
	'use strict';

	$(document).ready(function() {

		// Template
		if (typeof wp !== 'undefined' && wp.hasOwnProperty('data')) {
			var isSavedPost = false;

			wp.data.subscribe(function() {
				var editor = wp.data.select('core/editor');

				if (editor && 'hp_template' === editor.getCurrentPostType() && editor.isSavingPost() && !editor.isAutosavingPost() && editor.didPostSaveRequestSucceed()) {
					var field = $('select[name=hp_template]');

					if (field.length && field.val() !== editor.getEditedPostSlug()) {
						var interval = setInterval(function() {
							if (!editor.isSavingPost() && !isSavedPost) {
								window.location.reload();

								isSavedPost = true;
								clearInterval(interval);
							}
						}, 500);
					}
				}
			});
		}

		// Notice
		hivepress.getComponent('notice').each(function() {
			var notice = $(this);

			notice.find('button, .button').on('click', function(e) {
				var button = $(this),
					option = notice.data('option');

				if (button.is('button')) {
					option = null;
				} else if (!option) {
					return;
				}

				$.ajax({
					url: notice.data('url'),
					method: 'POST',
					data: {
						'dismissed': true,
						'option': option,
					},
					beforeSend: function(xhr) {
						xhr.setRequestHeader('X-WP-Nonce', hivepressCoreData.apiNonce);
					},
					complete: function(xhr) {
						if (!button.is('button')) {
							notice.slideUp(100);
						}
					},
				});

				if (!button.is('button')) {
					e.preventDefault();
				}
			});
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

		// Plugin deactivate
		$('a#deactivate-hivepress').on('click', function(e) {
			$.fancybox.close();
			$.fancybox.open({
				src: '#hivepress_deactivate_modal',
				touch: false,
			});

			e.preventDefault();
		});
	});
})(jQuery);
