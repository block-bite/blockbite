<?php

namespace Blockbite\Blockbite;

use Blockbite\Blockbite\Register;
use Blockbite\Blockbite\Rest\Api;

class Hooks {

	/**
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * @
	 * @var SettingsPage
	 */
	protected $editor;

	/**
	 * @var Frontend
	 */
	protected $frontend;

	/**
	 * @var Library
	 */
	protected $library;

	/**
	 * @var Tailwind
	 */
	protected $tailwind;

	/**
	 * @var SettingsPage
	 */
	protected $settingsPage;



	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		$this->editor = new Editor($plugin);
		$this->frontend = new Frontend($plugin);
		$this->library = new Library($plugin);
		$this->tailwind = new Tailwind($plugin);
		$this->settingsPage = new SettingsPage($plugin);
	}

	/**
	 * Register all hooks
	 */
	public function addHooks() {
		add_action( 'plugins_loaded', [$this->plugin, 'pluginLoaded']);
		// add_action( 'admin_menu', [$this->settingsPage, 'addPage' ]);
		add_action( 'rest_api_init', [$this->plugin->getRestApi(), 'registerRoutes']);
		add_action( 'enqueue_block_assets', [$this->editor, 'registerAssets']);
		add_action('init', [$this->editor, 'initBlocks']);
		add_filter('block_categories_all', [$this->editor, 'registerBlockCategory']);
		add_action( 'wp_enqueue_scripts', [$this->frontend, 'registerAssetsFrontend']);
		add_action( 'admin_init', [$this->frontend, 'registerAssetsBackend']);
		add_action( 'admin_init', [$this->library, 'registerAssets']);
		add_action( 'admin_init', [$this->tailwind, 'registerAssets'], 10);
		
		add_action('wp_head', [$this->frontend, 'blockbite_css']);
		add_filter('body_class', [$this->frontend, 'blockbite_body_class']);
		add_action( 'admin_enqueue_scripts', [$this->settingsPage, 'registerAssets']);
		add_action( 'admin_menu', [$this->settingsPage, 'addPage' ]);
	}

	/**
	 * Remove Hooks
	 */
	public function removeHooks() {
		remove_action( 'plugins_loaded', [$this->plugin, 'pluginLoaded']);
		// remove_action( 'admin_menu', [$this->settingsPage, 'addPage' ]);
		remove_action( 'rest_api_init', [$this->plugin->getRestApi(), 'registerRoutes']);
		remove_action( 'admin_enqueue_scripts', [$this->editor, 'registerAssets']	);
		remove_action( 'wp_enqueue_scripts', [$this->frontend, 'registerAssetsFrontend']);
		remove_action( 'admin_init', [$this->frontend, 'registerAssetsBackend']);
		remove_action( 'admin_init', [$this->library, 'registerAssets']	);
		remove_action( 'admin_init', [$this->tailwind, 'registerAssets']	);
		add_action( 'admin_enqueue_scripts', [$this->settingsPage, 'registerAssets']);
		remove_action( 'admin_menu', [$this->settingsPage, 'addPage' ]);
	}

}
