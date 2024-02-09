(function ($) {
    'use strict';
    /* Custom Meta Box Date*/
    $(document).on('ptb_add_metabox_event_date', function (e) {
        return {
            'showrange': false
        };
    }).on('ptb_metabox_create_event_date', function (e) {
        if (e.options.showrange) {
            e.container.find('input[name="' + e.id + '_showrange"]').prop('checked', true);
        }
        if (e.options.dateformat) {
            e.container.find('input[name="' + e.id + '_dateformat"]').val( e.options.dateformat );
        }
        if (e.options.timeformat) {
            e.container.find('input[name="' + e.id + '_timeformat"]').val( e.options.timeformat );
        }
    }).on('ptb_metabox_save_event_date', function (e) {
        e.options.showrange = $('input[name="' + e.id + '_showrange"]:checked').val();
		e.options.dateformat = $('input[name="' + e.id + '_dateformat"]').val();
		e.options.timeformat = $('input[name="' + e.id + '_timeformat"]').val();
    }).on('ptb_post_cmb_event_date_body_handle', function (e) {

        var $self = $('#' + e.id + '_start').length > 0 ? $('#' + e.id + '_start') : $('#ptb_extra_' + e.id);
        var $defaultargs = {
            showOn: 'focus',
            controlType: 'select',
            oneLine: true,
            showButtonPanel: true,  
            showHour:1,
            showMinute:1,
            timeOnly:false,
            showTimepicker:1,
            buttonText: false,
            dateFormat: $self.data( 'dateformat' ),
            timeFormat: $self.data( 'timeformat' ),
            stepMinute: 5,
            separator: '@',
            minInterval:0,
			changeMonth: true,
			changeYear: true,
			currentText: ptb.i18n.currentText,
			closeText: ptb.i18n.closeText,
			timeText: ptb.i18n.timeText,
            beforeShow: function () {
                $('#ui-datepicker-div').addClass('ptb_extra_datepicker');
            }
        };
        if ($('#' + e.id + '_start').length === 0) {
            ( $.fn.themifyDatetimepicker 
				? $.fn.themifyDatetimepicker 
				: $.fn.datetimepicker ).call( $self, $defaultargs );
        }
        else {
			( $.themifyTimepicker ? $.themifyTimepicker : $.timepicker ).datetimeRange(
				$self,
				$('#' + e.id + '_end'),
				$defaultargs
			);
        }

    });

}(jQuery));
 