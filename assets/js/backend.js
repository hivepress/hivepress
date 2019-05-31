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
	});
})(jQuery);
