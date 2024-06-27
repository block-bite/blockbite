<?php

namespace Blockbite\Blockbite;

use Blockbite\Blockbite\Rest\Api;
use Blockbite\Blockbite\Controllers\Database;

class Plugin
{


    /**
     * API instance
     *
     * @since 0.0.1
     *
     * @var Api
     */
    protected $api;

    /**
     * Hooks
     *
     * @since 0.0.1
     *
     * @var Hooks
     *
     */
    protected $hooks;

    /**
     * Editor instance
     *
     * @since 0.0.1
     *
     * @var Editor
     */
    protected $editor;

    /**
     * Frontend instance
     *
     * @since 0.0.1
     *
     * @var Frontend
     */

    protected $frontend;

    /**
     * Library instance
     *
     * @since 0.0.1
     *
     * @var Library
     */
    protected $library;


    /**
     * Library instance
     *
     * @since 0.0.1
     *
     * @var Tailwind
     */
    protected $tailwind;

    /**
     * Settings instance
     *
     * @since 0.0.1
     *
     * @var Settings
     */
    protected $settings;





    public function __construct(Editor $editor, Frontend $frontend, Library $library,  Settings $settings)
    {
        $this->editor = $editor;
        $this->frontend = $frontend;
        $this->library = $library;
        $this->settings = $settings;
    }

    /**
     * Initialize the plugin
     *
     * @since 0.0.1
     *
     * @uses "ACTION_PREFIX_init" action
     *
     * @return void
     */
    public function init()
    {

        add_theme_support('editor-styles');

        if (!isset($this->api)) {
            $this->api = new Api($this);
        }
        $this->hooks = new Hooks($this);
        $this->hooks->addHooks();

        if (!Database::checkTableExists()) {
            Database::createTable();
        }
    }

    public function createTable()
    {
        Database::createTable();
    }


    public function adminNotice()
    {
        if (get_transient('blockbite_db_creation_failed')) {
            echo '<div class="notice notice-error is-dismissible">
                <p>' . __('Blockbite Plugin: Failed to create database tables.', 'blockbite') . '</p>
            </div>';

            // Delete the transient so the message only shows once
            delete_transient('blockbite_db_creation_failed');
        }
    }

    /**
     * When the plugin is loaded:
     *  - Load the plugin's text domain.
     *
     * @uses "plugins_loaded" action
     *
     */
    public function pluginLoaded()
    {
        load_plugin_textdomain('blockbite');
    }


    /**
     * Get Settings
     *
     * @since 0.0.1
     *
     * @return Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }


    /**
     * Get API
     *
     * @since 0.0.1
     *
     * @return Api
     */
    public function getRestApi()
    {
        return $this->api;
    }
}
