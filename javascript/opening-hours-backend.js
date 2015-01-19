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

};

/** Set Meta Box Period */
jQuery.fn.opSinglePeriod 	= function () {

	var wrap 		= jQuery( this );

	if ( wrap.length > 1 ) {
		wrap.each( function( index, element ) {
			jQuery( element ).opSinglePeriod();
		} );

		return;
	}

	var inputTimeStart 		= wrap.find( '.input-time-start' );
	var inputTimeEnd 		= wrap.find( '.input-time-end' );
	var btnDeletePeriod	= wrap.find( '.delete-period' );

	function deletePeriod () {
		wrap.remove();
	}

	btnDeletePeriod.click( function() {
		deletePeriod();
	} );

};

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

	function remove () {
		wrap.remove();
	}

	removeButton.click( function (e) {
		e.preventDefault();

		remove();
	} );

};

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
	jQuery( 'tr.periods-day' ).opPeriodsDay();
	jQuery( 'tr.period' ).opSinglePeriod();

	jQuery( '#op-holidays-wrap').opHolidays();

	jQuery( '.extended-settings' ).opExtendedSettings();
} );
