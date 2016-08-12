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
  const TEXTDOMAIN = 'opening-hours';

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
    load_plugin_textdomain(self::TEXTDOMAIN, false, 'wp-opening-hours' . self::LANGUAGE_PATH);
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