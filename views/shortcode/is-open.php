<?php
/**
 *  Opening Hours: Template: Shortcode: Is Open
 */

echo $before_widget;

  if ( !empty( $title ) )
    echo $before_title . $title . $after_title;

  echo '<label class="'. $classes .'">'. $text .'</label>';

echo $after_widget;
?>
