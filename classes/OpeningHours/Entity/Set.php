<?php
/**
 *  Opening Hours: Entity: Set
 */

namespace OpeningHours\Entity;

use OpeningHours\Misc\ArrayObject;
use OpeningHours\Module\I18n;
use OpeningHours\Module\CustomPostType\Set as SetCpt;
use OpeningHours\Module\CustomPostType\MetaBox\Holidays as HolidaysMetaBox;
use OpeningHours\Module\CustomPostType\MetaBox\IrregularOpenings as IrregularOpeningsMetaBox;

use WP_Post;
use DateTime;
use DateInterval;
use InvalidArgumentException;

class Set {

	/**
	 * Constants
	 */
	const WP_ACTION_BEFORE_SETUP = 'op_set_before_setup';

	/**
	 * Periods
	 *
	 * @access     protected
	 * @type       ArrayObject
	 */
	protected $periods;

	/**
	 * Holidays0
	 *
	 * @access      protected
	 * @type        ArrayObject
	 */
	protected $holidays;

	/**
	 * Irregular Openings
	 *
	 * @access      protected
	 * @type        ArrayObject
	 */
	protected $irregularOpenings;

	/**
	 * Id
	 *
	 * @access      protected
	 * @type        int
	 */
	protected $id;

	/**
	 * Post
	 *
	 * @access     protected
	 * @type       WP_Post
	 */
	protected $post;

	/**
	 * Parent Id
	 *
	 * @access     protected
	 * @type       int
	 */
	protected $parentId;

	/**
	 * Parent Post
	 *
	 * @access     protected
	 * @type       WP_Post
	 */
	protected $parentPost;

	/**
	 * Description
	 * Description meta from CPT Set
	 *
	 * @access      protected
	 * @type        string
	 */
	protected $description;

	/**
	 * Has Parent
	 *
	 * @access     protected
	 * @type       bool
	 */
	protected $hasParent;

	/**
	 * Constructor
	 *
	 * @access     public
	 *
	 * @param      WP_Post|int $post
	 *
	 * @return     Set
	 */
	public function __construct( $post ) {

		$this->setPeriods( new ArrayObject );
		$this->setHolidays( new ArrayObject );
		$this->setIrregularOpenings( new ArrayObject );

		if ( ! is_int( $post ) and ! $post instanceof WP_Post ) {
			throw new InvalidArgumentException( sprintf( 'Argument one for __construct has to be of type WP_Post or int. %s given', gettype( $post ) ) );
		}

		if ( is_int( $post ) ) {
			$post = get_post( $post );
		}

		$this->setId( $post->ID );
		$this->setPost( $post );
		$this->setParentId( $post->ID );
		$this->setParentPost( $post );

		$this->setUp();

		return $this;

	}

	/**
	 * Set Up
	 *
	 * @access     public
	 */
	public function setUp() {

		// Check for Child Posts
		$childPosts = get_posts( array(
			'post_type'   => SetCpt::CPT_SLUG,
			'post_parent' => $this->getId()
		) );

		foreach ( $childPosts as $post ) :

			if ( self::childMatchesCriteria( $post ) ) :
				$this->setId( $post->ID );
				$this->setPost( $post );
			endif;

		endforeach;

		/**
		 * Action:    op_set_before_setup
		 *
		 * @param     Set     Set object
		 */
		do_action(
			self::WP_ACTION_BEFORE_SETUP,
			$this
		);

		$this->loadPeriods();

		$this->loadHolidays();

		$this->loadIrregularOpenings();

		$post_detail_description        = get_post_detail( 'description', $this->getId() );
		$post_parent_detail_description = get_post_detail( 'description', $this->getParentId() );

		/**
		 * Set Description
		 */
		if ( ! empty( $post_detail_description ) ) :
			$this->setDescription( $post_detail_description );

		elseif ( ! empty( $post_parent_detail_description ) ):
			$this->setDescription( $post_parent_detail_description );

		endif;
	}

	/**
	 * Load Periods
	 * get config from post meta and add period objects
	 *
	 * @access      protected
	 */
	public function loadPeriods() {

		$post_meta = get_post_meta( $this->getId(), SetCpt::PERIODS_META_KEY, true );

		if ( ! is_array( $post_meta ) or ! count( $post_meta ) ) {
			return;
		}

		foreach ( $post_meta as $config ) :
			$this->getPeriods()->addElement( new Period( $config ) );
		endforeach;

		$this->sortPeriods();

	}

