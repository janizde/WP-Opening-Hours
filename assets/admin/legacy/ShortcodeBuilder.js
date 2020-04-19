(function ($) {
  $.fn.opShortcodeBuilderLink = function () {
    this.each(function (index, element) {
      var $element = $(element);
      var scBuilderUrl = $element.data('shortcode-builder-url');
      $element.click(function (e) {
        e.preventDefault();
        window.open(scBuilderUrl, 'Shortcode Builder', 'width=1024,height=768,status=yes,scrollbars=yes,resizable=yes');
      });
    })
  };

  $(document).ready(function () {
    $('.op-generate-sc-link').opShortcodeBuilderLink();
  });
})(jQuery);
