<?php

namespace OpeningHours\Module;

/**
 * I18n Module
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module
 */
class I18n extends AbstractModule {

  /** The gettext text domain used for plugin translations */
  const TEXTDOMAIN = 'wp-opening-hours';

  /** Path to the language directory */
  const LANGUAGE_PATH = '/language/';

  /** Hook for action that is performed, when the timezone has been loaded */
  const WP_ACTION_TIMEZONE_LOADED = 'op_timezone_loaded';

  /** Constructor */
  public function __construct () {
    $this->registerHookCallbacks();
  }

  /** Registers Hook Callbacks */
  public function registerHookCallbacks () {
    add_action('plugins_loaded', array($this, 'registerTextdomain'));
  }

  /** Registers Plugin Textdomain */
  public function registerTextdomain () {
    global $wp_version;

    load_plugin_textdomain(self::TEXTDOMAIN, false, 'wp-opening-hours' . self::LANGUAGE_PATH);

    // Manually load translation files in wp-content/languages/plugins for WordPress version < 4.6
    if (version_compare($wp_version, "4.6") < 0) {
      $locale = apply_filters('plugin_locale', get_locale(), self::TEXTDOMAIN);
      $path = sprintf("%s/plugins/%s-%s.mo", WP_LANG_DIR, self::TEXTDOMAIN, $locale);
      if (file_exists($path)) {
        load_textdomain(self::TEXTDOMAIN, $path);
      }
    }
  }

  /**
   * Returns an associative array representing the variables for JS translations
   * @return    array     Associative array of translations with:
   *                        key:    string w/ translation key
   *                        value:  string w/ actual translation
   */
  public static function getJavascriptTranslations () {
    return array(
      'tp_hour' => __('Hour', self::TEXTDOMAIN),
      'tp_minute' => __('Minute', self::TEXTDOMAIN)
    );
  }
}