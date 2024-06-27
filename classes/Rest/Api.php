<?php

namespace Blockbite\Blockbite\Rest;

use Blockbite\Blockbite\Plugin;
use Blockbite\Blockbite\Rest\Routes\BlockHelpers as BlockHelperRoute;
use Blockbite\Blockbite\Rest\Routes\LibraryComponents as LibraryComponentsRoute;
use Blockbite\Blockbite\Rest\Routes\EditorStyles as EditorStylesRoute;
use Blockbite\Blockbite\Rest\Routes\BlockSupport as BlockSupportRoute;
use Blockbite\Blockbite\Rest\Routes\Items as ItemsRoute;
use Blockbite\Blockbite\Rest\Routes\Settings as SettingsRoute;

/**
 * Register all routes for REST API
 */
class Api
{

    /**
     * Plugin instance
     *
     * @since 1.0.0
     *
     * @var Plugin
     */
    protected $plugin;

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Register all routes
     *
     * @since 1.0.0
     *
     * @uses "rest_api_init" action
     * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
     * wp-json/blockbite/v1/
     * @return void
     */
    public function registerRoutes()
    {
        $routes = [
            new BlockHelperRoute($this->plugin),
            new LibraryComponentsRoute($this->plugin),
            new EditorStylesRoute($this->plugin),
            new BlockSupportRoute($this->plugin),
            new ItemsRoute($this->plugin),
            new SettingsRoute($this->plugin),
        ];

        foreach ($routes as $route) {
            $route->Register();
        }
    }
}
