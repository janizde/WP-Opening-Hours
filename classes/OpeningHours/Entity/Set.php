<?php
/**
 *  Opening Hours: Entity: Set
 */

namespace OpeningHours\Entity;

use OpeningHours\Misc\ArrayObject;
use OpeningHours\Module\I18n;
use OpeningHours\Module\CustomPostType\Set as SetCpt;

use WP_Post;
use DateTime;
use InvalidArgumentException;

class Set {

  /**
   * Constants
   */
  const WP_ACTION_BEFORE_SETUP    = 'op_set_before_setup';

  /**
   *  Config
   *
   *  @access     protected
   *  @type       array
   */
  protected $config;

  /**
   *  Periods
   *
   *  @access     protected
   *  @type       ArrayObject
   */
  protected $periods;

  /**
   *  Id
   *
   *  @access     protected
   *  @type       int
   */
  protected $id;

  /**
   *  Post
   *
   *  @access     protected
   *  @type       WP_Post
   */
  protected $post;

  /**
   *  Parent Id
   *
   *  @access     protected
   *  @type       int
   */
  protected $parentId;

  /**
   *  Parent Post
   *
   *  @access     protected
   *  @type       WP_Post
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
   *  Has Parent
   *
   *  @access     protected
   *  @type       bool
   */
  protected $hasParent;

  /**
   *  Constructor
   *
   *  @access     public
   *  @param      WP_Post|int     $post
   *  @return     Set
   */
  public function __construct ( $post ) {

    $this->setPeriods( new ArrayObject );

    if ( !is_int( $post ) and !$post instanceof WP_Post )
      throw new InvalidArgumentException( sprintf( 'Argument one for __construct has to be of type WP_Post or int. %s given', gettype( $post ) ) );

    if ( is_int( $post ) )
      $post = get_post( $post );

    $this->setId( $post->ID );
    $this->setPost( $post );
    $this->setParentId( $post->ID );
    $this->setParentPost( $post );

    $this->setUp();

    return $this;

  }

