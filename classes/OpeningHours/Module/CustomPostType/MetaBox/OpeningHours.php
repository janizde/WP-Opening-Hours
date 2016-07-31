<?php

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Entity\Period;
use OpeningHours\Entity\Set as SetEntity;
use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours as OpeningHoursModule;
use OpeningHours\Util\Persistence;
use OpeningHours\Util\ViewRenderer;
use WP_Post;

/**
 * Meta Box implementation for regular Opening Hours
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\CustomPostType\MetaBox
 */
class OpeningHours extends AbstractMetaBox {

  const TEMPLATE_PATH = 'views/meta-box/opening-hours.php';

  public function __construct () {
    parent::__construct('op_meta_box_opening_hours', __('Opening Hours', I18n::TEXTDOMAIN), self::CONTEXT_ADVANCED, self::PRIORITY_HIGH);
  }

  /** @inheritdoc */
  public function renderMetaBox ( WP_Post $post ) {
    if (!OpeningHoursModule::getSets()->offsetExists($post->ID))
      OpeningHoursModule::getSets()->offsetSet($post->ID, new SetEntity($post->ID));

    OpeningHoursModule::setCurrentSetId($post->ID);
    $set = OpeningHoursModule::getCurrentSet();
    $set->addDummyPeriods();

    $vr = new ViewRenderer(op_plugin_path() . self::TEMPLATE_PATH, array());
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
}