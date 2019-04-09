(function($) {
	'use strict';

	/**
	 * Gets jQuery component.
	 */
	function getComponent(name) {
		return $('[data-component=' + name + ']');
	}

	$(document).ready(function() {

		// File select
		getComponent('file-select').on('click', function(e) {
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
		getComponent('file-remove').on('click', function(e) {
			$(this).parent().remove();

			e.preventDefault();
		});
	});
})(jQuery);
