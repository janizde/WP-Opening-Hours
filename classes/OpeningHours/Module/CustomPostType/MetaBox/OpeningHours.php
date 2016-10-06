<?php

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Entity\Period;
use OpeningHours\Module\OpeningHours as OpeningHoursModule;
use OpeningHours\Util\Dates;
use OpeningHours\Util\Persistence;
use OpeningHours\Util\ViewRenderer;
use OpeningHours\Util\Weekday;
use OpeningHours\Util\Weekdays;
use WP_Post;

/**
 * Meta Box implementation for regular Opening Hours
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\CustomPostType\MetaBox
 */
class OpeningHours extends AbstractMetaBox {

  const TEMPLATE_PATH = 'meta-box/opening-hours.php';
  const TEMPLATE_PATH_SINGLE = 'ajax/op-set-period.php';

  public function __construct () {
    parent::__construct('op_meta_box_opening_hours', __('Opening Hours', 'wp-opening-hours'), self::CONTEXT_ADVANCED, self::PRIORITY_HIGH);
  }

  /** @inheritdoc */
  public function renderMetaBox ( WP_Post $post ) {
    $set = $this->getSet($post->ID);
    $periods = $this->groupPeriodsWithDummy($set->getPeriods()->getArrayCopy());

    $vr = new ViewRenderer(op_view_path(self::TEMPLATE_PATH), array(
      'periods' => $periods,
      'set' => $set
    ));
    $vr->render();
  }

  /** @inheritdoc */
  protected function saveData ( $post_id, WP_Post $post, $update ) {
    $config = $_POST['opening-hours'];

    if (!is_array($config))
      $config = array();

    $periods = $this->getPeriodsFromPostData($config);
    $persistence = new Persistence($post);
    $persistence->savePeriods($periods);
  }

  /**
   * Converts raw post data to an array of Periods
   *
   * @param     array $data associative array of raw post data
   *
   * @return    Period[]            array of Periods derived from post data
   */
  public function getPeriodsFromPostData ( array $data ) {
    $periods = array();

    foreach ($data as $weekday => $dayConfig) {
      for ($i = 0; $i <= count($dayConfig['start']); $i++) {
        if (empty($dayConfig['start'][$i]) or empty($dayConfig['end'][$i]))
          continue;

        if ($dayConfig['start'][$i] === '00:00' and $dayConfig['end'][$i] === '00:00')
          continue;

        try {
          $period = new Period($weekday, $dayConfig['start'][$i], $dayConfig['end'][$i]);
          $periods[] = $period;
        } catch (\InvalidArgumentException $e) {
          trigger_error(sprintf('Period could not be saved due to: %s', $e->getMessage()));
        }
      }
    }

    return $periods;
  }

  /**
   * Groups the periods by day and adds dummy periods if no period is set for a specific day
   * @param     Period[]  $periods  The periods to group
   * @return    array               Array containing period data. Each element is an associative array consisting of:
   *                                  day: Weekday instance representing the weekday
   *                                  periods: Period[] containing the period for day
   */
  public function groupPeriodsWithDummy (array $periods) {
    $days = array_map(function (Weekday $weekday) {
      return array(
        'day' => $weekday,
        'periods' => array()
      );
    }, Weekdays::getWeekdays());

    /** @var Period $period */
    foreach ($periods as $period) {
      $days[$period->getWeekday()]['periods'][] = $period;
    }

    foreach ($days as &$day) {
      if (count($day['periods']) < 1)
        $day['periods'][] = Period::createDummy($day['day']->getIndex());
    }

    for ($i = 0; $i < Dates::getStartOfWeek(); ++$i) {
      $days[] = array_shift($days);
    }

    return $days;
  }
}