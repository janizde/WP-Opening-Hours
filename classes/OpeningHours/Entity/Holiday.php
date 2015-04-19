<?php
/**
 * Opening Hours: Entity: Holiday
 */

namespace OpeningHours\Entity;

use OpeningHours\Module\I18n;

use DateTime;
use InvalidArgumentException;

class Holiday {

	/**
	 * Name
	 * string with holiday name
	 *
	 * @access          protected
	 * @type            string
	 */
	protected $name;

	/**
	 * Date Start
	 * DateTime object representing the start of the holiday
	 *
	 * @access          protected
	 * @type            DateTime
	 */
	protected $dateStart;

	/**
	 * Date End
	 * DateTime object representing the end of the holiday
	 *
	 * @access          protected
	 * @type            DateTime
	 */
	protected $dateEnd;

	/**
	 * Dummy
	 * bool telling whether holiday is a dummy or not
	 *
	 * @access          protected
	 * @type            bool
	 */
	protected $dummy;

	/**
	 * Constructor
	 *
	 * @access          public
	 *
	 * @param           array $config
	 *
	 * @throws          InvalidArgumentException
	 */
	public function __construct( array $config ) {

		$config = static::validateConfig( $config );
		$this->setUp( $config );

	}

	/**
	 * Set Up
	 * sets up holiday object
	 *
	 * @access          protected
	 *
	 * @param           array $config
	 */
	protected function setUp( array $config ) {

		$this->setName( $config['name'] );
		$this->setDateStart( new DateTime( $config['dateStart'] ) );
		$this->setDateEnd( new DateTime( $config['dateEnd'] ) );
		$this->setDummy( $config['dummy'] );

	}

	/**
	 * Is Active
	 * determines if current Holiday is active
	 *
	 * @access          public
	 *
	 * @param           DateTime $now
	 *
	 * @return          bool
	 */
	public function isActive( $now = null ) {

		if ( $now === null ) {
			$now = I18n::getTimeNow();
		}

		return ( $this->getDateStart() <= $now and $this->getDateEnd() >= $now );

	}

	/**
	 * Validate Config
	 *
	 * @access          public
	 * @static
	 *
	 * @param           array $config
	 *
	 * @return          array
	 * @throws          InvalidArgumentException
	 */
	public static function validateConfig( array $config ) {

		if ( isset( $config['dummy'] ) and $config['dummy'] === true ) :
			$config = array(
				'name'      => '',
				'dateStart' => 'now',
				'dateEnd'   => 'now',
				'dummy'     => true
			);

			return $config;
		endif;

		if ( ! isset( $config['dateStart'] ) ) {
			throw new InvalidArgumentException( 'dateStart not set in Holiday config.' );
		}

		if ( ! $config['dateStart'] instanceof DateTime and ! preg_match( I18n::STD_DATE_FORMAT_REGEX, $config['dateStart'] ) ) {
			throw new InvalidArgumentException( sprintf( 'dateStart in config does not correspond with standard date regex %s. %s given.', I18n::STD_DATE_FORMAT, $config['dateStart'] ) );
		}

		if ( ! isset( $config['dateEnd'] ) ) {
			throw new InvalidArgumentException( 'dateEnd not set in Holiday config.' );
		}

		if ( ! $config['dateEnd'] instanceof DateTime and ! preg_match( I18n::STD_DATE_FORMAT_REGEX, $config['dateEnd'] ) ) {
			throw new InvalidArgumentException( sprintf( 'dateEnd in config does not correspond with standard date regex %s. %s given.', I18n::STD_DATE_FORMAT, $config['dateEnd'] ) );
		}

		if ( ! isset( $config['dummy'] ) or ! is_bool( $config['dummy'] ) ) {
			$config['dummy'] = false;
		}

		if ( ! $config['dummy'] and ( ! isset( $config['name'] ) or empty( $config['name'] ) ) ) {
			throw new InvalidArgumentException( 'name not set in Holiday config.' );
		}

		return $config;

	}

