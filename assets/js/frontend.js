(function($) {
	'use strict';

	var TodoModel = function() {
		this.renderBlock = function() {


			$.ajax({
				url: 'http://localhost/hivepress/wp-json/hivepress/v1/templates/listings-page/blocks/listing-search-results?render=1',
				method: 'GET',
				// data: {
				// 	'order': index,
				// },
				beforeSend: function(xhr) {
					xhr.setRequestHeader('X-WP-Nonce', hpCoreFrontendData.apiNonce);
				},
				complete: function(xhr) {
					console.log(xhr.responseText);
					$('.todo').replaceWith(xhr.responseJSON.data.html);
				},
			});
	    };
	};

	ko.applyBindings(new TodoModel());
})(jQuery);
