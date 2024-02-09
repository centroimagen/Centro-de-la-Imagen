(function ($) {
    'use strict';

    var GetList = function () {
        var $wrapper = $('#ptb-search-list-form #the-list'),
			$form = $wrapper.closest('form');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {'action': 'ptb_search_list'},
            beforeSend: function () {
                $form.addClass('ptb-search-wait');
            },
            complete: function () {
                $form.removeClass('ptb-search-wait');
            },
            success: function (resp) {
                if (resp) {
                    resp = $(resp).find('#the-list').html();
                    $wrapper.html(resp);
                }
            }
        });
    };

    window.addEventListener('load', function(){
        $(document).on('PTB.close_lightbox', function (e, $this) {
            var $wr = $($this).next('#ptb_lightbox_container');
            if ($wr.find('#ptb-search-edit-form').length > 0) {
                GetList();
            }
        }).on('PTB.template_drag_end', function (event, $item, $ui, $type) {
            if ($type !== 'search') {
                return false;
            }
            var exclude = new Array('taxonomies', 'custom_image','plain_text' ,'custom_text','has'),
				$data = $item.data('type');

            if ($.inArray($data, exclude) === -1 && $('#ptb_row_wrapper').find('[data-type="' + $data + '"]').length > 2) {
                if ($item.hasClass('ptb_is_metabox')) {
                    var $name = $item.find('input,select').attr('name');
                    if ($name && $('#ptb_row_wrapper').find('[data-type="' + $data + '"] [name="' + $name + '"]').length < 2) {
                        return false;
                    }
                }
                alert(ptb_search.module + ' ' + $item.find('.ptb_module_name').text());
                $item.remove();
                return false;
            }
        });
        $('body').on( 'click','.ptb_search_templates a.ptb_search_delete', function (e) {
            e.preventDefault();
            if (confirm(ptb_js_admin.template_delete)) {
                var $form = $(this).closest('form');
                $.ajax({
                    url: this,
                    type: 'POST',
                    beforeSend: function () {
                        $form.addClass('ptb-search-wait');
                    },
                    complete: function () {
                        $form.removeClass('ptb-search-wait');
                    },
                    success: function (resp) {
                        if (resp) {
                            resp = $(resp).find('#the-list').html();
                            $('#ptb-search-list-form #the-list').html(resp);
                        }
                    }
                });
            }
        }).on('submit','#ptb-search-form-save', function (e) {
            e.preventDefault();
            var $form = $(this);
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function () {
                    $form.addClass('ptb-search-wait');
                },
                complete: function () {
                    $form.removeClass('ptb-search-wait');
                },
                success: function (resp) {
                    if (resp) {
                        $form.closest('.ptb_lightbox_inner').fadeOut('normal', function () {
                            $(this).html(resp).fadeIn();
                        });
                    }
                }
            });
        })
		.on('keyup','#ptb-search-title',function(e){
            var r = /^[A-Za-z0-9\s]+$/,
                submit = $('#ptb-search-form-save #submit');
               if(r.test($(this).val())){
                   submit.removeAttr('disabled');
               }
               else{
                   submit.prop('disabled','disabled');
               }
        }).on( 'change', '.ptb_result_switcher input[type=radio]', function() {
			if ( $( this ).is( ':checked' ) ) {
				var value = $( this ).val();
				$( '.ptb_same_page_select' ).toggle( value === 'same_page' );
				$( '.ptb_result_page_select' ).toggle( value === 'diff_page' );
			}
		} );

		$(document).on('PTB.openlightbox', function (e, $this) {
			$( '.ptb_result_switcher input[type=radio]' ).filter( ':checked' ).trigger( 'change' );

			// Taxonomy's Show As option
			$( 'body' ).on( 'change', '.ptb_search_show_as input', function() {
				this.closest( '.ptb_back_active_module_content' ).querySelector( '.ptb_search_tax_operator' ).style.display = this.value === 'checkbox' || this.value === 'multiselect' ? 'block' : 'none';
			} );
			$( '.ptb_search_show_as input:checked' ).trigger( 'change' );
		});

		if ( document.location.hash ) {
			const edit_link = document.querySelector('a.ptb_custom_lightbox[data-href*="' + document.location.hash.substring(1) + '"]');
			if ( edit_link ) {
				edit_link.click();
			}
		}

     }, {once:true, passive:true});
}(jQuery));