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

class Set {

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
   *  Has Parent
   *
   *  @access     protected
   *  @type       bool
   */

  /**
   *  Constructor
   *
   *  @access     public
   *  @param      WP_Post|int     $config
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

    // Check for appliable child posts
    $childPosts   = get_posts( array(
      'post_type'   => SetCpt::CPT_SLUG,
      'post_parent' => $this->getId()
    ) );

    // Skip if Set has no child posts
    if ( !count( $childPosts ) )
      return;

    // Load Config
    $post_meta = get_post_meta( $this->getId(), SetCpt::PERIODS_META_KEY, true );

    if ( self::isValidConfig( $post_meta ) )
      $this->setConfig( $post_meta );

    if ( !is_array( $this->getConfig() ) or !count( $this->getConfig() ) )
      return;

    foreach ( $this->getConfig() as $periodConfig ) :
      $this->getPeriods()->addElement( new Period( $periodConfig ) );
    endforeach;

  }

  /**
   *  Is Valid Config
   *  Validates configuration array
   *
   *  @access     public
   *  @static
   *  @param      array     $configuration
   *  @return     bool
   */
  public static function isValidConfig ( $config ) {

    if ( !is_array( $config ) )
      return false;

    return true;

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
   *  @param      array|int   $day
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
      ? $this->getPost()
      : $this->parentPost();
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
?>
