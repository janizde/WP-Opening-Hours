<?php

namespace OpeningHours\Entity;

use OpeningHours\Module\I18n;

use DateInterval;
use DateTime;
use InvalidArgumentException;

/**
 * Represents an irregular opening
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Entity
 */
class IrregularOpening {

	/**
	 * Name
	 * @type      string
	 */
	protected $name;

	/**
	 * The starting time of the IO
	 * @type      DateTime
	 */
	protected $timeStart;

	/**
	 * The ending time of the IO
	 * @type      DateTime
	 */
	protected $timeEnd;

	/**
	 * Whether this IO is a dummy
	 * @type      bool
	 */
	protected $dummy;

	/**
	 * Constructs a new IO with a config array
	 *
	 * @param     array     $config   The config array for the new IO
	 *
	 * @throws    InvalidArgumentException  On validation error
	 */
	public function __construct ( array $config ) {
		$config = static::validateConfig( $config );
		$this->setUp( $config );
	}

	/**
	 * Validates and filters configuration array for the IO
	 *
	 * @param     array     $config   The config array to validate
	 *
	 * @return    array               The filtered config array
	 */
	protected static function validateConfig ( array $config ) {
		if ( isset( $config['dummy'] ) or ( $config['dummy'] !== true and $config['dummy'] !== false ) )
			$config['dummy'] = false;

		if ( $config['dummy'] ) {
			$config['timeStart'] = new DateTime( 'now' );
			$config['timeEnd']   = new DateTime( 'now' );
			$config['name']      = null;

			return $config;
		}

		if ( !preg_match( I18n::STD_TIME_FORMAT_REGEX, $config['timeStart'] ) )
			throw new InvalidArgumentException( "timeStart in config is not in valid time format" );

		if ( !preg_match( I18n::STD_TIME_FORMAT_REGEX, $config['timeEnd'] ) )
			throw new InvalidArgumentException( "timeEnd in config is not in valid time format" );

		if ( !preg_match( I18n::STD_DATE_FORMAT_REGEX, $config['date'] ) )
			throw new InvalidArgumentException( "date in config is not in valid date format" );

		if ( !isset( $config['name'] ) or empty( $config['name'] ) )
			throw new InvalidArgumentException( "name in config is not set or empty" );

		$config['timeStart'] = I18n::mergeDateIntoTime( new DateTime( $config['date'] ), new DateTime( $config['timeStart'] ) );
		$config['timeEnd']   = I18n::mergeDateIntoTime( new DateTime( $config['date'] ), new DateTime( $config['timeEnd'] ) );

		return $config;
	}

	/**
	 * Sets up the object properties
	 * @param     array     $config   The config array to use
	 */
	public function setUp( array $config ) {
		$this->setName( $config['name'] );
		$this->setTimeStart( $config['timeStart'] );
		$this->setTimeEnd( $config['timeEnd'] );
		$this->setDummy( $config['dummy'] );
	}

	/** Updates the start and end time */
	public function updateTime() {
		if ( $this->timeStart instanceof DateTime or $this->timeEnd instanceof DateTime )
			return;

		if (
			$this->timeStart->format( I18n::STD_DATE_FORMAT ) === $this->timeEnd->format( I18n::STD_DATE_FORMAT )
			and $this->timeStart >= $this->timeEnd
		) {
			$this->timeEnd->add( new DateInterval( 'P1D' ) );
		}
	}

	/**
	 * Checks whether this IrregularOpening is active on the given date.
	 *
	 * @param     DateTime  $now      The DateTime to compare against. Default is the current time.
	 * @return    bool                Whether this IO is active on the given date
	 */
	public function isActive( DateTime $now = null ) {
		if ( $now == null )
			$now = I18n::getTimeNow();

		return ( $this->getDate()->format( I18n::STD_DATE_FORMAT ) == $now->format( I18n::STD_DATE_FORMAT ) );
	}

	/**
	 * Checks whether the venue is actually open due to the IrregularOpening
	 *
	 * @param     DateTime  $now    The DateTime to compare against. Default is the current time.
	 * @return    bool              Whether the venue is actually open due to this IO
	 */
	public function isOpen ( DateTime $now = null ) {
		if ( $now == null )
			$now = I18n::getTimeNow();

		if ( !$this->isActive( $now ) )
			return false;

		return ( $this->timeStart <= $now and $this->timeEnd >= $now );
	}

