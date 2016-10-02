<?php

namespace OpeningHours\Entity;

use DateInterval;
use DateTime;
use InvalidArgumentException;
use OpeningHours\Module\CustomPostType\MetaBox\SetDetails;
use OpeningHours\Module\CustomPostType\Set as SetCpt;
use OpeningHours\Util\ArrayObject;
use OpeningHours\Util\Dates;
use OpeningHours\Util\MetaBoxPersistence;
use OpeningHours\Util\Persistence;
use OpeningHours\Util\Weekday;
use OpeningHours\Util\Weekdays;
use WP_Post;

/**
 * Represents a Set of opening hours
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Entity
 */
class Set {

  /**
   * Constants
   */
  const WP_ACTION_BEFORE_SETUP = 'op_set_before_setup';

  /**
   * Collection of all Periods in the Set
   * @var       ArrayObject
   */
  protected $periods;

  /**
   * Collection of all Holidays in the Set
   * @var       ArrayObject
   */
  protected $holidays;

  /**
   * Collection of all Irregular Openings in the Set
   * @var       ArrayObject
   */
  protected $irregularOpenings;

  /**
   * The Id of the set
   * @var       int
   */
  protected $id;

  /**
   * The WP_Post instance representing the set
   * @var       WP_Post
   */
  protected $post;

  /**
 * The id of the parent set.
 * Id of this set if the set does not have a parent
 *
 * @var       int
 */
  protected $parentId;

  /**
   * The WP_Post instance representing the parent set
   * This Set's post if the set does not have a parent
   *
   * @var       WP_Post
   */
  protected $parentPost;

  /**
   * The set description
   * @var       string
   */
  protected $description;

  /**
   * Persistence object for set details
   * @var       MetaBoxPersistence
   */
  protected $setDetails;

  /**
   * Constructs a new Set with a WP_Post
   *
   * @param     WP_Post|int $post
   *
   * @throws    InvalidArgumentException  If the post is invalid
   */
  public function __construct ( $post ) {
    $this->periods = new ArrayObject();
    $this->holidays = new ArrayObject();
    $this->irregularOpenings = new ArrayObject();
    $this->setDetails = SetDetails::getInstance()->getPersistence();

    $post = get_post($post);

    if (!$post instanceof WP_Post)
      throw new InvalidArgumentException("A set with id $post does not exist.");

    $this->id = $post->ID;
    $this->post = $post;
    $this->parentId = $post->ID;
    $this->parentPost = $post;

    $this->setUp();
  }

  /** Sets up the Set instance */
  public function setUp () {
    $childPosts = get_posts(array(
      'post_type' => SetCpt::CPT_SLUG,
      'post_parent' => $this->getId()
    ));

    foreach ($childPosts as $post) {
      if ($this->postMatchesCriteria($post)) {
        $this->id = $post->ID;
        $this->post = $post;
        break;
      }
    }

    /** Action: op_set_before_setup */
    do_action(self::WP_ACTION_BEFORE_SETUP, $this);

    $persistence = new Persistence($this->post);
    $this->periods = ArrayObject::createFromArray($persistence->loadPeriods());
    $this->holidays = ArrayObject::createFromArray($persistence->loadHolidays());
    $this->irregularOpenings = ArrayObject::createFromArray($persistence->loadIrregularOpenings());

    $setDescription = $this->setDetails->getValue('description', $this->id);
    $parentSetDescription = $this->setDetails->getValue('description', $this->parentId);

    if (!empty($setDescription)) {
      $this->description = $setDescription;
    } elseif (!empty($parentSetDescription)) {
      $this->description = $parentSetDescription;
    }
  }

  /**
   * Checks if the specified post representing a set matches the criteria
   *
   * @param     WP_Post $post The child post
   *
   * @return    bool              Whether the child post matches the criteria
   */
  public function postMatchesCriteria ( WP_Post $post ) {
    $detailDateStart = $this->setDetails->getValue('dateStart', $post->ID);
    $detailDateEnd = $this->setDetails->getValue('dateEnd', $post->ID);
    $detailWeekScheme = $this->setDetails->getValue('weekScheme', $post->ID);

    $detailDateStart = (!empty($detailDateStart)) ? new DateTime($detailDateStart, Dates::getTimezone()) : null;
    $detailDateEnd = (!empty($detailDateEnd)) ? new DateTime($detailDateEnd, Dates::getTimezone()) : null;
    if ($detailDateEnd !== null)
      $detailDateEnd->setTime(23, 59, 59);

    if ($detailDateStart == null && $detailDateEnd == null && ($detailWeekScheme == 'all' || empty($detailWeekScheme)))
      return false;

    $now = Dates::getNow();

    if ($detailDateStart != null && $now < $detailDateStart)
      return false;

    if ($detailDateEnd != null && $now > $detailDateEnd)
      return false;

    $week_number_modulo = (int)$now->format('W') % 2;

    if ($detailWeekScheme == 'even' && $week_number_modulo === 1)
      return false;

    if ($detailWeekScheme == 'odd' && $week_number_modulo === 0)
      return false;

    return true;
  }

  /**
   * Checks if this set is a parent set
   * @return    bool      Whether this set is a parent set
   */
  public function isParent () {
    return $this->id === $this->parentId;
  }

  /**
   * Checks if any holiday in set is currently active
   *
   * @param     DateTime $now Custom time
   *
   * @return    bool                Whether any holiday in the set is currently active
   */
  public function isHolidayActive ( $now = null ) {
    return $this->getActiveHoliday($now) instanceof Holiday;
  }

