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
    this.placeholderClassName = 'shortcode-' + this.shortcodeTag;

    var $this = this;
    this.editor.addCommand(this.shortcodeTag + '_popup', function (ui, args) {
      $this.onCommandPopup(ui, args);
    });

    this.editor.on('BeforeSetcontent', function (event) {
      event.content = $this.replaceShortcodes(event.content);
    });

    this.editor.on('GetContent', function (event) {
      event.content = $this.restoreShortcodes(event.content);
    });

    this.editor.on('DblClick', function (event) {
      $this.handleDoubleClick(event);
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
   * @param     {object}    ui              The ui object
   * @param     {object}    attributes      The attributes to populate the inputs with
   */
  ShortcodeBuilder.prototype.onCommandPopup = function (ui, attributes) {
    var $this = this;
    this.editor.windowManager.open({
      title: this.name,
      width: Math.min(600, window.innerWidth),
      height: Math.min(500, window.innerHeight),
      body: this.mergeAttributes(attributes),
      classes: 'op-shortcode-builder-window',
      onsubmit: function (e) {
        var shortcode = $this.generateShortcode(e.data);
        $this.editor.insertContent(shortcode);
      }
    });
  };

  /**
   * Merges the attributes into the fields array as values
   * @param     {object}      attributes    Associative attributes object
   * @returns   {Array}                     Array of fields with values set
   */
  ShortcodeBuilder.prototype.mergeAttributes = function (attributes) {
    if (!attributes)
      return this.fields;

    var fields = [];
    for (var i = 0; i < this.fields.length; ++i) {
      var field = this.fields[i];

      if (attributes.hasOwnProperty(field.name) && attributes[field.name] !== undefined) {
        field = $.extend({}, field);
        field[field.type === 'checkbox' ? 'checked' : 'value'] = attributes[field.name];
      }

      fields.push(field);
    }

    return fields;
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
   * Replaces all occurrences of the shortcode with a visual representation
   * @param     {string}  content   The content string with shortcodes
   * @returns   {string}            The content string with replaced shortcodes
   */
  ShortcodeBuilder.prototype.replaceShortcodes = function (content) {
    var $this = this;
    var regex = new RegExp('(\\['+ this.shortcodeTag +'[^\\]]*\\])', 'g');
    return content.replace(regex, function (shortcode) {
      var attributes = $this.parseShortcodeAttributes(shortcode);
      var element = $('<div>');
      element.html('Shortcode: ' + $this.name);
      element.addClass('op-shortcode mceNonEditable');
      element.addClass($this.placeholderClassName);
      element.attr({
        spellcheck: false
      });
      for (var key in attributes) {
        if (!attributes.hasOwnProperty(key))
          continue;

        element.attr('data-' + key, attributes[key]);
      }
      return element.prop('outerHTML');
    });
  };

  /**
   * Replaces shortcode placeholders in content with real shortcodes
   * @param     {string}  content   Content containing placeholder markup
   * @returns   {string}            Content containing shortcodes
   */
  ShortcodeBuilder.prototype.restoreShortcodes = function (content) {
    var $this = this;
    var regex = new RegExp('<div\\s(.*\s)?class="(?:.*?\\s)?'+ this.placeholderClassName +'(?:\\s.*?)?"[^<>]*?>.*?<\/div>', 'g');
    return content.replace(regex, function (element) {
      element = $(element);
      var attributes = $this.extractAttributes(element);
      return $this.generateShortcode(attributes);
    });
  };

  /**
   * Parses a shortcode string to an attributes object
   * @param     {string}  shortcode The shortcode
   * @returns   {object}            key/value hash with shortcode attributes
   */
  ShortcodeBuilder.prototype.parseShortcodeAttributes = function (shortcode) {
    var regex = new RegExp('([a-zA-Z0-9-_]+)=(?:\"([^\"]*)\")', 'g');
    var attributes = {};
    do {
      var result = regex.exec(shortcode);
      if (!result)
        continue;

      attributes[result[1]] = result[2];
    } while (result);
    return attributes;
  };

  /**
   * Extracts shortcode attributes from a placeholder jQuery element
   * @param     {jQuery}  element   jQuery element containing the attributes
   */
  ShortcodeBuilder.prototype.extractAttributes = function (element) {
    var attributes = {};
    for (var i = 0; i < this.fields.length; ++i) {
      var field = this.fields[i];
      var value = element.data(field.name);
      if (value !== undefined)
        attributes[field.name] = element.data(field.name);
    }
    return attributes;
  };

  /**
   * Opens the edit modal if the double click happened on a placeholder
   * @param     {Event}   event     The click event
   */
  ShortcodeBuilder.prototype.handleDoubleClick = function (event) {
    if (event.target.className.indexOf(this.placeholderClassName) < 0)
      return;

    var target = $(event.target);
    var attributes = this.extractAttributes(target);
    this.editor.execCommand(this.shortcodeTag + '_popup', '', attributes);
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
      title: translations.shortcodeBuilderButtonTitle,
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