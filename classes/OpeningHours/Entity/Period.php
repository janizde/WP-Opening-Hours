<?php

namespace OpeningHours\Entity;

use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours;

use DateTime;
use DateInterval;
use DateTimeZone;
use InvalidArgumentException;
use OpeningHours\Util\Dates;

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
	 * Whether this Period is a dummy
	 * @type      bool
	 */
	protected $dummy;

	/**
	 * Constructs a new Period with a config array
	 *
	 * @param     int       $weekday    Weekday represented by integer. Monday: 0 - Sunday: 7
	 * @param     string    $timeStart  The start time in standard time format
	 * @param     string    $timeEnd    The end time in standard time format
	 * @param     bool      $dummy      Whether this period is a dummy. default: false
	 *
	 * @throws    InvalidArgumentException  On validation error
	 */
	public function __construct ( $weekday, $timeStart, $timeEnd, $dummy = false ) {
		if ( !is_int( $weekday ) or $weekday < 0 or $weekday > 6 )
			throw new InvalidArgumentException( sprintf('$weekday must be an integer between 0 and 6. got %s', (string) $weekday) );

		if ( !Dates::isValidTime( $timeStart ) )
			throw new InvalidArgumentException( sprintf('$timeStart must be in standard time format %s. got %s', Dates::STD_TIME_FORMAT, $timeStart) );

		if ( !Dates::isValidTime( $timeEnd ) )
			throw new InvalidArgumentException( sprintf('$timeEnd must be in standard time format %s. got %s', Dates::STD_TIME_FORMAT, $timeEnd) );

		$this->weekday = $weekday;
		$this->timeStart = Dates::applyWeekContext( new DateTime( $timeStart, Dates::getTimezone() ), $weekday );
		$this->timeEnd = Dates::applyWeekContext( new DateTime( $timeEnd, Dates::getTimezone() ), $weekday );
		$this->dummy = $dummy;

		if ( Dates::compareTime( $this->timeStart, $this->timeEnd ) >= 0 )
			$this->timeEnd->add( new DateInterval('P1D') );
	}

	/**
	 * Checks whether Period is currently open regardless of Holidays and SpecialOpenings
	 *
	 * @param     DateTime  $now
	 * @return    bool      Whether Period is currently open regardless of Holidays and SpecialOpenings
	 */
	public function isOpenStrict ( $now = null ) {
		if ( !$now instanceof DateTime )
			$now = Dates::getNow();

		return $this->timeStart <= $now and $now <= $this->timeEnd;
	}

	/**
	 * Checks if Period is currently open also regarding Holidays and SpecialOpenings
	 *
	 * @param     DateTime  $now
	 * @param     Set       $set      The set in whose context to determine the opening status of this Period
	 *
	 * @return    bool
	 */
	public function isOpen ( $now = null, Set $set = null ) {
		if ( $set == null )
			$set = OpeningHours::getCurrentSet();

		if ( !$set instanceof Set )
			return $this->isOpenStrict( $now );

		if ( $set->isHolidayActive( $now ) or $set->isIrregularOpeningActive( $now ) )
			return false;

		return $this->isOpenStrict( $now );
	}

	/**
	 * Checks whether this Period will be regularly open and not overridden due to Holidays or Special Openings
	 *
	 * @param       Set     $set
	 * @return      bool
	 */
	public function willBeOpen ( Set $set = null ) {
		return $this->isOpen( $this->timeStart, $set );
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
		} else {
			return 0;
		}
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
	 * Factory for dummy Period
	 * @param     int       $weekday  The weekday to use for the dummy period
	 * @return    Period
	 */
	public static function createDummy ( $weekday = 0 ) {
		return new Period( $weekday, '00:00', '00:00', true );
	}

	/**
	 * Returns the formatted string with start and end time for this Period
	 *
	 * @param     string    $timeFormat   Custom time format
	 * @return    string
	 */
	public function getFormattedTimeRange( $timeFormat = null ) {
		if ( $timeFormat == null )
			$timeFormat = Dates::getTimeFormat();

		return $this->timeStart->format( $timeFormat ) . ' - ' . $this->timeEnd->format( $timeFormat );
	}

	/**
	 * Getter: Weekday
	 * @return    int
	 */
	public function getWeekday() {
		return $this->weekday;
	}

	/**
	 * Getter: Time Start
	 * @return    DateTime
	 */
	public function getTimeStart () {
		return $this->timeStart;
	}

	/**
	 * Getter: Time End
	 * @return    DateTime
	 */
	public function getTimeEnd () {
		return $this->timeEnd;
	}

	/**
	 * Getter: Is Dummy
	 * @return     bool
	 */
	public function isDummy() {
		return $this->dummy;
	}
}