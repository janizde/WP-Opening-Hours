( function ( $ ) {

  $(document).ready( function () {

    var dateStart = $('#op-set-detail-date-start');
    var dateEnd = $('#op-set-detail-date-end');

    var parent = $('#parent_id');

    if ( parent.val() === "" ) {

      dateStart.parents('.field').hide();
      dateEnd.parents('.field').hide();
      $('#op-set-detail-week-scheme-all').parents('.field').hide();

      return;
    }

    dateStart.addClass('input-gray');
    dateEnd.addClass('input-gray');

    dateStart.datepicker({
      dateFormat: 'yy-mm-dd',
      onClose: function (date) {
        dateEnd.datepicker("option", "minDate", date);
      }
    });

    dateEnd.datepicker({
      dateFormat: 'yy-mm-dd',
      onClose: function (date) {
        dateStart.datepicker("option", "maxDate", date);
      }
    });

    dateStart.focus( function () {
      dateStart.blur();
    } );

    dateEnd.focus( function () {
      dateEnd.blur();
    } );

  });

} )( jQuery );