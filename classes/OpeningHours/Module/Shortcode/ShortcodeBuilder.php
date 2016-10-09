<?php

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Form\Form;

class ShortcodeBuilder {

  protected static $fieldMap = array(
    'text' => 'textbox',
    'select' => 'listbox',
    'checkbox' => 'checkbox'
  );

  /**
   * The Shortcode tag
   * @var       string
   */
  protected $shortcodeTag;

  /**
   * Display name of shortcode
   * @var       string
   */
  protected $name;

  /**
   * The form containing all fields for the Shortcode
   * @var       Form
   */
  protected $form;

  public function __construct ($shortcodeTag, $name, Form $form) {
    $this->shortcodeTag = $shortcodeTag;
    $this->name = $name;
    $this->form = $form;
  }

  /**
   * Generates data for the Shortcode builder
   * @return    array
   */
  public function getShortcodeBuilderData () {
    $data = array(
      'shortcodeTag' => $this->shortcodeTag,
      'shortcodeName' => $this->name
    );

    $fields = array();
    foreach ($this->form->getFields() as $field) {
      if (!array_key_exists($field['type'], self::$fieldMap))
        continue;

      $tinyMCEField = array(
        'type' => self::$fieldMap[$field['type']],
        'name' => $field['name'],
        'label' => $field['caption']
      );

      if ($field['type'] === 'select') {
        $oldOptions = array_key_exists('options_callback', $field) && is_callable($field['options_callback'])
          ? call_user_func($field['options_callback'])
          : $field['options'];

        $options = array();
        foreach ($oldOptions as $value => $caption) {
          $options[] = array(
            'text' => $caption,
            'value' => $value
          );
        }

        $tinyMCEField['values'] = $options;
      }

      $fields[] = $tinyMCEField;
    }

    $data['fields'] = $fields;
    return $data;
  }
}