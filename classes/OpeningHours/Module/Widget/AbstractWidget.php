<?php
/**
 *  Opening Hours: Module: Widget: AbstractWidget
 */

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\I18n;
use OpeningHours\Module\Shortcode\AbstractShortcode;

use WP_Widget;

abstract class AbstractWidget extends WP_Widget {

	/**
	 *  Widget Id
	 *  string with unique widget identifier
	 *
	 * @access     protected
	 * @type       string
	 */
	protected $widgetId;

	/**
	 *  Title
	 *  string with widget title
	 *
	 * @access     protected
	 * @type       string
	 */
	protected $title;

	/**
	 *  Description
	 *  string with widget description for widget admin panel
	 *
	 * @access     protected
	 * @type       string
	 */
	protected $description;

	/**
	 *  Shortcode
	 *  instance of shortcode class
	 *
	 * @access     protected
	 * @type       AbstractShortcode
	 */
	protected $shortcode;

	/**
	 *  Instance
	 *  associative array with:
	 *    key:    string w/ field name
	 *    value:  mixed w/ field value
	 *
	 * @access     protected
	 * @type       array
	 */
	protected $instance;

	/**
	 *  Fields
	 *  associative array with:
	 *    key:    string with field name
	 *    value:  associative array w/ field options
	 *
	 * @access     protected
	 * @type       array
	 */
	protected $fields = array();

	/**
	 *  Constructor
	 *
	 * @access     public
	 */
	public function __construct() {

		$this->init();

		$this->registerFields();

		parent::__construct( $this->getWidgetId(), $this->getTitle(), $this->getDescription() );

	}

	/**
	 * Render Field
	 * calls widget field renderer module
	 *
	 * @access     protected
	 *
	 * @param      string $field_name
	 *
	 * @return     string
	 */
	public function renderField( $field_name ) {

		return FieldRenderer::renderField( $this, $field_name );

	}

	/**
	 *  Widget Function
	 *  gets called by WordPress to render widget in front-end
	 *  wrapper function for widgetContent()
	 *
	 * @access     public
	 *
	 * @param      array $args
	 * @param      array $instance
	 */
	public function widget( array $args, array $instance ) {

		$filter_hook  = 'op_widget_' . $this->getWidgetId() . '_instance';

		/**
		 * Filter: Widget Instance
		 *
		 * @hook      op_widget_{widget_id}_instance
		 * @param     array             $instance   the instance array
		 * @param     AbstractWidget    $widget     the widget object
		 */
		$instance = apply_filters( $filter_hook, $instance, $this );

		$this->setInstance( $instance );

		$this->widgetContent( $args, $instance );

	}

	/**
	 * Widget Form Function
	 * gets called by WordPress to render widget form
	 *
	 * @access      public
	 *
	 * @param       array $instance
	 *
	 * @return      void
	 */
	public function form( array $instance ) {

		$this->setInstance( $instance );

		if ( method_exists( $this, 'customForm' ) ) :
			$this->customForm( $instance );

			return;
		endif;

		$extended = array();

		ob_start();

		foreach ( $this->getFields() as $field ) :

			if ( $field['extended'] !== true ) :
				echo $this->renderField( $field['name'] );
			else :
				$extended[] = $field;
			endif;

		endforeach;

		if ( ! count( $extended ) ) {
			return;
		}

		echo '<div class="extended-settings">';

		echo '<p><a class="collapse-toggle">' . __( 'More Settings', I18n::TEXTDOMAIN ) . '</a></p>';

		echo '<div class="settings-container hidden">';

		foreach ( $extended as $field ) {
			echo $this->renderField( $field['name'] );
		}

		echo '</div>';

		echo '</div>';

		$markup   = ob_get_contents();

		ob_end_clean();

		$filter_hook    = 'op_widget_' . $this->getWidgetId() . '_form_markup';

		/**
		 * Filter: Widget Form Markup
		 *
		 * @hook      op_widget_{widget_id}_form_markup
		 * @param     string            $markup   the form markup to be echoed
		 * @param     AbstractWidget    $widget   the widget object
		 */
		echo apply_filters( $filter_hook, $markup, $this );

	}

