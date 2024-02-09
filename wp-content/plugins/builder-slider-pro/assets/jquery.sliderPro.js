/*!v1.2.1*/
;(( window, $ )=>{

	"use strict";

	// Static methods for Slider Pro
	$.SliderPro ={

		// List of added modules
		modules: [],

		// Add a module by extending the core prototype
		addModule( name, module ){
			this.modules.push( name );
			$.extend( SliderPro.prototype, module );
		}
	};

	// namespace
	const NS = $.SliderPro.namespace = 'SliderPro',

        SliderPro = function( instance, options ){

		// Reference to the slider instance
		this.instance = instance;

		// Reference to the slider jQuery element
		this.$slider = $( this.instance );

		// Reference to the slides (sp-slides) jQuery element
		this.$slides = null;

		// Reference to the mask (sp-mask) jQuery element
		this.$slidesMask = null;

		// Reference to the slides (sp-slides-container) jQuery element
		this.$slidesContainer = null;

		// Array of SliderProSlide objects, ordered by their DOM index
		this.slides = [];

		// Array of SliderProSlide objects, ordered by their left/top position in the slider.
		// This will be updated continuously if the slider is loopable.
		this.slidesOrder = [];

		// Holds the options passed to the slider when it was instantiated
		this.options = options;

		// Holds the final settings of the slider after merging the specified
		// ones with the default ones.
		this.settings ={};

		// Another reference to the settings which will not be altered by breakpoints or by other means
		this.originalSettings ={};

		// Reference to the original 'gotoSlide' method
		this.originalGotoSlide = null;

		// The index of the currently selected slide (starts with 0)
		this.selectedSlideIndex = 0;

		// The index of the previously selected slide
		this.previousSlideIndex = 0;

		// Indicates the position of the slide considered to be in the middle.
		// If there are 5 slides (0, 1, 2, 3, 4) the middle position will be 2.
		// If there are 6 slides (0, 1, 2, 3, 4, 5) the middle position will be approximated to 2.
		this.middleSlidePosition = 0;

		// Indicates the name of the CSS transition's complete event (i.e., transitionend, webkitTransitionEnd, etc.)
		this.transitionEvent = null;

		// Indicates the 'left' or 'top' position
		this.positionProperty = null;

		// The position of the slides container
		this.slidesPosition = 0;

		// The width of the individual slide
		this.slideWidth = 0;

		// The height of the individual slide
		this.slideHeight = 0;

		// The width or height, depending on the orientation, of the individual slide
		this.slideSize = 0;

		// Reference to the old slide width, used to check if the width has changed
		this.previousSlideWidth = 0;

		// Reference to the old slide height, used to check if the height has changed
		this.previousSlideHeight = 0;
		

		// The distance from the margin of the slider to the left/top of the selected slide.
		// This is useful in calculating the position of the selected slide when there are 
		// more visible slides.
		this.visibleOffset = 0;

		// Property used for deferring the resizing of the slider
		this.allowResize = true;

		// Unique ID to be used for event listening
		this.uniqueId = new Date().valueOf();

		// Stores size breakpoints
		this.breakpoints = [];

		// Indicates the current size breakpoint
		this.currentBreakpoint = -1;

		// Initialize the slider
		this._init();
	};

	SliderPro.prototype ={

		// The starting place for the slider
		_init(){
			this.transitionEvent = SliderProUtils.getTransitionEvent();

			// Add the 'ios' class if it's an iOS device
			if ( window.navigator.userAgent.match( /(iPad|iPhone|iPod)/g ) ){
				this.$slider.addClass( 'ios' );
			}
			// Set up the slides containers
			// slider-pro > sp-slides-container > sp-mask > sp-slides > sp-slide
			this.$slidesContainer =this.$slider.find( '.sp-slides-container' );
			this.$slidesMask = this.$slidesContainer.find( '.sp-mask' );
			this.$slides = this.$slidesMask.find( '.sp-slides' );
			
			const modules = $.SliderPro.modules;

			// Merge the modules' default settings with the core's default settings
			if ( typeof modules !== 'undefined' ){
				for ( let i = 0,len=modules.length; i < len; i++ ){
					let defaults = modules[ i ].substring( 0, 1 ).toLowerCase() + modules[ i ].substring( 1 ) + 'Defaults';

					if ( typeof this[ defaults ] !== 'undefined' ){
						$.extend( this.defaults, this[ defaults ] );
					}
				}
			}

			// Merge the specified setting with the default ones
			this.settings = $.extend({}, this.defaults, this.options );

			// Initialize the modules
			if ( typeof modules !== 'undefined' ){
				for (let i=modules.length-1; i>=0;i--){
					if ( typeof this[ 'init' + modules[ i ] ] !== 'undefined' ){
						this[ 'init' + modules[ i ] ]();
					}
				}
			}

			// Keep a reference of the original settings and use it
			// to restore the settings when the breakpoints are used.
			this.originalSettings = $.extend({}, this.settings );

			// Get the reference to the 'gotoSlide' method
			this.originalGotoSlide = this.gotoSlide;

			// Parse the breakpoints object and store the values into an array,
			// sorting them in ascending order based on the specified size.
			if ( this.settings.breakpoints !== null ){
				for ( let sizes in this.settings.breakpoints ){
					this.breakpoints.push({ size: parseInt( sizes, 10 ), properties:this.settings.breakpoints[ sizes ] });
				}

				this.breakpoints = this.breakpoints.sort(function( a, b ){
					return a.size >= b.size ? 1: -1;
				});
			}

			// Set which slide should be selected initially
			this.selectedSlideIndex = this.settings.startSlide;
			const __resize=e=>{
                if(this.$slides && this.$slides[0] && this.$slides[0].isConnected){
                    this.resize();
                }
                else{
                    Themify.off( 'tfsmartresize',__resize);
                }
            };
			// Resize the slider when the browser window resizes.
			// Also, deffer the resizing in order to not allow multiple
			Themify.on( 'tfsmartresize',__resize);

			// Resize the slider when the 'update' method is called.
			this.on( 'update.' + NS, ()=>{
				// Reset the previous slide width
				this.previousSlideWidth = 0;
				// Some updates might require a resize
				this.resize();
			});

			this.update();

			// add the 'sp-selected' class to the initially selected slide
			this.$slides.find( '.sp-slide' ).eq( this.selectedSlideIndex ).addClass( 'sp-selected' );

			// Fire the 'init' event
			this.trigger({ type: 'init' });
			if ( $.isFunction( this.settings.init ) ){
				this.settings.init.call( this,{ type: 'init' });
			}
		},
		// Update the slider by checking for setting changes and for slides
		// that weren't initialized yet.
		update(){
			const body=document.body;
			// Set the position that will be used to arrange elements, like the slides,
			// based on the orientation.
			this.positionProperty = this.settings.orientation === 'horizontal' ? 'left' : 'top';

			// Reset the 'gotoSlide' method
			this.gotoSlide = this.originalGotoSlide;

			// Loop through the array of SliderProSlide objects and if a stored slide is found
			// which is not in the DOM anymore, destroy that slide.
			for ( let i = this.slides.length - 1; i >= 0; i-- ){
				if ( !this.slides[ i ].$slide || !this.slides[ i ].$slide[0].isConntected){
					let slide = this.slides[ i ];
					slide.destroy();
					this.slides.splice( i, 1 );
				}
			}

			this.slidesOrder.length = 0;

			// Loop through the list of slides and initialize newly added slides if any,
			// and reset the index of each slide.
                        for(let slides = this.$slider[0].getElementsByClassName('sp-slide'),i=0,len=slides.length;i<len;++i){
                            if ( !slides[i].hasAttribute( 'data-init' ) || !slides[i].isConntected){
                                    this._createSlide( i, $(slides[i]) );
                            } else{
                                    this.slides[ i ].setIndex( i );
                            }

                            this.slidesOrder.push( i );
                        }

			// Calculate the position/index of the middle slide
			this.middleSlidePosition = parseFloat( ( this.slidesOrder.length - 1 ) / 2, 10 );

			// Arrange the slides in a loop
			if ( this.settings.loop === true ){
				this._updateSlidesOrder();
			}

			// Fire the 'update' event
			this.trigger({ type: 'update' });
			if ( $.isFunction( this.settings.update ) ){
				this.settings.update.call( this,{ type: 'update' } );
			}
		},
		// Create a SliderProSlide instance for the slide passed as a jQuery element
		_createSlide( index, element ){
			const slide = new SliderProSlide( $( element ), index, this.settings );

			this.slides.splice( index, 0, slide );
		},
		// Arrange the slide elements in a loop inside the 'slidesOrder' array
		_updateSlidesOrder(){
			let slicedItems,
                            i;

				// Calculate the distance between the selected element and the middle position
                        const distance = $.inArray( this.selectedSlideIndex, this.slidesOrder ) - this.middleSlidePosition;

			// If the distance is negative it means that the selected slider is before the middle position, so
			// slides from the end of the array will be added at the beginning, in order to shift the selected slide
			// forward.
			// 
			// If the distance is positive, slides from the beginning of the array will be added at the end.
                        if(distance!==0){
                            if ( distance < 0 ){
                                    slicedItems = this.slidesOrder.splice( distance, Math.abs( distance ) );

                                    for ( i = slicedItems.length - 1; i >-1; --i ){
                                            this.slidesOrder.unshift( slicedItems[ i ] );
                                    }
                            } else{
                                    slicedItems = this.slidesOrder.splice( 0, distance );
                                    for ( i = 0; i <= slicedItems.length - 1; ++i ){
                                            this.slidesOrder.push( slicedItems[ i ] );
                                    }
                            }
                        }
		},
		// Set the left/top position of the slides based on their position in the 'slidesOrder' array
		_updateSlidesPosition(){
			const selectedSlidePixelPosition = parseFloat( this.$slides.find( '.sp-slide' ).eq( this.selectedSlideIndex ).css( this.positionProperty ), 10 );

			for ( let slideIndex = 0; slideIndex < this.slidesOrder.length; slideIndex++ ){
				let slide = this.$slides.find( '.sp-slide' ).eq( this.slidesOrder[ slideIndex ] );
				slide.css( this.positionProperty, selectedSlidePixelPosition + ( slideIndex - this.middleSlidePosition  ) * ( this.slideSize + this.settings.slideDistance ) );
			}
		},
		// Set the left/top position of the slides based on their position in the 'slidesOrder' array,
		// and also set the position of the slides container.
		_resetSlidesPosition(){
			for ( let slideIndex = 0; slideIndex < this.slidesOrder.length; slideIndex++ ){
				this.$slides.find( '.sp-slide' ).eq( this.slidesOrder[ slideIndex ] ).css( this.positionProperty, slideIndex * ( this.slideSize + this.settings.slideDistance ) );
			}

			const newSlidesPosition = - parseFloat( this.$slides.find( '.sp-slide' ).eq( this.selectedSlideIndex ).css( this.positionProperty ), 10 ) + this.visibleOffset;
                   
			this._moveTo( newSlidesPosition, true );
		},
		// Called when the slider needs to resize
		resize(){
			// Check if the current window width is bigger than the biggest breakpoint
			// and if necessary reset the properties to the original settings.
			// 
			// If the window width is smaller than a certain breakpoint, apply the settings specified
			// for that breakpoint but only after merging them with the original settings
			// in order to make sure that only the specified settings for the breakpoint are applied
			if ( this.settings.breakpoints !== null && this.breakpoints.length > 0 ){
                                const w = $( window ).width();
				if ( w > this.breakpoints[ this.breakpoints.length - 1 ].size && this.currentBreakpoint !== -1 ){
					this.currentBreakpoint = -1;
					this._setProperties( this.originalSettings, false );
				} else{
					for ( let i = 0, n = this.breakpoints.length; i < n; i++ ){
						if ( w <= this.breakpoints[ i ].size ){
							if ( this.currentBreakpoint !== this.breakpoints[ i ].size ){
								let eventObject ={ type: 'breakpointReach', size: this.breakpoints[ i ].size, settings: this.breakpoints[ i ].properties };
								this.trigger( eventObject );
								if ( $.isFunction( this.settings.breakpointReach ) )
									this.settings.breakpointReach.call( this, eventObject );

								this.currentBreakpoint = this.breakpoints[ i ].size;
								let settings = $.extend({}, this.originalSettings, this.breakpoints[ i ].properties );
								this._setProperties( settings, false );
								
								return;
							}

							break;
						}
					}
				}
			}

			// Set the width of the main slider container based on whether or not the slider is responsive,
			// full width or full size
			if ( this.settings.responsive === true ){
				if ( ( this.settings.forceSize === 'fullWidth' || this.settings.forceSize === 'fullWindow' ) &&
					( this.settings.visibleSize === 'auto' || this.settings.visibleSize !== 'auto' && this.settings.orientation === 'vertical' )
				){
					this.$slider.css( 'margin', 0 ).css({ 'width': $( window ).width(), 'max-width': '', 'marginLeft': - this.$slider.offset().left });
				} else{
					this.$slider.css({ 'width': '100%', 'max-width': this.settings.width, 'marginLeft': '' });
				}
			} else{
				this.$slider.css({ 'width': this.settings.width });
			}
			
			// Calculate the aspect ratio of the slider
			if ( this.settings.aspectRatio === -1 ) {
				this.settings.aspectRatio = this.settings.width / this.settings.height;
			}

			// Initially set the slide width to the size of the slider.
			// Later, this will be set to less if there are multiple visible slides.
			this.slideWidth = this.$slider.width();

			// Set the height to the same size as the browser window if the slider is set to be 'fullWindow',
			// or calculate the height based on the width and the aspect ratio.
			if ( this.settings.forceSize === 'fullWindow' ){
				this.slideHeight ='100vh';
			} else{
				this.slideHeight = isNaN( this.settings.aspectRatio ) ? this.settings.height : this.slideWidth / this.settings.aspectRatio;
			}
			
			// Resize the slider only if the size of the slider has changed
			// If it hasn't, return.
			if ( this.previousSlideWidth !== this.slideWidth ||
				this.previousSlideHeight !== this.slideHeight ||
				this.settings.visibleSize !== 'auto' ||
				this.$slider.outerWidth() > this.$slider.parent().width() ||
				this.$slider.width() !== this.$slidesMask.width()
			){
				this.previousSlideWidth = this.slideWidth;
				this.previousSlideHeight = this.slideHeight;
			} else{
				return;
			}

			// The slide width or slide height is needed for several calculation, so create a reference to it
			// based on the current orientation.
			this.slideSize = this.settings.orientation === 'horizontal' ? this.slideWidth : this.slideHeight;
			
			// Initially set the visible size of the slides and the offset of the selected slide as if there is only
			// on visible slide.
			// If there will be multiple visible slides (when 'visibleSize' is different than 'auto'), these will
			// be updated accordingly.
			this.visibleSlidesSize = this.slideSize;
			this.visibleOffset = 0;

			// Loop through the existing slides and reset their size.
			$.each( this.slides, ( index, element )=>{
				element.setSize( this.slideWidth, this.slideHeight );
			});

			// Set the initial size of the mask container to the size of an individual slide
			this.$slidesMask.css({ 'width': this.slideWidth, 'height': this.slideHeight });

			// Adjust the height if it's set to 'auto'
			if ( this.settings.autoHeight === true ){

				// Delay the resizing of the height to allow for other resize handlers
				// to execute first before calculating the final height of the slide
				setTimeout( ()=>{
					this._resizeHeight();
				}, 1 );
			} else{
				this.$slidesMask.css( 'transition', '' );
			}

			// The 'visibleSize' option can be set to fixed or percentage size to make more slides
			// visible at a time.
			// By default it's set to 'auto'.
			if ( this.settings.visibleSize !== 'auto' ){
				if ( this.settings.orientation === 'horizontal' ){

					// If the size is forced to full width or full window, the 'visibleSize' option will be
					// ignored and the slider will become as wide as the browser window.
					if ( this.settings.forceSize === 'fullWidth' || this.settings.forceSize === 'fullWindow' ){
						this.$slider.css({ 'margin':0, 'width': $( window ).width(), 'max-width': '', 'marginLeft': - this.$slider.offset().left });
					} else{
						this.$slider.css({ 'width': this.settings.visibleSize, 'max-width': '100%', 'marginLeft': 0 });
					}
					
					this.$slidesMask.css( 'width', this.$slider.width() );

					this.visibleSlidesSize = this.$slidesMask.width();
					this.visibleOffset = Math.round( ( this.$slider.width() - this.slideWidth ) / 2 );
				} else{

					// If the size is forced to full window, the 'visibleSize' option will be
					// ignored and the slider will become as high as the browser window.
					if ( this.settings.forceSize === 'fullWindow' ){
						this.$slider.css({ 'height': $( window ).height(), 'max-height': '' });
					} else{
						this.$slider.css({ 'height': this.settings.visibleSize, 'max-height': '100%' });
					}

					this.$slidesMask.css( 'height', this.$slider.height() );

					this.visibleSlidesSize = this.$slidesMask.height();
					this.visibleOffset = Math.round( ( this.$slider.height() - this.slideHeight ) / 2 );
				}
			}

			this._resetSlidesPosition();

			// Fire the 'sliderResize' event
			this.trigger({ type: 'sliderResize' });
			if ( $.isFunction( this.settings.sliderResize ) ){
				this.settings.sliderResize.call( this,{ type: 'sliderResize' });
			}
		},
		// Resize the height of the slider to the height of the selected slide.
		// It's used when the 'autoHeight' option is set to 'true'.
		_resizeHeight(){
			const that = this,
                            selectedSlide = this.getSlideAt( this.selectedSlideIndex );
                        let size = selectedSlide.getSize();

			selectedSlide.off( 'imagesLoaded.' + NS )
                        .on( 'imagesLoaded.' + NS, function( event ){
				if ( event.index === that.selectedSlideIndex ){
                                        size = selectedSlide.getSize();
					that._resizeHeightTo( size.height );
				}
			});

			// If the selected slide contains images which are still loading,
			// wait for the loading to complete and then request the size again.
			if ( size !== 'loading' ){
				this._resizeHeightTo( size.height );
			}
		},
		// Open the slide at the specified index
		gotoSlide( index ){
			if ( index === this.selectedSlideIndex || typeof this.slides[ index ] === 'undefined' ){
				return;
			}

			this.previousSlideIndex = this.selectedSlideIndex;
			this.selectedSlideIndex = index;

			// Re-assign the 'sp-selected' class to the currently selected slide
			this.$slides.find( '.sp-selected' ).removeClass( 'sp-selected' );
			this.$slides.find( '.sp-slide' ).eq( this.selectedSlideIndex ).addClass( 'sp-selected' );

			// If the slider is loopable reorder the slides to have the selected slide in the middle
			// and update the slides' position.
			if ( this.settings.loop === true ){
				this._updateSlidesOrder();
				this._updateSlidesPosition();
			}

			// Adjust the height of the slider
			if ( this.settings.autoHeight === true ){
				this._resizeHeight();
			}
                        
			// Calculate the new position that the slides container need to take
			const  newSlidesPosition = - parseFloat( this.$slides.find( '.sp-slide' ).eq( this.selectedSlideIndex ).css( this.positionProperty ), 10 ) + this.visibleOffset;

			// Move the slides container to the new position
			this._moveTo( newSlidesPosition, false, ()=>{
				if ( this.settings.loop === true ){
					this._resetSlidesPosition();
				}

				// Fire the 'gotoSlideComplete' event
				this.trigger({ type: 'gotoSlideComplete', index: index, previousIndex: this.previousSlideIndex });
				if ( $.isFunction( this.settings.gotoSlideComplete ) ){
					this.settings.gotoSlideComplete.call( this,{ type: 'gotoSlideComplete', index: index, previousIndex: this.previousSlideIndex } );
				}
			});

			// Fire the 'gotoSlide' event
			this.trigger({ type: 'gotoSlide', index: index, previousIndex: this.previousSlideIndex });
			if ( $.isFunction( this.settings.gotoSlide ) ){
				this.settings.gotoSlide.call( this,{ type: 'gotoSlide', index: index, previousIndex: this.previousSlideIndex } );
			}
		},
		// Open the next slide
		nextSlide(){
			const index = ( this.selectedSlideIndex >= this.getTotalSlides() - 1 ) ? 0 : ( this.selectedSlideIndex + 1 );
			this.gotoSlide( index );
		},
		// Open the previous slide
		previousSlide(){
			const index = this.selectedSlideIndex <= 0 ? ( this.getTotalSlides() - 1 ) : ( this.selectedSlideIndex - 1 );
			this.gotoSlide( index );
		},
		// Move the slides container to the specified position.
		// The movement can be instant or animated.
		_moveTo( position, instant, callback ){
			

			if ( position === this.slidesPosition ){
				return;
			}
			this.slidesPosition = position;
			const left = this.settings.orientation === 'horizontal' ? position : 0,
                    top = this.settings.orientation === 'horizontal' ? 0 : position,
                    css ={'transform':'translate3d(' + left + 'px,' + top + 'px,0)'};

                    let transition;

                    if ( typeof instant !== 'undefined' && instant === true ){
                            transition = '';
                    } 
                    else{
                            transition = 'transform ' + this.settings.slideAnimationDuration / 1000 + 's';
                            const trEnd=e=>{
                                const sl=e.currentTarget;
                                if ( e.target !== sl ){
                                        return;
                                }
                                sl.removeEventListener(e.type,trEnd);
                                sl.classList.remove( 'sp-animated' );

                                if ( typeof callback === 'function' ){
                                        callback();
                                }
                            };
                            this.$slides[0].addEventListener(this.transitionEvent,trEnd,{passive:true});
                            this.$slides[0].classList.add( 'sp-animated' )
                    }

                    css[ 'transition' ] = transition;

                    this.$slides.css( css );
			
		},
		// Stop the movement of the slides
		_stopMovement(){
			const // Get the current position of the slides by parsing the 'transform' property
                            matrixString = this.$slides.css( 'transform' ),
                            matrixType = matrixString.indexOf( 'matrix3d' ) !== -1 ? 'matrix3d' : 'matrix',
                            matrixArray = matrixString.replace( matrixType, '' ).match( /-?[0-9\.]+/g ),
                            left = matrixType === 'matrix3d' ? parseFloat( matrixArray[ 12 ], 10 ) : parseFloat( matrixArray[ 4 ], 10 ),
                            top = matrixType === 'matrix3d' ? parseFloat( matrixArray[ 13 ], 10 ) : parseFloat( matrixArray[ 5 ], 10 ),
                            css ={'transition':'','transform':'translate3d(' + left + 'px, ' + top + 'px, 0)'}; // Set the transform property to the value that the transform had when the function was called
			
                            this.slidesPosition = this.settings.orientation === 'horizontal' ? left : top;
                            this.$slides.css( css ).off( this.transitionEvent ).removeClass( 'sp-animated' );
		},
		// Resize the height of the slider to the specified value
		_resizeHeightTo( height ){
                const css ={ 'height': height,'transition':('height ' + this.settings.heightAnimationDuration / 1000 + 's')};
                
				this.$slidesMask.off( this.transitionEvent )
                                .on( this.transitionEvent, e=>{
					if ( e.target !== e.currentTarget ){
						return;
					}

					$(e.currentTarget).off( e.type );

					// Fire the 'resizeHeightComplete' event
					this.trigger({ type: 'resizeHeightComplete' });
					if ( $.isFunction( this.settings.resizeHeightComplete ) ){
						this.settings.resizeHeightComplete.call( this,{ type: 'resizeHeightComplete' } );
					}
				}).css( css );
			
		},
		// Destroy the slider instance
		destroy(){
			// Remove the stored reference to this instance
			this.$slider.removeData( 'sliderPro' ).removeAttr( 'style' );
			// Clean the CSS
			this.$slides.removeAttr( 'style' );

			// Remove event listeners
			this.off( 'update.' + NS );

			// Destroy modules
			const modules = $.SliderPro.modules;

			if ( typeof modules !== 'undefined' ){
				for ( let i = 0; i < modules.length; i++ ){
					if ( typeof this[ 'destroy' + modules[ i ] ] !== 'undefined' ){
						this[ 'destroy' + modules[ i ] ]();
					}
				}
			}

			// Destroy all slides
			$.each( this.slides, function( index, element ){
				element.destroy();
			});

			this.slides.length = 0;

			// Move the slides to their initial position in the DOM and 
			// remove the container elements created dynamically.
			this.$slides.prependTo( this.$slider );
			this.$slidesContainer.remove();
		},
		// Set properties on runtime
		_setProperties( properties, store ){
			// Parse the properties passed as an object
			for ( let prop in properties ){
				this.settings[ prop ] = properties[ prop ];

				// Alter the original settings as well unless 'false' is passed to the 'store' parameter
				if ( store !== false ){
					this.originalSettings[ prop ] = properties[ prop ];
				}
			}

			this.update();
		},
		// Attach an event handler to the slider
		on( type, callback ){
			return this.$slider.on( type, callback );
		},
		// Detach an event handler
		off( type ){
			return this.$slider.off( type );
		},
		// Trigger an event on the slider
		trigger( data ){
			return this.$slider.triggerHandler( data );
		},
		// Return the slide at the specified index
		getSlideAt( index ){
			return this.slides[ index ];
		},
		// Return the index of the currently opened slide
		getSelectedSlide(){
			return this.selectedSlideIndex;
		},
		// Return the total amount of slides
		getTotalSlides(){
			return this.slides.length;
		},
		// The default options of the slider
		defaults:{
			// Width of the slide
			width: 500,

			// Height of the slide
			height: 300,

			// Indicates if the slider is responsive
			responsive: true,

			// The aspect ratio of the slider (width/height)
			aspectRatio: -1,

			// The scale mode for images (cover, contain, exact and none)
			imageScaleMode: 'cover',

			// Indicates if the image will be centered
			centerImage: true,

			// Indicates if height of the slider will be adjusted to the
			// height of the selected slide
			autoHeight: false,

			// Indicates the initially selected slide
			startSlide: 0,

			// Indicates whether the slides will be arranged horizontally
			// or vertically. Can be set to 'horizontal' or 'vertical'.
			orientation: 'horizontal',

			// Indicates if the size of the slider will be forced to 'fullWidth' or 'fullWindow'
			forceSize: 'none',

			// Indicates if the slider will be loopable
			loop: true,

			// The distance between slides
			slideDistance: 10,

			// The duration of the slide animation
			slideAnimationDuration: 700,

			// The duration of the height animation
			heightAnimationDuration: 700,

			// Sets the size of the visible area, allowing the increase of it in order
			// to make more slides visible.
			// By default, only the selected slide will be visible. 
			visibleSize: 'auto',

			// Breakpoints for allowing the slider's options to be changed
			// based on the size of the window.
			breakpoints: null,

			// Called when the slider is initialized
			init(){},

			// Called when the slider is updates
			update(){},

			// Called when the slider is resized
			sliderResize(){},

			// Called when a new slide is selected
			gotoSlide(){},

			// Called when the navigation to the newly selected slide is complete
			gotoSlideComplete(){},

			// Called when the height animation of the slider is complete
			resizeHeightComplete(){},

			// Called when a breakpoint is reached
			breakpointReach(){}
		}
	};

	const SliderProSlide = function( slide, index, settings ){

		// Reference to the slide jQuery element
		this.$slide = slide;

		// Reference to the main slide image
		this.$mainImage = null;

		// Reference to the container that will hold the main image
		this.$imageContainer = null;


		// Indicates whether the main image is loaded
		this.isMainImageLoaded = false;

		// Indicates whether the main image is in the process of being loaded
		this.isMainImageLoading = false;

		// Indicates whether the slide has any image. There could be other images (i.e., in layers)
		// besides the main slide image.
		this.hasImages = false;

		// Indicates if all the images in the slide are loaded
		this.areImagesLoaded = false;

		// The width and height of the slide
		this.width = 0;
		this.height = 0;

		// Reference to the global settings of the slider
		this.settings = settings;

		// Set the index of the slide
		this.setIndex( index );

		// Initialize the slide
		this._init();
	};

	SliderProSlide.prototype ={

		// The starting point for the slide
		_init(){
			// Mark the slide as initialized
			this.$slide.attr( 'data-init', true );
			this.hasImages = this.$slide[0].getElementsByTagName ( 'img' )[0]!==undefined;
		},
		// Set the size of the slide
		setSize( width, height ){
			
			this.width = width;
			this.height = this.settings.autoHeight === true ? 'auto' : height;

			this.$slide.css({
				'width': this.width,
				'height': this.height
			});

		},
		// Get the size (width and height) of the slide
		getSize(){
			let size;

			// Check if all images have loaded, and if they have, return the size, else, return 'loading'
			if ( this.hasImages === true && this.areImagesLoaded === false && typeof this.$slide.attr( 'data-loading' ) === 'undefined' ){
				this.$slide.attr( 'data-loading', true );
                                const that=this,
                                status = SliderProUtils.checkImagesComplete( this.$slide, function(){
					that.areImagesLoaded = true;
					that.$slide.removeAttr( 'data-loading' );
					that.trigger({ type: 'imagesLoaded.' + NS, index: that.index });
				});

				if ( status === 'complete' ){
					size = this.calculateSize();

					return{
						'width': size.width,
						'height': size.height
					};
				} else{
					return 'loading';
				}
			} else{
				size = this.calculateSize();

				return{
					'width': size.width,
					'height': size.height
				};
			}
		},
		// Calculate the width and height of the slide by going
		// through all the child elements and measuring their 'bottom'
		// and 'right' properties. The element with the biggest
		// 'right'/'bottom' property will determine the slide's
		// width/height.
		calculateSize(){
			let width = this.$slide.width(),
				height = this.$slide.height();

			this.$slide.children().each(function( index, element ){
				let child = $( element );

				if ( child.is( ':hidden' ) === true ){
					return;
				}

				let rect = element.getBoundingClientRect(),
                                    bottom = child.position().top + ( rect.bottom - rect.top ),
                                    right = child.position().left + ( rect.right - rect.left );

				if ( bottom > height ){
					height = bottom;
				}

				if ( right > width ){
					width = right;
				}
			});

			return{ width: width, height: height };
		},
		// Resize the main image.
		// 
		// Call this when the slide resizes or when the main image has changed to a different image.
		resizeMainImage( isNewImage ){
			// If the main image has changed, reset the 'flags'
			if ( isNewImage === true ){
				this.isMainImageLoaded = false;
				this.isMainImageLoading = false;
			}
			// If the image was not loaded yet and it's not in the process of being loaded, load it
			if ( this.isMainImageLoaded === false && this.isMainImageLoading === false ){
				this.isMainImageLoading = true;
                                const that = this;
				SliderProUtils.checkImagesComplete( this.$mainImage, function(){
					that.isMainImageLoaded = true;
					that.isMainImageLoading = false;
					that.resizeMainImage();
					that.trigger({ type: 'imagesLoaded.' + NS, index: that.index });
				});

				return;
			}

			// After the main image has loaded, resize it
			if ( this.settings.autoHeight === true ){
				this.$mainImage.css({ width: '100%', height: 'auto', 'marginLeft': '', 'marginTop': '' });
			} else{
				if ( this.settings.imageScaleMode === 'cover' ){
					if ( this.$mainImage.width() / this.$mainImage.height() <= this.width / this.height ){
						this.$mainImage.css({ width: '100%', height: 'auto' });
					} else{
						this.$mainImage.css({ width: 'auto', height: '100%' });
					}
				} else if ( this.settings.imageScaleMode === 'contain' ){
					if ( this.$mainImage.width() / this.$mainImage.height() >= this.width / this.height ){
						this.$mainImage.css({ width: '100%', height: 'auto' });
					} else{
						this.$mainImage.css({ width: 'auto', height: '100%' });
					}
				} else if ( this.settings.imageScaleMode === 'exact' ){
					this.$mainImage.css({ width: '100%', height: '100%' });
				}

				if ( this.settings.centerImage === true ){
					this.$mainImage.css({ 'marginLeft': ( this.$imageContainer.width() - this.$mainImage.width() ) * 0.5, 'marginTop': ( this.$imageContainer.height() - this.$mainImage.height() ) * 0.5 });
				}
			}
		},
		// Destroy the slide
		destroy(){
			// Clean the slide element from attached styles and data
			this.$slide.removeAttr( 'style data-init data-index data-loaded' );

		},
		// Return the index of the slide
		getIndex(){
			return this.index;
		},
		// Set the index of the slide
		setIndex( index ){
			this.index = index;
			this.$slide.attr( 'data-index', this.index );
		},
		// Attach an event handler to the slide
		on( type, callback ){
			return this.$slide.on( type, callback );
		},
		// Detach an event handler to the slide
		off( type ){
			return this.$slide.off( type );
		},
		// Trigger an event on the slide
		trigger( data ){
			return this.$slide.triggerHandler( data );
		}
	};

	window.SliderPro = SliderPro;
	window.SliderProSlide = SliderProSlide;

	$.fn.sliderPro = function( options ){
		const args = Array.prototype.slice.call( arguments, 1 );

		return this.each(function(){
			// Instantiate the slider or alter it
			if ( typeof $( this ).data( 'sliderPro' ) === 'undefined' ){
				let newInstance = new SliderPro( this, options );

				// Store a reference to the instance created
				$( this ).data( 'sliderPro', newInstance );
			} else if ( typeof options !== 'undefined' ){
				let currentInstance = $( this ).data( 'sliderPro' );

				// Check the type of argument passed
				if ( typeof currentInstance[ options ] === 'function' ){
					currentInstance[ options ].apply( currentInstance, args );
				} else if ( typeof currentInstance.settings[ options ] !== 'undefined' ){
					let obj ={};
					obj[ options ] = args[ 0 ];
					currentInstance._setProperties( obj );
				} else if ( typeof options === 'object' ){
					currentInstance._setProperties( options );
				} else{
					$.error( options + ' does not exist in sliderPro.' );
				}
			}
		});
	};

	// Contains useful utility functions
	const SliderProUtils ={
		// Check the name of the transition's complete event in the current browser
		getTransitionEvent(){
			return 'transitionend';
		},
		// If a single image is passed, check if it's loaded.
		// If a different element is passed, check if there are images
		// inside it, and check if these images are loaded.
		checkImagesComplete( target, callback ){
			const that = this;

				// Check the initial status of the image(s)
                        let status = this.checkImagesStatus( target );

			// If there are loading images, wait for them to load.
			// If the images are loaded, call the callback function directly.
			if ( status === 'loading' ){
				const checkImages = setInterval(function(){
					status = that.checkImagesStatus( target );

					if ( status === 'complete' ){
						clearInterval( checkImages );

						if ( typeof callback === 'function' ){
							callback();
						}
					}
				}, 100 );
			} else if ( typeof callback === 'function' ){
				callback();
			}

			return status;
		},
		checkImagesStatus( target ){
			let status = 'complete';

			if ( target.is( 'img' ) && target[0].complete === false ){
				status = 'loading';
			} else{
				target.find( 'img' ).each(function( i ){
					let image = $( this )[0];

					if ( image.complete === false ){
						status = 'loading';
					}
				});
			}

			return status;
		}
	};

	window.SliderProUtils = SliderProUtils;

})( window, jQuery );

