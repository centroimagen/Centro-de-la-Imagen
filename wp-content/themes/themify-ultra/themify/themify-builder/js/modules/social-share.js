/**
 * social share module
 */

(Themify=>{
    'use strict';
    document.body.tfOn('click',  e=> {
        const target=e.target,
            el = target?.closest('.module-social-share');
        if(el){
            e.preventDefault();
            const url = el.dataset.url || window.location.href,
            type = 'A' === target.tagName ? target.dataset.type : target.parentNode.dataset.type;
            Themify.sharer(type,url,target.dataset.title);
        }
    });

})(Themify);