<?php

namespace Blockbite\Blockbite;

use Blockbite\Blockbite\Register;
use Blockbite\Blockbite\Rest\Api;
use Blockbite\Blockbite\Controllers\Database as DbController;



class Hooks
{

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
	 * @var SettingsPage
	 */
	protected $settingsPage;



	public function __construct(Plugin $plugin)
	{
		$this->plugin = $plugin;
		$this->editor = new Editor($plugin);
		$this->frontend = new Frontend($plugin);
		$this->library = new Library($plugin);
		$this->settingsPage = new SettingsPage($plugin);
	}

	/**
	 * Register all hooks
	 */
	public function addHooks()
	{

		add_action('plugins_loaded', [$this->plugin, 'pluginLoaded']);
		add_action('admin_notices', [$this->plugin, 'adminNotice']);

		// add_action( 'admin_menu', [$this->settingsPage, 'addPage' ]);
		add_action('rest_api_init', [$this->plugin->getRestApi(), 'registerRoutes']);
		add_action('enqueue_block_editor_assets', [$this->editor, 'registerAssets']);
		add_action('init', [$this->editor, 'initBlocks']);
		add_filter('block_categories_all', [$this->editor, 'registerBlockCategory']);
		add_action('wp_enqueue_scripts', [$this->frontend, 'registerAssetsFrontend']);
		add_action('admin_init', [$this->frontend, 'registerAssetsBackend']);
		add_action('admin_init', [$this->library, 'registerAssets']);
		add_action('wp_head', [$this->frontend, 'blockbite_css']);
		add_action('admin_enqueue_scripts', [$this->settingsPage, 'registerAssets']);
		add_action('admin_menu', [$this->settingsPage, 'addPage']);
		add_filter('body_class', [$this->frontend, 'blockbite_css_body']);
		add_action('enqueue_block_assets', [$this->editor, 'registerTailwindCdn'], 10);
		add_action('enqueue_block_assets', [$this->editor, 'registerTailwindConfig'], 11);
		add_action('enqueue_block_assets', [$this->editor, 'registerSwiperCdn'], 12);

		$dynamic_block_result = DbController::getRecordByHandle('dynamic_block_support');
		if (isset($dynamic_block_result->content)) {
			$dynamic_blocks = json_decode($dynamic_block_result->content);
			if (is_array($dynamic_blocks)) {
				foreach ($dynamic_blocks as $block) {
					add_filter('render_block_' . $block, [$this->frontend, 'biteClassDynamicBlocks'], 10, 2);
				}
			}
		}
	}



	/**
	 * Remove Hooks
	 */
	public function removeHooks()
	{
		remove_action('plugins_loaded', [$this->plugin, 'pluginLoaded']);
		// remove_action( 'admin_menu', [$this->settingsPage, 'addPage' ]);
		remove_action('rest_api_init', [$this->plugin->getRestApi(), 'registerRoutes']);
		remove_action('admin_enqueue_scripts', [$this->editor, 'registerAssets']);
		remove_action('wp_enqueue_scripts', [$this->frontend, 'registerAssetsFrontend']);
		remove_action('admin_init', [$this->frontend, 'registerAssetsBackend']);
		remove_action('admin_init', [$this->library, 'registerAssets']);
		remove_action('admin_enqueue_scripts', [$this->settingsPage, 'registerAssets']);
		remove_action('admin_menu', [$this->settingsPage, 'addPage']);
		remove_action('enqueue_block_assets', [$this->editor, 'registerTailwindCdn'], 10);
		remove_action('enqueue_block_assets', [$this->editor, 'registerSwiperCdn'], 12);
	}
}
