<?php

namespace OpeningHours\Entity;

use OpeningHours\Module\CustomPostType\MetaBox\SetDetails;

/**
 * Wrapper class around a child set containing validity criteria
 * and further children
 *
 * @package OpeningHours\Entity
 */
class ChildSetWrapper implements DateTimeRange {
  /**
   * @var     Set         The Set instance
   */
  protected $set;

  /**
   * The start of the child set range
   * @var     \DateTime|float
   */
  protected $dateStart;

  /**
   * The end of the child set range
   * @var     \DateTime|float
   */
  protected $dateEnd;

  /**
   * The week scheme (even / odd)
   * @var     string|null
   */
  protected $weekScheme;

  /**
   * All children of this child
   * @var     ChildSetWrapper[]
   */
  protected $children;

  /**
   * ChildSetWrapper constructor.
   *
   * @param     Set               $set          Set to be wrapped
   * @param     \DateTime|float   $dateStart    start of child set as \DateTime or -INF
   * @param     \DateTime|float   $dateEnd      end of child set as \DateTime or INF
   * @param     string|null       $weekScheme   week scheme as `even`, `odd` or null
   * @param     ChildSetWrapper[] $children     further children of this set
   */
  public function __construct(Set $set, $dateStart, $dateEnd, $weekScheme = null, $children = array()) {
    $this->set = $set;
    $this->dateStart = $dateStart;
    $this->dateEnd = $dateEnd;
    $this->weekScheme = $weekScheme;
    $this->children = $children;
  }

  /**
   * Creates a new ChildSetWrapper from the specified Set instance.
   * Reads validity criteria from post meta.
   * If passed a Set which does not correspond to a post all validity criteria will be `null`
   *
   * @param     Set       $set
   * @return    ChildSetWrapper
   */
  public static function createFromPostSet(Set $set) {
    $details = SetDetails::getInstance()->getPersistence();
    $weekScheme = $details->getValue('weekScheme', $set->getId());
    $dateStart = $details->getValue('dateStart', $set->getId());
    $dateStart = empty($dateStart) ? -INF : new \DateTime($dateStart);
    $dateEnd = $details->getValue('dateEnd', $set->getId());
    $dateEnd = empty($dateEnd) ? INF : new \DateTime($dateEnd);
    return new ChildSetWrapper($set, $dateStart, $dateEnd, $weekScheme);
  }

  /**
   * Determines whether the child set is in the past and will never become valid again
   * @param     \DateTime     $reference      Reference DateTime representing the current date and time
   * @return    bool                          Whether the child set is fully in the past
   */
  public function isPast(\DateTime $reference) {
    if (!$this->dateEnd instanceof \DateTime) {
      return false;
    }

    return $reference > $this->dateEnd;
  }

  /**
   * @return Set
   */
  public function getSet() {
    return $this->set;
  }

  /**
   * @return \DateTime
   */
  public function getStart() {
    return $this->dateStart;
  }

  /**
   * @return \DateTime
   */
  public function getEnd() {
    return $this->dateEnd;
  }

  /**
   * @return string
   */
  public function getWeekScheme() {
    return $this->weekScheme;
  }

  /**
   * @return ChildSetWrapper[]
   */
  public function getChildren() {
    return $this->children;
  }
}