// Lazy Loading module for Slider Pro.
// 
// Adds the possibility to delay the loading of the images until the slides/thumbnails
// that contain them become visible. This technique improves the initial loading
// performance.
;(function( $ ){

	"use strict";

	const NS = 'LazyLoading.' + $.SliderPro.namespace,

        LazyLoading ={
		allowLazyLoadingCheck: true,
		initLazyLoading(){
			// The 'resize' event is fired after every update, so it's possible to use it for checking
			// if the update made new slides become visible
			// 
			// Also, resizing the slider might make new slides or thumbnails visible
			this.on( 'sliderResize.' + NS, this._lazyLoadingOnResize.bind(this))

			// Check visible images when a new slide is selected
			.on( 'gotoSlide.' + NS, this._checkAndLoadVisibleImages.bind(this))

			// Check visible thumbnail images when the thumbnails are updated because new thumbnail
			// might have been added or the settings might have been changed so that more thumbnail
			// images become visible
			// 
			// Also, check visible thumbnail images after the thumbnails have moved because new thumbnails might
			// have become visible
			.on( 'thumbnailsUpdate.' + NS + ' ' + 'thumbnailsMoveComplete.' + NS,  this._checkAndLoadVisibleThumbnailImages.bind(this));
		},
		_lazyLoadingOnResize(){

			if ( this.allowLazyLoadingCheck === false ){
				return;
			}
                        
			const that = this;
			this.allowLazyLoadingCheck = false;
			
			this._checkAndLoadVisibleImages();

			if ( this.$slider.find( '.sp-thumbnail' ).length !== 0 ){
				this._checkAndLoadVisibleThumbnailImages();
			}

			// Use a timer to deffer the loading of images in order to prevent too many
			// checking attempts
			setTimeout(function(){
				that.allowLazyLoadingCheck = true;
			}, 500 );
		},
		// Check visible slides and load their images
		_checkAndLoadVisibleImages(){
			if ( this.$slider.find( '.sp-slide:not([ data-loaded ])' ).length === 0 ){
				return;
			}
			const that = this,

				// Use either the middle position or the index of the selected slide as a reference, depending on
				// whether the slider is loopable
				referencePosition = this.settings.loop === true ? this.middleSlidePosition : this.selectedSlideIndex,

				// Calculate how many slides are visible at the sides of the selected slide
				visibleOnSides = Math.ceil( ( this.visibleSlidesSize - this.slideSize ) / 2 / this.slideSize ),

				// Calculate the indexes of the first and last slide that will be checked
				from = referencePosition - visibleOnSides - 1 > 0 ? referencePosition - visibleOnSides - 1 : 0,
				to = referencePosition + visibleOnSides + 1 < this.getTotalSlides() - 1 ? referencePosition + visibleOnSides + 1 : this.getTotalSlides() - 1,
				
				// Get all the slides that need to be checked
				slidesToCheck = this.slidesOrder.slice( from, to + 1 );

			// Loop through the selected slides and if the slide is not marked as having
			// been loaded yet, loop through its images and load them.
			$.each( slidesToCheck, function( index, element ){
				let slide = that.slides[ element ],
					$slide = slide.$slide;

				if ( typeof $slide.attr( 'data-loaded' ) === 'undefined' ){
					$slide.attr( 'data-loaded', true )
                                        .find( 'img[ data-src ]' ).each(function(){
						that._loadImage( $( this ), function( newImage ){
							if ( newImage.hasClass( 'sp-image' ) ){
								slide.$mainImage = newImage;
								slide.resizeMainImage( true );
							}
						});
					});
				}
			});
		},
		// Check visible thumbnails and load their images
		_checkAndLoadVisibleThumbnailImages(){
			if ( this.$slider.find( '.sp-thumbnail-container:not([ data-loaded ])' ).length === 0 ){
				return;
			}

			const that = this,
				thumbnailSize = this.thumbnailsSize / this.thumbnails.length,

				// Calculate the indexes of the first and last thumbnail that will be checked
				from = Math.floor( Math.abs( this.thumbnailsPosition / thumbnailSize ) ),
				to = Math.floor( ( - this.thumbnailsPosition + this.thumbnailsContainerSize ) / thumbnailSize ),

				// Get all the thumbnails that need to be checked
				thumbnailsToCheck = this.thumbnails.slice( from, to + 1 );

			// Loop through the selected thumbnails and if the thumbnail is not marked as having
			// been loaded yet, load its image.
			$.each( thumbnailsToCheck, function( index, element ){
				let $thumbnailContainer = element.$thumbnailContainer;

				if ( typeof $thumbnailContainer.attr( 'data-loaded' ) === 'undefined' ){
					$thumbnailContainer.attr( 'data-loaded', true )
                                        .find( 'img[ data-src ]' ).each(function(){
						that._loadImage(  $( this ), function(){
							element.resizeImage();
						});
					});
				}
			});
		},
		// Load an image
		_loadImage( image, callback ){
			// Create a new image element
			const newImage = $( new Image() );

			// Copy the class(es) and inline style
			newImage.attr({ 'class':image.attr( 'class' ), 'style':image.attr( 'style' ) } );

			// Copy the data attributes
			$.each( image.data(), function( name, value ){
				newImage.attr( 'data-' + name, value );
			});

			// Copy the width and height attributes if they exist
			if ( typeof image.attr( 'width' ) !== 'undefined'){
				newImage.attr( 'width', image.attr( 'width' ) );
			}

			if ( typeof image.attr( 'height' ) !== 'undefined'){
				newImage.attr( 'height', image.attr( 'height' ) );
			}

			if ( typeof image.attr( 'alt' ) !== 'undefined' ){
				newImage.attr( 'alt', image.attr( 'alt' ) );
			}

			if ( typeof image.attr( 'title' ) !== 'undefined' ){
				newImage.attr( 'title', image.attr( 'title' ) );
			}

			// Assign the source of the image
			newImage.attr( 'src', image.attr( 'data-src' ) ).removeAttr( 'data-src' );

			// Add the new image in the same container and remove the older image
			newImage.insertAfter( image );
			image.remove();
			image = null;
			
			if ( typeof callback === 'function' ){
				callback( newImage );
			}
		},
		// Destroy the module
		destroyLazyLoading(){
			this.off( 'update.' + NS+' gotoSlide.' + NS+ ' sliderResize.' + NS+' thumbnailsUpdate.' + NS+ ' thumbnailsMoveComplete.' + NS  );
		}
	};

	$.SliderPro.addModule( 'LazyLoading', LazyLoading );

})( jQuery );

