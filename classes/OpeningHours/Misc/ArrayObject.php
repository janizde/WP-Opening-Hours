<?php
/**
 *  Opening Hours: Misc: Collection
 */

namespace OpeningHours\Misc;

use ArrayObject as PHPArrayObject;

class ArrayObject extends PHPArrayObject {

  /**
   *  Add Element
   *
   *  @access     public
   *  @param      mixed     $element
   *  @return     ArrayObject
   */
  public function addElement ( $element ) {
    $this->append( $element );
  }

  /**
   *  Remove Element
   *
   *  @access     public
   *  @param      mixed     $element
   *  @return     ArrayObject
   */
  public function removeElement ( $element ) {
    foreach ( $this as $id => $walkerElement ) :
      if ( $element === $walkerElement ) :
        $this->offsetUnset( $id );
      endif;
    endforeach;
  }

  /**
   *  Remove Element By Id
   *
   *  @access     public
   *  @param      string|int  $id
   *  @return     ArrayObject
   */
  public function removeElementById ( $id ) {
    if ( $this->offsetExists( $id ) )
      $this->offsetUnset( $id );

    return $this;
  }

  /**
   *  Exchange Element
   *
   *  @access     public
   *  @param      mixed     $oldElement
   *  @param      mixed     $newElement
   *  @return     ArrayObject
   */
  public function exchangeElement ( $oldElement, $newElement ) {
    foreach ( $this as $id => $walkerElement ) :
      if ( $walkerElement === $oldElement ) :
        $this->offsetUnset( $id );
        $this->offsetSet( $id, $newElement );
      endif;
    endforeach;
  }

}
?>
