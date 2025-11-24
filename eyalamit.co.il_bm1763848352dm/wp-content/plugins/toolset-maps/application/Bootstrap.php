<?php

namespace OTGS\Toolset\Maps;

use Toolset\DynamicSources\DynamicSources;

/**
 * Class Bootstrap
 * @package OTGS\Toolset\Maps
 * @since 1.5.3
 */
class Bootstrap {
	protected $soft_dependencies = array();

	public function __construct( array $do_available ) {
		$this->soft_dependencies = $do_available;
	}

	public function init() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 30 );
		add_action( 'toolset_common_loaded', array( $this, 'register_autoloaded_classes' ), 10 );
		add_action( 'toolset_common_loaded', array( $this, 'initialize_classes' ), 20 );
	}

	/**
	 * Register autoload classmap to Toolset Common autoloader
	 */
	public function register_autoloaded_classes() {
		$classmap = include( TOOLSET_ADDON_MAPS_PATH . '/application/autoload_classmap.php' );

		do_action( 'toolset_register_classmap', $classmap );
	}

	/**
	 * Initialize autoloaded classes, including those based on soft_dependencies.
	 */
	public function initialize_classes() {
		if ( in_array( 'views', $this->soft_dependencies ) ) {

		}
	}

	/**
	 * Do some early initializations, like DS API.
	 */
	public function plugins_loaded() {
		// Init DS API
		$ds_loader = require_once TOOLSET_ADDON_MAPS_PATH . '/vendor/toolset/dynamic-sources/server/ds-instance.php';
		ts_dynamic_sources_adjust_ds_instance( TOOLSET_ADDON_MAPS_PATH, $ds_loader['version'], $ds_loader['path'] );
	}
}
