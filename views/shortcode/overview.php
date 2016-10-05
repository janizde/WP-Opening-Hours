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

<table class="op-table op-table-overview">
  <?php if ($show_description && !empty($description)) : ?>
    <tr class="op-row op-row-description">
      <td class="op-cell op-cell-description" colspan="2"><?php echo $description; ?></td>
    </tr>
  <?php endif; ?>

  <?php foreach ($days as $dayData) : ?>
  <tr class="op-row op-row-day <?php echo $dayData['highlightedDayClass']; ?>">
    <th class="op-cell op-cell-heading" scope="row"><?php echo $dayData['dayCaption']; ?></th>
    <td class="op-cell op-cell-periods"><?php echo $dayData['periodsMarkup']; ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<?php echo $after_widget; ?>