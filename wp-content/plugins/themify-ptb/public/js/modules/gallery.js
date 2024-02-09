( ( doc, $,PTB ) => {
	'use strict';
	doc.body.addEventListener( 'ptb_gallery_init', async e =>{
		const context=e.detail.contex,
            $showcase = $( '.ptb_extra_showcase', context ),
            masonry_gallery = $( '.ptb_extra_gallery_masonry .ptb_extra_gallery', context ),
            isRtl=doc.body.classList.contains('rtl');
		$showcase.each(function() {
			$( this ).on( 'click', 'img', function(e) {
				e.preventDefault();
				let $main = $(this).closest('.ptb_extra_showcase').find('.ptb_extra_main_image'),
					$img = $(this).clone(),
					link = $img.data( 'ptb-image-link' );
				$main.html( $img );
				link && $img.wrap( '<a href="' + link + '" />' );
			 }).find( 'img:first' ).trigger('click');
		});
		if(masonry_gallery.length>0){
            await PTB.loadJs(ptb.include + 'masonry.min.js',typeof $.fn.masonry!=='undefined','4.2.2');
            masonry_gallery.each(function(){
                this.insertAdjacentHTML('afterbegin','<div class="ptb_gallery_gutter_sizer"></div><div class="ptb_gallery_item_sizer"></div>');
                PTB.imagesLoad(this).then(el=>{
                    $(el).masonry({
                        columnWidth : '.ptb_gallery_item_sizer' ,
                        itemSelector:'.ptb_extra_gallery_item',
                        isOriginLeft : !isRtl,
                        gutter: '.ptb_gallery_gutter_sizer' 
                    });
                });
            });
		}
	} );

} )( document, jQuery,PTB );