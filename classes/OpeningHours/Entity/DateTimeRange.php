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

}
