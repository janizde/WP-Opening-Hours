<?php

use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\Set;
use OpeningHours\Util\Dates;

extract( $this->data['attributes'] );

/**
 * variables defined by extract
 *
 * @var         $before_widget      string w/ HTML markup before Widget
 * @var         $after_widget       string w/ HTML markup after Widget
 * @var         $before_title       string w/ HTML markup before title
 * @var         $after_title        string w/ HTML markup after title
 *
 * @var         $set                Set object
 * @var         $holidays           ArrayObject w/ Holiday objects of set
 * @var         $highlight          bool whether highlight active Holiday or not
 * @var         $title              string w/ Widget title
 *
 * @var         $class_holiday      string w/ class for holiday row
 * @var         $class_highlighted  string w/ class for highlighted Holiday
 * @var         $date_format        string w/ PHP date format
 */

if ( !count( $holidays ) )
	return;

echo $before_widget;

if ( ! empty( $title ) ) {
	echo $before_title . $title . $after_title;
}

?>
<table class="op-table op-table-holidays">
  <tbody>
    <?php
    /** @var Holiday $holiday */
    foreach ($holidays as $holiday) :
    $highlighted = ($highlight && $holiday->isActive()) ? $class_highlighted : '';
    ?>
    <tr class="<?php echo $class_holiday; ?> <?php echo $highlighted; ?>">
      <td class="col-name"><?php echo $holiday->getName(); ?></td>
      <td class="col-date-start"><?php echo Dates::format($date_format, $holiday->getDateStart()); ?></td>
      <td class="col-date-end"><?php echo Dates::format($date_format, $holiday->getDateEnd()); ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php echo $after_widget; ?>