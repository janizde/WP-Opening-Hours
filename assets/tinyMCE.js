/**
 * Opening Hours TinyMCE extension
 * for Shortcode builder
 */

tinyMCECurrentEditor = null;

(function ($, tinyMCE, shortcodeBuilders) {
  if (!tinyMCE)
    return;

  var ShortcodeBuilder = function (editor, url, shortcodeTag, name, fields) {
    this.shortcodeTag = shortcodeTag;
    this.name = name;
    this.fields = fields;
    this.editor = editor;
    this.url = url;
  };

  ShortcodeBuilder.prototype.handleButtonClick = function () {
    var $this = this;
    this.editor.execCommand(this.shortcodeTag + '_popup', function (ui, args) {
      $this.onCommandPopup(ui, args);
    });
  };

  ShortcodeBuilder.prototype.onCommandPopup = function (ui, args) {
    var $this = this;
    console.log('editor', this.editor.selection);
    tinyMCECurrentEditor = $this.editor;
    this.editor.windowManager.open({
      title: 'Opening Hours Shortcode Builder',
      body: this.fields,
      onsubmit: function (e) {
        var shortcode = $this.generateShortcode(e.data);
        console.log(shortcode);
        $this.editor.insertContent(shortcode);
      }
    });
  };

  ShortcodeBuilder.prototype.generateShortcode = function (args) {
    var tag = '[' + this.shortcodeTag;
    for (var key in args) {
      if (!args.hasOwnProperty(key))
        continue;

      var value = args[key];
      if (typeof value === 'string' && value.length < 1)
        continue;

      tag += ' ' + key + '="' + args[key] + '"';
    }
    tag += ']';
    return tag;
  };

  tinyMCE.PluginManager.add('op_shortcode_builder', function (editor, ui) {
    var builders = shortcodeBuilders.map(function (scb) {
      return new ShortcodeBuilder(editor, ui, scb.shortcodeTag, scb.shortcodeName, scb.fields);
    });

    editor.addButton('op_shortcode_builder', {
      type: 'menubutton',
      icon: 'clock',
      menu: builders.map(function (scb) {
        return {
          text: scb.name,
          onclick: function () {
            scb.handleButtonClick();
          }
        };
      })
    });
  });

})(jQuery, tinyMCE, openingHoursShortcodeBuilders);