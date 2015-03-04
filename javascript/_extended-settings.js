/**
 * Opening Hours: JS: Backend: Extended Settings
 */

/** Extended Settings */
jQuery.fn.opExtendedSettings = function () {

    var wrap 		= jQuery( this );

    if ( wrap.length > 1 ) {
        wrap.each( function ( index, element ) {
            jQuery( element ).opExtendedSettings();
        } );

        return;
    }

    var container 	= wrap.find( '.settings-container' );
    var toggle 			= wrap.find( '.collapse-toggle' );

    toggle.click( function( e ) {
        e.preventDefault();

        container.toggleClass( 'hidden' );
    } );

};

/**
 *	Mapping
 */
jQuery( document ).ready( function() {

    jQuery( '.extended-settings' ).opExtendedSettings();

} );
