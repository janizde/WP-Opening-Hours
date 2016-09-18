<?php

namespace OpeningHours\Util;

/**
 * Represents a Weekday
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Util
 */
class Weekday {

  /**
   * The numeric weekday index from 0 (Monday) - 6 (Sunday)
   * @var       int
   */
  protected $index;

  /**
   * The Weekday's name in English and lowercase
   * @var       string
   */
  protected $slug;

  /**
   * The translated full name of the weekday
   * @var       string
   */
  protected $name;

  /**
   * The translated short name of the weekday
   * @var       string
   */
  protected $shortName;

  /**
   * Weekday constructor.
   *
   * @param     int    $index     The numeric weekday index from 0 (Monday) - 6 (Sunday)
   * @param     string $slug      The Weekday's name in English and lowercase
   * @param     string $name      The translated full name of the weekday
   * @param     string $shortName The translated short name of the weekday
   */
  public function __construct ( $index, $slug, $name, $shortName ) {
    $this->index = $index;
    $this->slug = $slug;
    $this->name = $name;
    $this->shortName = $shortName;
  }

  /**
   * Retrieves the relative index aware of the start_of_week setting.
   * E.g. when the start_of_week setting is set to Tuesday it will return 0 for Tuesday, 1 for Wednesday ... 6 for Monday
   * @return    int       The relative index
   */
  public function getRelativeIndex () {
    $startOfWeek = Dates::getStartOfWeek();
    return ($this->index - $startOfWeek + 7) % 7;
  }

  /**
   * Getter: Index
   * @return    int
   */
  public function getIndex () {
    return $this->index;
  }

  /**
   * Getter: Slug
   * @return    string
   */
  public function getSlug () {
    return $this->slug;
  }

  /**
   * Getter: Name
   * @return    string
   */
  public function getName () {
    return $this->name;
  }

  /**
   * Getter: Short Name
   * @return    string
   */
  public function getShortName () {
    return $this->shortName;
  }
}