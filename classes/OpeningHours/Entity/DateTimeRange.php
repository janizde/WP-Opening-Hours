<?php

namespace OpeningHours\Entity;

interface DateTimeRange {
  /**
   * Returns the Entity's start date and time
   * @return    \DateTime
   */
  public function getStart();

  /**
   * Returns the Entity's end date and time
   * @return    \DateTime
   */
  public function getEnd();

  /**
   * Checks whether the entity is in past
   * @param     \DateTime   $reference    Reference date to check against
   * @return    bool                      Whether the entity is in past as compared to $reference
   */
  public function isPast(\DateTime $reference);
}
