<?php
/**
 * Opening Hours: Util: Helpers
 */

namespace OpeningHours\Util;

class Helpers {

  /**
   * Unset Empty
   * unsets all array elements that have an empty array as value
   * useful if you don't want an empty string to overwrite a default value
   *
   * @param     array $array The array whose empty values to unset
   *
   * @return    array               The array without any empty values
   */
  public static function unsetEmptyValues ( array $array ) {
    foreach ($array as $key => $value)
      if (is_string($value) and empty($value))
        unset($array[$key]);

    return $array;
  }

}