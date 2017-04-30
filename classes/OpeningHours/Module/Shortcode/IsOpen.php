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
  protected function init () {

    $this->setShortcodeTag('op-is-open');

    $this->defaultAttributes = array(
      'set_id' => null,
      'open_text' => __('We\'re currently open.', 'wp-opening-hours'),
      'closed_text' => __('We\'re currently closed.', 'wp-opening-hours'),
      'show_next' => false,
      'next_format' => __('We\'re open again on %2$s (%1$s) from %3$s to %4$s', 'wp-opening-hours'),
      'show_today' => 'never',
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
  public function shortcode ( array $attributes ) {
    $setId = $attributes['set_id'];

    $set = OpeningHours::getInstance()->getSet($setId);

    if (!$set instanceof Set)
      return;

    $isOpen = $set->isOpen();
    $nextPeriod = $set->getNextOpenPeriod();

    if ($attributes['show_next']) {
      $attributes['next_period'] = $nextPeriod;
      $attributes['next_string'] = $this->formatNext($nextPeriod, $attributes);
    }

    if (
      $attributes['show_today'] === 'always'
      || $attributes['show_today'] === 'open' && $isOpen
    ) {
      $todayData = $set->getDataForDate(Dates::getNow());
      $todayPeriods = $this->getTodaysPeriods($todayData);
      $attributes['today_periods'] = $todayPeriods;
      $attributes['today_string'] = $this->formatToday($todayPeriods, $attributes);
    }

    $attributes['is_open'] = $isOpen;
    $attributes['classes'] .= ($isOpen) ? $attributes['open_class'] : $attributes['closed_class'];
    $attributes['text'] = ($isOpen) ? $attributes['open_text'] : $attributes['closed_text'];
    $attributes['next_period'] = $set->getNextOpenPeriod();

    echo $this->renderShortcodeTemplate($attributes, 'shortcode/is-open.php');
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
      return array(
        $io->createPeriod()
      );
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
   * @return    string                  Formatted today message (after filter 'op_is_open_format_today')
   */
  public function formatToday(array $periods, array $attributes) {
    if (count($periods) < 1) {
      $str = null;
    } else {
      $timeFormat = $attributes['time_format'];
      $periodStrings = array_map(function (Period $p) use ($timeFormat) {
        return $p->getFormattedTimeRange($timeFormat);
      }, $periods);

      $periodString = implode(', ', $periodStrings);

      $periodsStart = $periods[0]->getTimeStart()->format($timeFormat);
      $periodsEnd = $periods[count($periods) - 1]->getTimeEnd()->format($timeFormat);

      $str = sprintf($attributes['today_format'], $periodString, $periodsStart, $periodsEnd);
    }

    return apply_filters(self::FILTER_FORMAT_TODAY, $str, $periods, $attributes);
  }

  /**
   * Formats the next open period message according to shortcode attributes
   * @param     Period    $nextPeriod   The next open period or null if it doesnt exist
   * @param     array     $attributes   Shortcode attributes
   * @return    string                  Formatted next period message (after filter 'op_is_open_format_next')
   */
  public function formatNext(Period $nextPeriod = null, array $attributes) {
    if (!$nextPeriod instanceof Period) {
      $str = null;
    } else {
      $str = sprintf(
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

    return apply_filters(self::FILTER_FORMAT_NEXT, $str, $nextPeriod, $attributes);
  }
}