	/**
	 * Get Formatted Time Range
	 *
	 * @param     string    $timeFormat     Custom time format
	 * @param     string    $outputFormat   Custom output format. First variable: start time, second variable: end time
	 *
	 * @return    string                    The time range as string
	 */
	public function getFormattedTimeRange( $timeFormat = null, $outputFormat = "%s – %s" ) {
		if ( $timeFormat == null )
			$timeFormat = I18n::getTimeFormat();

		if ( !$this->timeStart instanceof DateTime or !$this->timeEnd instanceof DateTime )
			return "";

		return sprintf( $outputFormat, $this->timeStart->format( $timeFormat ), $this->timeEnd->format( $timeFormat ) );
	}

	/**
	 * Generates a config array representing this IO
	 * @return    array   Associative config array representing this IO
	 */
	public function getConfig() {
		return array(
			'name'      => $this->name,
			'timeStart' => $this->timeStart->format( I18n::STD_TIME_FORMAT ),
			'timeEnd'   => $this->timeEnd->format( I18n::STD_TIME_FORMAT ),
			'date'      => $this->getDate()
		);
	}

	/**
	 * Returns the config as JSON string
	 * @return    string
	 */
	public function __toString() {
		return json_encode( $this->getConfig() );
	}

	/**
	 * Sorts Irregular Openings by start-time (ASC)
	 *
	 * @param     IrregularOpening    $io1
	 * @param     IrregularOpening    $io2
	 *
	 * @return    int
	 */
	public static function sortStrategy( IrregularOpening $io1, IrregularOpening $io2 ) {
		if ( $io1->timeStart < $io2->timeStart ) :
			return - 1;
		elseif ( $io1->timeStart > $io2->timeStart ) :
			return 1;
		else :
			return 0;
		endif;
	}

	/**
	 * Factory for dummy IO
	 * @return    IrregularOpening  An IO dummy
	 */
	public static function createDummy() {
		return new IrregularOpening( array(
			'dummy' => true
		) );
	}

	/**
	 * Getter: Name
	 * @return    string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Setter: Name
	 * @param    string     $name
	 */
	protected function setName( $name ) {
		$this->name = $name;
	}

	/**
	 * Getter: Time Start
	 * @return    DateTime
	 */
	public function getTimeStart() {
		return $this->timeStart;
	}

	/**
	 * Setter: Time Start
	 * @param     DateTime  $timeStart
	 */
	public function setTimeStart( DateTime $timeStart ) {
		$this->timeStart = $timeStart;
		$this->updateTime();
	}

	/**
	 * Getter: Time End
	 * @return    DateTime
	 */
	public function getTimeEnd() {
		return $this->timeEnd;
	}

	/**
	 * Setter: Time End
	 * @param     DateTime  $timeEnd
	 */
	protected function setTimeEnd( DateTime $timeEnd ) {
		$this->timeEnd = $timeEnd;
		$this->updateTime();
	}

	/**
	 * Getter: Dummy
	 * @return    bool
	 */
	public function isDummy() {
		return $this->dummy;
	}

	/**
	 * Setter: Dummy
	 * @param     bool      $dummy
	 */
	protected function setDummy( $dummy ) {
		$this->dummy = $dummy;
	}

	/**
	 * Getter: Date
	 * @return    DateTime
	 */
	public function getDate() {
		return new DateTime( $this->getTimeStart()->format( I18n::STD_DATE_FORMAT ), I18n::getDateTimeZone() );
	}

	/**
	 * Setter: Date
	 * @param     DateTime|string   $date
	 */
	protected function setDate( $date ) {
		if ( is_string( $date ) and preg_match( $date, I18n::STD_DATE_FORMAT_REGEX ) )
			$date = new DateTime( $date, I18n::getDateTimeZone() );

		if ( !$date instanceof DateTime )
			add_notice( sprintf( "%::% requires Parameter 1 to be DateTime or string in correct date format. %s given", __CLASS__, __METHOD__, gettype( $date ) ), 'error' );

		if ( $this->getTimeStart() instanceof DateTime )
			$this->getTimeStart()->setDate( (int) $date->format( 'Y' ), (int) $date->format( 'm' ), (int) $date->format( 'd' ) );

		if ( $this->getTimeEnd() instanceof DateTime )
			$this->getTimeEnd()->setDate( (int) $date->format( 'Y' ), (int) $date->format( 'm' ), (int) $date->format( 'd' ) );

		$this->updateTime();
	}
}