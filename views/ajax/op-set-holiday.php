<?php
/**
 * Opening Hours: Views: Ajax: OP Set Holiday
 */

use OpeningHours\Entity\Holiday;
use OpeningHours\Module\CustomPostType\MetaBox\Holidays;
use OpeningHours\Module\I18n;

/**
 * pre-defined variables
 *
 * @var         $holiday        Holiday object
 */

$name       = $holiday->getName();
$dateStart  = ( $holiday->isDummy() ) ? null : $holiday->getDateStart()->format( I18n::STD_DATE_FORMAT );
$dateEnd    = ( $holiday->isDummy() ) ? null : $holiday->getDateEnd()->format( I18n::STD_DATE_FORMAT );

echo '<tr class="op-holiday">';

    echo '<td class="col-name">';
        echo '<input type="text" name="'. Holidays::GLOBAL_POST_KEY .'[name][]" class="widefat" value="'. $name .'" />';
    echo '</td>';

    echo '<td class="col-date-start">';
        echo '<input type="date" name="'. Holidays::GLOBAL_POST_KEY .'[dateStart][]" class="widefat" value="'. $dateStart .'" />';
    echo '</td>';

    echo '<td class="col-date-end">';
        echo '<input type="date" name="'. Holidays::GLOBAL_POST_KEY .'[dateEnd][]" class="widefat" value="'. $dateEnd .'" />';
    echo '</td>';

    echo '<td class="col-remove">';
        echo '<button class="button button-remove remove-holiday">x</button>';
    echo '</td>';

echo '</tr>';
