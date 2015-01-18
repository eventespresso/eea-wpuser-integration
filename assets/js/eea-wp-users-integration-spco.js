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
			dialog: {}
		},

		processResponse : function( response ) {
			//first determine if there are any wpuser errors that need handling.
			if ( typeof response.return_data.wp_user_response !== 'undefined' ) {
				this.params.responseData = response.return_data.wp_user_response;
				this.params.responseError = typeof response.errors !== 'undefined' && response.errors ? response.errors : '';
				this.params.responseSuccess = typeof response.success !== 'undefined' && response.success ? response.success : '';
				SPCO.override_messages = true;
				this.highlightFields();
				this.showDialog();
				return;
			}
			return;
		},

		highlightFields : function() {
			if ( typeof this.params.responseData.validation_error !== 'undefined' && typeof this.params.responseData.validation_error.field !== 'undefined' ) {
				_.each( this.params.responseData.validation_error.field, function( val, ind, list ) {
					$('#' + val).removeClass( 'ee-has-value' ).addClass( 'ee-needs-value' );
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

		doAjax : function() {

		}
	};


	SPCO.main_container.on('spco_process_response', function( event, next_step, SPCO_response ) {
		WPUSPCO.processResponse( SPCO_response );
	});
});
