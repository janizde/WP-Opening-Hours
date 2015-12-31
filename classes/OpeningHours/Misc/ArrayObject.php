<?php

namespace OpeningHours\Misc;

use ArrayObject as NativeArrayObject;

/**
 * Custom ArrayObject
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Misc
 */
class ArrayObject extends NativeArrayObject {

	/**
	 * Removes an element from the collection.
	 * Compares by identity (===).
	 *
	 * @param     mixed     $element  The element to remove
	 */
	public function removeElement ( $element ) {
		foreach ( $this as $id => $current )
			if ( $element === $current )
				$this->offsetUnset( $id );
	}

	/**
	 * Exchanges old element with new element.
	 *
	 * @param     mixed     $oldElement The old element to be replaced
	 * @param     mixed     $newElement The element that the old element should be replaced with
	 *
	 * @return    bool      Whether old element has been found or not
	 */
	public function exchangeElement( $oldElement, $newElement ) {
		foreach ( $this as $id => $current ) {
			if ( $current === $oldElement ) {
				$this->offsetUnset( $id );
				$this->offsetSet( $id, $newElement );
				return true;
			}
		}
		return false;
	}

}
