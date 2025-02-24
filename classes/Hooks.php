<?php

namespace Blockbite\Blockbite;

use Blockbite\Blockbite\Register;
use Blockbite\Blockbite\Rest\Api;
use Blockbite\Blockbite\Controllers\Database as DbController;
use Blockbite\Blockbite\Controllers\BlockRender as BlockRender;
use Blockbite\Blockbite\Controllers\EditorSettings as EditorSettings;

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
		add_action('enqueue_block_editor_assets', [$this->editor, 'registerEditor'], 12);
		add_action('init', [$this->editor, 'initBlocks']);
		add_filter('block_categories_all', [$this->editor, 'registerBlockCategory']);
		add_action('admin_init', [$this->editor, 'registerLibrarySettings']);
		add_action('admin_enqueue_scripts', [$this->settingsNavigation, 'registerAssets']);
		add_action('enqueue_block_assets', [$this->frontend, 'frontendHeadingEditor'], 9);
		add_action('enqueue_block_assets', [$this->editor, 'registerCssParser'], 10);
		add_action('enqueue_block_assets', [$this->editor, 'registerTailwind'], 11);
		add_action('enqueue_block_assets', [$this->editor, 'registerSwiperCdn'], 12);
		add_action('enqueue_block_assets', [$this->editor, 'registerGsapCdn'], 12);
		add_action('enqueue_block_assets', [$this->editor, 'registerLottieCdn'], 12);
		add_action('admin_init', [$this->frontend, 'registerAssetsBackend']);
		add_action('enqueue_block_assets', [$this->frontend, 'registerAssetsFrontend'], 15);
		add_action('enqueue_block_assets', [$this->frontend, 'registerParsedCssFrontend'], 16);
		add_action('after_setup_theme', [EditorSettings::class, 'add_theme_settings'], 20);
		add_action('wp_enqueue_scripts', [$this->frontend, 'frontendCodeEditorStyles'], 22);
		add_filter('upload_mimes', [$this->editor, 'blockbite_mime_types'], 18);
		add_filter('block_editor_settings_all', [$this->editor, 'add_global_styles']);

		add_action('wp_enqueue_scripts', [$this->frontend, 'frontendHeadingSite'], 23);


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
		remove_action('rest_api_init', [$this->plugin->getRestApi(), 'registerRoutes']);
		remove_action('admin_enqueue_scripts', [$this->editor, 'registerAssets']);
		remove_action('wp_enqueue_scripts', [$this->frontend, 'registerAssetsFrontend']);
		remove_action('admin_init', [$this->frontend, 'registerAssetsBackend']);
		remove_action('admin_init', [$this->editor, 'registerLibrarySettings']);
		remove_action('enqueue_block_assets', [$this->editor, 'registerTailwindCdn'], 10);
		remove_action('enqueue_block_assets', [$this->editor, 'registerSwiperCdn'], 12);
		remove_action('enqueue_block_assets', [$this->editor, 'registerGsapCdn'], 12);
		remove_action('enqueue_block_assets', [$this->editor, 'registerLottieCdn'], 12);
		remove_filter('upload_mimes', [$this->editor, 'blockbite_mime_types'], 18);
	}
}