	/**
	 * Load Holidays
	 * get config from post meta and add holiday objects
	 *
	 * @access      protected
	 */
	public function loadHolidays() {

		$post_meta = get_post_meta( $this->getId(), HolidaysMetaBox::HOLIDAYS_META_KEY, true );

		if ( ! is_array( $post_meta ) or ! count( $post_meta ) ) {
			return;
		}

		foreach ( $post_meta as $config ) {
			$this->getHolidays()->addElement( new Holiday( $config ) );
		}

		$this->sortHolidays();

	}

	/**
	 * Load Irregular Openings
	 *
	 * @access      protected
	 */
	public function loadIrregularOpenings() {

		$post_meta = get_post_meta( $this->getId(), IrregularOpeningsMetaBox::IRREGULAR_OPENINGS_META_KEY, true );

		if ( ! is_array( $post_meta ) or ! count( $post_meta ) ) {
			return;
		}

		foreach ( $post_meta as $config ) {
			$this->getIrregularOpenings()->addElement( new IrregularOpening( $config ) );
		}

		$this->sortIrregularOpenings();

	}

	/**
	 * Child Matches Criteria
	 * checks if child posts matches the set criteria
	 *
	 * @access    public
	 * @static
	 *
	 * @param     WP_Post $post
	 *
	 * @return    bool
	 */
	public static function childMatchesCriteria( WP_Post $post ) {

		$detail_date_start  = get_post_detail( 'date-start', $post->ID );
		$detail_date_end    = get_post_detail( 'date-end', $post->ID );
		$detail_week_scheme = get_post_detail( 'week-scheme', $post->ID );

		$detail_date_start = ( ! empty( $detail_date_start ) ) ? new DateTime( $detail_date_start, I18n::getDateTimeZone() ) : null;
		$detail_date_end   = ( ! empty( $detail_date_end ) ) ? new DateTime( $detail_date_end, I18n::getDateTimeZone() ) : null;

		/**
		 * Skip if no criteria is set
		 */
		if ( $detail_date_start == null and $detail_date_end == null and ( $detail_week_scheme == 'all' or empty( $detail_week_scheme ) ) ) {
			return false;
		}

		$date_time_now = I18n::getTimeNow();

		/**
		 * Date Range
		 */
		if ( $detail_date_start != null and $date_time_now < $detail_date_start ) {
			return false;
		}

		if ( $detail_date_end != null and $date_time_now > $detail_date_end ) {
			return false;
		}

		/**
		 * Week Scheme
		 */
		$week_number_modulo = (int) $date_time_now->format( 'W' ) % 2;

		if ( $detail_week_scheme == 'even' and $week_number_modulo === 1 ) {
			return false;
		}

		if ( $detail_week_scheme == 'odd' and $week_number_modulo === 0 ) {
			return false;
		}

		return true;

	}

	/**
	 * Is Parent
	 * checks if this set is a parent set
	 *
	 * @access     public
	 * @return     bool
	 */
	public function isParent() {
		return ( $this->getId() === $this->getParentId() );
	}

	/**
	 * Add Dummy Periods
	 *
	 * @access     public
	 */
	public function addDummyPeriods() {

		foreach ( I18n::getWeekdaysNumeric() as $id => $name ) :

			if ( ! count( $this->getPeriodsByDay( $id ) ) ) :

				$newPeriod = new Period( array(
					'weekday'   => $id,
					'timeStart' => new DateTime( '00:00' ),
					'timeEnd'   => new DateTime( '00:00' ),
					'dummy'     => true
				) );

				$this->getPeriods()->addElement( $newPeriod );

			endif;

		endforeach;

	}

	/**
	 * Is Open – Opening Hours
	 * only evaluates standard opening periods
	 *
	 * @access      public
	 *
	 * @param       DateTime $now
	 *
	 * @return      bool
	 */
	public function isOpenOpeningHours( $now = null ) {

		foreach ( $this->getPeriods() as $period ) :

			if ( $period->isOpen( $now ) ) {
				return true;
			}

		endforeach;

		return false;

	}

