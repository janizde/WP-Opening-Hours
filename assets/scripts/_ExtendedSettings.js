(function ($) {
  $.fn.opExtendedSettings = function () {
    return this.each(function () {
      var wrap = $(this);
      var container = wrap.find('.settings-container');
      var toggle = wrap.find('.collapse-toggle');

      var hidden = container.hasClass('hidden');

      toggle.click(function () {
        hidden = !hidden;
        if (hidden) {
          container.addClass('hidden');
          toggle.html(openingHoursData.translations.moreSettings);
        } else {
          container.removeClass('hidden');
          toggle.html(openingHoursData.translations.fewerSettings);
        }
      });
    });
  };

  $(document).ready(function () {
    $('.extended-settings').opExtendedSettings();
    $(document).on('widget-updated widget-added', function (e, widget) {
      $(widget).find('.extended-settings').opExtendedSettings();
    });
  });
})(jQuery);