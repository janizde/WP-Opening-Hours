<?php

namespace OpeningHours\Module\Widget;

use OpeningHours\Form\IsOpenForm;
use OpeningHours\Module\Shortcode\IsOpen as IsOpenShortcode;

/**
 * Widget for IsOpen Shortcode
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Widget
 */
class IsOpen extends AbstractWidget {

  public function __construct () {
    $title = __('Opening Hours: Is Open Status', 'wp-opening-hours');
    $description = __('Shows a box saying whether a specific set is currently open or closed based on Periods.', 'wp-opening-hours');
    parent::__construct(
      'widget_op_is_open',
      $title,
      $description,
      IsOpenShortcode::getInstance(),
      new IsOpenForm());
  }
}