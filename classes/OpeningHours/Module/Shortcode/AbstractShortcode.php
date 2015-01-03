<?php
/**
 *  Opening Hours: Module: Shortcode: AbstractShortcode
 */

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Module\AbstractModule;

use InvalidArgumentException;

abstract class AbstractShortcode extends AbstractModule {

  /**
   *  Shortcode Tag
   *  The tag used for the shortcode
   *
   *  @access     protected
   *  @type       string
   */
  protected $shortcodeTag;

  /**
   *  Default Attributes
   *  Associative array with:
   *    key:    attribute name
   *    value:  default value
   *
   *  @access     protected
   *  @type       array
   */
  protected $defaultAttributes = array();

  /**
   *  Valid Attribute Values
   *  Associative array with:
   *    key:    attribute name
   *    value:  sequencial array with accepted values. First array element is default/fallback value.
   *
   *  @access     protected
   *  @type       array
   */
  protected $validAttributeValues = array();

  /**
   *  Template Path
   *  Path to template file to render shortcode in. Default directory is /views/
   *
   *  @access     protected
   *  @type       string
   */
  protected $templatePath;

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

    $this->init();

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

    if ( empty( $this->shortcodeTag ) )
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

    $attributes   = shortcode_atts( $this->getDefaultAttributes(), $attributes, $this->getShortcodeTag() );

    ob_start();

    $this->shortcode( $attributes );

    $shortcodeMarkup  = ob_get_contents();
    ob_end_clean();

    return $shortcodeMarkup;

  }

  /**
   *  Render Shortcode Template
   *
   *  @access     protected
   *  @param      array     $attributes
   *  @param      string    $require
   *  @return     string
   */
  public function renderShortcodeTemplate ( array $attributes, $variables = array(), $require = 'always' ) {

    if ( empty( $this->templatePath ) )
      return;

    $variables[ 'attributes' ] = (array) $attributes;

    return self::renderTemplate(
      $this->getTemplatePath(),
      $variables,
      $require
    );

  }

  /**
   *  Filter Attributes
   *  applies filters on each attribute
   *
   *  @access     protected
   *  @static
   *  @param      array       $attributes
   *  @return     array
   */
  protected function filterAttributes ( array $attributes ) {

    if ( empty( $this->shortcodeTag ) ) :
      trigger_error( 'Tried to filter shortcode attributes before shortcode tag has been set.' );
      return;
    endif;

    $validValues  = $this->getValidAttributeValues();

    foreach ( $attributes as $key => &$value ) :

      /** Apply WordPress filters */
      $filter_hook  = $this->getShortcodeTag() . '_' . $key;

      $value  = apply_filters( $filter_hook, $value );

      /** Check if value is valid */
      if ( isset( $validValues[ $key ] )
        and is_array( $validValues[ $key ] )
        and count( $validValues[ $key ] )
        and !in_array( $value, $validValues[ $key ] )
        and isset( $validValues[ $key ][0] ) ) :

        $value  = $validValues[ $key ][0];

      endif;

    endforeach;

    unset( $key, $value );

    return $attributes;

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
   *  @return     AbstractShortcode
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
   *  Getter: Default Attribute (single)
   *
   *  @access     public
   *  @param      string    $attribute_name
   *  @return     mixed
   */
  public function getDefaultAttribute ( $attribute_name ) {
    return ( isset( $this->defaultAttributes[ $attribute_name ] ) )
      ? $this->defaultAttributes[ $attribute_name ]
      : null;
  }

  /**
   *  Setter: Default Attributes
   *
   *  @access     protected
   *  @param      array     $defaultAttributes
   *  @return     AbstractShortcode
   */
  protected function setDefaultAttributes ( array $defaultAttributes ) {
    $this->defaultAttributes  = $defaultAttributes;
    return $this;
  }

  /**
   *  Getter: Valid Attribute Values
   *
   *  @access     public
   *  @return     array
   */
  public function getValidAttributeValues () {
    return $this->validAttributeValues;
  }

  /**
   *  Setter: Valid Attribute Values
   *
   *  @access     protected
   *  @param      array     $validAttributeValues
   *  @return     AbstractShortcode
   */
  protected function setValidAttributeValues ( array $validAttributeValues ) {
    $this->validAttributeValues = $validAttributeValues;
  }

  /**
   *  Getter: Template Path
   *
   *  @access     public
   *  @return     string
   */
  public function getTemplatePath () {
    return $this->templatePath;
  }

  /**
   *  Setter: Template Path
   *
   *  @access     protected
   *  @param      string    $templatePath
   *  @return     AbstractShortcode
   */
  protected function setTemplatePath ( $templatePath ) {
    $this->templatePath = $templatePath;
  }

  /**
   *  Shortcode Function
   *
   *  @access     public
   *  @abstract
   *  @param      array     $attributes
   */
  abstract public function shortcode ( array $attributes );

  /**
   *  Init
   *  Sets up attributes
   *
   *  @access    protected
   *  @abstract
   */
  abstract protected function init ();

}
?>
