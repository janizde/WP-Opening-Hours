/**
 * Opening Hours: JS: Backend: Irregular Openings
 */

/** Irregular Openings Meta Box */
jQuery.fn.opIOs = function () {

  var wrap = jQuery(this);

  var ioWrap = wrap.find('tbody');
  var addButton = jQuery(wrap.find('.add-io'));

  function init() {
    ioWrap.find('tr.op-irregular-opening').each(function (index, element) {
      jQuery(element).opSingleIO();
    });
  }

  init();

  function add() {

    var data = {
      'action': 'op_render_single_dummy_irregular_opening'
    };

    jQuery.post(ajax_object.ajax_url, data, function (response) {
      var newIO = jQuery(response).clone();

      newIO.opSingleIO();

      ioWrap.append(newIO);
    });

  }

  addButton.click(function (e) {
    e.preventDefault();

    add();
  });

};

/** Irregular Opening Item */
jQuery.fn.opSingleIO = function () {

  var wrap = jQuery(this);

  if (wrap.length > 1) {
    wrap.each(function (index, element) {
      jQuery(element).opSingleIO();
    });

    return;
  }

  var removeButton = wrap.find('.remove-io');

  var inputDate = wrap.find('input.date');
  var inputsTime = wrap.find('input.input-timepicker');

  inputsTime.timepicker({
    hourText: translations.tp_hour,
    minuteText: translations.tp_minute
  });

  inputsTime.focus(function () {
    inputsTime.blur();
  });

  inputDate.datepicker({
    dateFormat: 'yy-mm-dd'
  });

  inputDate.focus( function() {
    inputDate.blur();
  } );

  function remove() {
    wrap.remove();
  }

  removeButton.click(function (e) {
    e.preventDefault();

    remove();
  });

};

/**
 * Mapping
 */
jQuery(document).ready(function () {

  jQuery('#op-irregular-openings-wrap').opIOs();

});