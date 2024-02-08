jQuery( function ( $ ) {

	$( '#smylesv2-modal' ).modal({
		closable: false,
		onApprove: function () {
			smylesv2.deactivate();
		}
    });
	
	$( '#smylesv2-msg-close' ).on( 'click', function () {
			$( this ).closest( '.message' ).transition( 'fade' );
	} );

	var smylesv2 = {};

	smylesv2.callbacks = {

		cards: function () {

			$( '.activate-license' ).off('click').click( function ( e ) {
				$( '#smylesv2-msg' ).fadeOut();
				var slug = $( this ).prop( 'id' );
				smylesv2.activate( slug );
			} );

			$( '.deactivate-license' ).off( 'click' ).click( function ( e ) {
				$( '#smylesv2-msg' ).fadeOut();
				slug = $( this ).prop( 'id' );
				product_id = $( '#' + slug + '_product_id' ).val();

				$( '#smylesv2-modal-slug' ).html( product_id );
				$( '#smylesv2-modal' ).data( 'slug', slug ).modal( 'show' );
			} );

			$( '.smylesv2-card .image' ).dimmer( { on: 'hover' } );

		}

	};

	smylesv2.activate = function ( slug ) {

		var has_error = false;

		var email = $( '#' + slug + '_email' );
		var license_key = $( '#' + slug + '_license_key' );
		var product_id = $( '#' + slug + '_product_id' );
		var version = $( '#' + slug + '_version' );

		if ( email.val().length < 1 ) {
			has_error = true;
			email.addClass( 'smylesv2-error' );
		} else {
			email.removeClass( 'smylesv2-error' );
		}

		if ( license_key.val().length < 1 ) {
			has_error = true;
			license_key.addClass( 'smylesv2-error' );
		} else {
			license_key.removeClass( 'smylesv2-error' );
		}

		if ( has_error ) return false;

		var data = {
			'action'     : 'smyles_updater_v2_activation',
			'nonce'      : $( '#smyles_updater_v2_activation' ).val(),
			'email'      : email.val(),
			'slug'       : slug,
			'license_key': license_key.val(),
			'product_id' : product_id.val(),
			'version'    : version.val()
		};

		$.ajax(
			ajaxurl, {
				type      : 'POST',
				dataType  : 'JSON',
				data      : data,
				beforeSend: function () {
					$( '#' + slug + '_dimmer' ).addClass( 'active' );
				},
				error     : function ( request, status, error ) {
					var errorResponse;
					if ( request.responseText ) {

						errorResponse = request.responseText;

						// Check if error response was xDebug to only return summary of error
						var xDebugCheck = $( '.xdebug-error th:first', request.responseText ).html();
						if ( xDebugCheck ) errorResponse = xDebugCheck;

					} else {

						errorResponse = error;

					}

					console.log( errorResponse );

					smylesv2.message.error.show( 'Error', errorResponse );
				},
				success   : function ( data ) {

					if ( data === '0' ) smylesv2.message.error.show( 'Error', 'Unknown Error' );

					if ( data.error_title && data.error_msg ) {
						smylesv2.message.error.show( data.error_title, data.error_msg );
					}

					if ( data.success_title && data.success_msg ) {
						smylesv2.message.success.show( data.success_title, data.success_msg );
						if ( data.card_html ){
							$( '#' + slug ).html( data.card_html );
							smylesv2.callbacks.cards();
						}

					}

				},
				complete  : function () {
					$( '#' + slug + '_dimmer' ).removeClass( 'active' );
				}
			}
		);
	};

	smylesv2.deactivate = function ( slug ) {

		if ( ! slug ) slug = $( '#smylesv2-modal' ).data( 'slug' );

		var product_id = $( '#' + slug + '_product_id' );
		var email = $( '#' + slug + '_email' );
		var license_key = $( '#' + slug + '_license_key' );

		var data = {
			'action'     : 'smyles_updater_v2_deactivation',
			'nonce'      : $( '#smyles_updater_v2_activation' ).val(),
			'slug'       : slug,
			'product_id' : product_id.val()
		};

		$.ajax(
			ajaxurl, {
				type      : 'POST',
				dataType  : 'JSON',
				data      : data,
				beforeSend: function () {
					$( '#' + slug + '_dimmer' ).addClass( 'active' );
				},
				error     : function ( request, status, error ) {
					var errorResponse;
					if ( request.responseText ) {

						errorResponse = request.responseText;

						// Check if error response was xDebug to only return summary of error
						var xDebugCheck = $( '.xdebug-error th:first', request.responseText ).html();
						if ( xDebugCheck ) errorResponse = xDebugCheck;

					} else {
						errorResponse = error;
					}

					console.log( errorResponse );
					smylesv2.message.error.show( 'Error', errorResponse );
				},
				success   : function ( data ) {

					if ( data === '0' ) smylesv2.message.error.show( 'Error', 'Unknown Error' );

					if ( data.error_title && data.error_msg ) {
						smylesv2.message.error.show( data.error_title, data.error_msg );
					}

					if ( data.success_title && data.success_msg ) {
						smylesv2.message.success.show( data.success_title, data.success_msg );
					}

					if ( data.card_html ) {
						$( '#' + slug ).html( data.card_html );
						smylesv2.callbacks.cards();
					}

				},
				complete  : function () {
					$( '#' + slug + '_dimmer' ).removeClass( 'active' );
				}
			}
		);
	};

	smylesv2.message = {

		reset: function(){
			$('#smylesv2-msg').removeClass().addClass( 'ui icon message hidden' );
			$('#smylesv2-msg-icon').removeClass().addClass( 'info icon' );
			$('#smylesv2-msg-header').html('');
			$('#smylesv2-msg-details').html('');
		},

		show: function ( title, message, color, icon ) {

			if ( ! color ) color = '';
			if ( ! icon ) icon = 'info';

			$( '#smylesv2-msg-header' ).html( title );
			$( '#smylesv2-msg-details' ).html( message );

			$( '#smylesv2-msg-icon' ).removeClass().addClass( icon + ' icon' );
			$( '#smylesv2-msg' ).removeClass().addClass( 'ui icon message ' + color );

		},

		hide: function () {
			$('#smylesv2-msg').transition( 'fade' );
		},

		error: {

			show: function ( title, message ) {
				smylesv2.message.show( title, message, 'red', 'remove' );
			}

		},

		success: {
			show: function ( title, message ) {
				smylesv2.message.show( title, message, 'olive', 'checkmark' );
			}
		}
	};

	// Initialize card callbacks (on click, etc)
	smylesv2.callbacks.cards();
} );