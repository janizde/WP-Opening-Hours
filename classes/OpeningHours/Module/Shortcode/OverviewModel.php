<?php

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Period;
use OpeningHours\Util\Dates;
use OpeningHours\Util\Weekdays;

class OverviewModel {

  /**
   * Array containing model data.
   * Each element is an array consisting of
   *  days: Weekday[]
   *  items: Period[]|IrregularOpening|Holiday
   * @var       array
   */
  protected $data;

  /**
   * Current time
   * @var       \DateTime
   */
  protected $now;

  /**
   * Minimum DateTime for Overview
   * @var       \DateTime
   */
  protected $minDate;

  /**
   * Maximum DateTime for Overview
   * @var       \DateTime
   */
  protected $maxDate;

  /**
   * Start of Week as Weekday index
   * @var       int
   */
  protected $startOfWeek;

  public function __construct (array $periods, \DateTime $now = null) {
    $this->startOfWeek = Dates::getStartOfWeek();
    $this->now = $now === null ? Dates::getNow() : $now;

    $nowWeekday = (int) $this->now->format('w');
    $this->minDate = clone $this->now;
    $this->minDate->setTime(0,0,0);
    if ($nowWeekday !== $this->startOfWeek) {
      $offset = ($nowWeekday + 7 - $this->startOfWeek) % 7;
      $this->minDate->sub(new \DateInterval('P'.$offset.'D'));
    }

    $this->maxDate = clone $this->minDate;
    $this->maxDate->add(new \DateInterval('P7D'));

    $this->data = array();

    for ($i = 0; $i < 7; ++$i) {
      $this->data[] = array(
        'days' => array(Weekdays::getWeekday(($i + $this->startOfWeek) % 7)),
        'items' => array()
      );
    }

    /** @var Period $period */
    foreach ($periods as $period) {
      $idx = ($period->getWeekday() - $this->startOfWeek + 7) % 7;
      $this->data[$idx]['items'][] = $now === null ? clone $period : $period->getCopyInDateContext($now);
    }
  }

  /**
   * Merges the specified holidays into the OverviewModel
   * @param     Holiday[] $holidays The Holidays to merge into the model
   */
  public function mergeHolidays (array $holidays) {
    /** @var Holiday $holiday */
    foreach ($holidays as $holiday) {
      if ($holiday->getDateEnd() < $this->minDate || $holiday->getDateStart() > $this->maxDate)
        continue;

      if ($holiday->getDateStart() <= $this->minDate && $holiday->getDateEnd() >= $this->maxDate) {
        foreach ($this->data as &$day) {
          $day['items'] = $holiday;
        }
        continue;
      }

      if ($holiday->getDateStart() <= $this->minDate) {
        $interval = $holiday->getDateEnd()->diff($this->minDate);
        for ($i = 0; $i < $interval->days + 1; ++$i) {
          $this->data[$i]['items'] = $holiday;
        }
        continue;
      }

      if ($holiday->getDateEnd() >= $this->maxDate) {
        $interval = $this->maxDate->diff($holiday->getDateStart());
        for ($i = 7 - $interval->days; $i < 7; ++$i) {
          $this->data[$i]['items'] = $holiday;
        }
        continue;
      }

      // Holiday is in between boundaries
      $offset = $holiday->getDateStart()->diff($this->minDate);
      $interval = $holiday->getDateEnd()->diff($holiday->getDateStart());

      for ($i = $offset->days; $i < $offset->days + $interval->days + 1; ++$i) {
        $this->data[$i]['items'] = $holiday;
      }
    }
  }

  /**
   * Merges the specified irregular openings into the OverviewModel
   * @param     IrregularOpening[]  $irregularOpenings  The Irregular Openings to merge into the model
   */
  public function mergeIrregularOpenings (array $irregularOpenings) {
    /** @var IrregularOpening $irregularOpening */
    foreach ($irregularOpenings as $irregularOpening) {
      if ($irregularOpening->getTimeEnd() < $this->minDate || $irregularOpening->getTimeStart() > $this->maxDate)
        continue;

      $offset = $irregularOpening->getDate()->diff($this->minDate);
      $this->data[$offset->days]['items'] = $irregularOpening;
    }
  }

  /**
   * Merges days with equal items together and retrieves data.
   * @return    array               The compressed model data
   */
  public function getCompressedData () {
    $compressed = array();
    foreach ($this->data as $day) {
      $inserted = false;
      foreach ($compressed as &$compressedDay) {
        if ($this->itemsEqual($day['items'], $compressedDay['items'])) {
          $compressedDay['days'][] = $day['days'][0];
          $inserted = true;
          break;
        }
      }

      if (!$inserted)
        $compressed[] = $day;
    }

    return $compressed;
  }

  /**
   * Checks whether the two items values equal
   * @param     mixed     $items1   First items value
   * @param     mixed     $items2   Second items value
   * @return    bool                Whether the two items values equal
   */
  protected function itemsEqual ($items1, $items2) {
    if (is_array($items1) xor is_array($items2))
      return false;

    if (is_array($items1)) {
      /** @var Period[] $items1, $items2 */
      if (count($items1) < 1 && count($items2) < 1)
        return true;

      if (count($items1) !== count($items2))
        return false;

      for ($i = 0; $i < count($items1); ++$i) {
        if (!$items1[$i]->equals($items2[$i], true))
          return false;
      }

      return true;
    }

    return $items1 === $items2;
  }

  /**
   * @return array
   */
  public function getData () {
    return $this->data;
  }

  /**
   * @return \DateTime
   */
  public function getNow () {
    return $this->now;
  }

  /**
   * @return \DateTime
   */
  public function getMinDate () {
    return $this->minDate;
  }

  /**
   * @return \DateTime
   */
  public function getMaxDate () {
    return $this->maxDate;
  }

  /**
   * @return int
   */
  public function getStartOfWeek () {
    return $this->startOfWeek;
  }
}