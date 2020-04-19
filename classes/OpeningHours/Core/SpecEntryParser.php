<?php

namespace OpeningHours\Core;

class SpecEntryParser {
  static function fromSerializableArray(array $array): SpecEntry {
    /** @var SpecEntry $entry */
    $entry = null;

    switch ($array['kind']) {
      case Holiday::SPEC_KIND:
        $entry = Holiday::fromSerializableArray($array);
        break;

      case DayOverride::SPEC_KIND:
        $entry = DayOverride::fromSerializableArray($array);
        break;

      case RecurringPeriods::SPEC_KIND:
        $entry = RecurringPeriods::fromSerializableArray($array);
        break;

      default:
        throw new \InvalidArgumentException(sprintf("Serialized array must have a valid value of 'kind'. Value '%s' not recognized.", $array['kind']));
    }

    return $entry;
  }
}
