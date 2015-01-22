<?php
/**
 * OpeningHours: Views: Shortcode: Holidays
 */

use OpeningHours\Entity\Set;

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
 * @var         $holidays           ArrayObject w/ Holiday objects of set
 * @var         $highlight          bool whether highlight active Holiday or not
 * @var         $title              string w/ Widget title
 *
 * @var         $class_holiday      string w/ class for holiday row
 * @var         $class_highlighted  string w/ class for highlighted Holiday
 * @var         $date_format        string w/ PHP date format
 */

echo $before_widget;

    if ( !empty( $title ) )
        echo $before_title . $title . $after_title;

    echo '<table>';

        echo '<tbody>';

        foreach ( $holidays as $holiday ) :

            $highlighted    = ( $highlight and $holiday->isActive() ) ? $class_highlighted : null;

            echo '<tr class="'. $class_holiday .' '. $highlighted .'">';

                echo '<td class="col-name">' . $holiday->getName() . '</td>';

                echo '<td class="col-date-start">'. $holiday->getDateStart()->format( $date_format ) .'</td>';

                echo '<td class="col-date-end">'. $holiday->getDateEnd()->format( $date_format ) .'</td>';

            echo '</tr>';

        endforeach;

        echo '</tbody>';

    echo '</table>';

echo $after_widget;
