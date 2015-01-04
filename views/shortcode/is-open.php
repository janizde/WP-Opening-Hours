<?php
/**
 *  Opening Hours: Template: Shortcode: Is Open
 */

/**
 * @var     $before_widget      string w/ html before widget
 * @var     $after_widget       string w/ html after widget
 * @var     $before_title       string w/ html before title
 * @var     $after_title        string w/ html after title
 *
 * @var     $classes            string w/ classes for span
 */

echo $before_widget;

  if ( !empty( $title ) )
    echo $before_title . $title . $after_title;

  echo '<span class="'. $classes .'">'. $text .'</span>';

echo $after_widget;
