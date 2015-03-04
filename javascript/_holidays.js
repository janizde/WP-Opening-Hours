/**
 * Opening Hours: JS: Backend: Holidays
 */

/** Holidays Meta Box */
jQuery.fn.opHolidays 		= function () {

    var wrap 		= jQuery( this );

    var holidaysWrap	= wrap.find( 'tbody' );
    var addButton		= wrap.find( '.add-holiday' );

    function init () {
        holidaysWrap.find( 'tr.op-holiday').each( function ( index, element ) {
            jQuery( element ).opSingleHoliday();
        } );
    }

    init();

    function add () {

        var data 	= {
            'action'	:	'op_render_single_dummy_holiday'
        };

        jQuery.post( ajax_object.ajax_url, data, function ( response ) {
            var newHoliday 	= jQuery( response).clone();

            newHoliday.opSingleHoliday();

            holidaysWrap.append( newHoliday );
        } );

    }

    addButton.click( function (e) {
        e.preventDefault();

        add();
    } );

};

/** Holiday Item */
jQuery.fn.opSingleHoliday 	= function () {

    var wrap 	= jQuery( this );

    if ( wrap.length > 1 ) {
        wrap.each( function( index, element ) {
            jQuery( element).opSingleHoliday();
        } );

        return;
    }

    var removeButton 	= wrap.find( '.remove-holiday' );
    var inputDateStart	= wrap.find( 'input[name=dateStart]' );
    var inputDateEnd	= wrap.find( 'input[name=dateEnd]' );

    function remove () {
        wrap.remove();
    }

    function syncInputs () {
        inputDateEnd.attr( 'min', inputDateStart.val() );
        inputDateStart.attr( 'max', inputDateEnd.val() );
    }

    removeButton.click( function (e) {
        e.preventDefault();

        remove();
    } );

    inputDateStart.change( function () {
        syncInputs();
    } );

    inputDateEnd.change( function () {
        syncInputs();
    } );

    syncInputs();

};

/**
 * Mapping
 */
jQuery( document ).ready( function () {

    jQuery( '#op-holidays-wrap').opHolidays();

} );