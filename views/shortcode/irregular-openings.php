<?php
/**
 * OpeningHours: Views: Shortcode: Holidays
 */

use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Set;

/**
 * @var         $attributes         array of attributes
 */

extract( $attributes );

/**
 * variables defined by extract
 *
 * @var         $before_widget      string w/ HTML markup before Widget
 * @var         $after_widget       string w/ HTML markup after Widget
 * @var         $before_title       string w/ HTML markup before title
 * @var         $after_title        string w/ HTML markup after title
 *
 * @var         $set                Set object
 * @var         $irregular_openings ArrayObject w/ IrregularOpening objects of set
 * @var         $highlight          bool whether highlight active Holiday or not
 * @var         $title              string w/ Widget title
 *
 * @var         $class_io           string w/ class for irregular opening row
 * @var         $class_highlighted  string w/ class for highlighted IrregularOpening
 * @var         $date_format        string w/ PHP date format
 * @var         $time_format        string w/ PHP time format
 */

if ( !count( $irregular_openings ) )
	return;

echo $before_widget;

if ( ! empty( $title ) ) {
	echo $before_title . $title . $after_title;
}

echo '<table class="op-irregular-openings">';

echo '<tbody>';

foreach ( $irregular_openings as $io ) :

	/**
	 * @var         $io         IrregularOpening object
	 */

	$highlighted = ( $highlight and $io->isActive() ) ? $class_highlighted : null;

	echo '<tr class="' . $class_io . ' ' . $highlighted . '">';

	echo '<td class="col-name">' . $io->getName() . '</td>';

	echo '<td class="col-date">' . $io->getDate()->format( $date_format ) . '</td>';

	echo '<td class="col-time">' . $io->getFormattedTimeRange( $time_format ) . '</td>';

	echo '</tr>';

endforeach;

echo '</tbody>';

echo '</table>';

echo $after_widget;
