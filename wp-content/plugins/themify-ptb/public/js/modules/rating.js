( ( document, $ ) => {
	'use strict';
	document.body.addEventListener( 'ptb_rating_init', function( e ) {
		const context = e.detail.context;
		var $rating = $( '.ptb_extra_rating', context ),
        style = '';
		$rating.each(function() {
			var $hcolor = $(this).data('hcolor'),
			 $vcolor = $(this).data('vcolor'),
			 $id = $(this).data('id'),
			$class = 'ptb_extra_' + $id;
			$(this).addClass($class);
			if ($hcolor) {
				style += '.' + $class + ':not(.ptb_extra_readonly_rating) > span:hover:before,' +
						'.' + $class + ':not(.ptb_extra_readonly_rating) > span:hover ~ span:before{color:' + $hcolor + ';}';
			}
			if ($vcolor) {
				style += '.' + $class + ' .ptb_extra_voted{color:' + $vcolor + ';}';
			}

		});
		if (style) {
			style = '<style type="text/css">' + style + '</style>'; 
			$('body').append(style);
		}
		$rating.not( '.ptb_extra_not_vote' ).children('span').on( 'click', function(e) {
			e.preventDefault();
			var $self = $(this).closest('.ptb_extra_rating');
			if ( $self.hasClass( 'ptb_extra_readonly_rating' ) ) {
				return;
			}
			var $spans = $self.children('span'),
				$value = $spans.length - $(this).index(),
				$post = $self.data('post'),
				$key = $self.data('key'),
				$same = $(".ptb_extra_rating[data-key='" + $key + "'][data-post='" + $post + "']");
			$.ajax({
				url: ptb.ajaxurl,
				dataType: 'json',
				data: {
					id: $post,
					value: $value,
					key: $key,
					action: 'ptb_extra_rate_voted'
				},
				type: 'POST',
				beforeSend() {
					if ($self.data('before')) {
						var $str = $self.data('before').replace(/#rated_value#/gi, $value);
						if ($str && !confirm($str)) {
						   return false;
						}
					}
					$same.addClass('ptb_extra_readonly_rating');  
				},
				success(data) {
					if (data && data.success) {
						var $total = data.total;
						$same.each(function() {
							$($(this).children('span').get().reverse()).each(function($i) {
								if ($total > $i) {
									$(this).addClass('ptb_extra_voted');
								}
							});
							var $count = $(this).next('.ptb_extra_vote_count');
							if ($count.length > 0) {
								$count.html('( ' + data.count + ' )');
							}
						});
						if ($self.data('after')) {
							var $str = $self.data('after').replace(/#rated_value#/gi, $value);
							if ($str) {
								alert($str);
							}
						}
					}
				}
			});
		});
	} );

} )( document, jQuery );