<?php

namespace OpeningHours\Module\Schema;

use OpeningHours\Entity\ChildSetWrapper;
use OpeningHours\Entity\Set;
use OpeningHours\Util\Dates;

class SchemaGenerator {

  /**
   * @var   Set
   */
  protected $mainSet;

  /**
   * @var   ChildSetWrapper[]
   */
  protected $childSets;

  public function __construct(Set $mainSet, array $childSets = array()) {
    $this->mainSet = $mainSet;
    $this->childSets = $childSets;
  }

  /**
   * Creates a sequential array containing the all available validity periods.
   * A set validity period is an associative array containing `set`, `start` and `end`
   * and represents that `set` is the set serving the regular periods during the date interval
   *
   * Gaps between child sets will be filled with validity periods of the parent set.
   * The first item's `start` date is always the current date (or `$referenceNow`).
   * The last item's `end` date is the last child set's `dateEnd`
   * but at least `$referenceNow` plus one year.
   *
   * `set`    contains the `Set` instance that serves the regular opening hours during the validity period
   * `start`  contains a `DateTime` representing the start of the first day the validity period is in effect e.g. `2018-04-01T00:00:00`
   * `end`    contains a `DateTime` representing the start of the last day the validity period is in effect e.g. `2018-04-01T00:00:00`
   *          though the `DateTime`'s time component is always 00:00:00 it represents that the validity period is valid through the end of that day
   *
   * @param       \DateTime       $referenceNow   Reference date time representing the current time
   *                                              Will be the first item's `start` value
   *
   * @return      array[array[string]mixed]       Set validity
   *
   * @throws      \Exception                      If the `interval_spec` of a \DateInterval is invalid
   */
  public function createSetValidityOrder(\DateTime $referenceNow = null) {
    $now = $referenceNow === null ? Dates::getNow() : $referenceNow;
    $now->setTime(0,0,0);
    $maxEnd = clone $now;
    $maxEnd->add(new \DateInterval('P1Y'));

    $childSets = array_filter($this->childSets, function (ChildSetWrapper $child) use ($now) {
      return !$child->isPast($now);
    });

    if (count($childSets) < 1) {
      $end = clone $maxEnd;
      $end->sub(new \DateInterval('P1D'));
      return array(
        array(
          'set' => $this->mainSet,
          'start' => $now,
          'end' => $end,
        ),
      );
    }

    // Create partial for each child set using `start` and `end`
    $setPartials = array_map(function(ChildSetWrapper $child) use ($now) {
      $start = $child->getStart();
      $end = $child->getEnd();

      return array(
        'set' => $child->getSet(),
        'start' => max($start, $now),
        'end' => $end,
      );
    }, $childSets);

    $latestDefault = clone $maxEnd;
    $latestDefault->sub(new \DateInterval('P1D'));
    // Determine latest explicitly set end date or one year in future from the generated child partials
    $latestSetDate = array_reduce($setPartials, function (\DateTime $latest, array $partial) {
      return max($latest, $partial['end']);
    }, $latestDefault);

    // Set the `$latestSetDate` for every partial that does not have an `end` date set
    foreach ($setPartials as &$_partial) {
      if ($_partial['end'] === null) {
        $_partial['end'] = $latestSetDate;
      }
    }

    // Sort `$childSetPartials` according to their start date
    usort($setPartials, function (array $a, array $b) {
      return $a['start']->getTimestamp() - $b['start']->getTimestamp();
    });

    // If the first child set starts in the future add a partial of the parent set
    // starting now and ending before the first child set partial
    if ($setPartials[0]['start'] > $now) {
      /** @var \DateTime $end */
      $end = clone $setPartials[0]['start'];
      $end->sub(new \DateInterval('P1D'));
      array_splice($setPartials, 0, 0, array(
        array(
          'set' => $this->mainSet,
          'start' => $now,
          'end' => $end,
        ),
      ));
    }

    // For each partial check if there is a gap to the next one
    // and if so, insert a partial with the main set at this position
    for ($i = 0; $i < count($setPartials); $i++) {
      $partial = $setPartials[$i];
      $nextStart = $i === count($setPartials) - 1
        ? $maxEnd
        : $setPartials[$i + 1]['start'];

      $endToNextStart = $partial['end']->diff($nextStart);
      if ($endToNextStart->days > 1 && $endToNextStart->invert === 0) {
        $start = clone $partial['end'];
        $start->add(new \DateInterval('P1D'));
        $end = clone $nextStart;
        $end->sub(new \DateInterval('P1D'));
        array_splice($setPartials, $i + 1, 0, array(
          array(
            'set' => $this->mainSet,
            'start' => $start,
            'end' => $end,
          ),
        ));

        // Skip the newly added item
        $i++;
      }
    }

    return $setPartials;
  }
}
