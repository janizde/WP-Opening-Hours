<?php

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Module\AbstractModule;
use OpeningHours\Misc\Helpers;

use InvalidArgumentException;

/**
 * Abstraction for a Shortcode
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Shortcode
 */
abstract class AbstractShortcode extends AbstractModule {

	/**
	 * The tag used for the shortcode
	 * @var       string
	 */
	protected $shortcodeTag;

	/**
	 * Associative array with:
	 *  key:    attribute name
	 *  value:  default value
	 *
	 * @var       array
	 */
	protected $defaultAttributes = array();

	/**
	 * Associative array with:
	 *  key:    attribute name
	 *  Value:  sequential array with accepted values. First array element is default/fallback value.
	 *
	 * @var       array
	 */
	protected $validAttributeValues = array();

	/**
	 * Path to shortcode template file. Default directory is /views/
	 * @var       string
	 */
	protected $templatePath;

	public function __construct() {
		$this->registerHookCallbacks();
	}

	/** Registers Hook Callbacks */
	protected function registerHookCallbacks() {
		add_action( 'init', array( $this, 'registerShortCode' ) );
	}

	/** Registers Shortcode */
	public function registerShortcode() {
		$this->init();

		try {
			$this->validate();
			add_shortcode( $this->shortcodeTag, array( $this, 'shortcodeCallback' ) );
		} catch ( InvalidArgumentException $e ) {
			add_notice( $e->getMessage(), 'error' );
		}
	}

	/**
	 * Validates the current Shortcode state
	 *
	 * @throws    InvalidArgumentException    On validation error
	 */
	public function validate() {
		if ( empty( $this->shortcodeTag ) )
			throw new InvalidArgumentException( __( 'Shortcode has no tag name and could not be registered', self::TEXTDOMAIN ) );
	}

	/**
	 * Shortcode Callback
	 *
	 * @param     array     $attributes The attributes for the shortcode
	 *
	 * @return    string    The shortcode markup
	 */
	public function shortcodeCallback ( array $attributes ) {
		$attributes = Helpers::unsetEmptyValues( $attributes );
		$attributes = shortcode_atts( $this->defaultAttributes, $attributes, $this->shortcodeTag );

		if ( !array_key_exists( 'shortcode', $attributes ) )
			$attributes['shortcode']  = $this;

		ob_start();
		$this->shortcode( $attributes );
		$shortcodeMarkup = ob_get_contents();
		ob_end_clean();

		$filterHook = 'op_shortcode_' . $this->shortcodeTag . '_markup';
		apply_filters( $filterHook, $shortcodeMarkup, static::getInstance() );

		return $shortcodeMarkup;
	}

	/**
	 * Renders the Shortcode Template
	 *
	 * @param     array     $attributes The shortcode attributes
	 * @param     array     $variables  The variables for the template
	 * @param     string    $require    Whether to require the template file once or always
	 *
	 * @return    string    The shortcode markup
	 */
	public function renderShortcodeTemplate ( array $attributes, $variables = array(), $require = 'always' ) {
		if ( empty( $this->templatePath ) )
			return '';

		$variables['attributes'] = $attributes;

		return self::renderTemplate( $this->templatePath, $variables, $require );
	}

	/**
	 * Applies filters on each attribute
	 *
	 * @param     array     $attributes The attributes to filter
	 *
	 * @return    array     The filtered attributes
	 */
	protected function filterAttributes ( array $attributes ) {
		$validValues = $this->validAttributeValues;
		$filterHookAttributes = 'op_shortcode_' . $this->shortcodeTag . '_attributes';

		$attributes = apply_filters( $filterHookAttributes, $attributes, static::getInstance() );

		foreach ( $attributes as $key => &$value ) {
			$filterHook = 'op_shortcode_' . $this->shortcodeTag . '_' . $key;
			$value      = apply_filters( $filterHook, $value, static::getInstance() );

			if ( !array_key_exists( $key, $validValues ) or !is_array( $validValues[ $key ] ) or
			     count( $validValues[ $key ] ) < 1 or in_array( $value, $validValues[ $key ] ) or
			     !isset( $validValues[ $key ][0] ) )
				continue;

			$value = $validValues[ $key ][0];
		}
		unset( $key, $value );
		return $attributes;
	}

	/**
	 * Getter: Shortcode Tag
	 * @return    string
	 */
	public function getShortcodeTag () {
		return $this->shortcodeTag;
	}

	/**
	 * Setter: Shortcode Tag
	 * @param     string    $shortcodeTag
	 */
	public function setShortcodeTag( $shortcodeTag ) {
		$this->shortcodeTag = apply_filters( 'op_shortcode_tag', $shortcodeTag );
	}


	/**
	 * Getter: Default Attribute (single)
	 *
	 * @param     string    $attributeName
	 *
	 * @return    mixed
	 */
	public function getDefaultAttribute( $attributeName ) {
		return ( isset( $this->defaultAttributes[ $attributeName ] ) )
			? $this->defaultAttributes[ $attributeName ]
			: null;
	}

	/**
	 *  Shortcode Function
	 *
	 * @access     public
	 * @abstract
	 *
	 * @param      array $attributes
	 */
	abstract public function shortcode ( array $attributes );

	/**
	 *  Init
	 *  Sets up attributes
	 *
	 * @access    protected
	 * @abstract
	 */
	abstract protected function init();

}