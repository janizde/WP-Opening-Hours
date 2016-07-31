<?php

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Set;
use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours;
use OpeningHours\Util\Dates;

/**
 * Shortcode implementation for a list or regular Opening Periods
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Shortcode
 */
class Overview extends AbstractShortcode {

  /** @inheritdoc */
  protected function init () {
    $this->setShortcodeTag('op-overview');

    $this->defaultAttributes = array(
      'before_title' => '<h3 class="op-overview-title">',
      'after_title' => '</h3>',
      'before_widget' => '<div class="op-overview-shortcode">',
      'after_widget' => '</div>',
      'set_id' => 0,
      'title' => null,
      'show_closed_days' => false,
      'show_description' => true,
      'highlight' => 'nothing',
      'compress' => false,
      'short' => false,
      'include_io' => false,
      'include_holidays' => false,
      'caption_closed' => __('Closed', I18n::TEXTDOMAIN),
      'table_classes' => null,
      'row_classes' => null,
      'cell_classes' => null,
      'cell_heading_classes' => null,
      'cell_periods_classes' => null,
      'cell_description_classes' => 'op-set-description',
      'highlighted_period_class' => 'highlighted',
      'highlighted_day_class' => 'highlighted',
      'table_id_prefix' => 'op-table-set-',
      'time_format' => Dates::getTimeFormat(),
      'hide_io_date' => false,
      'span_period_classes' => ''
    );

    $this->validAttributeValues = array(
      'highlight' => array('nothing', 'period', 'day'),
      'show_closed_day' => array(false, true),
      'show_description' => array(true, false),
      'include_io' => array(false, true),
      'include_holidays' => array(false, true),
      'hide_io_date' => array(false, true)
    );

    $this->templatePath = 'shortcode/overview.php';
  }

  /** @inheritdoc */
  public function shortcode ( array $attributes ) {
    if (!isset($attributes['set_id']) or !is_numeric($attributes['set_id']) or $attributes['set_id'] == 0) {
      trigger_error("Set id not properly set in Opening Hours Overview shortcode");
      return;
    }

    $setId = (int)$attributes['set_id'];
    $set = OpeningHours::getSet($setId);

    if (!$set instanceof Set) {
      trigger_error(sprintf("Set with id %d does not exist", $setId));
      return;
    }

    $attributes['set'] = $set;
    echo $this->renderShortcodeTemplate($attributes);
  }

  /**
   * Renders an Irregular Opening Item for Overview table
   *
   * @param     IrregularOpening $io         The Irregular Opening to show
   * @param     array            $attributes The shortcode attributes
   */
  public static function renderIrregularOpening ( IrregularOpening $io, array $attributes ) {
    $name = $io->getName();
    $date = $io->getTimeStart()->format(Dates::getDateFormat());

    $heading = ($attributes['hide_io_date']) ? $name : sprintf('%s (%s)', $name, $date);

    $now = Dates::getNow();
    $highlighted = ($attributes['highlight'] == 'period' and $io->getTimeStart() <= $now and $now <= $io->getTimeEnd()) ? $attributes['highlighted_period_class'] : null;

    echo '<span class="op-period-time irregular-opening ' . $highlighted . '">' . $heading . '</span>';

    $time_start = $io->getTimeStart()->format($attributes['time_format']);
    $time_end = $io->getTimeEnd()->format($attributes['time_format']);

    $period = sprintf('%s – %s', $time_start, $time_end);

    echo '<span class="op-period-time ' . $highlighted . ' ' . $attributes['span_period_classes'] . '">' . $period . '</span>';
  }

  /**
   * Renders a Holiday Item for Overview table
   *
   * @param     Holiday $holiday    The Holiday item to show
   * @param     array   $attributes The shortcode attributes
   */
  public static function renderHoliday ( Holiday $holiday, array $attributes ) {
    echo '<span class="op-period-time holiday ' . $attributes['span_period_classes'] . '">' . $holiday->getName() . '</span>';
  }
}