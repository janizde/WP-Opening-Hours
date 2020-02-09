<?php

namespace OpeningHours\Core;

/**
 * Describes a specification entry in the Opening Hours data tree
 * @package OpeningHours\Core
 */
interface SpecEntry {
  /**
   * Returns the kind of specification entry
   * @return    string
   */
  function getKind(): string;

  /**
   * Returns the children of the specification entry
   * @return    SpecEntry[]
   */
  function getChildren(): array;

  /**
   * Returns a ValidityPeriod for a specification entry
   * @return    ValidityPeriod
   */
  function getValidityPeriod(): ValidityPeriod;
}
