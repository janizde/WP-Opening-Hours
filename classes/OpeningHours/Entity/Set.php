<?php

namespace OpeningHours\Entity;

use OpeningHours\Util\ArrayObject;
use OpeningHours\Module\I18n;
use OpeningHours\Module\CustomPostType\Set as SetCpt;
use OpeningHours\Module\CustomPostType\MetaBox\Holidays as HolidaysMetaBox;
use OpeningHours\Module\CustomPostType\MetaBox\IrregularOpenings as IrregularOpeningsMetaBox;

use OpeningHours\Util\Dates;
use OpeningHours\Util\Persistence;
use OpeningHours\Util\Weekdays;
use WP_Post;
use DateTime;
use DateInterval;
use InvalidArgumentException;

/**
 * Represents a Set of opening hours
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Entity
 */
class Set {

	/**
	 * Constants
	 */
	const WP_ACTION_BEFORE_SETUP = 'op_set_before_setup';

	/**
	 * Collection of all Periods in the Set
	 * @var       ArrayObject
	 */
	protected $periods;

	/**
	 * Collection of all Holidays in the Set
	 * @var       ArrayObject
	 */
	protected $holidays;

	/**
	 * Collection of all Irregular Openings in the Set
	 * @var       ArrayObject
	 */
	protected $irregularOpenings;

	/**
	 * The Id of the set
	 * @var       int
	 */
	protected $id;

	/**
	 * The WP_Post instance representing the set
	 * @var       WP_Post
	 */
	protected $post;

	/**
	 * The id of the parent set.
	 * Id of this set if the set does not have a parent
	 *
	 * @var       int
	 */
	protected $parentId;

	/**
	 * The WP_Post instance representing the parent set
	 * This Set's post if the set does not have a parent
	 *
	 * @var       WP_Post
	 */
	protected $parentPost;

	/**
	 * The set description
	 * @var       string
	 */
	protected $description;

	/**
	 * Constructs a new Set with a WP_Post
	 *
	 * @param     WP_Post|int   $post
	 * @throws    InvalidArgumentException  If the post is invalid
	 */
	public function __construct( $post ) {
		$this->periods = new ArrayObject();
		$this->holidays = new ArrayObject();
		$this->irregularOpenings = new ArrayObject();

		if ( !is_int( $post ) and !$post instanceof WP_Post )
			throw new InvalidArgumentException( sprintf( 'Argument one for __construct has to be of type WP_Post or int. %s given', gettype( $post ) ) );

		$post = get_post( $post );

		$this->id = $post->ID;
		$this->post = $post;
		$this->parentId = $post->ID;
		$this->parentPost = $post;

		$this->setUp();
	}

	/** Sets up the Set instance */
	public function setUp() {
		$childPosts = get_posts( array(
			'post_type'   => SetCpt::CPT_SLUG,
			'post_parent' => $this->getId()
		) );

		foreach ( $childPosts as $post ) {
			if ( self::postMatchesCriteria( $post ) ) {
				$this->id   = $post->ID;
				$this->post = $post;
				break;
			}
		}

		/** Action: op_set_before_setup */
		do_action( self::WP_ACTION_BEFORE_SETUP, $this );

		$persistence = new Persistence( $this->post );
		$this->periods = ArrayObject::createFromArray( $persistence->loadPeriods() );
		$this->holidays = ArrayObject::createFromArray( $persistence->loadHolidays() );
		$this->irregularOpenings = ArrayObject::createFromArray( $persistence->loadIrregularOpenings() );

		$post_detail_description        = get_post_detail( 'description', $this->id );
		$post_parent_detail_description = get_post_detail( 'description', $this->parentId );

		if ( !empty( $post_detail_description ) ) {
			$this->description = $post_detail_description;
		} elseif ( !empty( $post_parent_detail_description ) ) {
			$this->description = $post_parent_detail_description;
		}
	}

	/**
	 * Checks if the specified post representing a set matches the criteria
	 *
	 * @param     WP_Post   $post   The child post
	 * @return    bool              Whether the child post matches the criteria
	 */
	public static function postMatchesCriteria ( WP_Post $post ) {
		$detailDateStart = get_post_detail( 'date-start', $post->ID );
		$detailDateEnd = get_post_detail( 'date-end', $post->ID );
		$detailWeekScheme = get_post_detail( 'week-scheme', $post->ID );

		$detailDateStart = ( !empty( $detailDateStart ) ) ? new DateTime( $detailDateStart, Dates::getTimezone() ) : null;
		$detailDateEnd   = ( !empty( $detailDateEnd ) ) ? new DateTime( $detailDateEnd, Dates::getTimezone() ) : null;
		if ( $detailDateEnd !== null )
			$detailDateEnd->setTime( 23, 59, 59 );

		if ( $detailDateStart == null and $detailDateEnd == null and ( $detailWeekScheme == 'all' or empty( $detailWeekScheme ) ) )
			return false;

		$now = Dates::getNow();

		if ( $detailDateStart != null and $now < $detailDateStart )
			return false;

		if ( $detailDateEnd != null and $now > $detailDateEnd )
			return false;

		$week_number_modulo = (int) $now->format( 'W' ) % 2;

		if ( $detailWeekScheme == 'even' and $week_number_modulo === 1 )
			return false;

		if ( $detailWeekScheme == 'odd' and $week_number_modulo === 0 )
			return false;

		return true;
	}

