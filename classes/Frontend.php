<?php

namespace Blockbite\Blockbite;

use Blockbite\Blockbite\Controllers\EditorSettings;

class Frontend
{

    /**
     * Name of the option settings are saved in
     *
     * @since 0.0.1
     *
     * @var string
     */
    protected $name = '';
    protected $css_url = '';

    public function __construct()
    {
        $this->css_url = plugins_url('build/blockbite-frontend.css', BLOCKBITE_MAIN_FILE);
    }



    // add biteClass and biteMotion to all dynamic blocks
    public function biteClassDynamicBlocks($block_content, $block)
    {
        if (!$block_content || !isset($block['attrs']['biteClass'])) {
            return $block_content;
        }
        $injected_class =  $block['attrs']['biteClass'];

        if (isset($block['attrs']['biteMotionClass'])) {
            $injected_class .= ' ' . $block['attrs']['biteMotionClass'];
        }

        $interaction_type_data_attr = '';
        if (isset($block['attrs']['biteMeta']['interaction']['actionType'])) {
            $interaction_type_data_attr = 'data-b_action_type="' . $block['attrs']['biteMeta']['interaction']['actionType'] . '"';
        }

        $interaction_ref_data_attr = '';
        if (isset($block['attrs']['biteMeta']['interaction']['actionRef'])) {
            $interaction_ref_data_attr = 'data-b_action_ref="' . $block['attrs']['biteMeta']['interaction']['actionRef'] . '"';
        }

        return preg_replace(
            '/' . preg_quote('class="', '/') . '/',
            'class="' . esc_attr($injected_class) . '" ' . $interaction_type_data_attr . ' ' . $interaction_ref_data_attr . ' ',
            $block_content,
            1
        );
    }


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

    public function blockbite_css()
    {
        $styles = EditorSettings::get_styles($request = null);
        // $components_css = LibraryComponents::get_components_css($request = null);

        if (isset($styles['css'])) {
            echo '<style id="blockbite">' . $styles['css'] . '</style>';
        }
    }

    public function blockbite_css_body($classes)
    {
        $classes[] = 'bite';
        return $classes;
    }




    public function registerAssetsFrontend()
    {
        $dependencies = [];
        $version      = BLOCKBITE_PLUGIN_VERSION;

        // Use asset file if it exists
        if (file_exists(BLOCKBITE_PLUGIN_DIR . 'build/blockbite-frontend.asset.php')) {
            $asset_file   = include BLOCKBITE_PLUGIN_DIR . 'build/blockbite-frontend.asset.php';
            $dependencies = $asset_file['dependencies'];
            $version      = $asset_file['version'];
        }

        // register frontend script
        wp_register_script(
            'blockbite-frontend',
            plugins_url('build/blockbite-frontend.js', BLOCKBITE_MAIN_FILE),
            $dependencies,
            $version,
        );

        wp_enqueue_script('blockbite-frontend');


        // register frontend style
        wp_register_style(
            'blockbite-frontend-style',
            $this->css_url,
            [],
            $version
        );

        // add to frontend
        wp_enqueue_style('blockbite-frontend-style');


        // pas data to react plugin
        wp_localize_script(
            'blockbite-frontend',
            'blockbiteFrontend',
            [
                'apiUrl'   => rest_url('blockbite/v1'),
                'settings' => [],

            ]
        );

        // register swiper script
        wp_register_script(
            'swiper-frontend',
            'https://cdn.jsdelivr.net/npm/swiper@11.1.4/swiper-element-bundle.min.js',
            [],
            '11.1.4',
        );

        wp_enqueue_script('swiper-frontend');
    }

    public function registerAssetsBackend()
    {

        $dependencies = [];
        $version      = BLOCKBITE_PLUGIN_VERSION;

        // Use asset file if it exists
        if (file_exists(BLOCKBITE_PLUGIN_DIR . 'build/blockbite-frontend.asset.php')) {
            $asset_file   = include BLOCKBITE_PLUGIN_DIR . 'build/blockbite-frontend.asset.php';
            $dependencies = $asset_file['dependencies'];
            $version      = $asset_file['version'];
        }

        add_editor_style($this->css_url);

        // register frontend script
        wp_register_script(
            'blockbite-frontend',
            plugins_url('build/blockbite-frontend.js', BLOCKBITE_MAIN_FILE),
            $dependencies,
            $version,
        );

        wp_enqueue_script('blockbite-frontend');
    }
}
