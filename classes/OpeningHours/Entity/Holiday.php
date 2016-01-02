<?php

namespace OpeningHours\Entity;

use OpeningHours\Module\I18n;

use DateTime;
use InvalidArgumentException;
use OpeningHours\Util\Dates;

/**
 * Represents a single holiday
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Entity
 */
class Holiday {

	/**
	 * The holiday's name
	 * @type            string
	 */
	protected $name;

	/**
	 * DateTime object representing the start of the holiday
	 * @type            DateTime
	 */
	protected $dateStart;

	/**
	 * DateTime object representing the end of the holiday
	 * @type            DateTime
	 */
	protected $dateEnd;

	/**
	 * Whether holiday is a dummy or not
	 * @type            bool
	 */
	protected $dummy;

	/**
	 * Constructs a new Holiday
	 *
	 * @param     array     $config   Configuration array for Holiday
	 *
	 * @throws    InvalidArgumentException  If config is invalid
	 */
	public function __construct ( array $config ) {
		$config = static::validateConfig( $config );
		$this->setUp( $config );
	}

	/**
	 * Sets up holiday object
	 *
	 * @param     array     $config   The config array containing the data for the holiday
	 */
	protected function setUp ( array $config ) {
		$this->setName( $config['name'] );
		$this->setDateStart( new DateTime( $config['dateStart'] ) );
		$this->setDateEnd( new DateTime( $config['dateEnd'] ) );
		$this->setDummy( $config['dummy'] );
	}

	/**
	 * Is Active
	 * determines if current Holiday is active
	 *
	 * @param     DateTime  $now    The DateTime representing the current time. Can be modified to check whether
	 *                              the holiday will be active or has been active at a certain time.
	 *                              Default is the current time
	 *
	 * @return    bool              Whether the holiday has been active, will be active, is active at the provided time
	 */
	public function isActive ( DateTime $now = null ) {
		if ( $now === null )
			$now = Dates::getNow();

		return ( $this->dateStart <= $now and $this->dateEnd >= $now );
	}

	/**
	 * Validate Config
	 *
	 * @param     array     $config   The configuration array to validate
	 *
	 * @return    array     The filtered config
	 * @throws    InvalidArgumentException  On validation error
	 */
	public static function validateConfig ( array $config ) {
		if ( array_key_exists('dummy', $config) and $config['dummy'] === true ) {
			$config = array(
				'name'      => '',
				'dateStart' => 'now',
				'dateEnd'   => 'now',
				'dummy'     => true
			);

			return $config;
		}

		if ( !isset( $config['dateStart'] ) )
			throw new InvalidArgumentException( 'dateStart not set in Holiday config.' );

		if ( !preg_match( Dates::STD_DATE_FORMAT_REGEX, $config['dateStart'] ) )
			throw new InvalidArgumentException( sprintf( 'dateStart in config does not correspond with standard date regex %s. %s given.', Dates::STD_DATE_FORMAT, $config['dateStart'] ) );

		if ( !isset( $config['dateEnd'] ) )
			throw new InvalidArgumentException( 'dateEnd not set in Holiday config.' );

		if ( !preg_match( Dates::STD_DATE_FORMAT_REGEX, $config['dateEnd'] ) )
			throw new InvalidArgumentException( sprintf( 'dateEnd in config does not correspond with standard date regex %s. %s given.', Dates::STD_DATE_FORMAT, $config['dateEnd'] ) );

		if ( !isset( $config['dummy'] ) or !is_bool( $config['dummy'] ) )
			$config['dummy'] = false;

		if ( !$config['dummy'] and ( !isset( $config['name'] ) or empty( $config['name'] ) ) )
			throw new InvalidArgumentException( 'name not set in Holiday config.' );

		return $config;
	}

	/**
	 * Sorts Holiday objects by dateStart (ASC)
	 *
	 * @param     Holiday   $holiday_1
	 * @param     Holiday   $holiday_2
	 *
	 * @return    int
	 */
	public static function sortStrategy ( Holiday $holiday_1, Holiday $holiday_2 ) {
		if ( $holiday_1->dateStart > $holiday_2->dateStart ) :
			return 1;
		elseif ( $holiday_1->dateStart < $holiday_2->dateStart ) :
			return - 1;
		else :
			return 0;
		endif;
	}

	/**
	 * Converts config array to json
	 * @return    string
	 */
	public function __toString () {
		return json_encode( $this->getConfig() );
	}

	/**
	 * Factory for dummy Holiday
	 * @return    Holiday
	 */
	public static function createDummyPeriod () {
		return new Holiday( array(
			'dummy' => true
		) );
	}

	/**
	 * Generates config array for Holiday object
	 * @return    array     Associative array of Holiday config that can be used to configure other instances
	 */
	public function getConfig () {
		$config = array(
			'name'      => $this->name,
			'dateStart' => $this->dateStart->format( Dates::STD_DATE_FORMAT ),
			'dateEnd'   => $this->dateEnd->format( Dates::STD_DATE_FORMAT ),
			'dummy'     => $this->dummy
		);

		return $config;
	}

	/**
	 * Getter: Name
	 * @return          string
	 */
	public function getName () {
		return $this->name;
	}

	/**
	 * Setter: Name
	 * @param           string $name
	 */
	protected function setName ( $name ) {
		$this->name = $name;
	}

	/**
	 * Getter: Date Start
	 * @return          DateTime
	 */
	public function getDateStart () {
		return $this->dateStart;
	}

	/**
	 * Setter: Date Start
	 * @param           DateTime|string $dateStart
	 */
	protected function setDateStart ( $dateStart ) {
		$this->setDateUniversal( $dateStart, 'dateStart' );
	}

	/**
	 * Getter: Date End
	 * @return          DateTime
	 */
	public function getDateEnd () {
		return $this->dateEnd;
	}

	/**
	 * Setter: Date End
	 * @param           DateTime|string $dateEnd
	 */
	protected function setDateEnd ( $dateEnd ) {
		$this->setDateUniversal( $dateEnd, 'dateEnd', true );
	}

	/**
	 * Universal setter for dates
	 *
	 * @param     DateTime|string $date         The date to set, either as string or DateTime instance
	 * @param     string          $property     The name of the property to set
	 * @param     bool            $end_of_day   Whether the time should be shifted to the end of the day
	 */
	protected function setDateUniversal ( $date, $property, $end_of_day = false ) {
		if ( is_string( $date ) and ( preg_match( Dates::STD_DATE_FORMAT_REGEX, $date ) or preg_match( Dates::STD_DATE_FORMAT_REGEX . ' ' . Dates::STD_TIME_FORMAT_REGEX, $date ) ) )
			$date = new DateTime( $date );

		if ( !$date instanceof DateTime )
			add_notice( sprintf( 'Argument one for %s has to be of type string or DateTime, %s given', __CLASS__ . '::' . __METHOD__, gettype( $date ) ) ) ;

		$date = Dates::applyTimeZone( $date );

		if ( $end_of_day === true )
			$date->setTime( 23, 59, 59 );

		$this->$property = Dates::applyTimeZone( $date );
	}

	/**
	 * Getter: Dummy
	 * @return    bool
	 */
	public function isDummy () {
		return $this->dummy;
	}

	/**
	 * Setter: Dummy
	 * @param     bool      $dummy
	 */
	protected function setDummy ( $dummy ) {
		$this->dummy = (bool) $dummy;
	}

}