	/**
	 *  Register Widget
	 *  registers the Widget class in WordPress. Gets called in \OpeningHours\OpeningHours
	 *
	 * @access     public
	 * @static
	 */
	public static function registerWidget() {

		register_widget( get_called_class() );

	}

	/**
	 *  Init
	 *  set up widget configuration
	 *
	 * @access     protected
	 * @abstract
	 */
	abstract protected function init();

	/**
	 *  Register Fields
	 *  Add all fields for this Widget
	 *
	 * @access     protected
	 * @abstract
	 */
	abstract protected function registerFields();

	/**
	 *  Widget Content
	 *  use this method in the child class instead of the standard WP_Widget::widget()
	 *
	 * @access     protected
	 * @abstract
	 *
	 * @param      array $args
	 * @param      array $instance
	 */
	abstract protected function widgetContent( array $args, array $instance );

	/**
	 * Get PHP Date Format Info
	 *
	 * @access      public
	 * @static
	 *
	 * @return      string
	 */
	public static function getPhpDateFormatInfo () {
		return sprintf( '<a href="http://bit.ly/16Wsegh" target="blank">%s</a>', __( 'More about PHP date and time formats.', I18n::TEXTDOMAIN ) );
	}

	/**
	 *  Getter: Widget Id
	 *
	 * @access     public
	 * @return     string
	 */
	public function getWidgetId() {
		return $this->widgetId;
	}

	/**
	 *  Setter: Widget Id
	 *
	 * @access     protected
	 *
	 * @param      string $widgetId
	 *
	 * @return     AbstractWidget
	 */
	protected function setWidgetId( $widgetId ) {
		$this->widgetId = $widgetId;

		return $this;
	}

	/**
	 *  Getter: Title
	 *
	 * @access     public
	 * @return     string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 *  Setter: Title
	 *
	 * @access     protected
	 *
	 * @param      string $title
	 *
	 * @return     AbstractWidget
	 */
	protected function setTitle( $title ) {
		$this->title = $title;

		return $this;
	}

	/**
	 *  Getter: Description
	 *
	 * @access     public
	 * @return     string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 *  Setter: Description
	 *
	 * @access     protected
	 *
	 * @param      string $description
	 *
	 * @return     AbstractWidget
	 */
	public function setDescription( $description ) {
		$this->description = $description;

		return $this;
	}

	/**
	 *  Getter: Shortcode
	 *
	 * @access     public
	 * @return     AbstractShortcode
	 */
	public function getShortcode() {
		return $this->shortcode;
	}

	/**
	 *  Setter: Shortcode
	 *
	 * @access     protected
	 *
	 * @param      AbstractShortcode $shortcode
	 *
	 * @return     AbstractWidget
	 */
	protected function setShortcode( AbstractShortcode $shortcode ) {
		$this->shortcode = $shortcode;

		return $this;
	}

	/**
	 *  Getter: Fields
	 *
	 * @access     public
	 * @return     array
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 *  Setter: Fields
	 *
	 * @access     public
	 *
	 * @param      array $fields
	 *
	 * @return     AbstractWidget
	 */
	public function setFields( array $fields ) {
		$this->fields = $fields;

		return $this;
	}

	/**
	 *  Adder: Field
	 *
	 * @access     protected
	 *
	 * @param      string $field_name
	 * @param      array $field_options
	 *
	 * @return     AbstractWidget
	 */
	public function addField( $field_name, array $field_options ) {
		$field_options['name']       = $field_name;
		$this->fields[ $field_name ] = $field_options;

		return $this;
	}

	/**
	 *  Getter: (single) Field
	 *
	 * @access     public
	 *
	 * @param      string $field_name
	 *
	 * @return     array
	 */
	public function getField( $field_name ) {
		return $this->fields[ $field_name ];
	}

	/**
	 *  Getter: Instance
	 *
	 * @access     public
	 * @return     array
	 */
	public function getInstance() {
		return $this->instance;
	}

	/**
	 *  Setter: Instance
	 *
	 * @access     protected
	 *
	 * @param      array $instance
	 *
	 * @return     AbstractWidget
	 */
	protected function setInstance( array $instance ) {
		$this->instance = $instance;

		return $this;
	}

}

?>
