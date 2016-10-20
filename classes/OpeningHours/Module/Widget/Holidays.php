<?php

namespace OpeningHours\Module\Widget;

use OpeningHours\Form\HolidaysForm;
use OpeningHours\Module\Shortcode\Holidays as HolidaysShortcode;

/**
 * Widget for Holiday Shortcode
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Widget
 */
class Holidays extends AbstractWidget {

  public function __construct () {
    $title = __('Opening Hours: Holidays', 'wp-opening-hours');
    $description = __('Lists up all Holidays in the selected Set.', 'wp-opening-hours');
    parent::__construct(
      'widget_op_holidays',
      $title,
      $description,
      HolidaysShortcode::getInstance(),
      new HolidaysForm());
  }
}