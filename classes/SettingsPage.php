<?php

namespace Blockbite\Blockbite;

class SettingsPage
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

        wp_register_script(
            SettingsPage::SCREEN,
            plugins_url('build/blockbite-settings.js', BLOCKBITE_MAIN_FILE),
            $dependencies,
            $version,
        );
    }
    /**
     * Adds the settings page to the Settings menu.
     *
     * @since 0.0.1
     *
     * @return string
     */
    public function addPage()
    {

        // Add the page
        $suffix = add_options_page(
            __('blockbite', 'blockbite-plugin'),
            __('blockbite', 'blockbite-plugin'),
            'manage_options',
            self::SCREEN,
            [
                $this,
                'renderPage',
            ]
        );

        // This adds a link in the plugins list table
        add_action(
            'plugin_action_links_' . plugin_basename(BLOCKBITE_MAIN_FILE),
            [
                $this,
                'addLinks',
            ]
        );

        return $suffix;
    }

    /**
     * Adds a link to the setting page to the plugin's entry in the plugins list table.
     *
     * @since 1.0.0
     *
     * @param array $links List of plugin action links HTML.
     * @return array Modified list of plugin action links HTML.
     */
    public function addLinks($links)
    {
        // Add link as the first plugin action link.
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(add_query_arg('page', self::SCREEN, admin_url('options-general.php'))),
            esc_html__('Settings', 'blockbite')
        );
        array_unshift($links, $settings_link);


        return $links;
    }

    /**
     * Renders the settings page.
     *
     * @since 0.0.1
     */
    public  function renderPage()
    {
        wp_enqueue_script(self::SCREEN);



        $settings = $this
            ->plugin
            ->getSettings()
            ->getAll();

        
         
         wp_localize_script(
            'blockbite-settings',
            'wpApiSettings',
            array('root' => esc_url_raw(rest_url()), 'nonce' => wp_create_nonce('wp_rest'),  'apiUrl'   => rest_url('blockbite/v1'))
     );




?>
        <div class="blockbite">
            <h1>
                <?php esc_html_e('blockbite', 'blockbite'); ?>
            </h1>
            <div id="<?php echo esc_attr(self::SCREEN); ?>"></div>
        </div>
<?php
    }
}