  /**
   *  Set Up
   *
   *  @access     public
   */
  public function setUp () {

    // Check for Child Posts
    $childPosts   = get_posts( array(
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

    /**
     * Load Config
     */
    $post_meta = get_post_meta( $this->getId(), SetCpt::PERIODS_META_KEY, true );

    if ( self::isValidConfig( $post_meta ) )
      $this->setConfig( $post_meta );

    if ( !is_array( $this->getConfig() ) or !count( $this->getConfig() ) )
      return;

    foreach ( $this->getConfig() as $periodConfig ) :
      $this->getPeriods()->addElement( new Period( $periodConfig ) );
    endforeach;

    $post_detail_description  = get_post_detail( 'description', $this->getId() );
    $post_parent_detail_description = get_post_detail( 'description', $this->getParentId() );

    /**
     * Set Description
     */
    if ( !empty( $post_detail_description ) ) :
      $this->setDescription( $post_detail_description );

    elseif ( !empty( $post_parent_detail_description ) ):
      $this->setDescription( $post_parent_detail_description );

    endif;
  }

  /**
   *  Is Valid Config
   *  Validates configuration array
   *
   *  @access     public
   *  @static
   *  @param      array     $config
   *  @return     bool
   */
  public static function isValidConfig ( $config ) {

    if ( !is_array( $config ) )
      return false;

    return true;

  }

  /**
   * Child Matches Criteria
   * checks if child posts matches the set criteria
   *
   * @access    public
   * @static
   * @param     WP_Post     $post
   * @return    bool
   */
  public static function childMatchesCriteria ( WP_Post $post ) {

    $detail_date_start  = get_post_detail( 'date-start', $post->ID );
    $detail_date_end    = get_post_detail( 'date-end', $post->ID );
    $detail_week_scheme = get_post_detail( 'week-scheme', $post->ID );

    $detail_date_start  = ( !empty( $detail_date_start ) ) ? new DateTime( $detail_date_start, I18n::getDateTimeZone() ) : null;
    $detail_date_end    = ( !empty( $detail_date_end ) ) ? new DateTime( $detail_date_end, I18n::getDateTimeZone() ) : null;

    /**
     * Skip if no criteria is set
     */
    if ( $detail_date_start == null and $detail_date_end == null and ( $detail_week_scheme == 'all' or empty( $detail_week_scheme ) ) )
      return false;

    $date_time_now      = I18n::getTimeNow();

    /**
     * Date Range
     */
    if ( $detail_date_start != null and $date_time_now < $detail_date_start )
      return false;

    if ( $detail_date_end != null and $date_time_now > $detail_date_end )
      return false;

    /**
     * Week Scheme
     */
    $week_number_modulo = (int) $date_time_now->format('W') % 2;

    if ( $detail_week_scheme == 'even' and $week_number_modulo === 1 )
      return false;

    if ( $detail_week_scheme == 'odd' and $week_number_modulo === 0 )
      return false;

    return true;

  }

  /**
   *  Is Parent
   *  checks if this set is a parent set
   *
   *  @access     public
   *  @return     bool
   */
  public function isParent () {
    return ( $this->getId() === $this->getParentId() );
  }

  /**
   *  Add Dummy Periods
   *
   *  @access     public
   */
  public function addDummyPeriods() {

    foreach ( I18n::getWeekdaysNumeric() as $id => $name ) :

      if ( !count( $this->getPeriodsByDay( $id ) ) ) :

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
   *  Is Open – Opening Hours
   *  only evaluates standard opening periods
   *
   *  @access       public
   *  @return       bool
   */
  public function isOpenOpeningHours () {

    foreach ( $this->getPeriods() as $period ) :

      if ( $period->isOpen() )
        return true;

    endforeach;

    return false;

  }

  /**
   *  Is Open
   *  evaluates all aspects
   *
   *  @access       public
   *  @return       bool
   */
  public function isOpen () {

    /** Holidays */

    /** Special Openings */

    /** Opening Hours */
    return $this->isOpenOpeningHours();

  }

  /**
   *  Get Sets from Posts
   *
   *  @access     public
   *  @static
   *  @param      array     $posts
   *  @return     array
   */
  public static function getSetsFromPosts ( array $posts ) {

    foreach ( $posts as &$post ) :

      if ( $post instanceof WP_Post or is_numeric( $post ) )
        $post   = new Set( $post );

    endforeach;

    return $posts;

  }

  /**
   *  Getter: Config
   *
   *  @access     public
   *  @return     array
   */
  public function getConfig () {
    return $this->config;
  }

  /**
   *  Setter: Config
   *
   *  @access     protected
   *  @param      array       $config
   *  @return     Set
   */
  protected function setConfig ( array $config ) {
    $this->config = $config;
    return $config;
  }

  /**
   *  Getter: Periods
   *
   *  @access     public
   *  @return     ArrayObject
   */
  public function getPeriods () {
    return $this->periods;
  }

  /**
   *  Getter: Periods By Day
   *
   *  @access     public
   *  @param      array|int   $days
   *  @return     array
   */
  public function getPeriodsByDay ( $days ) {

    if ( !is_array( $days ) and !is_numeric( $days ) )
      throw new InvalidArgumentException( sprintf( 'Argument 1 of getPeriodsByDay must be integer or array. %s given.', gettype( $days ) ) );

    if ( !is_array( $days ) )
      $days  = array( $days );

    $periods  = array();

    foreach ( $this->getPeriods() as $period ) :
      if ( in_array( $period->getWeekday(), $days ) )
        $periods[]  = $period;
    endforeach;

    return $periods;

  }

  /**
   *  Getter: (all) Periods Grouped By Day
   *
   *  @access       public
   *  @return       array
   */
  public function getPeriodsGroupedByDay () {

    $periods  = array();

    foreach ( I18n::getWeekdaysNumeric() as $id => $caption )
      $periods[ $id ]   = $this->getPeriodsByDay( $id );

    return $periods;

  }

  /**
   *  Setter: Periods
   *
   *  @access     public
   *  @param      ArrayObject $periods
   *  @return     Set
   */
  public function setPeriods ( ArrayObject $periods ) {
    $this->periods = $periods;
    return $this;
  }

  /**
   *  Getter: Id
   *
   *  @access     public
   *  @return     int
   */
  public function getId () {
    return $this->id;
  }

  /**
   *  Setter: Id
   *
   *  @access     public
   *  @param      int     $id
   *  @return     Set
   */
  public function setId ( $id ) {
    $this->id = $id;
    return $this;
  }

  /**
   *  Getter: Post
   *
   *  @access     public
   *  @return     WP_Post
   */
  public function getPost () {
    return $this->post;
  }

  /**
   *  Setter: Post
   *
   *  @access     public
   *  @param      WP_Post|int   $post
   *  @return     Set
   */
  public function setPost ( $post ) {

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
   *  Getter: Parent Id
   *
   *  @access     public
   *  @return     int
   */
  public function getParentId () {
    return $this->parentId;
  }

  /**
   *  Setter: Parent Id
   *
   *  @access     public
   *  @param      int       $parentId
   *  @return     Set
   */
  public function setParentId ( $parentId ) {
    $this->parentId = $parentId;
    return $this;
  }

  /**
   *  Getter: Parent Post
   *
   *  @access     public
   *  @return     WP_Post
   */
  public function getParentPost () {
    return ( !$this->hasParent() and !$this->parentPost instanceof WP_Post )
      ? $this->post
      : $this->parentPost;
  }

  /**
   *  Setter: Parent Post
   *
   *  @access     public
   *  @param      WP_Post|int   $parentPost
   *  @return     Set
   */
  public function setParentPost ( $parentPost ) {

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
  public function getDescription () {
    return $this->description;
  }

  /**
   * Setter: Description
   *
   * @access      protected
   * @param       string        $description
   */
  protected function setDescription ( $description ) {
    $this->description    = $description;
  }

  /**
   *  Getter: Has Parent
   *
   *  @access     public
   *  @return     bool
   */
  public function hasParent () {
    return $this->hasParent;
  }

  /**
   *  Setter: Has Parent
   *
   *  @access     public
   *  @param      bool    $hasParent
   *  @return     Set
   */
  public function setHasParent ( $hasParent ) {
    $this->hasParent = $hasParent;
    return $this;
  }

}
