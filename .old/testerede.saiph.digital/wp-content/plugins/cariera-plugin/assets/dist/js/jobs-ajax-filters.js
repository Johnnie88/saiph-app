jQuery(document).ready((function(e){var t="job_listing_";function i(t){return!(!window.sessionStorage||"function"!=typeof window.sessionStorage.setItem||e(document.body).hasClass("disable-job-manager-form-state-storage")||t.data("disable-form-state-storage"))}function n(i){var n=e("div.job_listings").index(i),o=i.data("post_id");return void 0!==o&&o||(o=window.location.href.replace(location.hash,"")),t+o+"_"+n}function o(e,t){if(!i(e))return!1;"object"!=typeof t&&(t={});var o=n(e);try{return window.sessionStorage.setItem(o,JSON.stringify(t))}catch(e){}return!1}function a(e){if(!i(e))return!1;var t=n(e);try{var o=window.sessionStorage.getItem(t);if(o)return JSON.parse(o)}catch(e){}return!1}function r(e,t){if(!i(e)||!e)return!1;var n=a(e);return!!n&&(n.persist_results=t,o(e,n))}function s(e){if(!i(e)||!e)return!1;var t=a(e);if(!t)return!1;var n=e.find(".job_filters");return t.form=n.serialize(),o(e,t)}function l(t,i,n){var o=t.find(".job_listings"),a=e(".showing_jobs");if("boolean"!=typeof n&&(n=!1),"string"==typeof i.showing&&i.showing){var r=jQuery("<span>").html(i.showing);a.show().html("").html(i.showing_links).prepend(r)}else a.hide();return i.showing_all?a.addClass("wp-job-manager-showing-all"):a.removeClass("wp-job-manager-showing-all"),i.html&&(n?o.append(i.html):o.html(i.html)),!0===t.data("show_pagination")?(t.find(".job-manager-pagination").remove(),i.pagination&&t.append(i.pagination)):(!i.found_jobs||i.max_num_pages<=i.data.page?e(".load_more_jobs:not(.load_previous)",t).hide():e(".load_more_jobs",t).show(),e(".load_more_jobs",t).removeClass("loading").data("page",i.data.page),e(".listing-loader").fadeOut(),e("li.job_listing",o).css("visibility","visible")),!0}e(document).on("click","a",(function(){e("div.job_listings").each((function(){s(e(this))}))})),e(document).on("submit","form",(function(){e("div.job_listings").each((function(){s(e(this))}))}));var d=[];e("div.job_listings").on("click","li.job_listing a",(function(){r(e(this).closest("div.job_listings"),!0)})).on("click",".job-manager-pagination a",(function(){var t=e(this).closest("div.job_listings"),i=e(this).data("page");return t.triggerHandler("update_results",[i,!1]),e("body, html").animate({scrollTop:t.offset().top},600),!1})).on("update_results",(function(t,r,s){var c,u,g,_,f=e(this),p=e(".job_filters"),h=f.find(".job_listings"),m=f.data("per_page"),b=f.data("orderby"),j=f.data("order"),v=f.data("featured"),w=f.data("filled"),y=f.data("remote_position"),k=(f.data("job_types"),f.data("post_status")),S=e("div.job_listings").index(this),x=e(".listing-loader"),C=f.data("job_layout"),H=f.data("job_version");if(!(S<0)){!function(e){if(!i(e))return!1;var t=n(e);try{window.sessionStorage.removeItem(t)}catch(e){return!1}}(f),d[S]&&d[S].abort(),s&&1!==r||(e("li.job_listing, li.no_job_listings_found",h).css("visibility","hidden"),h.addClass("loading"),e(x).fadeIn()),f.find(".load_more_jobs").data("page",r);var I=[];e(':input[name="filter_job_type[]"]:checked, :input[name="filter_job_type[]"][type="hidden"], :input[name="filter_job_type"]',p).each((function(){I.push(e(this).val())})),u=p.find(':input[name^="search_categories"]').map((function(){return e(this).val()})).get(),g="",_="";var O=p.find(':input[name="search_keywords"]'),z=p.find(':input[name="search_location"]'),E=p.find(':input[name="remote_position"]');O.val()!==O.attr("placeholder")&&(g=O.val()),z.val()!==z.attr("placeholder")&&(_=z.val()),E.length&&(y=E.is(":checked")?"true":null),c={lang:job_manager_ajax_filters.lang,search_keywords:g,search_location:_,search_categories:u,filter_job_type:I,filter_post_status:k,per_page:m,orderby:b,order:j,page:r,featured:v,filled:w,remote_position:y,show_pagination:f.data("show_pagination"),form_data:p.serialize(),job_layout:C,job_version:H},d[S]=e.ajax({type:"POST",url:job_manager_ajax_filters.ajax_url.toString().replace("%%endpoint%%","get_listings"),data:c,success:function(t){if(t)try{t.data=c,l(f,t,s),h.removeClass("loading"),e(x).fadeOut(),f.triggerHandler("updated_results",t),function(e,t){if(!i(e))return!1;var n=a(e);n||(n={persist_results:!1});var r=e.find(".job_listings");t.html=r.html(),n.results=t,o(e,n)}(f,t)}catch(e){window.console&&window.console.log(e)}},error:function(e,t,i){window.console&&"abort"!==t&&window.console.log(t+": "+i)},statusCode:{404:function(){window.console&&window.console.log("Error 404: Ajax Endpoint cannot be reached. Go to Settings > Permalinks and save to resolve.")}}})}})),e("#search_keywords, #search_location, #remote_position, #search_radius, .job_types :input, #search_categories, .job-manager-filter, .cariera-job-filters").change((function(){var t=e("div.job_listings");t.triggerHandler("update_results",[1,!1]),o(t)})).on("keyup",(function(t){13===t.which&&e(this).trigger("change")})),e(".job_filters").on("click",".reset",(function(){var t=e("div.job_listings"),i=e(this).closest("form");return i.find(':input[name="search_keywords"], :input[name="search_location"], .job-manager-filter, .cariera-job-filters').not(':input[type="hidden"]').val("").trigger("change.select2"),i.find(':input[name^="search_categories"]').not(':input[type="hidden"]').val("").trigger("change.select2"),i.find(':input[name="filter_job_type[]"]').not(':input[type="hidden"]').prop("checked",!0),i.find(':input[name="remote_position"]').not(':input[type="hidden"]').prop("checked",!1),t.triggerHandler("reset"),t.triggerHandler("update_results",[1,!1]),o(t),!1})).on("submit",(function(){return!1})),e(document.body).on("click",".load_more_jobs",(function(){var t=e(this).closest("div.job_listings"),i=parseInt(e(this).data("page")||1,10);return e(this).addClass("loading"),i+=1,e(this).data("page",i),t.triggerHandler("update_results",[i,!0]),!1})),e.isFunction(e.fn.select2)&&"undefined"!=typeof job_manager_select2_filters_args&&e('select[name^="search_categories"]:visible').select2(job_manager_select2_filters_args),e(window).on("unload",(function(){return e("div.job_listings").each((function(){var t=a(e(this));t&&!t.persist_results&&function(e){if(!i(e))return!1;var t=a(e);t||(t={}),t.results=null,o(e,t)}(e(this))})),!0})),e("div.job_listings").each((function(){var t=e(this),n=e(".job_filters"),s=!1,d=a(t);d&&(d.results&&(s=l(t,d.results),r(t,!1),function(e){if(!i(e))return!1;var t=a(e);t||(t={}),t.form=null,o(e,t)}(t)),"string"==typeof d.form&&""!==d.form&&(n.find("input[type=checkbox]").prop("checked",!1),n.deserialize(d.form),n.find(':input[name^="search_categories"]').not(':input[type="hidden"]').trigger("change.select2"))),!s&&n.length>0&&t.triggerHandler("update_results",[1,!1])}))}));