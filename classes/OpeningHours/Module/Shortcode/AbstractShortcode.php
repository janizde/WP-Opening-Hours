<?php

namespace OpeningHours\Module\Shortcode;

use InvalidArgumentException;
use OpeningHours\Module\AbstractModule;
use OpeningHours\Util\Helpers;
use OpeningHours\Util\ViewRenderer;

/**
 * Abstraction for a Shortcode
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Shortcode
 */
abstract class AbstractShortcode extends AbstractModule {

  const FILTER_ATTRIBUTES = 'op_shortcode_attributes';

  const FILTER_TEMPLATE = 'op_shortcode_template';

  const FILTER_SHORTCODE_MARKUP = 'op_shortcode_markup';

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

  public function __construct () {
    $this->registerHookCallbacks();
  }

  /** Registers Hook Callbacks */
  protected function registerHookCallbacks () {
    add_action('init', array($this, 'registerShortCode'));
  }

  /** Registers Shortcode */
  public function registerShortcode () {
    $this->init();

    try {
      $this->validate();
      add_shortcode($this->shortcodeTag, array($this, 'renderShortcode'));
    } catch (InvalidArgumentException $e) {
      add_notice($e->getMessage(), 'error');
    }
  }

  /**
   * Validates the current Shortcode state
   *
   * @throws    InvalidArgumentException    On validation error
   */
  public function validate () {
    if (empty($this->shortcodeTag))
      throw new InvalidArgumentException(__('Shortcode has no tag name and could not be registered', 'wp-opening-hours'));
  }

  /**
   * Shortcode Callback
   *
   * @param     array $attributes The attributes for the shortcode
   *
   * @return    string    The shortcode markup
   */
  public function renderShortcode ( $attributes ) {
    if (!is_array($attributes))
      return '';

    $attributes = Helpers::unsetEmptyValues($attributes);
    $attributes = shortcode_atts($this->defaultAttributes, $attributes, $this->shortcodeTag);

    if (!array_key_exists('shortcode', $attributes))
      $attributes['shortcode'] = $this;

    ob_start();
    $this->shortcode($attributes);
    $shortcodeMarkup = ob_get_contents();
    ob_end_clean();

    /**
     * Filter shortcode markup. Callback should be:
     * @param   string            $markup         The final Shortcode output as HTML
     * @param   AbstractShortcode $shortcode      The shortcode singleton instance
     * @return  string                            The filtered Shortcode output
     */
    return apply_filters(self::FILTER_SHORTCODE_MARKUP, $shortcodeMarkup, $this);
  }

  /**
   * Renders the Shortcode Template
   *
   * @param     array   $attributes   The shortcode attributes
   * @param     string  $templatePath Path to the template relative to view directory
   *
   * @return    string    The shortcode markup
   */
  public function renderShortcodeTemplate ( array $attributes, $templatePath ) {
    /**
     * Filter shortcode template path. Callback should be:
     * @param   string            $templatePath   Absolute path to template file
     * @param   AbstractShortcode $shortcode      The shortcode singleton instance
     * @return  string                            The filtered template path
     */
    $templatePath = apply_filters(self::FILTER_TEMPLATE, $templatePath, $this);

    /**
     * Filter shortcode attributes path. Callback should be:
     * @param   array             $templatePath   Associative array with all shortcode attributes
     * @param   AbstractShortcode $shortcode      The shortcode singleton instance
     * @return  array                             Filtered attributes array
     */
    $attributes = apply_filters(self::FILTER_ATTRIBUTES, $attributes, $this);

    if (empty($templatePath))
      return '';

    $data = array(
      'attributes' => $attributes
    );

    $templatePath = sprintf('%s/views/%s', op_plugin_path(), $templatePath);

    $view = new ViewRenderer($templatePath, $data);
    return $view->getContents();
  }

  /**
   * Applies filters on each attribute
   *
   * @param     array $attributes The attributes to filter
   *
   * @return    array     The filtered attributes
   */
  protected function filterAttributes ( array $attributes ) {
    $validValues = $this->validAttributeValues;

    foreach ($attributes as $key => &$value) {
      if (!array_key_exists($key, $validValues) or !is_array($validValues[$key]) or
        count($validValues[$key]) < 1 or in_array($value, $validValues[$key]) or
        !isset($validValues[$key][0])
      )
        continue;

      $value = $validValues[$key][0];
    }
    unset($key, $value);
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
   *
   * @param     string $shortcodeTag
   */
  public function setShortcodeTag ( $shortcodeTag ) {
    $this->shortcodeTag = apply_filters('op_shortcode_tag', $shortcodeTag);
  }

  /**
   * Getter: Default Attribute (single)
   *
   * @param     string $attributeName
   *
   * @return    mixed
   */
  public function getDefaultAttribute ( $attributeName ) {
    return (isset($this->defaultAttributes[$attributeName]))
      ? $this->defaultAttributes[$attributeName]
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
  abstract protected function init ();

}