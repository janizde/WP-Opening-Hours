<?php

extract( $this->data['attributes'] );

/**
 * Variables defined by extraction
 *
 * @var     $before_widget      string w/ html before widget
 * @var     $after_widget       string w/ html after widget
 * @var     $before_title       string w/ html before title
 * @var     $after_title        string w/ html after title
 *
 * @var     $title              string w/ widget title
 * @var     $text               string w/ status text for widget
 * @var     $next_string        string w/ string for next period
 * @var     $next_period_classes  string w/ classes for next period span
 * @var     $is_open            bool whether set is open or not
 *
 * @var     $classes            string w/ classes for span
 */

echo $before_widget;

if ( ! empty( $title ) ) {
	echo $before_title . $title . $after_title;
}

echo '<span class="' . $classes . '">' . $text . '</span>';

if ( !$is_open && isset($next_string) && is_string($next_string) ) {
	echo '<span class="op-next-period ' . $next_period_classes . '">' . $next_string . '</span>';
}

echo $after_widget;