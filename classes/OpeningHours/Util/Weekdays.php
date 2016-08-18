<?php

namespace OpeningHours\Util;

use OpeningHours\Module\AbstractModule;

/**
 * Helper class for dealing with Weekdays
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Util
 */
class Weekdays extends AbstractModule {

  /**
   * Collection of all weekday in the right order
   * @var       Weekday[]
   */
  protected $weekdays;

  /** Sets up all weekday instances */
  protected function __construct () {
    $domain = 'wp-opening-hours';

    $this->weekdays = array(
      new Weekday(0, 'monday', __('Monday', $domain), __('Mon.', $domain)),
      new Weekday(1, 'tuesday', __('Tuesday', $domain), __('Tue.', $domain)),
      new Weekday(2, 'wednesday', __('Wednesday', $domain), __('Wed.', $domain)),
      new Weekday(3, 'thursday', __('Thursday', $domain), __('Thu.', $domain)),
      new Weekday(4, 'friday', __('Friday', $domain), __('Fri.', $domain)),
      new Weekday(5, 'saturday', __('Saturday', $domain), __('Sat.', $domain)),
      new Weekday(6, 'sunday', __('Sunday', $domain), __('Sun.', $domain)),
    );
  }

  /**
   * Returns whole collection of weekdays
   * @return    Weekday[]
   */
  public static function getWeekdays () {
    $i = self::getInstance();
    return $i->weekdays;
  }

  /**
   * Returns only one weekday by its numeric index
   *
   * @param     int $index The numeric weekday index
   *
   * @return    Weekday             The weekday instance
   */
  public static function getWeekday ( $index ) {
    $days = self::getInstance()->getWeekdays();

    if ($index < 0 or $index >= count($days))
      return null;

    return $days[$index];
  }

  /**
   * Returns only one weekday by its slug
   *
   * @param     string $slug The weekday's slug
   *
   * @return    Weekday             The weekday instance
   */
  public static function getWeekdayBySlug ( $slug ) {
    $i = self::getInstance();
    foreach ($i->weekdays as $weekday)
      if ($weekday->getSlug() == $slug)
        return $weekday;

    return null;
  }

  /**
   * Returns an sequential array of weekday captions in the right order
   *
   * @param     bool $short Whether to use short names
   *
   * @return    string[]            Sequential array of weekday captions
   */
  public static function getCaptions ( $short = false ) {
    $captions = array();
    $i = self::getInstance();
    foreach ($i->weekdays as $weekday) {
      $captions[] = $short ? $weekday->getShortName() : $weekday->getName();
    }
    return $captions;
  }

  /**
   * Returns the string representation of the provided days
   *
   * @param     string|int|array $days  The days whose string representation to return.
   *                                    Either one day as numeric representation, a comma separated list of weekdays or
   *                                    an array of weekday numbers
   * @param     bool             $short Whether to use short string representations
   *
   * @return    string                  The string representation for the provided days
   */
  public static function getDaysCaption ( $days, $short = false ) {
    $captions = self::getCaptions($short);

    if (is_numeric($days))
      return $captions[$days];

    if (is_string($days) and strpos($days, ',')) {
      $days = explode(',', $days);
      foreach ($days as &$day) {
        $day = (int)trim($day);
      }
      unset($day);
    }

    if (!is_array($days))
      return '';

    if (count($days) === 1)
      return self::getDaysCaption($days[0]);

    sort($days);
    $days = array_values($days);

    $first_el = $days[0];
    $last_el = $days[count($days) - 1];

    if ($days == range($first_el, $last_el)) {
      $result_format = "%s - %s";
      return sprintf($result_format, $captions[$first_el], $captions[$last_el]);
    }

    $strings = array();
    foreach ($days as $day)
      $strings[] = $captions[$day];

    return implode(', ', $strings);
  }
}