// Layers module for Slider Pro.
// 
// Adds support for animated and static layers. The layers can contain any content,
// from simple text for video elements.
;(function( window, $ ){

	"use strict";

	const NS = 'Layers.' +  $.SliderPro.namespace,

        Layers ={
		// Reference to the original 'gotoSlide' method
		layersGotoSlideReference: null,
		// Reference to the timer that will delay the overriding
		// of the 'gotoSlide' method
		waitForLayersTimer: null,
		initLayers(){
			this.on( 'update.' + NS, this._layersOnUpdate.bind(this))
                            .on( 'sliderResize.' + NS, this._layersOnResize.bind(this))
                            .on( 'gotoSlide.' + NS, this._layersOnGotoSlide.bind(this));
		},
		// Loop through the slides and initialize all layers
		_layersOnUpdate( e ){
			const that = this;

			$.each( this.slides, function( index, element ){
				let $slide = element.$slide;

				// Initialize the layers
				this.$slide.find( '.sp-layer:not([ data-layer-init ])' ).each(function(){
					let layer = new Layer( $( this ) );

					// Add the 'layers' array to the slide objects (instance of SliderProSlide)
					if ( typeof element.layers === 'undefined' ){
						element.layers = [];
					}

					element.layers.push( layer );

					if ( $( this ).hasClass( 'sp-static' ) === false ){

						// Add the 'animatedLayers' array to the slide objects (instance of SliderProSlide)
						if ( typeof element.animatedLayers === 'undefined' ){
							element.animatedLayers = [];
						}

						element.animatedLayers.push( layer );
					}
				});
			});

			// If the 'waitForLayers' option is enabled, the slider will not move to another slide
			// until all the layers from the previous slide will be hidden. To achieve this,
			// replace the current 'gotoSlide' function with another function that will include the 
			// required functionality.
			// 
			// Since the 'gotoSlide' method might be overridden by other modules as well, delay this
			// override to make sure it's the last override.
			if ( this.settings.waitForLayers === true ){
				clearTimeout( this.waitForLayersTimer );

				this.waitForLayersTimer = setTimeout(function(){
					that.layersGotoSlideReference = that.gotoSlide;
					that.gotoSlide = that._layersGotoSlide;
				}, 1 );
			}
		},
		// When the slider resizes, try to scale down the layers proportionally. The automatic scaling
		// will make use of an option, 'autoScaleReference', by comparing the current width of the slider
		// with the reference width. So, if the reference width is 1000 pixels and the current width is
		// 500 pixels, it means that the layers will be scaled down to 50% of their size.
		_layersOnResize(){

			if ( this.settings.autoScaleLayers === false ){
				// Show the layers for the initial slide
				this.showLayers( this.selectedSlideIndex );
				
				return;
			}
                        let autoScaleReference,
                            useAutoScale = this.settings.autoScaleLayers;
			// If there isn't a reference for how the layers should scale down automatically, use the 'width'
			// option as a reference, unless the width was set to a percentage. If there isn't a set reference and
			// the width was set to a percentage, auto scaling will not be used because it's not possible to
			// calculate how much should the layers scale.
			if ( this.settings.autoScaleReference === -1 ){
				if ( typeof this.settings.width === 'string' && this.settings.width.indexOf( '%' ) !== -1 ){
					useAutoScale = false;
				} else{
					autoScaleReference = parseFloat( this.settings.width, 10 );
				}
			} else{
				autoScaleReference = this.settings.autoScaleReference;
			}

			const scaleRatio = (useAutoScale === true && this.slideWidth < autoScaleReference)?(this.slideWidth / autoScaleReference):1;

			$.each( this.slides, function( index, slide ){
				if ( typeof slide.layers !== 'undefined' ){
					$.each( slide.layers, function( index, layer ){
						layer.scale( scaleRatio );
					});
				}
			});

			// Show the layers for the initial slide
			this.showLayers( this.selectedSlideIndex );
		},
		// Replace the 'gotoSlide' method with this one, which makes it possible to 
		// change the slide only after the layers from the previous slide are hidden.
		_layersGotoSlide( index ){
			const that = this,
				animatedLayers = this.slides[ this.selectedSlideIndex ].animatedLayers;

			// If the slider is dragged, don't wait for the layer to hide
			if ( this.$slider.hasClass( 'sp-swiping' ) || typeof animatedLayers === 'undefined' || animatedLayers.length === 0  ){
				this.layersGotoSlideReference( index );
			} else{
				this.on( 'hideLayersComplete.' + NS, function(){
					that.off( 'hideLayersComplete.' + NS ).layersGotoSlideReference( index );
				});

				this.hideLayers( this.selectedSlideIndex );
			}
		},
		// When a new slide is selected, hide the layers from the previous slide
		// and show the layers from the current slide.
		_layersOnGotoSlide( e ){
			if ( this.previousSlideIndex !== this.selectedSlideIndex &&  this.settings.waitForLayers === false ){
				this.hideLayers( this.previousSlideIndex );
			}

			this.showLayers( this.selectedSlideIndex );
		},
		// Show the animated layers from the slide at the specified index,
		// and fire an event when all the layers from the slide become visible.
		showLayers( index ){
			const that = this,
                        animatedLayers = this.slides[ index ].animatedLayers;
                        let layerCounter = 0;

			if ( typeof animatedLayers === 'undefined' ){
				return;
			}

			$.each( animatedLayers, function( index, element ){

				// If the layer is already visible, increment the counter directly, else wait 
				// for the layer's showing animation to complete.
				if ( element.isVisible() === true ){
					++layerCounter;

					if ( layerCounter === animatedLayers.length ){
						that.trigger({ type: 'showLayersComplete', index: index });
						if ( $.isFunction( that.settings.showLayersComplete ) ){
							that.settings.showLayersComplete.call( that,{ type: 'showLayersComplete', index: index });
						}
					}
				} else{
					element.show(function(){
						++layerCounter;

						if ( layerCounter === animatedLayers.length ){
							that.trigger({ type: 'showLayersComplete', index: index });
							if ( $.isFunction( that.settings.showLayersComplete ) ){
								that.settings.showLayersComplete.call( that,{ type: 'showLayersComplete', index: index });
							}
						}
					});
				}
			});
		},
		// Hide the animated layers from the slide at the specified index,
		// and fire an event when all the layers from the slide become invisible.
		hideLayers( index ){
			const that = this,
                            animatedLayers = this.slides[ index ].animatedLayers;
                        let layerCounter = 0;

			if ( typeof animatedLayers === 'undefined' ){
				return;
			}

			$.each( animatedLayers, function( index, element ){

				// If the layer is already invisible, increment the counter directly, else wait 
				// for the layer's hiding animation to complete.
				if ( element.isVisible() === false ){
					++layerCounter;

					if ( layerCounter === animatedLayers.length ){
						that.trigger({ type: 'hideLayersComplete', index: index });
						if ( $.isFunction( that.settings.hideLayersComplete ) ){
							that.settings.hideLayersComplete.call( that,{ type: 'hideLayersComplete', index: index });
						}
					}
				} else{
					element.hide(function(){
						++layerCounter;

						if ( layerCounter === animatedLayers.length ){
							that.trigger({ type: 'hideLayersComplete', index: index });
							if ( $.isFunction( that.settings.hideLayersComplete ) ){
								that.settings.hideLayersComplete.call( that,{ type: 'hideLayersComplete', index: index });
							}
						}
					});
				}
			});
		},
		// Destroy the module
		destroyLayers(){
			this.off( 'update.' + NS+ ' resize.' + NS +' gotoSlide.' + NS+' hideLayersComplete.' + NS  );
		},
		layersDefaults:{
			// Indicates whether the slider will wait for the layers to disappear before
			// going to a new slide
			waitForLayers: false,
			// Indicates whether the layers will be scaled automatically
			autoScaleLayers: true,
			// Sets a reference width which will be compared to the current slider width
			// in order to determine how much the layers need to scale down. By default,
			// the reference width will be equal to the slide width. However, if the slide width
			// is set to a percentage value, then it's necessary to set a specific value for 'autoScaleReference'.
			autoScaleReference: -1,
			// Called when all animated layers become visible
			showLayersComplete(){},
			// Called when all animated layers become invisible
			hideLayersComplete(){}
		}
	};

	// Override the slide's 'destroy' method in order to destroy the 
	// layers that where added to the slide as well.
	const slideDestroy = window.SliderProSlide.prototype.destroy;

	window.SliderProSlide.prototype.destroy = function(){
		if ( typeof this.layers !== 'undefined' ){
			$.each( this.layers, function( index, element ){
				element.destroy();
			});

			this.layers.length = 0;
		}

		if ( typeof this.animatedLayers !== 'undefined' ){
			this.animatedLayers.length = 0;
		}

		slideDestroy.apply( this );
	};

	const Layer = function( layer ){

		// Reference to the layer jQuery element
		this.$layer = layer;

		// Indicates whether a layer is currently visible or hidden
		this.visible = false;

		// Indicates whether the layer was styled
		this.styled = false;

		// Holds the data attributes added to the layer
		this.data = null;

		// Indicates the layer's reference point (topLeft, bottomLeft, topRight or bottomRight)
		this.position = null;
		
		// Indicates which CSS property (left or right) will be used for positioning the layer 
		this.horizontalProperty = null;
		
		// Indicates which CSS property (top or bottom) will be used for positioning the layer 
		this.verticalProperty = null;

		// Indicates the value of the horizontal position
		this.horizontalPosition = null;
		
		// Indicates the value of the vertical position
		this.verticalPosition = null;

		// Indicates how much the layers needs to be scaled
		this.scaleRatio = 1;

		// Indicates the name of the CSS transition's complete event (i.e., transitionend, webkitTransitionEnd, etc.)
		this.transitionEvent = SliderProUtils.getTransitionEvent();

		// Reference to the timer that will be used to hide the layers automatically after a given time interval
		this.stayTimer = null;

		this._init();
	};

	Layer.prototype ={

		// Initialize the layers
		_init(){
			this.$layer.attr( 'data-layer-init', true );

			if ( this.$layer.hasClass( 'sp-static' ) ){
				this._setStyle();
			} else{
				this.$layer.css({ 'visibility': 'hidden', 'display': 'none' });
			}
		},

		// Set the size and position of the layer
		_setStyle(){
			this.styled = true;

			this.$layer.css( 'display', '' );

			// Get the data attributes specified in HTML
			this.data = this.$layer.data();
			
			if ( typeof this.data.width !== 'undefined' ){
				this.$layer.css( 'width', this.data.width );
			}

			if ( typeof this.data.height !== 'undefined' ){
				this.$layer.css( 'height', this.data.height );
			}

			if ( typeof this.data.depth !== 'undefined' ){
				this.$layer.css( 'z-index', this.data.depth );
			}

			this.position = this.data.position ? ( this.data.position ).toLowerCase() : 'topleft';

			if ( this.position.indexOf( 'right' ) !== -1 ){
				this.horizontalProperty = 'right';
			} else if ( this.position.indexOf( 'left' ) !== -1 ){
				this.horizontalProperty = 'left';
			} else{
				this.horizontalProperty = 'center';
			}

			if ( this.position.indexOf( 'bottom' ) !== -1 ){
				this.verticalProperty = 'bottom';
			} else if ( this.position.indexOf( 'top' ) !== -1 ){
				this.verticalProperty = 'top';
			} else{
				this.verticalProperty = 'center';
			}

			this._setPosition();

			this.scale( this.scaleRatio );
		},

		// Set the position of the layer
		_setPosition(){
			const inlineStyle = this.$layer.attr( 'style' );

			this.horizontalPosition = typeof this.data.horizontal !== 'undefined' ? this.data.horizontal : 0;
			this.verticalPosition = typeof this.data.vertical !== 'undefined' ? this.data.vertical : 0;

			// Set the horizontal position of the layer based on the data set
			if ( this.horizontalProperty === 'center' ){
				
				// prevent content wrapping while setting the width
				if ( this.$layer.is( 'img' ) === false && ( typeof inlineStyle === 'undefined' || ( typeof inlineStyle !== 'undefined' && inlineStyle.indexOf( 'width' ) === -1 ) ) ){
					this.$layer.css({'white-space': 'nowrap', 'width': this.$layer.outerWidth( true ) } );
				}

				this.$layer.css({ 'marginLeft': 'auto', 'marginRight': 'auto', 'left': this.horizontalPosition, 'right': 0 });
			} else{
				this.$layer.css( this.horizontalProperty, this.horizontalPosition );
			}

			// Set the vertical position of the layer based on the data set
			if ( this.verticalProperty === 'center' ){

				// prevent content wrapping while setting the height
				if ( this.$layer.is( 'img' ) === false && ( typeof inlineStyle === 'undefined' || ( typeof inlineStyle !== 'undefined' && inlineStyle.indexOf( 'height' ) === -1 ) ) ){
					this.$layer.css({'white-space': 'nowrap','height': this.$layer.outerHeight( true )} );
				}

				this.$layer.css({ 'marginTop': 'auto', 'marginBottom': 'auto', 'top': this.verticalPosition, 'bottom': 0 });
			} else{
				this.$layer.css( this.verticalProperty, this.verticalPosition );
			}
		},

		// Scale the layer
		scale( ratio ){

			// Return if the layer is set to be unscalable
			if ( this.$layer.hasClass( 'sp-no-scale' ) ){
				return;
			}

			// Store the ratio (even if the layer is not ready to be scaled yet)
			this.scaleRatio = ratio;

			// Return if the layer is not styled yet
			if ( this.styled === false ){
				return;
			}

			const css ={'transform-origin':(this.horizontalProperty + ' ' + this.verticalProperty),'transform':'scale(' + this.scaleRatio + ')'};

			// If the position is not set to a percentage value, apply the scaling to the position
			if ( typeof this.horizontalPosition !== 'string' ){
                            const horizontalProperty = this.horizontalProperty === 'center' ? 'left' : this.horizontalProperty;
                            css[ horizontalProperty ] = this.horizontalPosition * this.scaleRatio;
			}

			// If the position is not set to a percentage value, apply the scaling to the position
			if ( typeof this.verticalPosition !== 'string' ){
                            const verticalProperty = this.verticalProperty === 'center' ? 'top' : this.verticalProperty;
                            css[ verticalProperty ] = this.verticalPosition * this.scaleRatio;
			}

			// If the width or height is set to a percentage value, increase the percentage in order to
			// maintain the same layer to slide proportions. This is necessary because otherwise the scaling
			// transform would minimize the layers more than intended.
			if ( typeof this.data.width === 'string' && this.data.width.indexOf( '%' ) !== -1 ){
				css.width = ( parseFloat( this.data.width, 10 ) / this.scaleRatio ).toString() + '%';
			}

			if ( typeof this.data.height === 'string' && this.data.height.indexOf( '%' ) !== -1 ){
				css.height = ( parseFloat( this.data.height, 10 ) / this.scaleRatio ).toString() + '%';
			}

			this.$layer.css( css );
		},

		// Show the layer
		show( callback ){
			if ( this.visible === true ){
				return;
			}

			this.visible = true;

			// First, style the layer if it's not already styled
			if ( this.styled === false ){
				this._setStyle();
			}

			const that = this,
				offset = typeof this.data.showOffset !== 'undefined' ? this.data.showOffset : 50,
				duration = typeof this.data.showDuration !== 'undefined' ? this.data.showDuration / 1000 : 0.4,
				delay = typeof this.data.showDelay !== 'undefined' ? this.data.showDelay : 10,
				stayDuration = typeof that.data.stayDuration !== 'undefined' ? parseInt( that.data.stayDuration, 10 ) : -1,
				start ={ 'opacity': 0, 'visibility': 'visible','transform':'scale(' + this.scaleRatio + ')' },
                                target ={ 'opacity': 1,'transform':'scale(' + this.scaleRatio + ')','transition':'opacity ' + duration + 's' };
                        let transformValues = '';

				if ( typeof this.data.showTransition !== 'undefined' ){
					if ( this.data.showTransition === 'left' ){
						transformValues = offset + 'px, 0';
					} else if ( this.data.showTransition === 'right' ){
						transformValues = '-' + offset + 'px, 0';
					} else if ( this.data.showTransition === 'up' ){
						transformValues = '0, ' + offset + 'px';
					} else if ( this.data.showTransition === 'down'){
						transformValues = '0, -' + offset + 'px';
					}

					start[ 'transform' ] += ' translate3d(' + transformValues + ', 0)';
					target[ 'transform' ] += ' translate3d(0, 0, 0)';
					target[ 'transition' ] += ',transform ' + duration + 's';
				}

				// Listen when the layer animation is complete
				this.$layer.on( this.transitionEvent, function( event ){
					if ( event.target !== event.currentTarget ){
						return;
					}

					that.$layer
						.off( that.transitionEvent )
						.css( 'transition', '' );

					// Hide the layer after a given time interval
					if ( stayDuration !== -1 ){
						that.stayTimer = setTimeout(function(){
							that.hide();
							that.stayTimer = null;
						}, stayDuration );
					}

					if ( typeof callback !== 'undefined' ){
						callback();
					}
				}).css( start );

				setTimeout( function(){
					that.$layer.css( target );
				}, delay );
		},

		// Hide the layer
		hide( callback ){
			if ( this.visible === false ){
				return;
			}
                        
			this.visible = false;
                        
			const that = this,
				offset = typeof this.data.hideOffset !== 'undefined' ? this.data.hideOffset : 50,
				duration = typeof this.data.hideDuration !== 'undefined' ? this.data.hideDuration / 1000 : 0.4,
				delay = typeof this.data.hideDelay !== 'undefined' ? this.data.hideDelay : 10,
                                target ={ 'opacity': 0,'transform': 'scale(' + this.scaleRatio + ')','transition':'opacity ' + duration + 's'};
                        let transformValues = ''

			// If the layer is hidden before it hides automatically, clear the timer
			if ( this.stayTimer !== null ){
				clearTimeout( this.stayTimer );
			}
				

				if ( typeof this.data.hideTransition !== 'undefined' ){
					if ( this.data.hideTransition === 'left' ){
						transformValues = '-' + offset + 'px, 0';
					} else if ( this.data.hideTransition === 'right' ){
						transformValues = offset + 'px, 0';
					} else if ( this.data.hideTransition === 'up' ){
						transformValues = '0, -' + offset + 'px';
					} else if ( this.data.hideTransition === 'down' ){
						transformValues = '0, ' + offset + 'px';
					}

					target[ 'transform' ] += ' translate3d(' + transformValues + ', 0)';
					target[ 'transition' ] += ',transform ' + duration + 's';
				}

				// Listen when the layer animation is complete
				this.$layer.on( this.transitionEvent, function( event ){
					if ( event.target !== event.currentTarget ){
						return;
					}

					that.$layer
						.off( that.transitionEvent )
						.css( 'transition', '' );

					// Hide the layer after transition
					if ( that.visible === false ){
						that.$layer.css( 'visibility', 'hidden' );
					}

					if ( typeof callback !== 'undefined' ){
						callback();
					}
				});

				setTimeout( function(){
					that.$layer.css( target );
				}, delay );
		},

		isVisible(){
			return !(this.visible === false || this.$layer.is( ':hidden'));
		},

		// Destroy the layer
		destroy(){
			this.$layer.removeAttr( 'style data-layer-init' );
		}
	};

	$.SliderPro.addModule( 'Layers', Layers );
	
})( window, jQuery );


