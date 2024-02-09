(Themify=>{
    'use strict';
    if (!Themify.is_builder_active) {
		Themify.on('builder_load_module_partial', ( el, isLazy)=>{
			if (isLazy === true && !el.classList.contains('module-infinite-posts')) {
				return;
			}
			const items=Themify.selectWithParent('module-infinite-posts',el);
			for(let i=items.length-1;i>-1;--i){
				let cl=items[i].classList;
				if(!cl.contains('tb_infinite_scroll_done') && (cl.contains('pagination-infinite-scroll') || cl.contains('pagination-load-more'))){
					cl.add('tb_infinite_scroll_done');
					Themify.infinity(items[i].tfClass('builder-infinite-posts-wrap')[0],{
						id: '#' + items[i].id + ' .builder-infinite-posts-wrap', // selector for all items you'll retrieve
						scrollThreshold:!cl.contains('pagination-load-more'),
						history:false
					});
				}
			}
		});
    }
})(Themify);
