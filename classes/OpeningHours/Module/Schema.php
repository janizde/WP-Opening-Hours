<?php

namespace OpeningHours\Module;

use OpeningHours\Entity\ChildSetWrapper;
use OpeningHours\Entity\Set;

class Schema {

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
   * Creates an array containing the all available validity periods
   *
   * @return      array       Set validity
   */
  public function createSetValidityOrder() {
    $now = new \DateTime();
    $maxEnd = clone $now;
    $maxEnd->add(new \DateInterval('P1Y'));

    if (count($this->childSets) < 1) {
      return array(
        array(
          'set' => $this->mainSet,
          'start' => $now,
          'end' => $maxEnd,
        ),
      );
    }

    // Create partial for each child set using `start` and `end`
    $childSetPartials = array_map(function(ChildSetWrapper $child) use ($now) {
      $start = $child->getStart();
      $end = $child->getEnd();

      return array(
        'set' => $child->getSet(),
        'start' => $start === null ? $now : $start,
        'end' => $end,
      );
    }, $this->childSets);

    // Determine latest explicitly set end date or one year in future from the generated child partials
    $latestSetDate = array_reduce($childSetPartials, function (\DateTime $latest, array $partial) {
      return max($latest, $partial['end']);
    }, $maxEnd);

    // Set the `$latestSetDate` for every partial that does not have an `end` date set
    foreach ($childSetPartials as &$partial) {
      if ($partial['end'] === null) {
        $partial['end'] = $latestSetDate;
      }
    }

    // Sort `$childSetPartials` according to their start date
    usort($childSetPartials, function (array $a, array $b) {
      return $a['start']->getTimestamp() - $b['start']->getTimestamp();
    });

    for ($i = 0; $i < count($childSetPartials); ++$i) {
      if ($i === 0) {
        if ()
      }
    }
  }
}
