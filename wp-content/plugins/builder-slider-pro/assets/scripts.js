(($, Themify) => {
    'use strict';
    const args = tbLocalScript.addons['pro-slider'],
            v = '1.2.1',
            _click = function (e) {
                e.preventDefault();
                const slider = $(this.closest('.slider-pro')).data('sliderPro'),
                        action = this.getAttribute('href') === '#next-slide' ? 'nextSlide' : 'previousSlide';
                typeof slider === 'object' && slider[action].call(slider);
            },
            _lazyLoading = (el, self) => {
        const div = el.tfClass('bsp_frame')[0];
        if (el.hasAttribute('data-bg')) {
            el.style.backgroundImage = 'url(' + el.dataset.bg + ')';
            el.removeAttribute('data-bg');
        }
        if (div) {
            const url = div.dataset.url,
                    attr = Themify.parseVideo(url),
                    iframe = document.createElement('iframe');
            let src = '',
                    allow = '';

            if (attr.type === 'youtube') {
                src = 'https://www.youtube.com/embed/' + attr.id + '?autohide=1&border=0&wmode=opaque';
                allow = 'accelerometer;encrypted-media;gyroscope;picture-in-picture';
            } else {
                src = '//player.vimeo.com/video/' + attr.id + '?portrait=0&title=0&badge=0';
                allow = 'fullscreen';
            }
            let queryStr = url.split('?')[1];
            const params = queryStr ? new URLSearchParams(queryStr) : false;
            if (params && params.get('autoplay')) {
                src += '&autoplay=1';
                allow += ';autoplay';
            }
            iframe.className = 'tf_abs tf_w tf_h sp-video';
            iframe.setAttribute('allowfullscreen', '');
            iframe.setAttribute('allow', allow);
            iframe.setAttribute('src', src);
            div.replaceWith(iframe);
        } else {
            const video = el.tfTag('video')[0];
            if (video) {
                if (video.preload === 'none') {
                    if (self.options.autoplay === true && self.isTimerRunning === true) {
                        video.tfOn('ended', () => {
                            if (self.settings.autoplayDirection === 'normal') {
                                self.nextSlide();
                            } else if (self.settings.autoplayDirection === 'backwards') {
                                self.previousSlide();
                            }
                        }, {passive: true});
                    }
                    video.tfOn('canplay', function () {
                        if (this.paused) {
                            this.play();
                        }
                    }, {passive: true, once: true})
                    .setAttribute('preload', 'metadata');
                    video.setAttribute('autoplay', 'autoplay');
                } else if (video.paused) {
                    video.play();
                }
            }
        }
    },
            run = items => {
                for (let i = items.length - 1; i > -1; --i) {
                    Themify.imagesLoad(items[i]).then(item => {
                        const sw = item.dataset.sliderWidth,
                                sh = item.dataset.sliderHeight,
                                autoPlay = item.dataset.autoplay,
                                tw = item.dataset.thumbnailWidth,
                                th = item.dataset.thumbnailHeight,
                                pasue_last = item.dataset.pauseLast === '1',
                                config = {
                                    slideDistance: 0,
                                    buttons: !item.classList.contains('pager-none') && !item.classList.contains('pager-type-thumb'),
                                    arrows: true,
                                    loop: item.dataset.loop=== '1',
                                    responsive: true,
                                    autoHeightOnReize: true,
                                    autoHeight: false,
                                    thumbnailTouchSwipe: true, // this is required for the thumbnail click action to work
                                    thumbnailWidth: tw ? parseFloat(tw) : '',
                                    thumbnailHeight: th ? parseFloat(th) : '',
                                    timer_bar: item.dataset.timerBar === 'yes',
                                    autoplayDelay: autoPlay && autoPlay !== 'off' ? parseFloat(autoPlay) : 5000,
                                    autoplay: autoPlay !== 'off',
                                    autoScaleLayers: false,
                                    autoplayOnHover: item.dataset.hoverPause,
                                    width: sw && sw !== '100%' ? parseInt(sw) : '100%', // set default slider width to 100%
                                    fadeOutPreviousSlide: false,
                                    touchSwipe: (Themify.isTouch && item.dataset.touchSwipeMobile === 'yes') || (!Themify.isTouch && item.dataset.touchSwipeDesktop=== 'yes'),
                                    gotoSlide(e) {
                                        if (e.index === this.slides.length - 1 && this.options.autoplay === true && pasue_last && this.options.loop === false) {
                                            this.stopAutoplay();
                                        }
                                        for (let j = 0; j < 2; ++j) {
                                            let index = e.index + j,
                                                    el = this.getSlideAt(index);
                                            if (el) {
                                                el = el.$slide[0];
                                                if (el) {
                                                    _lazyLoading(el, this);
                                                }
                                            }
                                        }
                                    },
                                    init() {
                                        _lazyLoading(this.getSlideAt(0).$slide[0], this);
                                        const el = this.instance,
                                                buttons = el.tfClass('bsp-slide-button');
                                        for (let j = buttons.length - 1; j > -1; --j) {
                                            let href = buttons[j].getAttribute('href');
                                            if (href === '#next-slide' || href === '#prev-slide') {
                                                buttons[j].tfOn('click', _click);
                                            }
                                        }
                                        el.classList.remove('tf_hidden', 'tf_lazy');
                                        el.classList.add('tf_bsp_ready');
                                    }
                                };
                        if (sh === '') {
                            config.aspectRatio = 1.9;
                        } else {
                            config.height = sh;
                        }

                        $(item.tfClass('slider-pro')[0]).sliderPro(config);
                    });
                }
            },
            init = el => {
                const items = Themify.selectWithParent('module-pro-slider', el);
                if (items.length > 0) {
                    const css = {
                        button: 'bsp-slide-button',
                        excerpt: 'bsp-slide-excerpt',
                        image: 'sp-slide-image',
                        video: 'sp-video',
                        thumbnails: 'sp-thumbnail'
                    },
                    proModules = args.url + 'sliderpro/',
                    isSliderLoaded=!!$.SliderPro,
                    prms = [Themify.loadJs(args.url + 'jquery.sliderPro', isSliderLoaded, v,null,false)];
                    for (let i = items.length - 1; i > -1; --i) {
                        let hasTouch = false;
                        if ((Themify.isTouch && items[i].dataset.touchSwipeMobile === 'yes') || (!Themify.isTouch && items[i].dataset.touchSwipeDesktop === 'yes')) {
                            prms.push(Themify.loadJs(proModules + 'touchSwipe', (isSliderLoaded && $.SliderPro.modules.indexOf('TouchSwipe')!==-1), v,null,false));
                            hasTouch = true;
                        }
                        for (let k in css) {
                            if (items[i].tfClass(css[k])[0] !== undefined) {
                                let m = k === 'video' ? k + '.min' : k;
                                prms.push(Themify.loadCss(args.url + 'modules/' + m, 'bsp_' + k, args.ver));
                                if (k === 'video' || k === 'thumbnails') {
                                    prms.push(Themify.loadJs(proModules + k, null, v,null,false));
                                    if (hasTouch === true && k === 'thumbnails') {
                                        prms.push(Themify.loadJs(proModules + 'thumbnailtouchSwipe', (isSliderLoaded && $.SliderPro.modules.indexOf('ThumbnailTouchSwipe')!==-1), v,null,false));
                                    }
                                }
                            }
                        }
                    }
                    if (items.length === 2) {
                        prms.push(Themify.loadJs(proModules + 'twoSlidesFixer', (isSliderLoaded && $.SliderPro.modules.indexOf('TwoSlidesFixer')!==-1), v,null,false));
                    }

                    Promise.all(prms).then(() => {
                        run(items);
                    });
                }
            };
    Themify.on('builder_load_module_partial', (el, isLazy) => {
        if (isLazy === true && !el.classList.contains('module-pro-slider')) {
            return;
        }
        init(el);
    });
})(jQuery, Themify);
