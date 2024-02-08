/* global job_manager_bookmarks */
jQuery( document ).ready( function ( $ ) {
	$( '.bookmark-notice' ).click( function () {
		$( '.bookmark-details' ).slideToggle();
		$( this ).toggleClass( 'open' );
		return false;
	} );

	$( '.job-manager-bookmark-action-delete' ).click( function () {
		if ( $( this ).hasClass( 'disabled' ) ) {
			return false;
		}

		const $self = $( this );
		const $row = $self.closest( 'tr' );
		// eslint-disable-next-line no-alert
		const answer = confirm( job_manager_bookmarks.i18n_confirm_delete );

		if ( answer ) {
			const $spinner = $( '<span />', {
				class: 'spinner',
				style:
					'background-image: url(' +
					job_manager_bookmarks.spinner_url +
					')',
			} ).insertAfter( $self );
			$self.addClass( 'disabled' );
			$row.addClass( 'performing-action' );

			jQuery
				.getJSON( $( this ).attr( 'href' ), { 'wpjm-ajax': 1 } )
				.done( function ( data ) {
					if ( data && data.success ) {
						$row.remove();
					}
				} )
				.fail( function ( jqXHR ) {
					console.log( {
						failed_task: 'Bookmark Deletion',
						response_code: jqXHR.status,
						response: jqXHR.responseText,
					} );
				} )
				.always( function () {
					$spinner.remove();
					$row.removeClass( 'performing-action' );
					$self.removeClass( 'disabled' );
				} );
		}
		return false;
	} );

	$( '.wp-job-manager-bookmarks-form .remove-bookmark' ).click( function () {
		if ( $( this ).hasClass( 'disabled' ) ) {
			return false;
		}

		const $self = $( this );
		// eslint-disable-next-line no-alert
		const answer = confirm( job_manager_bookmarks.i18n_confirm_delete );
		if ( answer ) {
			const $spinner = $( '<span />', {
				class: 'spinner is-active',
				style:
					'background-image: url(' +
					job_manager_bookmarks.spinner_url +
					')',
			} ).appendTo( $self );
			$self.addClass( 'disabled performing-action' );

			jQuery
				.getJSON( $( this ).attr( 'href' ), { 'wpjm-ajax': 1 } )
				.done( function ( data ) {
					if ( data && data.success ) {
						const $form = $self.closest(
							'form.wp-job-manager-bookmarks-form'
						);
						$( '#bookmark_notes' ).val( '' );
						$form.removeClass( 'has-bookmark' );
						$form
							.find( '.submit-bookmark-button' )
							.prop(
								'value',
								job_manager_bookmarks.i18n_add_bookmark
							);
					}
				} )
				.fail( function ( jqXHR ) {
					console.log( {
						failed_task: 'Bookmark Deletion',
						response_code: jqXHR.status,
						response: jqXHR.responseText,
					} );
				} )
				.always( function () {
					$spinner.remove();
					$self.removeClass( 'disabled performing-action' );
				} );
		}
		return false;
	} );

	const $submit = $(
		'.wp-job-manager-bookmarks-form input[type=submit]'
	).click( function () {
		if ( $( this ).hasClass( 'disabled' ) ) {
			return false;
		}
		return true;
	} );

	const $spinner = $( this ).find( '.spinner' );

	$( '.wp-job-manager-bookmarks-form' ).submit( function ( e ) {
		e.preventDefault();
		const $self = $( this );

		$spinner.addClass( 'is-active' );
		$submit.addClass( 'disabled' );

		$.post( {
			url: $( this ).attr( 'action' ),
			data: $( this ).serialize() + '&wpjm-ajax=1&submit_bookmark=1',
		} )
			.done( function ( data ) {
				if ( ! data || ! data.success ) {
					return;
				}
				if ( data.note ) {
					$( '#bookmark_notes' ).val( data.note );
				}
				$self.addClass( 'has-bookmark' );
				$self
					.find( '.submit-bookmark-button' )
					.prop(
						'value',
						job_manager_bookmarks.i18n_update_bookmark
					);
			} )
			.always( function () {
				$spinner.removeClass( 'is-active' );
				$submit.removeClass( 'disabled' );
			} )
			.fail( function ( jqXHR ) {
				console.log( {
					failed_task: 'Bookmark Save',
					response_code: jqXHR.status,
					response: jqXHR.responseText,
				} );
			} );
	} );
} );
