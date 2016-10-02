<?php

use OpeningHours\Entity\Set;
use OpeningHours\Module\OpeningHours;

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
 * @var       $show_description   bool whether to show description or not
 * @var       $days               array containing per day data
 *
 * @var       $set                Set whose Opening Hours to show
 */

echo $before_widget;

if ( $title ) {
  echo $before_title . $title . $after_title;
}

$description = $set->getDescription();
?>

<dl class="op-list op-list-overview">
  <?php if ($show_description && !empty($description)) : ?>
    <dt class="op-cell op-cell-description"><?php echo $description; ?></dt>
  <?php endif; ?>

  <?php foreach ($days as $dayData) : ?>
    <dt class="op-cell op-cell-heading <?php echo $dayData['highlightedDayClass']; ?>"><?php echo $dayData['dayCaption']; ?></dt>
    <dd class="op-cell op-cell-periods <?php echo $dayData['highlightedDayClass']; ?>"><?php echo $dayData['periodsMarkup']; ?></dd>
  <?php endforeach; ?>
</dl>

<?php echo $after_widget; ?>