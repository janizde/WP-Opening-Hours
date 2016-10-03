<?php

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Entity\Set;
use OpeningHours\Module\OpeningHours;
use OpeningHours\Util\Dates;

/**
 * Shortcode implementation for a list of Holidays
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Shortcode
 */
class Holidays extends AbstractShortcode {

  /** @inheritdoc */
  protected function init () {
    $this->setShortcodeTag('op-holidays');

    $this->defaultAttributes = array(
      'title' => null,
      'set_id' => null,
      'highlight' => false,
      'before_widget' => '<div class="op-holidays-shortcode">',
      'after_widget' => '</div>',
      'before_title' => '<h3 class="op-holidays-title">',
      'after_title' => '</h3>',
      'class_holiday' => 'op-holiday',
      'class_highlighted' => 'highlighted',
      'date_format' => Dates::getDateFormat(),
      'template' => 'table'
    );

    $this->validAttributeValues = array(
      'template' => array('table', 'list')
    );
  }

  /** @inheritdoc */
  public function shortcode ( array $attributes ) {
    $setId = $attributes['set_id'];

    $set = OpeningHours::getInstance()->getSet($setId);

    if (!$set instanceof Set)
      return;

    $templateMap = array(
      'table' => 'shortcode/holidays.php',
      'list' => 'shortcode/holidays-list.php'
    );

    $attributes['set'] = $set;
    $attributes['holidays'] = $set->getHolidays();
    echo $this->renderShortcodeTemplate($attributes, $templateMap[$attributes['template']]);
  }

}