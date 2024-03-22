<?php

namespace Blockbite\Blockbite;

use Blockbite\Blockbite\Rest\Api;

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





    public function __construct(Editor $editor, Frontend $frontend, Library $library, Tailwind $tailwind, Settings $settings)
    {
        $this->editor = $editor;
        $this->frontend = $frontend;
        $this->library = $library;
        $this->tailwind = $tailwind;
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

        // create table
		
		
        
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


    public function createTable(){

        global $wpdb;
        $table_name = $wpdb->prefix . 'blockbite';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT(11) NOT NULL AUTO_INCREMENT,
            handle VARCHAR(500) NOT NULL,
            category INT(11),
            title VARCHAR(500),
            css TEXT NOT NULL,
            tailwind TEXT NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
    
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
    }
    

    /**
     * Get Settings
     *
     * @since 0.0.1
     *
     * @return Settings
     */
    public function getSettings() {
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
