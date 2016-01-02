<?php

namespace OpeningHours;

use OpeningHours\Module\AbstractModule;
use OpeningHours\Module as Module;
use OpeningHours\Module\Widget\AbstractWidget;

/**
 * Core Module for the Opening Hours Plugin
 *
 * @author      Jannik Portz
 * @package     OpeningHours
 */
class OpeningHours extends AbstractModule {

	/**
	 * Collection of all plugin modules
	 * @var       AbstractModule[]
	 */
	protected $modules;

	/**
	 * Collection of all plugin widgets
	 * @var       AbstractWidget[]
	 */
	protected $widgets;

	/** The plugin version */
	const VERSION = '2.0';

	/** The plugin prefix */
	const PREFIX = 'op_';

	/** Constructor for OpeningHours module */
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

	/** Registers callbacks for actions and filters */
	public function registerHookCallbacks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'loadResources' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'loadResources' ) );

		add_action( 'widgets_init', array( $this, 'registerWidgets' ) );
	}

	/** Registers all plugin widgets */
	public function registerWidgets() {
		foreach ( $this->widgets as $widgetClass )
			$widgetClass::registerWidget();
	}

	/**
	 * Enqueues resources
	 * @todo      separate callbacks for admin and frontend
	 */
	public function loadResources() {
		wp_register_script(
			self::PREFIX . 'js',
			plugins_url( 'dist/scripts/main.js', op_bootstrap_file() ),
			array( 'jquery', 'jquery-ui' ),
			self::VERSION,
			true
		);

		wp_register_style(
			self::PREFIX . 'css',
			plugins_url( 'dist/styles/main.css', op_bootstrap_file() )
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

		if ( is_admin() )
			wp_enqueue_script( self::PREFIX . 'js' );

		wp_localize_script( self::PREFIX . 'js', 'translations', Module\I18n::getJavascriptTranslations() );
	}

	public function activate () {
		// Silence is golden
	}

	public function deactivate () {
		// Silence is golden
	}
}
