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

}
?>
