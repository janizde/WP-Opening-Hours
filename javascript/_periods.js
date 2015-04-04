/**
 * Opening Hours: JS: Backend: Periods
 */

/** Set Meta Box */
jQuery.fn.opPeriodsDay = function () {

  var wrap = jQuery(this);

  if (wrap.length > 1) {
    wrap.each(function (index, element) {
      jQuery(element).opPeriodsDay();
    });

    return;
  }

  var periodContainer = wrap.find('.period-container');
  var tbody = periodContainer.find('tbody');

  var btnAddPeriod = wrap.find('a.add-period');

  function addPeriod() {

    var data = {
      'action': 'op_render_single_period',
      'weekday': periodContainer.attr('data-day'),
      'set': periodContainer.attr('data-set')
    };

    jQuery.post(ajax_object.ajax_url, data, function (response) {
      var newPeriod = jQuery(response).clone();

      newPeriod.opSinglePeriod();

      tbody.append(newPeriod);
    });

  }

  btnAddPeriod.click(function () {
    addPeriod();
  });

};

/** Set Meta Box Period */
jQuery.fn.opSinglePeriod = function () {

  var wrap = jQuery(this);

  if (wrap.length > 1) {
    wrap.each(function (index, element) {
      jQuery(element).opSinglePeriod();
    });

    return;
  }

  var btnDeletePeriod = wrap.find('.delete-period');
  var inputs_tp = wrap.find('.input-timepicker');

  function deletePeriod() {
    wrap.remove();
  }

  btnDeletePeriod.click(function () {
    deletePeriod();
  });

  inputs_tp.timepicker({
    hourText: translations.tp_hour,
    minuteText: translations.tp_minute
  });

  inputs_tp.focus(function () {
    inputs_tp.blur();
  });

};

/**
 *  Mapping
 */
jQuery(document).ready(function () {

  jQuery('tr.periods-day').opPeriodsDay();
  jQuery('tr.period').opSinglePeriod();

});