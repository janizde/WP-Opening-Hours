<?php

namespace OpeningHours\Module;

use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Period;
use OpeningHours\Util\Dates;
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

  const OPTION_KEY_SIDEBARS = 'sidebars_widgets';

  const REGEX_OLD_WIDGET_KEY = '/^widget_op_(.*?)-([0-9]+)$/';

  /**
   * The WP_Post instance representing the new set
   * @var       \WP_Post
   */
  protected $post;

  public function import () {
    $this->importOpeningHours();
    if (!$this->post instanceof \WP_Post)
      return;
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

    $this->post = get_post($postId);
    $persistence = new Persistence($this->post);
    $persistence->savePeriods($periods);
    $persistence->saveHolidays($holidays);
    $persistence->saveIrregularOpenings($irregularOpenings);

    delete_option(self::OPTION_KEY_PERIODS);
    delete_option(self::OPTION_KEY_HOLIDAYS);
    delete_option(self::OPTION_KEY_IRREGULAR_OPENINGS);
    delete_option(self::OPTION_KEY_SETTINGS);

    return $this->post;
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
        try {
          $periods[] = new Period($weekday->getIndex(), $period[0].':'.$period[1], $period[2].':'.$period[3]);
        } catch (\InvalidArgumentException $e) {
          // Ignore invalid periods
        }
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
      if (!is_array($holidayData) || count(array_diff(array('name', 'start', 'end'), array_keys($holidayData))) > 0)
        continue;

      try {
        $holidays[] = new Holiday($holidayData['name'], $this->parseDateString($holidayData['start']), $this->parseDateString($holidayData['end']));
      } catch (\InvalidArgumentException $e) {
        // Ignore invalid holidays
      }
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
      if (!is_array($ioData) || count(array_diff(array('name', 'start', 'end', 'date'), array_keys($ioData))) > 0)
        continue;

      try {
        $irregularOpenings[] = new IrregularOpening($ioData['name'], $this->parseDateString($ioData['date'])->format(Dates::STD_DATE_FORMAT), $ioData['start'], $ioData['end']);
      } catch (\InvalidArgumentException $e) {
        // Ignore invalid irregular openings
      }
    }

    return $irregularOpenings;
  }

  protected function upgradeWidgets () {
    $sidebars = get_option(self::OPTION_KEY_SIDEBARS);

    foreach ($sidebars as $key => &$widgets) {
      if ($key === 'array_version')
        continue;

      foreach ($widgets as $i => &$widgetKey) {
        if (preg_match(self::REGEX_OLD_WIDGET_KEY, $widgetKey, $matches) === false)
          continue;

        $type = $matches[1];
        $id = $matches[2];

        switch ($type) {
          case 'overview':
            $widgetKey = $this->upgradeWidgetOverview($id);
            break;

          case 'status':
            $widgetKey = $this->upgradeWidgetIsOpen($id);
            break;

          case 'holidays':
            $widgetKey = $this->upgradeWidgetHolidays($id);
            break;

          case 'special_openings':
            $widgetKey = $this->upgradeWidgetIrregularOpenings($id);
            break;

          default:
            continue;
        }
      }
    }

    update_option(self::OPTION_KEY_SIDEBARS, $sidebars);
  }

  protected function upgradeWidgetOverview ($id) {
    /**
     * - Load old widget config
     * - Convert to new widget config
     * - Find id for new widget config
     */
  }

  protected function upgradeWidgetIsOpen ($id) {}

  protected function upgradeWidgetHolidays ($id) {}

  protected function upgradeWidgetIrregularOpenings ($id) {}

  /**
   * Parses a date string used in older versions of the Plugin and build a DateTime object
   * @param     string    $dateString     Date string used in old versions
   * @return    \DateTime                 The result DateTime object
   * @throws    \InvalidArgumentException If the $dateString is not in the expected format
   */
  public function parseDateString ($dateString) {
    $elements = preg_split('/\//', $dateString);

    if (count($elements) !== 3)
      throw new \InvalidArgumentException("\$dateString must be in the format MM/dd/yyyy.");

    return new \DateTime(sprintf('%s-%s-%s', $elements[2], $elements[0], $elements[1]));
  }
}