// Autoplay module for Slider Pro.
// 
// Adds automatic navigation through the slides by calling the
// 'nextSlide' or 'previousSlide' methods at certain time intervals.
;(function(  $ ){

	"use strict";
	
	const NS = 'Autoplay.' + $.SliderPro.namespace,

        Autoplay ={
		autoplayTimer: null,
		isTimerRunning: false,
		isTimerPaused: false,
		initAutoplay(){
			this.on( 'update.' + NS, this._autoplayOnUpdate.bind(this));
		},
		// Start the autoplay if it's enabled, or stop it if it's disabled but running 
		_autoplayOnUpdate( e ){
			if ( this.settings.autoplay === true ){
				this.on( 'gotoSlide.' + NS, this._autoplayOnGotoSlide.bind(this))
				.on( 'mouseenter.' + NS, this._autoplayOnMouseEnter.bind(this))
				.on( 'mouseleave.' + NS,this._autoplayOnMouseLeave.bind(this));
                                this.startAutoplay();
			} else{
				this.off( 'gotoSlide.' + NS+ ' mouseenter.' + NS+' mouseleave.' + NS );
                                this.stopAutoplay();
			}
		},
		// Restart the autoplay timer when a new slide is selected
		_autoplayOnGotoSlide( e ){
			// stop previous timers before starting a new one
			if ( this.isTimerRunning === true ){
				this.stopAutoplay();
			}
			
			if ( this.isTimerPaused === false ){
				this.startAutoplay();
			}
		},
		// Pause the autoplay when the slider is hovered
		_autoplayOnMouseEnter( e ){
			if ( this.isTimerRunning && ( this.settings.autoplayOnHover === 'pause' || this.settings.autoplayOnHover === 'stop' ) ){
				this.stopAutoplay();
				this.isTimerPaused = true;
                                
                                /* hide the timer bar when autoplay stops */
				if( this.settings._autoplayOnHover === 'stop' ){
					this.$slider.find( '.bsp-timer-bar' ).fadeOut();
				}
			}
		},
		// Start the autoplay when the mouse moves away from the slider
		_autoplayOnMouseLeave( e ){
			if ( this.settings.autoplay === true && this.isTimerRunning === false && this.settings.autoplayOnHover !== 'stop' ){
				const start = this.settings.timer_bar && this.settings.autoplayOnHover === 'pause' ? (this.$slider.find('.bsp-timer-bar').width() * 100) / this.$slider.width() : 0;
				this.startAutoplay(start);
				this.isTimerPaused = false;
			}
		},
		// Starts the autoplay
		startAutoplay(start){
                        if(!start){
                            start=0;
                        }
			const that = this,
				duration = this.settings.timer_bar && start > 0 ? (5000 - Math.floor(this.settings.autoplayDelay * (start / 100))) : this.settings.autoplayDelay;
			
			this.isTimerRunning = true;
                        if( this.settings.timer_bar ){
				that.$slider.find('.bsp-timer-bar').css('width', start + '%').animate({width: '100%'},{
					duration: duration
				} );
			}
			this.autoplayTimer = setInterval(function(){
				if ( that.settings.autoplayDirection === 'normal' ){
					that.nextSlide();
				} else if ( that.settings.autoplayDirection === 'backwards' ){
					that.previousSlide();
				}
			}, duration );
		},
		// Stops the autoplay
		stopAutoplay(){
			this.isTimerRunning = false;
			this.isTimerPaused = false;
                        if( this.settings.timer_bar ){
				this.$slider.find( '.bsp-timer-bar' ).stop();
			}
			clearInterval( this.autoplayTimer );
		},
		// Destroy the module
		destroyAutoplay(){
			clearInterval( this.autoplayTimer );
                        this.$slider.find( '.bsp-timer-bar' ).remove();
			this.off( 'update.' + NS+' gotoSlide.' + NS +' mouseenter.' + NS + ' mouseleave.' + NS );
		},
		autoplayDefaults:{
			// Indicates whether or not autoplay will be enabled
			autoplay: true,
			// Sets the delay/interval at which the autoplay will run
			autoplayDelay: 5000,
			// Indicates whether autoplay will navigate to the next slide or previous slide
			autoplayDirection: 'normal',
			// Indicates if the autoplay will be paused or stopped when the slider is hovered.
			// Possible values are 'pause', 'stop' or 'none'.
			autoplayOnHover: 'pause'
		}
	};

	$.SliderPro.addModule( 'Autoplay', Autoplay );
	
})(jQuery);

