<?php
/**
 *  Opening Hours
 */

namespace OpeningHours;

use OpeningHours\Module\AbstractModule;
use OpeningHours\Module as Module;

class OpeningHours extends AbstractModule {

	/**
	 *  Modules
	 *
	 * @access    protected
	 * @type      array
	 */
	protected $modules = array();

	/**
	 *  Constants
	 */
	const VERSION = '2.0';
	const PREFIX = 'op_';
	const DEBUG_MODE = false;

	/**
	 *  Constructor
	 *
	 * @access    protected
	 */
	protected function __construct() {

		$this->registerHookCallbacks();

		$this->modules = array(
			'OpeningHours'                => Module\OpeningHours::getInstance(),
			'I18n'                        => Module\I18n::getInstance(),
			'Ajax'                        => Module\Ajax::getInstance(),
			'CustomPostType\Set'          => Module\CustomPostType\Set::getInstance(),
			'Shortcode\IsOpen'            => Module\Shortcode\IsOpen::getInstance(),
			'Shortcode\Overview'          => Module\Shortcode\Overview::getInstance(),
			'Shortcode\Holidays'          => Module\Shortcode\Holidays::getInstance(),
			'Shortcode\IrregularOpenings' => Module\Shortcode\IrregularOpenings::getInstance()
		);

		$this->widgets = array(
			'OpeningHours\Module\Widget\Overview',
			'OpeningHours\Module\Widget\IsOpen',
			'OpeningHours\Module\Widget\Holidays',
			'OpeningHours\Module\Widget\IrregularOpenings'
		);

	}

	/**
	 *  Register callbacks for actions and filters
	 *
	 * @access    public
	 */
	public function registerHookCallbacks() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'loadResources' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'loadResources' ) );

		add_action( 'widgets_init', array( $this, 'registerWidgets' ) );
	}

	/**
	 *  Register Widgets
	 * @access      public
	 * @wp_action    widgets_init
	 */
	public function registerWidgets() {

		if ( ! is_array( $this->widgets ) ) {
			return;
		}

		foreach ( $this->widgets as $widgetClass ) {
			$widgetClass::registerWidget();
		}

	}


	/**
	 *  Enqueues CSS, JavaScript, etc
	 *
	 * @access      public
	 * @static
	 * @wp_action    wp_enqueue_scripts, admin_enqueue_scripts
	 */
	public static function loadResources() {

		wp_register_script(
			self::PREFIX . 'js',
			plugins_url( 'javascript/opening-hours.js', op_bootstrap_file() ),
			array( 'jquery', 'jquery-ui' ),
			self::VERSION,
			true
		);

		wp_register_style(
			self::PREFIX . 'css',
			plugins_url( 'css/opening-hours.css', op_bootstrap_file() )
		);

		// Backend Styles and Scripts
		wp_enqueue_script( 'jquery-ui' );


		if ( ! wp_script_is( 'jquery-ui' ) ) :
			wp_register_script( 'jquery-ui', 'http://code.jquery.com/ui/1.10.4/jquery-ui.min.js', array( 'jquery' ) );
			wp_enqueue_script( 'jquery-ui' );
		endif;


		Module\Ajax::injectAjaxUrl( self::PREFIX . 'js' );


		// Frontend Styles and Scripts
		wp_enqueue_style( self::PREFIX . 'css' );
		wp_enqueue_script( self::PREFIX . 'js' );

		wp_localize_script( self::PREFIX . 'js', 'translations', Module\I18n::getJavascriptTranslations() );

	}

}
