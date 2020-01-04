<?php

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\OpeningHours;
use OpeningHours\Module\Shortcode\Schema as SchemaShortcode;

/**
 * Widget for Schema.org Shortcode
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Widget
 */
class Schema extends AbstractWidget {
  public function __construct() {
    $title = __('Opening Hours: Schema.org', 'wp-opening-hours');
    $description = __(
      'Inserts script-Tag containing schema.org specifications for a Set in JSON-LD format.',
      'wp-opening-hours'
    );
    parent::__construct('widget_op_schema', $title, $description, SchemaShortcode::getInstance());
  }

  /** @inheritdoc */
  protected function registerFields() {
    // Standard Fields
    $this->addField('set_id', array(
      'type' => 'select',
      'caption' => __('Set', 'wp-opening-hours'),
      'options_callback' => array(OpeningHours::getInstance(), 'getSetsOptions')
    ));

    $this->addField('exclude_holidays', array(
      'type' => 'checkbox',
      'caption' => __('Exclude Holidays', 'wp-opening-hours')
    ));

    $this->addField('exclude_irregular_openings', array(
      'type' => 'checkbox',
      'caption' => __('Exclude Irregular Openings', 'wp-opening-hours')
    ));

    $this->addField('schema_attr_type', array(
      'type' => 'text',
      'caption' => __('<code>@Type</code> property of the schema object', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('schema_attr_name', array(
      'type' => 'text',
      'caption' => __('<code>name</code> property of the schema object', 'wp-opening-hours'),
      'extended' => true,
      'description' => 'Leave empty to use the selected Set\'s name'
    ));

    $this->addField('schema_attr_description', array(
      'type' => 'text',
      'caption' => __('<code>description</code> property of the schema object', 'wp-opening-hours'),
      'extended' => true,
      'description' => 'Leave empty to use the selected Set\'s description'
    ));
  }
}
