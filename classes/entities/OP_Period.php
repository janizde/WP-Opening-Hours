<?php
/**
 *  Opening Hours: Entity: Period
 */

if ( class_exists( 'OP_Period' ) )
  return;

class OP_Period {

  /**
   *  Config
   *
   *  @access     protected
   *  @type       array
   */
  protected $config;

  /**
   *  Constructor
   *
   *  @access     public
   *  @param      array     $config
   *  @return     OP_Period
   */
  public function __construct ( array $config ) {

    if ( $config !== null and count( $config ) )
      $this->setConfig( $config );

    return $this;
  }

  /**
   *  Set Up
   *
   *  @access     public
   *  @return     OP_Period
   */
  public function setUp () {

    $config   = $this->getConfig();

    

  }

  /**
   *  Getter: Config
   *
   *  @access     public
   *  @return     array
   */
  public function getConfig () {
    return $this->config;
  }

  /**
   *  Setter: Config
   *
   *  @access     public
   *  @param      array   $config
   *  @return     OP_Period
   */
  public function setConfig ( array $config ) {
    $this->config = $config;
    return $this;
  }

}
?>
