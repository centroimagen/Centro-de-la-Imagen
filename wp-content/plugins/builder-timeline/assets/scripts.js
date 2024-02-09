(Themify=>{
    'use strict';
    const args=tbLocalScript.addons.timeline,
    config={
        language:args.lng,
        script_path:args.url+'timeline'
    },
    _callback=el=>{
            el.classList.remove('tf_lazy','tf_hidden');
    };
    Themify.on('builder_load_module_partial', (el,isLazy)=>{
        if(isLazy===true && !el.classList.contains('module-timeline')){
            return;
        }
        const items = Themify.selectWithParent('module-timeline',el),
			graph=[];
        for(let i=items.length-1;i>-1;--i){
            if(items[i].classList.contains('layout-list')){
                Themify.loadCss(args.url + 'style','tb_timeline',args.ver).then(()=>{
                    _callback(items[i]);
                });
            }
            else if(items[i].classList.contains('layout-graph') && items[i].tfClass('tl-storyslider')[0] === undefined){
                graph.push(items[i]);
            }
            else{
                _callback(items[i]);
            }
        }
        if(graph.length>0){
            Promise.all([
                Themify.loadCss(args.url + 'timeline/css/timeline.min',null,'3.6.3'),
                Themify.loadJs(args.url + 'timeline/js/timeline.min',!!window.TL,'3.6.3')
            ]).then(()=>{
                for(let i=graph.length-1;i>-1;--i){
                    const embed =graph[i].tfClass('storyjs-embed')[0];
                            config.start_at_end=embed.dataset.startEnd;
                            config.embed_id=embed.id;
                            config.id='story-js-'+embed.dataset.id;
                       const item = new  TL.Timeline(config.id,{events:JSON.parse(embed.dataset.events)},config);
                            _callback(graph[i]);
                       setTimeout(()=>{
                               item.updateDisplay();
                       },1300);
                }
            });
        }
    });

})(Themify);
