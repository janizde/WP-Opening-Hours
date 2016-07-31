<?php

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Entity\Set;
use OpeningHours\Module\OpeningHours;
use OpeningHours\Util\Dates;

/**
 * Shortcode implementation for a list of Irregular Openings
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Shortcode
 */
class IrregularOpenings extends AbstractShortcode {

  /** @inheritdoc */
  protected function init () {

    $this->setShortcodeTag('op-irregular-openings');

    $this->defaultAttributes = array(
      'title' => null,
      'set_id' => null,
      'highlight' => false,
      'before_widget' => null,
      'after_widget' => null,
      'before_title' => null,
      'after_title' => null,
      'class_highlighted' => 'highlighted',
      'date_format' => Dates::getDateFormat(),
      'time_format' => Dates::getTimeFormat(),
      'template' => 'table'
    );

    $this->validAttributeValues = array(
      'highlight' => array(false, true),
      'template' => array('table', 'list')
    );
  }

  /** @inheritdoc */
  public function shortcode ( array $attributes ) {
    $setId = $attributes['set_id'];

    if (!is_numeric($setId))
      return;

    $set = OpeningHours::getSet($setId);

    if (!$set instanceof Set)
      return;

    $templateMap = array(
      'table' => 'shortcode/irregular-openings.php',
      'list' => 'shortcode/irregular-openings-list.php'
    );

    $attributes['set'] = $set;
    $attributes['irregular_openings'] = $set->getIrregularOpenings();

    echo $this->renderShortcodeTemplate($attributes, $templateMap[$attributes['template']]);
  }
}