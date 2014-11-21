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
   *  @type       ArrayObject
   */
  protected $sets;

  /**
   *  Current Set Id
   *
   *  @access     protected
   *  @type       int
   */
  protected $currentSetId;

  /**
   *  Constructor
   *
   *  @access     public
   */
  public function __construct () {

    $this->setSets( new ArrayCollection );

    $this->registerHookCallbacks();

  }

  /**
   *  Register Hook Callbacks
   *
   *  @access     public
   */
  public function registerHookCallbacks () {

    add_filter( 'detail_fields_metabox_context',    array( __CLASS__, 'modifyDetailFieldContext' ) );

  }

  /**
   *  Set Up
   *
   *  @access     public
   *  @wp_action  init
   */
  public function setUp () {

    // Get all parent op-set posts
    $posts  = get_posts( array(
      'post_type'     => SetCpt::CPT_SLUG,
      'post_parent'   => 0
    ) );

    foreach ( $posts as $post ) :
      $this->getSets()->addElement( new SetEntity( $post ) );
    endforeach;

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
   *  @return     array
   */
  public function getSets () {
    return $this->sets;
  }

  /**
   *  Setter: Sets
   *
   *  @access     protected
   *  @param      array     $sets
   *  @return     ArrayObject
   */
  public function setSets ( ArrayObject $sets ) {
    $this->sets   = $sets;
    return $this;
  }

  /**
   *  Getter: Current Set Id
   *
   *  @access     public
   *  @return     int
   */
  public function getCurrentSetId () {
    return $this->currentSetId;
  }

  /**
   *  Setter: Current Set Id
   *
   *  @access     public
   *  @param      int     $currentSetId
   *  @return     OpeningHours
   */
  public function setCurrentSetId ( $currentSetId ) {
    $this->currentSetId = $currentSetId;
    return $this;
  }

  /**
   *  Getter: Set
   *
   *  @access     public
   *  @param      int     $setId
   *  @return     OpeningHours\Entity\Set
   */
  public function getSet ( $setId ) {
    return $this->getSets()->offsetGet( $setId );
  }

  /**
   *  Getter: Current Set
   *
   *  @access     public
   *  @return     OpeningHours\Entity\Set
   */
  public function getCurrentSet () {
    if ( is_int( $this->getCurrentSetId() ) )
      return $this->getSets()->offsetGet( $this->getCurrentSedId() );
  }

}
?>
