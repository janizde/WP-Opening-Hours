/**
 * Opening Hours TinyMCE extension
 * for Shortcode builder
 *
 * @author      Jannik Portz <hello@jannikportz.de>
 */

(function ($, tinyMCE, shortcodeBuilders) {
  if (!tinyMCE)
    return;

  /**
   * ShortcodeBuilder constructor
   * @param     editor        The tinyMCE editor
   * @param     shortcodeTag  The shortcode tag
   * @param     name          The shortcode display name
   * @param     fields        Available fields for the ShortcodeBuilder
   * @constructor
   */
  var ShortcodeBuilder = function (editor, shortcodeTag, name, fields) {
    this.shortcodeTag = shortcodeTag;
    this.name = name;
    this.fields = fields;
    this.editor = editor;

    var $this = this;
    this.editor.addCommand(this.shortcodeTag + '_popup', function (ui, args) {
      $this.onCommandPopup(ui, args);
    });
  };

  /**
   * Callback for menu item click in ShortcodeBuilders menu
   */
  ShortcodeBuilder.prototype.handleButtonClick = function () {
    this.editor.execCommand(this.shortcodeTag + '_popup', {});
  };

  /**
   * Callback for ShortcodeBuilder popup
   * @param     ui          The ui object
   * @param     args        Data passed to the popup
   */
  ShortcodeBuilder.prototype.onCommandPopup = function (ui, args) {
    var $this = this;
    this.editor.windowManager.open({
      title: this.name,
      body: this.fields,
      onsubmit: function (e) {
        var shortcode = $this.generateShortcode(e.data);
        console.log(shortcode);
        $this.editor.insertContent(shortcode);
      }
    });
  };

  /**
   * Generates a shortcode string from the specified args
   * @param     {object}    args    Key/value hash containing shortcode args
   * @returns   {string}            Formatted shortcode string
   */
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

  /**
   * Initialize tinyMCE plugin
   * Loads all available ShortcodeBuilders and creates dropdown menu
   */
  tinyMCE.PluginManager.add('op_shortcode_builder', function (editor) {
    var builders = shortcodeBuilders.map(function (scb) {
      return new ShortcodeBuilder(editor, scb.shortcodeTag, scb.shortcodeName, scb.fields);
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