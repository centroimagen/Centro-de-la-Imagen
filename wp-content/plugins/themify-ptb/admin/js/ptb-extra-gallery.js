(function ($) {
    'use strict';
    /* Custom Meta Box Gallery*/
    
    function selectImage(attachment,$id,$current){
         var $parent=$current?$current:$('#'+$id+'_options_wrapper li.ptb_cmb_option').last();
         $parent.find('input[name^="' + $id + '[url]"]').val(attachment.url);
         var $new = $('#auto_draft').length > 0,
             $item = $parent.find('.ptb_post_cmb_image');
         var $title = $parent.find('input[name^="' + $id + '[title]"]');
         if ($new || !$.trim($title.val())) {
             $title.val(attachment.title);
         }
         var $desc = $parent.find('textarea');
         if ($new || !$.trim($desc.val())) {
             $desc.val(attachment.caption);
         }
        $item.css('background-image', 'url(' + attachment.url + ')');
     }

    $(document).on('ptb_add_metabox_gallery', function (e) {
        return {};
    }).on('ptb_post_cmb_gallery_body_handle', function (e) {
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
			.on( 'click', {
					wrapper: $optionsWrapper
				},
			function (event) {
				var $newOption = $option.clone();
				$newOption.appendTo($optionsWrapper).hide().show('blind', 500);
				$newOption.find('[name^="' + e.id + '"]').val('');
				$newOption.find('.ptb_post_cmb_image').removeAttr('style', '');
				$newOption.find('.' + e.id + '_remove').on( 'click', {item: $newOption}, removeOption);

				event.data.wrapper.sortable("refresh");
			});

        $optionsWrapper.children().each(function () {
            var $self = $(this);
            $self.find('.' + e.id + '_remove').on( 'click', {item: $self}, removeOption);
        });

        // remove option
        function removeOption(e) {
            e.preventDefault();
            e.data.item.hide('blind', 500, function () {
                $(this).remove();
            });
        }
        
     
        
        $('.ptb_post_cmb_item_gallery').on('click','.' + e.id + '_option_wrapper .ptb_post_cmb_image',  function (event) {
            event.preventDefault();
            var $item = $(this),
                $parent = $item.closest('li');
            // Create the media frame.
            wp.media.frames.file_frame = wp.media({
                title: wp.media.view.l10n.editGalleryTitle,
                button: {
                    text: $(this).data('uploader_button_text')
                },
                frame:      'post',
                state:      'gallery-edit',
                library: {type: 'image'},
                editing: true,
                multiple: true,
                selection: false

            }).on('update', function (selection) {
                for(var $i in selection.models){
                    var attachment = selection.models[$i].attributes;
                    if($i==0){
                         selectImage(attachment,e.id,$parent);
                    }
                    else if($i<=selection.models.length-1){
                         $('#'+e.id +'_add_new').trigger('click'); 
                         setTimeout(selectImage(attachment,e.id),600);
                     }
                }
            }).open();
            
        }).on('paste keyup keypress change', 'input[name^="' + e.id + '[url]"][type="text"]', function (event) {
            var $self = $(this),
                $parent = $self.closest('li');
            setTimeout(function () {
                $parent.find('.ptb_post_cmb_image').css('background-image', 'url(' + $self.val() + ')');
            }, 100);
        });
    });
	
    window.addEventListener('DOMContentLoaded', function(){
        $('body').on('change', '.ptb_extra_gallery_mode input[type="radio"]', function () {
            var $column = $(this).closest('.ptb_back_active_module_content').find('.ptb_extra_gallery_columns, .ptb_extra_gallery_layout');
            if ($(this).val() === 'showcase') {
                $column.slideUp();
            }
            else {
                $column.slideDown();
            }
        });
        $(document).on('PTB.template_load', function () {
            $('.ptb_extra_gallery_mode input[type="radio"]:checked').trigger('change');
        });
    }, {once:true, passive:true});

}(jQuery));
 