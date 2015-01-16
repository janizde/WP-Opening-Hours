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

        if ( !preg_match( I18n::STD_DATE_FORMAT, $config[ 'dateStart' ] ) )
            throw new InvalidArgumentException( sprintf( 'dateStart in config does not correspond with standard date regex %s. %s given.', I18n::STD_DATE_FORMAT, $config[ 'dateStart' ] ) );

        if ( !isset( $config[ 'dateEnd' ] ) )
            throw new InvalidArgumentException( 'dateEnd not set in Holiday config.' );

        if ( !preg_match( I18n::STD_DATE_FORMAT, $config[ 'dateEnd' ] ) )
            throw new InvalidArgumentException( sprintf( 'dateEnd in config does not correspond with standard date regex %s. %s given.', I18n::STD_DATE_FORMAT, $config[ 'dateEnd' ] ) );

        if ( !isset( $config[ 'dummy' ] ) or !is_bool( $config[ 'dummy' ] ) )
            $config[ 'dummy' ]  = false;

        return $config;

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
        $this->setDateUniversal( $dateEnd, 'dateEnd' );
    }

    /**
     * Setter: Set Universal Date
     * universal setter for dates
     *
     * @access          protected
     * @param           DateTime|string $date
     * @param           string          $property
     */
    protected function setDateUniversal ( $date, $property ) {

        if ( is_string( $date ) and ( preg_match( I18n::STD_DATE_FORMAT, $date ) or preg_match( I18n::STD_DATE_TIME_FORMAT, $date ) ) )
            $date  = new DateTime( $date );

        if ( $date instanceof DateTime )
            throw new InvalidArgumentException( sprintf( 'Argument one for %s has to be of type string or DateTime, %s given', __CLASS__ . '::' . __METHOD__, gettype( $date ) ) );

        $this->$property  = I18n::applyTimeZone( $date );

    }

}