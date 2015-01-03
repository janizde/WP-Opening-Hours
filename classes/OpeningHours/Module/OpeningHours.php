<?php
/**
 *  Opening Hours: Module: Opening Hours
 */

namespace OpeningHours\Module;

use OpeningHours\Misc\ArrayObject;
use OpeningHours\Entity\Set as SetEntity;
use OpeningHours\Module\CustomPostType\Set as SetCpt;

use WP_Screen;
use WP_Post;

class OpeningHours extends AbstractModule {

  /**
   *  Sets
   *
   *  @access     protected
   *  @static
   *  @type       ArrayObject
   */
  protected static $sets;

  /**
   *  Current Set Id
   *
   *  @access     protected
   *  @static
   *  @type       int
   */
  protected static $currentSetId;

  /**
   *  Constructor
   *
   *  @access     public
   */
  public function __construct () {

    self::setSets( new ArrayObject );

    self::registerHookCallbacks();

  }

  /**
   *  Register Hook Callbacks
   *
   *  @access     public
   *  @static
   */
  public static function registerHookCallbacks () {

    add_filter( 'detail_fields_metabox_context',    array( __CLASS__, 'modifyDetailFieldContext' ) );

    add_action( 'init',               array( __CLASS__, 'init' ) );
    add_action( 'current_screen',     array( __CLASS__, 'init_admin' ) );


  }

  /**
   * Initializer
   * initializes all parent posts and loads children
   * gets called on every init
   *
   * @access     public
   * @static
   * @wp_action  init
   */
  public static function init () {

    // Get all parent op-set posts
    $posts  = get_posts( array(
      'post_type'     => SetCpt::CPT_SLUG,
      'post_parent'   => 0,
      'numberposts'   => -1
    ) );

    foreach ( $posts as $singlePost ) :
      self::getSets()->offsetSet( $singlePost->ID, new SetEntity( $singlePost ) );
    endforeach;

    self::initCurrentSet();

  }

  /**
   * Initializer Admin
   * Initializes all Set posts for post_type op_set admin screen
   * Overwrites Sets that have been set in init()
   *
   * @access    public
   * @static
   * @wp_action current_screen
   */
  public static function init_admin () {

    $screen   = get_current_screen();

    if ( !$screen instanceof WP_Screen ) :
      trigger_error( sprintf( '%s::%s(): get_current_screen() may be hooked too early. Return value is not an instance of WP_Screen.', __CLASS__, __METHOD__ ) );
      return;
    endif;

    /**
     * Skip if current screen is no op_set post edit screen
     */
    if ( !$screen->base == 'post' or !$screen->post_type == SetCpt::CPT_SLUG )
      return;

    /**
     * Redo Child Set mechanism
     */
    add_action( SetEntity::WP_ACTION_BEFORE_SETUP, function ( SetEntity $set ) {

      $parent_post    = $set->getParentPost();

      $set->setId( $parent_post->ID );
      $set->setPost( $parent_post );

    } );

    /**
     * Load new ArrayObject into sets property
     */
    self::setSets( new ArrayObject );

    $posts    = get_posts( array(
      'post_type'     => SetCpt::CPT_SLUG,
      'numberposts'   => -1
    ) );

    foreach ( $posts as $single_post ) :
      self::getSets()->offsetSet( $single_post->ID, new SetEntity( $single_post ) );
    endforeach;

    self::initCurrentSet();

  }

  /**
   * Init current Set
   * checks global posts and sets current set
   *
   * @access      protected
   * @static
   */
  protected static function initCurrentSet () {

    global $post;

    if ( !$post instanceof WP_Post )
      return;

    if ( self::getSets()->offsetGet( $post->ID ) instanceof SetEntity )
      self::setCurrentSetId( $post->ID );

  }

  /**
   *  Modify Detail Field Context
   *  Forces Detail Fields Meta Box to show up in sidebar
   *
   *  @access     public
   *  @static
   *  @return     string
   */
  public static function modifyDetailFieldContext () {
    return 'side';
  }

  /**
   *  Getter: Sets
   *
   *  @access     public
   *  @static
   *  @return     ArrayObject
   */
  public static function getSets () {
    return self::$sets;
  }

  /**
   * Get Sets Options
   * returns a numeric array with:
   *   key:     int with set id
   *   value:   string with set name
   *
   * @access      public
   * @static
   * @return      array
   */
  public static function getSetsOptions () {

    $sets   = array();

    foreach ( self::getSets() as $set ) :

      $sets[ $set->getId() ]  = $set->getPost()->post_title;

    endforeach;

    return $sets;

  }

  /**
   *  Setter: Sets
   *
   *  @access     protected
   *  @static
   *  @param      ArrayObject     $sets
   */
  public static function setSets ( ArrayObject $sets ) {
    self::$sets   = $sets;
  }

  /**
   *  Getter: Current Set Id
   *
   *  @access     public
   *  @static
   *  @return     int
   */
  public static function getCurrentSetId () {
    return self::$currentSetId;
  }

  /**
   *  Setter: Current Set Id
   *
   *  @access     public
   *  @static
   *  @param      int     $currentSetId
   */
  public static function setCurrentSetId ( $currentSetId ) {
    self::$currentSetId = (int) $currentSetId;
  }

  /**
   *  Getter: Set
   *
   *  @access     public
   *  @static
   *  @param      int     $setId
   *  @return     \OpeningHours\Entity\Set
   */
  public static function getSet ( $setId ) {
    return self::getSets()->offsetGet( $setId );
  }

  /**
   *  Getter: Current Set
   *
   *  @access     public
   *  @static
   *  @return     \OpeningHours\Entity\Set
   */
  public static function getCurrentSet () {
    $setId  = self::getCurrentSetId();
    return  self::getSet( $setId );
  }

}
?>
