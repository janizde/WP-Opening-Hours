/**
 * Opening Hours TinyMCE extension
 * for Shortcode builder
 */
(function ($, tinyMCE) {
  if (!tinyMCE)
    return;

  console.log(tinyMCE);
  tinyMCE.PluginManager.add('op_shortcode_builder', function (editor, url) {
    editor.addButton('op_shortcode_builder', {
      icon: 'clock',
      tooltip: 'Opening Hours',
      onclick: function () {
        alert('Hello World');
      }
    });
  });

})(jQuery, tinyMCE);