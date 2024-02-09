// fixes blank frame after 2 slides slider
;(function( $ ) {
	"use strict";
	const NS = 'TwoSlidesFixer.' + $.SliderPro.namespace,
	TwoSlidesFixer = {
		initTwoSlidesFixer() {
			this.on( 'update.' + NS, this._onUpdate.bind(this) );
		},
		_fixerGotoSlide( index ) {

			// Dont do anything if number of slides are not 2 or its not touch device
			if ( !this.$slider.hasClass( 'sp-swiping' ) || this.$slider.find('.sp-slide').length !== 2 ) {
				if (index === undefined) {
					index = this.slidesOrder[0];
				}

				this.twoSlidesFixerOrigGotoSlide( index );
				return;
			}

			const that = this,
				origUpdateSlidesOrder = this._updateSlidesOrder,
			    origUpdateSlidesPosition = this._updateSlidesPosition;

			// Assign a no-op
			this._updateSlidesOrder = this._updateSlidesPosition = function(){};
			//Call goto function
			this.twoSlidesFixerOrigGotoSlide( index );
			// Revert original functions
			this._updateSlidesOrder = origUpdateSlidesOrder;
			this._updateSlidesPosition = origUpdateSlidesPosition;

			setTimeout(function () {
				that._updateSlidesOrder();
				that._updateSlidesPosition();
			}, this.settings.slideAnimationDuration);
		},
		_onUpdate() {
			this.twoSlidesFixerOrigGotoSlide = this.gotoSlide;
			this.gotoSlide = this._fixerGotoSlide;
		},
		// Destroy the module
		destroyTwoSlidesFixer() {
			this.off( 'update.' + NS );
		},
		TwoSlidesFixerDefaults: {}
	};

	$.SliderPro.addModule( 'TwoSlidesFixer', TwoSlidesFixer );

})(jQuery);