(function($) {
	'use strict';

	var TodoModel = function() {
		this.renderBlock = function(element) {
			var object = $(element),
				container = $('.todo');

			if (object.is('form')) {
				object.find(':submit').prop('disabled', true).attr('data-state', 'loading');
			}

			container.attr('data-state', 'loading');

			$.ajax({
				url: 'http://localhost/hivepress/wp-json/hivepress/v1/templates/listings-page/blocks/listing-search-results?render=1',
				method: 'GET',
				data: object.serializeJSON(),
				beforeSend: function(xhr) {
					xhr.setRequestHeader('X-WP-Nonce', hpCoreFrontendData.apiNonce);
				},
				complete: function(xhr) {
					if (object.is('form')) {
						object.find(':submit').prop('disabled', false).removeAttr('data-state');
					}

					container.removeAttr('data-state');

					console.log(xhr.responseText);
					window.history.replaceState({}, null, object.attr('action') + '?' + $.param(object.serializeJSON()));
					container.replaceWith(xhr.responseJSON.data.html);
				},
			});
		};
	};

	ko.applyBindings(new TodoModel());
})(jQuery);
