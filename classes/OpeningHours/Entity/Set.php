<?php
/**
 *  Opening Hours: Entity: Set
 */

namespace OpeningHours\Entity;

use OpeningHours\Misc\ArrayObject;
use OpeningHours\Module\CustomPostType\Set as SetCpt;

use WP_Post;

if ( class_exists( 'OpeningHours\Entity\Set' ) )
  return;

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

    // Determine child Post

    $post_meta = get_pot_meta( SetCpt::PERIODS_META_KEY, $this->getId() );

    if ( self::isValidConfig( $post_meta ) )
      $this->setConfig( $post_meta );

    foreach ( $this->getConfig() as $periodConfig ) :
      $this->getPeriods()->addElement( new Period( $periodConfig ) );
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
   *  Setter: Periods
   *
   *  @access     public
   *  @param      array     $periods
   *  @return     Set
   */
  public function setPeriods ( array $periods ) {
    $this->getPeriods()->exchangeArray( $periods );
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
