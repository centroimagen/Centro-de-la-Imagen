(function ($) {
    'use strict';

	/* decode HTML entities */
	let decodeEntities = ( string ) => {
		var textarea = document.createElement( 'textarea' );
		textarea.innerHTML = string;
		return textarea.innerText;
	};

    var slug = false;
    var AjaxLoop = function ( data, i ) {
		var length = data.length - 1;

		$.ajax( {
			url: ptb_search.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: { 'action': 'ptb_search_set_values', 'data': data[i] },
			success: function ( resp ) {
				var $container = $( $('form.ptb-search-form')[i] );
				
				for ( var j in resp ) {
					if ( resp[j].min ) {
						var min = Math.floor( resp[j].min ),
							max = Math.floor( resp[j].max ),
							$val_min = $( '#' + j + '_min' ),
							$val_max = $( '#' + j + '_max' );

						! $val_min.val() && $val_min.val( min );
						! $val_max.val() && $val_max.val( max );

						$val_min.attr( 'min', min );
						$val_max.prop( 'max', max );
					} else {
						for (var k in resp[j]) {
							if( $( '#' + j + '_' + resp[j][k] ).length ) {
								$( '#' + j + '_' + resp[j][k] )
									.prop( 'disabled', true )
									.closest( 'label' )
									.addClass( 'ptb-search-disabled' );
							} else if( $( '#' + j ).is( 'select' ) ) {
								$( '#' + j )
									.find( 'option[value="' + resp[j][k] + '"]' )
									.prop( 'disabled', true )
									.end()
									.trigger( 'chosen:updated' );
							}
						}
					}
				}
				
				InitSlider( $container );
				length > i && AjaxLoop( data, ++i );
			}
		} );
	};

    var InitSelect = function () {
        if ($.fn.select2) {
			$('form.ptb-search-form').find('select').select2({ width: '100%' });
        }
    };

    var InitSlider = function ($container) {
        if ($.fn.slider) {
            $container.find('.ptb-search-slider').each(function () {
                var $wrap = $(this).closest('.ptb_search_wrap_number'),
                        $max = $wrap.find('.ptb_search_number_max'),
                        $min = $wrap.find('.ptb_search_number_min'),
                        $min_val = parseInt($min.prop('min')),
                        $max_val = parseInt($max.prop('max')),
                        $v1 = parseInt($min.val()),
                        $v2 = parseInt($max.val());
                if( !isNaN($min_val) && !isNaN($max_val) ) {
                    $(this).slider({
                        range: true,
                        min: $min_val,
                        step: 1,
                        max: $max_val,
                        values: [$v1, $v2],
                        slide: function (event, ui) {
                            $min.val(ui.values[ 0 ]);
                            $max.val(ui.values[ 1 ]);
                            var $slider = $(this).find('.ptb-search-slider-tooltip-inner');
                            $slider.first().html(ui.values[0]);
                            $slider.last().html(ui.values[1]);
							
                        },
						stop: function (event, ui) {
                            var $form = $container.closest('.ptb-search-form');
							if (
								! $form.hasClass( 'ptb-search-no-ajax' )
								&& ! $form.find( 'input[type="submit"]' ).length // if there's a Submit button, disable onchange events
							) {
								$form.trigger( 'submit' );
							}
                        },
                        create: function (event, ui) {
                            var tooltip = '<span class="ptb-search-slider-tooltip"><span class="ptb-search-slider-tooltip-inner">' + $v1 + '</span><span class="ptb-search-slider-tooltip-arrow"></span></span>',
                                    $slider = $(this).children('.ui-slider-handle');
                            $slider.first().html(tooltip);
                            $slider.last().html(tooltip.replace($v1, $v2));
                        }
                    });
                }
            });
        }
    };

    var InitAutoComplete = function () {

        $('.ptb-search-autocomplete').each(function () {
            var $this = $(this),
                    $post_type = $this.data('post_type'),
                    cache = [];
            cache[$post_type] = [];
            $this.autocomplete({
                minLength: 2,
                source: function (request, response) {
                    var term = $.trim(request.term);
                    if (term.length < 2) {
                        return;
                    }
                    term = term.toLowerCase();
                    if (term in cache[$post_type]) {
                        response(cache[$post_type][ term ]);
                        return;
                    }
                    request.action = 'ptb_search_autocomplete';
                    request.key = $this.data('key');
                    $.getJSON(ptb_search.ajaxurl, request, function (data, status, xhr) {
						for ( const i in data ) {
							data[ i ]['label'] = decodeEntities( data[ i ]['label'] );
						}

                        cache[$post_type][ term ] = data;
                        response(data);
                    });
                },
                select: function (event, ui) {
                    $this.val(ui.item.value);

                    $this.next('input').val(ui.item.id);
                    return false;
                }
            })
                    .focus(function () {
                        $(this).autocomplete("search");
                    })
                    .autocomplete("widget")
                    .addClass("ptb-search-autocomplete-dropdown");
        });

    };

	function get_search_container( post_type, context = document ) {
		let container;
		const selectors = [
			/* 1. explicit [ptb-search-results] denoting where the search results should go */
			'.ptb-search-container .ptb_loops_wrapper',
			/* 2. main query of the page, except for Single pages */
			'body.archive .ptb_loops_wrapper.ptb_main_query',
			/* 3. Themify Builder's Post modules, with Dynamic Query active */
			'.themify_builder_content .module[data-ptb-search-posttype="' + post_type + '"] > :first-child',
			/* 4. any generic [ptb] shortcode with matching post type */
			'.ptb_loops_wrapper[data-type="' + post_type + '"]:not(.ptb_main_query)'
		];

		for ( let i = 0; i < selectors.length; i++ ) {
			container = context.querySelector( selectors[ i ] );
			if ( container ) {
				container = container.parentElement; /* get the .ptb_wrap element which includes pagination links & the PTB loop */
				break;
			}
		}

		return container;
	}

	function htmlToElement( html ) {
		var parser = new DOMParser();
        return parser.parseFromString( html, "text/html" );
	}

    var InitSubmit = function () {
        $( 'body' ).on( 'submit', '.ptb-search-form:not(.ptb-search-no-ajax)',  function (e) {
            var $form = $( this ),
				post_type = $form.find( '.ptb-search-post-type' ).val();

			/* determine where the search results will appear */
			let container = get_search_container( post_type );
			if ( ! container ) {
				/* no place to show the search results, send the form to post type's archive page */
				$form.attr( 'action', $form.attr( 'data-archive' ) );
				return;
			}

            e.preventDefault();

			let $slug = $form.find('input[name="f"]').val(),
				new_url = new URL( $form[0].action );

			/* exclude empty fields, to generate cleaner URLs */
			const data = $form.find( ':input' ).filter( function( i, el ) {
				return el.value != '';
			}).serialize();

			new_url.search = new URLSearchParams( data );

            $form.find('input[name="p"]').val('');
            $.ajax({
                url: new_url.href,
                data: data,
                type: 'GET',
                beforeSend: function () {
                    $form.addClass('ptb-search-submit');
                    container.classList.add('ptb-search-wait');
                },
                complete: function () {
                    $form.removeClass('ptb-search-submit');
                    container.classList.remove('ptb-search-wait');
                },
                success: function (resp) {
                    if ( resp ) {
						resp = htmlToElement( resp );
						/* find the search results in the new document and replace */
						const new_container = get_search_container( post_type, resp );
						if ( ! new_container ) {
							return;
						}
						container.innerHTML = new_container.innerHTML;
						PTB.init( container );

						if ( ! $form.hasClass( 'ptb-search-no-scroll' ) ) {
							ToScroll( $( container ) );
						}
                        slug = $slug;

						/* generate the URL for deep linking search results */
						history.pushState( {}, null, new_url.href );

						/* force reload the page when pressing Back button in browser */
						$(window).on( 'popstate', function (e) {
							location.reload();
						} );
                        if( typeof window.wp != 'undefined' && typeof window.wp.mediaelement != 'undefined' ) {
                            window.wp.mediaelement.initialize();
                        }
                        $(document).trigger('ptb_loaded',false);

						/* support for JetPack lazy load */
						document.body.dispatchEvent( new CustomEvent( 'jetpack-lazy-images-load' ) );

						/* load Themify elements */
						if (typeof Themify !== 'undefined') {
							Themify.lazyScroll(Themify.convert(container.parentElement.querySelectorAll('[data-lazy]')).reverse(), true);
						}
                    }
                }
            });
        }).on( 'reset', '.ptb-search-form', function() {
			const form = this.closest( 'form' ),
				inputs = form.querySelectorAll( 'input,select' );

			for ( let i = inputs.length - 1; i >= 0; i-- ) {
				if ( inputs[i].tagName === 'INPUT' ) {
					switch (inputs[i].type) {
						case 'text':
						case 'number':
							inputs[i].value = '';
							break;
						case 'radio':
						case 'checkbox':
							inputs[i].checked = false;
					}
				} else {
					inputs[i].selectedIndex = 0;
					$( inputs[i] ).val( null ).trigger( 'change' ); /* trigger change for select2.js */
				}
			}

			return false;
		} );
    };

    function find_page_number(element) {
        var $page = parseInt(element.text());
        if (!$page) {
            $page = parseInt(element.closest('.ptb_pagenav').find('.current').text());
            if (element.hasClass('next')) {
                ++$page;
            }
            else {
                --$page;
            }
        }
        return $page;
    }
    var InitPagination = function () {
        $('body').on('click', '.ptb-search-container .ptb_pagenav a', function (e) {
            var $slug = slug,
                    $form = $('.ptb-search-' + $slug);
            if ($form.length > 0) {
                e.preventDefault();
                $form.find('input[name="ptb_page"]').val(find_page_number($(this)));
                $form.submit();
            }
        });
    };
    var ToScroll = function ($container) {
		if($container.length>0){
			let top = $container.offset().top - 10,
				$admin_bar = $( '#wpadminbar' );
			if ( $admin_bar.length ) {
				top -= $admin_bar.outerHeight( true );
			}
			$('html,body').animate({
				scrollTop: top
			}, 1000);
		}
    };
    
    var InitDates = function(){
        if( $.timepicker){
            var $dates = $('.ptb_search_field_date input');
            var $defaultargs = {
			   showOn: 'focus',
			   showButtonPanel: true,  
			   showHour:1,
			   showMinute:1,
			   timeOnly:false,
			   showTimepicker:false,
			   buttonText: false,
			   dateFormat: 'yy-mm-dd',
			   timeFormat: 'hh:mm tt',
			   stepMinute: 5,
			   separator: '@',
			   minInterval:0,
				currentText: ptb_search.i18n.currentText,
				closeText: ptb_search.i18n.closeText,
				timeText: ptb_search.i18n.timeText,
			   beforeShow: function() {
				   $('#ui-datepicker-div').addClass( 'ptb_extra_datepicker ptb_search_datepicker' );
			   },
			   onClose: function(dateText, inst) {
				   
			   }
		   }; 
		   $dates.each(function(){
			   var $key = $(this).data('id');
			   if($key){
				   var $endDateTextBox = $('#'+$key+'_end');
				   if($(this).data('time')){
					   $defaultargs.showTimepicker = 1;
				   }
				   $.timepicker.datetimeRange(
					   $(this),
					   $endDateTextBox,
					   $defaultargs
				   ); 
			   }

		   });
	   }
	};

   window.addEventListener('load', function(){

		// update search results when changing fields
		$( 'body' ).on( 'change', '.ptb-search-form select, .ptb-search-form input, .ptb-search-form textarea', function() {
			var $form = $( this ).closest( '.ptb-search-form' );
			if (
				! $form.hasClass( 'ptb-search-no-ajax' )
				&& ! $form.find( 'input[type="submit"]' ).length // if there's a Submit button, disable onchange events
			) {
				$form.trigger( 'submit' );
			}
		} );

        InitSelect();
        InitAutoComplete();
        var $forms = [],
			temp = $('form.ptb-search-form');
		temp.each(function ($i) {
			$forms[$i] = $(this).find('.ptb_search_keys').val();
		});

        if ( typeof window.ptb_searched_slug !== 'undefined') {
            slug = window.ptb_searched_slug;
        }

        if(('ontouchstart' in window) || (navigator.MaxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0)){
            PTB.LoadAsync(ptb_search.url + 'js/jquery.ui.touch-punch.min.js', function(){
                InitDates();
                AjaxLoop($forms, 0);
            }, null, ptb_search.ver);
        }
        else{
            AjaxLoop($forms, 0);
            InitDates();
        }
        InitSubmit();
        InitPagination();
    }, {once:true, passive:true});


}(jQuery));