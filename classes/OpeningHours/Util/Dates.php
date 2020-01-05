<?php

namespace OpeningHours\Util;

use DateInterval;
use DateTime;
use DateTimeZone;
use OpeningHours\Module\AbstractModule;

/**
 * Helper class for Dates and Time
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Util
 */
class Dates extends AbstractModule {
  /** Standard time format */
  const STD_TIME_FORMAT = 'H:i';

  /** Standard date format */
  const STD_DATE_FORMAT = 'Y-m-d';

  /** Standard date-time format */
  const STD_DATE_TIME_FORMAT = 'Y-m-d H:i';

  /** Regular expression recognizing time in standard time format */
  const STD_TIME_FORMAT_REGEX = '/^([0-9]{1,2}:[0-9]{2})$/';

  /** Regular expression recognizing date in standard date format */
  const STD_DATE_FORMAT_REGEX = '/^([0-9]{4}(-[0-9]{2}){2})$/';

  /**
   * Custom date format
   * @var       string
   */
  protected $dateFormat;

  /**
   * Custom time format
   * @var       string
   */
  protected $timeFormat;

  /**
   * Current timezone
   * @var       DateTimeZone
   */
  protected $timezone;

  /**
   * Index of the day on which the week starts from 0 (Sun) to 6 (Mon)
   * @var       int
   */
  protected $startOfWeek;

  /**
   * Current DateTime
   * @var       DateTime
   */
  protected $now;

  /** Sets up date/time formats, timezone and current date/time */
  protected function __construct() {
    $this->dateFormat = get_option('date_format', self::STD_DATE_FORMAT);
    $this->timeFormat = get_option('time_format', self::STD_TIME_FORMAT);
    $this->startOfWeek = intval(get_option('start_of_week', 0));
    $this->timezone = new DateTimeZone('UTC');
    $this->now = new DateTime(current_time('Y-m-d H:i:s'), $this->timezone);
  }

  /**
   * Checks whether the provided time string is in standard time format
   *
   * @param     string $time The time string to be checked
   *
   * @return    bool                Whether $time is in standard time format or not
   * @todo                          Check for Hour and Minute values
   */
  public static function isValidTime($time) {
    return preg_match(self::STD_TIME_FORMAT_REGEX, $time) === 1;
  }

  /**
   * Merges the date of $date into the $time DateTime instance
   *
   * @param     DateTime $date The date to be merged into time
   * @param     DateTime $time The time to merge the date into
   *
   * @return    DateTime            The $time with the date attributes from $date
   */
  public static function mergeDateIntoTime(DateTime $date, DateTime $time) {
    $time->setDate((int) $date->format('Y'), (int) $date->format('m'), (int) $date->format('d'));

    return $time;
  }

  /**
   * Applies the current time zone to a DateTime object
   *
   * @param     DateTime $dateTime The date whose timezone to set
   *
   * @return    DateTime            $dateTime with the current timezone applied
   */
  public static function applyTimeZone(DateTime $dateTime) {
    return $dateTime->setTimezone(self::getTimezone());
  }

  /**
   * Sets the date of a DateTime object to a specific weekday in the current week
   * It will only increase but never decrease the date
   *
   * @param     DateTime $dateTime The DateTime whose date to update
   * @param     int      $weekday  The numeric representation of the weekday
   * @param     DateTime $now      Custom current DateTime
   *
   * @return    DateTime            $dateTime with updated date attributes
   */
  public static function applyWeekContext(DateTime $dateTime, $weekday, DateTime $now = null) {
    if ($weekday < 0 or $weekday > 6) {
      return $dateTime;
    }

    if ($now == null) {
      $now = self::getNow();
    }

    $today = (int) $now->format('w');
    $offset = ($weekday + 7 - $today) % 7;
    $interval = new DateInterval('P' . $offset . 'D');

    $dateTime->setDate((int) $now->format('Y'), (int) $now->format('m'), (int) $now->format('d'));

    return $dateTime->add($interval);
  }

  /**
   * Compares only the time in hours and minutes of two DateTime objects
   *
   * @param     DateTime $time1 The first DateTime object
   * @param     DateTime $time2 The second DateTime object
   *
   * @return    int                 -1 if $time1 is less than $time2
   *                                0 if $time1 is equal to $time2
   *                                1 if $time1 is greater than $time2
   */
  public static function compareTime(DateTime $time1, DateTime $time2) {
    $time1 = (int) $time1->format('Hi');
    $time2 = (int) $time2->format('Hi');

    if ($time1 < $time2) {
      return -1;
    } elseif ($time1 == $time2) {
      return 0;
    } else {
      return 1;
    }
  }

