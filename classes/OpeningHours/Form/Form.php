<?php

namespace OpeningHours\Form;

class Form {

  /**
   * Array of field configurations
   * @var       array
   */
  protected $fields;

  public function __construct () {
    $this->fields = array();
  }

  /**
   * Appends a field to the list
   * @param     string    $name     Name of field
   * @param     array     $field    Associative array containing field configuration
   */
  public function addField ($name, array $field) {
    $field['name'] = $name;
    $this->fields[] = $field;
  }

  /**
   * Returns all fields
   * @return    array     Array of field configurations
   */
  public function getFields () {
    return $this->fields;
  }
}