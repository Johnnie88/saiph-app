(self.webpackChunkcariera=self.webpackChunkcariera||[]).push([[279],{231:function(t,e,n){var i,s,a,r,o,l,d,c;s=[n(311)],i=function(t){return function(t){function e(){t[r].glbl||(a={$wndw:t(window),$docu:t(document),$html:t("html"),$body:t("body")},n={},i={},s={},t.each([n,i,s],(function(t,e){e.add=function(t){for(var n=0,i=(t=t.split(" ")).length;n<i;n++)e[t[n]]=e.mm(t[n])}})),n.mm=function(t){return"mm-"+t},n.add("wrapper menu panels panel nopanel navbar listview nolistview listitem btn hidden"),n.umm=function(t){return"mm-"==t.slice(0,3)&&(t=t.slice(3)),t},i.mm=function(t){return"mm-"+t},i.add("parent child title"),s.mm=function(t){return t+".mm"},s.add("transitionend webkitTransitionEnd click scroll resize keydown mousedown mouseup touchstart touchmove touchend orientationchange"),t[r]._c=n,t[r]._d=i,t[r]._e=s,t[r].glbl=a)}var n,i,s,a,r="mmenu",o="7.0.0";t[r]&&t[r].version>o||(t[r]=function(t,e,n){return this.$menu=t,this._api=["bind","getInstance","initPanels","openPanel","closePanel","closeAllPanels","setSelected"],this.opts=e,this.conf=n,this.vars={},this.cbck={},this.mtch={},"function"==typeof this.___deprecated&&this.___deprecated(),this._initHooks(),this._initWrappers(),this._initAddons(),this._initExtensions(),this._initMenu(),this._initPanels(),this._initOpened(),this._initAnchors(),this._initMatchMedia(),"function"==typeof this.___debug&&this.___debug(),this},t[r].version=o,t[r].uniqueId=0,t[r].wrappers={},t[r].addons={},t[r].defaults={hooks:{},extensions:[],wrappers:[],navbar:{add:!0,title:"Menu",titleLink:"parent"},onClick:{setSelected:!0},slidingSubmenus:!0},t[r].configuration={classNames:{divider:"Divider",inset:"Inset",nolistview:"NoListview",nopanel:"NoPanel",panel:"Panel",selected:"Selected",spacer:"Spacer",vertical:"Vertical"},clone:!1,openingInterval:25,panelNodetype:"ul, ol, div",transitionDuration:400},t[r].prototype={getInstance:function(){return this},initPanels:function(t){this._initPanels(t)},openPanel:function(e,s){if(this.trigger("openPanel:before",e),e&&e.length&&(e.is("."+n.panel)||(e=e.closest("."+n.panel)),e.is("."+n.panel))){var a=this;if("boolean"!=typeof s&&(s=!0),e.parent("."+n.listitem+"_vertical").length)e.parents("."+n.listitem+"_vertical").addClass(n.listitem+"_opened").children("."+n.panel).removeClass(n.hidden),this.openPanel(e.parents("."+n.panel).not((function(){return t(this).parent("."+n.listitem+"_vertical").length})).first()),this.trigger("openPanel:start",e),this.trigger("openPanel:finish",e);else{if(e.hasClass(n.panel+"_opened"))return;var o=this.$pnls.children("."+n.panel),l=this.$pnls.children("."+n.panel+"_opened");if(!t[r].support.csstransitions)return l.addClass(n.hidden).removeClass(n.panel+"_opened"),e.removeClass(n.hidden).addClass(n.panel+"_opened"),this.trigger("openPanel:start",e),void this.trigger("openPanel:finish",e);o.not(e).removeClass(n.panel+"_opened-parent");for(var d=e.data(i.parent);d;)(d=d.closest("."+n.panel)).parent("."+n.listitem+"_vertical").length||d.addClass(n.panel+"_opened-parent"),d=d.data(i.parent);o.removeClass(n.panel+"_highest").not(l).not(e).addClass(n.hidden),e.removeClass(n.hidden);var c=function(){l.removeClass(n.panel+"_opened"),e.addClass(n.panel+"_opened"),e.hasClass(n.panel+"_opened-parent")?(l.addClass(n.panel+"_highest"),e.removeClass(n.panel+"_opened-parent")):(l.addClass(n.panel+"_opened-parent"),e.addClass(n.panel+"_highest")),a.trigger("openPanel:start",e)},h=function(){l.removeClass(n.panel+"_highest").addClass(n.hidden),e.removeClass(n.panel+"_highest"),a.trigger("openPanel:finish",e)};s&&!e.hasClass(n.panel+"_noanimation")?setTimeout((function(){a.__transitionend(e,(function(){h()}),a.conf.transitionDuration),c()}),a.conf.openingInterval):(c(),h())}this.trigger("openPanel:after",e)}},closePanel:function(t){this.trigger("closePanel:before",t);var e=t.parent();e.hasClass(n.listitem+"_vertical")&&(e.removeClass(n.listitem+"_opened"),t.addClass(n.hidden),this.trigger("closePanel",t)),this.trigger("closePanel:after",t)},closeAllPanels:function(t){this.trigger("closeAllPanels:before"),this.$pnls.find("."+n.listview).children().removeClass(n.listitem+"_selected").filter("."+n.listitem+"_vertical").removeClass(n.listitem+"_opened");var e=this.$pnls.children("."+n.panel),i=t&&t.length?t:e.first();this.$pnls.children("."+n.panel).not(i).removeClass(n.panel+"_opened").removeClass(n.panel+"_opened-parent").removeClass(n.panel+"_highest").addClass(n.hidden),this.openPanel(i,!1),this.trigger("closeAllPanels:after")},togglePanel:function(t){var e=t.parent();e.hasClass(n.listitem+"_vertical")&&this[e.hasClass(n.listitem+"_opened")?"closePanel":"openPanel"](t)},setSelected:function(t){this.trigger("setSelected:before",t),this.$menu.find("."+n.listitem+"_selected").removeClass(n.listitem+"_selected"),t.addClass(n.listitem+"_selected"),this.trigger("setSelected:after",t)},bind:function(t,e){this.cbck[t]=this.cbck[t]||[],this.cbck[t].push(e)},trigger:function(){var t=this,e=Array.prototype.slice.call(arguments),n=e.shift();if(this.cbck[n])for(var i=0,s=this.cbck[n].length;i<s;i++)this.cbck[n][i].apply(t,e)},matchMedia:function(t,e,n){var i={yes:e,no:n};this.mtch[t]=this.mtch[t]||[],this.mtch[t].push(i)},_initHooks:function(){for(var t in this.opts.hooks)this.bind(t,this.opts.hooks[t])},_initWrappers:function(){this.trigger("initWrappers:before");for(var e=0;e<this.opts.wrappers.length;e++){var n=t[r].wrappers[this.opts.wrappers[e]];"function"==typeof n&&n.call(this)}this.trigger("initWrappers:after")},_initAddons:function(){var e;for(e in this.trigger("initAddons:before"),t[r].addons)t[r].addons[e].add.call(this),t[r].addons[e].add=function(){};for(e in t[r].addons)t[r].addons[e].setup.call(this);this.trigger("initAddons:after")},_initExtensions:function(){this.trigger("initExtensions:before");var t=this;for(var e in this.opts.extensions.constructor===Array&&(this.opts.extensions={all:this.opts.extensions}),this.opts.extensions)this.opts.extensions[e]=this.opts.extensions[e].length?n.menu+"_"+this.opts.extensions[e].join(" "+n.menu+"_"):"",this.opts.extensions[e]&&function(e){t.matchMedia(e,(function(){this.$menu.addClass(this.opts.extensions[e])}),(function(){this.$menu.removeClass(this.opts.extensions[e])}))}(e);this.trigger("initExtensions:after")},_initMenu:function(){this.trigger("initMenu:before"),this.conf.clone&&(this.$orig=this.$menu,this.$menu=this.$orig.clone(),this.$menu.add(this.$menu.find("[id]")).filter("[id]").each((function(){t(this).attr("id",n.mm(t(this).attr("id")))}))),this.$menu.attr("id",this.$menu.attr("id")||this.__getUniqueId()),this.$pnls=t('<div class="'+n.panels+'" />').append(this.$menu.children(this.conf.panelNodetype)).prependTo(this.$menu),this.$menu.addClass(n.menu).parent().addClass(n.wrapper),this.trigger("initMenu:after")},_initPanels:function(e){this.trigger("initPanels:before",e),e=e||this.$pnls.children(this.conf.panelNodetype);var i=t(),s=this,a=function(e){e.filter(s.conf.panelNodetype).each((function(e){var r=s._initPanel(t(this));if(r){s._initNavbar(r),s._initListview(r),i=i.add(r);var o=r.children("."+n.listview).children("li").children(s.conf.panelNodeType).add(r.children("."+s.conf.classNames.panel));o.length&&a(o)}}))};a(e),this.trigger("initPanels:after",i)},_initPanel:function(t){if(this.trigger("initPanel:before",t),t.hasClass(n.panel))return t;if(this.__refactorClass(t,this.conf.classNames.panel,n.panel),this.__refactorClass(t,this.conf.classNames.nopanel,n.nopanel),this.__refactorClass(t,this.conf.classNames.inset,n.listview+"_inset"),t.filter("."+n.listview+"_inset").addClass(n.nopanel),t.hasClass(n.nopanel))return!1;var e=t.hasClass(this.conf.classNames.vertical)||!this.opts.slidingSubmenus;t.removeClass(this.conf.classNames.vertical);var s=t.attr("id")||this.__getUniqueId();t.is("ul, ol")&&(t.removeAttr("id"),t.wrap("<div />"),t=t.parent()),t.attr("id",s),t.addClass(n.panel+" "+n.hidden);var a=t.parent("li");return e?a.addClass(n.listitem+"_vertical"):t.appendTo(this.$pnls),a.length&&(a.data(i.child,t),t.data(i.parent,a)),this.trigger("initPanel:after",t),t},_initNavbar:function(e){if(this.trigger("initNavbar:before",e),!e.children("."+n.navbar).length){var s=e.data(i.parent),a=t('<div class="'+n.navbar+'" />'),r=this.__getPanelTitle(e,this.opts.navbar.title),o="";if(s&&s.length){if(s.hasClass(n.listitem+"_vertical"))return;if(s.parent().is("."+n.listview))var l=s.children("a, span").not("."+n.btn+"_next");else l=s.closest("."+n.panel).find('a[href="#'+e.attr("id")+'"]');var d=(s=(l=l.first()).closest("."+n.panel)).attr("id");switch(r=this.__getPanelTitle(e,t("<span>"+l.text()+"</span>").text()),this.opts.navbar.titleLink){case"anchor":o=l.attr("href");break;case"parent":o="#"+d}a.append('<a class="'+n.btn+" "+n.btn+"_prev "+n.navbar+'__btn" href="#'+d+'" />')}else if(!this.opts.navbar.title)return;this.opts.navbar.add&&e.addClass(n.panel+"_has-navbar"),a.append('<a class="'+n.navbar+'__title"'+(o.length?' href="'+o+'"':"")+">"+r+"</a>").prependTo(e),this.trigger("initNavbar:after",e)}},_initListview:function(e){this.trigger("initListview:before",e);var s=this.__childAddBack(e,"ul, ol");this.__refactorClass(s,this.conf.classNames.nolistview,n.nolistview);var a=s.not("."+n.nolistview).addClass(n.listview).children().addClass(n.listitem);this.__refactorClass(a,this.conf.classNames.selected,n.listitem+"_selected"),this.__refactorClass(a,this.conf.classNames.divider,n.listitem+"_divider"),this.__refactorClass(a,this.conf.classNames.spacer,n.listitem+"_spacer");var r=e.data(i.parent);if(r&&r.is("."+n.listitem)&&!r.children("."+n.btn+"_next").length){var o=r.children("a, span").first(),l=t('<a class="'+n.btn+'_next" href="#'+e.attr("id")+'" />').insertBefore(o);o.is("span")&&l.addClass(n.btn+"_fullwidth")}this.trigger("initListview:after",e)},_initOpened:function(){this.trigger("initOpened:before");var t=this.$pnls.find("."+n.listitem+"_selected").removeClass(n.listitem+"_selected").last().addClass(n.listitem+"_selected"),e=t.length?t.closest("."+n.panel):this.$pnls.children("."+n.panel).first();this.openPanel(e,!1),this.trigger("initOpened:after")},_initAnchors:function(){this.trigger("initAnchors:before");var e=this;a.$body.on(s.click+"-oncanvas","a[href]",(function(i){var s=t(this),a=s.attr("href"),o=e.$menu.find(s).length,l=s.is("."+n.listitem+" > a"),d=s.is('[rel="external"]')||s.is('[target="_blank"]');if(o&&a.length>1&&"#"==a.slice(0,1))try{var c=e.$menu.find(a);if(c.is("."+n.panel))return e[s.parent().hasClass(n.listitem+"_vertical")?"togglePanel":"openPanel"](c),void i.preventDefault()}catch(t){}var h={close:null,setSelected:null,preventDefault:"#"==a.slice(0,1)};for(var p in t[r].addons){var f=t[r].addons[p].clickAnchor.call(e,s,o,l,d);if(f){if("boolean"==typeof f)return void i.preventDefault();"object"==typeof f&&(h=t.extend({},h,f))}}o&&l&&!d&&(e.__valueOrFn(s,e.opts.onClick.setSelected,h.setSelected)&&e.setSelected(t(i.target).parent()),e.__valueOrFn(s,e.opts.onClick.preventDefault,h.preventDefault)&&i.preventDefault(),e.__valueOrFn(s,e.opts.onClick.close,h.close)&&e.opts.offCanvas&&"function"==typeof e.close&&e.close())})),this.trigger("initAnchors:after")},_initMatchMedia:function(){var t=this;for(var e in this.mtch)!function(){var n=e,i=window.matchMedia(n);t._fireMatchMedia(n,i),i.addListener((function(e){t._fireMatchMedia(n,e)}))}()},_fireMatchMedia:function(t,e){for(var n=e.matches?"yes":"no",i=0;i<this.mtch[t].length;i++)this.mtch[t][i][n].call(this)},_getOriginalMenuId:function(){var t=this.$menu.attr("id");return this.conf.clone&&t&&t.length&&(t=n.umm(t)),t},__api:function(){var e=this,n={};return t.each(this._api,(function(t){var i=this;n[i]=function(){var t=e[i].apply(e,arguments);return void 0===t?n:t}})),n},__valueOrFn:function(t,e,n){if("function"==typeof e){var i=e.call(t[0]);if(void 0!==i)return i}return"function"!=typeof e&&void 0!==e||void 0===n?e:n},__getPanelTitle:function(e,n){var s;return"function"==typeof this.opts.navbar.title&&(s=this.opts.navbar.title.call(e[0])),void 0===s&&(s=e.data(i.title)),void 0!==s?s:"string"==typeof n?t[r].i18n(n):t[r].i18n(t[r].defaults.navbar.title)},__refactorClass:function(t,e,n){return t.filter("."+e).removeClass(e).addClass(n)},__findAddBack:function(t,e){return t.find(e).add(t.filter(e))},__childAddBack:function(t,e){return t.children(e).add(t.filter(e))},__filterListItems:function(t){return t.not("."+n.listitem+"_divider").not("."+n.hidden)},__filterListItemAnchors:function(t){return this.__filterListItems(t).children("a").not("."+n.btn+"_next")},__openPanelWoAnimation:function(t){t.hasClass(n.panel+"_noanimation")||(t.addClass(n.panel+"_noanimation"),this.__transitionend(t,(function(){t.removeClass(n.panel+"_noanimation")}),this.conf.openingInterval),this.openPanel(t))},__transitionend:function(t,e,n){var i=!1,a=function(n){void 0!==n&&n.target!=t[0]||(i||(t.off(s.transitionend),t.off(s.webkitTransitionEnd),e.call(t[0])),i=!0)};t.on(s.transitionend,a),t.on(s.webkitTransitionEnd,a),setTimeout(a,1.1*n)},__getUniqueId:function(){return n.mm(t[r].uniqueId++)}},t.fn[r]=function(n,i){e(),n=t.extend(!0,{},t[r].defaults,n),i=t.extend(!0,{},t[r].configuration,i);var s=t();return this.each((function(){var e=t(this);if(!e.data(r)){var a=new t[r](e,n,i);a.$menu.data(r,a.__api()),s=s.add(a.$menu)}})),s},t[r].i18n=function(){var e={};return function(n){switch(typeof n){case"object":return t.extend(e,n),e;case"string":return e[n]||n;default:return e}}}(),t[r].support={touch:"ontouchstart"in window||navigator.msMaxTouchPoints||!1,csstransitions:"undefined"==typeof Modernizr||void 0===Modernizr.csstransitions||Modernizr.csstransitions,csstransforms:"undefined"==typeof Modernizr||void 0===Modernizr.csstransforms||Modernizr.csstransforms,csstransforms3d:"undefined"==typeof Modernizr||void 0===Modernizr.csstransforms3d||Modernizr.csstransforms3d})}(t),o="offCanvas",(e=t)[r="mmenu"].addons[o]={setup:function(){if(this.opts[o]){var t=this.opts[o],i=this.conf[o];a=e[r].glbl,this._api=e.merge(this._api,["open","close","setPage"]),"object"!=typeof t&&(t={}),t=this.opts[o]=e.extend(!0,{},e[r].defaults[o],t),"string"!=typeof i.pageSelector&&(i.pageSelector="> "+i.pageNodetype),this.vars.opened=!1;var s=[n.menu+"_offcanvas"];e[r].support.csstransforms||s.push(n["no-csstransforms"]),e[r].support.csstransforms3d||s.push(n["no-csstransforms3d"]),this.bind("initMenu:after",(function(){var t=this;this.setPage(a.$page),this._initBlocker(),this["_initWindow_"+o](),this.$menu.addClass(s.join(" ")).parent("."+n.wrapper).removeClass(n.wrapper),this.$menu[i.menuInsertMethod](i.menuInsertSelector);var e=window.location.hash;if(e){var r=this._getOriginalMenuId();r&&r==e.slice(1)&&setTimeout((function(){t.open()}),1e3)}})),this.bind("open:start:sr-aria",(function(){this.__sr_aria(this.$menu,"hidden",!1)})),this.bind("close:finish:sr-aria",(function(){this.__sr_aria(this.$menu,"hidden",!0)})),this.bind("initMenu:after:sr-aria",(function(){this.__sr_aria(this.$menu,"hidden",!0)}))}},add:function(){n=e[r]._c,i=e[r]._d,s=e[r]._e,n.add("slideout page no-csstransforms3d"),i.add("style")},clickAnchor:function(t,e){var i=this;if(this.opts[o]){var s=this._getOriginalMenuId();if(s&&t.is('[href="#'+s+'"]')){if(e)return this.open(),!0;var r=t.closest("."+n.menu);if(r.length){var l=r.data("mmenu");if(l&&l.close)return l.close(),i.__transitionend(r,(function(){i.open()}),i.conf.transitionDuration),!0}return this.open(),!0}if(a.$page)return(s=a.$page.first().attr("id"))&&t.is('[href="#'+s+'"]')?(this.close(),!0):void 0}}},e[r].defaults[o]={blockUI:!0,moveBackground:!0},e[r].configuration[o]={pageNodetype:"div",pageSelector:null,noPageSelector:[],wrapPageIfNeeded:!0,menuInsertMethod:"prependTo",menuInsertSelector:"body"},e[r].prototype.open=function(){if(this.trigger("open:before"),!this.vars.opened){var t=this;this._openSetup(),setTimeout((function(){t._openFinish()}),this.conf.openingInterval),this.trigger("open:after")}},e[r].prototype._openSetup=function(){var t=this,r=this.opts[o];this.closeAllOthers(),a.$page.each((function(){e(this).data(i.style,e(this).attr("style")||"")})),a.$wndw.trigger(s.resize+"-"+o,[!0]);var l=[n.wrapper+"_opened"];r.blockUI&&l.push(n.wrapper+"_blocking"),"modal"==r.blockUI&&l.push(n.wrapper+"_modal"),r.moveBackground&&l.push(n.wrapper+"_background"),a.$html.addClass(l.join(" ")),setTimeout((function(){t.vars.opened=!0}),this.conf.openingInterval),this.$menu.addClass(n.menu+"_opened")},e[r].prototype._openFinish=function(){var t=this;this.__transitionend(a.$page.first(),(function(){t.trigger("open:finish")}),this.conf.transitionDuration),this.trigger("open:start"),a.$html.addClass(n.wrapper+"_opening")},e[r].prototype.close=function(){if(this.trigger("close:before"),this.vars.opened){var t=this;this.__transitionend(a.$page.first(),(function(){t.$menu.removeClass(n.menu+"_opened");var s=[n.wrapper+"_opened",n.wrapper+"_blocking",n.wrapper+"_modal",n.wrapper+"_background"];a.$html.removeClass(s.join(" ")),a.$page.each((function(){e(this).attr("style",e(this).data(i.style))})),t.vars.opened=!1,t.trigger("close:finish")}),this.conf.transitionDuration),this.trigger("close:start"),a.$html.removeClass(n.wrapper+"_opening"),this.trigger("close:after")}},e[r].prototype.closeAllOthers=function(){a.$body.find("."+n.menu+"_offcanvas").not(this.$menu).each((function(){var t=e(this).data(r);t&&t.close&&t.close()}))},e[r].prototype.setPage=function(t){this.trigger("setPage:before",t);var i=this,s=this.conf[o];t&&t.length||(t=a.$body.find(s.pageSelector),s.noPageSelector.length&&(t=t.not(s.noPageSelector.join(", "))),t.length>1&&s.wrapPageIfNeeded&&(t=t.wrapAll("<"+this.conf[o].pageNodetype+" />").parent())),t.each((function(){e(this).attr("id",e(this).attr("id")||i.__getUniqueId())})),t.addClass(n.page+" "+n.slideout),a.$page=t,this.trigger("setPage:after",t)},e[r].prototype["_initWindow_"+o]=function(){a.$wndw.off(s.keydown+"-"+o).on(s.keydown+"-"+o,(function(t){if(a.$html.hasClass(n.wrapper+"_opened")&&9==t.keyCode)return t.preventDefault(),!1}));var t=0;a.$wndw.off(s.resize+"-"+o).on(s.resize+"-"+o,(function(e,i){if(1==a.$page.length&&(i||a.$html.hasClass(n.wrapper+"_opened"))){var s=a.$wndw.height();(i||s!=t)&&(t=s,a.$page.css("minHeight",s))}}))},e[r].prototype._initBlocker=function(){var t=this;this.opts[o].blockUI&&(a.$blck||(a.$blck=e('<div class="'+n.page+"__blocker "+n.slideout+'" />')),a.$blck.appendTo(a.$body).off(s.touchstart+"-"+o+" "+s.touchmove+"-"+o).on(s.touchstart+"-"+o+" "+s.touchmove+"-"+o,(function(t){t.preventDefault(),t.stopPropagation(),a.$blck.trigger(s.mousedown+"-"+o)})).off(s.mousedown+"-"+o).on(s.mousedown+"-"+o,(function(e){e.preventDefault(),a.$html.hasClass(n.wrapper+"_modal")||(t.closeAllOthers(),t.close())})))},function(t){var e,n,i="mmenu",s="screenReader";t[i].addons[s]={setup:function(){var a=this,r=this.opts[s],o=this.conf[s];t[i].glbl,"boolean"==typeof r&&(r={aria:r,text:r}),"object"!=typeof r&&(r={}),(r=this.opts[s]=t.extend(!0,{},t[i].defaults[s],r)).aria&&(this.bind("initAddons:after",(function(){this.bind("initMenu:after",(function(){this.trigger("initMenu:after:sr-aria")})),this.bind("initNavbar:after",(function(){this.trigger("initNavbar:after:sr-aria",arguments[0])})),this.bind("openPanel:start",(function(){this.trigger("openPanel:start:sr-aria",arguments[0])})),this.bind("close:start",(function(){this.trigger("close:start:sr-aria")})),this.bind("close:finish",(function(){this.trigger("close:finish:sr-aria")})),this.bind("open:start",(function(){this.trigger("open:start:sr-aria")})),this.bind("initOpened:after",(function(){this.trigger("initOpened:after:sr-aria")}))})),this.bind("updateListview",(function(){this.$pnls.find("."+e.listview).children().each((function(){a.__sr_aria(t(this),"hidden",t(this).is("."+e.hidden))}))})),this.bind("openPanel:start",(function(t){var n=this.$menu.find("."+e.panel).not(t).not(t.parents("."+e.panel)),i=t.add(t.find("."+e.listitem+"_vertical ."+e.listitem+"_opened").children("."+e.panel));this.__sr_aria(n,"hidden",!0),this.__sr_aria(i,"hidden",!1)})),this.bind("closePanel",(function(t){this.__sr_aria(t,"hidden",!0)})),this.bind("initPanels:after",(function(n){var i=n.find("."+e.btn).each((function(){a.__sr_aria(t(this),"owns",t(this).attr("href").replace("#",""))}));this.__sr_aria(i,"haspopup",!0)})),this.bind("initNavbar:after",(function(t){var n=t.children("."+e.navbar);this.__sr_aria(n,"hidden",!t.hasClass(e.panel+"_has-navbar"))})),r.text&&(this.bind("initlistview:after",(function(t){var n=t.find("."+e.listview).find("."+e.btn+"_fullwidth").parent().children("span");this.__sr_aria(n,"hidden",!0)})),"parent"==this.opts.navbar.titleLink&&this.bind("initNavbar:after",(function(t){var n=t.children("."+e.navbar),i=!!n.children("."+e.btn+"_prev").length;this.__sr_aria(n.children("."+e.title),"hidden",i)})))),r.text&&(this.bind("initAddons:after",(function(){this.bind("setPage:after",(function(){this.trigger("setPage:after:sr-text",arguments[0])}))})),this.bind("initNavbar:after",(function(n){var s=n.children("."+e.navbar),a=s.children("."+e.title).text(),r=t[i].i18n(o.text.closeSubmenu);a&&(r+=" ("+a+")"),s.children("."+e.btn+"_prev").html(this.__sr_text(r))})),this.bind("initListview:after",(function(s){var r=s.data(n.parent);if(r&&r.length){var l=r.children("."+e.btn+"_next"),d=l.nextAll("span, a").first().text(),c=t[i].i18n(o.text[l.parent().is("."+e.listitem+"_vertical")?"toggleSubmenu":"openSubmenu"]);d&&(c+=" ("+d+")"),l.html(a.__sr_text(c))}})))},add:function(){e=t[i]._c,n=t[i]._d,t[i]._e,e.add("sronly")},clickAnchor:function(t,e){}},t[i].defaults[s]={aria:!0,text:!0},t[i].configuration[s]={text:{closeMenu:"Close menu",closeSubmenu:"Close submenu",openSubmenu:"Open submenu",toggleSubmenu:"Toggle submenu"}},t[i].prototype.__sr_aria=function(t,e,n){t.prop("aria-"+e,n)[n?"attr":"removeAttr"]("aria-"+e,n)},t[i].prototype.__sr_role=function(t,e){t.prop("role",e)[e?"attr":"removeAttr"]("role",e)},t[i].prototype.__sr_text=function(t){return'<span class="'+e.sronly+'">'+t+"</span>"}}(t),function(t){var e,n,i,s="mmenu",a="scrollBugFix";t[s].addons[a]={setup:function(){var n=this.opts[a];this.conf[a],i=t[s].glbl,t[s].support.touch&&this.opts.offCanvas&&this.opts.offCanvas.blockUI&&("boolean"==typeof n&&(n={fix:n}),"object"!=typeof n&&(n={}),(n=this.opts[a]=t.extend(!0,{},t[s].defaults[a],n)).fix&&(this.bind("open:start",(function(){this.$pnls.children("."+e.panel+"_opened").scrollTop(0)})),this.bind("initMenu:after",(function(){this["_initWindow_"+a]()}))))},add:function(){e=t[s]._c,t[s]._d,n=t[s]._e},clickAnchor:function(t,e){}},t[s].defaults[a]={fix:!0},t[s].prototype["_initWindow_"+a]=function(){var s=this;i.$docu.off(n.touchmove+"-"+a).on(n.touchmove+"-"+a,(function(t){i.$html.hasClass(e.wrapper+"_opened")&&t.preventDefault()}));var r=!1;i.$body.off(n.touchstart+"-"+a).on(n.touchstart+"-"+a,"."+e.panels+"> ."+e.panel,(function(t){i.$html.hasClass(e.wrapper+"_opened")&&(r||(r=!0,0===t.currentTarget.scrollTop?t.currentTarget.scrollTop=1:t.currentTarget.scrollHeight===t.currentTarget.scrollTop+t.currentTarget.offsetHeight&&(t.currentTarget.scrollTop-=1),r=!1))})).off(n.touchmove+"-"+a).on(n.touchmove+"-"+a,"."+e.panels+"> ."+e.panel,(function(n){i.$html.hasClass(e.wrapper+"_opened")&&t(this)[0].scrollHeight>t(this).innerHeight()&&n.stopPropagation()})),i.$wndw.off(n.orientationchange+"-"+a).on(n.orientationchange+"-"+a,(function(){s.$pnls.children("."+e.panel+"_opened").scrollTop(0).css({"-webkit-overflow-scrolling":"auto"}).css({"-webkit-overflow-scrolling":"touch"})}))}}(t),!0;var e,n,i,s,a,r,o},void 0===(a="function"==typeof i?i.apply(e,s):i)||(t.exports=a),r=jQuery,c="counters",r[d="mmenu"].addons[c]={setup:function(){var t=this,e=this.opts[c];if(this.conf[c],r[d].glbl,"boolean"==typeof e&&(e={add:e,update:e}),"object"!=typeof e&&(e={}),e=this.opts[c]=r.extend(!0,{},r[d].defaults[c],e),this.bind("initListview:after",(function(t){var e=this.conf.classNames[c].counter;this.__refactorClass(t.find("."+e),e,o.counter)})),e.add&&this.bind("initListview:after",(function(t){("panels"===e.addTo?t:t.filter(e.addTo)).each((function(){var t=r(this).data(l.parent);t&&(t.children("."+o.counter).length||t.prepend(r('<em class="'+o.counter+'" />')))}))})),e.update){var n=function(e){(e=e||this.$pnls.children("."+o.panel)).each((function(){var e=r(this),n=e.data(l.parent);if(n){var i=n.children("em."+o.counter);i.length&&(e=e.children("."+o.listview)).length&&i.html(t.__filterListItems(e.children()).length)}}))};this.bind("initListview:after",n),this.bind("updateListview",n)}},add:function(){o=r[d]._c,l=r[d]._d,r[d]._e,o.add("counter")},clickAnchor:function(t,e){}},r[d].defaults[c]={add:!1,addTo:"panels",count:!1},r[d].configuration.classNames[c]={counter:"Counter"}},101:function(t,e,n){"use strict";n.r(e)}}]);