( ( document, $ ) => {
	'use strict';
	document.body.addEventListener( 'ptb_post_filter_init', function( e ) {
		const context = e.detail.context;
		var $filter = $('.ptb-post-filter',context);
		$filter.each(function () {
			var $entity = $(this).closest( '.ptb_wrap' ).find( '.ptb_loops_wrapper' );
			$(this).on('click', 'li', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var $posts = $entity.find('.ptb_post'),
					masonry = $entity.hasClass('ptb_masonry');
				$posts.removeClass('ptb-isotop-filter-clear');

				if ($(this).hasClass('ptb_filter_active')) {
					$filter.find('li.ptb_filter_active').removeClass('ptb_filter_active');
					$entity.removeClass('ptb-isotop-filter');
					$posts.stop().fadeIn('normal',function(){
						if(masonry){
							$entity.masonry('layout');
						}
					});
				}
				else {
					$filter.find('li.ptb_filter_active').removeClass('ptb_filter_active');
					$(this).addClass('ptb_filter_active');
					$entity.addClass('ptb-isotop-filter');
					var $tax = '.ptb-tax-' + $(this).data('tax'),
							$child = $(this).find('li');
					if ($child.length > 0) {
						$child.each(function () {
							$tax += ' ,.ptb-tax-' + $(this).data('tax');
						});
					}
					var $items = $posts.filter($tax),
						$grid = $entity.hasClass('ptb_grid4') ? 4 : ($entity.hasClass('ptb_grid3') ? 3 : ($entity.hasClass('ptb_grid2') ? 2 : 1));
					if ($grid > 1) {
						$items.each(function ($i) {
							if ($i % $grid === 0) {
								$(this).addClass('ptb-isotop-filter-clear');
							}
						});
					}
					$posts.hide();
					$items.not('visible').stop().fadeIn('normal',function(){
						if(masonry){
							$entity.masonry('layout');
						}
					});
				}
			});
		});
	} );

} )( document, jQuery );