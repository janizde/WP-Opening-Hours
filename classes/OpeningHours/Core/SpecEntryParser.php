<?php

namespace OpeningHours\Core;

class SpecEntryParser {
  static function fromSerializableArray(array $array): ArraySerializable {
    switch ($array['kind']) {
      case Holiday::SPEC_KIND:
        return Holiday::fromSerializableArray($array);

      case DayOverride::SPEC_KIND:
        return DayOverride::fromSerializableArray($array);

      case RecurringPeriods::SPEC_KIND:
        return RecurringPeriods::fromSerializableArray($array);

      default:
        throw new \InvalidArgumentException(sprintf("Serialized array must have a valid value of 'kind'. Value '%s' not recognized.", $array['kind']));
    }
  }
}
