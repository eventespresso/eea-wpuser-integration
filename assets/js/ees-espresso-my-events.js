/**
 * Javascript for the ESPRESSO_MY_EVENTS shortcode
 */

jQuery(document).ready( function($) {

    var EEMYEVENTS = {

        data : {
            ee_front_ajax : true,
            action : '',
            ee_mye_page : '',
            per_page : null,
            template : '',
            successCallback : ''
        },


        doPagination: function( page, per_page, template ) {
            this.data.ee_mye_page = typeof page === 'undefined' ? 1 : page;
            this.data.per_page = typeof per_page === 'undefined' ? null : per_page;
            this.data.template = typeof template === 'undefined' ? 'event_section' : template;
            this.data.successCallback = 'replaceTable';
            this.data.action = 'ee_my_events_load_paged_template';
            this.doAjax();
        },



        replaceTable: function( response ) {
            //if no content get out.
            if ( typeof response.content === 'undefined' ) {
                return false;
            }
            $('.espresso-my-events-inner-content').html(response.content);
            return;
        },


        doAjax: function() {
            $('.spinner', '.espresso-my-events').addClass('is-active');
            jQuery.ajax({
                type: "POST",
                url: eei18n.ajax_url,
                data: EEMYEVENTS.data,
                success: function( response, status, xhr ) {
                    var ct = xhr.getResponseHeader("content-type") || "";
                    if (ct.indexOf('html') > -1) {
                        //for now just dumping response to console because this js isn't
                        //really setup to handle (nor expecting) non json responses.  So its likely an error.
                        console.log( response );
                        return;
                    }

                    if (ct.indexOf('json') > -1 ) {
                        if ( typeof EEMYEVENTS[EEMYEVENTS.data.successCallback] === 'function' ) {
                            EEMYEVENTS[EEMYEVENTS.data.successCallback](response);
                        }
                    }
                    $('.spinner', '.espresso-my-events').removeClass('is-active');
                }
            });
        }
    }


    //event listener for pagination events
    $( '.espresso-my-events' ).on( 'click', '.pagination-links > a', function(e) {
        //if required localized object isn't available, just let the click work as normal.
        if ( typeof EE_MYE_JS === 'undefined' ) {
            return true;
        }
        e.preventDefault();
        e.stopPropagation();
        //grab the page being navigated to from the clicked link
        var pageBrowsedTo = parseInt( this.search.replace('?ee_mye_page=','') );
        EEMYEVENTS.doPagination( pageBrowsedTo, EE_MYE_JS.per_page, EE_MYE_JS.template );
    });


    $( '.espresso-my-events').on( 'click', '.js-ee-my-events-toggle-details', function(e) {
        e.stopPropagation();
        var detailsrow = $(this).closest('tr').next();

        //if details row is hidden then we have the wrong row.
        if ( detailsrow.is(":visible") || ! detailsrow.length ) {
            detailsrow = $(this).closest('tr').prev();
        }
        var summaryrow = $(this).closest('tr');
        detailsrow.toggle( 1000 );
        summaryrow.toggle();
    });

});
