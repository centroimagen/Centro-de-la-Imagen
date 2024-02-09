
var PTB_Layout,
    PTB_Tabs;
($=> {
    'use strict';
    //==================================================================================================================
    // Helper functions
    //==================================================================================================================

    function getQueryVariable(variable) {
        var query = window.location.search.substring(1),
            vars = query.split("&");
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split("=");
            if (pair[0] === variable) {
                return pair[1];
            }
        }
        return false;
    }


    function library(module) {

        $(function () {
            if (module.init) {
                module.init();
            }
        });

        return module;
    }

    /*******************************************************************************************************************
     * Custom Post Type Module ******************************************************************************************
     * *****************************************************************************************************************/

    library(function () {

        var cache = function () {
            return $('#ptb-cpt-filter').length !== 0;
        };

        var bindActions = function () {};


        return {
            init () {
                if (cache()) {
                    bindActions();
                }
            }
        };

    }());

    library(function () {

        var $postTypeId,
                $postTypeSingularLabel,
                $postTypeSlug,
                $postMetaSlug,
                $postTypeRewriteSlug,
                $postMetaName,
                postTypeId,
                slug,
                $validationMessage,
                options,
                $inputOptions,
                $metaBoxItemsWrapper,
                $buttonsWrapper,
                selectorMetaBoxCollapse = '.ptb_cmb_item_collapse',
                selectorMetaBoxRemove = '.ptb_cmb_item_remove',
                selectorMetaBoxBody = '.ptb_cmb_item_body';

        var cache = function () {

            $postTypeId = $('#ptb_cpt_id');

            // if ($postTypeId.length === 0) {
                // return false;
            // }

            $postTypeSingularLabel = $('#ptb_cpt_singular_label_' + ptb_js_admin.lng);
            $postTypeSlug = $('#ptb_cpt_slug');
            $postMetaSlug = '.ptb_cmb_items_wrapper .ptb_cmb_slug';
            $postMetaName = '.ptb_cmb_items_wrapper .ptb_active_lng.ptb_meta_name input[type="text"]';
            $postTypeRewriteSlug = $('#ptb_cpt_rewrite_slug');

            $validationMessage = $('#ptb_ajax_message');
            postTypeId = $postTypeId.val();
            slug = $postTypeSlug.val();

            $inputOptions = $('#ptb_post_type_cmb_data');
            $metaBoxItemsWrapper = $('.ptb_cmb_items_wrapper');
            $buttonsWrapper = $('.ptb_cmb_buttons_wrapper');

            return true;
        },
        createMetaBoxItem = function (id, options, ready) {

            var selector = "#" + options.type + "_\\{\\{id\\}\\}";
            if ($(selector).length > 0) {
                var reg = ready ? new RegExp(options.type + '_{{id}}', "gi") : new RegExp('{{id}}', "gi"),
                    html = $(selector).clone()[0].outerHTML.replace(reg, ready ? id : options.id),
                        $metaBox = $(html).appendTo($metaBoxItemsWrapper),
                        $names = $metaBox.find('input[id^=' + id + '_name_]'),
                        $description = $metaBox.find('input[id^=' + id + '_description_]'),
                        $slug = $metaBox.find('input[id^=' + id + '_slug]');
                $names.each(function () {
                    var $code = $(this).attr('id').replace(id + '_name_', '');
                    $(this).val(options.name[$code]);
                });
                $description.each(function () {
                    var $code = $(this).attr('id').replace(id + '_description_', '');
                    $(this).val(options.description[$code]);
                });
                if ( typeof slug !== 'undefined' && slug !== '' && !ready ) {
                    $slug.val(slug + '_' + id);
                } else {
                    $slug.val(id);
                }
                if (ready) {
                    $slug.prop('readonly', true);
                }
				if ( typeof options['admin_column'] !== 'undefined' && options['admin_column'] === true ) {
					$metaBox.find( 'input[id^=' + id + '_admin_column]' )[0].checked = true;
				}
                var $metaBoxBody = $metaBox.find(selectorMetaBoxBody);

                makeRemovable($metaBox.find(selectorMetaBoxRemove));
                makeCollapsible($metaBox.find(selectorMetaBoxCollapse));

                // general event which triggers for every metabox type
                $.event.trigger({
                    type: "ptb_metabox_create",
                    id: id,
                    options: options,
                    container: $metaBoxBody
                });

                $.event.trigger({
                    type: "ptb_metabox_create_" + options.type,
                    id: id,
                    options: options,
                    container: $metaBoxBody
                });

                $metaBox.data(id, options);

                return $metaBox;
            }
            return false;
        },
        createMetaBoxItems = function () {
            var data = getOptions();

            if (!data) {
                // error
                return;
            }

            try {
                options = JSON.parse(data);

                if (Array.isArray(options)) {
                    options = {};
                }
            } catch (e) {
                // error
                return;
            }
            $.each(options, function (id, option) {
                var $item = createMetaBoxItem(id, option, 1);
                if ($item) {
                    $item.show();
                }
            });

            makeSortable();
        },
        getOptions = function () {
            return $inputOptions.val();
        },
        sanitize_post_type_slug = function ($val) {
            return $val.replace(/[^A-Za-z0-9_]+/g, '-').toLowerCase().substr( 0, 20 );
        },
        sanitize_slug = function ($val) {
            return $val.replace(/[^A-Za-z0-9_]+/g, '_').toLowerCase();
        },
        updateOptions = function () {
            options = {};

            $metaBoxItemsWrapper.children().each(function (index, $element) {


                var id = $element.id,
                    $old_id = id,
                    $slug = $('input[id^=' + id + '_slug]');

                if ($slug && !$slug.prop('readonly')) {
                    id = $element.id = sanitize_slug($slug.val());
                    $($element).find('input,select,.ptb_cmb_options_wrapper').each(function () {
                        var $name = $(this).prop('name'),
                                $id = $(this).prop('id');
                        if ($name) {
                            $(this).prop('name', $name.replace($old_id, id));
                        }
                        if ($id) {
                            $(this).prop('id', $id.replace($old_id, id));
                        }

                    });
                    $('#' + $old_id).prop('id', id);
                }
                options[id] = $.data($element)[$old_id];

                var $names = $('input[id^=' + id + '_name_]'),
                    $description = $('input[id^=' + id + '_description]');
                options[id].name = {};
                $names.each(function () {
                    var $code = $(this).attr('id').replace(id + '_name_', '');
                    options[id].name[$code] = $(this).val();
                });
                options[id].description = {};
                $description.each(function () {
                    var $code = $(this).attr('id').replace(id + '_description_', '');
                    options[id].description[$code] = $(this).val();
                });
				delete options[id]['admin_column'];
				if ( $('input[id^=' + id + '_admin_column]').is( ':checked' ) ) {
					options[id].admin_column = true;
				}

                $.event.trigger({
                    type: "ptb_metabox_save_" + options[id].type,
                    id: id,
                    options: options[id]
                });
            });
            $inputOptions.val(JSON.stringify(options));
            $metaBoxItemsWrapper.find('[name]').removeAttr('name');
            $('.ptb_cmb_item_wrapper').find('[name]').removeAttr('name');
        },
        getNextId = function (type) {

            var idSet = $.map(options, function (o) {
                if (o.type === type) {
                    return o.id;
                }
            });
            idSet = idSet.filter(function (el) {
                return el > 0;
            });
            var maxId = idSet.length === 0 ? 0 : Math.max.apply(null, idSet);
            if (!maxId) {
                maxId = 0;
            }
            return maxId + 1;
        },
        makeCollapsible = function ($button) {
            $($button).on('click', function (e) {
                $(this).closest( 'li' ).toggleClass('collapse');
                var $metaBoxItemBody = $(this).parent().next();
                $metaBoxItemBody.toggle('blind', 500);
            });
        },
        makeRemovable = function ($button) {
            $($button).on('click', function (e) {
                var $metaBoxItem = $(this).parents('.ptb_cmb_item_wrapper'),
                    id = $metaBoxItem.attr('id');
                $metaBoxItem.hide('blind', 500);
                $metaBoxItem.data(id).deleted = true;
            });
        },
        makeSortable = function () {
            $metaBoxItemsWrapper.sortable({
                placeholder: "ui-state-highlight",
				handle : '.ptb_cmb_item_title_wrapper'
            });
        },
        refreshSortable = function () {
            $metaBoxItemsWrapper.sortable("refresh");
        },
        bindButtonActions = function () {

            $buttonsWrapper.children().each(function (index, element) {

                var metabox_type = $(this).data('type');

                $(element).on('click', function (e) {

                    e.preventDefault();

                    $.event.trigger({
                        type: "ptb_add_metabox"
                    });

                    $.event.trigger({
                        type: "ptb_add_metabox_" + metabox_type
                    });

                });

                $(document).on("ptb_add_metabox_" + metabox_type, function (e) {

                    var newOptions = e.result || {},
                        nextId = getNextId(metabox_type),
                        id = metabox_type + '_' + nextId;

                    newOptions.type = metabox_type;
                    newOptions.id = nextId;
                    newOptions.deleted = false;
                    newOptions.name = "";
                    newOptions.description = "";

                    options[id] = newOptions;

                    createMetaBoxItem(id, newOptions, false).hide().show('blind', 500);
                    refreshSortable();
                });
            });

			const menu_icon = document.getElementById( 'ptb_cpt_menu_icon' );
			if ( menu_icon ) {
				const preview = menu_icon.nextElementSibling.querySelector( 'span' );
				menu_icon.addEventListener( 'change', function() {
					preview.className = 'dashicons-before ' + this.value;
				} );
			}
        },
        validateForm = function () {

            var $form = $('form'),
                ajaxTimer;

            if (getQueryVariable('action') === 'add') {

                $postTypeSingularLabel.on('keyup change', function () {

                    slug = sanitize_post_type_slug($(this).val());


                    if ($postTypeSlug.val() !== slug) {

                        $postTypeSlug.val(slug).trigger('change');
                        $postTypeRewriteSlug.val(slug);
                    }

                });

            }

            $postTypeSlug.on('change', function () {

                slug = $(this).val();

                if (postTypeId !== slug || slug === "") {
                    clearTimeout(ajaxTimer);
                    ajaxTimer = setTimeout(function () {
                        is_post_type_name_valid(slug);
                    }, 250);
                }

            });

            $('body').on( 'focusout',$postMetaSlug, function (e) {
                if (!$(this).prop('readonly')) {
                    var slug = sanitize_slug($(this).val()),
                        $msg = " already exists";
                    $(this).val(slug);
                    $('.ptb_meta_slug_error').removeClass('ptb_meta_slug_error');
                    show_validation_message($(this).data('prev_value') + $msg, false);

                    if (slug) {
                        var $find = false,
                            $id = $(this).prop('id');
                        $($postMetaSlug).each(function () {
                            if ($(this).val() === slug && $id !== $(this).prop('id')) {
                                $(this).addClass('ptb_meta_slug_error');
                                $find = 1;
                            }
                        });
                        if ($find) {
                            show_validation_message(slug + $msg, true);
                            $(this).addClass('ptb_meta_slug_error').data('prev_value', slug);
                        }
                        else {
                            $(this).data('ptb_focus', 1);
                        }
                    }
                    else {
                        $(this).val($(this).closest('.ptb_cmb_item_wrapper').prop('id')).trigger('focusout');
                    }
                }
                else {
                    $(this).off('focusout');
                }
            }).on('focusout',$postMetaName,  function () {
                var $id = $(this).closest('.ptb_cmb_item_wrapper').prop('id');
                if (!$('#' + $id + '_slug').prop('readonly') && !$('#' + $id + '_slug').data('ptb_focus')) {
					if ( typeof slug !== 'undefined' && slug !== '' ) {
						$('#' + $id + '_slug').val(slug + '_' + sanitize_slug($(this).val())).trigger('focusout');
					} else {
                    $('#' + $id + '_slug').val(sanitize_slug($(this).val())).trigger('focusout');
                }
                }
                else {
                    $(this).off('focusout');
                }
            });

            $form.on('submit',function (event) {
				if ( ! $( 'input[id^=ptb_cpt_singular_label_' + ptb_js_admin.lng + ']' ).length ) {
					updateOptions();
					return;
				}

                var valid = true;
                $validationMessage.find('p').remove();
                if (slug === "" || postTypeId !== slug) {
                    valid = !is_post_type_name_valid(slug, false);
                }

                var required = $('input[id^=ptb_cpt_singular_label_' + ptb_js_admin.lng + '],input[id^=ptb_cpt_plural_label_' + ptb_js_admin.lng + ']'),
                    errors = [];
                required.each(function () {
                    if ( ! $(this).val().trim() ) {
                        valid = false;
                        var $error = $(this).closest('tr').find('label').text();

                        if (!errors[$error]) {
                            show_validation_message( ptb_js_admin.empty_message.replace( '%s', $error ), true );
                            errors[$error] = 1;
                        }
                    }
                });

                $($postMetaSlug).trigger('focusout');
                if ($('.ptb_meta_slug_error').length > 0) {
                    valid = false;
                }
                if (!valid) {
                    $("html, body").animate({scrollTop: 0}, "slow");
                    event.preventDefault();
                }
                else {
                    updateOptions();
                }
            });



            $form.find('.ptb-collapse + table').each(function () {
                $(this).hide();
            });

            var $collapse = $form.find('.ptb-collapse'),
                $ht = $collapse.prev('h2');
            if ($ht.length === 0) {//for wp<4.4 
                $ht = $collapse.prev('h3');
            }
            $ht.addClass('ptb-collapse-title').on('click',function () {
                $(this).toggleClass('collapsed').nextAll('table').first().toggle( 'blind', 500 );
            });


        },
        is_post_type_name_valid = function (slug, async) {

            async = typeof async !== 'undefined' ? async : true;

            var result = true;
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    // The name of the function to fire on the server
                    action: 'ptb_ajax_post_type_name_validate',
                    // The nonce value to send for the security check
                    nonce: $('#ptb-ajax-notification-nonce').text().trim(),
                    // The slug/name of custom post type
                    slug: slug
                },
                async: async,
                success:function(response){
                    result = !!response.trim();
                    show_validation_message(response, result);
                }
            });
            return result;

        },
        show_validation_message = function (message, show) {

            if (show) {
                if ($validationMessage.find('p:contains(' + message + ')').length === 0) {
                    $validationMessage.append('<p>' + message + '</p>');
                }
                $validationMessage.fadeIn();

            }
            else if ($validationMessage.find('p').length === 0 || message) {
                if (message) {
                    $validationMessage.find('p:contains(' + message + ')').remove();
                    if ($validationMessage.find('p').length === 0) {
                        $validationMessage.fadeOut('fast');
                    }
                }
                else {
                    $validationMessage.fadeOut('fast');
                }
            }


        };

        return {
            init: function () {
                if (cache()) {
                    bindButtonActions();
                    createMetaBoxItems();
                    validateForm();
                }
            }
        };
    }());

    /*******************************************************************************************************************
     * Custom Taxonomy Template *****************************************************************************************
     * *****************************************************************************************************************/

    library(function () {

        var cache = function () {
            return $('#ptb-ctx-filter').length !== 0;
        };

        var bindActions = function () {};

        return {
            init: function () {
                if (cache()) {
                    bindActions();
                }
            }
        };

    }());

     library(function () {

        var $taxonomyId,
                $taxonomySlug,
                taxonomyId,
                slug,
                $validationMessage;

        var cache = function () {

            $taxonomyId = $('#ptb_ctx_id');
            $taxonomySlug = $('#ptb_ctx_slug');

            if ($taxonomyId.length === 0) {
                return false;
            }

            $validationMessage = $('#ptb_ajax_message');
            taxonomyId = $taxonomyId.val();
            slug = $taxonomySlug.val();

            return true;
        },
        validateForm = function () {

            var $form = $('form'),
                ajaxTimer;

            if (getQueryVariable('action') === 'add') {

                $('#ptb_ctx_singular_label_' + ptb_js_admin.lng).on('keyup keypress blur change', function () {

                    slug = $(this).val().replace(/[^A-Za-z0-9_]+/g, '_').toLowerCase();

                    if ($taxonomySlug.val() !== slug) {
                        $taxonomySlug.val(slug).trigger('change');
                    }

                });

            }

            $taxonomySlug.on('change', function () {

                slug = $(this).val();

                if (taxonomyId !== slug || slug === "") {
                    clearTimeout(ajaxTimer);
                    ajaxTimer = setTimeout(function () {
                        is_taxonomy_name_valid(slug);
                    }, 250);
                }

            });

            $form.on('submit',function (event) {
                var valid = true;
                $validationMessage.find('p').remove();
                if (slug === "" || taxonomyId !== slug) {
                    valid = !is_taxonomy_name_valid(slug, false);
                }

                var required = $('input[id^=ptb_ctx_singular_label_],input[id^=ptb_ctx_plural_label_]'),
                    errors = [];
                required.each(function () {
                    if (! $(this).val().trim() ) {
                        valid = false;
                        var $error = $(this).closest('tr').find('label').text();

                        if (!errors[$error]) {
                            show_validation_message( ptb_js_admin.empty_message.replace( '%s', $error ), true );
                            errors[$error] = 1;
                        }
                    }
                });

                if (!valid) {
                    $("html, body").animate({scrollTop: 0}, "slow");
                    event.preventDefault();
                }
            });

        },
        is_taxonomy_name_valid = function (slug, async) {

            async = typeof async !== 'undefined' ? async : true;

            var result = true;

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    // The name of the function to fire on the server
                    action: 'ptb_ajax_taxonomy_name_validate',
                    // The nonce value to send for the security check
                    nonce: $('#ptb-ajax-notification-nonce').text().trim(),
                    // The slug/name of custom post type
                    slug: slug

                },
                async: async,
                success(response){
                    result = !!response.trim();
                    show_validation_message(response, result);
                }
            });

            return result;

        },
        show_validation_message = function (message, show) {
            if (show) {
                $validationMessage.append('<p>' + message + '</p>');
                $validationMessage.fadeIn();
            }
            else if ($validationMessage.find('p').length === 0) {
                $validationMessage.fadeOut('fast');
            }
        };

        return {
            init () {
                if (cache()) {
                    validateForm();
                }
            }
        };
    }());

    /******************************************************************************************************************/
    /******************************************************************************************************************/
    /******************************************************************************************************************/

    library(function () {

        var options,
                $inputOptions,
                $templateItemsWrapper,
                $templateItemWrapper,
                $templateItemTitleWrapper,
                $templateItemTitle,
                $templateItemRemove,
                $templateItemBody,
                $buttonsWrapper;

        var cache = function () {
            if ($('#ptb_ptt_id').length === 0) {
                return false;
            }


            $inputOptions = $('#ptb_ptt_archive').length ? $('#ptb_ptt_archive') : $('#ptb_ptt_single');
            $templateItemsWrapper = $('ul.ptb_template_items_wrapper');
            $templateItemWrapper = $('<li class="ptb_template_item_wrapper"></li>');
            $templateItemTitleWrapper = $('<div class="ptb_template_item_title_wrapper"></div>');
            $templateItemTitle = $('<h4 class="ptb_template_item_title"></h4>');
            $templateItemRemove = $('<button type="button" class="ptb_template_item_remove" aria-label="Remove"><span aria-hidden="true">&times;</span></button>');
            $templateItemBody = $('<div class="ptb_template_item_body"></div>');
            $buttonsWrapper = $('.ptb_template_buttons_wrapper');

            return true;
        },
        createTemplateItem = function (id, options) {

            var _$templateItem = $templateItemWrapper.clone().appendTo($templateItemsWrapper),
                _$templateTitleWrapper = $templateItemTitleWrapper.clone().prependTo(_$templateItem),
                _$templateTitle = $templateItemTitle.clone().appendTo(_$templateTitleWrapper),
                _$templateRemove = $templateItemRemove.clone().appendTo(_$templateTitleWrapper),
                _$templateBody = $templateItemBody.clone().appendTo(_$templateItem);

            // general event which triggers for every metabox type
            $.event.trigger({
                type: "ptb_template_item_create",
                id: id,
                options: options,
                container: _$templateBody
            });

            $.event.trigger({
                type: "ptb_template_item_create_" + options.type,
                id: id,
                options: options,
                title: _$templateTitle,
                container: _$templateBody
            });

            _$templateItem.data(id, options).attr('id', id);

            makeRemovable(_$templateRemove);

            return _$templateItem;
        },
        makeRemovable = function ($button) {
            $($button).on('click', function (e) {
                var $templateItem = $(this).parents('.ptb_template_item_wrapper'),
                    id = $templateItem.attr('id');
                $templateItem.hide(500).data(id).deleted = true;
            });
        },
        getOptions = function () {
            return $inputOptions.val();
        },
        getNextId = function () {

            var idSet = $.map(options, function (o) {
                return o.id;
            });
            idSet = idSet.filter(function (el) {
                return el > 0;
            });
            var maxId = idSet.length === 0 ? 0 : Math.max.apply(null, idSet);
            if (!maxId) {
                maxId = 0;
            }
            return maxId + 1;
        },
        makeSortable = function () {
            $templateItemsWrapper.sortable({
                placeholder: "ui-state-highlight",
				handle : '.ptb_back_module_top'
            });
            $templateItemsWrapper.disableSelection();
        },
        refreshSortable = function () {
            $templateItemsWrapper.sortable("refresh");
        },
        createTemplateItems = function () {
            var data = getOptions();

            if (!data) {
                // error
                return;
            }

            try {
                options = JSON.parse(data);

                if (Array.isArray(options)) {
                    options = {};
                }
            } catch (e) {
                // error
                return;
            }

            $.each(options, createTemplateItem);

            makeSortable();
            makeRemovable();
        },
        bindButtonActions = function () {
            $buttonsWrapper.children().each(function (index, element) {

                var template_item_type = $(this).data('type');


                $(element).on('click', function (e) {

                    e.preventDefault();

                    var nextId = getNextId(),
                        id = template_item_type + '_' + nextId;

                    options[id] = {
                        id: nextId,
                        type: template_item_type,
                        deleted: false,
                        name: $(this).text(),
                        meta_key: $(this).data('meta-key')
                    };

                    createTemplateItem(id, options[id]).hide().show(500);
                    refreshSortable();

                });

            });
        },
        bindLightboxActions = function ( context ) {
            var $opened = true;
            $( 'a.ptb_lightbox', context ).on('click',function (e) {
                e.preventDefault();
                if ($opened === true) {
					PTB_Common.show_loader();
                    var $self = $(this);
                    $.ajax({
                        url: ajaxurl,
                        beforeSend: function () {
                            $opened = false;
                        },
                        data: {
                            'action': "ptb_ajax_themes",
                            'ptb-ptt': getQueryVariable("ptb-ptt"),
                            'template': $self.data("template-type")
                        },
                        success: function (data) {
                            PTB_Common.hide_loader();
							if (data) {
                                openLightBox(e, $self.attr('title'), data, false, false);
                                $opened = true;
                            }
                        }
                    });
                }
            });

        },
        bindRemoveTemplateBtn = function ( context ) {
            $( 'a.ptb_ptt_remove', context ).on( 'click', function (e) {
                e.preventDefault();
				if ( ! window.confirm( ptb_js_admin.confirm_remove_template ) ) {
					return;
				}
				$.ajax({
					url: ajaxurl,
					type : 'POST',
					data: {
						'action': "ptb_remove_custom_template",
						'ptb-ptt': getQueryVariable( "ptb-ptt" ),
						'ptb_type': e.target.dataset['templateType']
					},
					success () {
						e.target.closest( 'div' ).remove();
					}
				});
            });
		},
		/* add support for deep linking to template edits */
		templateDeepLink = function() {
			if ( document.location.hash ) {
				const hash = document.location.hash.substring(1);
				let edit_link;
				if ( hash === 'single' || hash === 'archive' ) {
					edit_link = document.getElementById( 'ptb_ptt_edit_' + hash );
				} else {
					edit_link = document.querySelector( 'a.ptb_ptt_edit_button[data-template-type="custom_' + decodeURI( hash ) + '"]' );
				}
				if ( edit_link ) {
					edit_link.click();
				}
			}
		},
		customTemplates = function() {
			let btn = document.getElementById( 'ptb_new_custom_template' );
			if ( ! btn ) {
				return;
			}
			btn.addEventListener( 'click', ( e ) => {
				e.preventDefault();
				let name = window.prompt( ptb_js_admin.new_template );
				if ( null !== name && '' !== name ) {
					let wrap = document.createElement( 'div' ),
						a = document.createElement( 'a' ),
						removeBtn = document.createElement( 'a' );
					a.setAttribute( 'href', '#' );
					a.setAttribute( 'data-template-type', 'custom_' + name );
					a.className = 'ptb_ptt_edit_button ptb_lightbox';
					a.textContent = ptb_js_admin.edit_template.replace( '%s', name );
					wrap.appendChild( a );
					bindLightboxActions( wrap );
					removeBtn.setAttribute( 'href', '#' );
					removeBtn.setAttribute( 'data-template-type', 'custom_' + name );
					removeBtn.className = 'ptb_ptt_remove';
					removeBtn.innerHTML = '<svg aria-hidden="true" class="ptb_fa ptb_ti-close"><use href="#ptb-ti-close"></use></svg> ' + ptb_js_admin.remove_template;
					wrap.appendChild( removeBtn );
					bindRemoveTemplateBtn( wrap );
					btn.previousElementSibling.append( wrap );
				}
			} );
		};

        return {
            init () {
                if (cache()) {
                    bindLightboxActions();
                    bindButtonActions();
                    createTemplateItems();
                    customTemplates();
					bindRemoveTemplateBtn();
					templateDeepLink();
                }
            }
        };

    }());

    /******************************************************************************************************************/
    /******************************************************************************************************************/
    /******************************************************************************************************************/
    /* Custom Meta Box Extension */

    library(function () {

        var $cmbWrapper,
            bodyWrapperSelector = '.ptb_post_cmb_body_wrapper';

        var cache = function () {
			/* wrapper for all metabox options. In term.php file, WP's <form> tag is used */
            $cmbWrapper = $( '.ptb_post_cmb_wrapper, form#edittag' );
            return $cmbWrapper.length !== 0;
        },
        cmbItemHandler = function () {

            var $self, id;

            $cmbWrapper.find( '.ptb_post_cmb_item_wrapper' ).each(function () {
                $self = $(this);
                id = $self.attr('id');

                $.event.trigger({
                    type: "ptb_post_cmb_" + $self.data('ptb-cmb-type') + "_handle",
                    id: id,
                    cmbItem: $self
                });

                $.event.trigger({
                    type: "ptb_post_cmb_" + $self.data('ptb-cmb-type') + "_body_handle",
                    id: id,
                    cmbItemBody: $self.find(bodyWrapperSelector)
                });
            });

        };

        return {
            init: function () {
                if (cache()) {
                    cmbItemHandler();
                }
				/* display the Edit Fields link inside the metabox header */
				const edit_link = $cmbWrapper.find( '> .ptb_cmb_edit_link' );
				if ( edit_link ) {
					edit_link.appendTo( $cmbWrapper.closest( '.postbox' ).find( '.postbox-header h2' ) );
				}
            }
        };
    }());

    /******************************************************************************************************************/

    /* Custom Meta Box Text *******************************************************************************************/

    $(document).on('ptb_metabox_create_text', function (e) {
        var $default = e.container.find('input[id^=' + e.id + '_default_value_]'),
            $repeatable = e.options.repeatable ? 'yes' : 'no';

        $default.each(function () {
            var $code = $(this).attr('id').replace(e.id + '_default_value_', '');
            $(this).val(e.options.defaultValue[$code]);

        });
        $('#' + e.id + '_repeatable_' + $repeatable).prop('checked', true);
    })
    .on('ptb_add_metabox_text', function (e) {
        return {
            defaultValue: '',
            repeatable: false
        };
    })
    .on('ptb_metabox_save_text', function (e) {
        e.options.defaultValue = {};

        var $default = $('input[id^=' + e.id + '_default_value_]');
        $default.each(function () {
            var $code = $(this).attr('id').replace(e.id + '_default_value_', '');
            e.options.defaultValue[$code] = $(this).val();
        });
        e.options.repeatable = $('#' + e.id + '_repeatable_yes:checked').length > 0;
    })
    .on('ptb_post_cmb_text_body_handle', function (e) {
        var $optionsWrapper = e.cmbItemBody.find('.ptb_cmb_options_wrapper');

        if ($optionsWrapper.length === 0)
            return false;

        var $option = $optionsWrapper.children().first().clone();

        $optionsWrapper.sortable({
            placeholder: "ui-state-highlight"
        });

        e.cmbItemBody.find('.ptb_cmb_option_add')
			.on( 'click', { wrapper: $optionsWrapper },
			function (event) {
				var $newOption = $option.clone();
				$newOption.appendTo($optionsWrapper).hide().show('blind', 500);
				$newOption.find('input[name="' + e.id + '[]"]').val('');
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

    })


    /* Custom Meta Box Email *******************************************************************************************/

    .on('ptb_metabox_create_email', function (e) {
        e.container.find('input[id^=' + e.id + '_default_value]').val(e.options.defaultValue);
    })
    .on('ptb_add_metabox_email', function (e) {
        return {
            defaultValue: ''
        };
    })
    .on('ptb_metabox_save_email', function (e) {
        e.options.defaultValue = $('input[id^=' + e.id + '_default_value]').val().trim();
    })


    /* Custom Meta Box Number *******************************************************************************************/

   .on('ptb_metabox_create_number', function (e) {
        if (e.options.range) {
            $('#' + e.id + '_range').prop('checked', true);
        }
    })
    .on('ptb_add_metabox_number', function (e) {
        return {
            range: false
        };
    })
    .on('ptb_metabox_save_number', function (e) {
        e.options.range = $('input[id^=' + e.id + '_range]:checked').val();
    })

    /******************************************************************************************************************/

    /* Custom Meta Box Textarea ***************************************************************************************/

   .on('ptb_metabox_create_textarea', function (e) {
        var $default = e.container.find('input[id^=' + e.id + '_default_value_]');
        $default.each(function () {
            var $code = $(this).attr('id').replace(e.id + '_default_value_', '');
            $(this).val(e.options.defaultValue[$code]);
        });
        if (e.options.editor) {
            $('#' + e.id + '_editor').prop('checked', true);
        }
    })
    .on('ptb_add_metabox_textarea', function (e) {
        return {
            defaultValue: "",
            editor: false
        };
    })
    .on('ptb_metabox_save_textarea', function (e) {
        e.options.defaultValue = {};

        var $default = $('input[id^=' + e.id + '_default_value_]');
        $default.each(function () {
            var $code = $(this).attr('id').replace(e.id + '_default_value_', '');
            e.options.defaultValue[$code] = $(this).val();
        });
        e.options.editor = $('#' + e.id + '_editor').is(':checked');
    })


    /******************************************************************************************************************/

    /* Custom Meta Box Radio Button ***********************************************************************************/

    .on('ptb_metabox_create_radio_button', function (e) {
        var $option = e.container.find('.' + e.id + '_option_wrapper'),
            $optionWrapper = e.container.find('#' + e.id + '_options_wrapper');

        $option.remove();

		if ( e.options.columns ) {
			e.container.find( '#' + e.id + '_columns' ).val( e.options.columns );
		}

        // add options from settings
        $.each(e.options.options, function (index, option) {
            if ( option.id === undefined ) {
                return;
            }

            var $newOption = $option.clone();
            $newOption.appendTo($optionWrapper);

            var $options = $newOption.find('input[name^="' + e.id + '_options_"]');
            $options.each(function () {
                var $code = $(this).attr('name').replace(e.id + '_options_', '');
                $code = $code.replace('[]', '');
                var $val = option[$code] ? option[$code] : option.name,
                        $id = $.isNumeric(option.id) ? option.id : option.id.replace(e.id + '_', '');
                $(this).val($val)
                        .attr('id', e.id + '_' + $id)
                        .data('id', $id);
            });

            $newOption.find('.' + e.id + '_remove').click({option: $newOption}, removeOption);

            var $defaultSelected = $newOption.find('input[name="' + e.id + '_default_selected"]');

            $defaultSelected.attr('checked', option.selected);
        });

        $optionWrapper.sortable({
            placeholder: "ui-state-highlight",
			handle: '.ptb_cmb_option_sort',
        });

        // add new option
        e.container.find('#' + e.id + '_add_new')
                .click(
                        {
                            id: e.id,
                            option: $option,
                            wrapper: $optionWrapper
                        },
                function (e) {
                    e.preventDefault();
                    var $newOption = e.data.option.clone(),
                        $defaultSelected = $newOption.find('input[name="' + e.data.id + '_default_selected"]');

                    var nextId = getNextId(e);

                    $newOption.find('input[name^="' + e.data.id + '_options_"]')
                            .val('')
                            .attr('id', e.data.id + '_' + nextId)
                            .data('id', nextId);
                    $defaultSelected.attr('checked', false);
                    $newOption.find('.' + e.data.id + '_remove').click({option: $newOption}, removeOption);
                    $newOption.appendTo(e.data.wrapper).hide().show('blind', 500);
                    e.data.wrapper.sortable("refresh");
                });

        // remove option
        function removeOption(e) {
            e.preventDefault();
            e.data.option.hide('blind', 500, function () {
                $(this).remove();
            });
        }

        function getNextId(e) {
            var idSet = $('#'+e.data.id+'_options_wrapper input[name^="' + e.data.id + '_options_"]').map(function () {
                return $(this).data('id');
            });

            idSet = idSet.filter(function (el) {
                return el > 0;
            });
            var maxId = idSet.length === 0 ? 0 : Math.max.apply(null, idSet);
            if (!maxId) {
                maxId = 0;
            }
            return ++maxId;
        }
    })
    .on('ptb_add_metabox_radio_button', function (e) {
        return {
            options: [
                {
                    name: "Option 1",
                    id: 1,
                    selected: true
                },
                {
                    name: "Option 2",
                    id: 2,
                    selected: false
                }
            ]
        };
    })
    .on('ptb_metabox_save_radio_button', function (e) {
        e.options.options = {};
        var $li = $('#' + e.id + '_options_wrapper').children('li');
        $li.each(function ($i) {
            var $options = $(this).find('input[name^=' + e.id + '_options_]'),
                $selected = $(this).find('input[name^=' + e.id + '_default_selected]').is(':checked');
            e.options.options[$i] = {};
            $options.each(function () {
                var $code = $(this).attr('name').replace(e.id + '_options_', '');
                $code = $code.replace('[]', '');
                e.options.options[$i][$code] = $(this).val();
                e.options.options[$i].selected = $selected;
                e.options.options[$i].id = $(this).attr('id');
            });
        });
		e.options.columns = $( '#' + e.id + '_columns' ).val();
    })

    /******************************************************************************************************************/

    /* Custom Meta Box Checkbox ***************************************************************************************/

    .on('ptb_metabox_create_checkbox', function (e) {
        var $option = e.container.find('.' + e.id + '_option_wrapper'),
            $optionWrapper = e.container.find('#' + e.id + '_options_wrapper');

        $option.remove();

        // add options from settings
        $.each(e.options.options, function (index, option) {
            if ( option.id === undefined ) {
                return;
            }

            var $newOption = $option.clone();
            $newOption.appendTo($optionWrapper);
            var $options = $newOption.find('input[name^="' + e.id + '_options_"]');
            $options.each(function () {
                var $code = $(this).attr('name').replace(e.id + '_options_', '');
                $code = $code.replace('[]', '');
                var $val = option[$code] ? option[$code] : option.name,
                        $id = $.isNumeric(option.id) ? option.id : option.id.replace(e.id + '_', '');
                $(this).val($val)
                        .attr('id', e.id + '_' + $id)
                        .data('id', $id);
            });

            $newOption.find('.' + e.id + '_remove').click({option: $newOption}, removeOption);

            $newOption.find('input[name="' + e.id + '_default_checked"]').attr('checked', option.checked);

        });

        $optionWrapper.sortable({
            placeholder: "ui-state-highlight",
			handle : '.ptb_cmb_option_sort'
        });

		if ( e.options.columns ) {
			e.container.find( '#' + e.id + '_columns' ).val( e.options.columns );
		}

        // add new option
        e.container.find('#' + e.id + '_add_new')
			.click(
					{
						id: e.id,
						option: $option,
						wrapper: $optionWrapper
					},
			function (e) {
				e.preventDefault();
				var $newOption = e.data.option.clone(),
					nextId = getNextId(e);

				$newOption.find('input[name^="' + e.data.id + '_options_"]')
						.val('')
						.attr('id', e.data.id + '_' + nextId)
						.data('id', nextId);
				$newOption.find('input[name="' + e.data.id + '_default_checked"]').attr('checked', false);
				$newOption.find('.' + e.data.id + '_remove').click({option: $newOption}, removeOption);
				$newOption.appendTo(e.data.wrapper).hide().show('blind', 500);
				e.data.wrapper.sortable("refresh");
			});

        function getNextId(e) {
            var set = $('#'+e.data.id+'_options_wrapper input[name^="' + e.data.id + '_options_"]').map(function () {
                return $(this).data('id');
            });

            var maxId = Math.max.apply(null, set);

            return ++maxId;
        }

        // remove option
        function removeOption(e) {
            e.preventDefault();
            e.data.option.hide('blind', 500, function () {
                $(this).remove();
            });
        }

    })
    .on('ptb_add_metabox_checkbox', function (e) {
        return {
            options: [
                {
                    name: "Option 1",
                    id: 1,
                    checked: false
                },
                {
                    name: "Option 2",
                    id: 2,
                    checked: false
                }
            ]
        };
    })
    .on('ptb_metabox_save_checkbox', function (e) {
        e.options.options = {};
        var $li = $('#' + e.id + '_options_wrapper').children('li');
        $li.each(function ($i) {
            var $options = $(this).find('input[name^=' + e.id + '_options_]'),
                $selected = $(this).find('input[name^=' + e.id + '_default_checked]').is(':checked');
            e.options.options[$i] = {};
            $options.each(function () {
                var $code = $(this).attr('name').replace(e.id + '_options_', '');
                $code = $code.replace('[]', '');
                e.options.options[$i][$code] = $(this).val();
                e.options.options[$i].checked = $selected;
                e.options.options[$i].id = $(this).attr('id');
            });
        });
		e.options.columns = $( '#' + e.id + '_columns' ).val();
    })

    /******************************************************************************************************************/

    /* Custom Meta Box Select *****************************************************************************************/

    .on('ptb_metabox_create_select', function (e) {
        e.container
                .find('input[name="' + e.id + '_multiple_selects"]')
                .filter('[value="' + (e.options.multipleSelects ? "Yes" : "No") + '"]').attr('checked', true);

        var $option = e.container.find('.' + e.id + '_option_wrapper'),
            $optionWrapper = e.container.find('#' + e.id + '_options_wrapper');

        $option.remove();
        // add options from settings
        $.each(e.options.options, function (index, option) {
            if ( option.id === undefined ) {
                return;
            }

            var $newOption = $option.clone();
            $newOption.appendTo($optionWrapper);
            var $options = $newOption.find('input[name^="' + e.id + '_options_"]');
            $options.each(function () {
                var $code = $(this).attr('name').replace(e.id + '_options_', '');
                $code = $code.replace('[]', '');
                var $val = option[$code] ? option[$code] : option.name,
                        $id = $.isNumeric(option.id) ? option.id : option.id.replace(e.id + '_', '');
                $(this).val($val)
                        .attr('id', e.id + '_' + $id)
                        .data('id', $id);
            });
            $newOption.find('.' + e.id + '_remove').click({option: $newOption}, removeOption);

        });

        $optionWrapper.sortable({
            placeholder: "ui-state-highlight"
        });

        // add new option
        e.container.find('#' + e.id + '_add_new')
                .click(
                        {
                            id: e.id,
                            option: $option,
                            wrapper: $optionWrapper
                        },
                function (e) {
                    e.preventDefault();
                    var $newOption = e.data.option.clone(),
                        nextId = getNextId(e);

                    $newOption.find('input[name^="' + e.data.id + '_options_"]')
                            .val('')
                            .attr('id', e.data.id + '_' + nextId)
                            .data('id', nextId);
                    $newOption.find('.' + e.data.id + '_remove').click({option: $newOption}, removeOption);
                    $newOption.appendTo(e.data.wrapper).hide().show('blind', 500);
                    e.data.wrapper.sortable("refresh");
                });

        function getNextId(e) {
            var set = $('#'+e.data.id+'_options_wrapper input[name^="' + e.data.id + '_options_"]').map(function () {
                return $(this).data('id');
            });

            var maxId = Math.max.apply(null, set);

            return ++maxId;
        }

        // remove option
        function removeOption(e) {
            e.preventDefault();
            e.data.option.hide('blind', 500, function () {
                $(this).remove();
            });
        }
    })
    .on('ptb_add_metabox_select', function (e) {
        return {
            multipleSelects: false,
            options: [
                {
                    name: "Option 1",
                    id: 1
                },
                {
                    name: "Option 2",
                    id: 2
                }
            ]
        };
    })
    .on('ptb_metabox_save_select', function (e) {
        e.options.multipleSelects = $('input[name="' + e.id + '_multiple_selects"]:checked').val() === 'Yes';
        e.options.options = {};
        var $li = $('#' + e.id + '_options_wrapper').children('li');
        $li.each(function ($i) {
            var $options = $(this).find('input[name^=' + e.id + '_options_]');
            e.options.options[$i] = {};
            $options.each(function () {
                var $code = $(this).attr('name').replace(e.id + '_options_', '');
                $code = $code.replace('[]', '');
                e.options.options[$i][$code] = $(this).val();
                e.options.options[$i].id = $(this).attr('id');
            });

        });
    })


    /******************************************************************************************************************/

    /* Custom Meta Box Image ******************************************************************************************/
    /******************************************************************************************************************/
    .on('ptb_post_cmb_image_body_handle', function (e) {

        $('input[name="' + e.id + '[]"].ptb_cmb_image_url').on('paste keyup keypress change', function () {
            var $self = $(this);
            $('input[name="' + e.id + '[]"][type="hidden"]').val(''); /* the attachment ID field */
            setTimeout(function () {
                $('#image_' + e.id).css('background-image', 'url(' + $self.val() + ')');
            }, 100);
        });

        var ptb_cmb_image_file_frame;

        $('#image_' + e.id).on('click', function (event) {

            event.preventDefault();

            // If the media frame already exists, reopen it.
            if (ptb_cmb_image_file_frame) {
                ptb_cmb_image_file_frame.open();
                return;
            }

            // Create the media frame.
            ptb_cmb_image_file_frame = wp.media.frames.file_frame = wp.media({
                title: $(this).data('uploader_title'),
                button: {
                    text: $(this).data('uploader_button_text')
                },
                library: {type: 'image'},
                multiple: false  // Set to true to allow multiple files to be selected
            });

            // When an image is selected, run a callback.
            ptb_cmb_image_file_frame.on('select', function () {
                // We set multiple to false so only get one image from the uploader
                var attachment = ptb_cmb_image_file_frame.state().get('selection').first().toJSON();
                $('input[name="' + e.id + '[]"][type="hidden"]').val(attachment.id);
                $('input[name="' + e.id + '[]"].ptb_cmb_image_url').val(attachment.url);
                $('#image_' + e.id).css('background-image', 'url(' + attachment.url + ')');
            });

            // Finally, open the modal
            ptb_cmb_image_file_frame.open();
        });

    });



    /* End Custom Meta Box Extension */

    /******************************************************************************************************************/
    /******************************************************************************************************************/
    /******************************************************************************************************************/
    /* Template Extension */

    var  metabox = ['text','textarea','radio_button','checkbox','select','image','link_button'];
    for(var i in metabox){
        $(document).on('ptb_template_item_create_'+metabox[i], function (e) {
            e.title.html(e.options.name);
        });
    }
    $(document).on('ptb_template_item_create_title', function (e) {
        e.title.html('Title');
    })
    .on('ptb_template_item_create_content', function (e) {
        e.title.html('Content');
    });


    /******************************************************************************************************************/
    /******************************************************************************************************************/
    /******************************************************************************************************************/
    /* Custom Meta Box Extension */

    library(function () {

        var $form,
                $btnExport,
                $btnImport,
                $listCpt,
                $listCtx,
                $listPtt,
                $radioMode,
                target,
                mode,
        cache = function () {
            $form = $('form#ptb_form_export');
            if ($form.length === 0) {
                return false;
            }

            $radioMode = $('input[name="ptb_export_mode"]');

            changeMode();

            $btnExport = $('#ptb_export');
            $btnImport = $('#ptb_import');

            $listCpt = $('#ptb_export_cpt_list');
            $listCtx = $('#ptb_export_ctx_list');
            $listPtt = $('#ptb_export_ptt_list');

            return true;
        },
        bindExportAction = function () {

            $btnExport.on('click',function (e) {
                e.preventDefault();

                var $list;

                target = $("a.nav-tab-active").data('target');

                switch (target) {
                    case 'cpt':
                        $list = $listCpt;
                        break;
                    case 'ctx':
                        $list = $listCtx;
                        break;
                    case 'ptt':
                        $list = $listPtt;
                        break;
                    default :
                        $list = $listCpt;
                }

                var data = {
                    mode: mode,
                    target: target,
                    list: $list.find('input:checked').map(function () {
                        return $(this).val();
                    }).get()
                };

                $('input[name="ptb_plugin_options[ptb_ie_export]"]').val(JSON.stringify(data));

                $('form#ptb_form_export').submit();
            });

        },
        bindImportAction = function () {

            $btnImport.addClass('disabled');
            $('input:file').on('change',function () {
                if ($(this).val()) {
                    $btnImport.removeClass('disabled');
                }
                else {
                    $btnImport.addClass('disabled');
                }
            });

            $btnImport.on('click',function (e) {
                e.preventDefault();
                if ($('input:file').val()) {
                    $('form#ptb_form_import').submit();
                }
            });
        },
        bindTabActions = function () {
            $(".nav-tab-wrapper a").on('click',function (e) {
                e.preventDefault();
                if ($(this).hasClass("nav-tab-active")) {
                    return;
                }
                $(this).addClass("nav-tab-active").siblings().removeClass("nav-tab-active");
                var tab = $(this).attr("href");
                $(".ptb_tab_content").not(tab).css("display", "none");
                $(tab).fadeIn();
            });

        },
        bindModeActions = function () {
            $radioMode.on('change',changeMode);
        },
        changeMode = function () {
            mode = $radioMode.filter(':checked').val();
            var list = $('a[href="#ptb_export_cpt_list"]').siblings();
            if (mode === 'linked') {
               list.fadeOut().click();
            } else {
                list.fadeIn();
            }
        };

        return {
            init() {
                if (cache()) {
                    bindModeActions();
                    bindTabActions();
                    bindExportAction();
                    bindImportAction();
                }
            }
        };
    }());


    /******************************************************************************************************************/
    /******************************************************************************************************************/
    /******************************************************************************************************************/

        // custom select wrapper
        window.addEventListener('load', function(){
            
        
            $(".ptb_interface select").wrap("<div class='ptb_custom_select'></div>");
            $('.ptb_number').on('keyup',function(){
                if(!/^[1-9][0-9]*[.,]*[0-9]*$/gi.test($(this).val())){
                    $(this).val($(this).val().replace(/[^0-9.,]/, ''));
                }
            });
            $(document).on( 'click','.ptb_remove_dialog_form input', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var form = $(this).closest('form'),
                    confirm_text =$(this).data('confirm');
                if(confirm_text && !confirm(confirm_text)){
                    return false;
                }
                $('input[name="remove"]',form).val(confirm_text?'1':'0');
                var progress = $('.ptb_alert',form);
                $.ajax({
                    type: 'POST',
                    url: ajaxurl, 
                    dataType:'json',
                    data:form.serialize(),
                    beforeSend(){
                         progress.removeClass('done error').addClass('busy').show();
                    },
                    complete(){
                        progress.removeClass('busy');
                    },
                    success:function(resp){
                        if(resp){
                            var cl = '',
                                callback;
                            if(resp.success){
                                cl = 'done';
                                callback = function(){
                                    if(resp.data=='1'){
                                        window.location.reload();
                                    }
                                    else{
                                        window.location = resp.data;
                                    }
                                };
                            }
                            else{
                                cl = 'error';
                                callback = function(){
                                    if(resp.data){
                                        alert(resp.data);
                                    }
                                };
                            }
                            progress.addClass(cl);
                            setTimeout(function(){
                                    progress.animate({'opacity':0},300,callback);
                            },800);
                        }
                    }
                });
            })
            .on( 'click','.ptb_register,.ptb_unregister,.ptb_cpt_copy,.ptb_ctx_copy', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $.ajax({
                    type: 'POST',
                    url: this, 
                    dataType:'json',
                    success:function(resp){
                        if(resp){
                            if(resp.data=='1'){
                                window.location.reload();
                            }
                            else{
                                window.location = resp.data;
                            }
                        }
                    }
                });
            });
        }, {once:true, passive:true});
		
    PTB_Layout = {
        init(){
            var pre = $('#ptb_pre_wrapper'),
                self = this;
            if(pre.length>0){
                $.getJSON('https://themify.org/public-api/ptb-layouts/index.json')
                .done(data=> {
                    if(data){
                        var html = '';
                        for(var i in data){
                            html+='<li>';
                            html+='<div class="ptb_pre_overlay" style="background-image:url('+data[i].img+')"></div>';
                            html+='<div class="ptb_pre_content"><a class="ptb_pre_zoom" href="'+data[i].url+'"><svg aria-hidden="true" class="ptb_fa ptb_ti-zoom-in"><use href="#ptb-ti-zoom-in"></use></svg></a>';
                            html+='<div class="ptb_pre_import"><a target="_blank" href="'+data[i].url+'">'+data[i].title+'</a>';
                            html+='<div class="ptb_pre_actions"><label><input type="checkbox" value="'+i+'" name="sample" />'+ptb_js_admin.import_samples+'</label>';
                            html+='<input class="ptb_pre_import_btn" type="button" data-slug="'+i+'" value="'+ptb_js_admin.import+'" />';
                            html+='</div></div></div></li>';
                        }
                        var ul = pre.find('ul');
                        ul.empty();
                        ul[0].insertAdjacentHTML('afterbegin',html);
                        self.preview();
                        self.import();
                    }                
                });
            }
        },
        preview(){
            $('.ptb_pre_zoom').on('click',function(e){
               e.preventDefault(); 
                openLightBox(e, '', '<iframe id="ptb_preview_iframe" src="'+$(this).prop('href')+'"></iframe>','ptb_preview_iframe_wrapper');
            });
        },
        progress(state,val,count,total){
            if(state==='loading'){
                var html = '<div id="ptb_pre_progress_wrap" class="ptb_admin_lightbox ptb_scrollbar"><div class="ptb_pre_progress"><div class="ptb_pre_progress_indicator">0%</div></div><div class="ptb_pre_progress_info"></div><div class="ptb_pre_progress_count"></div></div><div class="ptb_overlay"></div>';
                document.body.insertAdjacentHTML('beforeend',html);
            }
            if(count && total){
                 $('.ptb_pre_progress_count').html(count+' / '+total);
            }
            if(ptb_js_admin.import_pre[state]!==undefined){
                var msg = ptb_js_admin.import_pre[state];
                if(val){
                    msg = msg.replace('%s',val);
                }
                $('.ptb_pre_progress_info').html(msg);
            }
        },
        setIndicator(val){
            val = val+'%';
            $('.ptb_pre_progress').css('width',val).find('.ptb_pre_progress_indicator').html(val);
        },
        import:function(){
            var self = this;
            $('.ptb_pre_import_btn').on('click',function(e){
                e.preventDefault(); 
                var slug = $(this).data('slug'),
                    cpt = $(this).closest('#ptb_pre_wrapper').data('cpt');
                if($.inArray(slug,cpt)!==-1 && !confirm(ptb_js_admin.confirm_import.replace('%s',slug))){
                    return;
                }
                self.progress('loading');
                var is_sample = $(this).closest('.ptb_pre_actions').find('input[type="checkbox"]').is(':checked');
                self.setIndicator(10);
                $.getJSON('https://themify.org/public-api/ptb-layouts/'+slug+'.json')
                .done(function (cpt) {
                    if(cpt){
                        var callback = samples=>{
                            self.setIndicator(80);
                            self.progress('saving');
                            return $.ajax({
                                url:ajaxurl,
                                type:'POST',
                                dataType:'json',
                                data:{
                                    'slug':slug,
                                    'cpt':JSON.stringify(cpt),
                                    'action':'ptb_import_pre_cpt',
                                    'samples':samples?JSON.stringify(samples):''
                                },
                                success:function(res){
                                    if(res && res.status){
                                        self.setIndicator(100);
                                        self.progress('finish');
                                    }
                                    else{
                                        alert('Something went wrong');
                                    }
                                    setTimeout(function(){
                                        $('#ptb_pre_progress_wrap,.ptb_overlay').remove();
                                        if(res.status){
                                             setTimeout(function(){
                                                 window.location.reload();
                                             },100);
                                        }
                                    },2000);
                                }
                            });  
                        };
                        self.progress('cpt',slug);
                        self.setIndicator(is_sample?20:45);
                        if(is_sample){
                            self.progress('start_samples');
                           var max,
                            page = 1,
                            all,
                            arr = [],
                            limit = 5,
                            img_count=0,
                            getSamples = ()=>{
                                $.ajax({
                                    url:'https://themify.org/ptb/wp-json/wp/v2/'+slug+'?statuses=public&per_page='+limit+'&page='+page,//rest API
                                    dataType:'json',
                                    crossDomain: true,
                                    success(res,status,response){
                                        if(status==='success' && res){
                                            if(!max){
                                                max = response.getResponseHeader('X-WP-TotalPages');
                                                all = response.getResponseHeader('X-WP-Total');
                                            }
                                            self.setIndicator(20+(page*limit));
                                            self.progress(null,null,page*limit,all);
                                            for(var i=0,len=res.length;i<len;++i){
                                                var tax = {};
                                                if(res[i].ptb_taxonomy){
                                                    var ptb_tax = res[i].ptb_taxonomy;
                                                    for(var k in ptb_tax){
                                                        tax[k] = {};
                                                        for(var j=0,tlen=ptb_tax[k].length;j<tlen;++j){
                                                            tax[k][ptb_tax[k][j].slug] = ptb_tax[k][j].name;
                                                        }
                                                    }
                                                }
                                                if(res[i].ptb_featured_image){
                                                    ++img_count;
                                                }
                                                self.progress('samples',res[i].title.rendered);
                                                arr.push({
													'post_type' : res[ i ].type,
                                                    'title':res[i].title.rendered,
                                                    'excerpt':res[i].excerpt.rendered,
                                                    'content':res[i].content.rendered,
                                                    'meta':res[i].ptb_metabox?res[i].ptb_metabox:null,
                                                    'tax':tax,
                                                    'img':res[i].ptb_featured_image?res[i].ptb_featured_image:null
                                                });
                                            }
                                            ++page;
                                            if(page!=max){
                                                setTimeout(getSamples,800);
                                            }
                                            else{
                                                var total = 50;
                                                self.progress('start_images',null,all,all);
                                                self.setIndicator(total);
                                                page =all = max = null;
                                                var i=0,
                                                    len=arr.length,
                                                    getImage = function(){
                                                        if(!arr[i].img){
                                                            ++i;
                                                            if(i<len){
                                                              setTimeout(getImage,300);
                                                            }
                                                            return;
                                                        }
                                                        self.progress(null,null,i,img_count-1);
                                                        var xhr = new XMLHttpRequest();
                                                        self.progress('images',arr[i].img.url);
                                                        
                                                        self.setIndicator(total+i);
                                                        xhr.open('GET', arr[i].img.url, true);
                                                        xhr.responseType = 'blob';
                                                        xhr.onload = function(e) {
                                                           if (this.status === 200) {
                                                             var formData = new FormData(),
                                                                xhr2 = new XMLHttpRequest();
                                                                formData.append('file',this.response);
                                                                if(arr[i].img.title){
                                                                    formData.append('title',arr[i].img.title);
                                                                }
                                                                if(arr[i].img.caption){
                                                                    formData.append('caption',arr[i].img.caption);
                                                                }
                                                                xhr2.responseType = 'json';
                                                                xhr2.open('POST', ajaxurl+'?action=ptb_upload_blob', true);
                                                                xhr2.onload = function(e) { 
                                                                    if(this.response.id){
                                                                        arr[i].img = this.response.id;
                                                                    }
                                                                    ++i;
                                                                    if(i<len){
                                                                       setTimeout(getImage,300);
                                                                    }
                                                                    else{
                                                                        callback(arr);
                                                                    }
                                                                };
                                                                xhr2.send(formData);
                                                                formData = xhr = null;
                                                           }
                                                        };
                                                        xhr.onerror = function (e) {
                                                            alert('Cant`t Load the image '+arr[i].url);
                                                        };
                                                        xhr.send();
                                                    };
                                                getImage();
                                            }
                                        }
                                    },
                                    error(){
                                        alert("Can't get samples");
                                    }
                                });  
                            };
                            getSamples();
                        }
                        else{
                            callback(null);
                        }
                        
                    }
                });
            });
        }
    };

	/**
	 * Tabbed admin interface
	 */
	PTB_Tabs = {
		init() {
			const tabs = document.getElementsByClassName( 'ptb-tabs' );
			if ( tabs ) {
				for ( let i = 0; i < tabs.length; i++ ) {
					const nav_tab_wrapper = tabs[ i ].querySelector( '.nav-tab-wrapper' );
					nav_tab_wrapper.addEventListener( 'click', function( e ) {
						e.preventDefault();
						if ( e.target && e.target.tagName === 'A' ) {
							const selected = e.target.getAttribute( 'href' ).substring(1),
								nav_links = this.closest( '.nav-tab-wrapper' ).querySelectorAll( 'a' ),
								panels = this.closest( '.ptb-tabs' ).querySelectorAll( ':scope > div' );
							for ( let j = 0; j < panels.length; j++ ) {
								panels[ j ].style.display = panels[ j ].id === selected ? 'block' : 'none';
							}
							for ( let j = 0; j < nav_links.length; j++ ) {
								nav_links[ j ].classList.remove( 'nav-tab-active' );
							}
							e.target.classList.add( 'nav-tab-active' );
						}
					} );
				}
			}			
		}
	};

})(jQuery);

