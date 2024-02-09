var PTB;
(($,doc,und,vars)=>{
    'use strict';
    PTB = {
        jsLazy:new Map(),
        cssLazy:new Map(),
        mobile:null,
        v:vars.ver,
		hash(s) {
            let hash = 0;
            for (let i = s.length - 1; i > -1; --i) {
                hash = ((hash << 5) - hash) + s.charCodeAt(i);
                hash = hash & hash; // Convert to 32bit integer
            }
            return hash;
        },
        imagesLoad(items) {
            return new Promise(resolve=>{
                if (items !== null) {
                    if (items.length === und) {
                        items = [items];
                    }
                    const prms=[];
                    for(let i=items.length-1;i>-1;--i){
                        let images=items[i].tagName==='IMG'?[items[i]]:items[i].getElementsByTagName('img');
                        for(let j=images.legnth-1;j>-1;--j){
                            if(!images[j].complete){
                                let elem=images[j];
                                prms.push(new Promise((resolve, reject) => {
                                    elem.onload = resolve;
                                    elem.onerror = reject;
                                    elem=null;
                                }));
                            }
                        }
                    }
                    Promise.all(prms).finally(()=>{
                        resolve(items[0]); 
                    });
                }
                else{
                    resolve();
                }
            });
        },
        loadJs(src, test, version, async) {
            let origSrc=src,
                pr=this.jsLazy.get(origSrc);
            if(pr===und){
                pr=new Promise((resolve,reject)=>{
                    if(test===true){
                        requestAnimationFrame(resolve);
                        return;
                    }
                     if (src.indexOf('.min.js') === -1 && vars.min!==und && vars.min.js!==und) {
                        const name = src.match(/([^\/]+)(?=\.\w+$)/);
                        if (name && name[0] && vars.min.js[name[0]]!==und) {
                            src = src.replace('.js','.min.js');
                        }
                    }
                    if (version !== false && src.indexOf('ver=')===-1) {
                        if(!version){
                            version = this.v;
                        }
                        src+='?ver=' + version;
                    }
                    const s = doc.createElement('script');
                    s.async=!!async;
                    s.addEventListener('load', ()=> {
                        requestAnimationFrame(resolve);
                    }, {passive: true, once: true});
                    s.addEventListener('error', reject, {passive: true, once: true});
                    s.src=src;
                    requestAnimationFrame(()=>{
                        doc.head.appendChild(s);
                    });
                });
                this.jsLazy.set(origSrc,pr);
            }
            return pr;
        },
        LoadAsync (src, callback, version, test) {//backward
            this.loadJs(src,test,version).then(callback);
        },
        loadCss(href,id, version, before, media) {
            if(!id){
                id = 'ptb_'+this.hash(href);
            }
            let prms=this.cssLazy.get(id);
            if (prms===und) {
                prms=new Promise((resolve,reject)=>{
                    const d=before?before.getRootNode():doc,
                        el = d.getElementById(id);
                        if(el!==null && el.media!== 'print'){
                            resolve();
                            return;
                        }
               
                const ss = doc.createElement('link'),
                        onload = function () {
                            if (!media) {
								media = 'all';
							}
                            this.media=media;
                            const key = this.id,
                                    checkApply = ()=>{
                                        const sheets = this.getRootNode().styleSheets;
                                        let found = false;
                                        for (let i = sheets.length - 1; i > -1; --i) {
                                            if (sheets[i].ownerNode!==null && sheets[i].ownerNode.id === key) {
                                                found = true;
                                                break;
                                            }
                                        }
                                        if (found === true) {
                                            resolve();
                                        }
                                        else {
                                            requestAnimationFrame(()=>{
                                                checkApply();
                                            });
                                        }
                                    };
                                requestAnimationFrame(()=>{
                                    checkApply();
                                });
                        };
                        if (href.indexOf('.min.css') === -1 && vars.min!==und && vars.min.css!==und) {
                            const name = href.match(/([^\/]+)(?=\.\w+$)/);
                            if (name && name[0] && vars.min.css[name[0]]!==und) {
                                href = href.replace('.css', '.min.css');
                            }
                        }
                        if (version !== false && href.indexOf('ver=')===-1) {
                            if(!version){
                                version = this.v;
                            }
                            href+='?ver=' + version;
                        }
                        ss.rel='stylesheet';
                        ss.media='print';
                        ss.id=id;
                        ss.href=href;
                        ss.setAttribute('fetchpriority', 'low');
                        if ('isApplicationInstalled' in navigator) {
                            ss.onloadcssdefined(onload);
                        } else {
                            ss.addEventListener('load', onload, {passive: true, once: true});
                        }
                        ss.addEventListener('error', reject, {passive: true, once: true});
                        let ref=before;
                        if (!ref){
                            const refs = (doc.body || doc.getElementsByTagName('head')[ 0 ]).childNodes;
                            ref = refs[ refs.length - 1];
                        }
                        requestAnimationFrame(()=>{ 
                            ref.parentNode.insertBefore(ss, (before ? ref : ref.nextSibling));
                        });
                    });
                    this.cssLazy.set(id,prms);
            }
            else if(prms===true){
                prms=Promise.resolve();
                this.cssLazy.set(id,prms);
            }
            else if(before){//maybe it's shadow root,need to recheck
                const el=before.getRootNode().getElementById(id);
                if(el===null){
                    this.cssLazy.delete(id);
                    return this.loadCss(href,id, version, before, media);
                }
            }
            return prms;
        },
        LoadCss(href, version, before, media,callback) {//backward
            this.loadCss(href,null,version,before,media).then(callback);
        },
        is_mobile(){
            if(this.mobile===null){
                this.mobile = /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) ||
                /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0, 4));
            }
            return this.mobile;
        },
		async init( context = doc ) {
            const modules=vars.modules;
			for ( let module in modules) {
				if ( context.querySelector( modules[ module ].selector ) ) {
                    await this.loadJs( modules[ module ].js);
					let event = new CustomEvent( 'ptb_' + module + '_init', {
                        detail : {
                            context : context
                        }
                    } );
                    doc.body.dispatchEvent( event );
				}
			}
		}
	};

	window.addEventListener('load', ()=>{
        $( doc ).trigger( 'ptb_loaded', false );

		PTB.init();
   }, {once:true, passive:true});
})(jQuery,document,undefined,ptb);