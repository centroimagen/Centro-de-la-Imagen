;($=> {

    'use strict';
	let ev='';
    const NS = 'MouseWheel.' + $.SliderPro.namespace,

	MouseWheel = {
        allowMouseWheelScroll:true,
        initMouseWheel(){
            

            if (this.settings.mouseWheel === false) {
                return;
            }
			
            // get the current mouse wheel event used in the browser
            if ('onwheel' in document) {
                ev = 'wheel';
            } else if ('onmousewheel' in document) {
                ev = 'mousewheel';
            } else if ('onDomMouseScroll' in document) {
                ev = 'DomMouseScroll';
            } else if ('onMozMousePixelScroll' in document) {
                ev = 'MozMousePixelScroll';
            }
			const that = this;
            this.on(ev+ '.' + NS, function(e) {
                const eventObject = e.originalEvent;
				let delta;

                // get the movement direction and speed indicated in the delta property
                if (typeof eventObject.detail !== 'undefined') {
                    delta = eventObject.detail;
                }

                if (typeof eventObject.wheelDelta !== 'undefined') {
                    delta = eventObject.wheelDelta;
                }

                if (typeof eventObject.deltaY !== 'undefined') {
                    delta = eventObject.deltaY * -1;
                }

                if (that.allowMouseWheelScroll === true && Math.abs(delta) >= that.settings.mouseWheelSensitivity) {
                    that.allowMouseWheelScroll = false;

                    setTimeout(function() {
                        that.allowMouseWheelScroll = true;
                    }, 500);

                    if (delta <= -that.settings.mouseWheelSensitivity) {
                        that.nextSlide();
                    } else if (delta >= that.settings.mouseWheelSensitivity) {
                        that.previousSlide();
                    }
                }
            });
        },

        destroyMouseWheel(){
            this.off(ev+ '.' + NS);
        },

        mouseWheelDefaults: {
            mouseWheel: false,
            mouseWheelSensitivity: 10
        }
    };

    $.SliderPro.addModule( 'MouseWheel', MouseWheel );

})(jQuery);