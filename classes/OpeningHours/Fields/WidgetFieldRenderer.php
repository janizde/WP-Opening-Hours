<?php

namespace OpeningHours\Fields;

use OpeningHours\Module\Widget\AbstractWidget;

/**
 * Field Renderer for Widget form field
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Fields
 */
class WidgetFieldRenderer extends FieldRenderer {

  /**
   * The widget whose fields to render
   * @var       AbstractWidget
   */
  protected $widget;

  public function __construct ( AbstractWidget $widget ) {
    $this->widget = $widget;
  }

  /**
   * Use the widget's get_field_id and get_field_name methods to determine the element id and name
   * @inheritdoc
   */
  protected function filterField ( array $field ) {
    $field = parent::filterField($field);

    if (array_key_exists('default_placeholder', $field) && $field['default_placeholder'] === true) {
      $sc = $this->widget->getShortcode();
      $default = $sc->getDefaultAttribute($field['name']);

      if (!empty($default) && (is_string($default) || is_numeric($default)))
        $field['attributes']['placeholder'] = $default;
    }

    $field['id'] = $this->widget->get_field_id($field['name']);
    $field['name'] = $this->widget->get_field_name($field['name']);

    return $field;
  }
}