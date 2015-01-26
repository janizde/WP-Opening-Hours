<?php
/**
 * Opening Hours: Entity: Irregular Opening
 */

namespace OpeningHours\Entity;

use OpeningHours\Module\I18n;

use DateInterval;
use DateTime;
use InvalidArgumentException;

class IrregularOpening {

    /**
     * Name
     *
     * @access          protected
     * @type            string
     */
    protected $name;

    /**
     * Time Start
     *
     * @access          protected
     * @type            DateTime
     */
    protected $timeStart;

    /**
     * Time End
     *
     * @access          protected
     * @type            DateTime
     */
    protected $timeEnd;

    /**
     * Dummy
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

        $config     = static::validateConfig( $config );

        $this->setUp( $config );

    }

    /**
     * Validate Config
     * validates and filters configuration array for Irregular Opening
     *
     * @access          protected
     * @static
     * @param           array           $config
     * @return          array
     */
    protected static function validateConfig ( array $config ) {

        if ( !isset( $config[ 'dummy' ] ) or ( $config[ 'dummy' ] !== true and $config[ 'dummy' ] !== false ) )
            $config[ 'dummy' ]  = false;

        if ( $config[ 'dummy' ] ) :
            $config[ 'timeStart' ]  = new DateTime( 'now' );
            $config[ 'timeEnd' ]    = new DateTime( 'now' );
            $config[ 'name' ]       = null;

            return $config;
        endif;

        if ( !preg_match( $config[ 'timeStart' ], I18n::STD_TIME_FORMAT_REGEX ) )
            throw new InvalidArgumentException( "timeStart in config is not in valid time format" );

        if ( !preg_match( $config[ 'timeEnd' ], I18n::STD_TIME_FORMAT_REGEX ) )
            throw new InvalidArgumentException( "timeEnd in config is not in valid time format" );

        if ( !preg_match( $config[ 'date' ], I18n::STD_DATE_FORMAT_REGEX ) )
            throw new InvalidArgumentException( "date in config is not in valid date format" );

        if ( !isset( $config[ 'name' ] ) or empty( $config[ 'name' ] ) )
            throw new InvalidArgumentException( "name in config is not set or empty" );

        $config[ 'timeStart' ]  = I18n::mergeDateIntoTime( new DateTime( $config[ 'date' ] ), new DateTime( $config[ 'timeStart' ] ) );
        $config[ 'timeEnd' ]    = I18n::mergeDateIntoTime( new DateTime( $config[ 'date' ] ), new DateTime( $config[ 'timeEnd' ] ) );

        return $config;

    }

    /**
     * Set Up
     *
     * @access          protected
     * @param           array           $config
     */
    public function setUp ( array $config ) {

        $this->setName( $config[ 'name' ] );
        $this->setTimeStart( $config[ 'timeStart' ] );
        $this->setTimeEnd( $config[ 'timeEnd' ] );
        $this->setDummy( $config[ 'dummy' ] );

    }

    /**
     * Update Time
     *
     * @access          public
     */
    public function updateTime () {

        if (
            $this->getTimeStart()->format( I18n::STD_DATE_FORMAT ) === $this->getTimeEnd()->format( I18n::STD_DATE_FORMAT )
            and $this->getTimeStart() >= $this->getTimeEnd()
        ) :
            $this->getTimeEnd()->add( new DateInterval( 'P1D' ) );
        endif;

    }

    /**
     * Get Config
     *
     * @access          public
     * @return          array
     */
    public function getConfig () {

        return array(
            'name'      => $this->getName(),
            'timeStart' => $this->getTimeStart()->format( I18n::STD_TIME_FORMAT ),
            'timeEnd'   => $this->getTimeEnd()->format( I18n::STD_TIME_FORMAT ),
            'date'      => $this->getDate()
        );

    }

    /**
     * To String
     *
     * @access          public
     * @return          string
     */
    public function __toString () {

        return json_encode( $this->getConfig() );

    }

    /**
     * Factory: Dummy
     *
     * @access          public
     * @static
     * @return          IrregularOpening
     */
    public static function createDummy () {

        return new IrregularOpening( array(
            'dummy'     => true
        ) );

    }

    /**
     * Getter: Name
     *
     * @access          public
     * @return          string
     */
    public function getName () {
        return $this->name;
    }

    /**
     * Setter: Name
     *
     * @access          protected
     * @param           string          $name
     */
    protected function setName ( $name ) {
        $this->name = $name;
    }

    /**
     * Getter: Time Start
     *
     * @access          public
     * @return          DateTime
     */
    public function getTimeStart () {
        return $this->timeStart;
    }

    /**
     * Setter: Time Start
     *
     * @access          protected
     * @param           DateTime        $timeStart
     */
    public function setTimeStart ( DateTime $timeStart ) {
        $this->timeStart    = $timeStart;
        $this->updateTime();
    }

    /**
     * Getter: Time End
     *
     * @access          public
     * @return          DateTime
     */
    public function getTimeEnd () {
        return $this->timeEnd;
    }

    /**
     * Setter: Time End
     *
     * @access          protected
     * @param           DateTime        $timeEnd
     */
    protected function setTimeEnd ( DateTime $timeEnd ) {
        $this->timeEnd      = $timeEnd;
        $this->updateTime();
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
        $this->dummy    = $dummy;
    }

    /**
     * Getter: Date
     *
     * @access          public
     * @return          DateTime
     */
    public function getDate () {
        return new DateTime( $this->getTimeStart()->format( I18n::STD_DATE_FORMAT ), I18n::getDateTimeZone() );
    }

    /**
     * Setter: Date
     *
     * @access          protected
     * @param           DateTime|string $date
     */
    protected function setDate ( $date ) {

        if ( is_string( $date ) and preg_match( $date, I18n::STD_DATE_FORMAT_REGEX ) )
            $date   = new DateTime( $date, I18n::getDateTimeZone() );

        if ( !$date instanceof DateTime )
            throw new InvalidArgumentException( sprintf( "%::% requires Parameter 1 to be DateTime or string in correct date format. %s given", __CLASS__, __METHOD__, gettype( $date ) ) );

        if ( $this->getTimeStart() instanceof DateTime )
            $this->getTimeStart()->setDate( (int) $date->format( 'Y' ), (int) $date->format( 'm' ), (int) $date->format( 'd' ) );

        if ( $this->getTimeEnd() instanceof DateTime )
            $this->getTimeEnd()->setDate( (int) $date->format( 'Y' ), (int) $date->format( 'm' ), (int) $date->format( 'd' ) );

        $this->updateTime();

    }

}