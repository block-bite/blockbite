<?php

namespace Blockbite\Blockbite;

// use settings
use Blockbite\Blockbite\SettingsNavigation;

class Settings
{

    // construct
    public function __construct()
    {
    }

    /**
     * Name of the option settings are saved in
     *
     * @since 0.0.1
     *
     * @var string
     */
    protected $optionName = 'blockbite-settings';


    /**
     * Defaults
     *
     * @since 0.0.1
     *
     * @param array $defaults
     */
    protected $defaults = [
        'apiKey' => '',
    ];

    /**
     * Save settings
     *
     * @since 0.0.1
     *
     * @param array $data
     * @return void
     */
    public function save(array $data)
    {
        $data = array_merge($this->defaults, $data);
        /**
         * Filter settings before saving
         *
         * @since 0.0.1
         * @param array $data Data to save
         */
        $data = apply_filters('blockbite_save_settings', $data);
        update_option($this->optionName, $data);
    }
    /**
     * Get all settings
     *
     * @since 0.0.1
     *
     * @return array
     */
    public function getAll()
    {
        $values =  get_option($this->optionName, $this->defaults);
        /**
         * Filter settings before returning
         *
         * @since 0.0.1
         * @param array $values Settings
         */
        return apply_filters('blockbite_save_settings', $values);
    }

    /**
     * Get defaults for settings
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }
}
