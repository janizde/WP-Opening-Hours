<?php

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Period;
use OpeningHours\Entity\Set;
use OpeningHours\Module\OpeningHours;
use OpeningHours\Util\Dates;
use OpeningHours\Util\Weekdays;

/**
 * Shortcode indicating whether the venue is currently open or not
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Shortcode
 */
class IsOpen extends AbstractShortcode {
  const FILTER_FORMAT_TODAY = 'op_is_open_format_today';
  const FILTER_FORMAT_NEXT = 'op_is_open_format_next';

  /** @inheritdoc */
  protected function init() {
    $this->setShortcodeTag('op-is-open');

    $this->defaultAttributes = array(
      'set_id' => null,
      'open_text' => __('We\'re currently open.', 'wp-opening-hours'),
      'closed_text' => __('We\'re currently closed.', 'wp-opening-hours'),
      'closed_holiday_text' => __('We\'re currently closed for %1$s.', 'wp-opening-hours'),
      'show_next' => false,
      'next_format' => __('We\'re open again on %2$s (%1$s) from %3$s to %4$s', 'wp-opening-hours'),
      'show_today' => 'never',
      'show_closed_holidays' => false,
      'today_format' => __('Opening Hours today: %1$s', 'wp-opening-hours'),
      'before_widget' => '<div class="op-is-open-shortcode">',
      'after_widget' => '</div>',
      'before_title' => '<h3 class="op-is-open-title">',
      'after_title' => '</h3>',
      'title' => null,
      'classes' => null,
      'next_period_classes' => null,
      'open_class' => 'op-open',
      'closed_class' => 'op-closed',
      'date_format' => Dates::getDateFormat(),
      'time_format' => Dates::getTimeFormat()
    );

    $this->validAttributeValues = array(
      'show_next' => array(false, true),
      'show_today' => array('never', 'open', 'always')
    );
  }

  /** @inheritdoc */
  public function shortcode(array $attributes) {
    $setId = $attributes['set_id'];

    $set = OpeningHours::getInstance()->getSet($setId);

    if (!$set instanceof Set) {
      return;
    }

    $isOpen = $set->isOpen();
    $todayData = $set->getDataForDate(Dates::getNow());

    if ($attributes['show_next']) {
      $nextPeriod = $set->getNextOpenPeriod();
      $attributes['next_period'] = $set->getNextOpenPeriod();
      $attributes['next_string'] = apply_filters(
        self::FILTER_FORMAT_NEXT,
        $this->formatNext($nextPeriod, $attributes),
        $nextPeriod,
        $attributes,
        $todayData
      );
    }

    if ($attributes['show_today'] === 'always' || ($attributes['show_today'] === 'open' && $isOpen)) {
      $todayPeriods = $this->getTodaysPeriods($todayData);
      $attributes['today_periods'] = $todayPeriods;
      $attributes['today_string'] = apply_filters(
        self::FILTER_FORMAT_TODAY,
        $this->formatToday($todayPeriods, $attributes),
        $todayPeriods,
        $attributes,
        $todayData
      );
    }

    $attributes['is_open'] = $isOpen;
    $attributes['classes'] .= $isOpen ? $attributes['open_class'] : $attributes['closed_class'];

    // If the attribute show_closed_holidays is enabled
    if ($attributes['show_closed_holidays']) {
      $holidaysList = $this->getTodaysHolidaysCommaSeperated($todayData);
      $closedText = $holidaysList
        ? sprintf($attributes['closed_holiday_text'], $holidaysList)
        : $attributes['closed_text'];
    } else {
      $closedText = $attributes['closed_text'];
    }

    $attributes['text'] = $isOpen ? $attributes['open_text'] : $closedText;

    echo $this->renderShortcodeTemplate($attributes, 'shortcode/is-open.php');
  }

  /**
   * Retrieves holiday names for today
   * @param  array $todayData   Data for today
   * @return string            Extracted holiday name(s)
   */
  public function getTodaysHolidaysCommaSeperated($todayData) {
    if (count($todayData['holidays']) > 0) {
      $holidayNames = array();

      foreach ($todayData['holidays'] as $holiday) {
        array_push($holidayNames, $holiday->getName());
      }

      return implode(', ', $holidayNames);
    }

    return null;
  }

  /**
   * Retrieves periods from today data
   * @param     array   $todayData    Data for today
   * @return    Period[]              Extracted periods
   */
  public function getTodaysPeriods($todayData) {
    if (count($todayData['irregularOpenings']) > 0) {
      /* @var IrregularOpening $io */
      $io = $todayData['irregularOpenings'][0];
      return array($io->createPeriod());
    }

    if (count($todayData['holidays']) > 0) {
      return array();
    }

    return $todayData['periods'];
  }

  /**
   * Formats the todays opening hours message according to shortcode attributes
   * @param     Period[]    $periods    Array of period on that day
   * @param     array       $attributes Shortcode attributes
   * @return    string|null             Formatted today message (after filter 'op_is_open_format_today')
   */
  public function formatToday(array $periods, array $attributes) {
    if (count($periods) < 1) {
      return null;
    }

    $timeFormat = $attributes['time_format'];
    $periodStrings = array_map(function (Period $p) use ($timeFormat) {
      return $p->getFormattedTimeRange($timeFormat);
    }, $periods);

    $periodString = implode(', ', $periodStrings);

    $periodsStart = $periods[0]->getTimeStart()->format($timeFormat);
    $periodsEnd = $periods[count($periods) - 1]->getTimeEnd()->format($timeFormat);

    return sprintf($attributes['today_format'], $periodString, $periodsStart, $periodsEnd);
  }

  /**
   * Formats the next open period message according to shortcode attributes
   * @param     Period    $nextPeriod   The next open period or null if it doesnt exist
   * @param     array     $attributes   Shortcode attributes
   * @return    string|null             Formatted next period message (after filter 'op_is_open_format_next')
   */
  public function formatNext(Period $nextPeriod = null, array $attributes) {
    if (!$nextPeriod instanceof Period) {
      return null;
    }

    return sprintf(
      // Format String
      $attributes['next_format'],

      // 1$: Formatted Date
      Dates::format($attributes['date_format'], $nextPeriod->getTimeStart()),

      // 2$: Translated Weekday
      Weekdays::getWeekday($nextPeriod->getWeekday())->getName(),

      // 3%: Formatted Start Time
      $nextPeriod->getTimeStart()->format($attributes['time_format']),

      // 4%: Formatted End Time
      $nextPeriod->getTimeEnd()->format($attributes['time_format'])
    );
  }
}
