<?php

namespace OpeningHours\Entity;

use OpeningHours\Module\CustomPostType\MetaBox\SetDetails;
use OpeningHours\Util\Dates;

/**
 * Wrapper class around a child set containing validity criteria
 *
 * @package OpeningHours\Entity
 */
class ChildSetWrapper implements DateTimeRange {

  /**
   * @var     Set         The Set instance
   */
  protected $set;

  /**
   * @var     \DateTime | null   The start of the child set range
   */
  protected $dateStart;

  /**
   * @var     \DateTime | null   The end of the child set range
   */
  protected $dateEnd;

  /**
   * @var     string | null     The week scheme (even / odd)
   */
  protected $weekScheme;

  public function __construct(Set $set, \DateTime $dateStart = null, \DateTime $dateEnd = null, $weekScheme = null) {
    $this->set = $set;
    $this->dateStart = $set;
    $this->dateEnd = $set;
    $this->weekScheme = $weekScheme;
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
    $dateStart = empty($dateStart) ? null : new \DateTime($dateStart);
    $dateEnd = $details->getValue('dateEnd', $set->getId());
    $dateEnd = empty($dateEnd) ? null : Dates::endOfDay(new \DateTime($dateEnd));
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
   * @return Set
   */
  public function getStart() {
    return $this->dateStart;
  }

  /**
   * @return Set
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
}
