<?php

namespace OpeningHours\Entity;

use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours;

use DateTime;
use DateInterval;
use DateTimeZone;
use InvalidArgumentException;

/**
 * Represents a regular opening period
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Entity
 */
class Period {

	/**
	 * weekdays represented by integer. Monday: 0 - Sunday: 7
	 * @type      int
	 */
	protected $weekday;

	/**
	 * DateTime object representing the period's start time in the current week
	 * @type      DateTime
	 */
	protected $timeStart;

	/**
	 * DateTime object representing the period's end time in the current week
	 * @type      DateTime
	 */
	protected $timeEnd;

	/**
	 * Time Difference
	 * DateInterval representing the difference between timeStart and timeEnd
	 * Automatically updated in setters for timeStart and timeEnd
	 *
	 * @type      DateInterval
	 */
	protected $timeDifference;

	/**
	 * Whether this Period is a dummy
	 * @type      bool
	 */
	protected $dummy;

	/**
	 * Constructs a new Period with a config array
	 *
	 * @param     array     $config   The config array for the new Period
	 * @throws    InvalidArgumentException  On validation error
	 */
	public function __construct ( $config = array() ) {
		if ( $config === null or count( $config ) < 1 ) {
			$config = array(
				'weekday'   => null,
				'timeStart' => null,
				'timeEnd'   => null,
				'dummy'     => true
			);
		}

		$this->setUp( $config );

		add_action( I18n::WP_ACTION_TIMEZONE_LOADED, array(
			$this, 'updateDateTimezone'
		) );

		add_action( I18n::WP_ACTION_TIMEZONE_LOADED, array(
			$this, 'updateWeekContext'
		) );
	}

	/**
	 * Sets up the object properties with the provided config
	 *
	 * @param     array     $config   The config array whose data to use
	 * @throws    InvalidArgumentException  On validation error
	 */
	public function setUp ( array $config ) {
		if ( !is_array( $config ) )
			throw new InvalidArgumentException( '$config is not an array in Period' );

		$config = wp_parse_args( $config, array(
			'weekday'   => null,
			'timeStart' => null,
			'timeEnd'   => null,
			'dummy'     => false
		) );

		$this->setWeekday( $config['weekday'] );
		$this->setTimeStart( $config['timeStart'] );
		$this->setTimeEnd( $config['timeEnd'] );
		$this->setDummy( $config['dummy'] );
		$this->updateDateTimezone();
	}

	/**
	 * Checks whether Period is currently open regardless of Holidays and SpecialOpenings
	 *
	 * @param     DateTime  $now
	 * @return    bool      Whether Period is currently open regardless of Holidays and SpecialOpenings
	 */
	public function isOpenStrict ( $now = null ) {
		if ( !$now instanceof DateTime or $now === null )
			$now = I18n::getTimeNow();

		return $this->timeStart <= $now and $now <= $this->timeEnd;
	}

	/**
	 * Checks if Period is currently open also regarding Holidays and SpecialOpenings
	 *
	 * @param     DateTime  $now
	 * @param     int       $setId
	 *
	 * @return    bool
	 */
	public function isOpen ( $now = null, $setId = null ) {
		$set = $setId === null ? OpeningHours::getCurrentSet() : OpeningHours::getSet( $setId );

		if ( !$set instanceof Set )
			return $this->isOpenStrict( $now );

		if ( $set->isHolidayActive( $now ) or $set->isIrregularOpeningActive( $now ) )
			return false;

		return $this->isOpenStrict( $now );
	}

	/**
	 * Checks whether this Period will be regularly open and not overridden due to Holidays or Special Openings
	 *
	 * @param       int     $setId
	 * @return      bool
	 */
	public function willBeOpen ( $setId = null ) {
		return $this->isOpen( $this->timeStart, $setId );
	}

	/** Updates the timeDifference based on timeStart and timeEnd */
	public function updateTimeDifference () {
		if ( !$this->timeStart instanceof DateTime or !$this->timeEnd instanceof DateTime )
			return;

		$this->timeDifference = $this->timeEnd->diff( $this->timeStart );
	}

	/**
	 * Update Week Context
	 * applies week context on start and end time
	 * handles periods that exceed midnight
	 */
	public function updateWeekContext () {
		if ( !$this->timeStart instanceof DateTime or !$this->timeEnd instanceof DateTime )
			return;

		I18n::applyWeekContext( $this->timeStart, $this->weekday );
		I18n::applyWeekContext( $this->timeEnd, $this->weekday );

		if ( $this->timeStart->getTimestamp() >= $this->timeEnd->getTimestamp() )
			$this->timeEnd->add( new DateInterval( 'P1D' ) );
	}

	/**
	 * Update DateTimeZone
	 * updates the DateTimeZone on timeStart and timeEnd
	 */
	public function updateDateTimezone () {
		$timezone = I18n::getDateTimeZone();
		if ( !$timezone instanceof DateTimeZone )
			return;

		/**
		 * Instantiate new DateTime objects to keep time
		 * otherwise time would be converted
		 */
		$this->setTimeStart( new DateTime(
			$this->timeStart->format( I18n::STD_DATE_TIME_FORMAT ),
			$timezone
		) );

		$this->setTimeEnd( new DateTime(
			$this->timeEnd->format( I18n::STD_DATE_TIME_FORMAT ),
			$timezone
		) );
	}

