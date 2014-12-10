/**
 *	Opening Hours: Javascript
 */

/** Set Meta Box */
jQuery.fn.opPeriodsDay 		= function () {

	var wrap 		= jQuery( this );

	if ( wrap.length > 1 ) {
		wrap.each( function( index, element ) {
			jQuery( element ).opPeriodsDay();
		} );

		return;
	}

	var periodContainer = wrap.find( '.period-container' );
	var tbody 					= periodContainer.find( 'tbody' );

	var btnAddPeriod 		= wrap.find( 'a.add-period' );

	function addPeriod () {

		data 	= {
			'action'	: 'op_render_single_period',
			'weekday'	: periodContainer.attr( 'data-day' ),
			'set'			: periodContainer.attr( 'data-set' )
		};

		jQuery.post( ajax_object.ajax_url, data, function ( response ) {
			var newPeriod 	= jQuery( response ).clone();

			newPeriod.opSinglePeriod();

			tbody.append( newPeriod );
		} );

	}

	btnAddPeriod.click( function() {
		addPeriod();
	} );

}

/** Set Meta Box Period */
jQuery.fn.opSinglePeriod 	= function () {

	var wrap 		= jQuery( this );

	if ( wrap.length > 1 ) {
		wrap.each( function( index, element ) {
			jQuery( element ).opSinglePeriod();
		} );

		return;
	}

	var inputTimeStart 	= wrap.find( '.input-time-start' );
	var inputTimeEnd 		= wrap.find( '.input-time-end' );
	var btnDeletePeriod	= wrap.find( '.delete-period' );

	function deletePeriod () {
		wrap.remove();
	}

	btnDeletePeriod.click( function() {
		deletePeriod();
	} );

}

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

}

/**
 *	Mapping
 */
jQuery( document ).ready( function() {
	jQuery( 'tr.periods-day' ).opPeriodsDay();
	jQuery( 'tr.period' ).opSinglePeriod();

	jQuery( '.extended-settings' ).opExtendedSettings();
} );
