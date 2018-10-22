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

  // Link
  $(document).on('click', hivepress.getSelector('link'), function(e) {
    var link = $(this),
      type = [];

    if (typeof link.data('type') !== 'undefined') {
      type = link.data('type').split(' ');
    }

    if (type.includes('remove')) {
      link.parent().remove();
    }

    e.preventDefault();
  });

  // Field
  hivepress.getObject('field').each(function() {
    var field = $(this);

    if (field.data('parent')) {
      var parentField = null,
        parentValue = null;

      if (typeof field.data('parent') === 'object') {
        $.each(field.data('parent'), function(key, value) {
          parentField = $('[name="' + key + '"]');
          parentValue = value;
        });
      } else {
        parentField = $('[name="' + field.data('parent') + '"]');
      }

      if (parentField !== null && parentField.length) {
        if (!parentField.is(':checked') && parentField.val() !== parentValue && $.inArray(parentField.val(), parentValue) === -1) {
          field.closest('tr').hide();
        }

        parentField.on('change', function() {
          if (parentField.is(':checked') || parentField.val() === parentValue || $.inArray(parentField.val(), parentValue) !== -1) {
            field.closest('tr').show();
          } else {
            field.closest('tr').hide();
          }
        });
      }
    }
  });

  // File select
  hivepress.getObject('file-select').each(function() {
    var button = $(this),
      container = button.parent().children('div').clone();

    button.on('click', function(e) {
      var frame = wp.media({
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
  });
})(jQuery);