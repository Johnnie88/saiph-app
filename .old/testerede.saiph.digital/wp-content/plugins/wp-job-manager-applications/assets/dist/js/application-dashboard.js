jQuery(document).ready((function(i){i("section.job-application-content, section.job-application-notes, section.job-application-edit").hide().prepend('<a href="#" class="hide_section">'+job_manager_application.i18n_hide+"</a>"),i("form.filter-job-applications").on("change","select",(function(){i("form.filter-job-applications").submit()})),i("#job-manager-job-applications").on("click",".job-application-toggle-content",(function(){return i(this).closest("li.job-application").find("section:not(.job-application-content)").slideUp(),i(this).closest("li.job-application").find("section.job-application-content").slideToggle(),!1})).on("click",".job-application-toggle-edit",(function(){return i(this).closest("li.job-application").find("section:not(.job-application-edit)").slideUp(),i(this).closest("li.job-application").find("section.job-application-edit").slideToggle(),!1})).on("click",".job-application-toggle-notes",(function(){return i(this).closest("li.job-application").find("section:not(.job-application-notes)").slideUp(),i(this).closest("li.job-application").find("section.job-application-notes").slideToggle(),!1})).on("click","a.hide_section",(function(){return i(this).closest("section").slideUp(),!1})).on("click",".job-application-note-add input.button",(function(){const o=i(this),t=o.data("application_id"),n=i(this).closest(".job-application"),a=n.find("textarea"),e=i(this).attr("disabled"),c=n.find("ul.job-application-notes-list");if(void 0!==e&&!1!==e)return!1;if(!a.val())return!1;o.attr("disabled","disabled");const l={action:"add_job_application_note",note:a.val(),application_id:t,security:job_manager_application.job_application_notes_nonce};return i.post(job_manager_application.ajax_url,l,(function(i){c.append(i),o.removeAttr("disabled"),a.val("")})),!1})).on("click","a.delete_note",(function(){if(confirm(job_manager_application.i18n_confirm_delete)){const o=i(this).closest("li"),t={action:"delete_job_application_note",note_id:o.attr("rel"),security:job_manager_application.job_application_notes_nonce};i.post(job_manager_application.ajax_url,t,(function(){o.fadeOut(500,(function(){o.remove()}))}))}return!1})).on("click","a.delete_job_application",(function(){return!!confirm(job_manager_application.i18n_confirm_delete)}))}));