// Keyboard module for Slider Pro.
// 
// Adds the possibility to navigate through slides using the keyboard arrow keys, or
// open the link attached to the main slide image by using the Enter key.
;(function( $ ){

	"use strict";
	
	const NS = 'Keyboard.' + $.SliderPro.namespace,

        Keyboard ={

		initKeyboard(){
			if ( this.settings.keyboard === false ){
				return;
			}
			const that = this;
                        let hasFocus = false;
			// Detect when the slide is in focus and when it's not, and, optionally, make it
			// responsive to keyboard input only when it's in focus
			this.$slider.on( 'focus.' + NS, function(){
				hasFocus = true;
			})
                        .on( 'blur.' + NS, function(){
				hasFocus = false;
			});
			$( document ).on( 'keydown.' + this.uniqueId + '.' + NS, function( e ){
				if ( that.settings.keyboardOnlyOnFocus === true && hasFocus === false ){
					return;
				}

				// If the left arrow key is pressed, go to the previous slide.
				// If the right arrow key is pressed, go to the next slide.
				// If the Enter key is pressed, open the link attached to the main slide image.
				if ( e.which === 37 ){
					that.previousSlide();
				} else if ( e.which === 39 ){
					that.nextSlide();
				} else if ( e.which === 13 ){
					that.$slider.find( '.sp-slide' ).eq( that.selectedSlideIndex ).find( '.sp-image-container a' )[0].click();
				}
			});
		},
		// Destroy the module
		destroyKeyboard(){
			this.$slider.off( 'focus.' + NS ).off( 'blur.' + NS );
			$( document ).off( 'keydown.' + this.uniqueId + '.' + NS );
		},
		keyboardDefaults:{
			// Indicates whether keyboard navigation will be enabled
			keyboard: true,
			// Indicates whether the slider will respond to keyboard input only when
			// the slider is in focus.
			keyboardOnlyOnFocus: false
		}
	};

	$.SliderPro.addModule( 'Keyboard', Keyboard );
	
})( jQuery );


