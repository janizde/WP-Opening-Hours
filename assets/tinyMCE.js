/**
 * Opening Hours TinyMCE extension
 * for Shortcode builder
 */

tinyMCECurrentEditor = null;

(function ($, tinyMCE, shortcodeBuilders) {
  if (!tinyMCE)
    return;

  var ShortcodeBuilder = function (editor, url, shortcodeTag, fields) {
    var $this = this;
    this.pluginName = 'op_shortcode_builder';
    this.shortcodeTag = shortcodeTag;
    this.fields = fields;
    this.editor = editor;
    this.url = url;

    editor.addButton(this.pluginName, {
      icon: 'clock',
      tooltip: 'Opening Hours',
      onclick: function (e) { console.log('event', e);
        editor.execCommand($this.pluginName + '_popup', '', {
          foo: 'bar'
        });
      }
    });

    editor.addCommand(this.pluginName + '_popup', function (ui, args) {
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
    for (var i = 0; i < shortcodeBuilders.length; ++i) {
      new ShortcodeBuilder(editor, ui, shortcodeBuilders[i].shortcodeTag, shortcodeBuilders[i].fields);
    }
  });

})(jQuery, tinyMCE, openingHoursShortcodeBuilders);