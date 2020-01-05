<?php

namespace OpeningHours\Util;

use OpeningHours\Entity\DateTimeRange as DateTimeRangeInterface;

class DateTimeRange {
  /**
   * Sorts an array of DateTimeRange entities by start date
   * @param     $objects    DateTimeRangeInterface[]
   * @param     $removePast bool    Whether to filter out entities that have already ended
   * @param     $now        \DateTime
   * @return    array       Sorted and filtered array
   */
  public static function sortObjects(array $objects, $removePast = false, \DateTime $now = null) {
    if ($removePast) {
      if ($now === null) {
        $now = Dates::getNow();
      }

      $objects = array_filter($objects, function (DateTimeRangeInterface $o) use ($now) {
        return !$o->isPast($now);
      });
    }

    usort($objects, function (DateTimeRangeInterface $a, DateTimeRangeInterface $b) {
      if ($a->getStart() < $b->getStart()) {
        return -1;
      }

      if ($a->getStart() > $b->getStart()) {
        return 1;
      }

      return 0;
    });

    return $objects;
  }
}
