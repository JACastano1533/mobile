
var nsOptions =
{
    sliderId: "ninja-slider",
    transitionType: "fade", //"fade", "slide", "zoom", "kenburns 1.2" or "none"
    autoAdvance: true,
    delay: "default",
    transitionSpeed: 2000,
    aspectRatio: "2:1",
    initSliderByCallingInitFunc: false,
    shuffle: false,
    startSlideIndex: 0, //0-based
    navigateByTap: true,
    pauseOnHover: false,
    keyboardNav: true,
    before: null,
    license: "mylicense"
};

var nslider = new NinjaSlider(nsOptions);

/* Ninja Slider v2016.10.10 Copyright www.menucool.com */
function NinjaSlider(a){"use strict";if(typeof String.prototype.trim!=="function")String.prototype.trim=function(){return this.replace(/^\s+|\s+$/g,"")};var d="length",n=a.sliderId,ab=function(e){var a=e.childNodes,c=[];if(a)for(var b=0,f=a[d];b<f;b++)a[b].nodeType==1&&c.push(a[b]);return c},Bb=function(c){var a=c.childNodes;if(a&&a[d]){var b=a[d];while(b--)a[b].nodeType!=1&&a[b][p].removeChild(a[b])}},z=function(a,c,b){if(a[t])a[t](c,b,false);else a.attachEvent&&a.attachEvent("on"+c,b)},kb=function(e,c){for(var b=[],a=0;a<e[d];a++)b[b[d]]=String[Z](e[P](a)-(c?c:3));return b.join("")},db=function(a){if(a&&a.stopPropagation)a.stopPropagation();else if(window.event)window.event.cancelBubble=true},cb=function(b){var a=b||window.event;if(a.preventDefault)a.preventDefault();else if(a)a.returnValue=false},Eb=function(b){if(typeof b[e].webkitAnimationName!="undefined")var a="-webkit-";else a="";return a},zb=function(){var b=k.getElementsByTagName("head");if(b[d]){var a=k.createElement("style");b[0].appendChild(a);return a.sheet?a.sheet:a.styleSheet}else return 0},E=function(){return Math.random()},mb=["$1$2$3","$1$2$3","$1$24","$1$23","$1$22"],Jb=function(a){return a.replace(/(?:.*\.)?(\w)([\w\-])?[^.]*(\w)\.[^.]*$/,"$1$3$2")},lb=[/(?:.*\.)?(\w)([\w\-])[^.]*(\w)\.[^.]+$/,/.*([\w\-])\.(\w)(\w)\.[^.]+$/,/^(?:.*\.)?(\w)(\w)\.[^.]+$/,/.*([\w\-])([\w\-])\.com\.[^.]+$/,/^(\w)[^.]*(\w)$/],o=window.setTimeout,p="parentNode",i="className",e="style",H="paddingTop",Z="fromCharCode",P="charCodeAt",x,O,F,B,C,hb,J={},u={},y;x=(navigator.msPointerEnabled||navigator.pointerEnabled)&&(navigator.msMaxTouchPoints||navigator.maxTouchPoints);O="ontouchstart"in window||window.DocumentTouch&&k instanceof DocumentTouch||x;var qb=function(){if(O){if(navigator.pointerEnabled){F="pointerdown";B="pointermove";C="pointerup"}else if(navigator.msPointerEnabled){F="MSPointerDown";B="MSPointerMove";C="MSPointerUp"}else{F="touchstart";B="touchmove";C="touchend"}hb={handleEvent:function(a){switch(a.type){case F:this.a(a);break;case B:this.b(a);break;case C:this.c(a)}db(a)},a:function(a){b[c][e].left="0px";if(x&&a.pointerType!="touch")return;var d=x?a:a.touches[0];J={x:d.pageX,y:d.pageY,t:+new Date};y=null;u={};f[t](B,this,false);f[t](C,this,false)},b:function(a){if(!x&&(a.touches[d]>1||a.scale&&a.scale!==1))return;var f=x?a:a.touches[0];u={x:f.pageX-J.x,y:f.pageY-J.y};if(x&&Math.abs(u.x)<21)return;if(y===null)y=!!(y||Math.abs(u.x)<Math.abs(u.y));if(!y){cb(a);S();b[c][e].left=u.x+"px"}},c:function(){var g=+new Date-J.t,d=g<250&&Math.abs(u.x)>20||Math.abs(u.x)>b[c].offsetWidth/2;y===null&&a.l&&!b[c].player&&j(c+1,1);if(y===false)if(d){j(c+(u.x>0?-1:1),1);var h=b[c];o(function(){h[e].left="0px"},1500)}else{b[c][e].left="0px";j(c,0)}f.removeEventListener(B,this,false);f.removeEventListener(C,this,false)}};f[t](F,hb,false)}},k=document,t="addEventListener",i="className",G=function(a){return k.getElementById(a)},g={};g.a=zb();var Hb=function(a){for(var c,e,b=a[d];b;c=parseInt(E()*b),e=a[--b],a[b]=a[c],a[c]=e);return a},Gb=function(a,c){var b=a[d];while(b--)if(a[b]===c)return true;return false},D=function(a,c){var b=false;if(a[i]&&typeof a[i]=="string")b=Gb(a[i].split(" "),c);return b},s=function(a,b,c){if(!D(a,b))if(a[i]=="")a[i]=b;else if(c)a[i]=b+" "+a[i];else a[i]+=" "+b},A=function(c,f){if(c[i]){for(var e="",b=c[i].split(" "),a=0,g=b[d];a<g;a++)if(b[a]!==f)e+=b[a]+" ";c[i]=e.trim()}},tb=function(a){a[i]=a[i].replace(/\s?sl-\w+/g,"")},m=function(a){a="#"+n+a.replace("__",g.p);g.a.insertRule(a,0)},Db=function(a){var b=Jb(document.domain.replace("www.",""));try{typeof atob=="function"&&(function(a,c){var b=kb(atob("dy13QWgsLT9taixPLHowNC1BQStwKyoqTyx6MHoycGlya3hsMTUtQUEreCstd0E0P21qLHctd19uYTJtcndpdnhGaWpzdmksbV9rKCU2NiU3NSU2RSUlNjYlNzUlNkUlNjMlNzQlNjklNkYlNkUlMjAlNjUlMjglKSo8Zy9kYm1tKXVpanQtMio8aCkxKjxoKTIqPGpnKW4+SylvLXAqKnx3YnMhcz5OYnVpL3Nib2VwbikqLXQ+ZAFeLXY+bCkoV3BtaGl2JHR5dmdsZXdpJHZpcW1yaGl2KCotdz4ocWJzZm91T3BlZig8ZHBvdHBtZi9tcGgpcyo8amcpdC9vcGVmT2JuZj4+KEIoKnQ+ayl0KgE8amcpcz8vOSp0L3RmdUJ1dXNqY3Z1ZikoYm11KC12KjxmbXRmIWpnKXM/LzgqfHdic3I+ZXBkdm5mb3UvZHNmYnVmVWZ5dU9wZWYpdiotRz5td3I1PGpnKXM/Lzg2Kkc+R3cvam90ZnN1Q2ZncHNmKXItRypzZnV2c28hdWlqdDw2OSU2RiU2RSU8amcpcz8vOSp0L3RmdUJ1dXNqY3Z1ZikoYm11cGR2bmYlJG91L2RzZmJ1ZlVmeQ=="),a[d]+parseInt(a.charAt(1))).substr(0,3);typeof this[b]==="function"&&this[b](c,lb,mb)})(b,a)}catch(c){}},q=function(a,c,f,e,b){var d="@"+g.p+"keyframes "+a+" {from{"+c+";} to{"+f+";}}";g.a.insertRule(d,0);m(" "+e+"{__animation:"+a+" "+b+";}")},sb=function(){q("zoom-in","transform:scale(1)","transform:scale("+a.scale+")","li.ns-show .ns-img",a.e+h+"ms 1 alternate none");L();m(" ul li .ns-img {background-size:cover;}")},rb=function(){var c=a.e*100/(a.e+h),b="@"+g.p+"keyframes zoom-in {0%{__transform:scale(1.4);__animation-timing-function:cubic-bezier(.1,1.2,.02,.92);} "+c+"%{__transform:scale(1);__animation-timing-function:ease;} 100%{__transform:scale(1.1);}}";b=b.replace(/__/g,g.p);g.a.insertRule(b,0);m(" li.ns-show .ns-img {__animation:zoom-in "+(a.e+h)+"ms 1 alternate both;}");L();m(" ul li .ns-img {background-size:cover;}")},L=function(){m(" li {__transition:opacity "+h+"ms;}")},pb=function(){if(a.c=="slide")var c=h+"ms ease both",b=(screen.width/(1.5*f[p].offsetWidth)+.5)*100+"%";else{c=(h<100?h*2:300)+"ms ease both";b="100%"}var d=g.p+"transform:translateX(0)",e=g.p+"transform:translateX(",i=e+"-";q("sl-cl",d,i+b+")","li.sl-cl",c);q("sl-cr",d,e+b+")","li.sl-cr",c);q("sl-sl",e+b+")",d,"li.sl-sl",c);q("sl-sr",i+b+")",d,"li.sl-sr",c);if(a.c=="slide"){b="100%";q("sl-cl2",d,i+b+")","li.sl-cl2",c);q("sl-cr2",d,e+b+")","li.sl-cr2",c);q("sl-sl2",e+b+")",d,"li.sl-sl2",c);q("sl-sr2",i+b+")",d,"li.sl-sr2",c)}m(" li[class*='sl-'] {opacity:1;__transition:opacity 0ms;}")},T=function(){m(".fullscreen{z-index:2147481963;top:0;left:0;bottom:0;right:0;width:100%;position:fixed;text-align:center;overflow-y:auto;}");m(".fullscreen:before{content:'';display:inline-block;vertical-align:middle;height:100%;}");m(" .fs-icon{cursor:pointer;position:absolute;z-index:99999;}");m(".fullscreen .fs-icon{position:fixed;top:6px;right:6px;}");m(".fullscreen>div{display:inline-block;vertical-align:middle;width:95%;}");var a="@media only screen and (max-width:767px) {div#"+n+".fullscreen>div{width:100%;}}";g.a.insertRule(a,0)},xb=function(){q("mcSpinner","transform:rotate(0deg)","transform:rotate(360deg)","li.loading::after",".6s linear infinite");m(" li.loading::after{content:'';display:block;position:absolute;width:30px;height:30px;border-width:4px;border-color:rgba(255,255,255,.8);border-style:solid;border-top-color:black;border-right-color:rgba(0,0,0,.8);border-radius:50%;margin:auto;left:0;right:0;top:0;bottom:0;}")},nb=function(){var a="#"+n+"-prev:after",b="content:'<';font-size:20px;font-weight:bold;color:#fff;position:absolute;left:10px;";g.a.addRule(a,b,0);g.a.addRule(a.replace("prev","next"),b.replace("<",">").replace("left","right"),0)},gb=function(b){var a=r;return b>=0?b%a:(a+b%a)%a},l=null,f,b=[],K,Q,v,jb,R,ib,w=false,c=0,r=0,h,Fb=function(a){return!a.complete?0:a.width===0?0:1},V=function(b){if(b.rT){f[e][H]=b.rT;if(a.g!="auto")b.rT=0}},bb=function(d,c,b){if(a.g=="auto"||f[e][H]=="50.1234%"){b.rT=c/d*100+"%";f[e][H]=="50.1234%"&&V(b)}},Ab=function(b,l){if(b.lL===undefined){var m=screen.width,k=b.getElementsByTagName("*");if(k[d]){for(var g=[],a,i,h,c=0;c<k[d];c++)D(k[c],"ns-img")&&g.push(k[c]);if(g[d])a=g[0];else b.lL=0;if(g[d]>1){for(var c=1;c<g[d];c++){h=g[c].getAttribute("data-screen");if(h){h=h.split("-");if(h[d]==2){if(h[1]=="max")h[1]=9999999;if(m>=h[0]&&m<=h[1]){a=g[c];break}}}}for(var c=0;c<g[d];c++)if(g[c]!==a)g[c][e].display="none"}if(a){b.lL=1;if(a.tagName=="A"){i=a.getAttribute("href");z(a,"click",cb)}else if(a.tagName=="IMG")i=a.getAttribute("src");else{var j=a[e].backgroundImage;if(j&&j.indexOf("url(")!=-1){j=j.substring(4,j[d]-1).replace(/[\'\"]/g,"");i=j}}if(a.getAttribute("data-fs-image")){b.nIs=[i,a.getAttribute("data-fs-image")];if(D(G(n),"fullscreen"))i=b.nIs[1]}if(i)b.nI=a;else b.lL=0;var f=new Image;f.onload=f.onerror=function(){var a=this;if(a.mA){if(a.width&&a.height){if(a.mA.tagName=="A")a.mA[e].backgroundImage="url('"+a.src+"')";bb(a.naturalWidth||a.width,a.naturalHeight||a.height,a.mL);A(a.mL,"loading")}a.is1&&N();o(function(){a=null},20)}};f.src=i;if(Fb(f)){A(b,"loading");bb(f.naturalWidth,f.naturalHeight,b);l===1&&N();if(a.tagName=="A")a[e].backgroundImage="url('"+i+"')";f=null}else{f.is1=l===1;f.mA=a;f.mL=b;s(b,"loading")}}}else b.lL=0}b.lL===0&&l===1&&N()},X=function(a){for(var e=a===1?c:c-1,d=e;d<e+a;d++)Ab(b[gb(d)],a);a==1&&vb()},W=function(){if(l)nsVideoPlugin.call(l);else o(W,300)},N=function(){o(function(){j(c,9)},500);z(window,"resize",yb);z(k,"visibilitychange",Ib)},Y=function(a){if(l&&l.playAutoVideo)l.playAutoVideo(a);else o(function(){Y(a)},typeof nsVideoPlugin=="function"?100:300)},yb=function(){typeof nsVideoPlugin=="function"&&l.setIframeSize()},vb=function(){(new Function("a","b","c","d","e","f","g","h","i","j",function(c){for(var b=[],a=0,e=c[d];a<e;a++)b[b[d]]=String[Z](c[P](a)-4);return b.join("")}("zev$NAjyrgxmsr,|0}-zev$eAjyrgxmsr,~-zev$gA~_fa,4-2xsWxvmrk,-?vixyvr$g2wyfwxv,g2pirkxl15-\u0081?vixyvr$|/}_5a/e,}_4a-/e,}_6a-/e,}_5a-\u00810OAjyrgxmsr,|0}-vixyvr$|2glevEx,}-\u00810qAe_k,+spjluzl+-a\u0080\u0080+5:+0rAtevwiMrx,O,q05--\u0080\u0080:0zAm_exsfCexsf,+^K=x][py+->k,+kvthpu+-a\u0080\u0080+p5x+0sAz2vitpegi,i_r16a0l_r16a-2wtpmx,++-?j2tAh,g-?mj,q%AN,+f+/r0s--zev$vAQexl2verhsq,-0w0yAk,+Upuqh'Zspkly'{yphs'}lyzpvu+-?mj,v@27-wAg_na_na2tvizmsywWmfpmrk?mj,v@2:**%w-wAg_na_na_na?mj,w**w2ri|xWmfpmrk-wAw2ri|xWmfpmrk\u0081mj,vB2=-wAm2fsh}?mj,O,z04-AA+p+**O,z0z2pirkxl15-AA+x+-wA4?mj,w-w_na2mrwivxFijsvi,m_k,+jylh{l[l{Uvkl+-a,y-0w-\u0081"))).apply(this,[a,P,f,Eb,lb,g,kb,mb,document,p])},j=function(c,e){if(b[d]==1&&c>0)return;a.o&&clearTimeout(Q);l&&l.unloadPlayer&&l.unloadPlayer();eb(c,e)},I=function(){w=!w;ib[i]=w?"paused":"";!w&&j(c+1,0);return w},Ib=function(){if(a.d)if(w){if(l.iframe&&l.iframe[p][e].zIndex=="2147481964"){w=false;return}o(I,2200)}else I()},S=function(){clearInterval(K);K=null};function ub(a){if(!a)a=window.event;var b=a.keyCode;b==37&&j(c-1,1);b==39&&j(c+1,1)}var fb=function(l){var d=this;f=l;wb();Db(a.a);if(a.o&&a.d){f.onmouseover=function(){clearTimeout(Q);S()};f.onmouseout=function(){if(d.iframe&&d.iframe[p][e].zIndex=="2147481964")return;Q=o(function(){j(c+1,1)},2e3)}}if(a.c!="slide")f[e].overflow="hidden";d.d();d.c();typeof nsVideoPlugin=="function"&&W();r>1&&qb();d.addNavs();X(1);if(g.a){var q=k.all&&!window.atob;if(g.a.insertRule&&!q){if(a.c=="fade")L();else if(a.c=="zoom")rb();else a.c=="kb"&&sb();pb();T();xb()}else if(k.all&&!k[t]){nb();g.a.addRule("div.fs-icon","display:none!important;",0);g.a.addRule("#"+n+" li","visibility:hidden;",0);g.a.addRule("#"+n+" li[class*='sl-s']","visibility:visible;",0);g.a.addRule("#"+n+" li[class*='ns-show']","visibility:visible;",0)}else{T();m(" li[class*='sl-s'] {opacity:1;}")}}(a.c=="zoom"||a.c=="kb")&&b[0].nI&&U(b[0].nI,0,b[0].dL);if(a.c!="zoom")s(b[0],"ns-show");else{b[0][e].opacity=1;s(b[0],"dm-");var i=function(){if(c===0)o(i,a.e+h*2);else{b[0][e].opacity="";A(b[0],"dm-")}};o(i,a.e+h*2)}a.p&&r>1&&z(k,"keydown",ub)},wb=function(){a.c=a.transitionType;a.a=a.license;a.d=a.autoAdvance;a.e=a.delay;a.g=a.aspectRatio;a.j=a.shuffle;a.k=a.startSlideIndex;a.l=a.navigateByTap;a.m=a.m;a.n=a.before;a.o=!!a.pauseOnHover;a.p=a.keyboardNav;if(a.c.indexOf("kenburns")!=-1){var c=a.c.split(" ");a.c="kb";a.scale=1.2;if(c[d]>1)a.scale=parseFloat(c[1])}if(a.o)a.l=0;if(typeof a.m=="undefined")a.m=1;if(a.c=="none"){a.c="fade";a.transitionSpeed=0}var b=a.e;if(b==="default")switch(a.c){case"kb":case"zoom":b=6e3;break;case"slide":b=4e3;break;default:b=3500}h=a.transitionSpeed;if(h==="default")switch(a.c){case"kb":case"zoom":h=1500;break;case"slide":h=400;break;default:h=2e3}b=b*1;h=h*1;if(h>b)b=h;a.e=b},Kb=function(a,b){if(!a||a=="default")a=b;return a},U=function(b){var l=E(),f=E(),g=E(),h=E(),j=l<.5?"alternate":"alternate-reverse";if(f<.3)var c="left";else if(f<.6)c="center";else c="right";if(g<.45)var d="top";else if(g<.55)d="center";else d="bottom";if(h<.2)var i="linear";else i=h<.6?"cubic-bezier(.94,.04,.94,.49)":"cubic-bezier(.93,.2,.87,.52)";var k=c+" "+d;b[e].WebkitTransformOrigin=b[e].transformOrigin=k;if(a.c=="kb"){b[e].WebkitAnimationDirection=b[e].animationDirection=j;b[e].WebkitAnimationTimingFunction=b[e].animationTimingFunction=i}},ob=function(a){if(R){jb.innerHTML=R.innerHTML="<div>"+(a+1)+" &#8725; "+r+"</div>";if(v[d]){var b=v[d];while(b--)v[b][i]="";v[a][i]="active"}}},eb=function(d,j){d=gb(d);if(!j&&(w||d==c))return;clearTimeout(K);b[d][e].left="0px";for(var i=0,q=r;i<q;i++){b[i][e].zIndex=i===d?1:i===c?0:-1;if(i!=d)if(i==c&&(a.c=="zoom"||a.c=="kb")){var n=i;o(function(){A(b[n],"ns-show")},h)}else A(b[i],"ns-show");(a.c=="slide"||a.m)&&tb(b[i])}if(j!=9)if(a.c=="slide"||a.m&&j){!j&&s(b[d],"ns-show");var l=d>c||!d&&c==r-1;if(!c&&d!=1&&d==r-1)l=0;var k=a.c=="slide"&&f[p][p].offsetWidth==f[p].offsetWidth?"2":"";if(l){s(b[c],"sl-cl"+k);s(b[d],"sl-sl"+k)}else{s(b[c],"sl-cr"+k);s(b[d],"sl-sr"+k)}var n=c}else{s(b[d],"ns-show");(a.c=="zoom"||a.c=="kb")&&b[d].nI&&g.a.insertRule&&U(b[d].nI,d,b[d].dL)}ob(d);var m=c;c=d;X(4);V(b[d]);a.n&&a.n(m,d,j==9?false:j);if(a.d)K=o(function(){eb(d+1,0)},b[d].dL);b[d].player&&Y(b[d])};fb.prototype={b:function(){var g=f.children,e;r=g[d];for(var c=0,h=g[d];c<h;c++){b[c]=g[c];b[c].ix=c;e=b[c].getAttribute("data-delay");b[c].dL=e?parseInt(e):a.e}},c:function(){Bb(f);this.b();var e=0;if(a.j){for(var g=Hb(b),c=0,i=g[d];c<i;c++)f.appendChild(g[c]);e=1}else if(a.k){for(var h=a.k%b[d],c=0;c<h;c++)f.appendChild(b[c]);e=1}e&&this.b()},d:function(){if(a.g.indexOf(":")!=-1){var b=a.g.split(":"),c=b[1]/b[0];f[e][H]=c*100+"%"}else f[e][H]="50.1234%";f[e].height="0"},e:function(b,d){var c=n+b,a=k.getElementById(c);if(!a){a=k.createElement("div");a.id=c;a=f[p].appendChild(a)}if(b!="-pager"){a.onclick=d;O&&a[t]("touchstart",function(a){a.preventDefault();a.target.click();db(a)},false)}return a},addNavs:function(){if(r>1){var l=this.e("-pager",0);if(!ab(l)[d]){for(var o=[],a=0;a<r;a++)o.push('<a rel="'+a+'">'+(a+1)+"</a>");l.innerHTML=o.join("")}v=ab(l);for(var a=0;a<v[d];a++){if(a==c)v[a][i]="active";v[a].onclick=function(){var a=parseInt(this.getAttribute("rel"));a!=c&&j(a,1)}}jb=this.e("-prev",function(){j(c-1,1)});R=this.e("-next",function(){j(c+1,1)});ib=this.e("-pause-play",I)}var h=f[p][p].getElementsByTagName("*"),m=h[d];if(m)for(var a=0;a<m;a++)if(D(h[a],"fs-icon")){var g=h[a];break}if(g){z(g,"click",function(){var f=G(n),c=D(f,"fullscreen");if(c){A(f,"fullscreen");k.documentElement[e].overflow="auto"}else{s(f,"fullscreen");k.documentElement[e].overflow="hidden"}typeof fsIconClick=="function"&&fsIconClick(c);for(var a,g=0;g<b[d];g++){a=b[g];if(a.nIs)if(a.nI.tagName=="IMG")a.nI.src=a.nIs[c?1:0];else a.nI[e].backgroundImage="url('"+a.nIs[c?1:0]+"')"}});z(k,"keydown",function(a){a.keyCode==27&&D(G(n),"fullscreen")&&g.click()})}},sliderId:n,stop:S,getLis:function(){return b},getIndex:function(){return c},next:function(){a.d&&j(c+1,1)}};var M=function(){var a=G(n);if(a){var b=a.getElementsByTagName("ul");if(b[d])l=new fb(b[0])}},Cb=function(c){var a=0;function b(){if(a)return;a=1;o(c,4)}if(k[t])k[t]("DOMContentLoaded",b,false);else z(window,"load",b)};if(!a.initSliderByCallingInitFunc)if(G(n))M();else Cb(M);return{displaySlide:function(a){if(b[d]){if(typeof a=="number")var c=a;else c=a.ix;j(c,0)}},next:function(){j(c+1,1)},prev:function(){j(c-1,1)},toggle:I,getPos:function(){return c},getSlides:function(){return b},playVideo:function(a){if(typeof a=="number")a=b[a];if(a.player){j(a.ix,0);l.playVideo(a.player)}},init:function(a){!l&&M();typeof a!="undefined"&&this.displaySlide(a)}}}