	/**
	 * Sorts period by day and time
	 *
	 * @param     Period    $period1
	 * @param     Period    $period2
	 *
	 * @return    int
	 */
	public static function sortStrategy ( Period $period1, Period $period2 ) {
		if ( $period1->timeStart < $period2->timeStart ) {
			return - 1;
		} elseif ( $period1->timeStart > $period2->timeStart ) {
			return 1;
		}

		return 0;
	}

	/**
	 * Returns JSON string from Period config
	 * @return    string
	 */
	public function __toString () {
		return json_encode( $this->getConfig() );
	}

	/**
	 * Compares this Period to another Period
	 *
	 * @param     Period    $other
	 * @param     bool      $ignoreDay
	 *
	 * @return    bool
	 */
	public function equals ( Period $other, $ignoreDay = false ) {
		$timeFormat = 'Hi';

		if ( !$ignoreDay and $this->weekday != $other->weekday )
			return false;

		if ( $this->timeStart->format( $timeFormat ) != $other->timeStart->format( $timeFormat ) )
			return false;

		if ( $this->timeEnd->format( $timeFormat ) != $other->timeEnd->format( $timeFormat ) )
			return false;

		return true;
	}

	/**
	 * Returns a copy of the current Period and adds up a DateInterval
	 *
	 * @param     DateInterval  $offset The offset to add to the copy
	 * @return    Period
	 */
	public function getCopy ( DateInterval $offset ) {
		$period = clone $this;
		$period->timeStart->add( $offset );
		$period->timeEnd->add( $offset );
		return $period;
	}

	/**
	 * Factory for dummy Period
	 * @return    Period
	 */
	public static function getDummyPeriod() {
		return new Period( array(
			'dummy' => true
		) );
	}

	/**
	 * Generates config array representing this Period instance
	 * @return    array
	 */
	public function getConfig() {
		return array(
			'weekday'   => $this->weekday,
			'timeStart' => $this->timeStart->format( I18n::STD_DATE_TIME_FORMAT ),
			'timeEnd'   => $this->timeEnd->format( I18n::STD_DATE_TIME_FORMAT ),
			'dummy'     => $this->dummy
		);
	}

	/**
	 * Returns the formatted string with start and end time for this Period
	 *
	 * @param     string    $timeFormat   Custom time format
	 * @return    string
	 */
	public function getFormattedTimeRange( $timeFormat = null ) {
		return $this->getTimeStart( true, $timeFormat ) . ' – ' . $this->getTimeEnd( true, $timeFormat );
	}

	/**
	 * Getter: Weekday
	 * @return    int
	 */
	public function getWeekday() {
		return $this->weekday;
	}

	/**
	 * Setter: Weekday
	 * @param     int       $weekday
	 */
	public function setWeekday( $weekday ) {
		$this->weekday = $weekday;
	}

	/**
	 * Getter: Time Start
	 *
	 * @param     bool      $formatted    Whether to format to time
	 * @param     string    $timeFormat   Custom time format. Only works when $formatted is true
	 * @return    DateTime|string
	 */
	public function getTimeStart( $formatted = false, $timeFormat = null ) {
		return ( $formatted and $this->timeStart instanceof DateTime )
			? $this->timeStart->format( ( $timeFormat != null ) ? $timeFormat : I18n::getTimeFormat() )
			: $this->timeStart;
	}

	/**
	 * Setter: Time Start
	 *
	 * @param     DateTime|string  $timeStart
	 */
	public function setTimeStart( $timeStart ) {
		$this->timeStart = is_string( $timeStart )
			? new DateTime( $timeStart, I18n::getDateTimeZone() )
			: I18n::applyTimeZone( $timeStart );

		$this->updateTimeDifference();
		$this->updateWeekContext();
	}

	/**
	 * Getter: Time End
	 *
	 * @param     bool    $formatted
	 * @param     string  $time_format
	 *
	 * @return    DateTime|string
	 */
	public function getTimeEnd ( $formatted = false, $time_format = null ) {
		return ( $formatted and $this->timeEnd instanceof DateTime )
			? $this->timeEnd->format( ( $time_format != null ) ? $time_format : I18n::getTimeFormat() )
			: $this->timeEnd;
	}

	/**
	 * Setter: Time End
	 * @param     DateTime|string $timeEnd
	 */
	public function setTimeEnd ( $timeEnd ) {
		$this->timeEnd = is_string( $timeEnd )
			? new DateTime( $timeEnd, I18n::getDateTimeZone() )
			: I18n::applyTimeZone( $timeEnd );

		$this->updateTimeDifference();
		$this->updateWeekContext();
	}

	/**
	 * Getter: Is Dummy
	 * @return     bool
	 */
	public function isDummy() {
		return $this->dummy;
	}

	/**
	 * Setter: Is Dummy
	 * @param     bool      $dummy
	 */
	public function setDummy( $dummy ) {
		$this->dummy = $dummy;
	}
}