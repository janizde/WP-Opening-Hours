<?php
/**
 *  Opening Hours: Shortcodes: AsbtractShortcode
 */

if ( class_exists( 'OP_AbstractShortCode' ) )
  return;

abstract class OP_AbstractShortcode extends OP_AbstractModule {

  /**
   *  Shortcode Tag
   *
   *  @access     protected
   *  @type       string
   */
  protected $shortcodeTag;

  /**
   *  Default Attributes
   *
   *  @access     protected
   *  @type       array
   */
  protected $defaultAttributes = array();

  /**
   *  Constructor
   *
   *  @access     public
   */
  public function __construct () {

    $this->registerHookCallbacks();

  }

  /**
   *  Register Hook Callbacks
   *
   *  @access     protected
   */
  protected function registerHookCallbacks () {

    add_action( 'init',       array( $this, 'registerShortCode' ) );

  }

  /**
   *  Register Shortcode
   *
   *  @access     public
   *  @wp_acton   int
   */
  public function registerShortcode () {

    try {
      $this->validate();
      add_shortcode( $this->getShortcodeTag(), array( $this, 'renderShortcode' ) );
    } catch ( InvalidArgumentException $e ) {
      add_notice( $e->getMessage(), 'error' );
    }

  }

  /**
   *  Validate Shortcode
   *
   *  @access     protected
   *  @throws     InvalidArgumentException
   */
  public function validate () {

    if ( empty( $this->getShortcodeTag() ) )
      throw new InvalidArgumentException( __( 'Shortcode has no tagname and could not be registered', self::TEXTDOMAIN ) );

  }

  /**
   *  Render Shortcode
   *
   *  @access     public
   *  @param      array     $attributes
   *  @return     string
   */
  public function renderShortcode ( array $attributes ) {

    $attributes   = shortcode_atts( $this->getDefaultAttributes, $attributes, $this->getShortcodeTag );

    ob_start();

    $this->shortcode( $attributes );

    $shortcodeMarkup  = ob_get_contents();
    ob_end_clean();

    return $shortcodeMarkup;

  }

  /**
   *  Getter: Shortcode Tag
   *
   *  @access     public
   *  @return     string
   */
  public function getShortcodeTag () {
    return $this->shortcodeTag;
  }

  /**
   *  Setter: Shortcode Tag
   *
   *  @access     public
   *  @param      string    $shortcodeTag
   *  @return     OP_AbstractShortcode
   */
  public function setShortcodeTag ( $shortcodeTag ) {
    $this->shortcodeTag     = apply_filters( 'op_shortcode_tag', $shortcodeTag );
    return $this;
  }

  /**
   *  Getter: Default Attributes
   *
   *  @access     public
   *  @return     array
   */
  public function getDefaultAttributes () {
    return $this->defaultAttributes;
  }

  /**
   *  Setter: Default Attributes
   *
   *  @access     protected
   *  @param      array     $defaultAttributes
   *  @return     OP_AbstractModule
   */
  protected function setDefaultAttributes ( array $defaultAttributes ) {
    $this->defaultAttributes  = $defaultAttributes;
    return $this;
  }

  /**
   *  Shortcode Function
   *
   *  @access     public
   *  @param      array     $attributes
   */
  abstract public function shortcode ( array $attributes );

}
?>