	/**
	 * Is Holiday Active
	 * checks if any holiday in set is currently active
	 *
	 * @access      public
	 *
	 * @param       DateTime $now
	 *
	 * @return      bool
	 */
	public function isHolidayActive( $now = null ) {

		return $this->getActiveHoliday( $now ) instanceof Holiday;

	}

	/**
	 * Get Active Holiday
	 * returns the first active holiday or null if none is active
	 *
	 * @access      public
	 *
	 * @param       DateTime    $now
	 *
	 * @return      Holiday
	 */
	public function getActiveHoliday ( DateTime $now = null ) {

		foreach ( $this->getHolidays() as $holiday ) :

			if ( $holiday->isActive() )
				return $holiday;

		endforeach;

		return null;

	}

	/**
	 * Get Active Holiday on Weekday
	 * returns the first active holiday on the specified weekday
	 *
	 * @access      public
	 *
	 * @param       int       $weekday (weekday number 0-6)
	 *
	 * @return      Holiday
	 */
	public function getActiveHolidayOnWeekday ( $weekday ) {

		$date   = I18n::applyWeekContext( new DateTime('now'), $weekday );

		return $this->getActiveHoliday( $date );

	}

	/**
	 * Is Irregular Opening Active
	 * checks if any irregular opening is currently active (based on the date)
	 *
	 * @access      public
	 *
	 * @param       DateTime $now
	 *
	 * @return      true
	 */
	public function isIrregularOpeningActive( DateTime $now = null ) {

		return $this->getActiveIrregularOpening( $now ) instanceof IrregularOpening;

	}

	/**
	 * Is Open
	 * evaluates all aspects
	 *
	 * @access      public
	 *
	 * @param       DateTime $now
	 *
	 * @return      bool
	 */
	public function isOpen( DateTime $now = null ) {

		/** Holidays */
		if ( $this->isHolidayActive( $now ) ) {
			return false;
		}

		/** Irregular Openings */
		if ( $this->isIrregularOpeningActive( $now ) ) :

			$io = $this->getActiveIrregularOpening( $now );

			return $io->isOpen( $now );

		endif;

		/** Opening Hours */

		return $this->isOpenOpeningHours( $now );

	}

	/**
	 * Sort Periods
	 * sorts periods with Period::sortStrategy
	 *
	 * @access        public
	 */
	public function sortPeriods() {

		$this->getPeriods()->uasort( array( 'OpeningHours\Entity\Period', 'sortStrategy' ) );

	}

	/**
	 * Sort Holidays
	 * sorts holidays with Holiday::sortStrategy
	 *
	 * @access        public
	 */
	public function sortHolidays() {

		$this->getHolidays()->uasort( array( 'OpeningHours\Entity\Holiday', 'sortStrategy' ) );

	}

	/**
	 * Sort Irregular Openings
	 *
	 * @access        public
	 */
	public function sortIrregularOpenings() {

		$this->getIrregularOpenings()->uasort( array( 'OpeningHours\Entity\IrregularOpening', 'sortStrategy' ) );

	}

	/**
	 * Get Next Open Period
	 * returns the next open period
	 *
	 * @access      public
	 * @return      Period
	 */
	public function getNextOpenPeriod() {

		$this->sortPeriods();

		for ( $week_offset = 0; true; $week_offset ++ ) :

			if ( $week_offset === 0 ) :

				foreach ( $this->getPeriods() as $period ) :

					if ( $period->willBeOpen( $this->getId() ) ) {
						return $period;
					}

				endforeach;

			else:

				$time_difference = new DateInterval( 'P' . 7 * $week_offset . 'D' );

				foreach ( $this->getPeriods() as $period ) :

					$new_period = $period->getCopy( $time_difference );

					if ( $new_period->willBeOpen( $this->getId() ) ) {
						return $new_period;
					}

				endforeach;

			endif;

		endfor;

	}

	/**
	 * Get Sets from Posts
	 *
	 * @access     public
	 * @static
	 *
	 * @param      array $posts
	 *
	 * @return     array
	 */
	public static function getSetsFromPosts( array $posts ) {

		foreach ( $posts as &$post ) :

			if ( $post instanceof WP_Post or is_numeric( $post ) ) {
				$post = new Set( $post );
			}

		endforeach;

		return $posts;

	}

