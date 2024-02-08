(function($) {
	'use strict';
  
	$( function() {
  
		/**
		 * Install LoginPress Add-Ons on one click.
		 * @since 1.2.2
		 */
		$(document).on( 'change', '.loginpress-install-pro-addon', function (e) {
  
			e.preventDefault();
			e.stopPropagation();
			var addonBtn     = $(this);
			var addonWrapper = $(this).closest('.loginpress-extension');
			var nonce        = addonWrapper.find('input[name="loginpress_pro_addon_nonce"]').val();
			var pluginSlug   = addonWrapper.find('input[name="loginpress_pro_addon_slug"]').val();
			var pluginID     = addonWrapper.find('input[name="loginpress_pro_addon_id"]').val();

			$.ajax({
				type: 'GET',
				url : 'update.php',
				data: {
					action  : 'install-plugin',
					plugin  : pluginSlug,
					lgp     : 1,
					id      : pluginID,
					_wpnonce: nonce
				},
				beforeSend: function(){
					addonWrapper.find('.loginpress-addon-enable').show();
				},
				success: function (res) {
					activateAddon( pluginSlug, nonce, addonWrapper, addonBtn );
				},
				error  : function (res) {
					// console.log(res);
					addonWrapper.find('.loginpress-uninstalling').hide();
					addonWrapper.find('.loginpress-uninstall').hide();
					addonWrapper.find('.loginpress-addon-enable').hide();
					addonWrapper.find('.loginpress-wrong').show();
					setTimeout( function() {
						addonWrapper.find('.loginpress-wrong').hide();
					}, 2000);
				}
			});

    	});

		/**
		 * Deactivate LoginPress Add-Ons on one click.
		 * @since 1.2.2
		 */
		$(document).on( 'change', '.loginpress-uninstall-pro-addon', function (e) {

			e.preventDefault();
			e.stopPropagation();
			var addonBtn     = $(this);
			var addonWrapper = $(this).closest('.loginpress-extension');
			var nonce        = addonWrapper.find('input[name="loginpress_pro_addon_nonce"]').val();
			var pluginSlug   = addonWrapper.find('input[name="loginpress_pro_addon_slug"]').val();

			$.ajax({
				type: 'POST',
				url : ajaxurl,
				data: {
					action  : 'loginpress_deactivate_addon',
					slug    : pluginSlug,
					_wpnonce: nonce
				},
				beforeSend: function(){
					addonWrapper.find('.loginpress-uninstalling').show();
				},
				success: function (res) {
					var newNonce = res;

					addonWrapper.find('input[name="loginpress_pro_addon_nonce"]').val(newNonce);
					addonWrapper.find('.loginpress-uninstalling').hide();
					addonWrapper.find('.loginpress-uninstall').show();
					addonBtn.addClass('loginpress-active-pro-addon').removeClass('loginpress-install-pro-addon loginpress-uninstall-pro-addon').html('Activate Plugin');
					setTimeout( function() {
						addonWrapper.find('.loginpress-uninstall').hide();
					}, 3000);
				},
				error: function (res) {
					// console.log(res);
					addonWrapper.find('.loginpress-uninstalling').hide();
					addonWrapper.find('.loginpress-uninstall').hide();
					addonWrapper.find('.loginpress-wrong').show();
					setTimeout( function() {
						addonWrapper.find('.loginpress-wrong').hide();
					}, 2000);
				}
			});

		});

		/**
		 * Activate LoginPress Add-Ons on one click.
		 * @since 1.2.2
		 */
		$(document).on( 'change', '.loginpress-active-pro-addon', function (e) {

			e.preventDefault();
			e.stopPropagation();
			var addonBtn     = $(this);
			var addonWrapper = $(this).closest('.loginpress-extension');
			var nonce        = addonWrapper.find('input[name="loginpress_pro_addon_nonce"]').val();
			var pluginSlug   = addonWrapper.find('input[name="loginpress_pro_addon_slug"]').val();

			activateAddon( pluginSlug, nonce, addonWrapper, addonBtn );

		});

    	/**
		 * Activate LoginPress Add-Ons.
		 * @param  string pluginSlug
		 * @param  string nonce
		 * @param  string addonWrapper
		 * @param  string addonBtn
		 * @since 1.2.2
		 */
		function activateAddon( pluginSlug, nonce, addonWrapper, addonBtn ) {

			$.ajax({
				url : ajaxurl,
				type: 'POST',
				data: {
					slug  : pluginSlug,
          			action: 'loginpress_activate_addon',
          			_wpnonce: nonce
				},
				beforeSend: function(){
					addonWrapper.find('.loginpress-addon-enable').show();
				},
        		success: function (res) {
         			var newNonce = res;

					addonWrapper.find('.loginpress-addon-enable').hide();
					addonWrapper.find('.loginpress-install').show();
					addonBtn.addClass('loginpress-uninstall-pro-addon').removeClass('loginpress-install-pro-addon loginpress-active-pro-addon').html('Uninstall');
          			addonWrapper.find('input[name="loginpress_pro_addon_nonce"]').val(newNonce);

					setTimeout( function() {
						addonWrapper.find('.loginpress-install').hide();
					}, 3000);
				},
				error  : function ( xhr, textStatus, errorThrown ) {
					// console.log('Ajax Not Working');
					addonWrapper.find('.loginpress-uninstalling').hide();
					addonWrapper.find('.loginpress-uninstall').hide();
					addonWrapper.find('.loginpress-wrong').show();
					setTimeout( function() {
						addonWrapper.find('.loginpress-wrong').hide();
					}, 2000);
				}
			});

		}

		// Code to fire when the DOM is ready. 3.0.0
		$('.wpbrigade-video-link').on( 'click', function(e) {
			e.preventDefault();
			var target = $(this).data('video-id');
			$( '#' + target ).fadeIn();
		} );
		$('.wpbrigade-close-popup').on('click', function(e) {
			$(this).parent().parent().fadeOut();
			$('.wpbrigade-video-wrapper iframe').attr( 'src', 'https://www.youtube.com/embed/GMAwsHomJlE' );
		});

		// Code for Video Popup
		var videoBtn = document.querySelectorAll('.video a');
		for (var i = 0; i < videoBtn.length; i++) {
			videoBtn[i].addEventListener('click', function(e) {
				e.preventDefault();
				var getLink = this.getAttribute('href');
				document.querySelector('#loginpress-video').setAttribute('src', getLink.replace('watch?v=', 'embed/')+ '?autoplay=1');
				document.querySelector('html').classList.add('loginpress-video-play');
			});
		}
	
		function closePop() {
			document.querySelector('#loginpress-video').setAttribute('src', '');
			document.querySelector('html').classList.remove('loginpress-video-play');
		}
		if (document.querySelector('.loginpress-cross')) {
			document.querySelector('.loginpress-cross').addEventListener('click', function(e) {
				closePop();
			});
			document.querySelector('.loginpress-video-overlay').addEventListener('click', function(e) {
				closePop();
			});
		}

		// $("#wpb-loginpress_setting\\[enable_recaptcha_promo\\]").on('click', function() {
		//
		//   var promotion = $('#wpb-loginpress_setting\\[enable_recaptcha_promo\\]');
		//   if ( promotion.is(":checked") ) {
		//     $('tr.recaptcha-promo-img').show();
		//   } else {
		//     $('tr.recaptcha-promo-img').hide();
		//   }
		// }); // on click promo checkbox.

		// Remove Disabled attribute from Import Button.
		$('#loginPressImport').on('change', function(event) {

			event.preventDefault();
			var loginpressFileImp = $('#loginPressImport').val();
			$(this).prev('span').html(loginpressFileImp.split('\\').pop());
			var loginpressFileExt = loginpressFileImp.substr(
			loginpressFileImp.lastIndexOf('.') + 1);
	
			$('.loginpress-import').attr( "disabled", "disabled" );
	
			if ( 'json' == loginpressFileExt && loginpressFileImp.split(/(\\|\/)/g).pop().substring( 0, 10 ) === 'loginpress' ) {
				$(".import_setting .wrong-import").html("");
				$('.loginpress-import').removeAttr("disabled");
			} else {
				$(".import_setting .wrong-import").html("Choose LoginPress settings file only.");
			}
		});

		$("#wpb-loginpress_setting\\[enable_privacy_policy\\]").on( 'click', function() {

			var privacy_editor = $('#wpb-loginpress_setting\\[enable_privacy_policy\\]');
			if (privacy_editor.is(":checked")) {
				$('tr.privacy_policy').show();
			} else {
				$('tr.privacy_policy').hide();
			}
      	}); // on click promo checkbox.

		/**
		 * On load of LoginPress settings or Click on Force Rest Password show or hide section 
		 * @since 3.0.0
		 */
		function loginPressForceChangePasswordSettings() {

			if ( $('#wpb-loginpress_setting\\[enable_password_reset\\]').is(":checked") ) {
				$('tr.roles_for_password_reset').show();
				$('tr.loginpress_password_reset_time_limit').show();

			} else{
				$('tr.roles_for_password_reset').hide();
				$('tr.loginpress_password_reset_time_limit').hide();
			}
		}
		$("#wpb-loginpress_setting\\[enable_password_reset\\]").on('click', function() {
			loginPressForceChangePasswordSettings();
		} );

    	$(window).on('load', function() {

			$('#loginpress_login_redirect_roles th.loginpress_user_id').text(loginpress_script.localize_translations[0]);

			$( '<tr class="recaptcha-promo-img"><th class="recaptcha-promo" colspan="2"><img src="' + loginpress_script.plugin_url + '/loginpress/img/promo/recaptcha_promo.png"><a class="recaptcha-promo-link" href="https://loginpress.pro/pricing/?utm_source=loginpress-lite&amp;utm_medium=recaptcha-settings&amp;utm_campaign=pro-upgrade" target="_blank"><span>Unlock Premium Feature</span></a></th></tr>' ).insertAfter($(".enable_recaptcha_promo").closest('tr'));

			var promotion = $('#wpb-loginpress_setting\\[enable_recaptcha_promo\\]');
			if (promotion.is(":checked")) {
				$('tr.recaptcha-promo-img').show();
			}

			var privacy_editor = $('#wpb-loginpress_setting\\[enable_privacy_policy\\]');
			if (privacy_editor.is(":checked")) {
				$('tr.privacy_policy').show();
			}

			loginPressForceChangePasswordSettings();

		}); // Window on load.

    	$('.loginpress-log-file').on('click', function(event) {

    		event.preventDefault();

    		$.ajax({

				url: ajaxurl,
        		type: 'POST',
        		data: {
          			action: 'loginpress_help',
					security: loginpress_script.help_nonce,
        		},
				beforeSend: function() {
					$(".log-file-sniper").show();
				},
        		success: function(response) {

					$(".log-file-sniper").hide();
					$(".log-file-text").show();

          			if (!window.navigator.msSaveOrOpenBlob) { // If msSaveOrOpenBlob() is supported, then so is msSaveBlob().
						$("<a />", {
							"download": "loginpress-log.txt",
							"href": "data:text/plain;charset=utf-8," +
							encodeURIComponent(response),
						}).appendTo("body")
						.click(function() {
							$(this).remove()
						})[0].click()
					} else {
						var blobObject = new Blob([response]);
						window.navigator.msSaveBlob(blobObject,
						'loginpress-log.txt');
					}
					setTimeout(function() {
						$(".log-file-text").fadeOut()
					}, 3000);
        		}
      		});

    	});

    	$('.loginpress-export').on('click', function(event) {

			event.preventDefault();

			var dateObj = new Date();
			var month = dateObj.getUTCMonth() + 1; //months from 1-12
			var day = dateObj.getUTCDate();
			var year = dateObj.getUTCFullYear();
			var newdate = year + "-" + month + "-" + day;
			var export_nonce = $('.loginpress_export_nonce').val();

			$.ajax({

				url: ajaxurl,
				type: 'POST',
				data: {
				action: 'loginpress_export',
					security: export_nonce,
				},
				beforeSend: function() {
					$(".export_setting .export-sniper").show();
				},
				success: function(response) {

					$(".export_setting .export-sniper").hide();
					$(".export_setting .export-text").show();

					if ( ! window.navigator.msSaveOrOpenBlob ) { // If msSaveOrOpenBlob() is supported, then so is msSaveBlob().
						$("<a />", {
							"download": "loginpress-export-" + newdate +
							".json",
							"href": "data:application/json;charset=utf-8," +
							encodeURIComponent(response),
						}).appendTo("body")
						.click(function() {
							$(this).remove()
						})[0].click()
					} else {
						var blobObject = new Blob([response]);
						window.navigator.msSaveBlob( blobObject, "loginpress-export-" + newdate + ".json" );
					}
					setTimeout( function() {
						$(".export_setting .export-text").fadeOut()
					}, 3000 );
				}
			});
    	});

    	$('.loginpress-import').on('click', function(event) {

      		event.preventDefault();
			
			var file = $('#loginPressImport');
			var import_nonce = $('.loginpress_import_nonce').val();
			var fileObj = new FormData();
			var content = file[0].files[0];

			fileObj.append('file', content);
			fileObj.append('name', content['name']);
			fileObj.append('action', 'loginpress_import');
			fileObj.append('security', import_nonce);

    		$.ajax({

				processData: false,
				contentType: false,
				url: ajaxurl,
        		type: 'POST',
        		data: fileObj, // file and action append into variable fileObj.
				beforeSend: function() {
					$(".import_setting .import-sniper").show();
					$(".import_setting .wrong-import").html("");
					$('.loginpress-import').attr("disabled", "disabled");
				},
				success: function(response) {
					// console.log(response);
					$(".import_setting .import-sniper").hide();
					// $(".import_setting .import-text").fadeIn();
					if ('error' == response) {
						$(".import_setting .wrong-import").html(
						"JSON File is not Valid.");
					} else {
						$(".import_setting .import-text").show();
						setTimeout(function() {
							$(".import_setting .import-text").fadeOut();
							// $(".import_setting .wrong-import").html("");
							file.val('');
						}, 3000);
					}

				}
    		}); //!ajax.
		});
	 });
	 $(document).ready(function () {
		//run the select code for all selects
		generate_select('select:not(.gfield_select)'); 
		$(".tabs-toggle").on("click", function(){
			$(this).toggleClass("active").next(".loginpress-tabs-wrapper").slideToggle();
		})
		$(".settings-tabs-list").on("click", function(){
			if(window.matchMedia('(max-width: 767px)').matches === true){
				$(this).closest(".loginpress-tabs-wrapper").slideUp();
				$(".tabs-toggle").removeClass("active");
			}
		})
	})

	 function generate_select(selector) {
        $(selector).each(function() {

            // Cache the number of options
            var $this = $(this),
                activeValue = $this.val(),
                classselect = $this.attr("class"),
                numberOfOptions = $(this).children("option").length;

            // Hides the select element
            $this.addClass("s-hidden");

            // Wrap the select element in a div
            $this.wrap('<div class="select ' + classselect + '"></div>');

            // Insert a styled div to sit over the top of the hidden select element
            $this.after('<div class="styledSelect"></div>');

            // Cache the styled div
            var $styledSelect = $this.next("div.styledSelect");

            var getHTML = $this.children('option[value="' + $this.val() + '"]').text();

            //   if ($this.children('option[value="' + $this.val() + '"]').length > 1) {
            // var getHTML = $this
            // .children("option")
            // .eq(0)
            // .text();
            //   }
            // Show the first select option in the styled div
            $styledSelect.html('<span class="text-ellipses">' + getHTML + '</span>');

            // Insert an unordered list after the styled div and also cache the list
            var $list = $("<ul />", { class: "options" }).insertAfter($styledSelect);

            // Insert a list item into the unordered list for each select option
            for (var i = 0; i < numberOfOptions; i++) {
                var Cls = $this.children("option").eq(i).attr('class');
                if (Cls == undefined) {
                    Cls = '';
                }
                if ($this.children("option").eq(i).val() == activeValue) {
                    Cls = Cls + ' active';
                    $('.text-ellipses').addClass('valueAdded');
                }
                $("<li />", {
                    text: $this
                        .children("option")
                        .eq(i)
                        .text(),
                    rel: $this
                        .children("option")
                        .eq(i)
                        .val(),
                    class: Cls
                }).appendTo($list);
            }

            // Cache the list items
            var $listItems = $list.children("li");

            // Show the unordered list when the styled div is clicked (also hides it if the div is clicked again)
            $styledSelect.click(function(e) {
                e.stopPropagation();
                if (!$(this).hasClass('active')) {
                    $('div.styledSelect.active').each(function() {
                        $(this).removeClass('active').next('ul.options').slideUp();
                        // return false;
                    });
                    $(this).toggleClass("active");
                    $(this).next("ul.options").stop(true).slideToggle();
                } else {
                    $('div.styledSelect.active').each(function() {
                        $(this).removeClass('active').next('ul.options').slideUp();
                        // return false;
                    });
                }
            });

            // Hides the unordered list when a list item is clicked and updates the styled div to show the selected list item
            // Updates the select element to have the value of the equivalent option
            $listItems.click(function(e) {
                e.stopPropagation();
                $styledSelect.html('<span class="text-ellipses valueAdded">' + $(this).text() + '</span>').removeClass("active");
                var value = $(this).attr("rel").toString();
                $($this).val(value);
                $($this).trigger("change");
                $('ul.options').slideUp();
                $(this).addClass("active").siblings().removeClass("active");
                /* alert($this.val()); Uncomment this for demonstration! */
            });

            // Hides the unordered list when clicking outside of it
            $(document).click(function() {
                $styledSelect.removeClass("active");
                $list.slideUp();
            });

        });
    }
})(jQuery); // This invokes the function above and allows us to use '$' in place of 'jQuery' in our code.
