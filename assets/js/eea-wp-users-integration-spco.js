/**
 * Javascript for wpusers integration with spco
 */
var WPUSPCO;
jQuery(document).ready( function($) {


	/**
	 * @namespace WPUSPCO
	*/
	WPUSPCO = {

		params : {
			content: '',
			responseData: {},
			responseError: '',
			responseSuccess: '',
			showErrorsInContext: false,
			dialog: {}
		},

		processResponse : function( response ) {
			//make sure we ALWAYS clear the WPUSPCO notices.
			this.clearNotices();

			//first determine if there are any wpuser errors that need handling.
			if ( typeof response.return_data !== 'undefined' && typeof response.return_data.wp_user_response !== 'undefined' ) {
				this.params.responseData = response.return_data.wp_user_response;
				this.params.responseError = typeof response.errors !== 'undefined' && response.errors ? response.errors : '';
				this.params.responseSuccess = typeof response.success !== 'undefined' && response.success ? response.success : '';
				this.params.showErrorsInContext = typeof this.params.responseData.show_errors_in_context !== 'undefined' ? this.params.responseData.show_errors_in_context : this.params.showErrorsInContext;
				SPCO.override_messages = true;
				this.highlightFields();
				if ( ! this.params.showErrorsInContext ) {
					this.showDialog();
				}
				return;
			}
			return;
		},

		highlightFields : function() {
			if ( typeof this.params.responseData.validation_error !== 'undefined' && typeof this.params.responseData.validation_error.field !== 'undefined' ) {
				_.each( this.params.responseData.validation_error.field, function( val, ind, list ) {
					$('#' + val).removeClass( 'ee-has-value' ).addClass( 'ee-needs-value' );
					if ( WPUSPCO.params.showErrorsInContext ) {
						WPUSPCO.showInContext( val );
					}
				});
			}
		},

		showLoginForm : function() {

		},


		/**
		 * Displays dialog and shows message.  This dialog remains
		 * persistent, so user is able to choose their action.
		 * uses the #espresso-ajax-notices container however we do NOT
		 * fade out.
		 *
		 */
		showDialog : function() {
			SPCO.hide_notices();
			var msgType = this.params.responseError !== '' ? 'error' : 'success';
			var msg = msgType == 'error' ? this.params.responseError : this.params.responseSuccess;

			$('#espresso-ajax-notices').eeCenter('fixed');

			this.dialog = $('#espresso-ajax-notices-' + msgType );
			this.dialog.children( '.espresso-notices-msg' ).html( msg );
			SPCO.end_ajax();
			this.dialog.removeClass('hidden').show();

			if ( SPCO.allow_enable_submit_buttons ) {
				SPCO.enable_submit_buttons();
			}
		},


		/**
		 * This differs from showDialog in that it shows in context for the given element id string.
		 */
		showInContext : function( id ) {
			SPCO.hide_notices();
			SPCO.end_ajax();
			msg = '<div class="highlight-bg important-notice ee-inline-context-notice">' + this.params.responseError + '</div>';
			$('#' + id).after( msg );
			if ( SPCO.allow_enable_submit_buttons ) {
				SPCO.enable_submit_buttons();
			}
		},


		clearNotices : function() {
			$('.ee-inline-context-notice').remove();
		},


		doAjax : function() {

		}
	};


	SPCO.main_container.on('spco_process_response', function( event, next_step, SPCO_response ) {
		WPUSPCO.processResponse( SPCO_response );
	});
});
