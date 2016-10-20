<?php

namespace OpeningHours\Module\Widget;

use OpeningHours\Form\OverviewForm;
use OpeningHours\Module\Shortcode\Overview as OverviewShortcode;

/**
 * Widget for Overview Shortcode
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Widget
 */
class Overview extends AbstractWidget {

  public function __construct () {
    $title = __('Opening Hours: Overview', 'wp-opening-hours');
    $description = __('Displays a Table with your Opening Hours. Alternatively use the op-overview Shortcode.', 'wp-opening-hours');
    parent::__construct(
      'widget_op_overview',
      $title,
      $description,
      OverviewShortcode::getInstance(),
      new OverviewForm());
  }
}