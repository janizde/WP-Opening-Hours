<?php
/**
 *  Opening Hours: Module: I18n
 */

if ( class_exists( 'OP_I18n' ) )
  return;

class OP_I18n extends OP_AbstractModule {

  /**
   *  Constants
   */
  const   LANGUAGE_PATH  = '/language/';

  /**
   *  Constructor
   *
   *  @access       public
   */
  public function __construct() {

    $this->registerHookCallbacks();

  }

  /**
   *  Register Hook Callbacks
   *
   *  @access       public
   */
  public function registerHookCallbacks() {

    add_action( 'plugins_loaded',       array( __CLASS__, 'registerTextdomain' ) );

  }

  /**
   *  Register Textdomain
   *
   *  @access       public
   *  @static
   *  @wp_action    plugins_loaded
   */
  public static function registerTextdomain() {

    load_plugin_textdomain( self::TEXTDOMAIN, false, 'wp-opening-hours' . self::LANGUAGE_PATH );

  }
}
?>
