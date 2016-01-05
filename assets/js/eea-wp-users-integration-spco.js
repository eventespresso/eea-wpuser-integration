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
			dialog: {},
			loginForm: {},
			ajaxData: {}
		},

		submitListenerSet : false,

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

				if ( ! this.params.showErrorsInContext && this.params.responseError !== '' 	) {
					this.showDialog();
				}

				//login form response?
				if ( typeof this.params.responseData.show_login_form !== 'undefined' && this.params.responseData.show_login_form ) {
					this.clearNotices();
					this.showLoginForm();
					this.highlightFields( 'login' );
					return;
				}

				if ( this.params.responseSuccess !== '' ) {
					SPCO.override_messages = false;
					this.clearNotices();
					SPCO.scroll_to_top_and_display_messages( '#spco-attendee_information-dv', response, true );
					return;
				}

				this.highlightFields();

				return;
			}
			return;
		},

		highlightFields : function( context ) {
			context = typeof context === 'undefined' ? 'default' : context;
			var container = SPCO.main_container;
			var cssType = '#';

			if ( context == 'login' ) {
				container = dialogHelper.dialogContentContainer;
				cssType = '.';
			}

			if ( typeof this.params.responseData.validation_error !== 'undefined' && typeof this.params.responseData.validation_error.field !== 'undefined' ) {
				_.each( this.params.responseData.validation_error.field, function( val, ind, list ) {
					$( cssType + val, container).removeClass( 'ee-has-value' ).addClass( 'ee-needs-value' );
					if ( WPUSPCO.params.showErrorsInContext ) {
						WPUSPCO.showInContext( val, cssType, container );
					}
				});
			}
		},


		/**
		 * Shows the login form for the WordPress site.
		 *
		 */
		showLoginForm : function() {
			this.params.loginForm = $('#ee-login-form-container').clone().html();
			this.showDialog( this.params.loginForm, 'attention' );
			position_dialog(4);
			this.setSubmitListener();
			return;
		},



		setSubmitListener : function() {
			if ( this.submitListenerSet ) {
				return;
			}
			//add listener for login submit
			dialogHelper.dialogContentContainer.on('submit', '.ee-login-form', function(e) {
				e.preventDefault();
				e.stopPropagation();
				WPUSPCO.processLogin();
			});
			this.submitListenerSet = true;
		},



		processLogin : function() {
			this.params.ajaxData = {
				action : 'ee_process_login_form',
				ee_front_ajax : true,
				login_name : $('.user_login', dialogHelper.dialogContentContainer ).val(),
				login_pass : $('.user_pass', dialogHelper.dialogContentContainer ).val(),
				rememberme : $('.rememberme', dialogHelper.dialogContentContainer ).val()
			};
			dialogHelper.closeModal();

			this.doAjax();
		},


		/**
		 * Displays dialog and shows message.  This dialog remains
		 * persistent, so user is able to choose their action.
		 * uses the ee-dialog helper.
		 *
		 */
		showDialog : function( msg, msgType ) {
			SPCO.hide_notices();
			if ( typeof msgType === 'undefined' ) {
				msgType = this.params.responseError !== '' ? 'error' : 'success';
			}

			if ( typeof msg === 'undefined' ) {
				msg = msgType == 'error' ? this.params.responseError : this.params.responseSuccess;
			}

			dialogHelper.displayModal();
			dialogHelper.addContent( msg );

			if ( SPCO.allow_enable_submit_buttons ) {
				SPCO.enable_submit_buttons();
			}
		},


		/**
		 * This differs from showDialog in that it shows in context for the given element id string.
		 */
		showInContext : function( ref, cssType, container ) {
			SPCO.hide_notices();
			SPCO.end_ajax();
			cssType = typeof cssType === 'undefined' ? '#' : cssType;
			container = typeof container === 'undefined' ? SPCO.main_container : container;

			msg = '<div class="highlight-bg important-notice ee-inline-context-notice">' + this.params.responseError + '</div>';
			$(cssType + ref, container).after( msg );
			if ( SPCO.allow_enable_submit_buttons ) {
				SPCO.enable_submit_buttons();
			}
		},


		clearNotices : function() {
			$('.ee-inline-context-notice').remove();
			$('input', SPCO.container).removeClass('ee-needs-value').addClass('ee-has-value');
			//unbind any ajax success handlers that are set to prevent repeat fires.
			$(document).unbind('ajaxSuccess');
		},


		doAjax : function() {
			$.ajax({
				type: 'post',
				url: eei18n.ajax_url,
				data: WPUSPCO.params.ajaxData,
				dataType: "json",

				beforeSend: function() {
					$('#espresso-ajax-long-loading').remove();
					$('#espresso-ajax-loading').attr('z-index', '9999999').show();
				},

				success: function ( response ) {
					WPUSPCO.processResponse( response );
				},

				error: function() {
					SPCO.submit_reg_form_server_error();
				}
			})
		}
	};


	/**
	 * Handler bound to 'spco_process_response' event.
	 *
	 * @param {object} event
	 * @param {string} next_step     Next Step in spco process.
	 * @param {object} SPCO_response )
	 *
	 */
	SPCO.main_container.on( 'spco_process_response', function( event, next_step, SPCO_response ) {
		WPUSPCO.processResponse( SPCO_response );
	});


	/**
	 * Handler bound to 'click' event for capturing login button presses.
	 *
	 * @param {object}  e  the event triggered
	 */
	SPCO.main_container.on( 'click', '.ee-wpuser-login-button', function(e) {
		e.preventDefault();
		e.stopPropagation();
		WPUSPCO.showLoginForm();
	});


	SPCO.main_container.on( 'click', '.js-toggle-followup-notification', function(e) {
		e.preventDefault();
		var $attentionContainer = $('.ee-attention-notification-form');
		if ( $attentionContainer.hasClass( 'hidden' ) ) {
			$attentionContainer.slideToggle().removeClass('hidden');
		} else {
			$attentionContainer.slideToggle().addClass('hidden');
		}
	});

	SPCO.main_container.on( 'click', '.js-submit-notification-followup', function(e){
		e.preventDefault();
		e.stopPropagation();

		$('.js-toggle-followup-notification').trigger('click');

		WPUSPCO.params.ajaxData = {
			action : 'ee_process_user_trouble_notification',
			ee_front_ajax : true,
			contact_email : $('#notification-email-contact').val(),
			reg_url_link : eei18n.e_reg_url_link
		};

		$('.ee-attention-notification-form').remove();
		$('.ee-send-email-info-text').remove();

		WPUSPCO.doAjax();
	});
});
