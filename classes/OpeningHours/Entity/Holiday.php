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
     * Config
     * associative config array with:
     *  key:    config key
     *  value:  config value
     *
     * @access          protected
     * @type            array
     */
    protected $config;

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
     * @param           array           $config
     */
    public function __construct ( array $config ) {

        /** validate/filter config and set property */
        $this->setConfig( static::validateConfig( $config ) );

    }

    /**
     * Set Up
     * sets up holiday object
     *
     * @access          protected
     */
    protected function setUp () {

        $config = $this->getConfig();

        $this->setDateStart( new DateTime( $config[ 'dateStart' ] ) );
        $this->setDateEnd( new DateTime( $config[ 'dateEnd' ] ) );
        $this->setDummy( $config[ 'dummy' ] );

    }

    /**
     * Is Closed
     * determines if venue is currently closed due to this holiday object
     *
     * @access          public
     * @param           DateTime        $now
     * @return          bool
     */
    public function isClosed ( $now = null ) {

        if ( $now === null )
            $now = I18n::getTimeNow();

        return ( $this->getDateStart() <= $now and $this->getDateEnd() >= $now );

    }

    /**
     * Is Open
     * determines if venue is not closed due to this holiday object
     * complement of isClosed
     *
     * @access          public
     * @param           DateTime        $now
     * @return          bool
     */
    public function isOpen ( $now = null ) {

        return !$this->isClosed( $now );

    }

    /**
     * Validate Config
     *
     * @access          public
     * @static
     * @param           array           $config
     * @return          array
     * @throws          InvalidArgumentException
     */
    public static function validateConfig ( array $config ) {

        if ( !isset( $config[ 'dateStart' ] ) )
            throw new InvalidArgumentException( 'dateStart not set in Holiday config.' );

        if ( !preg_match( I18n::STD_DATE_FORMAT_REGEX, $config[ 'dateStart' ] ) )
            throw new InvalidArgumentException( sprintf( 'dateStart in config does not correspond with standard date regex %s. %s given.', I18n::STD_DATE_FORMAT, $config[ 'dateStart' ] ) );

        if ( !isset( $config[ 'dateEnd' ] ) )
            throw new InvalidArgumentException( 'dateEnd not set in Holiday config.' );

        if ( !preg_match( I18n::STD_DATE_FORMAT_REGEX, $config[ 'dateEnd' ] ) )
            throw new InvalidArgumentException( sprintf( 'dateEnd in config does not correspond with standard date regex %s. %s given.', I18n::STD_DATE_FORMAT, $config[ 'dateEnd' ] ) );

        if ( !isset( $config[ 'dummy' ] ) or !is_bool( $config[ 'dummy' ] ) )
            $config[ 'dummy' ]  = false;

        return $config;

    }

    /**
     * Sort Strategy
     * sorts Holiday objects by dateStart (ASC)
     *
     * @access          public
     * @static
     * @param           Holiday         $holiday_1
     * @param           Holiday         $holiday_2
     * @return          int
     */
    public static function sortStrategy ( Holiday $holiday_1, Holiday $holiday_2 ) {

        if ( $holiday_1->getDateStart() > $holiday_2->getTimeStart() ) :
            return 1;
        elseif ( $holiday_1->getDateStart() < $holiday_2->getTimeStart() ) :
            return -1;
        else :
            return 0;
        endif;

    }

    /**
     * Getter: Config
     *
     * @access          public
     * @return          array
     */
    public function getConfig () {
        return $this->config;
    }

    /**
     * Setter: Config
     *
     * @access          protected
     * @param           array           $config
     */
    protected function setConfig ( array $config ) {
        $this->config = $config;
    }

    /**
     * Getter: Date Start
     *
     * @access          public
     * @return          DateTime
     */
    public function getDateStart () {
        return $this->dateStart;
    }

    /**
     * Setter: Date Start
     *
     * @access          protected
     * @param           DateTime|string $dateStart
     */
    protected function setDateStart ( $dateStart ) {
        $this->setDateUniversal( $dateStart, 'dateStart' );
    }

    /**
     * Getter: Date End
     *
     * @access          public
     * @return          DateTime
     */
    public function getDateEnd () {
        return $this->dateEnd;
    }

    /**
     * Setter: Date End
     *
     * @access          protected
     * @param           DateTime|string $dateEnd
     */
    protected function setDateEnd ( $dateEnd ) {
        $this->setDateUniversal( $dateEnd, 'dateEnd', true );
    }

    /**
     * Setter: Set Universal Date
     * universal setter for dates
     *
     * @access          protected
     * @param           DateTime|string $date
     * @param           bool            $end_of_day
     * @param           string          $property
     */
    protected function setDateUniversal ( $date, $property, $end_of_day = false ) {

        if ( is_string( $date ) and ( preg_match( I18n::STD_DATE_FORMAT_REGEX, $date ) or preg_match( I18n::STD_DATE_FORMAT_REGEX . ' ' . I18n::STD_TIME_FORMAT_REGEX, $date ) ) )
            $date  = new DateTime( $date );

        if ( $date instanceof DateTime )
            throw new InvalidArgumentException( sprintf( 'Argument one for %s has to be of type string or DateTime, %s given', __CLASS__ . '::' . __METHOD__, gettype( $date ) ) );

        $date   = I18n::applyTimeZone( $date );

        if ( $end_of_day === true )
            $date->setTime( 23, 59, 59 );

        $this->$property  = I18n::applyTimeZone( $date );

    }

    /**
     * Getter: Dummy
     *
     * @access          public
     * @return          bool
     */
    public function isDummy () {
        return $this->dummy;
    }

    /**
     * Setter: Dummy
     *
     * @access          protected
     * @param           bool            $dummy
     */
    protected function setDummy ( $dummy ) {
        $this->dummy = (bool) $dummy;
    }

}