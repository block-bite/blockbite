<?php

namespace Blockbite\Blockbite;

use Blockbite\Blockbite\Rest\Api;
use Blockbite\Blockbite\Controllers\Database;
use Blockbite\Blockbite\Controllers\Settings as SettingsController;

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
     * Tailwind instance
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





    public function __construct(Editor $editor, Frontend $frontend,  Settings $settings)
    {
        $this->editor = $editor;
        $this->frontend = $frontend;
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
        if (!Database::checkTableExists('blockbite')) {
            // create table
            Database::createTable();
            // create style file
            $this->createStyleFile();
            // add default
            SettingsController::sync_blockbite_items([
                'handle' => 'preset',
                'version' => BLOCKBITE_ITEMS_VERSION,
                'platform' => 'blockbite'
            ]);
        } else {
            $this->hooks = new Hooks($this);
            $this->hooks->addHooks();
        }
    }

    public function createStyleFile()
    {
        // Define the path to your CSS file
        $css_file_path = BLOCKBITE_PLUGIN_DIR . 'public/style.css';

        // Define the directory path
        $directory_path = dirname($css_file_path);


        // Check if the directory doesn't exist
        if (!is_dir($directory_path)) {
            // Create the directory, including any parent directories
            if (!mkdir($directory_path, 0755, true) && !is_dir($directory_path)) {
                // Handle error, if needed
                error_log("Failed to create directory: " . $directory_path);
                return;
            }
        }

        // Check if the file doesn't exist to avoid overwriting an existing file
        if (!file_exists($css_file_path)) {
            // Write to the CSS file
            $file = fopen($css_file_path, 'w');
            if ($file) {
                fwrite($file, '');
                fclose($file);
            } else {
                // Handle error, if needed
                error_log("Failed to create CSS file: " . $css_file_path);
            }
        }
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
