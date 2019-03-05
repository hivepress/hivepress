(function($) {
	'use strict';

	var TodoModel = function() {
		this.renderBlock = function(element) {
			var object = $(element);

			if (object.is('form')) {
				object.find(':submit').prop('disabled', true).attr('data-state', 'loading');
			}

			$('.todo').attr('data-state', 'loading');

			$.ajax({
				url: 'http://localhost/hivepress/wp-json/hivepress/v1/templates/listings-page/blocks/listing-search-results?render=1',
				method: 'GET',
				beforeSend: function(xhr) {
					xhr.setRequestHeader('X-WP-Nonce', hpCoreFrontendData.apiNonce);
				},
				complete: function(xhr) {
					if (object.is('form')) {
						object.find(':submit').prop('disabled', false).removeAttr('data-state');
					}

					console.log(xhr.responseText);
					$('.todo').replaceWith(xhr.responseJSON.data.html);
				},
			});
		};
	};

	ko.applyBindings(new TodoModel());
})(jQuery);
