<?php
/**
 *  Opening Hours: Module: Opening Hours
 */

namespace OpeningHours\Module;

use OpeningHours\Misc\ArrayObject;
use OpeningHours\Entity\Set as SetEntity;
use OpeningHours\Module\CustomPostType\Set as SetCpt;

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

    add_action( 'init',       array( __CLASS__, 'init' ) );

  }

  /**
   *  Initializer
   *  @access     public
   *  @static
   *  @wp_action  init
   */
  public static function init () {

    // Get all parent op-set posts
    $posts  = get_posts( array(
      'post_type'     => SetCpt::CPT_SLUG
    ) );

    // Collect all Post Ids
    $postIds  = array();

    foreach ( $posts as $singlePost ) :
      self::getSets()->offsetSet( $singlePost->ID, new SetEntity( $singlePost ) );
      $postIds[] = $singlePost->ID;
    endforeach;

    global $post;

    // Set current Set to global $post
    if ( $post instanceof WP_Post and in_array( $post->ID, $postIds ) )
      self::setCurrentPostId( $post->ID );

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
   *  Get Sets Options
   *  returns a numeric array with:
   *    key:    int with set id
   *    value:  string with set name
   *
   *  @access     public
   *  @static
   *  @return     array
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
   *  @param      array     $sets
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
   *  @return     OpeningHours\Entity\Set
   */
  public static function getSet ( $setId ) {
    return self::getSets()->offsetGet( $setId );
  }

  /**
   *  Getter: Current Set
   *
   *  @access     public
   *  @static
   *  @return     OpeningHours\Entity\Set
   */
  public static function getCurrentSet () {
    $setId  = self::getCurrentSetId();
    return  self::getSet( $setId );
  }

}
?>
