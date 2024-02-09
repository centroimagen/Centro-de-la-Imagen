
var PTB_Common;
( $=> {
	'use strict';
	PTB_Common = {

		el : {
			loader : null,
			icon_picker : null,
			search : null,
			search_css : null
		},
		get_loader() {
			if ( this.el.loader === null ) {
				this.el.loader = document.getElementsByClassName('ptb_alert')[0];
				if ( ! this.el.loader ) {
					this.el.loader = document.createElement('div');
					this.el.loader.className = 'ptb_alert';
					document.body.appendChild( this.el.loader );
				}
			}
			return this.el.loader;
		},
		show_loader() {
			this.get_loader().classList.add( 'busy' );
			this.get_loader().style.display = 'block';
		},
		hide_loader() {
			this.get_loader().classList.remove( 'busy' );
			this.get_loader().style.display = 'none';
		},
		load_lightbox( callback ) {
			if ( 'undefined' === typeof $.fn.magnificPopup ) {
				const style = document.createElement( 'link' );
				style.rel = 'stylesheet';
				style.href = ptbCommonVars.lb_css;
				document.body.appendChild( style );
				$.getScript( ptbCommonVars.lb_js, () => {
					callback();
				} );
			} else {
				callback();
			}
		},
		/**
		 * @param target Element(s) that their value changes to the selected icon
		 */
		show_icon_picker( target ) {
			if ( this.el.icon_picker === null ) {
				this.show_loader();
				$.ajax( {
					url : ptbCommonVars.ajaxurl,
					type : 'POST',
					data : { action : 'ptb_get_icons_list' },
					success : ( result ) => {
						document.body.insertAdjacentHTML( 'beforeend', result );
						this.el.icon_picker = document.getElementById( 'ptb_icon_picker_wrap' );
						this.el.search = this.el.icon_picker.querySelector( '.ptb_icon_search' );
						this.el.search_css = this.el.search.nextElementSibling;

						this.hide_loader();
						this.show_icon_picker( target );
					}
				} );
			} else {
				const self = this,
					el_container = target.closest( '[data-ptb_icon_picker_container]' ),
				callback = function( e ) {
					e.preventDefault();
					if ( e.target && e.target.tagName === 'A' ) {
						const value = e.target.dataset.cat + '-' + e.target.dataset.icon,
							value_targets = el_container.querySelectorAll( '[data-ptb_icon_picker_value]' ), /* elements to replace their value with the chosen icon */
							preview_targets = el_container.querySelectorAll( '[data-ptb_icon_picker_preview]' ); /* elements that their HTML inside will be replaced with the icon */
						if ( value_targets ) {
							for ( let i = 0; i < value_targets.length; i++ ) {
								value_targets[ i ].value = value;
							}
						}
						if ( preview_targets ) {
							for ( let i = 0; i < preview_targets.length; i++ ) {
								preview_targets[ i ].innerHTML = self.get_icon( value );
							}
						}
						$.magnificPopup.close();
						self.el.icon_picker.removeEventListener( 'click', callback );
						self.el.search.removeEventListener( 'keyup', this.search );
					}
				};
				this.el.icon_picker.style.display = 'block';
				$.magnificPopup.open({
					items: {
						src: this.el.icon_picker,
						type: 'inline'
					},
					callbacks : {
						open : function() {
							document.body.classList.add( 'ptb_icon_picker_open' );
						},
						close : function() {
							document.body.classList.remove( 'ptb_icon_picker_open' );
						}
					}
				});
				this.el.icon_picker.addEventListener( 'click', callback );
				this.el.search.addEventListener( 'keyup', this.search );
				this.el.icon_picker.parentElement.classList.add( 'ptb_scrollbar' );
			}
		},
		search() {
			const value = this.value.trim();
			if ( value ) {
				const css = `div#ptb_icon_picker_wrap section a:not([data-icon*="${value}"]) {
					display: none;
				}
				/* hide empty icon groups */
				div#ptb_icon_picker_wrap section:not(:has(a[data-icon*="${value}"])) {
					display: none;
				}
				`;
				PTB_Common.el.search_css.innerHTML = css;
			} else {
				PTB_Common.el.search_css.innerHTML = '';
			}
		},
		get_icon( name ) {
			return '<svg aria-hidden="true" class="ptb_fa ptb_' + name + '"><use href="#ptb-' + name + '"></use></svg>';
		},
		init_icon_picker() {
			$( 'body' ).on( 'click', '[data-ptb_icon_picker]', function (e) {
				e.preventDefault();
				PTB_Common.load_lightbox( () => {
					PTB_Common.show_icon_picker( this );
				} );
			} );
		}
	};

	if ( document.readyState === 'complete' ) {
		PTB_Common.init_icon_picker();
	} else {
		window.addEventListener( 'load', PTB_Common.init_icon_picker, { once: true, passive: true } );
	}
} )( jQuery );
