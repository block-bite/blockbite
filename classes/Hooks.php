<?php

namespace Blockbite\Blockbite;

use Blockbite\Blockbite\Register;
use Blockbite\Blockbite\Rest\Api;
use Blockbite\Blockbite\Controllers\Database as DbController;
use Blockbite\Blockbite\Controllers\BlockRender as BlockRender;
use Blockbite\Blockbite\Controllers\EditorSettings as EditorSettings;
use Blockbite\Blockbite\PostTypes as PostTypes;

class Hooks
{

	/**
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * @
	 * @var SettingsNavigation
	 */
	protected $editor;

	/**
	 * @var Frontend
	 */
	protected $frontend;

	/**
	 * @var Settings
	 */
	protected $settings;



	/**
	 * @var SettingsNavigation
	 */
	protected $settingsNavigation;


	/**
	 * @var BlockRender
	 */
	protected $render;



	public function __construct(Plugin $plugin)
	{
		$this->plugin = $plugin;
		$this->editor = new Editor($plugin);
		$this->frontend = new Frontend($plugin);
		$this->settingsNavigation = new SettingsNavigation($plugin);
		$this->settings = new Settings($plugin);
		$this->render = new BlockRender($plugin);
	}

	/**
	 * Register all hooks
	 */
	public function addHooks()
	{

		add_action('plugins_loaded', [$this->plugin, 'pluginLoaded']);
		add_action('admin_notices', [$this->plugin, 'adminNotice']);
		add_action('admin_menu', [$this->settingsNavigation, 'addAdminMenu']);
		add_action('rest_api_init', [$this->plugin->getRestApi(), 'registerRoutes']);
		add_action('enqueue_block_assets', [$this->editor, 'registerPlayground'], 10);
		add_action('enqueue_block_assets', [$this->editor, 'registerTailwind'], 11);

		add_action('enqueue_block_editor_assets', [$this->editor, 'registerEditor'], 12);
		add_action('init', [$this->editor, 'initBlocks']);
		add_filter('block_categories_all', [$this->editor, 'registerBlockCategory']);
		add_action('wp_enqueue_scripts', [$this->frontend, 'registerAssetsFrontend']);
		add_action('admin_init', [$this->frontend, 'registerAssetsBackend']);
		add_action('admin_init', [$this->editor, 'registerLibrarySettings']);

		add_action('admin_enqueue_scripts', [$this->settingsNavigation, 'registerAssets']);
		add_action('enqueue_block_assets', [$this->editor, 'registerSwiperCdn'], 12);
		add_filter('render_block', [$this->render, 'carousel_dynamic'], 13, 2);
		add_action('init', [PostTypes::class, 'register_bites']);
		add_action('after_setup_theme', [EditorSettings::class, 'add_theme_settings'], 20);
		add_action('wp_head', [PostTypes::class, 'bites_view']);

		// add_filter('allowed_block_types_all', [PostTypes::class, 'restrict_block_to_post_type'], 10, 2);
		add_filter('block_editor_settings_all', [$this->editor, 'add_global_styles']);
	}



	/**
	 * Remove Hooks
	 */
	public function removeHooks()
	{
		remove_action('plugins_loaded', [$this->plugin, 'pluginLoaded']);
		remove_action('rest_api_init', [$this->plugin->getRestApi(), 'registerRoutes']);
		remove_action('admin_enqueue_scripts', [$this->editor, 'registerAssets']);
		remove_action('wp_enqueue_scripts', [$this->frontend, 'registerAssetsFrontend']);
		remove_action('admin_init', [$this->frontend, 'registerAssetsBackend']);
		add_action('admin_init', [$this->editor, 'registerLibrarySettings']);
		remove_action('enqueue_block_assets', [$this->editor, 'registerTailwindCdn'], 10);
		remove_action('enqueue_block_assets', [$this->editor, 'registerSwiperCdn'], 12);
	}
}