	/**
	 * Checks if this set is a parent set
	 * @return    bool      Whether this set is a parent set
	 */
	public function isParent() {
		return $this->id === $this->parentId;
	}

	/** Adds dummy periods to the set */
	public function addDummyPeriods() {
		for ( $i = 0; $i < 7; $i++ ) {
			if ( count( $this->getPeriodsByDay( $i ) ) < 1 ) {
				$newPeriod = Period::createDummy( $i );
				$this->periods->append( $newPeriod );
			}
		}
	}

	/**
	 * Only evaluates standard opening periods
	 * @param     DateTime  $now    Custom time
	 * @return    bool              Whether venue is open due to regular Opening Hours
	 */
	public function isOpenOpeningHours ( $now = null ) {
		foreach ( $this->periods as $period )
			if ( $period->isOpen( $now, $this ) )
				return true;

		return false;
	}

	/**
	 * Checks if any holiday in set is currently active
	 * @param     DateTime  $now      Custom time
	 * @return    bool                Whether any holiday in the set is currently active
	 */
	public function isHolidayActive ( $now = null ) {
		return $this->getActiveHoliday( $now ) instanceof Holiday;
	}

	/**
	 * Returns the first active holiday or null if none is active
	 * @param     DateTime  $now      Custom Time
	 * @return    Holiday             The first active Holiday or null if none is active
	 */
	public function getActiveHoliday ( DateTime $now = null ) {
		foreach ( $this->holidays as $holiday )
			if ( $holiday->isActive( $now ) )
				return $holiday;

		return null;
	}

	/**
	 * Returns the first active holiday on the specified weekday
	 * @param     int       $weekday  weekday number 0-6
	 * @param     DateTime  $now      custom DateTime. The next day of the specified weekday with be used
	 * @return    Holiday             The first active holiday on the specified weekday
	 */
	public function getActiveHolidayOnWeekday ( $weekday, DateTime $now = null ) {
		if ( $now == null )
			$now = Dates::getNow();

		$now = clone $now;
		$date = Dates::applyWeekContext( $now, $weekday, $now );
		return $this->getActiveHoliday( $date );
	}

	/**
	 * Checks whether any irregular opening is currently active (based on the date)
	 *
	 * @param     DateTime  $now      Custom time
	 *
	 * @return    bool                whether any irregular opening is currently active
	 */
	public function isIrregularOpeningActive( DateTime $now = null ) {
		return $this->getActiveIrregularOpening( $now ) instanceof IrregularOpening;
	}

	/**
	 * Evaluates all aspects determining whether the venue is currently open or not
	 *
	 * @param     DateTime  $now      Custom time
	 *
	 * @return    bool                Whether venue is currently open or not
	 */
	public function isOpen( DateTime $now = null ) {
		if ( $this->isHolidayActive( $now ) )
			return false;

		if ( $this->isIrregularOpeningActive( $now ) ) {
			$io = $this->getActiveIrregularOpening( $now );
			return $io->isOpen( $now );
		}

		return $this->isOpenOpeningHours( $now );
	}

	/**
	 * Returns the first open Period after $now
	 *
	 * @param     DateTime  $now      The date context for the Periods. default: current datetime
	 * @return    Period    The next open period or null if no period has been found
	 */
	public function getNextOpenPeriod ( DateTime $now = null ) {
		$periods = $this->periods;

		if ( $now != null ) {
			$periods = new ArrayObject();
			foreach ( $this->periods as $period ) {
				$periods->append( $period->getCopyInDateContext( $now ) );
			}
		}

		$periods->uasort( array( '\OpeningHours\Entity\Period', 'sortStrategy' ) );

		if ( count( $periods ) < 1 )
			return null;

		foreach ( $periods as $period ) {
			if ( $period->compareToDateTime( $now ) <= 0 )
				continue;

			if ( $period->willBeOpen( $this ) )
				return $period;
		}

		for ( $weekOffset = 1; true; $weekOffset++ ) {
			if ( $weekOffset > 52 ) {
				return null;
			}

			$timeDifference = new DateInterval( 'P' . 7 * $weekOffset . 'D' );

			foreach ( $this->periods as $period ) {
				$newPeriod = clone $period;
				$newPeriod->getTimeStart()->add( $timeDifference );
				$newPeriod->getTimeEnd()->add( $timeDifference );

				if ( $newPeriod->willBeOpen( $this ) ) {
					return $newPeriod;
				}
			}
		}

		return null;
	}

	/**
	 * Getter: Periods
	 * @return    ArrayObject
	 */
	public function getPeriods() {
		return $this->periods;
	}

