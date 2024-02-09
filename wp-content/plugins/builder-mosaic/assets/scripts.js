((Themify, doc) => {
    'use strict';
    let winW = Themify.w,
            cssText = '.grid-stack div.grid-stack-item{position:absolute;left:0;top:0;padding:0}.grid-stack.grid-stack-1{width:auto;margin-left:0;height:auto!important}.grid-stack.grid-stack-1 .builder_mosaic_item.grid-stack-item{position:relative;top:0;left:0;width:auto;margin-bottom:20px}';
    const  isActive = Themify.is_builder_active === true,
            st = doc.createElement('style'),
            loadedSt = {},
            base = 8.333,
            selector = '.grid-stack .grid-stack-item',
            args = tbLocalScript.addons.mosaic,
            css_url = args.url + 'modules/',
            options = {
                float: true,
                verticalMargin: 0,
                disableDrag: true,
                disableWindowResize: true,
                disableResize: true,
                ddPlugin: false,
                animate: false,
                inHead: Themify.is_builder_active === true,
                rtl: Themify.isRTL
            },
            setCss = (left, w) => {
        if (loadedSt['l' + left] === undefined) {
            loadedSt['l' + left] = true;
            let v = left * base;
            if (left % 3 === 0) {
                v = Math.round(v);
            }
            st.textContent += selector + '[data-gs-x="' + left + '"]{left:' + v + '%}';
        }
        if (loadedSt['w' + w] === undefined) {
            loadedSt['w' + w] = true;
            let v = w == 0 ? base : (w * base);
            if (w % 3 === 0) {
                v = Math.round(v);
            }
            st.textContent += selector + '[data-gs-width="' + w + '"]{width:' + v + '%}';
        }
    },
            init = el => {
                const items = Themify.selectWithParent('tpgs-wrap', el);
                for (let i = items.length - 1; i > -1; --i) {
                    if (!items[i].classList.contains('grid-stack-done')) {
                        options.cellHeight = parseFloat(items[i].dataset.cellHeight);
                        options.minWidth = parseFloat(items[i].dataset.minWidth);
                        let grid = GridStack.init(options, items[i]),
                                stacks = Themify.convert(items[i].tfClass('builder_mosaic_item')).reverse();
                        if (options.minWidth >= winW) {
                            items[i].classList.add('grid-stack-1');
                        }
                        for (let j = stacks.length - 1; j > -1; --j) {
                            let left = stacks[j].dataset.gsX,
                                    w = stacks[j].dataset.gsWidth,
                                    hover = stacks[j].dataset.hover;
                            if (hover && loadedSt['ef' + hover] !== true) {
                                loadedSt['ef' + hover] = true;
                                Themify.loadCss(css_url + 'effects/' + hover, null, args.ver);
                            }
                            if (options.minWidth < winW) {
                                setCss(left, w);
                            }
                            grid.addWidget(stacks[j], {x: left, y: stacks[j].dataset.gsY, width: w, height: stacks[j].dataset.gsHeight});
                            if (isActive === false) {
                                stacks[j].style.visibility = 'visible';
                                stacks[j].style.animationDelay = (j * 200) + 'ms';
                                stacks[j].classList.add('animated', stacks[j].dataset.effect);
                            }
                        }

                        items[i].classList.add('grid-stack-done');
                        initSlider(items[i]);
                    }
                }
            },
            infiniteScroll = el => {
                const item = el.tfClass('tbm_wrap_more')[0];
                if (item) {
                    Themify.loadCss(css_url + 'infinite', 'tbm_infinity', args.ver);
                    const container = item.previousElementSibling,
                            isLoadMore = container.parentNode.classList.contains('pagination-load-more');
                    container.tfOn('infiniteloaded', function (e) {
                        let last = this;
                        while (true) {
                            if (last.nextElementSibling && last.nextElementSibling.classList.contains('tpgs-wrap')) {
                                last = last.nextElementSibling;
                            } else {
                                break;
                            }
                        }
                        const wrap = this.cloneNode(false),
                                f = doc.createDocumentFragment(),
                                items = e.detail.items;
                        wrap.classList.remove(this.gridstack.opts._class, 'grid-stack-done');
                        wrap.removeAttribute('data-gs-current-row');
                        for (let i = 0, len = items.length; i < len; ++i) {
                            f.appendChild(items[i]);
                        }
                        wrap.appendChild(f);
                        init(wrap);
                        last.parentNode.insertBefore(wrap, last.nextSibling);
                    }, {passive: true});

                    if (isLoadMore) {
                        item.classList.remove('tf_hidden');
                    }
                    Themify.infinity(container, {scrollThreshold: !isLoadMore, history: false});
                }
            },
            initSlider = el => {
                const swiper = el.closest('.tf_swiper-container');
                if (swiper && !swiper.classList.contains('tf_swiper-container-initialized')) {
                    Themify.carousel(swiper);
                }
            };
    st.id = 'tb_mosaic_css';
    if (Themify.isRTL) {
        cssText += '.grid-stack-rtl{direction:ltr}.grid-stack-rtl .grid-stack-item{direction:rtr}';
    }
    if (isActive === true) {
        cssText += '.themify_builder_active .builder_mosaic_item{visibility:visible}';
    }
    st.textContent = cssText;
    doc.head.appendChild(st);
    cssText = null;
    Themify.on('builder_load_module_partial', (el, isLazy) => {
        if (isLazy === true && !el.classList.contains('module-mosaic')) {
            return;
        }
        const prms = [],
                p = el || doc;
        if (p.tfClass('product')[0]) {
            prms.push(Themify.loadCss(css_url + 'product', 'tbm_products', args.ver));
        }
        prms.push(Themify.animateCss());
        prms.push(Themify.loadJs(args.url + 'gridstack', ('undefined' !== typeof GridStack), args.ver));
        Promise.all(prms).then(() => {
            init(el);
            if (isActive === false) {
                Themify.requestIdleCallback(() => {
                    infiniteScroll(el);
                }, 100);
            }
        });
    })
    .on('tfsmartresize', e => {
        if (e) {
            winW = e.w;
            const items = doc.tfClass('grid-stack-done');
            for (let i = items.length - 1; i > -1; --i) {
                if (items[i].gridstack) {
                    if (items[i].dataset.minWidth >= winW) {
                        items[i].classList.add('grid-stack-1');
                    } else {
                        for (let stacks = items[i].tfClass('grid-stack-item'), j = stacks.length - 1; j > -1; --j) {
                            setCss(stacks[j].dataset.gsX, stacks[j].dataset.gsWidth);
                        }
                        items[i].classList.remove('grid-stack-1');
                    }
                }
            }
        }
    });
})(Themify, document);