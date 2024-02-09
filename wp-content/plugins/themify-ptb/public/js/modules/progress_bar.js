( ( document, $ ) => {
	'use strict';
	document.body.addEventListener( 'ptb_progress_bar_init', function( e ) {
		const context = e.detail.context;
		var $progress_bar = $( '.ptb_extra_progress_bar', context ).not('.ptb_extra_progress_bar_done');
		function progressCallback() {
			$progress_bar.each(function() {
				var $orientation = $(this).data('meterorientation'),
				$args = {
					goal: '100',
					raised: $(this).data('raised').toString(),
					meterOrientation: $orientation,
					bgColor: 'rgba(0,0,0,.1)',
					width: $orientation === 'vertical' ? '60px' : '100%',
					height: $orientation === 'vertical' ? '200px' : '3px',
					displayTotal: !$(this).data('displaytotal'),
					animationSpeed: 2000
						 };
				if ($(this).data('barcolor')) {
					   $args.barColor = $(this).data('barcolor');
					}
				$(this).jQMeter($args);
				$(this).addClass('ptb_extra_progress_bar_done');
			});
		}
		if ($progress_bar.length > 0) {
			if ($.fn.jQMeter) {
				progressCallback();
			} else {
				PTB.LoadAsync( ptb.jqmeter, progressCallback, ptb.ver, function() {
					return ('undefined' !== typeof $.fn.jQMeter);
				});
			}
		}
	} );

} )( document, jQuery );