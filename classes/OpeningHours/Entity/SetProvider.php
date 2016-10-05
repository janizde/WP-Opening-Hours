<?php

namespace OpeningHours\Entity;

/**
 * Abstraction for a factory for Sets.
 * A SetProvider may provide no, one or many Sets.
 *
 * @package OpeningHours\Entity
 */
abstract class SetProvider {

  /**
   * Cached version of Set Info
   * @var       array
   */
  protected $setInfo;

  /**
   * Creates a new Set for the specified Set Id
   * @param     string|int  $id     The id of the Set to create
   * @return    Set                 The newly created Set
   */
  abstract public function createSet ($id);

  /**
   * Returns the ids and names of all available Sets for this SetProvider.
   * @return    array     Array of set info. Each element is an array consisting of:
   *                        id:   scalar with set id
   *                        name: string with set name
   */
  abstract protected function createAvailableSetInfo ();

  /**
   * Returns the cached Set Info or creates it if it has not already been created
   * @return    array     Array of set info. Each element is an array consiting of:
   *                        id:   scalar with set id
   *                        name: string with set name
   */
  public function getAvailableSetInfo () {
    if (is_array($this->setInfo))
      return $this->setInfo;

    $this->setInfo = $this->createAvailableSetInfo();
    return $this->setInfo;
  }
}