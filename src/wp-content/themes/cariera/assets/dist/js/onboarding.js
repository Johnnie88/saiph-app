!function(){var e={589:function(){"use strict";var e=function(){function e(e,t){for(var i=0;i<t.length;i++){var a=t[i];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}return function(t,i,a){return i&&e(t.prototype,i),a&&e(t,a),t}}();function t(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(function(){var i,a,n,s,r=[].indexOf;jQuery.fn.extend({imagepicker:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};return this.each((function(){var t;if((t=jQuery(this)).data("picker")&&t.data("picker").destroy(),t.data("picker",new i(this,s(e))),null!=e.initialized)return e.initialized.call(t.data("picker"))}))}}),s=function(e){var t;return t={hide_select:!0,show_label:!1,initialized:void 0,changed:void 0,clicked:void 0,selected:void 0,limit:void 0,limit_reached:void 0,font_awesome:!1},jQuery.extend(t,e)},n=function(e,t){var i,a,n,s;if(!e||!t||e.length!==t.length)return!1;for(e=e.slice(0),t=t.slice(0),e.sort(),t.sort(),i=a=0,n=e.length;a<n;i=++a)if(s=e[i],t[i]!==s)return!1;return!0},i=function(){function i(e){var a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};t(this,i),this.sync_picker_with_select=this.sync_picker_with_select.bind(this),this.opts=a,this.select=jQuery(e),this.multiple="multiple"===this.select.attr("multiple"),null!=this.select.data("limit")&&(this.opts.limit=parseInt(this.select.data("limit"))),this.build_and_append_picker()}return e(i,[{key:"destroy",value:function(){var e,t,i;for(e=0,t=(i=this.picker_options).length;e<t;e++)i[e].destroy();return this.picker.remove(),this.select.off("change",this.sync_picker_with_select),this.select.removeData("picker"),this.select.show()}},{key:"build_and_append_picker",value:function(){return this.opts.hide_select&&this.select.hide(),this.select.on("change",this.sync_picker_with_select),null!=this.picker&&this.picker.remove(),this.create_picker(),this.select.after(this.picker),this.sync_picker_with_select()}},{key:"sync_picker_with_select",value:function(){var e,t,i,a,n;for(n=[],e=0,t=(a=this.picker_options).length;e<t;e++)(i=a[e]).is_selected()?n.push(i.mark_as_selected()):n.push(i.unmark_as_selected());return n}},{key:"create_picker",value:function(){return this.picker=jQuery("<ul class='thumbnails image_picker_selector'></ul>"),this.picker_options=[],this.recursively_parse_option_groups(this.select,this.picker),this.picker}},{key:"recursively_parse_option_groups",value:function(e,t){var i,n,s,r,o,l,c,d,u,h;for(n=0,r=(d=e.children("optgroup")).length;n<r;n++)c=d[n],c=jQuery(c),(i=jQuery("<ul></ul>")).append(jQuery("<li class='group_title'>"+c.attr("label")+"</li>")),t.append(jQuery("<li class='group'>").append(i)),this.recursively_parse_option_groups(c,i);for(u=function(){var t,i,n,s;for(s=[],t=0,i=(n=e.children("option")).length;t<i;t++)l=n[t],s.push(new a(l,this,this.opts));return s}.call(this),h=[],s=0,o=u.length;s<o;s++)l=u[s],this.picker_options.push(l),l.has_image()&&h.push(t.append(l.node));return h}},{key:"has_implicit_blanks",value:function(){var e;return function(){var t,i,a,n;for(n=[],t=0,i=(a=this.picker_options).length;t<i;t++)(e=a[t]).is_blank()&&!e.has_image()&&n.push(e);return n}.call(this).length>0}},{key:"selected_values",value:function(){return this.multiple?this.select.val()||[]:[this.select.val()]}},{key:"toggle",value:function(e,t){var i,a,s;if(a=this.selected_values(),s=e.value().toString(),this.multiple?r.call(this.selected_values(),s)>=0?((i=this.selected_values()).splice(jQuery.inArray(s,a),1),this.select.val([]),this.select.val(i)):null!=this.opts.limit&&this.selected_values().length>=this.opts.limit?null!=this.opts.limit_reached&&this.opts.limit_reached.call(this.select):this.select.val(this.selected_values().concat(s)):this.has_implicit_blanks()&&e.is_selected()?this.select.val(""):this.select.val(s),!n(a,this.selected_values())&&(this.select.change(),null!=this.opts.changed))return this.opts.changed.call(this.select,a,this.selected_values(),t)}}]),i}(),a=function(){function i(e,a){var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{};t(this,i),this.clicked=this.clicked.bind(this),this.picker=a,this.opts=n,this.option=jQuery(e),this.create_node()}return e(i,[{key:"destroy",value:function(){return this.node.find(".thumbnail").off("click",this.clicked)}},{key:"has_image",value:function(){return null!=this.option.data("img-src")}},{key:"is_blank",value:function(){return!(null!=this.value()&&""!==this.value())}},{key:"is_selected",value:function(){var e;return e=this.picker.select.val(),this.picker.multiple?jQuery.inArray(this.value(),e)>=0:this.value()===e}},{key:"mark_as_selected",value:function(){return this.node.find(".thumbnail").addClass("selected")}},{key:"unmark_as_selected",value:function(){return this.node.find(".thumbnail").removeClass("selected")}},{key:"value",value:function(){return this.option.val()}},{key:"label",value:function(){return this.option.data("img-label")?this.option.data("img-label"):this.option.text()}},{key:"clicked",value:function(e){if(this.picker.toggle(this,e),null!=this.opts.clicked&&this.opts.clicked.call(this.picker.select,this,e),null!=this.opts.selected&&this.is_selected())return this.opts.selected.call(this.picker.select,this,e)}},{key:"create_node",value:function(){var e,t,i,a;return this.node=jQuery("<li/>"),this.option.data("font_awesome")?(e=jQuery("<i>")).attr("class","fa-fw "+this.option.data("img-src")):(e=jQuery("<img class='image_picker_image'/>")).attr("src",this.option.data("img-src")),a=jQuery("<div class='thumbnail'>"),(i=this.option.data("img-class"))&&(this.node.addClass(i),e.addClass(i),a.addClass(i)),(t=this.option.data("img-alt"))&&e.attr("alt",t),a.on("click",this.clicked),a.append(e),this.opts.show_label&&a.append(jQuery("<p/>").html(this.label())),this.node.append(a),this.node}}]),i}()}).call(void 0)},845:function(){jQuery((function(e){function t(){var t=e('select[name="cariera_demo_file"]').val(),i=e('select[name="cariera_demo_file"]').find('option[value="'+t+'"]');if(e('select[name="cariera_demo_file"]').length){0===i[0].length?e("#cariera_import_form").addClass("disabled"):e("#cariera_import_form").removeClass("disabled");var a=i.data("settings");a.home_page?e(".after_import").removeClass("disable"):(e(".after_import").addClass("disable"),e(".after_import").removeClass("click")),a.slider_data?e(".slider").removeClass("disable"):(e(".slider").addClass("disable"),e(".slider").removeClass("click")),a.theme_option?e(".theme_options").removeClass("disable"):(e(".theme_options").addClass("disable"),e(".theme_options").removeClass("click")),a.widgets?e(".widgets").removeClass("disable"):(e(".widgets").addClass("disable"),e(".widgets").removeClass("click")),a.content?e(".demo_content").removeClass("disable"):(e(".demo_content").addClass("disable"),e(".demo_content").removeClass("click"))}else console.log("Core Plugin is not activated.")}function i(){0===e(".cariera_demo_content").find("li.click").length?e("#cariera_import_form").find('button[type="submit"]').prop("disabled",!0):e("#cariera_import_form").find('button[type="submit"]').prop("disabled",!1)}function a(t,i,a){return e.confirm({title:'<span style="color:ORANGE;font-size:34px;" class="dashicons dashicons-info"></span>',theme:"modern",content:t,buttons:{confirm:{text:i,btnClass:"btn-green",keys:["enter","space"],action:function(){a&&e("#cariera_import_form").submit()}},cancel:function(){}}})}function n(t,a,n,s){e.ajax({url:ajaxurl,type:"POST",data:{action:a,demo:t},beforeSend:function(){e(n).removeClass("click"),e(n).addClass("loading")},success:function(t){e(n).removeClass("loading"),e(n).addClass("done"),e(n).addClass("click"),"function"==typeof s(t)&&s(t)},error:function(){e(n).removeClass("loading"),e(n).removeClass("click"),e("#cariera_import_form").removeClass("disabled"),e.confirm({title:'<span class="error-icon"></span><div style="margin-top:30px;">Import Error!</div>',theme:"modern",content:'Something went wrong during the import and the importer stopped working.<br> Please make sure that: <ul class="import-error"><li>This is a clean & manual WordPress installation</li><li>No third party plugins are installed & activated</li><li>Server requirements are all on green</li></ul>',buttons:{cancel:{text:"Close",keys:["space"]}}})}}).done((function(){0===e(".cariera_demo_content").find("li.loading").length&&(e("#cariera_import_form").removeClass("disabled"),e("#cariera_import_form").find('button[type="submit"]').prop("disabled",!1),e(".cariera_demo_content li").removeClass("done"),e(".cariera_demo_content li").removeClass("click"),i(),e.confirm({title:'<span class="success-icon"></span><div style="margin-top:30px;">Import completed successfully</div>',theme:"modern",content:"",buttons:{view_site:{text:"View Site",btnClass:"btn-green",keys:["enter"],action:function(){return window.open(e("#cariera-home-url").html(),"_blank"),!1}},cancel:{text:"Close",keys:["space"]}}}))}))}e("select.image-picker").imagepicker({show_label:!0,selected:function(e,i,a){t()}}),t(),i(),e(".radio-list").find("li").each((function(){e(this).on("click",(function(){var t=e(this);e("#cariera_import_form").hasClass("disabled")||(t.hasClass("disable")?t.removeClass("click"):t.toggleClass("click")),i()}))})),e("#cariera_import_form").find(".panel-save").on("click",(function(e){e.preventDefault(),a("Make sure you are importing the demo content on a clean WordPress installation otherwise the content might not import right.","Yes",!0)})),e("#cariera_import_form").on("submit",(function(t){var i,s,r,o,l=[],c=e('select[name="cariera_demo_file"]').val(),d="cariera",u=0;void 0===(o=e(""))&&(o=e(".loader")),o.hasClass("loading")?o.removeClass("loading"):o.addClass("loading"),e(".cariera_demo_content").find("li").each((function(){e(this).hasClass("click")&&l.push(e(this).data("value"))})),e("#cariera_import_form").addClass("disabled"),e("#cariera_import_form").find('button[type="submit"]').prop("disabled",!0),(i=l.length)>0?(s=l[i-i],r=e(".cariera_demo_content").find('[data-value="'+s+'"]'),n(c,d+"_"+s,r,(function(t){u+=1,i>=2&&(s=l[u],r=e(".cariera_demo_content").find('[data-value="'+s+'"]'),n(c,d+"_"+s,r,(function(t){u+=1,i>=3&&(s=l[u],r=e(".cariera_demo_content").find('[data-value="'+s+'"]'),n(c,d+"_"+s,r,(function(t){u+=1,i>=4&&(s=l[u],r=e(".cariera_demo_content").find('[data-value="'+s+'"]'),n(c,d+"_"+s,r,(function(t){u+=1,i>=5&&(s=l[u],r=e(".cariera_demo_content").find('[data-value="'+s+'"]'),n(c,d+"_"+s,r,(function(e){})))})))})))})))}))):a("Please select at least one content !","Ok",!1),t.preventDefault()}))}))},198:function(){jQuery((function(e){var t,i=window.location.href;function a(t){if(void 0!==t)e(".cariera-onboarding .onboarding-header").find(".menu-item.active").removeClass("active"),e(".cariera-onboarding .onboarding-header").find('a[data-tab="'+t+'"]').addClass("active"),e(".cariera-onboarding .onboarding-content .content-page.active").removeClass("active"),e("#"+t).addClass("active"),n(t);else;e("a[data-tab]").each((function(){"undefined"!==e(this).data("tab")&&e(this).on("click",(function(i){i.preventDefault(),document.documentElement.scrollTop=0,t=e(this).data("tab"),e(".cariera-onboarding .onboarding-header").find(".menu-item.active").removeClass("active"),e(".cariera-onboarding .onboarding-header").find('a[data-tab="'+t+'"]').addClass("active"),e(".cariera-onboarding .onboarding-content .content-page.active").removeClass("active"),e("#"+t).addClass("active"),n(t)}))}))}function n(e){var t,a=i.match("#");null!==a&&""!==a[0]?(t=i.replace(/#(.*)/,"#"+e),window.location.href=t,document.documentElement.scrollTop=0):(window.location.href=i+"#"+e,document.documentElement.scrollTop=0)}e(".cariera-onboarding .onboarding-header").find(".menu-item").each((function(){e(this).on("click",(function(i){return i.preventDefault(),t=e(this).data("tab"),e(".cariera-onboarding .onboarding-header").find(".menu-item.active").removeClass("active"),e(this).addClass("active"),e(".cariera-onboarding .onboarding-content .content-page.active").removeClass("active"),e("#"+t).addClass("active"),!1}))})),window.location.hash&&a(window.location.hash.replace("#","")),a(),e(".license-container form").on("submit",(function(){e(this).find('.el-license-active-btn input[type="submit"]').val("Please wait...")}))}))},529:function(){(function(e){var t={install_plugins:function(e){(new a).init(e)}};function i(){e(".onboarding-btn").on("click",(function(i){if(i.preventDefault(),!function(e){var t=jQuery(e);if("yes"==t.data("done-loading"))return!1;t.is("input")||t.is("button");return t.data("done-loading","yes"),t.addClass("loading"),{done:function(){!0,t.attr("disabled",!1)}}}(this))return!1;var a=e(this).data("callback");return!a||void 0===t[a]||(t[a](this),!1)}))}function a(){var t,i,a=0,n="",s="";function r(e){"object"==typeof e&&void 0!==e.message?("Success"===e.message&&(i.find("span").first().addClass("green"),i.find(".checkmark").first().addClass("green")),i.find("span.message").first().text(e.message),i.find(".loader").addClass("loading"),void 0!==e.url?e.hash==s?(i.find("span.message").text("failed"),l()):(s=e.hash,jQuery.post(e.url,e,(function(t){o(),i.find("span.message").first().text(e.message+cariera_onboarding.verify_text)})).fail(r)):(e.done,l())):(i.find("span.message").text("ajax error"),l())}function o(){n&&(i.find("input:checkbox").is(":checked")?jQuery.post(cariera_onboarding.ajaxurl,{action:"cariera_plugins",wpnonce:cariera_onboarding.wpnonce,slug:n},(function(e){r(e)})).fail(r):(i.addClass("skipping"),setTimeout(l,300)))}function l(){var s=!1;i&&(i.data("done_item")||(a++,i.data("done_item",1)),i.find(".loader").removeClass("loading"));var r=e(".cariera-onboarding .onboarding-install-plugins li");r.each((function(){""==n||s?(n=e(this).data("slug"),i=e(this),o(),s=!1):e(this).data("slug")==n&&(s=!0)})),a>=r.length&&t()}return{init:function(i){e(".cariera-onboarding .onboarding-install-plugins").addClass("installing"),t=function(){var e=window.location.href;e=e.split("#")[0],window.location.href=e+"#plugins",location.reload()},l()}}}return{init:function(){this,e(i)}}})(jQuery).init()}},t={};function i(a){var n=t[a];if(void 0!==n)return n.exports;var s=t[a]={exports:{}};return e[a](s,s.exports,i),s.exports}i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,{a:t}),t},i.d=function(e,t){for(var a in t)i.o(t,a)&&!i.o(e,a)&&Object.defineProperty(e,a,{enumerable:!0,get:t[a]})},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},function(){"use strict";i(198),i(529),i(845),i(589)}()}();