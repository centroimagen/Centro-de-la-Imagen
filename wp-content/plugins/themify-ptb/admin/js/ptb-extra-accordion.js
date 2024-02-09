(function ($) {
    'use strict';
    /* Custom Meta Box Accordion*/

    $(document).on('ptb_add_metabox_accordion', function (e) {
        return {};
    }).on('ptb_post_cmb_accordion_body_handle', function (e) {
        var $optionsWrapper = e.cmbItemBody.find('.ptb_cmb_options_wrapper');

        if ($optionsWrapper.length === 0) {
            return false;
        }

        var $option = $optionsWrapper.children().first().clone();

        $optionsWrapper.sortable({
            placeholder: "ui-state-highlight",
			handle : '.ptb_cmb_option_sort'
        });

        e.cmbItemBody.find('.ptb_cmb_option_add')
            .click(
                {
                    wrapper: $optionsWrapper
                },
                function (event) {
                    var $newOption = $option.clone();
                    $newOption.appendTo($optionsWrapper).hide().show('blind', 500);
                    $newOption.find('[name^="' + e.id + '"]').val('');
                    $newOption.find('.' + e.id + '_remove').click({item: $newOption}, removeOption);
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

    });

}(jQuery));