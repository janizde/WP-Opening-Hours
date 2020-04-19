<?php

namespace OpeningHours\Core;

/**
 * Interface describing an object that can create a serializable associative array representation of itself
 * and a class that offers a static factory method to create a one of these objects from the array representation.
 *
 * @package OpeningHours\Core
 */
interface ArraySerializable {
  /**
   * Creates an representation of a `SpecEntry` as an associative array that can eventually be serialized
   * for persistence. The output must be consumable by `fromSerializableArray`.
   *
   * @return    array     Serializable array representation
   */
  function toSerializableArray(): array;

  /**
   * Creates an instance of a `SpecEntry` by passing it a serializable array representation
   * created by `toSerializableArray`.
   *
   * @static
   * @param     array     $array    Serializable array representation
   * @return    self                Instance of `SpecEntry` implementation
   */
  static function fromSerializableArray(array $array): self;
}
