(Themify=>{
    'use strict';
    const args=tbLocalScript.addons.audio,
    endCallback = function(){
            const wrap = this.closest('.auto_play');
            this.pause();
            if(wrap!==null){
                    let next = wrap.nextElementSibling;
                    if(next===null || !next.classList.contains('auto_play')){
                            next = wrap.parentNode.tfClass('auto_play')[0];
                    }
                    if(next && next.id!==this.id){
                            this.load();
                            next.tfTag('audio')[0].play();
                    }
            }
    };
    Themify.on('builder_load_module_partial', (el,isLazy)=>{
        if(isLazy===true && !el.classList.contains('module-audio')){
                return;
        }
        const items = Themify.selectWithParent('module-audio',el);
        for(let i=items.length-1;i>-1;--i){
            let autoPlay = items[i].tfClass('auto_play');
            for(let j=autoPlay.length-1;j>-1;--j){
                autoPlay[j].tfTag('audio')[0].tfOff('ended', endCallback, {passive:true})
                    .tfOn('ended', endCallback, {passive:true});
            }
        }
    });
})(Themify);