// Buttons module for Slider Pro.
// 
// Adds navigation buttons at the bottom of the slider.
;(function( $ ){

	"use strict";
	
	const NS = 'Buttons.' + $.SliderPro.namespace,

        Buttons ={

		// Reference to the buttons container
		$buttons: null,

		initButtons(){
			this.on( 'update.' + NS,this._buttonsOnUpdate.bind(this));
		},

		_buttonsOnUpdate(){
			this.$buttons = this.$slider.find('.sp-buttons');
			if ( this.settings.buttons === true && this.getTotalSlides() > 1){
				this._createButtons();
			}
			else if ( this.settings.buttons === false || ( this.getTotalSlides() <= 1 && this.$buttons.length !== 0 ) ){
				this._removeButtons();
			}
		},

		// Create the buttons
		_createButtons(){
			const that = this;
                        if(this.$buttons.length===0){
                            const buttons=document.createElement('div');
                                  buttons.className='sp-buttons tf_rel tf_w tf_textc';

                            // Create the buttons
                            for ( let i = this.getTotalSlides()-1;i>-1;--i){
                                let b=document.createElement('div');
                                b.className='sp-button tf_box tf_inline_b tf_vmiddle';
                                if(1===i){
                                    b.className+=' sp-selected-button';
                                }
                                buttons.appendChild(b);
                            }
                            this.$slider[0].appendChild(buttons);
                            this.$buttons=$(buttons);
                        }
			// Listen for button clicks 
			this.$buttons.on( 'click.' + NS, '.sp-button', function(){
				that.gotoSlide( $( this ).index() );
			});

			// Select the corresponding button when the slide changes
			this.on( 'gotoSlide.' + NS, function( event ){
				that.$buttons.find( '.sp-selected-button' ).removeClass( 'sp-selected-button' );
				that.$buttons.find( '.sp-button' ).eq( event.index ).addClass( 'sp-selected-button' );
			});
		},


		// Remove the buttons
		_removeButtons(){
			this.off( 'gotoSlide.' + NS );
			this.$buttons.off( 'click.' + NS, '.sp-button' ).remove();
			this.$slider.removeClass( 'sp-has-buttons' );
		},

		destroyButtons(){
			this._removeButtons();
			this.off( 'update.' + NS );
		},

		buttonsDefaults:{
			
			// Indicates whether the buttons will be created
			buttons: true
		}
	};

	$.SliderPro.addModule( 'Buttons', Buttons );

})( jQuery );

