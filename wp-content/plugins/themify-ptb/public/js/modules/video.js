( ( document, $ ) => {
	'use strict';
	document.body.addEventListener( 'ptb_video_init', function( e ) {
		const context = e.detail.context;
		 $( '.ptb_extra_show_video', context ).on('click',function(e) {
			e.preventDefault();
			var $url = $(this).data('url');
			if ($url) {
			   $(this).next('img').replaceWith('<iframe src="' + $url + '" frameborder="0" ebkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>');
			} else {
				var $v = $(this).next('video');
				$v.prop('controls', 1);
				$v.get(0).play();
			}
			$(this).remove();
		});
	} );

} )( document, jQuery );