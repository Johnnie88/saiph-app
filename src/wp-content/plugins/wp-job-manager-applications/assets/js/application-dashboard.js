/* global job_manager_application */

jQuery( document ).ready( function ( $ ) {
	$(
		'section.job-application-content, section.job-application-notes, section.job-application-edit'
	)
		.hide()
		.prepend(
			'<a href="#" class="hide_section">' +
				job_manager_application.i18n_hide +
				'</a>'
		);

	$( 'form.filter-job-applications' ).on( 'change', 'select', function () {
		$( 'form.filter-job-applications' ).submit();
	} );

	$( '#job-manager-job-applications' )
		.on( 'click', '.job-application-toggle-content', function () {
			$( this )
				.closest( 'li.job-application' )
				.find( 'section:not(.job-application-content)' )
				.slideUp();
			$( this )
				.closest( 'li.job-application' )
				.find( 'section.job-application-content' )
				.slideToggle();
			return false;
		} )
		.on( 'click', '.job-application-toggle-edit', function () {
			$( this )
				.closest( 'li.job-application' )
				.find( 'section:not(.job-application-edit)' )
				.slideUp();
			$( this )
				.closest( 'li.job-application' )
				.find( 'section.job-application-edit' )
				.slideToggle();
			return false;
		} )
		.on( 'click', '.job-application-toggle-notes', function () {
			$( this )
				.closest( 'li.job-application' )
				.find( 'section:not(.job-application-notes)' )
				.slideUp();
			$( this )
				.closest( 'li.job-application' )
				.find( 'section.job-application-notes' )
				.slideToggle();
			return false;
		} )
		.on( 'click', 'a.hide_section', function () {
			$( this ).closest( 'section' ).slideUp();
			return false;
		} )
		.on( 'click', '.job-application-note-add input.button', function () {
			const button = $( this );
			const application_id = button.data( 'application_id' );
			const job_application = $( this ).closest( '.job-application' );
			const job_application_note = job_application.find( 'textarea' );
			const disabled_attr = $( this ).attr( 'disabled' );
			const job_application_notes_list = job_application.find(
				'ul.job-application-notes-list'
			);

			if (
				typeof disabled_attr !== 'undefined' &&
				disabled_attr !== false
			) {
				return false;
			}
			if ( ! job_application_note.val() ) {
				return false;
			}

			button.attr( 'disabled', 'disabled' );

			const data = {
				action: 'add_job_application_note',
				note: job_application_note.val(),
				application_id,
				security: job_manager_application.job_application_notes_nonce,
			};

			$.post(
				job_manager_application.ajax_url,
				data,
				function ( response ) {
					job_application_notes_list.append( response );
					button.removeAttr( 'disabled' );
					job_application_note.val( '' );
				}
			);

			return false;
		} )
		.on( 'click', 'a.delete_note', function () {
			// eslint-disable-next-line no-alert
			const answer = confirm(
				job_manager_application.i18n_confirm_delete
			);
			if ( answer ) {
				const note = $( this ).closest( 'li' );
				const note_id = note.attr( 'rel' );

				const data = {
					action: 'delete_job_application_note',
					note_id,
					security:
						job_manager_application.job_application_notes_nonce,
				};

				$.post( job_manager_application.ajax_url, data, function () {
					note.fadeOut( 500, function () {
						note.remove();
					} );
				} );
			}
			return false;
		} )
		.on( 'click', 'a.delete_job_application', function () {
			// eslint-disable-next-line no-alert
			const answer = confirm(
				job_manager_application.i18n_confirm_delete
			);
			if ( answer ) {
				return true;
			}
			return false;
		} );
} );
