<?php
/**
 * Opening Hours: Views: Ajax: OP Set Holiday
 */

use OpeningHours\Entity\Holiday;
use OpeningHours\Module\CustomPostType\MetaBox\Holidays;
use OpeningHours\Util\Dates;

/**
 * pre-defined variables
 *
 * @var         $holiday        Holiday object
 */

$name      = $holiday->getName();
$dateStart = ( $holiday->isDummy() ) ? null : $holiday->getDateStart()->format( Dates::STD_DATE_FORMAT );
$dateEnd   = ( $holiday->isDummy() ) ? null : $holiday->getDateEnd()->format( Dates::STD_DATE_FORMAT );

echo '<tr class="op-holiday">';

echo '<td class="col-name">';
echo '<input type="text" name="' . Holidays::POST_KEY . '[name][]" class="widefat" value="' . $name . '" />';
echo '</td>';

echo '<td class="col-date-start">';
echo '<input type="text" name="' . Holidays::POST_KEY . '[dateStart][]" class="widefat date-start input-gray" value="' . $dateStart . '" />';
echo '</td>';

echo '<td class="col-date-end">';
echo '<input type="text" name="' . Holidays::POST_KEY . '[dateEnd][]" class="widefat date-end input-gray" value="' . $dateEnd . '" />';
echo '</td>';

echo '<td class="col-remove">';
echo '<button class="button button-remove remove-holiday has-icon"><i class="dashicons dashicons-no-alt"></i></button>';
echo '</td>';

echo '</tr>';
