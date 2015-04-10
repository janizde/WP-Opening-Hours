<?php
/**
 *  Opening Hours: Module: Shortcode: Overview
 */

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Set;
use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours;

class Overview extends AbstractShortcode {

	/**
	 *  Init
	 *
	 * @access     protected
	 */
	protected function init() {

		$this->setShortcodeTag( 'op-overview' );

		$default_attributes = array(
			'before_title'             => '<h3 class="op-overview-title">',
			'after_title'              => '</h3>',
			'before_widget'            => '<div class="op-overview-shortcode">',
			'after_widget'             => '</div>',
			'set_id'                   => 0,
			'title'                    => null,
			'show_closed_days'         => false,
			'show_description'         => true,
			'highlight'                => 'nothing',
			'compress'                 => false,
			'short'                    => false,
			'include_io'               => false,
			'include_holidays'         => false,
			'caption_closed'           => __( 'Closed', I18n::TEXTDOMAIN ),
			'table_classes'            => null,
			'row_classes'              => null,
			'cell_classes'             => null,
			'cell_heading_classes'     => null,
			'cell_periods_classes'     => null,
			'cell_description_classes' => 'op-set-description',
			'highlighted_period_class' => 'highlighted',
			'highlighted_day_class'    => 'highlighted',
			'table_id_prefix'          => 'op-table-set-',
			'time_format'              => I18n::getTimeFormat()
		);

		$this->setDefaultAttributes( $default_attributes );

		$valid_attribute_values = array(
			'highlight'        => array( 'nothing', 'period', 'day' ),
			'show_closed_day'  => array( false, true ),
			'show_description' => array( true, false ),
			'include_io'       => array( false, true ),
			'include_holidays' => array( false, true )
		);

		$this->setValidAttributeValues( $valid_attribute_values );

		$this->setTemplatePath( 'shortcode/overview.php' );

	}

	/**
	 * Shortcode
	 *
	 * @access       public
	 *
	 * @param        array $attributes
	 */
	public function shortcode( array $attributes ) {

		extract( $attributes );

		if ( ! isset( $set_id ) or ! is_numeric( $set_id ) or $set_id == 0 ) :
			trigger_error( "Set id not properly set in Opening Hours Overview shortcode" );

			return;
		endif;

		$set_id = (int) $set_id;

		$set = OpeningHours::getSet( $set_id );

		if ( ! $set instanceof Set ) :
			trigger_error( sprintf( "Set with id %d does not exist", $set_id ) );

			return;
		endif;

		$attributes['set']      = $set;
		$attributes['weekdays'] = I18n::getWeekdaysNumeric();

		echo $this->renderShortcodeTemplate( $attributes );

	}

	/**
	 * Render Irregular Opening
	 *
	 * @access        public
	 * @static
	 *
	 * @param         IrregularOpening  $io
	 * @param         array             $attributes
	 */
	public static function renderIrregularOpening ( IrregularOpening $io, array $attributes ) {

		$name   = $io->getName();
		$date   = $io->getTimeStart()->format( I18n::getDateFormat() );

		$heading  = sprintf( '%s (%s)', $name, $date );

		$now = I18n::getTimeNow();
		$highlighted  = ( $attributes['highlight'] == 'period' and $io->getTimeStart() <= $now and $now <= $io->getTimeEnd() ) ? $attributes['highlighted_period_class'] : null;

		echo '<span class="op-period-time irregular-opening '. $highlighted .'">'. $heading .'</span>';

		$time_start   = $io->getTimeStart()->format( $attributes['time_format'] );
		$time_end     = $io->getTimeEnd()->format( $attributes['time_format'] );

		$period   = sprintf( '%s – %s', $time_start, $time_end );

		echo '<span class="op-period-time '. $highlighted .' '. $attributes['span_period_classes'] .'">'. $period .'</span>';

	}

	/**
	 * Render Holiday
	 *
	 * @access        public
	 * @static
	 *
	 * @param         Holiday         $holiday
	 * @param         array           $attributes
	 */
	public static function renderHoliday ( Holiday $holiday, array $attributes ) {

		echo '<span class="op-period-time holiday '. $attributes['span_period_classes'] .'">'. $holiday->getName() .'</span>';

	}

}