<?php
/**
 *  Opening Hours: Module: Opening Hours
 */

if ( class_exists( 'OP_OpeningHours' ) )
  return;

class OP_OpeningHours extends OP_AbstractModule {

  /**
   *  Sets
   *
   *  @access     protected
   *  @type       array
   */
  protected $sets   = array();

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

    $this->registerHookCallbacks();

  }

  /**
   *  Register Hook Callbacks
   *
   *  @access     public
   */
  public function registerHookCallbacks () {



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
   *  @return     OP_OpeningHours
   */
  public function setSets ( array $sets ) {
    $this->sets   = $sets;
    return $this
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
   *  @return     OP_OpeningHours
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
   *  @return     OP_Set
   */
  public function getSet ( $setId ) {
    if ( array_key_exists( $setId, $this->getSets() ) )
      return $this->sets[ $setId ];
  }

  /**
   *  Getter: Current Set
   *
   *  @access     public
   *  @return     OP_Set
   */
  public function getCurrentSet () {
    if ( !is_int( $this->getCurrentSetId() ) )
      return $this->getSet( $this->getCurrentSetId() );
  }

}
?>
