<?php
/**
 *  Opening Hours: Entity: Set
 */

if ( class_exists( 'OP_Set' ) )
  return;

class OP_Set {

  /**
   *  Config
   *
   *  @access     protected
   *  @type       array
   */
  protected $config;

  /**
   *  Periods
   *
   *  @access     protected
   *  @type       OP_ArrayObject
   */
  protected $periods = new OP_ArrayObject;

  /**
   *  Constructor
   *
   *  @access     public
   *  @param      array     $config
   *  @return     OP_Set
   */
  public function __construct ( array $config ) {

    if ( $config !== null and count( $config ) ) :
      $this->setConfig( $config );
      $this->setUp();
    endif;

    return $this;

  }

  /**
   *  Set Up
   *
   *  @access     public
   *  @return     OP_Set
   */
  public function setUp () {

    $config   = $this->getConfig();

    if ( !isset( $config['periods'] ) or !count( $config['periods'] ) )
      return $this;

    foreach ( $config['periods'] as $periodConfig ) :
      if ( OP_Period::isValidConfig( $periodConfig ) )
        $this->getPeriods()->addElement( new OP_Period( $periodConfig ) );
    endforeach;

    return $this;

  }

  /**
   *  Getter: Config
   *
   *  @access     public
   *  @return     array
   */
  public function getConfig () {
    return $this->config;
  }

  /**
   *  Setter: Config
   *
   *  @access     protected
   *  @param      array       $config
   *  @return     OP_Set
   */
  protected function setConfig ( array $config ) {
    $this->config = $config;
    return $config;
  }

  /**
   *  Getter: Periods
   *
   *  @access     public
   *  @return     array
   */
  public function getPeriods () {
    return $this->periods;
  }

  /**
   *  Setter: Periods
   *
   *  @access     protected
   *  @param      array     $periods
   *  @return     OP_Set
   */
  protected function setPeriods ( array $periods ) {
    $this->getPeriods()->exchangeArray( $periods );
    return $this;
  }

}
?>