// Arrows module for Slider Pro.
// 
// Adds arrows for navigating to the next or previous slide.
;(function( $ ){

	"use strict";

	const NS = 'Arrows.' + $.SliderPro.namespace,

        Arrows ={

		// Reference to the arrows container
		$arrows: null,

		// Reference to the previous arrow
		$previousArrow: null,

		// Reference to the next arrow
		$nextArrow: null,

		initArrows(){
			this.on( 'update.' + NS, this._arrowsOnUpdate.bind(this))
			.on( 'gotoSlide.' + NS,this._checkArrowsVisibility.bind(this) );
		},
		_arrowsOnUpdate(){
			const that = this;

			// Create the arrows if the 'arrows' option is set to true
			if ( this.settings.arrows === true && this.$arrows === null ){
				this.$arrows = $( '<div class="sp-arrows"></div>' ).appendTo( this.$slidesContainer );
				
				this.$previousArrow = $( '<div class="sp-arrow sp-previous-arrow"></div>' ).appendTo( this.$arrows );
				this.$nextArrow = $( '<div class="sp-arrow sp-next-arrow"></div>' ).appendTo( this.$arrows );

				this.$previousArrow.on( 'click.' + NS, function(){
					that.previousSlide();
				});

				this.$nextArrow.on( 'click.' + NS, function(){
					that.nextSlide();
				});

				this._checkArrowsVisibility();
			} else if ( this.settings.arrows === false && this.$arrows !== null ){
				this._removeArrows();
			}

			if ( this.settings.arrows === true ){
                            this.$arrows.toggleClass( 'sp-fade-arrows',this.settings.fadeArrows === true );
			}
		},

		// Show or hide the arrows depending on the position of the selected slide
		_checkArrowsVisibility(){
			if ( this.settings.arrows === false || this.settings.loop === true ){
				return;
			}
                        this.$previousArrow[0]['style']['display']=this.selectedSlideIndex === 0?'none':'block';
			this.$nextArrow[0]['style']['display']=(this.selectedSlideIndex === this.getTotalSlides() - 1)?'none':'block';
		},
		
		_removeArrows(){
			if ( this.$arrows !== null ){
				this.$previousArrow.off( 'click.' + NS );
				this.$nextArrow.off( 'click.' + NS );
				this.$arrows.remove();
				this.$arrows = null;
			}
		},

		destroyArrows(){
			this._removeArrows();
			this.off( 'update.' + NS+ ' gotoSlide.' + NS  );
		},

		arrowsDefaults:{

			// Indicates whether the arrow buttons will be created
			arrows: false,

			// Indicates whether the arrows will fade in only on hover
			fadeArrows: true
		}
	};

	$.SliderPro.addModule( 'Arrows', Arrows );

})( jQuery );

