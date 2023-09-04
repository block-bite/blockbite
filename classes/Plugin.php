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





    public function __construct(Editor $editor, Frontend $frontend, Library $library, Tailwind $tailwind)
    {
        $this->editor = $editor;
        $this->frontend = $frontend;
        $this->library = $library;
        $this->tailwind = $tailwind;
        
      
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
