<?php
/**
 *  Opening Hours: Entity: Set
 */

namespace OpeningHours\Entity;

use OpeningHours\Misc\ArrayObject;

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
   *  @param      array     $config
   *  @return     Set
   */
  public function __construct ( array $config ) {

    $this->setPeriods( new ArrayObject );

    if ( $config !== null and count( $config ) ) :
      $this->setConfig( $config );
      $this->setUp();
    endif;

    return $this;

  }

  /**
   *  Set Up
   *
   *  @access     public
   *  @return     Set
   */
  public function setUp () {

    $config   = $this->getConfig();

    if ( !isset( $config['periods'] ) or !count( $config['periods'] ) )
      return $this;

    foreach ( $config['periods'] as $periodConfig ) :
      if ( Period::isValidConfig( $periodConfig ) )
        $this->getPeriods()->addElement( new Period( $periodConfig ) );
    endforeach;

    return $this;

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
