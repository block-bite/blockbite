<?php

namespace Blockbite\Blockbite;

class SettingsNavigation
{

    const SCREEN = 'blockbite-settings';


    /**
     * Main plugin class
     *
     * @since 0.0.1
     *
     * @var Plugin
     *
     */
    protected $plugin;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Register assets
     *
     * @since 0.0.1
     *
     * @uses "admin_enqueue_scripts" action
     */
    public function registerAssets()
    {
        $dependencies = [];
        $version      = BLOCKBITE_PLUGIN_VERSION;

        // Use asset file if it exists
        if (file_exists(BLOCKBITE_PLUGIN_DIR . 'build/blockbite-settings.asset.php')) {
            $asset_file   = include BLOCKBITE_PLUGIN_DIR . 'build/blockbite-settings.asset.php';
            $dependencies = $asset_file['dependencies'];
            $version      = $asset_file['version'];
        }

        // reuse editor style
        wp_register_style(
            SettingsNavigation::SCREEN,
            plugins_url('build/blockbite-editor.css', BLOCKBITE_MAIN_FILE),
            [],
            $version
        );

        wp_register_script(
            SettingsNavigation::SCREEN,
            plugins_url('build/blockbite-settings.js', BLOCKBITE_MAIN_FILE),
            $dependencies,
            $version,
        );

        wp_enqueue_style('blockbite-editor-style');
        wp_enqueue_style('wp-components'); // Loads Gutenberg components styles
        wp_enqueue_style('wp-edit-post');  // Loads block editor styles
    }
    /**
     * Adds the settings page to the Settings menu.
     *
     * @since 0.0.1
     *
     * @return string
     */
    public function addAdminMenu()
    {
        // Add the main menu page
        add_menu_page(
            'Blockbite Design System',
            'Blockbite',
            'manage_options',
            self::SCREEN,
            [$this, 'renderSettings'],
            'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB3aWR0aD0iMjIwIiBoZWlnaHQ9IjIyMCIgdmlld0JveD0iMCAwIDIyMCAyMjAiPgogIDxkZWZzPgogICAgPHN0eWxlPgogICAgICAuY2xzLTEgewogICAgICAgIGZpbGw6ICNmZmY7CiAgICAgIH0KICAgIDwvc3R5bGU+CiAgPC9kZWZzPgogIDxwYXRoIGNsYXNzPSJjbHMtMSIgZD0iTTc5LjAzLDEwNS41MkgyNy42OXYxMTIuNDhoMTEyLjQ4di01MS4zNGgtNjEuMTR2LTYxLjE0Wk03OS4wMyw1NC4xNXY1MS4zN2g2MS4xNHY2MS4xNGg1MS4zNGwuODEtNjEuMTR2LTUxLjM3aC0xMTMuMjlaTTI3LjY5LDJ2NTIuMTVoNTEuMzR2LTI2LjA3TDI3LjY5LDJaIi8+Cjwvc3ZnPg==',
            50
        );
        // Add a submenu item for 'Settings' (replaces the default 'Blockbite' submenu)
        add_submenu_page(
            self::SCREEN, // parent_slug
            'Settings', // page_title
            'Settings', // menu_title
            'manage_options', // capability
            self::SCREEN, // menu_slug should match the main menu to replace the first item
            [$this, 'renderSettings'] // callback
        );
        // Add the submenu page under the main menu
        add_submenu_page(
            self::SCREEN, // parent_slug should match menu_slug from add_menu_page
            'Blockbites', // page_title
            'Blockbites', // menu_title
            'manage_options', // capability
            'edit.php?post_type=blockbites',
            null
        );
    }


    public  function renderSettings()
    {
        wp_enqueue_script(self::SCREEN);
        wp_enqueue_style(self::SCREEN);

        $settings = $this
            ->plugin
            ->getSettings()
            ->getAll();


        wp_localize_script(
            'blockbite-settings',
            'wpApiSettings',
            array(
                'root' => esc_url_raw(rest_url()),
                'nonce' => wp_create_nonce('wp_rest'),
                'apiUrl'   => rest_url('blockbite/v1'),
                'itemsVersion' => BLOCKBITE_ITEMS_VERSION,
            )
        );
?><div id="blockbite-settings"></div>
<?php
    }



    public function renderSubMenu()
    {
        echo '<h1>Blockbite Submenu</h1>';
    }
}
