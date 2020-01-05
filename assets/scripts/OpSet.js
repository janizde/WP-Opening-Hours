(function($) {
  $(document).ready(function() {
    var dateStart = $(".op-criteria-date-start");
    var dateEnd = $(".op-criteria-date-end");

    dateStart.addClass("input-gray");
    dateEnd.addClass("input-gray");

    dateStart.datepicker({
      dateFormat: "yy-mm-dd",
      firstDay: openingHoursData.startOfWeek || 0,
      dayNames: openingHoursData.weekdays.full,
      dayNamesMin: openingHoursData.weekdays.short,
      dayNamesShort: openingHoursData.weekdays.short,
      onClose: function(date) {
        dateEnd.datepicker("option", "minDate", date);
      }
    });

    dateEnd.datepicker({
      dateFormat: "yy-mm-dd",
      firstDay: openingHoursData.startOfWeek || 0,
      dayNames: openingHoursData.weekdays.full,
      dayNamesMin: openingHoursData.weekdays.short,
      dayNamesShort: openingHoursData.weekdays.short,
      onClose: function(date) {
        dateStart.datepicker("option", "maxDate", date);
      }
    });

    dateStart.focus(function() {
      dateStart.blur();
    });

    dateEnd.focus(function() {
      dateEnd.blur();
    });

    $("#op-set-detail-child-set-notice")
      .parents(".field")
      .hide();
  });
})(jQuery);
