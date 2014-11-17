<?php
/**
 *  Opening Hours: Module: I18n
 */

namespace OpeningHours\Module;

if ( class_exists( 'OpeningHours\Module\I18n' ) )
  return;

class I18n extends AbstractModule {

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

  /**
   *  Get Weekdays Array
   *
   *  @access       public
   *  @static
   *  @return       array
   */
  public static function getWeekdays () {
    return array(
      'monday'      => __( 'Monday', self::TEXTDOMAIN ),
      'tuesday'     => __( 'Tuesday', self::TEXTDOMAIN ),
      'wednesday'   => __( 'Wednesday', self::TEXTDOMAIN ),
      'thursday'    => __( 'Thursday', self::TEXTDOMAIN ),
      'friday'      => __( 'Friday', self::TEXTDOMAIN ),
      'saturday'    => __( 'Saturday', self::TEXTDOMAIN ),
      'sunday'      => __( 'Sunday', self::TEXTDOMAIN )
    );
  }
}
?>
