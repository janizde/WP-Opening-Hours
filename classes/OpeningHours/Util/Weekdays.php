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
    $this->weekdays = array(
      new Weekday(0, 'sunday', __('Sunday'), __('Sun') . '.'),
      new Weekday(1, 'monday', __('Monday'), __('Mon') . '.'),
      new Weekday(2, 'tuesday', __('Tuesday'), __('Tue') . '.'),
      new Weekday(3, 'wednesday', __('Wednesday'), __('Wed') . '.'),
      new Weekday(4, 'thursday', __('Thursday'), __('Thu') . '.'),
      new Weekday(5, 'friday', __('Friday'), __('Fri') . '.'),
      new Weekday(6, 'saturday', __('Saturday'), __('Sat') . '.')
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
   * Returns a string containing the names of the weekdays.
   * If all weekdays are in sequence it will return a from-to string
   * @param     Weekday[]   $days     The weekdays for which to generate the name string
   * @param     bool        $short    Whether to use short weekday names
   * @return    string                Caption for the specified weekdays
   */
  public static function getDaysCaption (array $days, $short = false) {
    if (count($days) == 1)
      return $short ? $days[0]->getShortName() : $days[0]->getName();

    $sequence = true;
    for ($i = 1; $i < count($days); ++$i) {
      if ($days[$i-1]->getRelativeIndex() !== $days[$i]->getRelativeIndex()-1) {
        $sequence = false;
        break;
      }
    }

    if ($sequence) {
      $format = "%s - %s";
      $last = $days[count($days)-1];
      if ($short) {
        return sprintf($format, $days[0]->getShortName(), $last->getShortName());
      } else {
        return sprintf($format, $days[0]->getName(), $last->getName());
      }
    } else {
      $names = array_map(function (Weekday $w) use ($short) {
        return $short ? $w->getShortName() : $w->getName();
      }, $days);
      return implode(', ', $names);
    }
  }

  /**
   * Returns a two-dimensional array containing day translations in the right format for jQuery UI datePicker
   * @return    array     Associative array with:
   *                        'full': Array with full day names starting from Sunday
   *                        'short': Array with short day names starting from Sunday without dot
   */
  public static function getDatePickerTranslations () {
    $weekdays = self::getInstance()->weekdays;
    $full = array_map(function ( Weekday $d ) {
      return $d->getName();
    }, $weekdays);

    $short = array_map(function (Weekday $d) {
      return trim($d->getShortName(), '[\s\.]');
    }, $weekdays);

    return array(
      'full' => $full,
      'short' => $short
    );
  }

  /**
   * Returns all Weekdays in order according to startOfWeek
   * @return    Weekday[]
   */
  public static function getWeekdaysInOrder () {
    $instance = self::getInstance();
    $days = array();
    $start = Dates::getStartOfWeek();
    for ($i = 0; $i < 7; ++$i) {
      $days[] = $instance->weekdays[($i+$start) % 7];
    }
    return $days;
  }


  /**
   * Checks whether any of the provided weekdays represents today's weekday
   * @param     Weekday[] $weekdays The weekdays to check
   * @return    bool                Whether $weekdays contains any day that represents today's weekday
   */
  public static function containsToday (array $weekdays) {
    $today = intval(Dates::getNow()->format('w'));
    foreach ($weekdays as $day) {
      if ($day->getIndex() === $today)
        return true;
    }
    return false;
  }
}