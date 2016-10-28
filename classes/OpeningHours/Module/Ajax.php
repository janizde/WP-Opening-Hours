<?php

namespace OpeningHours\Module;

use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Period;
use OpeningHours\Module\CustomPostType\MetaBox\Holidays;
use OpeningHours\Module\CustomPostType\MetaBox\IrregularOpenings;
use OpeningHours\Module\CustomPostType\MetaBox\OpeningHours as OpeningHoursMetaBox;
use OpeningHours\Util\Dates;
use OpeningHours\Util\ViewRenderer;

/**
 * Ajax module
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module
 */
class Ajax extends AbstractModule {

  /** The action hook prefix for ajax callbacks */
  const WP_ACTION_PREFIX = 'wp_ajax_';

  /** The name of the ajax variable for scripts */
  const JS_AJAX_OBJECT = 'ajax_object';

  /**
   * Collection of all ajax actions
   * @var       array
   */
  protected static $actions = array();

  /** Module Constructor */
  public function __construct () {
    self::registerActions();
  }

  /** Registers AJAX actions */
  public static function registerActions () {
    self::registerAjaxAction('op_render_single_period', 'renderSinglePeriod');
    self::registerAjaxAction('op_render_single_dummy_holiday', 'renderSingleDummyHoliday');
    self::registerAjaxAction('op_render_single_dummy_irregular_opening', 'renderSingleDummyIrregularOpening');
  }

  /** Action: Render Single Period */
  public static function renderSinglePeriod () {
    $weekday = (int)$_POST['weekday'];
    $timeStart = $_POST['timeStart'];
    $timeEnd = $_POST['timeEnd'];
    $config = array(
      'weekday' => $weekday
    );

    $config['timeStart'] = (Dates::isValidTime($timeStart)) ? $timeStart : '00:00';
    $config['timeEnd'] = (Dates::isValidTime($timeEnd)) ? $timeEnd : '00:00';
    $period = new Period($config['weekday'], $config['timeStart'], $config['timeEnd']);

    $vr = new ViewRenderer(op_view_path(OpeningHoursMetaBox::TEMPLATE_PATH_SINGLE), array(
      'period' => $period
    ));

    $vr->render();

    die();
  }

  /** Action: Render Single Dummy Holiday */
  public static function renderSingleDummyHoliday () {
    $holiday = Holiday::createDummyPeriod();
    Holidays::getInstance()->renderSingleHoliday($holiday);
    die();
  }

  /** Action: Render Single Dummy Irregular Opening */
  public static function renderSingleDummyIrregularOpening () {
    $view = new ViewRenderer(op_view_path(IrregularOpenings::TEMPLATE_PATH_SINGLE), array(
      'io' => IrregularOpening::createDummy()
    ));

    $view->render();
    die();
  }

  /**
   * Registers an AJAX action
   *
   * @param     string $hook   The name for the ajax hook without the WordPress specific prefix
   * @param     string $method The name of the method
   */
  public static function registerAjaxAction ( $hook, $method ) {
    if (!method_exists(__CLASS__, $method))
      self::terminate(sprintf('Ajax method %s does not exist', $method));

    $callback = array(__CLASS__, $method);
    add_action(self::WP_ACTION_PREFIX . $hook, $callback);
    self::addAction($hook, $callback);
  }

  /**
   * Registers the ajax object for JS
   *
   * @param     string $handle The script handle
   */
  public static function injectAjaxUrl ( $handle ) {
    wp_localize_script($handle, self::JS_AJAX_OBJECT, array(
      'ajax_url' => admin_url('admin-ajax.php')
    ));
  }

  /**
   * Triggers an error and dies
   *
   * @param     string $message The message to log to the console
   */
  protected static function terminate ( $message ) {
    error_log($message);
    die();
  }

  /**
   * Adds an action to the collection
   *
   * @param     string   $hook     The ajax callback hook without the WordPress specific prefix
   * @param     callable $callback The ajax callback to run
   */
  protected static function addAction ( $hook, $callback ) {
    self::$actions[$hook] = $callback;
  }
}
