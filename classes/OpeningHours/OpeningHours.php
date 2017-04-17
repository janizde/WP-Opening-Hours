<?php

namespace OpeningHours;

use OpeningHours\Module as Module;
use OpeningHours\Module\AbstractModule;
use OpeningHours\Module\Widget\AbstractWidget;
use OpeningHours\Util\Dates;
use OpeningHours\Util\Weekdays;

/**
 * Core Module for the Opening Hours Plugin
 *
 * @author      Jannik Portz
 * @package     OpeningHours
 */
class OpeningHours extends AbstractModule {

  const FILTER_USE_FRONT_END_STYLES = 'op_use_front_end_styles';

  /**
   * Collection of all plugin modules
   * @var       AbstractModule[]
   */
  protected $modules;

  /**
   * Collection of all plugin widgets
   * @var       AbstractWidget[]
   */
  protected $widgets;

  /** The plugin version */
  const VERSION = '2.0.5';

  /** The Plugin DB version */
  const DB_VERSION = 2;

  /** The plugin prefix */
  const PREFIX = 'op_';

  /** Constructor for OpeningHours module */
  protected function __construct () {
    $this->registerHookCallbacks();

    $this->modules = array(
      'OpeningHours' => Module\OpeningHours::getInstance(),
      'I18n' => Module\I18n::getInstance(),
      'Ajax' => Module\Ajax::getInstance(),
      'CustomPostType\Set' => Module\CustomPostType\Set::getInstance(),
      'Shortcode\IsOpen' => Module\Shortcode\IsOpen::getInstance(),
      'Shortcode\Overview' => Module\Shortcode\Overview::getInstance(),
      'Shortcode\Holidays' => Module\Shortcode\Holidays::getInstance(),
      'Shortcode\IrregularOpenings' => Module\Shortcode\IrregularOpenings::getInstance()
    );

    $this->widgets = array(
      'OpeningHours\Module\Widget\Overview',
      'OpeningHours\Module\Widget\IsOpen',
      'OpeningHours\Module\Widget\Holidays',
      'OpeningHours\Module\Widget\IrregularOpenings'
    );
  }

  /** Registers callbacks for actions and filters */
  public function registerHookCallbacks () {
    add_action('wp_enqueue_scripts', array($this, 'loadResources'));
    add_action('admin_enqueue_scripts', array($this, 'loadResources'));

    add_action('widgets_init', array($this, 'registerWidgets'));
    add_action('wp_loaded', array($this, 'maybeUpdate'));
  }

  public function maybeUpdate () {
    $dbVersion = get_option('opening_hours_db_version', false);

    if ($dbVersion === false) {
      Module\Importer::getInstance()->import();
      add_option('opening_hours_db_version', self::DB_VERSION);
    } else if ($dbVersion !== self::DB_VERSION) {
      update_option('opening_hours_db_version', self::DB_VERSION);
    }

    $version = get_option('opening_hours_version');
    if ($version === false) {
      add_option('opening_hours_version', self::VERSION);
    } else if ($version !== self::VERSION) {
      update_option('opening_hours_version', self::VERSION);
    }
  }

  /** Registers all plugin widgets */
  public function registerWidgets () {
    foreach ($this->widgets as $widgetClass)
      $widgetClass::registerWidget();
  }

  public function loadResources () {
    wp_register_script(
      self::PREFIX . 'js',
      plugins_url('dist/scripts/main.js', op_bootstrap_file()),
      array('jquery', 'jquery-ui'),
      self::VERSION,
      true
    );

    wp_register_style(
      self::PREFIX . 'css',
      plugins_url('dist/styles/main.css', op_bootstrap_file())
    );

    // Backend Styles and Scripts
    wp_enqueue_script('jquery-ui');


    if (!wp_script_is('jquery-ui')) :
      wp_register_script('jquery-ui', '//code.jquery.com/ui/1.11.4/jquery-ui.min.js', array('jquery'));
      wp_enqueue_script('jquery-ui');
    endif;

    Module\Ajax::injectAjaxUrl(self::PREFIX . 'js');
    wp_localize_script(self::PREFIX . 'js', 'openingHoursData', array(
      'startOfWeek' => (int) Dates::getStartOfWeek(),
      'weekdays' => Weekdays::getDatePickerTranslations(),
      'translations' => array(
        'moreSettings' => __('More Settings', 'wp-opening-hours'),
        'fewerSettings' => __('Fewer Settings', 'wp-opening-hours')
      )
    ));

    $useFrontEndStyles = apply_filters(self::FILTER_USE_FRONT_END_STYLES, true);

    // Frontend Styles and Scripts
    if (is_admin() || $useFrontEndStyles)
      wp_enqueue_style(self::PREFIX . 'css');

    if (is_admin())
      wp_enqueue_script(self::PREFIX . 'js');

    wp_localize_script(self::PREFIX . 'js', 'translations', Module\I18n::getJavascriptTranslations());
  }
}
