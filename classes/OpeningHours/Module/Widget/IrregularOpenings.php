<?php

namespace OpeningHours\Module\Widget;

use OpeningHours\Form\IrregularOpeningsForm;
use OpeningHours\Module\Shortcode\IrregularOpenings as IrregularOpeningsShortcode;

/**
 * Widget for IrregularOpenings Shortcode
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Widget
 */
class IrregularOpenings extends AbstractWidget {

  public function __construct () {
    $title = __('Opening Hours: Irregular Openings', 'wp-opening-hours');
    $description = __('Lists up all Irregular Openings in the selected Set.', 'wp-opening-hours');
    parent::__construct(
      'widget_op_irregular_openings',
      $title,
      $description,
      IrregularOpeningsShortcode::getInstance(),
      new IrregularOpeningsForm());
  }
}