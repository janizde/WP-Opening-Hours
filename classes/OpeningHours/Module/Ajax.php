<?php
/**
 *  Opening Hours: Module: Ajax
 */

namespace OpeningHours\Module;

use OpeningHours\OpeningHours as OpeningHoursWrap;
use OpeningHours\Module\OpeningHours;
use OpeningHours\Module\I18n;
use OpeningHours\Entity\Set;
use OpeningHours\Entity\Period;

class Ajax extends AbstractModule {

  /**
   *  Contants
   */
  const   WP_ACTION_PREFIX  = 'wp_ajax_';
  const   JS_AJAX_OBJECT    = 'ajax_object';

  /**
   *  Actions
   *
   *  @access     public
   *  @static
   *  @type       array
   */
  protected static  $actions = array();

  /**
   *  Constructor
   *
   *  @access     public
   */
  public function __construct() {

    self::registerActions();

  }

  /**
   *  Register Actions
   *
   *  @access     public
   *  @static
   */
  public static function registerActions () {

    self::registerAjaxAction( 'op_render_periods_day',    'renderPeriodsDay'    );
    self::registerAjaxAction( 'op_render_single_period',  'renderSinglePeriod'  );

  }

  /**
   *  Action: Render Periods Day
   *
   *  @access     public
   *  @static
   */
  public static function renderPeriodsDay () {

    error_log( 'action' );

    $day    = $_POST[ 'day' ];
    $setId  = $_POST[ 'set' ];

    if ( !is_int( $day ) )
      self::terminate( 'Day is not an integer' );

    if ( !is_int( $setId ) )
      self::terminate( 'SetId is not an integer' );

    $empty  = ( $setId === 0 );

    $set    = OpeningHours::getInstance()->getSet( $setId );

    if ( !$empty and !$set instanceof Set )
      self::terminate( sprintf( 'Set with id %d does not exist', $setId ) );

    echo self::renderTemplate(
      'ajax/op-set-periods-day.php',
      array(
        'day'   => $day,
        'set'   => $set,
        'empty' => $empty
      ),
      'always'
    );

    die();

  }

  /**
   *  Action: Render Single Period
   *
   *  @access     public
   *  @static
   */
  public static function renderSinglePeriod () {

    $weekday    = $_POST[ 'weekday' ];
    $timeStart  = $_POST[ 'timeStart' ];
    $timeEnd    = $_POST[ 'timeEnd' ];

    $config     = array(
      'weekday'   => $weekday
    );

    $config[ 'timeStart' ]  = ( I18n::isValidTime( $timeStart ) ) ? $timeStart  : '00:00';
    $config[ 'timeEnd' ]    = ( I18n::isValidTime( $timeEnd ) )   ? $timeEnd    : '00:00';

    $period = new Period( $config );

    echo self::renderTemplate(
      'ajax/op-set-period.php',
      array(
        'period'  => $period
      ),
      'always'
    );

    die();

  }

  /**
   *  Register Ajax Action
   *
   *  @access     public
   *  @static
   *  @param      string    $hook
   *  @param      string    $method
   */
  public static function registerAjaxAction ( $hook, $method ) {

    // Trigger error and die if Ajax method doesn't exist
    if ( !method_exists( __CLASS__, $method ) )
      self::terminate( sprintf( 'Ajax method %s does not exist', $method ) );

    $callback = array( __CLASS__, $method );

    add_action( self::WP_ACTION_PREFIX . $hook, $callback );

    self::addAction( $hook, $callback );

  }

  /**
   *  Inject Ajax Url
   *  Makes Ajax Url accessible in JS script
   *
   *  @access     public
   *  @static
   *  @param      string    $handle
   */
  public static function injectAjaxUrl ( $handle ) {

    wp_localize_script(
      $handle,
      self::JS_AJAX_OBJECT,
      array(
        'ajax_url'    => admin_url( 'admin-ajax.php' )
      )
    );

  }

  /**
   *  Terminate
   *  Triggers error and dies
   *
   *  @access     protected
   *  @static
   *  @param      string    $message
   */
  protected static function terminate ( $message ) {
    error_log( $message );
    die();
  }

  /**
   *  Setter: Actions
   *
   *  @access     protected
   *  @static
   *  @param      array     $actions
   *  @return     Ajax
   */
  public static function setActions ( array $actions ) {
    self::$actions = $actions;
  }

  /**
   *  Add Action
   *
   *  @access     protected
   *  @static
   *  @param      string    $hook
   *  @param      callable  $callback
   *  @return     Ajax
   */
  protected static function addAction ( $hook, $callback ) {
    self::$actions[ $hook ] = $callback;
  }


}
?>