	/**
	 * Getter: Periods By Day
	 * @param     int[]|int $days
	 *
	 * @return    Period[]
	 */
	public function getPeriodsByDay ( $days ) {
		if ( !is_array( $days ) and !is_numeric( $days ) )
			throw new InvalidArgumentException( sprintf( 'Argument 1 of getPeriodsByDay must be integer or array. %s given.', gettype( $days ) ) );

		if ( !is_array( $days ) )
			$days = array( $days );

		$periods = array();
		foreach ( $this->periods as $period )
			if ( in_array( $period->getWeekday(), $days ) )
				$periods[] = $period;

		return $periods;
	}

	/**
	 * Getter: (all) Periods Grouped By Day
	 * @return       Period[][]
	 */
	public function getPeriodsGroupedByDay() {
		$periods = array();
		for ( $i = 0; $i < 7; $i++ )
			$periods[ $i ] = $this->getPeriodsByDay( $i );

		return $periods;
	}

	/**
	 * Getter: (all) Periods Grouped By Day and Compressed
	 * Applies some kind of array_unique on the array. Days with same Periods are uniqued to one array element with a comma separated string containing the day IDs
	 *
	 * @return    Period[][]
	 */
	public function getPeriodsGroupedByDayCompressed() {
		$periodsArray = $this->getPeriodsGroupedByDay();
		$newPeriodsArray = array();
		$compressed = array();
		$days = range( 0, 6 );

		foreach ( $days as $day1 ) {
			if ( in_array( $day1, $compressed ) )
				continue;

			$keys = array( $day1 );
			foreach ( $days as $day2 ) {
				if ( $day1 == $day2 )
					continue;

				if ( in_array( $day2, $compressed ) )
					continue;

				if ( $this->daysEqual( $day1, $day2 ) )
					$keys[] = $day2;
			}

			$newPeriodsArray[ implode( ',', $keys ) ] = $periodsArray[ $day1 ];
			$compressed = array_merge( $compressed, $keys );
		}

		return $newPeriodsArray;
	}

	/**
	 * Returns first active irregular opening on that day
	 * Only evaluates the date of $now and not the time
	 *
	 * @param     DateTime  $now      Custom time
	 *
	 * @return    IrregularOpening
	 */
	public function getActiveIrregularOpening( DateTime $now = null ) {
		foreach ( $this->irregularOpenings as $io )
			if ( $io->isActiveOnDay( $now ) )
				return $io;

		return null;
	}

	/**
	 * Returns first active irregular opening on a specific weekday
	 *
	 * @param     int       $weekday  weekday number, 0-6
	 * @param     DateTime  $now      custom time
	 *
	 * @return    IrregularOpening    The first active irregular opening fpr the current weekday
	 */
	public function getActiveIrregularOpeningOnWeekday ( $weekday, DateTime $now = null ) {
		$date = Dates::applyWeekContext( new DateTime('now'), $weekday, $now );
		return $this->getActiveIrregularOpening( $date );
	}

	/**
	 * Checks if two days have equal Periods
	 *
	 * @param     int       $day1
	 * @param     int       $day2
	 * @param     Period[]  $periodsByDay
	 *
	 * @return    bool
	 */
	public function daysEqual( $day1, $day2, array $periodsByDay = null ) {
		if ( $day1 === $day2 )
			return true;

		if ( $periodsByDay === null or ! is_array( $periodsByDay ) )
			$periodsByDay = $this->getPeriodsGroupedByDay();

		if ( count( $periodsByDay[ $day1 ] ) < 1 and count( $periodsByDay[ $day2 ] ) < 1 )
			return true;

		if ( count( $periodsByDay[ $day1 ] ) != count( $periodsByDay[ $day2 ] ) )
			return false;

		for ( $i = 0; $i < count( $periodsByDay[ $day1 ] ); $i++ ) {
			$period1 = $periodsByDay[ $day1 ][ $i ];
			$period2 = $periodsByDay[ $day2 ][ $i ];

			if ( !$period1->equals( $period2, true ) )
				return false;
		}

		return true;
	}

	/**
	 * Getter: Holidays
	 * @return    ArrayObject
	 */
	public function getHolidays () {
		return $this->holidays;
	}

	/**
	 * Getter: Irregular Openings
	 * @return    ArrayObject
	 */
	public function getIrregularOpenings() {
		return $this->irregularOpenings;
	}

	/**
	 * Getter: Id
	 * @return    int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Setter: Id
	 * @param     int       $id
	 */
	public function setId( $id ) {
		$this->id = $id;
	}

	/**
	 * Getter: Post
	 * @return    WP_Post
	 */
	public function getPost() {
		return $this->post;
	}

	/**
	 * Setter: Post
	 * @param     WP_Post   $post
	 */
	public function setPost( WP_Post $post ) {
		$this->post = $post;
	}

	/**
	 * Getter: Parent Id
	 * @return    int
	 */
	public function getParentId() {
		return $this->parentId;
	}

	/**
	 * Getter: Parent Post
	 * @return    WP_Post
	 */
	public function getParentPost() {
		return ( !$this->hasParent() and !$this->parentPost instanceof WP_Post )
			? $this->post
			: $this->parentPost;
	}

	/**
	 * Getter: Description
	 * @return    bool
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Getter: Has Parent
	 * @return    bool
	 */
	public function hasParent() {
		return $this->id !== $this->parentId;
	}
}