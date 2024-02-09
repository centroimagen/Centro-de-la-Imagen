( ( document, $ ) => {
	'use strict';
	document.body.addEventListener( 'ptb_accordion_init', function( e ) {
		const context = e.detail.context;
		var $accordion = $( ".ptb_extra_accordion .ptb_accordion_title", context );

		if ($accordion.length > 0) {
			$accordion.each(function() {
				$(this).on( 'click', function() {
					
					var $this = $(this),
						$panel = $this.next(),
						$parent = $this.parent();

					if ($this.hasClass('ptb-accordion-active')) {
						$panel.slideUp();
						$this.removeClass('ptb-accordion-active');
					} else {
						var $pre = $parent.find('.ptb-accordion-active');
						if ( $pre.length > 0){
							$pre.each( function() { 
								var $t = $(this);
								$t.removeClass('ptb-accordion-active');
								$t.next().slideUp();
							});
						}
						$panel.slideDown();
						$this.addClass('ptb-accordion-active');
					}
				 });
			});
		}
	} );

} )( document, jQuery );