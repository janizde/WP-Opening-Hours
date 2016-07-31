<?php

use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Set;
use OpeningHours\Module\OpeningHours;
use OpeningHours\Module\Shortcode\Overview as Shortcode;
use OpeningHours\Util\Dates;
use OpeningHours\Util\Weekdays;

extract( $this->data['attributes'] );

/**
 * Variables defined by extraction
 *
 * @var       $before_widget      string w/ html before widget
 * @var       $after_widget       string w/ html after widget
 * @var       $before_title       string w/ html before title
 * @var       $after_title        string w/ html after title
 *
 * @var       $title              string w/ widget title
 * @var       $set                Set object to show opening hours of
 * @var       $highlight          string w/ identifier of what section to highlight
 * @var       $show_closed        bool whether to show closed days or not
 * @var       $show_description   bool whether to show description or not
 * @var       $compress           bool whether to compress Opening Hours
 * @var       $short              bool whether to use short day captions
 * @var       $include_io         bool whether to be aware of irregular openings
 * @var       $include_holidays   bool whether to be aware of holidays
 *
 * @var       $caption_closed     string w/ caption for closed days
 *
 * @var       $highlighted_day_class      string w/ class for highlighted day
 * @var       $highlighted_period_class   string w/ class for highlighted period
 * @var       $time_format                string w/ PHP time format to format start and end time of a period with
 */

echo $before_widget;

if ( $title ) {
  echo $before_title . $title . $after_title;
}

OpeningHours::setCurrentSetId( $set->getId() );

$description = $set->getDescription();
$periods = $compress
  ? $set->getPeriodsGroupedByDayCompressed()
  : $set->getPeriodsGroupedByDay();
?>

<dl class="op-list op-list-overview">
  <?php if ($show_description && !empty($description)) : ?>
    <dt class="op-cell op-cell-description"><?php echo $description; ?></dt>
  <?php endif; ?>

  <?php foreach ($periods as $day => $dayPeriods) :
    $highlightedDay = ($highlight === 'day' && Dates::isToday($day)) ? $highlighted_day_class : null;
    ?>

    <dt class="op-cell op-cell-heading <?php echo $highlightedDay; ?>"><?php echo Weekdays::getDaysCaption($day, $short); ?></dt>
    <dd class="op cell op-cell-periods <?php echo $highlightedDay; ?>">
      <?php
      $finished = false;
      if ($include_io) {
        $io = $set->getActiveIrregularOpeningOnWeekday($day);
        if ($io instanceof IrregularOpening) {
          Shortcode::renderIrregularOpening($io, $this->data['attributes']);
          $finished = true;
        }
      }

      if (!$finished && $include_holidays) {
        $holiday = $set->getActiveHolidayOnWeekday($day);
        if ($holiday instanceof Holiday) {
          Shortcode::renderHoliday($holiday);
          $finished = true;
        }
      }

      if (!$finished && count($dayPeriods) < 1) {
        echo '<span class="op-closed">'.$caption_closed.'</span>';
        $finished = true;
      }

      if (!$finished) {
        /** @var \OpeningHours\Entity\Period $period */
        foreach ($dayPeriods as $period) {
          $highlightedPeriod = ( $highlight == 'period' and $period->isOpen() ) ? $highlighted_period_class : '';
          printf('<span class="op-period-time %s">%s</span>', $highlightedPeriod, $period->getFormattedTimeRange($time_format));
        }
      }
      ?>
    </dd>
  <?php endforeach; ?>
</dl>

<?php echo $after_widget; ?>