<?php

namespace OpeningHours\Persistence;

use OpeningHours\Core\DayOverride;
use OpeningHours\Core\Holiday;
use OpeningHours\Core\RecurringPeriod;
use OpeningHours\Core\RecurringPeriods;
use OpeningHours\Core\SpecEntry;
use OpeningHours\Core\SpecEntryParser;

/**
 * Class responsible for storing and retrieving opening hours specifications to and from WP_Post metadata
 * @package OpeningHours\Persistence
 */
class PostSpecStore {
  /** Meta key under which the opening hours specification is stores */
  const SPEC_META_KEY = '_op_set_specification';

  /**
   * ID of the post to save the data to or read from
   * @var int
   */
  private $postId;

  public function __construct(int $postId) {
    $this->postId = $postId;
  }

  /**
   * Loads the specification from post meta data, parses the serialized version and returns the root spec entry
   * @return    SpecEntry|null   Root of the specification or null if none exists
   */
  public function loadSpecification() {
    // $serialized = get_post_meta($this->postId, self::SPEC_META_KEY, true);
    $rpChild = new RecurringPeriods(new \DateTime('2020-05-01'), new \DateTime('2020-08-01'), [], []);
    $holiday = new Holiday('Foo Holiday', new \DateTime('2020-03-15'), new \DateTime('2020-04-01'));
    $dayOverride = new DayOverride('Foo Override', new \DateTime('2020-04-15'), []);

    $serialized = (new RecurringPeriods(
      new \DateTime('2020-03-02'),
      new \DateTime('2020-10-01'),
      [new RecurringPeriod('12:00', 6 * 60 * 60, 3)],
      [$rpChild, $holiday, $dayOverride]
    ))->toSerializableArray();

    if (!is_array($serialized)) {
      return null;
    }

    return SpecEntryParser::fromSerializableArray($serialized);
  }

  /**
   * Serializes the passed in $rootEntry and stores it as the specification of a Set in the post meta data
   * @param     SpecEntry   $rootEntry    Root entry of the opening hours specification
   */
  public function storeSpecification(SpecEntry $rootEntry) {
    $serialized = $rootEntry->toSerializableArray();
    update_post_meta($this->postId, self::SPEC_META_KEY, $serialized);
  }
}
