<?php
/**
 * Opening Hours: Views: Ajax: OP Set Holiday
 */

use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Module\CustomPostType\MetaBox\IrregularOpenings as MetaBox;
use OpeningHours\Module\I18n;

/**
 * pre-defined variables
 *
 * @var         $io        IrregularOpening object
 */

$name      = $io->getName();
$date      = ( $io->isDummy() ) ? null : $io->getDate()->format( I18n::STD_DATE_FORMAT );
$timeStart = ( $io->isDummy() ) ? null : $io->getTimeStart()->format( I18n::STD_TIME_FORMAT );
$timeEnd   = ( $io->isDummy() ) ? null : $io->getTimeEnd()->format( I18n::STD_TIME_FORMAT );

echo '<tr class="op-irregular-opening">';

echo '<td class="col-name">';
echo '<input type="text" name="' . MetaBox::GLOBAL_POST_KEY . '[name][]" class="widefat name" value="' . $name . '" />';
echo '</td>';

echo '<td class="col-date">';
echo '<input type="text" name="' . MetaBox::GLOBAL_POST_KEY . '[date][]" class="widefat date" value="' . $date . '" />';
echo '</td>';

echo '<td class="col-time-start">';
echo '<input type="time" name="' . MetaBox::GLOBAL_POST_KEY . '[timeStart][]" class="widefat time-start input-timepicker" value="' . $timeStart . '" />';
echo '</td>';

echo '<td class="col-time-end">';
echo '<input type="time" name="' . MetaBox::GLOBAL_POST_KEY . '[timeEnd][]" class="widefat time-end input-timepicker" value="' . $timeEnd . '" />';
echo '</td>';

echo '<td class="col-remove">';
echo '<button class="button button-remove remove-io">x</button>';
echo '</td>';

echo '</tr>';
