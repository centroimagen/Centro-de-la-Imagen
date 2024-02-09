(function ($) {
    'use strict';
    /* Custom Meta Box Icon*/
    var InitColor = function (el, $optionsWrapper) {
        el.minicolors({
            opacity: false,
            show: function () {
                $optionsWrapper.sortable('disable');
            },
            hide: function () {
                $optionsWrapper.sortable('enable');
            },
            change: function (hex, opacity) {
                $(this).closest('.ptb_cmb_option').find('.ptb_custom_lightbox').css('color', hex);
            }
        });
    };
    $(document).on('ptb_add_metabox_icon', function (e) {
        return {};
    }).on('ptb_post_cmb_icon_body_handle', function (e) {
        var $optionsWrapper = e.cmbItemBody.find('.ptb_cmb_options_wrapper');

        if ($optionsWrapper.length === 0) {
            return false;
        }

        var $option = $optionsWrapper.children().first().clone();

        $optionsWrapper.sortable({
            placeholder: "ui-state-highlight",
			handle : '.ptb_cmb_option_sort'
        });
        InitColor(e.cmbItemBody.find('.ptb_color_picker'), $optionsWrapper);
        e.cmbItemBody.find('.ptb_cmb_option_add')
                .click(
                        {
                            wrapper: $optionsWrapper
                        },
                function (event) {
                    var $newOption = $option.clone();
                    $newOption.appendTo($optionsWrapper).hide().show('blind', 500);

                    var $cl = $newOption.find('.ptb_extra_input_icon').val(),
						$temp = $cl.split(' ');
					$cl = $temp.length <= 1 ? 'fa fa-' + $cl : $cl;
                    $newOption.find('[name^="' + e.id + '"]').val('');
                    $newOption.find('.ptb_extra_input_icon_holder').val('');
                    $newOption.find('.ptb_post_cmb_image').removeClass($cl);
                    $newOption.find('.' + e.id + '_remove').click({item: $newOption}, removeOption);
                    InitColor(e.cmbItemBody.find('.ptb_color_picker'), $optionsWrapper);
                    event.data.wrapper.sortable("refresh");
                });

        $optionsWrapper.children().each(function () {
            var $self = $(this);
            $self.find('.' + e.id + '_remove').click({item: $self}, removeOption);
        });

        // remove option
        function removeOption(e) {
            e.preventDefault();
            e.data.item.hide('blind', 500, function () {
                $(this).remove();
            });
        }
    }).on( 'click','.ptb-icons-list a',function (e) {
        e.preventDefault();
        if ($('#ptb_row_wrapper').length===0) {
            var $val = $(this).find('i').attr('class'),
				$cl = $val.split(' '),
                $current = $('.ptb_post_cmb_item_icon .ptb_current_ajax'),
                $icon = $current.closest('li').find('.ptb_extra_input_icon'),
                $holder = $current.closest('li').find('.ptb_extra_input_icon_holder');

			if ( $cl.length > 1 ) $cl = $.trim($cl[1]);
			else $cl = $.trim($cl[0]);

            if ($icon.val()) {
				if ( $icon.val().split(' ').length > 1 ) $current.removeClass($icon.val());
                else $current.removeClass( 'fa-' + $icon.val());
            }

            $icon.val($val);
            $holder.val($cl.replace('fa-', ''));
            $current.addClass($val);
            $('.ptb_close_lightbox').trigger('click');
        }
    });
}(jQuery));

