<?php
/**
 * Opening Hours: Views: Ajax: OP Set Holiday
 */

use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Module\CustomPostType\MetaBox\IrregularOpenings as MetaBox;
use OpeningHours\Module\I18n;
use OpeningHours\Util\Dates;

/**
 * pre-defined variables
 *
 * @var         $io        IrregularOpening object
 */

$name      = $io->getName();
$date      = ( $io->isDummy() ) ? null : $io->getDate()->format( Dates::STD_DATE_FORMAT );
$timeStart = ( $io->isDummy() ) ? null : $io->getTimeStart()->format( Dates::STD_TIME_FORMAT );
$timeEnd   = ( $io->isDummy() ) ? null : $io->getTimeEnd()->format( Dates::STD_TIME_FORMAT );

echo '<tr class="op-irregular-opening">';

echo '<td class="col-name">';
echo '<input type="text" name="' . MetaBox::POST_KEY . '[name][]" class="widefat name" value="' . $name . '" />';
echo '</td>';

echo '<td class="col-date">';
echo '<input type="text" name="' . MetaBox::POST_KEY . '[date][]" class="widefat date input-gray" value="' . $date . '" />';
echo '</td>';

echo '<td class="col-time-start">';
echo '<input type="text" name="' . MetaBox::POST_KEY . '[timeStart][]" class="widefat time-start input-timepicker input-gray" value="' . $timeStart . '" />';
echo '</td>';

echo '<td class="col-time-end">';
echo '<input type="text" name="' . MetaBox::POST_KEY . '[timeEnd][]" class="widefat time-end input-timepicker input-gray" value="' . $timeEnd . '" />';
echo '</td>';

echo '<td class="col-remove">';
echo '<button class="button button-remove remove-io has-icon"><i class="dashicons dashicons-no-alt"></i></button>';
echo '</td>';

echo '</tr>';
