<?php

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Fields\MetaBoxFieldRenderer;
use OpeningHours\Module\I18n;
use OpeningHours\Util\MetaBoxPersistence;
use WP_Post;

/**
 * Meta Box for setting up set details
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\CustomPostType\MetaBox
 */
class SetDetails extends AbstractMetaBox {

	/**
	 * Array of field configuration arrays
	 * @var       array[]
	 */
	protected $fields;

	/**
	 * The MetaBoxPersistence for the detail meta box
	 * @var       MetaBoxPersistence
	 */
	protected $persistence;

	/**
	 * The FieldRenderer used to render the meta box fields
	 * @var       MetaBoxFieldRenderer
	 */
	protected $fieldRenderer;

	public function __construct () {
		parent::__construct( 'op_meta_box_set_details', __('Set Details', I18n::TEXTDOMAIN), self::CONTEXT_SIDE, self::PRIORITY_HIGH );
		$this->fieldRenderer = new MetaBoxFieldRenderer( $this->id );
		$this->persistence = new MetaBoxPersistence( $this->id );

		$this->fields = array(
			array(
				'type' => 'textarea',
				'name' => 'description',
				'caption' => __('Description', I18n::TEXTDOMAIN),
				'show_when' => 'child'
			),
			array (
				'type' => 'date',
				'name' => 'dateStart',
				'caption' => __('Date Start', I18n::TEXTDOMAIN),
				'show_when' => 'child'
			),
			array(
				'type' => 'date',
				'name' => 'dateEnd',
				'caption' => __('Date End', I18n::TEXTDOMAIN),
				'show_when' => 'child'
			),
			array(
				'type' => 'select',
				'name' => 'weekScheme',
				'caption' => __('Week Scheme', I18n::TEXTDOMAIN),
				'options' => array(
					'all' => __('Every week', I18n::TEXTDOMAIN),
					'even' => __('Even weeks only', I18n::TEXTDOMAIN),
					'odd' => __('Odd weeks only', I18n::TEXTDOMAIN)
				),
				'show_when' => 'child'
			),
			array(
				'type' => 'heading',
				'name' => 'childSetNotice',
				'heading'     => __( 'Add a Child-Set', I18n::TEXTDOMAIN ),
				'description' => __( 'You may add a child set that overwrites the parent Opening Hours in specific time range. Use the post type hierarchy.', I18n::TEXTDOMAIN ),
				'show_when' => 'parent'
			)
		);
	}

	/** @inheritdoc */
	public function renderMetaBox ( WP_Post $post ) {
		$this->nonceField();

		$type = $post->post_parent == 0 ? 'parent' : 'child';

		foreach ( $this->fields as $field ) {
			if ( array_key_exists('show_when', $field) && $field['show_when'] != $type )
				continue;

			$value = $this->persistence->getValue( $field['name'], $post->ID );
			echo $this->fieldRenderer->getFieldMarkup( $field, $value );
		}
	}

	/** @inheritdoc */
	protected function saveData ( $post_id, WP_Post $post, $update ) {
		$data = $_POST[ $this->id ];
		foreach ( $this->fields as $field ) {
			$value = array_key_exists( $field['name'], $data ) ? $data[ $field['name'] ] : null;
			$this->persistence->putValue( $field['name'], $value, $post_id );
		}
	}

	/**
	 * Returns the persistence manager for the meta box
	 * @return    MetaBoxPersistence
	 */
	public function getPersistence () {
		return $this->persistence;
	}
}