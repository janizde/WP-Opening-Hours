<?php

namespace OpeningHours\Fields;

/**
 * FieldRenderer for Fields in a meta box
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Fields
 */
class MetaBoxFieldRenderer extends FieldRenderer {

  /**
   * The POST namespace for the fields
   * @var       string
   */
  protected $namespace;

  public function __construct ( $namespace ) {
    $this->namespace = $namespace;
  }

  /** @inheritdoc */
  public function filterField ( array $field ) {
    $field = parent::filterField($field);
    $field['id'] = sprintf('%s_%s', $this->namespace, $field['name']);
    $field['name'] = sprintf('%s[%s]', $this->namespace, $field['name']);

    return $field;
  }
}