  /**
   * Compares $date1 and $date2 and determines the difference in seconds.
   * Also if $date1 or $date2 is not finite i.e. either INF or -INF then an infinite difference will be returned.
   * If you can be sure $date1 or $date2 are not `INF` or `-INF` use the overloaded comparison operators for
   * instances of `\DateTime` for better readability.
   *
   * If $date1 is before $date2 a negative value will be returned.
   * If $date1 is after $date2 a positive value will be returned.
   * If $date1 and $date2 are equal 0 will be returned.
   * If $date1 and $date2 are both infinite and equal 0 will be returned.
   *
   * @param       \DateTime|float     $date1      The first date as `DateTime`, `INF` or `-INF`
   * @param       \DateTime|float     $date2      The second date as `DateTime`, `INF` or `-INF`
   * @return      float                           Difference of $date1 and $date2 in seconds (concerning the day)
   *                                              or `INF` / `-INF`
   */
  public static function compareDateTime($date1, $date2) {
    if ($date1 instanceof DateTime) {
      $date1 = $date1->getTimestamp();
    }

    if ($date2 instanceof DateTime) {
      $date2 = $date2->getTimestamp();
    }

    // Manual workaround because INF - INF evaluates to NAN
    if (is_infinite($date1) && is_infinite($date2) && $date1 === $date2) {
      return 0;
    }

    return $date1 - $date2;
  }

  /**
   * Returns $date as a float value.
   * If $date is an instance of `\DateTime` its timestamp will be returned as float.
   * If $date is a float it will be returned.
   * If $date is neither of the above 0 will be returned.
   *
   * This method is particularly useful if $date could either be a `\DateTime`, `INF` or `-INF`
   *
   * @param $date
   * @return float
   */
  public static function getFloatFrom($date) {
    if ($date instanceof DateTime) {
      return (float) $date->getTimestamp();
    }

    return is_float($date) ? $date : 0;
  }

  /**
   * Determines the min value of $a and $b.
   * $a and $b can either be instances of DateTime, -INF or INF
   *
   * @param     DateTime|float    $a
   * @param     DateTime|float    $b
   * @return    DateTime|float
   */
  public static function min($a, $b) {
    $aFloat = self::getFloatFrom($a);
    $bFloat = self::getFloatFrom($b);
    $min = min($aFloat, $bFloat);

    return is_finite($min) ? \DateTime::createFromFormat('U', $min) : $min;
  }

  /**
   * Determines the max value of $a and $b.
   * $a and $b can either be instances of DateTime, -INF or INF
   *
   * @param     DateTime|float    $a
   * @param     DateTime|float    $b
   * @return    DateTime|float
   */
  public static function max($a, $b) {
    $aFloat = self::getFloatFrom($a);
    $bFloat = self::getFloatFrom($b);
    $max = max($aFloat, $bFloat);

    return is_finite($max) ? \DateTime::createFromFormat('U', $max) : $max;
  }

  /**
   * Compares only the date in year, month and day of two DateTime objects
   *
   * @param     DateTime $date1 The first DateTime object
   * @param     DateTime $date2 The second DateTime object
   *
   * @return    int                 -1 if $date1 is less than $date2
   *                                0 if $date1 is equal to $date2
   *                                1 if $date1 is greater than $date2
   */
  public static function compareDate(DateTime $date1, DateTime $date2) {
    $date1 = (int) $date1->format('Ymd');
    $date2 = (int) $date2->format('Ymd');

    if ($date1 < $date2) {
      return -1;
    } elseif ($date1 == $date2) {
      return 0;
    } else {
      return 1;
    }
  }

  /**
   * Returns a new instance of `DateTime` with the same date as `$date`
   * but the time component set to 23:59:59.
   *
   * @param     DateTime    $date
   * @return    DateTime
   */
  public static function endOfDay(DateTime $date) {
    $date = clone $date;
    $date->setTime(23, 59, 59);
    return $date;
  }

  /**
   * Formats a DateTime object to a date string using the date_i18n function to translate months
   * @param     string    $format   The PHP date format
   * @param     DateTime  $date     The DateTime object to format
   * @return    string              The formatted and translated date
   */
  public static function format($format, DateTime $date) {
    return date_i18n($format, (int) $date->format('U'));
  }

  /**
   * Getter: Date Format
   * @return    string
   */
  public static function getDateFormat() {
    return self::getInstance()->dateFormat;
  }

  /**
   * Getter: Time Format
   * @return    string
   */
  public static function getTimeFormat() {
    return self::getInstance()->timeFormat;
  }

  /**
   * Getter: Timezone
   * @return    DateTimeZone
   */
  public static function getTimezone() {
    $timezone = self::getInstance()->timezone;
    return clone $timezone;
  }

  /**
   * Getter: Now
   * @return    DateTime
   */
  public static function getNow() {
    $now = self::getInstance()->now;
    return clone $now;
  }

  /**
   * Getter: Start of Week
   * @return    int
   */
  public static function getStartOfWeek() {
    return self::getInstance()->startOfWeek;
  }

  /**
   * @param string $dateFormat
   */
  public static function setDateFormat($dateFormat) {
    self::getInstance()->dateFormat = $dateFormat;
  }

  /**
   * @param string $timeFormat
   */
  public static function setTimeFormat($timeFormat) {
    self::getInstance()->timeFormat = $timeFormat;
  }

  /**
   * @param DateTimeZone $timezone
   */
  public static function setTimezone($timezone) {
    self::getInstance()->timezone = $timezone;
  }

  /**
   * @param int $startOfWeek
   */
  public static function setStartOfWeek($startOfWeek) {
    self::getInstance()->startOfWeek = $startOfWeek;
  }

  /**
   * @param DateTime $now
   */
  public static function setNow($now) {
    self::getInstance()->now = $now;
  }
}
