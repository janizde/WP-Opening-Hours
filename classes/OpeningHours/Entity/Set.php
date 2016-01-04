<?php

namespace OpeningHours\Entity;

use OpeningHours\Misc\ArrayObject;
use OpeningHours\Module\I18n;
use OpeningHours\Module\CustomPostType\Set as SetCpt;
use OpeningHours\Module\CustomPostType\MetaBox\Holidays as HolidaysMetaBox;
use OpeningHours\Module\CustomPostType\MetaBox\IrregularOpenings as IrregularOpeningsMetaBox;

use OpeningHours\Util\Dates;
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
	 * @type      ArrayObject
	 */
	protected $periods;

	/**
	 * Collection of all Holidays in the Set
	 * @type      ArrayObject
	 */
	protected $holidays;

	/**
	 * Collection of all Irregular Openings in the Set
	 * @type      ArrayObject
	 */
	protected $irregularOpenings;

	/**
	 * The Id of the set
	 * @type      int
	 */
	protected $id;

	/**
	 * The WP_Post instance representing the set
	 * @type      WP_Post
	 */
	protected $post;

	/**
	 * The id of the parent set.
	 * Id of this set if the set does not have a parent
	 *
	 * @type      int
	 */
	protected $parentId;

	/**
	 * The WP_Post instance representing the parent set
	 * This Set's post if the set does not have a parent
	 *
	 * @type      WP_Post
	 */
	protected $parentPost;

	/**
	 * The set description
	 * @type      string
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
			if ( self::childMatchesCriteria( $post ) ) {
				$this->id   = $post->ID;
				$this->post = $post;
				break;
			}
		}

		/** Action: op_set_before_setup */
		do_action( self::WP_ACTION_BEFORE_SETUP, $this );

		$this->loadPeriods();
		$this->loadHolidays();
		$this->loadIrregularOpenings();

		$post_detail_description        = get_post_detail( 'description', $this->id );
		$post_parent_detail_description = get_post_detail( 'description', $this->parentId );

		if ( !empty( $post_detail_description ) ) {
			$this->description = $post_detail_description;
		} elseif ( !empty( $post_parent_detail_description ) ) {
			$this->description = $post_parent_detail_description;
		}
	}

	/** Get config from post meta and add period objects */
	public function loadPeriods () {
		$post_meta = get_post_meta( $this->id, SetCpt::PERIODS_META_KEY, true );

		if ( !is_array( $post_meta ) or count( $post_meta ) < 1 )
			return;

		foreach ( $post_meta as $config ) {
			try {
				$p = new Period( (int) $config['weekday'], $config['timeStart'], $config['timeEnd'] );
				$this->periods->append( $p );
			} catch ( InvalidArgumentException $e ) {
				add_notice( $e->getMessage(), 'error' );
			}
		}

		$this->sortPeriods();
	}

	/** Get config from post meta and add holiday objects */
	public function loadHolidays () {
		$post_meta = get_post_meta( $this->id, HolidaysMetaBox::HOLIDAYS_META_KEY, true );

		if ( !is_array( $post_meta ) or count( $post_meta ) < 1 )
			return;

		foreach ( $post_meta as $config ) {
			$h = new Holiday( $config['name'], new DateTime( $config['dateStart'] ), new DateTime( $config['dateEnd'] ) );
			$this->holidays->append( $h );
		}

		$this->sortHolidays();
	}

	/** Loads all Irregular Openings for this Set */
	public function loadIrregularOpenings () {
		$post_meta = get_post_meta( $this->id, IrregularOpeningsMetaBox::IRREGULAR_OPENINGS_META_KEY, true );

		if ( !is_array( $post_meta ) or count( $post_meta ) < 1 )
			return;

		foreach ( $post_meta as $config ) {
			try {
				$io = new IrregularOpening( $config['name'], $config['date'], $config['timeStart'], $config['timeEnd'] );
				$this->irregularOpenings->append( $io );
			} catch ( InvalidArgumentException $e ) {
				add_notice( $e->getMessage(), 'error' );
			}
		}

		$this->sortIrregularOpenings();
	}

	/**
	 * Checks if child posts match the Set criteria
	 *
	 * @param     WP_Post   $post   The child post
	 * @return    bool              Whether the child post matches the criteria
	 */
	public static function childMatchesCriteria ( WP_Post $post ) {
		$detail_date_start  = get_post_detail( 'date-start', $post->ID );
		$detail_date_end    = get_post_detail( 'date-end', $post->ID );
		$detail_week_scheme = get_post_detail( 'week-scheme', $post->ID );

		$detail_date_start = ( !empty( $detail_date_start ) ) ? new DateTime( $detail_date_start, Dates::getTimezone() ) : null;
		$detail_date_end   = ( !empty( $detail_date_end ) ) ? new DateTime( $detail_date_end, Dates::getTimezone() ) : null;

		if ( $detail_date_start == null and $detail_date_end == null and ( $detail_week_scheme == 'all' or empty( $detail_week_scheme ) ) )
			return false;

		$date_time_now = Dates::getNow();

		if ( $detail_date_start != null and $date_time_now < $detail_date_start )
			return false;

		if ( $detail_date_end != null and $date_time_now > $detail_date_end )
			return false;

		$week_number_modulo = (int) $date_time_now->format( 'W' ) % 2;

		if ( $detail_week_scheme == 'even' and $week_number_modulo === 1 )
			return false;

		if ( $detail_week_scheme == 'odd' and $week_number_modulo === 0 )
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
				$newPeriod = Period::createDummy();
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
			if ( $period->isOpen( $now ) )
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
	 *
	 * @param     int       $weekday  weekday number 0-6
	 *
	 * @return    Holiday             The first active holiday on the specified weekday
	 */
	public function getActiveHolidayOnWeekday ( $weekday ) {
		$date = Dates::applyWeekContext( new DateTime('now'), $weekday );
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

	/** Sorts periods with Period::sortStrategy */
	public function sortPeriods() {
		$this->periods->uasort( array( 'OpeningHours\Entity\Period', 'sortStrategy' ) );
	}

	/** Sorts holidays with Holiday::sortStrategy */
	public function sortHolidays() {
		$this->holidays->uasort( array( 'OpeningHours\Entity\Holiday', 'sortStrategy' ) );
	}

	/** Sorts Irregular Openings */
	public function sortIrregularOpenings() {
		$this->irregularOpenings->uasort( array( 'OpeningHours\Entity\IrregularOpening', 'sortStrategy' ) );
	}

	/**
	 * Returns the next open period
	 * @return    Period    The next open period
	 */
	public function getNextOpenPeriod() {
		$this->sortPeriods();

		if ( count( $this->periods ) < 1 )
			return null;

		foreach ( $this->periods as $period )
			if ( $period->willBeOpen( $this->getId() ) )
				return $period;


		for ( $weekOffset = 1; true; $weekOffset++ ) {
			if ( $weekOffset > 52 ) {
				return null;
			}

			$timeDifference = new DateInterval( 'P' . 7 * $weekOffset . 'D' );

			foreach ( $this->periods as $period ) {
				/** @var Period $newPeriod */
				$newPeriod = $period->getCopy( $timeDifference );

				if ( $newPeriod->willBeOpen( $this->id ) ) {
					return $newPeriod;
				}
			}
		}
	}

	/**
	 * Get Sets from Posts
	 * @param    array      $posts    Array of posts
	 *
	 * @return   array                Sets from the posts
	 */
	public static function getSetsFromPosts( array $posts ) {
		$sets = array();

		foreach ( $posts as $post )
			if ( $post instanceof WP_Post or is_numeric( $post ) )
				$sets[] = new Set( $post );

		return $sets;
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

		foreach ( $days as $day_1 ) {
			if ( in_array( $day_1, $compressed ) )
				continue;

			$keys = array( $day_1 );
			foreach ( $days as $day_2 ) {
				if ( $day_1 == $day_2 )
					continue;

				if ( in_array( $day_2, $compressed ) )
					continue;

				if ( $this->daysEqual( $day_1, $day_2 ) )
					$keys[] = $day_2;
			}

			$newPeriodsArray[ implode( ',', $keys ) ] = $periodsArray[ $day_1 ];
			$compressed = array_merge( $compressed, $keys );
		}

		return $newPeriodsArray;
	}

	/**
	 * Returns first active irregular opening
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
	 *
	 * @return    IrregularOpening    The first active irregular opening fpr the current weekday
	 */
	public function getActiveIrregularOpeningOnWeekday ( $weekday ) {
		$date = Dates::applyWeekContext( new DateTime('now'), $weekday );
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
	public function daysEqual( $day1, $day2, $periodsByDay = null ) {
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