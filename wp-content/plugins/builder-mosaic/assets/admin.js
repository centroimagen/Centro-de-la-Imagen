(($, Themify, api,doc)=>{
    'use strict';
    const args=builderMosaicAdmin,
        top = window.top,
        st=doc.createElement('style'),
        loadedSt={};
    
    
    const init=()=>{
        let isJsLoaded=false;
        const setCss=(left,w)=>{
            const selector='.grid-stack .grid-stack-item',
                base=8.333;
            if(loadedSt['l'+left]===undefined){
                loadedSt['l'+left]=true;
                let v=left*base;
                if(left%3===0){
                    v=Math.round(v);
                }
                st.textContent+=selector+'[data-gs-x="'+left+'"]{left:'+v+'%}';
            }
            if(loadedSt['w'+w]===undefined){
                loadedSt['w'+w]=true;
                let v=w*base;
                if(w%3===0){
                    v=Math.round(v);
                }
                st.textContent+=selector+'[data-gs-width="'+w+'"]{width:'+v+'%}';
            }
        };
        ThemifyConstructor.ptb_mosaic_group = {
            render(data, self) {
				data.wrap_class = 'ptb_mosaic_options';
				const options = self.create( data.options, self ),
					btn = options.querySelectorAll( '.mosaic_ptb_field_group' );
				for ( let i = 0, len = btn.length; i < len; ++i ) {
					btn[ i ].tfOn( Themify.click, e=> {
						$(e.target).toggleClass('expanded').nextUntil('.mosaic_ptb_field_group').toggle( e.target.classList.contains( 'expanded' ) );
					}, {passive:true} );
				}
				return options;
            }
        };
        ThemifyConstructor.tile_grid = {
            render (data, self) {
                let is_updating = false,
                        Grid = null,
                        Grid_el = null,
                        clickEv=Themify.click,
                        // load dependencies
                        load_dependencies = async ()=> {
                            if(isJsLoaded!==true){
                                const url=args.jqui,
                                    wp=themify_vars.wp,
                                    v='1.1.2',
                                    prms=[],
                                    scripts = {
                                        [url + 'core.min.js']:{test:!!top.jQuery.ui,v:wp},
                                        [url + 'widget.min.js']:{test:!!(top.jQuery.widget  || !args.ui_widget),v:wp},
                                        [url + 'mouse.min.js']:{test:!!top.jQuery.fn.mouse,v:wp},
                                        [url + 'draggable.min.js']:{test:!!top.jQuery.fn.draggable,v:wp},
                                        [url + 'droppable.min.js']:{test:!!top.jQuery.fn.droppable,v:wp},
                                        [url + 'resizable.min.js']:{test:!!top.jQuery.fn.resizable,v:wp},
                                        [args.path + 'assets/gridstack.js']:{test:!!top.GridStack,v:v},
                                        [args.path + 'assets/gridstack.jQueryUI.js']:{test:!!top.$Grid,v:v}
                                    },
                                    keys=Object.keys(scripts);

                                for(let i=0,len=keys.length;i<len;++i){
                                    let v=scripts[keys[i]];
                                    prms.push(top.Themify.loadJs(keys[i],v.test,v.v,false,false));
                                }
                                await Promise.all(prms);
                                isJsLoaded=true;
                            }
                        },
                        save_grid =  ()=> {
                            const data = [],
                                items = top.document.tfClass('grid-stack-item');
                            for (let i = 0, len = items.length; i < len; ++i) {
                                if (items[i].offsetParent !== null) {
                                    let node = top.jQuery(items[i]).data('_gridstack_node');
                                    data.push({
                                        x: node.x,
                                        y: node.y,
                                        width: node.width,
                                        height: node.height
                                    });
                                }
                            }
                            return JSON.stringify(data);
                        },
                        update_template =  (template, remove)=> {
                            is_updating = false;
                            if (remove === undefined) {
                                remove = true;
                            }
                            if (remove) {
                                Grid.removeAll();
                            }
							if ( typeof template === 'string' ) {
								template=JSON.parse(template);
							}
                            for (let i in template) {
                                let item=doc.createElement('div'),
                                    div=doc.createElement('div');
                                item.className='grid-stack-item-content';
                                div.appendChild(item);
                                Grid.addWidget(div, template[i]);
                                setCss(template[i].x,template[i].width);
                            }
                            is_updating = true;
                            if (remove) {
                                Grid_el.triggerHandler('change.tile_grid_change');
                            }
                        };

                const wr = doc.createElement('div'),
                        mosaic = doc.createElement('div'),
                        template = doc.createElement('div'),
                        icon = doc.createElement('span'),
                        panel = doc.createElement('div'),
                        presets = doc.createElement('div'),
                        save_preset = doc.createElement('a'),
                        hidden = self.hidden.render({id: data.id, class: 'builder_tile_grid_template', type: 'hidden', control: {control_type: 'change'}}, self),
                        editor = doc.createElement('div'),
                        stack = doc.createElement('div'),
                        actions = doc.createElement('div'),
                        add_tile = doc.createElement('a'),
                        remove_tile = doc.createElement('a'),
                        selectWrap = self.select.render({id: 'layout'}, self),
                        select = selectWrap.querySelector('select');
                wr.className = 'builder_tile_grid';
                mosaic.id = 'mosaic-presets';
                template.className = 'load-template';
                icon.className = 'template-grid-icon';
                panel.className = 'load-template-panel';
                presets.className = 'presets';
                editor.id = 'tile-grid-editor';
                stack.className = 'grid-stack';
                save_preset.href = '#';
                save_preset.id = 'tftp-save-preset';
                save_preset.dataset.prompt= args.labels.name;
                save_preset.textContent = args.labels.save;
                actions.className = 'builder_grid_actions';
                add_tile.className = 'tb_builder_add_tile';
                add_tile.textContent = args.labels.add;
                remove_tile.className = 'tb_builder_remove_tile';
                remove_tile.textContent = args.labels.remove;
                for (let i in data.options) {
                    let a = doc.createElement('a');
                    a.className = 'tb_mosaic_preset';
                    a.width = a.height = 70;
                    a.dataset.index= i;
                    a.dataset.template= data.options[i].template;
                    presets.appendChild(a);
                }
                panel.append(presets,doc.createTextNode(args.labels.custom),doc.createElement('br'),selectWrap);

                icon.append(api.Helper.getIcon('ti-layout-column3'),doc.createTextNode(args.labels.template),api.Helper.getIcon('ti-angle-down'));
                template.append(panel,icon);
                mosaic.append(save_preset,template);

                editor.appendChild(stack);
                actions.append(add_tile,remove_tile);
                wr.append(mosaic,hidden,editor,actions);
                add_tile.tfOn(clickEv, e=> {
                    e.preventDefault();
                    e.stopPropagation();
                    const item=doc.createElement('div'),
                        div=doc.createElement('div');
                    item.className='grid-stack-item-content';
                    div.appendChild(item);
                    const gridItem = Grid.addWidget(div, {x:undefined, y:undefined, width:3, height:2,autoPosition:true});
                    setCss(gridItem.dataset.gsX,gridItem.dataset.gsWidth);
					Grid_el.triggerHandler('change.tile_grid_change');
                });
                remove_tile.tfOn(clickEv, e=> {
                    e.preventDefault();
                    e.stopPropagation();
                    const el = Grid_el.find('.selected');
                    if (el.length > 0) {
                        Grid.removeWidget(el);
                    }
					Grid_el.triggerHandler('change.tile_grid_change');
                });
                select.tfOn('change', function (e) {
                    const id = this.value;
                    if (typeof args.presets[ id ] !== 'object') {
                        return;
                    }
                    update_template(args.presets[ id ].content);
                }, {passive: true});


                presets.tfOn(clickEv, e=> {
                    e.preventDefault();
                    e.stopPropagation();
                    if (e.target.classList.contains('tb_mosaic_preset')) {
                        update_template(e.target.dataset.template);
                    }
                });

                /**
                 * Populate the Custom Presets <select> field
                 */
                const populate_custom_presets = settings=>{
                    if (typeof args.presets === 'object') {
                        const fr = doc.createDocumentFragment();
                        fr.appendChild(doc.createElement('option'));
                        for (let i in args.presets) {
                            let opt = doc.createElement('option');
                            opt.value = i;
                            opt.textContent = args.presets[i].title;
                            if (settings !== undefined && i === settings.layout) {
                                opt.selected = true;
                            }
                            fr.appendChild(opt);
                        }
                        select.innerHTML = '';
                        select.appendChild(fr);
                    }
                };
                save_preset.tfOn(clickEv, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if(Grid_el.length === 0){
                         return;
                    }
                    api.LiteLightBox.prompt(this.dataset.prompt,api.LightBox.el.querySelector('#mod_title').value).then(answer => {
                        if(answer[0]==='yes'){
                            const grid = save_grid(),
                                name=answer[1].trim();
                            api.Spinner.showLoader();
                            api.LocalFetch({
                                action: 'builder_tiled_posts_save_preset',
                                name: name,
                                grid: grid
                            },'text')
                            .then(data=>{
                                args.presets[ data ] = {title: name, content: grid};
                                populate_custom_presets({layout: data});
                            })
                            .finally(()=>{
                                api.Spinner.showLoader('spinhide');
                            });
                        }
                    }).catch(e=>{
                        
                    });
                    
                });
                load_dependencies().then(()=>{
                    let lb = api.LightBox.el;
                    Grid_el = $('.grid-stack', lb);
                    Grid = new top.GridStack.init({
                        float: false,
                        rtl: Themify.isRTL,
                        resizable: {
                            handles: 'e, se, s, sw, w'
                        },
                        disableDrag: false,
                        disableResize: false
                    }, Grid_el[0]);
                    Grid_el.on(clickEv, '.grid-stack-item', function () {// Remove tile
                        $(this).siblings().removeClass('selected').end().addClass('selected');
                    });
                    Grid_el.on('change.tile_grid_change',  (e, items) =>{
                        if (is_updating) {
                            hidden.value = save_grid();
                            Themify.triggerEvent(hidden, 'change');
                        }
                    });
                    Grid.on('change', (e, items) =>{
                        for(let i=items.length-1;i>-1;--i){
                            setCss(items[i].x,items[i].width);
                        }
                    });
                    // add previously added tiles to the grid
                    if (typeof self.values.template !== 'undefined') {
                        update_template(self.values.template, false);
                    }
                    $('#tiled_posts_display', lb).trigger('change.tiled_posts_display');
                    populate_custom_presets(data);

					const setup_ptb_settings = ()=>{
						const ptb_post_type = lb.querySelector( '#post_type_ptb' );
						if ( ptb_post_type ) {
							const inputs = ptb_post_type.tfTag( 'input' ),
								groups = lb.querySelectorAll( '.ptb_mosaic_options > .tb_field_group' ),
								switch_tab = post_type => {
									for ( let i = groups.length - 1; i > -1; --i ) {
										groups[ i ].style.display = groups[ i ].classList.contains( 'group_' + post_type ) ? 'block' : 'none';
									}
								};
							for ( let i = 0; i < inputs.length; i++ ) {
								inputs[ i ].tfOn( 'change', function() {
									switch_tab( this.value );
								} );
							}

							switch_tab( ptb_post_type.querySelector( 'input:checked' ).value );
						}
					};

                    const display = lb.querySelector('#tiled_posts_display'),
						groups = lb.querySelectorAll('.tb_mosaic_tabs > .tb_field_group'),
                        showTabs = v=> {
                            if (v !== undefined) {
                                for ( let i = groups.length - 1; i > -1; --i ) {
									groups[ i ].style.display = groups[ i ].classList.contains( 'group_' + v ) ? 'block' : 'none';
									if ( v === 'ptb' ) {
										setup_ptb_settings();
									}
								}
                            }
                        };
                    display.tfOn('change', function () {
                        showTabs(this.value);
                    });

                    showTabs(self.values[display.id]);

                    Themify.on('themify_builder_lightbox_close', ()=>{
                        if (Grid_el) {
                            Grid_el.off('change');
                        }
                        Grid = Grid_el =clickEv=lb= null;
                    },true);
                });
                return wr;
            }
        };
    };
    
    api.jsModuleLoaded().then(init);
    
    Themify.on('themify_builder_ready',()=>{
        Themify.requestIdleCallback(()=>{
            st.textContent='.grid-stack .grid-stack-item{position:absolute;left:0;top:0;padding:0}.grid-stack.grid-stack-1{width:auto;margin-left:0;height:auto!important}.grid-stack.grid-stack-1 .builder_mosaic_item.grid-stack-item{position:relative;top:0;left:0;width:auto;margin-bottom:20px}';
            top.document.head.appendChild(st);
            
            top.Themify.loadCss(args.admin_css, null, args.v);
            
        },800);
    },true,api.is_builder_ready);

})(jQuery, Themify, tb_app,document);