( ( doc, $,PTB ) => {
    'use strict';
	doc.body.addEventListener( 'ptb_lightbox_init', async e=>{
		const context = e.detail.context,
            items =  $( 'a.ptb_lightbox', context );
		if ( items.length > 0 ) {
			PTB.LoadCss(ptb.url + 'css/lightbox.css');
            await PTB.loadJs(ptb.url + 'js/lightbox.min.js',typeof $.fn.magnificPopup!=='undefined');
			
            items.on( 'click', function( e ) {
                e.preventDefault();
                e.stopPropagation();
                const $this = $( this ),
                    url = new URL( $this.prop( 'href' ) );

                url.searchParams.set( 'ptb_lightbox', 1 ); // flag the request URL
                const type = $this.hasClass( 'ptb_lightbox_ajax' ) ? 'ajax' : 'iframe',
                args = {
                    type : type,
                    mainClass : 'standard-frame',
                    iframe : {
                        markup: '<div class="mfp-iframe-scaler" style="max-width: 100% !important; height: 100%;">' +
                                '<div role="button" tabindex="0" class="tf_close mfp-close"></div>' +
                                '<div class="mfp-iframe-wrapper">' +
                                '<iframe class="mfp-iframe" ' + 'noresize="noresize" frameborder="0" allowfullscreen></iframe>' +
                                '</div>' +
                                '</div>'
                    },
                    items : {
                        src : url.href
                    },
                    callbacks : {}
                },
                open_callback = ()=> {
                    $( doc ).trigger( 'ptb_loaded', true );
                };
                args.callbacks[ type === 'ajax' ? 'ajaxContentAdded' : 'open' ] = open_callback;

                $.magnificPopup.open( args );
            } );
		}

		const gallery_items = $( '.ptb_extra_lightbox .ptb_extra_gallery_item a' ),
            video_items = $( 'a.ptb_extra_video_lightbox' );
		if ( gallery_items.length > 0 || video_items.length > 0 ) {
			PTB.LoadCss(ptb.url + 'css/lightbox.css');
            await PTB.loadJs(ptb.url + 'js/lightbox.min.js',typeof $.fn.magnificPopup!=='undefined');
            gallery_items.on( 'click', function( e ) {
                
                e.preventDefault();
                e.stopPropagation();
                const $this = $( this ),
                    args = {
                       type : 'image',
                       gallery : {
                           enabled: true
                       }
                   },
                items = [];
                let index = 0;
                $( this ).closest( '.ptb_extra_gallery' ).find( 'a[data-rel]' ).each( function( i, v ) {
                    items.push( {
                        src : this.href,
                        title : $( this ).find( 'img' ).attr( 'title' )
                    } );
                    if ( $this.is( $( this ) ) ) {
                        index = i;
                    }
                } );
                args.items = items;
                $.magnificPopup.open( args, index );
            } );

            video_items.on( 'click', function( e ) {
                e.preventDefault();
                e.stopPropagation();

                const $this = $( this ),
                    url = $this.prop( 'href' ),
                args = {
                    type: 'iframe',
                    mainClass : 'video-frame',
                    iframe: {
                        markup: '<div class="mfp-iframe-scaler" style="max-width: 100% !important; height: 100%;">' +
                                '<div role="button" tabindex="0" class="tf_close mfp-close"></div>' +
                                '<div class="mfp-iframe-wrapper">' +
                                '<iframe class="mfp-iframe" ' + 'noresize="noresize" frameborder="0" allowfullscreen></iframe>' +
                                '</div>' +
                                '</div>',
                        patterns: {
                            youtu: {
                                index: 'youtu.be/', 
                                id(url) {        
                                    const m = url.match(/\.be\/(.*)/);
                                    if ( !m || !m[1] ) return null;
                                    return m[1];
                                },
                                src: '//www.youtube.com/embed/%id%?autoplay=1'
                            },
                            youtube: {
                                index: 'youtube.com/', 
                                id(url) {        
                                    const m = url.match(/[\\?\\&]v=([^\\?\\&]+)/);
                                    if ( !m || !m[1] ) return null;
                                    return m[1];
                                },
                                src: '//www.youtube.com/embed/%id%?autoplay=1'
                            },
                            vimeo: {
                                index: 'vimeo.com/', 
                                id(url) {        
                                    const m = url.match(/(https?:\/\/)?(www.)?(player.)?vimeo.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/);
                                    if ( !m || !m[5] ) return null;
                                    return m[5];
                                },
                                src: '//player.vimeo.com/video/%id%?autoplay=1'
                            }
                        }
                    },
                    items : {
                        src : url
                    }
                };

                $.magnificPopup.open( args );
            } );
		}
	} );

} )( document, jQuery,PTB );