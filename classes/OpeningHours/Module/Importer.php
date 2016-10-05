<?php

namespace OpeningHours\Module;

use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Period;
use OpeningHours\Module\CustomPostType\Set as SetCPT;
use OpeningHours\Util\Dates;
use OpeningHours\Util\Persistence;
use OpeningHours\Util\Weekdays;

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

  protected static $widgetMap = array(
    'overview' => array(
      'oldId' => 'widget_widget_op_overview',
      'newId' => 'widget_widget_op_overview',
      'attributeMap' => array(
        'title' => 'title',
        'caption-closed' => 'caption_closed',
        'show-closed' => 'show_closed_days',
        'highlight' => 'highlight'
      )
    ),
    'status' => array(
      'oldId' => 'widget_widget_op_status',
      'newId' => 'widget_widget_op_is_open',
      'attributeMap' => array(
        'title' => 'title',
        'caption-open' => 'open_text',
        'caption-closed' => 'closed_text'
      )
    ),
    'holidays' => array(
      'oldId' => 'widget_widget_op_holidays',
      'newId' => 'widget_widget_op_holidays',
      'attributeMap' => array(
        'title' => 'title',
        'highlighted' => 'highlight'
      )
    ),
    'special_openings' => array(
      'oldId' => 'widget_widget_op_special_openings',
      'newId' => 'widget_widget_op_irregular_openings',
      'attributeMap' => array(
        'title' => 'title',
        'highlighted' => 'highlight'
      )
    )
  );

  /**
   * The WP_Post instance representing the new set
   * @var       \WP_Post
   */
  protected $post;

  public function import () {
    $this->importOpeningHours();
    if (!$this->post instanceof \WP_Post)
      return;

    $this->upgradeWidgets();

    add_action('init', array($this, 'addImportedNotice'));
  }

  public function addImportedNotice () {
    add_notice(__('Your Opening Hours and related widgets have automatically been upgraded to work with the updated version of the Plugin. Please double check your Opening Hours and Widgets.', 'wp-opening-hours'), 'update');
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
      'post_title' => __('Opening Hours', 'wp-opening-hours'),
      'post_status' => 'publish'
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

  /**
   * Upgrades old widget data to new widget data
   */
  protected function upgradeWidgets () {
    foreach (self::$widgetMap as $widget) {
      $old = get_option($widget['oldId']);
      if (!$old || !is_array($old) || count($old) < 1)
        continue;

      $new = array();
      foreach ($old as $key => $oldWidget) {
        if (!is_array($oldWidget))
          continue;

        $newWidget = array(
          'set_id' => $this->post->ID
        );

        foreach ($widget['attributeMap'] as $oldKey => $newKey) {
          if (array_key_exists($oldKey, $oldWidget))
            $newWidget[$newKey] = $oldWidget[$oldKey];
        }

        $new[$key] = $newWidget;
      }

      delete_option($widget['oldId']);
      add_option($widget['newId'], $new);
    }

    $sidebars = get_option(self::OPTION_KEY_SIDEBARS);

    if (!is_array($sidebars))
      return;

    foreach ($sidebars as $key => &$widgets) {
      if ($key === 'array_version')
        continue;

      foreach ($widgets as $offset => $id) {
        if (preg_match(self::REGEX_OLD_WIDGET_KEY, $id) === false)
          continue;

        $id = str_replace(array('status', 'special_openings'), array('is_open', 'irregular_openings'), $id);
        $widgets[$offset] = $id;
      }
    }

    update_option(self::OPTION_KEY_SIDEBARS, $sidebars);
  }

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