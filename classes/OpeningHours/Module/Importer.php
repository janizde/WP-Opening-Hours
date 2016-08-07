<?php

namespace OpeningHours\Module;

use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Period;
use OpeningHours\Util\Persistence;
use OpeningHours\Util\Weekdays;
use OpeningHours\Module\CustomPostType\Set as SetCPT;

/**
 * Module importing data from an older version of the Plugin
 *
 * @author  Jannik Portz
 * @package OpeningHours\Module
 */
class Importer extends AbstractModule {

  /** Option keys that old versions of the plugin used */
  const OPTION_KEY_PERIODS = 'wp_opening_hours';
  const OPTION_KEY_HOLIDAYS = 'wp_opening_hours_holidays';
  const OPTION_KEY_IRREGULAR_OPENINGS = 'wp_opening_hours_special_openings';
  const OPTION_KEY_SETTINGS = 'wp_opening_hours_settings';

  public function import () {
    $this->importOpeningHours();
    // update / change widgets
  }

  /**
   * Imports data from older versions of the Plugin to a new Set
   * @return    \WP_Post  Post representing the new Set or null if no set has been created
   */
  protected function importOpeningHours () {
    $periods = $this->getOldPeriods();
    $holidays = $this->getOldHolidays();
    $irregularOpenings = $this->getOldIrregularOpenings();

    if (count($periods) + count($holidays) + count($irregularOpenings) < 1)
      return null;


    $postId = wp_insert_post(array(
      'post_type' => SetCPT::CPT_SLUG,
      'post_title' => __('Opening Hours', I18n::TEXTDOMAIN)
    ));

    $post = get_post($postId);
    $persistence = new Persistence($post);
    $persistence->savePeriods($periods);
    $persistence->saveHolidays($holidays);
    $persistence->saveIrregularOpenings($irregularOpenings);

    delete_option(self::OPTION_KEY_PERIODS);
    delete_option(self::OPTION_KEY_HOLIDAYS);
    delete_option(self::OPTION_KEY_IRREGULAR_OPENINGS);
    delete_option(self::OPTION_KEY_SETTINGS);

    return $post;
  }

  /**
   * Loads Periods from old versions of the Plugin
   * @return    Period[]  Old periods
   */
  protected function getOldPeriods () {
    $meta = json_decode(get_option(self::OPTION_KEY_PERIODS), true);
    if (!is_array($meta) || count($meta) < 1)
      return array();

    $periods = array();
    foreach ($meta as $weekday => $values) {
      if (!array_key_exists('times', $values))
        continue;

      $times = $values['times'];
      $weekday = Weekdays::getWeekdayBySlug($weekday);
      if ($weekday == null)
        continue;

      foreach ($times as $period) {
        $periods[] = new Period($weekday->getIndex(), $period[0].':'.$period[1], $period[2].':'.$period[3]);
      }
    }

    return $periods;
  }

  /**
   * Loads Holidays from old versions of the Plugin
   * @return    Holiday[] Old Holidays
   */
  protected function getOldHolidays () {
    $meta = json_decode(get_option(self::OPTION_KEY_HOLIDAYS), true);
    if (!is_array($meta) || count($meta) < 1)
      return array();

    $holidays = array();

    foreach ($meta as $holidayData) {
      $holidays[] = new Holiday($holidayData['name'], $this->parseDateString($holidayData['start']), $this->parseDateString($holidayData['end']));
    }

    return $holidays;
  }

  /**
   * Loads Irregular Openings from old versions of the Plugin
   * @return    IrregularOpening[]    Old Irregular Openings
   */
  protected function getOldIrregularOpenings () {
    $meta = json_decode(get_option(self::OPTION_KEY_IRREGULAR_OPENINGS), true);
    if (!is_array($meta) || count($meta) < 1)
      return array();

    $irregularOpenings = array();
    foreach ($meta as $ioData) {
      $irregularOpenings[] = new IrregularOpening($ioData['name'], $this->parseDateString($ioData['date']), $ioData['start'], $ioData['end']);
    }

    return $irregularOpenings;
  }

  protected function upgradeWidgets ($setId) {
    /**
     * - Load old widget data
     * - Foreach old-widget new-widget combination:
     *  - Update widget
     */
  }

  protected function upgradeWidgetOverview ($setId) {}

  /**
   * Parses a date string used in older versions of the Plugin and build a DateTime object
   * @param     string    $dateString   Date string used in old versions
   * @return    \DateTime               The result DateTime object
   */
  public function parseDateString ($dateString) {
    $elements = preg_split('/(\\\/|\/)/', $dateString);
    return new \DateTime(sprintf('%s-%s-%s', $elements[2], $elements[0], $elements[1]));
  }
}