// Fade module for Slider Pro.
// 
// Adds the possibility to navigate through slides using a cross-fade effect.
;(function(  $ ){

	"use strict";

	const NS = 'TransitionEffects.' + $.SliderPro.namespace,

	TransitionEffects ={

		// Reference to the original 'gotoSlide' method
		originalGotoSlideReference: null,

		initTransitionEffects(){
			this.on( 'update.' + NS,this._TransitionEffectsOnUpdate.bind(this));
		},

		// If fade is enabled, store a reference to the original 'gotoSlide' method
		// and then assign a new function to 'gotoSlide'.
		_TransitionEffectsOnUpdate(){
                    this.originalGotoSlideReference = this.gotoSlide;
                    this.gotoSlide = this._gotoSlide;
		},

		// Will replace the original 'gotoSlide' function by adding a cross-fade effect
		// between the previous and the next slide.
		_gotoSlide( index ){

			if(index === this.selectedSlideIndex ||  this.$slider.data( 'isKeySliding' )){
				return false;
			}
			
			// If the slides are being swiped/dragged, don't use fade, but call the original method instead.
			// If not, which means that a new slide was selected through a button, arrows or direct call, then
			// use fade.
			if ( this.$slider.hasClass( 'sp-swiping' ) ){
				if( index === undefined ){
					index = this.slidesOrder[0];
				}

				this.originalGotoSlideReference( index );
			} else{
				const that = this,
                                    newIndex = index;
                                let $nextSlide,
                                    $previousSlide;
					

				// Loop through all the slides and overlap the the previous and next slide,
				// and hide the other slides.
				$.each( this.slides, function( index, element ){
					let slideIndex = element.getIndex(),
						$slide = element.$slide;

					if ( slideIndex === newIndex ){
						$slide.css({ 'opacity': 0, 'left': 0, 'top': 0, 'z-index': 20, 'visibility': 'visible' });
						$nextSlide = $slide;
					} else if ( slideIndex === that.selectedSlideIndex ){
						$slide.css({ 'opacity': 1, 'left': 0, 'top': 0, 'z-index': 10 });
						$previousSlide = $slide;
					} else{
						$slide.css( 'visibility', 'hidden' );
					}
				});

				// Set the new indexes for the previous and selected slides
				this.previousSlideIndex = this.selectedSlideIndex;
				this.selectedSlideIndex = index;

				// Re-assign the 'sp-selected' class to the currently selected slide
				this.$slides.find( '.sp-selected' ).removeClass( 'sp-selected' );
				this.$slides.find( '.sp-slide' ).eq( this.selectedSlideIndex ).addClass( 'sp-selected' );
			
				// Rearrange the slides if the slider is loopable
				if ( that.settings.loop === true ){
					that._updateSlidesOrder();
				}

				// Move the slides container so that the cross-fading slides (which now have the top and left
				// position set to 0) become visible and in the center of the slider.
				this._moveTo( this.visibleOffset, true );
                                
                                const transition = $nextSlide.data( 'transition' ),
                                    duration = $nextSlide.data( 'duration' ),
									_transition_callback = function(){
					// Reset the position of the slides and slides container
					that._resetSlidesPosition();

					that.$slider.data( 'isKeySliding', true );
                                        
					setTimeout(function(){
						// After the animation is over, make all the slides visible again
						$.each( that.slides, function( index, element ){
							element.$slide.css({ 'visibility': '', 'opacity': '', 'z-index': '', 'transform' : '' });
						});

						// Fire the 'gotoSlideComplete' event
						that.trigger({ type: 'gotoSlideComplete', index: index, previousIndex: that.previousSlideIndex, slider: that.$slider });
						if (that.settings.gotoSlideComplete){
							that.settings.gotoSlideComplete.call( that,{ type: 'gotoSlideComplete', index: index, previousIndex: that.previousSlideIndex, slider: that.$slider } );
						}

						/* After the animation is done, recalculate the heights */
						if ( that.settings.autoHeight === true ){
							that._resizeHeight();
						}

						that.$slider.data( 'isKeySliding', false );
					}, parseFloat( duration) * 1000 );
				};
                            
				// Fade out the previous slide, if indicated, in addition to fading in the next slide
				// The previous slide is always faded out
				if ( this.settings.fadeOutPreviousSlide === true ){
					this._transition_effect( $previousSlide, 'fadeOut', transition );
				}
				/**
				 * This is where magic happens.
				 * Apply the transition effect to next slide
				 */
                                this._transition_effect( $nextSlide, transition, duration, _transition_callback );
				if ( this.settings.autoHeight === true ){
					this._resizeHeight();
				}
				// Fire the 'gotoSlide' event
				this.trigger({ type: 'gotoSlide', index: index, previousIndex: this.previousSlideIndex });
				if ( this.settings.gotoSlide){
					this.settings.gotoSlide.call( this,{ type: 'gotoSlide', index: index, previousIndex: this.previousSlideIndex });
				}
			}
		},
		// slide effect
		_transition_effect( target, effect, duration, callback ){
			let sp_mask = target.closest( '.sp-mask' ),
                initial_css ={}, // CSS properties applied before transition effect
                css ={}; // CSS properties to make the transition to

			if( effect === 'slideTop' ){
				initial_css ={ opacity : 1, top : '-' + sp_mask.height() + 'px' };
				css ={ top : 0 };
			} else if( effect === 'slideBottom' ){
				initial_css ={ opacity : 1, top : sp_mask.height() + 'px' };
				css ={ top : 0 };
			} else if( effect === 'slideLeft' ){
				initial_css ={ opacity : 1, left : '-' + sp_mask.width() + 'px' };
				css ={ left : 0 };
			} else if( effect === 'slideRight' ){
				initial_css ={ opacity : 1, left : sp_mask.width() + 'px' };
				css ={ left : 0 };
			} else if( effect === 'slideTopFade' ){
				initial_css ={ top : '-' + sp_mask.height() + 'px' };
				css ={ top : 0, opacity : 1 };
			} else if( effect === 'slideBottomFade' ){
				initial_css ={ top : sp_mask.height() + 'px' };
				css ={ top : 0, opacity : 1 };
			} else if( effect === 'slideLeftFade' ){
				initial_css ={ left : '-' + sp_mask.width() + 'px' };
				css ={ left : 0, opacity : 1 };
			} else if( effect === 'slideRightFade' ){
				initial_css ={ left : sp_mask.width() + 'px' };
				css ={ left : 0, opacity : 1 };
			} else if( effect === 'zoomOut' ){
				initial_css['transform'] = 'scale(2)';
				css[ 'opacity' ] = 1;
				css[ 'transform' ] = 'scale(1)';
			} else if( effect === 'zoomTop' ){
				initial_css[ 'transform' ] = 'scale(2)';
				initial_css[ 'top' ] = '-' + sp_mask.height() + 'px';
				css[ 'opacity' ] = 1;
				css[ 'transform' ] = 'scale(1)';
				css[ 'top' ] = 0;
			} else if( effect === 'zoomBottom' ){
				initial_css[ 'transform' ] = 'scale(2)';
				initial_css[ 'top' ] = sp_mask.height() + 'px';
				css[ 'opacity' ] = 1;
				css[ 'transform' ] = 'scale(1)';
				css[ 'top' ] = 0;
			} else if( effect === 'zoomLeft' ){
				initial_css[ 'transform' ] = 'scale(2)';
				initial_css[ 'left' ] = '-' + sp_mask.width() + 'px';
				css[ 'opacity' ] = 1;
				css[ 'transform' ] = 'scale(1)';
				css[ 'left' ] = 0;
			} else if( effect === 'zoomTop' ){
				initial_css[ 'transform' ] = 'scale(2)';
				initial_css[ 'left' ] = sp_mask.width() + 'px';
				css[ 'opacity' ] = 1;
				css[ 'transform' ] = 'scale(1)';
				css[ 'left' ] = 0;
			} else if( effect === 'fadeOut' ){
				initial_css[ 'opacity' ] = 1;
				css[ 'opacity' ] = 0;
			} else{ // fadeIn, as fallback
                                css ={ opacity : 1 };
			}
			if(effect!=='fade'){
				target.css( initial_css );
			}
			setTimeout(()=>{
					css[ 'transition-property' ]=Object.keys(css).join(',');
					css[ 'transition-duration' ] = duration + 's';
					target.css( css );
			}, 100 );

			target.on( this.transitionEvent, e=>{
					if ( e.target !== e.currentTarget ){
							return;
					}
					$(e.currentTarget).off( e.type ).css( 'transition', '' );

					if ( typeof callback === 'function' ){
							callback();
					}
			});
			
		},
		// Destroy the module
		destroyTransitionEffects(){
			this.off( 'update.' + NS );
			if ( this.originalGotoSlideReference !== null ){
				this.gotoSlide = this.originalGotoSlideReference;
			}
		}
	};

	$.SliderPro.addModule( 'TransitionEffects', TransitionEffects );

})( jQuery );
