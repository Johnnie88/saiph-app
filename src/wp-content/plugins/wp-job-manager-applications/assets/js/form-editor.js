import { setupTabNavigation } from './tabs';

setupTabNavigation( '.wp-job-manager-applications-form-editor-tabs' );

/* global wp_job_manager_applications_form_editor */
jQuery( document ).ready( function ( $ ) {
	const wpjm_application_form_editor_select2_args = {
		minimumResultsForSearch: 10,
	};
	if (
		1 === parseInt( wp_job_manager_applications_form_editor.is_rtl, 10 )
	) {
		wpjm_application_form_editor_select2_args.dir = 'rtl';
	}

	$( '.wp-job-manager-applications-form-editor' )
		.on( 'init', function () {
			$( '#form-fields', this ).sortable( {
				items: 'tr',
				cursor: 'move',
				axis: 'y',
				handle: 'td.sort-column',
				scrollSensitivity: 40,
				helper( e, ui ) {
					ui.children().each( function () {
						$( this ).width( $( this ).width() );
					} );
					return ui;
				},
				start( event, ui ) {
					ui.item.css( 'background-color', '#FEFEE6' );
				},
				stop( event, ui ) {
					ui.item.removeAttr( 'style' );
				},
			} );
			$( this ).find( '.field-type select' ).change();

			if ( $.isFunction( $.fn.select2 ) ) {
				$( this )
					.find( '.field-rules select' )
					.select2( wpjm_application_form_editor_select2_args )
					.change();
			} else if ( $.isFunction( $.fn.chosen ) ) {
				$( this ).find( '.field-rules select' ).chosen().change();
			}
		} )
		.on( 'rules:refresh', '.field-rules select', function () {
			if ( $.isFunction( $.fn.select2 ) ) {
				const self = this;
				setTimeout( function () {
					$( self ).select2();
				}, 500 );
			} else {
				$( this ).trigger( 'chosen:updated' );
			}
		} )
		.on( 'rule:deselect', '.field-rules select', function ( evt, params ) {
			if ( undefined === params.deselected ) {
				return;
			}
			$( '.field-rules select' ).each( function () {
				$( this )
					.find( 'option[value=' + params.deselected + ']' )
					.removeProp( 'disabled' );
				$( this ).trigger( 'rules:refresh' );
			} );
		} )
		.on( 'select2:unselect', '.field-rules select', function ( evt ) {
			const data = evt.params.data;
			$( this ).trigger( 'rule:deselect', { deselected: data.id } );
		} )
		.on( 'change', '.field-rules select', function ( evt, params ) {
			if ( undefined !== params && undefined !== params.deselected ) {
				$( this ).trigger( 'rule:deselect', params );
				return;
			}
			const $self = $( this );
			const unique_selected = [];
			jQuery.each( $( this ).val(), function ( i, option_key ) {
				const $option = $self.find(
					'option[value=' + option_key + ']'
				);
				if ( $option.hasClass( 'unique' ) ) {
					unique_selected.push( option_key );
				}
			} );
			if ( unique_selected.length > 0 ) {
				$( '.field-rules select' ).each( function () {
					if ( this.isEqualNode( $self.get( 0 ) ) ) {
						return;
					}
					const $selector = $( this );
					jQuery.each( unique_selected, function ( i, option_key ) {
						$selector
							.find( 'option[value=' + option_key + ']' )
							.prop( 'disabled', true )
							.removeProp( 'selected' );
					} );
					$( this ).trigger( 'rules:refresh' );
				} );
			}
		} )
		.on( 'change', '.field-type select', function () {
			$( this )
				.closest( 'tr' )
				.find( '.field-options .placeholder' )
				.hide();
			$( this ).closest( 'tr' ).find( '.field-options .options' ).hide();
			$( this ).closest( 'tr' ).find( '.field-options .na' ).hide();
			$( this )
				.closest( 'tr' )
				.find( '.field-options .file-options' )
				.hide();

			if (
				'select' === $( this ).val() ||
				'multiselect' === $( this ).val()
			) {
				$( this )
					.closest( 'tr' )
					.find( '.field-options .options' )
					.show();
			} else if (
				'resumes' === $( this ).val() ||
				'output-content' === $( this ).val()
			) {
				$( this ).closest( 'tr' ).find( '.field-options .na' ).show();
			} else if ( 'file' === $( this ).val() ) {
				$( this )
					.closest( 'tr' )
					.find( '.field-options .file-options' )
					.show();
			} else {
				$( this )
					.closest( 'tr' )
					.find( '.field-options .placeholder' )
					.show();
			}

			$( this ).closest( 'tr' ).find( '.field-rules .rules' ).hide();
			$( this ).closest( 'tr' ).find( '.field-rules .na' ).hide();

			if ( 'output-content' === $( this ).val() ) {
				$( this ).closest( 'tr' ).find( '.field-rules .na' ).show();
				const $rules = $( this )
					.closest( 'tr' )
					.find( '.field-rules select' );
				jQuery.each( $rules.val(), function ( i, value ) {
					$rules.trigger( 'rule:deselect', { deselected: value } );
				} );
				$rules.val( '' );
			} else {
				$( this ).closest( 'tr' ).find( '.field-rules .rules' ).show();
				if ( $.isFunction( $.fn.select2 ) ) {
					$( this )
						.closest( 'tr' )
						.find( '.field-rules select:visible' )
						.select2( wpjm_application_form_editor_select2_args );
				} else if ( $.isFunction( $.fn.chosen ) ) {
					$( this )
						.closest( 'tr' )
						.find( '.field-rules select:visible' )
						.chosen();
				}
			}
		} )
		.on( 'click', '.delete-field', function () {
			if (
				// eslint-disable-next-line no-alert
				window.confirm(
					wp_job_manager_applications_form_editor.confirm_delete_i18n
				)
			) {
				$( this ).closest( 'tr' ).remove();
			}
		} )
		.on( 'click', '.reset', function () {
			if (
				// eslint-disable-next-line no-alert
				window.confirm(
					wp_job_manager_applications_form_editor.confirm_reset_i18n
				)
			) {
				return true;
			}
			return false;
		} )
		.on( 'click', '.add-field', function () {
			const $tbody = $( this ).closest( 'table' ).find( 'tbody' );
			let row = $tbody.data( 'field' );
			row = row.replace(
				/\[-1\]/g,
				'[' + $tbody.find( 'tr' ).length + ']'
			);
			$tbody.append( row );
			$( '.wp-job-manager-applications-form-editor' ).trigger( 'init' );
			return false;
		} );

	$( '.wp-job-manager-applications-form-editor' ).trigger( 'init' );
} );
