<?php

namespace Blockbite\Blockbite;


use Blockbite\Blockbite\Controllers\EditorSettings;
use Blockbite\Blockbite\Controllers\Database as DbController;
use Blockbite\Blockbite\Frontend as FrontendController;

class Editor
{

    /**
     * Name of the option settings are saved in
     *
     * @since 0.0.1
     *
     * @var string
     */
    protected $name = '';
    protected $blocks = [];
    protected $blocknamespaces = [];


    public function __construct()
    {
        // Ensure is_plugin_active function is available
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $this->blocks = [
            'main',
            'section',
            'group',
            'visual',
            'advanced-button',
            'counter',
            'icon',
            'button-content',
            'carousel',
            'carousel-slide',
            'carousel-header',
            'carousel-footer',
            'interaction',
            'dynamic-content',
            'dynamic-display',
            'dynamic-design'
        ];

        $this->blocknamespaces;
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

    public function initBlocks()
    {
        foreach ((array) $this->blocks as $block) {
            register_block_type(BLOCKBITE_PLUGIN_DIR . 'build/blocks/' . $block);
            array_push($this->blocknamespaces, 'blockbite/' . $block);
        }
    }


    public function registerBlockCategory($categories)
    {
        $custom_block = array(
            'slug'  => 'blockbite',
            'title' => __('blockbite', 'blockbite'),
        );
        // order
        $categories_sorted = array();
        $categories_sorted[0] = $custom_block;
        foreach ($categories as $category) {
            $categories_sorted[] = $category;
        }
        return $categories_sorted;
    }

    public function registerBB()
    {

        $dependencies = [];
        $version      = BLOCKBITE_PLUGIN_VERSION;
        // Use asset file if it exists
        if (file_exists(BLOCKBITE_PLUGIN_DIR . 'build/blockbite-bb.asset.php')) {
            $asset_file   = include BLOCKBITE_PLUGIN_DIR . 'build/blockbite-bb.asset.php';
            $dependencies =  array_merge($dependencies, $asset_file['dependencies']);
            $version      = $asset_file['version'];
        }

        // register editor script
        wp_register_script(
            'blockbite-bb',
            plugins_url('build/blockbite-bb.js', BLOCKBITE_MAIN_FILE),
            $dependencies,
            $version,
        );

        wp_enqueue_script('blockbite-bb');
        // global BB dependency object
        wp_localize_script(
            'blockbite-bb',
            'bb',
            [
                'apiUrl'   => rest_url('blockbite/v1'),
                'api' => 'blockbite/v1',
                'data' => [
                    'postType' => get_post_type(),
                    'id' => get_the_ID(),
                ],
                'settings' => [
                    'gsap' => get_option('blockbite_load_gsap', true),
                    'swiper' => get_option('blockbite_load_swiper', true),
                    'lottie' => get_option('blockbite_load_lottie', true),
                    'tw_base' => get_option('blockbite_tw_base', false),
                    'tw_strategy' => get_option('blockbite_tw_strategy', 'b_'),
                ],
                'css' => '',
                'core' => '',
                'codex' => '',
            ]
        );
    }


    public function registerCore()
    {
        $dependencies = ['blockbite-bb'];
        $version      = BLOCKBITE_PLUGIN_VERSION;


        // Use asset file if it exists
        if (file_exists(BLOCKBITE_PLUGIN_DIR . 'build/blockbite-core.asset.php')) {
            $asset_file   = include BLOCKBITE_PLUGIN_DIR . 'build/blockbite-core.asset.php';
            $dependencies =  array_merge($dependencies, $asset_file['dependencies']);
            $version      = $asset_file['version'];
        }

        // register core script
        wp_register_script(
            'blockbite-core',
            plugins_url('build/blockbite-core.js', BLOCKBITE_MAIN_FILE),
            $dependencies,
            $version,
        );

        wp_enqueue_script('blockbite-core');
    }



    public function registerCssParser()
    {

        $dependencies = ['blockbite-bb', 'blockbite-core'];
        $version = BLOCKBITE_PLUGIN_VERSION;

        // Use asset file if it exists
        if (file_exists(BLOCKBITE_PLUGIN_DIR . 'build/blockbite-css-parser.asset.php')) {
            $asset_file   = include BLOCKBITE_PLUGIN_DIR . 'build/blockbite-css-parser.asset.php';
            $dependencies =  array_merge($dependencies, $asset_file['dependencies']);
            $version      = $asset_file['version'];
        }

        wp_register_script(
            'blockbite-css-parser',
            plugins_url('build/blockbite-css-parser.js', BLOCKBITE_MAIN_FILE),
            $dependencies,
            $version,
        );

        wp_enqueue_script('blockbite-css-parser');
    }



    public function registerReady()
    {
        $dependencies = ['blockbite-bb', 'blockbite-core', 'blockbite-css-parser'];
        $version      = BLOCKBITE_PLUGIN_VERSION;


        // Use asset file if it exists
        if (file_exists(BLOCKBITE_PLUGIN_DIR . 'build/blockbite-core.asset.php')) {
            $asset_file   = include BLOCKBITE_PLUGIN_DIR . 'build/blockbite-core.asset.php';
            $dependencies =  array_merge($dependencies, $asset_file['dependencies']);
            $version      = $asset_file['version'];
        }

        // register editr script
        wp_register_script(
            'blockbite-ready',
            plugins_url('build/blockbite-ready.js', BLOCKBITE_MAIN_FILE),
            $dependencies,
            $version,
        );

        wp_enqueue_script('blockbite-ready');
    }





    public function registerAce()
    {


        $dependencies = ['blockbite-bb'];
        $version      = BLOCKBITE_PLUGIN_VERSION;

        // Use asset file if it exists
        if (file_exists(BLOCKBITE_PLUGIN_DIR . 'build/blockbite-ace.asset.php')) {
            $asset_file   = include BLOCKBITE_PLUGIN_DIR . 'build/blockbite-ace.asset.php';
            $dependencies =  array_merge($dependencies, $asset_file['dependencies']);
            $version      = $asset_file['version'];
        }
        // register editor script
        wp_register_script(
            'blockbite-ace',
            plugins_url('build/blockbite-ace.js', BLOCKBITE_MAIN_FILE),
            $dependencies,
            $version,
        );
        wp_enqueue_script('blockbite-ace');
    }


    public function registerEditor()
    {


        $dependencies = ['blockbite-bb', 'blockbite-css-parser', 'blockbite-ace', 'blockbite-core', 'blockbite-ready'];
        $version      = BLOCKBITE_PLUGIN_VERSION;

        if (is_plugin_active('blockbite-pro/blockbite-pro.php')) {
            $dependencies[] = "blockbite-pro";
        }

        // Use asset file if it exists
        if (file_exists(BLOCKBITE_PLUGIN_DIR . 'build/blockbite-editor.asset.php')) {
            $asset_file   = include BLOCKBITE_PLUGIN_DIR . 'build/blockbite-editor.asset.php';
            $dependencies =  array_merge($dependencies, $asset_file['dependencies']);
            $version      = $asset_file['version'];
        }


        // register editor script
        wp_register_script(
            'blockbite-editor',
            plugins_url('build/blockbite-editor.js', BLOCKBITE_MAIN_FILE),
            $dependencies,
            $version,
        );
        // register editor style
        wp_register_style(
            'blockbite-editor-style',
            plugins_url('build/blockbite-editor.css', BLOCKBITE_MAIN_FILE),
            [],
            $version
        );
        wp_enqueue_script('blockbite-editor');
        wp_enqueue_style('blockbite-editor-style');
    }



    function registerLibrarySettings()
    {
        register_setting(
            'blockbite_settings',
            'blockbite_load_swiper',
            [
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => true,
                'show_in_rest'      => true,
            ],
            'blockbite_load_gsap',
            [
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => false,
                'show_in_rest'      => true,
            ],
            'blockbite_load_lottie',
            [
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => false,
                'show_in_rest'      => true,
            ]
        );
    }



    // fetch global editor css and add to localize script
    function add_global_styles($editorSettings)
    {
        // Fetch CSS string from the database
        $styleRecord = DBController::getRecordByHandle('blockbite-editor-css');

        if ($styleRecord && !empty($styleRecord->content)) {
            $editorSettings['styles'][] = array(
                'css' => $styleRecord->content,
                '__unstableType' => 'theme',
                'source' => 'blockbite-global',
            );
        }

        return $editorSettings;
    }

    /* Additions */
    function blockbite_mime_types($mimes)
    {
        $mimes['svg'] = 'image/svg+xml';
        $mimes['json'] = 'text/plain';
        return $mimes;
    }
}
