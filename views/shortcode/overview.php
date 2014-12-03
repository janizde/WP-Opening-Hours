<?php

  use OpeningHours\Module\I18n;

  extract( $attributes );

  echo $before_widget;

    if ( $title )
      echo $before_title . $title . $after_title;

    foreach ( $sets as $set ) :

      echo '<table class="op-table op-table-overview '. $table_classes .'" id="'. $table_id_prefix . $set->getId() .'">';

      foreach ( $set->getPeriodsGroupedByDay() as $day => $periods ) :

        $highlighted_day  = ( $highlight == 'day' and I18n::isToday( $day ) ) ? $highlighted_day_class : null;

        echo '<tr class="op-row op-row-day '. $row_classes .' '. $highlighted_day .'">';

          echo '<th scope="row" class="op-cell op-cell-heading '. $cell_heading_classes .' '. $cell_classes .'">';
            echo $weekdays[ $day ];
          echo '</th>';

          echo '<td class="op-cell op-cell-periods '. $cell_periods_classes .' '. $cell_classes .'">';

          foreach ( $periods as $period ) :
            $highlighted_period   = ( $highlight == 'period' and $period->isOpen() ) ? $highlighted_period_class : null;
            echo '<span class="op-period-time '. $span_period_classes .' '. $highlighted_period .'">' . $period->getFormattedTimeRange() . '</span>';
          endforeach;

          echo '</td>';

        echo '</tr>';

      endforeach;

      echo '</table>';

    endforeach;

  echo $after_widget;

?>
