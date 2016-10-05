<?php

namespace OpeningHours\Module\Widget;

use OpeningHours\Fields\FieldRenderer;
use OpeningHours\Fields\WidgetFieldRenderer;
use OpeningHours\Module\Shortcode\AbstractShortcode as Shortcode;
use WP_Widget;

/**
 * Abstraction for a Widget
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Widget
 */
abstract class AbstractWidget extends WP_Widget {

  /**
   * String with unique widget identifier
   * @var       string
   */
  protected $widgetId;

  /**
   * The Widget title
   * @var       string
   */
  protected $title;

  /**
   * Widget description for widget admin panel
   * @var       string
   */
  protected $description;

  /**
   * Singleton instance of shortcode
   * @var       Shortcode
   */
  protected $shortcode;

  /**
   * Associative array with:
   *  key:    string with field name
   *  value:  associative array w/ field options
   *
   * @var       array
   */
  protected $fields;

  /**
   * The FieldRenderer used to render the form fields
   * @var       FieldRenderer
   */
  protected $fieldRenderer;

  /**
   * AbstractWidget constructor.
   *
   * @param     string    $id          The widget id
   * @param     string    $title       The widget title
   * @param     array     $description The widget description
   * @param     Shortcode $shortcode   The shortcode singleton instance
   */
  public function __construct ( $id, $title, $description, Shortcode $shortcode ) {
    $this->id = $id;
    $this->title = $title;
    $this->description = $description;
    $this->shortcode = $shortcode;
    $this->fields = array();
    $this->fieldRenderer = new WidgetFieldRenderer($this);
    $this->registerFields();

    parent::__construct($id, $title, $description);
  }

  /**
   * Renders a single field from the collection
   *
   * @param     array $field    The field config array
   * @param     array $instance The current widget instance
   *
   * @return    string                The field markup
   */
  public function renderField ( array $field, array $instance ) {
    $value = array_key_exists($field['name'], $instance) ? $instance[$field['name']] : null;
    return $this->fieldRenderer->getFieldMarkup($field, $value);
  }

  /**
   * Widget Function
   * Gets called by WordPress to render widget in front-end
   * Wrapper function for widgetContent()
   *
   * @param     array $args     The widget args including the sidebar args
   * @param     array $instance The current widget instance
   */
  public function widget ( $args, $instance ) {
    $this->widgetContent($args, $instance);
  }

  /**
   * Widget Form Function
   * Gets called by WordPress to render widget form
   *
   * @param     array $instance The current widget instance to populate the fields with
   *
   * @return    void
   */
  public function form ( $instance ) {
    $extended = array();

    ob_start();

    foreach ($this->fields as $field) {
      if (!array_key_exists('extended', $field) || $field['extended'] !== true) {
        echo $this->renderField($field, $instance);
      } else {
        $extended[] = $field;
      }
    }

    if (count($extended) < 1)
      return;

    echo '<div class="extended-settings">';
    echo '<p><a class="collapse-toggle">' . __('More Settings', 'wp-opening-hours') . '</a></p>';
    echo '<div class="settings-container hidden">';

    foreach ($extended as $field)
      echo $this->renderField($field, $instance);

    echo '</div>';
    echo '</div>';

    $markup = ob_get_contents();
    ob_end_clean();

    $filter_hook = 'op_widget_' . $this->widgetId . '_form_markup';
    echo apply_filters($filter_hook, $markup, $this);
  }

  /** Registers the Widget class in WordPress. Gets called in \OpeningHours\OpeningHours */
  public static function registerWidget () {
    register_widget(get_called_class());
  }

  /** Adds all fields for this Widget */
  abstract protected function registerFields ();

  /**
   * Prints the widget content
   *
   * @param     array $args     The widget args including the sidebar args
   * @param     array $instance The current widget instance
   */
  protected function widgetContent ( array $args, array $instance ) {
    echo $this->shortcode->renderShortcode(array_merge($args, $instance));
  }

  /**
   * Returns string containing a link to more information on PHP date and time formats
   * @return      string
   */
  public static function getPhpDateFormatInfo () {
    return sprintf('<a href="http://bit.ly/16Wsegh" target="blank">%s</a>', __('More about PHP date and time formats.', 'wp-opening-hours'));
  }

  /**
   * Getter: Widget Id
   * @return    string
   */
  public function getWidgetId () {
    return $this->widgetId;
  }

  /**
   * Getter: Title
   * @return    string
   */
  public function getTitle () {
    return $this->title;
  }

  /**
   * Getter: Shortcode
   * @return    Shortcode
   */
  public function getShortcode () {
    return $this->shortcode;
  }

  /**
   * Adds a field to the collection
   *
   * @param     string $name    The field name
   * @param     array  $options The field options
   */
  public function addField ( $name, array $options ) {
    $options['name'] = $name;
    $this->fields[$name] = $options;
  }

  /**
   * Getter: (single) Field
   *
   * @param     string $name The name to search for
   *
   * @return    array               The field options
   */
  public function getField ( $name ) {
    return $this->fields[$name];
  }
}
