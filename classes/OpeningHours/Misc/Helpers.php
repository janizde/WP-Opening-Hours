<?php
/**
 * Opening Hours: Misc: Helpers
 */

namespace OpeningHours\Misc;

class Helpers {

	/**
	 * Unset Empty
	 * unsets all array elements that have an empty array as value
	 * useful if you don't want an empty string to overwrite a default value
	 *
	 * @access      public
	 * @static
	 *
	 * @param       array $array
	 *
	 * @return      array
	 */
	public static function unsetEmptyValues( array $array ) {

		foreach ( $array as $key => $value ) :

			if ( is_string( $value ) and empty( $value ) ) {
				unset( $array[ $key ] );
			}

		endforeach;

		return $array;

	}

}