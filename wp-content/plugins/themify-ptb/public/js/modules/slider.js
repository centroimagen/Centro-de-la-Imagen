( ( doc, $,PTB ) => {
    'use strict';
	doc.body.addEventListener( 'ptb_slider_init', async e=> {
		const context = e.detail.context,
		$sliders = $( '.ptb_slider', context ).not( '.ptb_slider_done' );
		if ( $sliders.length > 0 ) {
            await PTB.loadJs(ptb.url + 'js/swiper.min.js',typeof TF_Swiper === 'function','5.3.8');
            await PTB.loadJs(ptb.url + 'js/swiper-autoplay.min.js','undefined' !== typeof TF_Swiper.prototype.modules.autoplay,'5.3.8');
            $sliders.each(function(){
				const $this = $( this ),
					navWrap=doc.createElement( 'div' ),
					slides_count = this.getElementsByClassName('tf_swiper-slide').length,
                args = {
					centeredSlides : false,
					autoHeight : true,
					slidesPerView : Math.min( slides_count, $this.data( 'visible' ) ),
					slidesPerGroup: 1,
					loop : slides_count > 1,
					speed: $this.data( 'speed' )
				};
                if(slides_count > 1 ){
                    if ( $this.data( 'slider_nav' ) ) {
                        const prev = doc.createElement('a'),
                            next = doc.createElement('a');
                        prev.className = 'carousel-prev';
                        next.className = 'carousel-next';
                        prev.href = '#';
                        next.href = '#';
                        navWrap.append( prev,next );

                        args.navigation = {
                            disabledClass: 'disabled',
                            nextEl: next,
                            prevEl: prev
                        };
                    }
                    if ( $this.data( 'pager' )) {
                        const pager = doc.createElement('div');
                        pager.className = 'carousel-pager';
                        navWrap.appendChild( pager );
                        args.pagination = {
                           el : pager,
                           type : 'bullets',
                           bulletClass : 'ptb_carousel_bullet',
                           bulletActiveClass: 'selected',
                           modifierClass : 'ptb_carousel_',
                           currentClass: 'selected',
                           clickable : true
                       };
                    }
                    if ( $this.data( 'auto' )) {
                        args.autoplay = {
                            delay : $this.data( 'auto' ) * 1000,
                            disableOnInteraction : $this.data( 'pause_hover' )
                        };
                    }
                    if ( $this.data( 'pager' ) || $this.data( 'slider_nav' ) ) {
                        navWrap.className = 'ptb_carousel_nav_wrap';
                        this.appendChild( navWrap );
                    }
                }
				new TF_Swiper( this, args );
				$this.addClass('ptb_slider_done');
			});
		}
	} );

} )( document, jQuery,PTB );