	/**
	 * Sort Strategy
	 * sorts Holiday objects by dateStart (ASC)
	 *
	 * @access          public
	 * @static
	 *
	 * @param           Holiday $holiday_1
	 * @param           Holiday $holiday_2
	 *
	 * @return          int
	 */
	public static function sortStrategy( Holiday $holiday_1, Holiday $holiday_2 ) {

		if ( $holiday_1->getDateStart() > $holiday_2->getDateStart() ) :
			return 1;
		elseif ( $holiday_1->getDateStart() < $holiday_2->getDateStart() ) :
			return - 1;
		else :
			return 0;
		endif;

	}

	/**
	 * To String
	 * converts config array to json
	 *
	 * @access          public
	 * @return          string
	 */
	public function __toString() {
		return json_encode( $this->getConfig() );
	}

	/**
	 * Factory: Dummy Holiday
	 *
	 * @access          public
	 * @static
	 * @return          Holiday
	 */
	public static function createDummyPeriod() {
		return new Holiday( array(
			'dummy' => true
		) );
	}

	/**
	 * Getter: Config
	 * generates config array for Holiday object
	 *
	 * @access          public
	 * @return          array
	 */
	public function getConfig() {

		$config = array(
			'name'      => $this->getName(),
			'dateStart' => $this->getDateStart()->format( I18n::STD_DATE_FORMAT ),
			'dateEnd'   => $this->getDateEnd()->format( I18n::STD_DATE_FORMAT ),
			'dummy'     => $this->isDummy()
		);

		return $config;

	}

	/**
	 * Getter: Name
	 *
	 * @access          public
	 * @return          string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Setter: Name
	 *
	 * @access          protected
	 *
	 * @param           string $name
	 */
	protected function setName( $name ) {
		$this->name = $name;
	}

	/**
	 * Getter: Date Start
	 *
	 * @access          public
	 * @return          DateTime
	 */
	public function getDateStart() {
		return $this->dateStart;
	}

	/**
	 * Setter: Date Start
	 *
	 * @access          protected
	 *
	 * @param           DateTime|string $dateStart
	 */
	protected function setDateStart( $dateStart ) {
		$this->setDateUniversal( $dateStart, 'dateStart' );
	}

	/**
	 * Getter: Date End
	 *
	 * @access          public
	 * @return          DateTime
	 */
	public function getDateEnd() {
		return $this->dateEnd;
	}

	/**
	 * Setter: Date End
	 *
	 * @access          protected
	 *
	 * @param           DateTime|string $dateEnd
	 */
	protected function setDateEnd( $dateEnd ) {
		$this->setDateUniversal( $dateEnd, 'dateEnd', true );
	}

	/**
	 * Setter: Set Universal Date
	 * universal setter for dates
	 *
	 * @access          protected
	 *
	 * @param           DateTime|string $date
	 * @param           bool $end_of_day
	 * @param           string $property
	 */
	protected function setDateUniversal( $date, $property, $end_of_day = false ) {

		if ( is_string( $date ) and ( preg_match( I18n::STD_DATE_FORMAT_REGEX, $date ) or preg_match( I18n::STD_DATE_FORMAT_REGEX . ' ' . I18n::STD_TIME_FORMAT_REGEX, $date ) ) ) {
			$date = new DateTime( $date );
		}

		if ( ! $date instanceof DateTime ) {
			add_notice( sprintf( 'Argument one for %s has to be of type string or DateTime, %s given', __CLASS__ . '::' . __METHOD__, gettype( $date ) ) ) ;
		}

		$date = I18n::applyTimeZone( $date );

		if ( $end_of_day === true ) {
			$date->setTime( 23, 59, 59 );
		}

		$this->$property = I18n::applyTimeZone( $date );

	}

	/**
	 * Getter: Dummy
	 *
	 * @access          public
	 * @return          bool
	 */
	public function isDummy() {
		return $this->dummy;
	}

	/**
	 * Setter: Dummy
	 *
	 * @access          protected
	 *
	 * @param           bool $dummy
	 */
	protected function setDummy( $dummy ) {
		$this->dummy = (bool) $dummy;
	}

}