	/**
	 * Getter: Periods
	 *
	 * @access     public
	 * @return     ArrayObject
	 */
	public function getPeriods() {
		return $this->periods;
	}

	/**
	 * Getter: Periods By Day
	 *
	 * @access     public
	 *
	 * @param      array|int $days
	 *
	 * @return     array
	 */
	public function getPeriodsByDay( $days ) {

		if ( ! is_array( $days ) and ! is_numeric( $days ) ) {
			throw new InvalidArgumentException( sprintf( 'Argument 1 of getPeriodsByDay must be integer or array. %s given.', gettype( $days ) ) );
		}

		if ( ! is_array( $days ) ) {
			$days = array( $days );
		}

		$periods = array();

		foreach ( $this->getPeriods() as $period ) :
			if ( in_array( $period->getWeekday(), $days ) ) {
				$periods[] = $period;
			}
		endforeach;

		return $periods;

	}

	/**
	 * Getter: (all) Periods Grouped By Day
	 *
	 * @access       public
	 * @return       array
	 */
	public function getPeriodsGroupedByDay() {

		$periods = array();

		foreach ( I18n::getWeekdaysNumeric() as $id => $caption ) {
			$periods[ $id ] = $this->getPeriodsByDay( $id );
		}

		return $periods;

	}

	/**
	 * Getter: (all) Periods Grouped By Day and Compressed
	 * applies some kind of array_unique on the array. Days with same Periods are uniqued to one array element with a comma separated string containing the day IDs
	 *
	 * @access      public
	 * @return      array
	 */
	public function getPeriodsGroupedByDayCompressed() {

		$periods_array = $this->getPeriodsGroupedByDay();

		$new_periods_array = array();

		$compressed = array();

		$days = range( 0, 6 );

		foreach ( $days as $day_1 ) :

			if ( in_array( $day_1, $compressed ) ) {
				continue;
			}

			$keys = array(
				$day_1
			);

			foreach ( $days as $day_2 ) :

				if ( $day_1 == $day_2 ) {
					continue;
				}

				if ( in_array( $day_2, $compressed ) ) {
					continue;
				}

				if ( $this->daysEqual( $day_1, $day_2 ) ) {
					$keys[] = $day_2;
				}

			endforeach;

			$new_periods_array[ implode( ',', $keys ) ] = $periods_array[ $day_1 ];

			$compressed = array_merge( $compressed, $keys );

		endforeach;

		return $new_periods_array;

	}

	/**
	 * Get Active Irregular Opening
	 * returns first active irregular opening
	 *
	 * @access      public
	 *
	 * @param       DateTime $now
	 *
	 * @return      IrregularOpening
	 */
	public function getActiveIrregularOpening( DateTime $now = null ) {

		foreach ( $this->getIrregularOpenings() as $io ) :

			if ( $io->isActive( $now ) ) {
				return $io;
			}

		endforeach;

		return null;

	}

	/**
	 * Get Active Irregular Opening On Weekday
	 * returns first active irregular opening on a specific weekday
	 *
	 * @access      public
	 *
	 * @param       int     $weekday (weekday number, 0-6)
	 *
	 * @return      IrregularOpening
	 */
	public function getActiveIrregularOpeningOnWeekday ( $weekday ) {

		$date   = I18n::applyWeekContext( new DateTime('now'), $weekday );

		return $this->getActiveIrregularOpening( $date );

	}

	/**
	 * Days equal
	 * checks if two days have equal Periods
	 *
	 * @access      public
	 *
	 * @param       int $day_1
	 * @param       int $day_2
	 * @param       array $periods_by_day
	 *
	 * @return      bool
	 */
	public function daysEqual( $day_1, $day_2, $periods_by_day = null ) {

		if ( $day_1 == $day_2 ) {
			return true;
		}

		if ( $periods_by_day === null or ! is_array( $periods_by_day ) ) {
			$periods_by_day = $this->getPeriodsGroupedByDay();
		}

		if ( ! count( $periods_by_day[ $day_1 ] ) and ! count( $periods_by_day[ $day_2 ] ) ) {
			return true;
		}

		if ( count( $periods_by_day[ $day_1 ] ) != count( $periods_by_day[ $day_2 ] ) ) {
			return false;
		}

		for ( $i = 0; $i < count( $periods_by_day[ $day_1 ] ); $i ++ ) :

			$period_1 = $periods_by_day[ $day_1 ][ $i ];
			$period_2 = $periods_by_day[ $day_2 ][ $i ];

			if ( ! $period_1->equals( $period_2, true ) ) {
				return false;
			}

		endfor;

		return true;

	}

