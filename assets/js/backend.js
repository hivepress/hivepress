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

	// Button
	$(document).on('click', hivepress.getSelector('button'), function(e) {
		var button = $(this),
			type = [];

		if (typeof button.data('type') !== 'undefined') {
			type = button.data('type').split(' ');
		}

		// Remove button
		if (type.includes('remove')) {
			button.parent().remove();
		}

		// File select
		if (type.includes('file-select')) {
			var container = button.parent().children('div').clone(),
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
		}

		e.preventDefault();
	});
})(jQuery);
