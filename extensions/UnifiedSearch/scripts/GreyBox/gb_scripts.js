var GB_CURRENT=null;
GB_hide=function(cb){
GB_CURRENT.hide(cb);
};
GreyBox=new AJS.Class({init:function(_2){
this.use_fx=AJS.fx;
this.type="page";
this.overlay_click_close=false;
this.salt=0;
this.root_dir=GB_ROOT_DIR;
this.callback_fns=[];
this.reload_on_close=false;
this.src_loader=this.root_dir+"loader_frame.html";
var _3=window.location.hostname.indexOf("www");
var _4=this.src_loader.indexOf("www");
if(_3!=-1&&_4==-1){
this.src_loader=this.src_loader.replace("://","://www.");
}
if(_3==-1&&_4!=-1){
this.src_loader=this.src_loader.replace("://www.","://");
}
this.show_loading=true;
AJS.update(this,_2);
},addCallback:function(fn){
if(fn){
this.callback_fns.push(fn);
}
},show:function(_6){
GB_CURRENT=this;
this.url=_6;
var _7=[AJS.$bytc("object"),AJS.$bytc("select")];
AJS.map(AJS.flattenList(_7),function(_8){
_8.style.visibility="hidden";
});
this.createElements();
return false;
},hide:function(cb){
var me=this;
AJS.callLater(function(){
var _b=me.callback_fns;
if(_b!=[]){
AJS.map(_b,function(fn){
fn();
});
}
me.onHide();
if(me.use_fx){
var _d=me.overlay;
AJS.fx.fadeOut(me.overlay,{onComplete:function(){
AJS.removeElement(_d);
_d=null;
},duration:300,from:0.3});
AJS.removeElement(me.g_window);
}else{
AJS.removeElement(me.g_window,me.overlay);
}
me.removeFrame();
AJS.REV(window,"scroll",_GB_setOverlayDimension);
AJS.REV(window,"resize",_GB_update);
var _e=[AJS.$bytc("object"),AJS.$bytc("select")];
AJS.map(AJS.flattenList(_e),function(_f){
_f.style.visibility="visible";
});
GB_CURRENT=null;
if(me.reload_on_close){
window.location.reload();
}
if(AJS.isFunction(cb)){
cb();
}
},10);
},update:function(){
this.setOverlayDimension();
this.setFrameSize();
this.setWindowPosition();
},createElements:function(){
this.initOverlay();
this.g_window=AJS.DIV({"id":"GB_window"});
AJS.hideElement(this.g_window);
AJS.getBody().insertBefore(this.g_window,this.overlay.nextSibling);
this.initFrame();
this.initHook();
this.update();
var me=this;
if(this.use_fx){
AJS.fx.fadeIn(this.overlay,{duration:300,to:0.3,onComplete:function(){
me.onShow();
AJS.showElement(me.g_window);
me.startLoading();
}});
}else{
AJS.setOpacity(this.overlay,0.7);
AJS.showElement(this.g_window);
this.onShow();
this.startLoading();
}
AJS.AEV(window,"scroll",_GB_setOverlayDimension);
AJS.AEV(window,"resize",_GB_update);
},removeFrame:function(){
try{
AJS.removeElement(this.iframe);
}
catch(e){
}
this.iframe=null;
},startLoading:function(){
this.iframe.src=this.src_loader+"?s="+this.salt++;
AJS.showElement(this.iframe);
},setOverlayDimension:function(){
var _11=AJS.getWindowSize();
if(AJS.isMozilla()||AJS.isOpera()){
AJS.setWidth(this.overlay,"100%");
}else{
AJS.setWidth(this.overlay,_11.w);
}
var _12=Math.max(AJS.getScrollTop()+_11.h,AJS.getScrollTop()+this.height);
if(_12<AJS.getScrollTop()){
AJS.setHeight(this.overlay,_12);
}else{
AJS.setHeight(this.overlay,AJS.getScrollTop()+_11.h);
}
},initOverlay:function(){
this.overlay=AJS.DIV({"id":"GB_overlay"});
if(this.overlay_click_close){
AJS.AEV(this.overlay,"click",GB_hide);
}
AJS.setOpacity(this.overlay,0);
AJS.getBody().insertBefore(this.overlay,AJS.getBody().firstChild);
},initFrame:function(){
if(!this.iframe){
var d={"name":"GB_frame","class":"GB_frame","frameBorder":0};
if(AJS.isIe()){
d.src="javascript:false;document.write(\"\");";
}
this.iframe=AJS.IFRAME(d);
this.middle_cnt=AJS.DIV({"class":"content"},this.iframe);
this.img_header=this.root_dir+"header_bg.gif";
this.top_cnt1=AJS.DIV({"id":"gb_header_halo1"});
this.top_cnt1.style.backgroundImage="url("+this.img_header+")";
this.top_cnt2=AJS.DIV({"id":"gb_header_halo2"});
this.top_cnt3=AJS.DIV({"id":"gb_header_halo3"});
this.bottom_cnt=AJS.DIV({"id":"gb_footer_halo"});
AJS.ACN(this.g_window,this.top_cnt1,this.top_cnt2,this.top_cnt3,this.middle_cnt,this.bottom_cnt);
}
},onHide:function(){
},onShow:function(){
},setFrameSize:function(){
},setWindowPosition:function(){
},initHook:function(){
}});
_GB_update=function(){
if(GB_CURRENT){
GB_CURRENT.update();
}
};
_GB_setOverlayDimension=function(){
if(GB_CURRENT){
GB_CURRENT.setOverlayDimension();
}
};
AJS.preloadImages(GB_ROOT_DIR+"indicator.gif");
script_loaded=true;
var GB_SETS={};
function decoGreyboxLinks(){
var as=AJS.$bytc("a");
AJS.map(as,function(a){
if(a.getAttribute("href")&&a.getAttribute("rel")){
var rel=a.getAttribute("rel");
if(rel.indexOf("gb_")==0){
var _17=rel.match(/\w+/)[0];
var _18=rel.match(/\[(.*)\]/)[1];
var _19=0;
var sp=_18.split(/, ?/);
var _1b={"caption":a.title||"","url":sp[2],"ajaxurl":a.href};
var _1c=sp[0];
if(_17=="gb_pageset"||_17=="gb_imageset"||_17=="gb_pageset_halo"){
if(!GB_SETS[_1c]){
GB_SETS[_1c]=[];
}
GB_SETS[_1c].push(_1b);
_19=GB_SETS[_1c].length;
}
if(_17=="gb_pageset"){
a.onclick=function(){
GB_showFullScreenSet(GB_SETS[_18],_19);
return false;
};
}
if(_17=="gb_pageset_halo"){
a.onclick=function(){
GB_showSet_halo(GB_SETS[_1c],_19,sp[1]);
return false;
};
}
if(_17=="gb_imageset"){
a.onclick=function(){
GB_showImageSet(GB_SETS[_18],_19);
return false;
};
}
if(_17=="gb_image"){
a.onclick=function(){
GB_showImage(_1b.caption,_1b.url,_19);
return false;
};
}
if(_17=="gb_page"){
a.onclick=function(){
var sp=_18.split(/, ?/);
GB_show(_1b.caption,_1b.url,parseInt(sp[1]),parseInt(sp[0]));
return false;
};
}
if(_17=="gb_page_fs"){
a.onclick=function(){
GB_showFullScreen(_1b.caption,_1b.url);
return false;
};
}
if(_17=="gb_page_center"){
a.onclick=function(){
var sp=_18.split(/, ?/);
GB_showCenter(_1b.caption,_1b.url,parseInt(sp[1]),parseInt(sp[0]));
return false;
};
}
}
}
});
};
AJS.AEV(window,"load",decoGreyboxLinks);
GB_showImage=function(_1f,url,_21){
var _22={width:300,height:300,type:"image",fullscreen:false,center_win:true,caption:_1f,callback_fn:_21};
var win=new GB_Gallery(_22);
return win.show(url);
};
GB_showPage=function(_24,url,_26){
var _27={type:"page",caption:_24,callback_fn:_26,fullscreen:true,center_win:false};
var win=new GB_Gallery(_27);
return win.show(url);
};
GB_Gallery=GreyBox.extend({init:function(_29){
this.parent({});
this.img_close=this.root_dir+"smw_plus_closewindow_icon_16x16.png";
AJS.update(this,_29);
this.addCallback(this.callback_fn);
},initHook:function(){
AJS.addClass(this.g_window,"GB_Gallery");
var _2a=AJS.DIV({"id":"gb_header_halo1_inner"});
var _2b=AJS.DIV({"id":"gb_header_halo1_inner_close"});
this.header1=AJS.$("gb_header_halo1");
AJS.setHTML(_2a,"Close preview");
td_close=AJS.IMG({"class":"close","style":"float:right;margin-top:6px;margin-right:10px;z-index: 500;cursor: pointer",src:this.img_close});
AJS.AEV(td_close,"click",GB_hide);
AJS.ACN(this.header1,td_close,_2a);
this.header2=AJS.$("gb_header_halo2");
GB_STATUS=AJS.SPAN({"id":"GB_navStatus"});
AJS.ACN(this.header2,GB_STATUS);
this.header3=AJS.$("gb_header_halo3");
var _2c=AJS.DIV({"class":"inner"});
AJS.ACN(this.header3,_2c);
var _2d=AJS.DIV({"id":"GB_caption","class":"caption"},this.caption);
AJS.ACN(_2c,_2d);
this.footer=AJS.$("gb_footer_halo");
var foo=AJS.DIV({"class":"inner"});
AJS.ACN(this.footer,foo);
var _2f=AJS.DIV({"id":"GB_middle","class":"middle","width":"100%"});
AJS.ACN(foo,_2f);
if(this.fullscreen){
AJS.AEV(window,"scroll",AJS.$b(this.setWindowPosition,this));
}else{
AJS.AEV(window,"scroll",AJS.$b(this._setHeaderPos,this));
}
},setFrameSize:function(){
var _30=this.overlay.offsetWidth;
var _31=AJS.getWindowSize();
if(this.fullscreen){
this.width=_30-40;
this.height=_31.h-80;
}
AJS.setWidth(this.iframe,this.width);
AJS.setHeight(this.iframe,this.height);
this.restorex=this.width;
this.restorey=this.height;
},_setHeaderPos:function(){
AJS.setTop(this.header1,AJS.getScrollTop()+10);
},setWindowPosition:function(){
var _32=this.overlay.offsetWidth;
var _33=AJS.getWindowSize();
if(this.is_halo){
AJS.setLeft(this.g_window,(3*(_32-50-this.width)/4));
}else{
AJS.setLeft(this.g_window,((_32-50-this.width)/2));
}
var _34=AJS.getScrollTop()+55;
if(!this.center_win){
AJS.setTop(this.g_window,_34);
}else{
var fl=((_33.h-this.height)/2)+20+AJS.getScrollTop();
if(fl<0){
fl=0;
}
if(_34>fl){
fl=_34;
}
AJS.setTop(this.g_window,fl);
}
this._setHeaderPos();
},onHide:function(){
AJS.removeElement(this.header1);
AJS.removeClass(this.g_window,"GB_Gallery");
},onShow:function(){
if(this.use_fx){
AJS.fx.fadeIn(this.header1,{to:1});
}else{
AJS.setOpacity(this.header1,1);
}
}});
AJS.preloadImages(GB_ROOT_DIR+"smw_plus_closewindow_icon_16x16.png",GB_ROOT_DIR+"smw_plus_maximize_icon_16x16.png");
GB_showFullScreenSet=function(set,_37,_38){
var _39={type:"page",fullscreen:true,center_win:false};
var _3a=new GB_Sets(_39,set);
_3a.addCallback(_38);
_3a.showSet(_37-1);
return false;
};
GB_showSet_halo=function(set,_3c,_3d,_3e){
var _3f=0,_40=0;
if(typeof (window.innerWidth)=="number"){
_3f=window.innerWidth;
_40=window.innerHeight;
}else{
if(document.documentElement&&(document.documentElement.clientWidth||document.documentElement.clientHeight)){
_3f=document.documentElement.clientWidth;
_40=document.documentElement.clientHeight;
}else{
if(document.body&&(document.body.clientWidth||document.body.clientHeight)){
_3f=document.body.clientWidth;
_40=document.body.clientHeight;
}
}
}
_3f=_3f*0.5;
_40=_40*0.5;
var _41={type:"page",fullscreen:false,center_win:true,height:_40,width:_3f,overlay_click_close:true,searchterm:_3d,is_halo:true};
var _42=new GB_Sets(_41,set);
_42.addCallback(_3e);
_42.showSet(_3c-1);
return false;
};
GB_showImageSet=function(set,_44,_45){
var _46={type:"image",fullscreen:false,center_win:true,width:300,height:300};
var _47=new GB_Sets(_46,set);
_47.addCallback(_45);
_47.showSet(_44-1);
return false;
};
GB_Sets=GB_Gallery.extend({init:function(_48,set){
this.parent(_48);
if(!this.img_next){
this.img_next=this.root_dir+"smw_plus_next_icon_16x16.png";
}
if(!this.img_prev){
this.img_prev=this.root_dir+"smw_plus_prev_icon_16x16.png";
}
this.current_set=set;
},showSet:function(_4a){
this.current_index=_4a;
var _4b=this.current_set[this.current_index];
this.show(_4b.ajaxurl);
this._setCaption(_4b.caption,_4b.url);
var _4c=AJS.DIV({"id":"lefttext","style":"float:left;margin-left:16px"});
var _4d=AJS.DIV({"id":"righttext","style":"float:right;margin-right:16px"});
this.btn_prev=AJS.IMG({"class":"left",src:this.img_prev});
AJS.setHTML(_4c,"Previous search result");
this.btn_next=AJS.IMG({"class":"right",src:this.img_next});
AJS.setHTML(_4d,"Next search result");
AJS.AEV(this.btn_prev,"click",AJS.$b(this.switchPrev,this));
AJS.AEV(this.btn_next,"click",AJS.$b(this.switchNext,this));
AJS.ACN(AJS.$("GB_middle"),this.btn_prev,_4c,this.btn_next,_4d);
this.updateStatus();
},updateStatus:function(){
AJS.setHTML(GB_STATUS,"<b>Preview</b> Result <b>"+(this.current_index+1)+"</b> of <b>"+this.current_set.length+"</b> for <b>"+this.searchterm+"</b>");
if(this.current_index==0){
AJS.addClass(this.btn_prev,"disabled");
}else{
AJS.removeClass(this.btn_prev,"disabled");
}
if(this.current_index==this.current_set.length-1){
AJS.addClass(this.btn_next,"disabled");
}else{
AJS.removeClass(this.btn_next,"disabled");
}
},_setCaption:function(_4e,url){
AJS.setHTML(AJS.$("GB_caption"),"<a href=\""+url+"\" style=\"text-decoration:none;float:left;\"><b>"+_4e+"</b></a>");
var _50=AJS.DIV({"id":"GB_rightcaption"});
AJS.setHTML(_50,"<a href=\""+url+"\" style=\"text-decoration:none;\"><b>"+this.cut_caption(url)+"</b></a>");
AJS.ACN(AJS.$("GB_caption"),_50);
},cut_caption:function(url){
if(url.length>40){
return url.substr(0,40)+" [...]";
}
return url;
},updateFrame:function(){
var _52=this.current_set[this.current_index];
this._setCaption(_52.caption,_52.url);
this.url=_52.ajaxurl;
this.startLoading();
},switchPrev:function(){
if(this.current_index!=0){
this.current_index--;
this.updateFrame();
this.updateStatus();
}
},switchNext:function(){
if(this.current_index!=this.current_set.length-1){
this.current_index++;
this.updateFrame();
this.updateStatus();
}
}});
AJS.AEV(window,"load",function(){
AJS.preloadImages(GB_ROOT_DIR+"smw_plus_next_icon_16x16.png",GB_ROOT_DIR+"smw_plus_prev_icon_16x16.png",GB_ROOT_DIR+"smw_plus_next_highl_icon_16x16.png",GB_ROOT_DIR+"smw_plus_prev_highl_icon_16x16.png");
});
GB_show=function(_53,url,_55,_56,_57){
var _58={caption:_53,height:_55||500,width:_56||500,fullscreen:false,callback_fn:_57};
var win=new GB_Window(_58);
return win.show(url);
};
GB_showCenter=function(_5a,url,_5c,_5d,_5e){
var _5f={caption:_5a,center_win:true,height:_5c||500,width:_5d||500,fullscreen:false,callback_fn:_5e};
var win=new GB_Window(_5f);
return win.show(url);
};
GB_showFullScreen=function(_61,url,_63){
var _64={caption:_61,fullscreen:true,callback_fn:_63};
var win=new GB_Window(_64);
return win.show(url);
};
GB_Window=GreyBox.extend({init:function(_66){
this.parent({});
this.img_header=this.root_dir+"header_bg.gif";
this.img_close=this.root_dir+"smw_plus_closewindow_icon_16x16.png";
this.show_close_img=true;
AJS.update(this,_66);
this.addCallback(this.callback_fn);
},initHook:function(){
AJS.addClass(this.g_window,"GB_Window");
this.header=AJS.TABLE({"class":"header"});
this.header.style.backgroundImage="url("+this.img_header+")";
var _67=AJS.TD({"class":"caption"},this.caption);
var _68=AJS.TD({"class":"close"});
if(this.show_close_img){
var _69=AJS.IMG({"src":this.img_close});
var _6a=AJS.SPAN("Close");
var btn=AJS.DIV(_69,_6a);
AJS.AEV([_69,_6a],"mouseover",function(){
AJS.addClass(_6a,"on");
});
AJS.AEV([_69,_6a],"mouseout",function(){
AJS.removeClass(_6a,"on");
});
AJS.AEV([_69,_6a],"mousedown",function(){
AJS.addClass(_6a,"click");
});
AJS.AEV([_69,_6a],"mouseup",function(){
AJS.removeClass(_6a,"click");
});
AJS.AEV([_69,_6a],"click",GB_hide);
AJS.ACN(_68,btn);
}
tbody_header=AJS.TBODY();
AJS.ACN(tbody_header,AJS.TR(_67,_68));
AJS.ACN(this.header,tbody_header);
AJS.ACN(this.top_cnt,this.header);
if(this.fullscreen){
AJS.AEV(window,"scroll",AJS.$b(this.setWindowPosition,this));
}
},setFrameSize:function(){
if(this.fullscreen){
var _6c=AJS.getWindowSize();
overlay_h=_6c.h;
this.width=Math.round(this.overlay.offsetWidth-(this.overlay.offsetWidth/100)*10);
this.height=Math.round(overlay_h-(overlay_h/100)*10);
}
AJS.setWidth(this.header,this.width+6);
AJS.setWidth(this.iframe,this.width);
AJS.setHeight(this.iframe,this.height);
},setWindowPosition:function(){
var _6d=AJS.getWindowSize();
if(this.is_halo){
AJS.setLeft(this.g_window,(3*(_6d.w-this.width)/4)-13);
}else{
AJS.setLeft(this.g_window,((_6d.w-this.width)/2)-13);
}
if(!this.center_win){
AJS.setTop(this.g_window,AJS.getScrollTop());
}else{
var fl=((_6d.h-this.height)/2)-20+AJS.getScrollTop();
if(fl<0){
fl=0;
}
AJS.setTop(this.g_window,fl);
}
}});
AJS.preloadImages(GB_ROOT_DIR+"smw_plus_closewindow_icon_16x16.png",GB_ROOT_DIR+"header_bg.gif");


script_loaded=true;