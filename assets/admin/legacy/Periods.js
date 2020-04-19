(function($) {
  $.fn.opPeriodsDay = function() {
    return this.each(function(index, element) {
      var wrap = $(element);

      var periodContainer = wrap.find(".period-container");
      var tbody = periodContainer.find("tbody");
      var btnAddPeriod = wrap.find("a.add-period");

      function addPeriod() {
        var data = {
          action: "op_render_single_period",
          weekday: periodContainer.attr("data-day"),
          set: periodContainer.attr("data-set")
        };

        $.post(ajax_object.ajax_url, data, function(response) {
          var newPeriod = $(response).clone();
          newPeriod.opSinglePeriod();
          tbody.append(newPeriod);
        });
      }

      btnAddPeriod.click(function() {
        addPeriod();
      });
    });
  };

  $.fn.opSinglePeriod = function() {
    return this.each(function(index, element) {
      var wrap = $(element);

      var btnDeletePeriod = wrap.find(".delete-period");
      var inputs_tp = wrap.find(".input-timepicker");

      btnDeletePeriod.click(function() {
        wrap.remove();
      });

      inputs_tp.timepicker({
        hourText: translations.tp_hour,
        minuteText: translations.tp_minute
      });

      inputs_tp.focus(function() {
        inputs_tp.blur();
      });
    });
  };

  $(document).ready(function() {
    var form = $(".form-opening-hours");
    form.find("tr.periods-day").opPeriodsDay();
    form.find("tr.period").opSinglePeriod();
  });
})(jQuery);