  /**
   * Returns the first active holiday or null if none is active
   *
   * @param     DateTime $now Custom Time
   *
   * @return    Holiday             The first active Holiday or null if none is active
   */
  public function getActiveHoliday ( DateTime $now = null ) {
    foreach ($this->holidays as $holiday)
      if ($holiday->isActive($now))
        return $holiday;

    return null;
  }

  /**
   * Checks whether any irregular opening is currently active (based on the date)
   *
   * @param     DateTime $now Custom time
   *
   * @return    bool                whether any irregular opening is currently active
   */
  public function isIrregularOpeningActive ( DateTime $now = null ) {
    return $this->getActiveIrregularOpening($now) instanceof IrregularOpening;
  }

  /**
   * Evaluates all aspects determining whether the venue is currently open or not
   *
   * @param     DateTime $now Custom time
   *
   * @return    bool                Whether venue is currently open or not
   */
  public function isOpen ( DateTime $now = null ) {
    if ($this->isHolidayActive($now))
      return false;

    if ($this->isIrregularOpeningActive($now)) {
      $io = $this->getActiveIrregularOpening($now);
      return $io->isOpen($now);
    }

    foreach ($this->periods as $period)
      if ($period->isOpen($now, $this))
        return true;

    return false;
  }

  /**
   * Returns the first open Period after $now
   *
   * @param     DateTime $now The date context for the Periods. default: current datetime
   *
   * @return    Period    The next open period or null if no period has been found
   */
  public function getNextOpenPeriod (DateTime $now = null) {
    /** @var Period[] $periods */
    $periods = new ArrayObject();
    foreach ($this->periods as $period) {
      $periods->append($now !== null ? $period->getCopyInDateContext($now) : clone $period);
    }

    $periods->uasort(array('\OpeningHours\Entity\Period', 'sortStrategy'));

    if (count($periods) < 1 && count($this->irregularOpenings) < 1)
      return null;

    // For each period in future: check if it will actually be open
    foreach ($periods as $period) {
      if ($period->compareToDateTime($now) <= 0)
        continue;

      if ($period->willBeOpen($this))
        return $this->periodOrIrregularOpening($period, $now);
    }

    $interval = new DateInterval('P7D');
    for ($weekOffset = 1; true; $weekOffset++) {
      if ($weekOffset > 52) {
        return null;
      }

      foreach ($periods as $period) {
        $period->getTimeStart()->add($interval);
        $period->getTimeEnd()->add($interval);

        if ($period->willBeOpen($this)) {
          return $this->periodOrIrregularOpening($period, $now);
        }
      }
    }

    return null;
  }

  /**
   * Determines whether an Irregular Opening exists which is in the future but happens before the Period.
   * If so, it will return a Period created from that Irregular Opening, if not, it will pass $period through
   * @param     Period    $period   The period to check
   * @param     DateTime  $now      Custom current time
   * @return    Period
   */
  private function periodOrIrregularOpening (Period $period, DateTime $now = null) {
    if ($now === null)
      $now = Dates::getNow();

    foreach ($this->irregularOpenings as $irregularOpening) {
      $ioStart = $irregularOpening->getTimeStart();
      if ($ioStart >= $now && $ioStart <= $period->getTimeStart())
        return $irregularOpening->createPeriod();
    }

    return $period;
  }

  /**
   * Getter: Periods
   * @return    ArrayObject
   */
  public function getPeriods () {
    return $this->periods;
  }

  /**
   * Returns first active irregular opening on that day
   * Only evaluates the date of $now and not the time
   *
   * @param     DateTime $now Custom time
   *
   * @return    IrregularOpening
   */
  public function getActiveIrregularOpening ( DateTime $now = null ) {
    /** @var IrregularOpening $io */
    foreach ($this->irregularOpenings as $io)
      if ($io->isActiveOnDay($now))
        return $io;

    return null;
  }

  /**
   * Getter: Holidays
   * @return    ArrayObject
   */
  public function getHolidays () {
    return $this->holidays;
  }

  /**
   * Getter: Irregular Openings
   * @return    ArrayObject
   */
  public function getIrregularOpenings () {
    return $this->irregularOpenings;
  }

  /**
   * Getter: Id
   * @return    int
   */
  public function getId () {
    return $this->id;
  }

  /**
   * Setter: Id
   *
   * @param     int $id
   */
  public function setId ( $id ) {
    $this->id = $id;
  }

  /**
   * Getter: Post
   * @return    WP_Post
   */
  public function getPost () {
    return $this->post;
  }

  /**
   * Setter: Post
   *
   * @param     WP_Post $post
   */
  public function setPost ( WP_Post $post ) {
    $this->post = $post;
  }

  /**
   * Getter: Parent Id
   * @return    int
   */
  public function getParentId () {
    return $this->parentId;
  }

  /**
   * Getter: Parent Post
   * @return    WP_Post
   */
  public function getParentPost () {
    return (!$this->hasParent() and !$this->parentPost instanceof WP_Post)
      ? $this->post
      : $this->parentPost;
  }

  /**
   * Getter: Description
   * @return    bool
   */
  public function getDescription () {
    return $this->description;
  }

  /**
   * Getter: Has Parent
   * @return    bool
   */
  public function hasParent () {
    return $this->id !== $this->parentId;
  }
}