	/**
	 * Setter: Periods
	 *
	 * @access     public
	 *
	 * @param      ArrayObject $periods
	 *
	 * @return     Set
	 */
	public function setPeriods( ArrayObject $periods ) {
		$this->periods = $periods;

		return $this;
	}

	/**
	 * Getter: Holidays
	 *
	 * @access      public
	 * @return      ArrayObject
	 */
	public function getHolidays() {
		return $this->holidays;
	}

	/**
	 * Setter: Holidays
	 *
	 * @access      protected
	 *
	 * @param       ArrayObject $holidays
	 */
	public function setHolidays( ArrayObject $holidays ) {
		$this->holidays = $holidays;
	}

	/**
	 * Getter: Irregular Openings
	 *
	 * @access      public
	 * @return      ArrayObject
	 */
	public function getIrregularOpenings() {
		return $this->irregularOpenings;
	}

	/**
	 * Setter: Irregular Openings
	 *
	 * @access      protected
	 *
	 * @param       ArrayObject $irregularOpenings
	 */
	protected function setIrregularOpenings( ArrayObject $irregularOpenings ) {
		$this->irregularOpenings = $irregularOpenings;
	}

	/**
	 * Getter: Id
	 *
	 * @access     public
	 * @return     int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Setter: Id
	 *
	 * @access     public
	 *
	 * @param      int $id
	 *
	 * @return     Set
	 */
	public function setId( $id ) {
		$this->id = $id;

		return $this;
	}

	/**
	 * Getter: Post
	 *
	 * @access     public
	 * @return     WP_Post
	 */
	public function getPost() {
		return $this->post;
	}

	/**
	 * Setter: Post
	 *
	 * @access     public
	 *
	 * @param      WP_Post|int $post
	 *
	 * @return     Set
	 */
	public function setPost( $post ) {

		if ( $post instanceof WP_Post ) :
			$this->post = $post;

		elseif ( is_int( $post ) ) :
			$this->post = get_post( $post );

		else :
			$this->post = null;

		endif;

		return $this;

	}

	/**
	 * Getter: Parent Id
	 *
	 * @access     public
	 * @return     int
	 */
	public function getParentId() {
		return $this->parentId;
	}

	/**
	 * Setter: Parent Id
	 *
	 * @access     public
	 *
	 * @param      int $parentId
	 *
	 * @return     Set
	 */
	public function setParentId( $parentId ) {
		$this->parentId = $parentId;

		return $this;
	}

	/**
	 * Getter: Parent Post
	 *
	 * @access     public
	 * @return     WP_Post
	 */
	public function getParentPost() {
		return ( ! $this->hasParent() and ! $this->parentPost instanceof WP_Post )
			? $this->post
			: $this->parentPost;
	}

	/**
	 * Setter: Parent Post
	 *
	 * @access     public
	 *
	 * @param      WP_Post|int $parentPost
	 *
	 * @return     Set
	 */
	public function setParentPost( $parentPost ) {

		if ( $parentPost instanceof WP_Post ) :
			$this->parentPost = $parentPost;

		elseif ( is_int( $parentPost ) ) :
			$this->parentPost = get_post( $parentPost );

		else :
			$this->parentPost = null;

		endif;

		return $this;

	}

	/**
	 * Getter: Description
	 *
	 * @access      public
	 * @return      bool
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Setter: Description
	 *
	 * @access      protected
	 *
	 * @param       string $description
	 */
	protected function setDescription( $description ) {
		$this->description = $description;
	}

	/**
	 * Getter: Has Parent
	 *
	 * @access     public
	 * @return     bool
	 */
	public function hasParent() {
		return $this->hasParent;
	}

	/**
	 * Setter: Has Parent
	 *
	 * @access     public
	 *
	 * @param      bool $hasParent
	 *
	 * @return     Set
	 */
	public function setHasParent( $hasParent ) {
		$this->hasParent = $hasParent;

		return $this;
	}

}