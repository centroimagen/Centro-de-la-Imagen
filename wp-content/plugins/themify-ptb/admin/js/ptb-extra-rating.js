(function ($) {
    'use strict';
    /* Custom Meta Box Rating*/

    $(document).on('ptb_add_metabox_rating', function (e) {
        return {'readonly': 1,
            'stars_count': 5,
			'schema':''
        };
    }).on('ptb_metabox_create_rating', function (e) {
        e.container.find('input[name="' + e.id + '_readonly"][value="' + e.options.readonly + '"]').prop('checked', true);
        if(e.options.schema){
            e.container.find('input[name="' + e.id + '_schema"]').prop('checked', true);
        }
        e.container.find('#' + e.id + '_stars_count option[value="' + e.options.stars_count + '"]').prop('selected', true);
    }).on('ptb_metabox_save_rating', function (e) {
        e.options.readonly = $('input[name="' + e.id + '_readonly"]:checked').val();
		e.options.schema = $('input[name="' + e.id + '_schema"]:checked').val();
        e.options.stars_count = $('#' + e.id + '_stars_count option:selected').val();
        e.options.can_be_empty = 1;
    });
}(jQuery));
 