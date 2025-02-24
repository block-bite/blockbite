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
            'bites-wrap',
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

    public function registerEditor()
    {


        $dependencies = [];
        $version      = BLOCKBITE_PLUGIN_VERSION;


        // Use asset file if it exists
        if (file_exists(BLOCKBITE_PLUGIN_DIR . 'build/blockbite-editor.asset.php')) {
            $asset_file   = include BLOCKBITE_PLUGIN_DIR . 'build/blockbite-editor.asset.php';
            $dependencies = $asset_file['dependencies'];
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
        // only load in backend
        if (is_admin()) {
            wp_enqueue_script('blockbite-editor');
            wp_enqueue_style('blockbite-editor-style');
        }
        // global  api bite
        wp_localize_script(
            'blockbite-editor',
            'blockbite',
            [
                'apiUrl'   => rest_url('blockbite/v1'),
                'api' => 'blockbite/v1',
                'createTailwindcss' => null,
                'data' => [
                    'postType' => get_post_type(),
                    'id' => get_the_ID(),
                ],
                'settings' => [
                    'gsap' => get_option('blockbite_load_gsap', true),
                    'swiper' => get_option('blockbite_load_swiper', true),
                    'lottie' => get_option('blockbite_load_lottie', true),
                    'tw_base' => get_option('blockbite_load_tw_base', false),
                ]
            ]
        );
    }



    public function registerCssParser()
    {

        $dependencies_css_parser = [];
        $version_css_parser = BLOCKBITE_PLUGIN_VERSION;

        // Use asset file if it exists
        if (file_exists(BLOCKBITE_PLUGIN_DIR . 'build/blockbite-css-parser.asset.php')) {
            $asset_file_css_parser   = include BLOCKBITE_PLUGIN_DIR . 'build/blockbite-css-parser.asset.php';
            $dependencies_css_parser = $asset_file_css_parser['dependencies'];
            $version_css_parser      = $asset_file_css_parser['version'];
        }

        wp_register_script(
            'blockbite-css-parser',
            plugins_url('build/blockbite-css-parser.js', BLOCKBITE_MAIN_FILE),
            $dependencies_css_parser,
            $version_css_parser,
        );



        if (is_admin()) {
            // enqueue the CSS parser script
            wp_enqueue_script('blockbite-css-parser');
        }
    }

    public function registerTailwind()
    {

        // Initialize dependencies array for blockbite-tailwind
        $dependencies_tailwind = ['blockbite-css-parser']; // Add blockbite-css_parser as a dependency by default
        $version_tailwind = BLOCKBITE_PLUGIN_VERSION;

        // Use asset file if it exists
        if (file_exists(BLOCKBITE_PLUGIN_DIR . 'build/blockbite-tailwind.asset.php')) {
            $asset_file_tailwind   = include BLOCKBITE_PLUGIN_DIR . 'build/blockbite-tailwind.asset.php';
            $dependencies_tailwind = $asset_file_tailwind['dependencies'];
            $version_tailwind      = $asset_file_tailwind['version'];
        }


        wp_register_script(
            'blockbite-tailwind',
            plugins_url('build/blockbite-tailwind.js', BLOCKBITE_MAIN_FILE),
            $dependencies_tailwind,
            $version_tailwind,
        );

        if (is_admin()) {
            wp_enqueue_script('blockbite-tailwind');
        }
    }




    public function registerSwiperCdn()
    {
        $load_swiper = get_option('blockbite_load_swiper', true);

        if ($load_swiper) {

            wp_register_script(
                'swiper-editor',
                'https://cdn.jsdelivr.net/npm/swiper@11.1.4/swiper-element-bundle.min.js',
                [],
                '11.1.4',
            );

            wp_enqueue_script('swiper-editor');
        }
    }

    public function registerGsapCdn()
    {

        $load_gsap = get_option('blockbite_load_gsap', true);
        if ($load_gsap) {
            wp_register_script(
                'gsap-editor',
                'https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/gsap.min.js',
                [],
                '3.12.7',
            );
            wp_enqueue_script('gsap-editor');
        }
    }


    public function registerLottieCdn()
    {
        $load_lottie = get_option('blockbite_load_lottie', true);
        if ($load_lottie) {


            wp_register_script(
                'lottie-player',
                'https://unpkg.com/@dotlottie/player-component@2.7.10/dist/dotlottie-player.js',
                [],
                '2.7.12',

            );
            wp_enqueue_script('lottie-player');
        }
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
        $mimes['lottie'] = 'application/json';
        return $mimes;
    }
}
