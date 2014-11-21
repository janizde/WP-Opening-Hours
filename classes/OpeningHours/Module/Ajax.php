<?php
/**
 *  Opening Hours: Module: Ajax
 */

namespace OpeningHours\Module;

class Ajax extends AbstractModule {

  /**
   *  Contants
   */
  const   WP_ACTION_PREFIX  = 'wp_ajax_';

  /**
   *  Actions
   *
   *  @access     public
   *  @static
   *  @type       array
   */
  protected static  $action = array();

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



  }

  /**
   *  Register Ajax Action
   *
   *  @access     public
   *  @static
   *  @param      string    $hook
   *  @param      callable  $callback
   */
  public static function registerAjaxAction ( $hook, $callback ) {

    add_action( WP_ACTION_PREFIX . $hook, $callback );

    self::addAction( $hook, $callback );

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
