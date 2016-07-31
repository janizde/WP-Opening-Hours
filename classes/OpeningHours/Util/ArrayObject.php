<?php

namespace OpeningHours\Util;

use ArrayObject as NativeArrayObject;

/**
 * Custom ArrayObject
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Util
 */
class ArrayObject extends NativeArrayObject {

  /**
   * Removes an element from the collection.
   * Compares by identity (===).
   *
   * @param     mixed $element The element to remove
   */
  public function removeElement ( $element ) {
    foreach ($this as $id => $current)
      if ($element === $current)
        $this->offsetUnset($id);
  }

  /**
   * Creates a new ArrayObjects and fills is with the provided data
   *
   * @param     array $data The data to fill the ArrayObject with
   *
   * @return    ArrayObject         The ArrayObject filled with the data
   */
  public static function createFromArray ( array $data ) {
    $ao = new ArrayObject();
    foreach ($data as $item) {
      $ao->append($item);
    }
    return $ao;
  }

}
