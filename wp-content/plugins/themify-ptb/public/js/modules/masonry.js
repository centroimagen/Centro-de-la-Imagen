( ( doc, $,PTB ) => {
    'use strict';
	doc.body.addEventListener( 'ptb_masonry_init', async e=> {
		const items = $('.ptb_masonry',e.detail.context);
		if(items.length>0){
            await PTB.loadJs(ptb.include + 'masonry.min.js',typeof $.fn.masonry!=='undefined','4.2.2');
            const isRtl=doc.body.classList.contains('rtl');
            items.each(function(){
                this.insertAdjacentHTML('afterbegin','<div class="ptb_gutter_sizer"></div><div class="ptb_post_sizer"></div>');
                PTB.imagesLoad(this).then(el=>{
                    if ( $( el).data( 'masonry' ) ) {
                        $( el).masonry( 'destroy' );
                    }
                    $( el ).masonry({
                        horizontalOrder : true,
                        columnWidth : '.ptb_post_sizer' ,
                        itemSelector:'.ptb_post',
                        isOriginLeft : ! isRtl,
                        gutter: '.ptb_gutter_sizer' 
                    });
                });
            });
		}
	} );

} )( document, jQuery,PTB );