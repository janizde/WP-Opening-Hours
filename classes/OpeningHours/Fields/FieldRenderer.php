<?php

namespace OpeningHours\Fields;

/**
 * Abstraction for a FieldRenderer
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Fields
 */
class FieldRenderer {

  /**
   * Filter the field configuration
   *
   * @param     array $field associative config-array for the field
   *
   * @return    array               filtered config-array for the field
   */
  protected function filterField ( array $field ) {
    $field = $this->moveToAttributes($field, array('required', 'placeholder'));
    if (array_key_exists('class', $field['attributes'])) {
      if (!is_array($field['attributes']['class']))
        $field['attributes']['class'] = preg_split('/\s+/', $field['attributes']['class']);

      $field['attributes']['class'][] = 'widefat';
    } else {
      $field['attributes']['class'] = array('widefat');
    }

    if (array_key_exists('options_callback', $field) && is_callable($field['options_callback']))
      $field['options'] = call_user_func($field['options_callback']);

    if (array_key_exists('datalist', $field) && is_callable($field['datalist']))
      $field['datalist'] = call_user_func($field['datalist']);

    return $field;
  }

  /**
   * Actually renders the field with the filtered configuration
   *
   * @param     array $field filtered config-array for the field
   * @param     mixed $value the value that the field shall be populated with. (default: null)
   */
  protected function renderField ( array $field, $value = null ) {
    $id = array_key_exists('id', $field) ? $field['id'] : '';
    $caption = array_key_exists('caption', $field) ? $field['caption'] : '';
    $type = $field['type'];
    $name = $field['name'];
    $options = array_key_exists('options', $field) ? $field['options'] : array();

    $attributes = array_key_exists('attributes', $field) && is_array($field['attributes'])
      ? $field['attributes']
      : array();

    /** Start of Field Element */
    echo '<p>';

    /** Field Label */
    if (!empty($caption) and !in_array($type, array(FieldTypes::CHECKBOX, FieldTypes::HEADING)))
      printf('<label for="%s">%s</label>', $id, $caption);

    switch ($type) {
      case FieldTypes::TEXT:
      case FieldTypes::TIME:
      case FieldTypes::EMAIL:
      case FieldTypes::URL:
        if (array_key_exists('datalist', $field) && is_array($field['datalist'])) {
          $attributes['list'] = $id . '_datalist';
          $datalistOptions = array_map(function ($item) {
            return sprintf('<option value="%s">', $item);
          }, $field['datalist']);
        }

        $attrString = $this->generateAttributesString($attributes);
        printf('<input type="%s" id="%s" name="%s" value="%s" %s />', $type, $id, $name, $value, $attrString);
        if (isset($datalistOptions)) {
          printf('<datalist id="%s">%s</datalist>', $id . '_datalist', implode(PHP_EOL, $datalistOptions));
        }
        break;

      case FieldTypes::TEXTAREA:
        $attrString = $this->generateAttributesString($attributes);
        printf('<textarea id="%s" name="%s" %s>%s</textarea>', $id, $name, $attrString, $value);
        break;

      case FieldTypes::SELECT:
      case FieldTypes::SELECT_MULTI:
        $is_multi = ($type == FieldTypes::SELECT_MULTI);

        if ($is_multi) {
          $attributes['multiple'] = 'multiple';
          $attributes['size'] = 5;
          $name .= '[]';
          $attributes['style'] = 'height: 50px;';
        }

        $attrString = $this->generateAttributesString($attributes);

        printf('<select id="%s" name="%s" %s>', $id, $name, $attrString);
        foreach ($options as $key => $caption) {
          $selected = 'selected="selected"';

          if ($is_multi) {
            $selected = in_array($key, (array)$value) ? $selected : '';
          } else {
            $selected = ($key == $value) ? $selected : null;
          }

          printf('<option value="%s" %s>%s</option>', $key, $selected, $caption);
        }

        echo '</select>';
        break;

      case FieldTypes::CHECKBOX:
        if (!empty($value))
          $attributes['checked'] = 'checked';

        $attrString = $this->generateAttributesString($attributes);
        printf('<label for="%s"><input type="checkbox" name="%s" id="%s" %s /> %s</label>', $id, $name, $id, $attrString, $caption);
        break;

      case FieldTypes::HEADING:
        if (!array_key_exists('heading', $field))
          break;

        printf('<h3>%s</h3>', trim($field['heading']));
        break;
    }

    if (array_key_exists('description', $field))
      printf('<span class="op-field-description">%s</span>', $field['description']);

    if (isset($description) and is_string($description)) {
      echo '<span class="op-widget-description">' . $description . '</span>';
    }

    echo '</p>';
  }

  /**
   * Returns the markup for the field
   *
   * @param     array $field unfiltered config-array for the field
   * @param     mixed $value the value that the field shall be populated with. (default: null)
   *
   * @return    string              the field markup
   */
  public function getFieldMarkup ( array $field, $value = null ) {
    $field = $this->filterField($field);
    ob_start();
    $this->renderField($field, $value);
    $markup = ob_get_contents();
    ob_end_clean();
    return $markup;
  }

  /**
   * Generates a string containing HTML attributes from an associative array.
   * If an attribute value is an array itself it will be converted to a space-separated string
   *
   * @param     array $attributes Associative array of attributes with attribute key and value
   *
   * @return    string                HTML attribute string
   */
  protected function generateAttributesString ( array $attributes ) {
    $str = '';
    foreach ($attributes as $key => $value) {
      if (is_array($value))
        $value = implode(' ', $value);

      $str .= sprintf('%s="%s" ', $key, $value);
    }

    if (count($attributes) > 0)
      $str = substr($str, 0, -1);

    return $str;
  }

  /**
   * Moves field config elements to attributes
   *
   * @param     array $field      associative field config array
   * @param     array $properties array of properties which to move to attributes
   *
   * @return    array                 field config with moved attributes
   */
  protected function moveToAttributes ( array $field, array $properties ) {
    if (!array_key_exists('attributes', $field))
      $field['attributes'] = array();

    foreach ($properties as $property) {
      if (!array_key_exists($property, $field))
        continue;

      $field['attributes'][$property] = $field[$property];
      unset($field[$property]);
    }

    return $field;
  }
}