window.addEventListener('load', ()=>{
    'use strict';
	PTB_Tabs.init();
    InitLanguageTabs();
    ThemplateDelete();
    const $ = jQuery;
    PTB_Layout.init();
    $('body').on('click', 'a.ptb_custom_lightbox', function (e) {
        e.preventDefault();
        e.stopPropagation();

		PTB_Common.show_loader();

        $('.ptb_current_ajax').removeClass('ptb_current_ajax');
        $(this).addClass('ptb_current_ajax');
        var $self = $(this);
		let url = this.dataset.href ? this.dataset.href : this.href;
        $.ajax({
            url: url,
            success: function (data) {
                if (data) {
                    openLightBox(e, $self.attr('title'), data, $self.data('class'), $self.data('top'));
                }
            }
        });
    })
    .on('ptb_pre_layout_update',()=>{
            PTB_Layout.init();
    });

	const custom_labels_heading = document.getElementById( 'ptb_admin_custom_labels_heading' );
	if ( custom_labels_heading ) {
		custom_labels_heading.parentElement.colSpan = 2;
	}

}, {once:true, passive:true});
var InitLanguageTabs = function () {
    'use strict';
    var $ = jQuery;
    $('body').on('click', '.ptb_language_tabs li', function (e) {
        e.preventDefault();
        if (!$(this).hasClass('ptb_active_tab_lng')) {
            var $tab = $(this).closest('ul'),
                $fields = $tab.next('ul');
            $tab.find('.ptb_active_tab_lng').removeClass('ptb_active_tab_lng');
            $(this).addClass('ptb_active_tab_lng');
            $fields.find('.ptb_active_lng').removeClass('ptb_active_lng');
            $fields.find('li').eq($(this).index()).addClass('ptb_active_lng');
        }
    });
},
ThemplateDelete = function () {
    'use strict';
    jQuery('.templates .delete a').on('click',function (e) {
        if (!confirm(ptb_js_admin.template_delete)) {
            e.preventDefault();
        }
    });
},
lightboxCloseKeyListener = function (e) {
    'use strict';
    if (e.keyCode === 27) {
        e.preventDefault();
        closeLightBox(e);
    }
},
openLightBox = function ( e, title, content, $class, callback ) {
    'use strict';
    e.preventDefault();
    var $ = jQuery,
		$uniqid = 'ptb_' + Math.random().toString(36).substr(2, 9),
        $lightbox = '<div id="' + $uniqid + '" class="ptb_admin_lightbox ptb_scrollbar ptb_icon_lightbox ptb_interface">' +
            '<div class="ptb_lightbox_title">' + (title?title:'') + '</div>' +
            '<a href="#" class="ptb_close_lightbox">×</a>' +
            '<div id="ptb_lightbox_container">' +
            '<div class="ptb_lightbox_inner">' + content + '</div>' +
            '</div></div><div class="ptb_overlay"></div>';
    $(document).on('keyup', lightboxCloseKeyListener);
    $('body').append($lightbox);
     var el = $('#' + $uniqid);
    if ($class) {
        el.addClass($class);
    }

	PTB_Common.hide_loader();

    el.show();
    setTimeout(function(){
        el.addClass('ptb_lightbox_show').one('transitionend webkitTransitionEnd oTransitionEnd',function(){
			if ( callback ) {
				callback();
			}
            $.event.trigger("PTB.openlightbox", [e, $uniqid]);
        }).find('.ptb_close_lightbox').on('click',closeLightBox); 
    },100);

	document.body.classList.add( 'ptb_lightbox_active' );
    
},
closeLightBox = function (e) {
    'use strict';
    e.preventDefault();
    var $ = jQuery,
        $container = $(this).closest('.ptb_admin_lightbox');
    $(document).off('keyup', lightboxCloseKeyListener);
    $.event.trigger('PTB.close_lightbox', this);
    $container.removeClass('ptb_lightbox_show').one('transitionend webkitTransitionEnd oTransitionEnd',function(){
        $(this).next('.ptb_overlay').remove();
        $(this).remove();
    });
	document.body.classList.remove( 'ptb_lightbox_active' );
};