<?php
if ( class_exists( 'OpeningHours\OpeningHours' ) ) {
	$GLOBALS['op'] = OpeningHours\OpeningHours::getInstance();
	register_activation_hook( __FILE__, array( $GLOBALS['op'], 'activate' ) );
	register_deactivation_hook( __FILE__, array( $GLOBALS['op'], 'deactivate' ) );
  require_once __DIR__ . '/functions.php';
}