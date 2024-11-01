<?php

namespace Blockbite\Blockbite;


use Blockbite\Blockbite\Controllers\EditorSettings;
use Blockbite\Blockbite\Controllers\Database as DbController;

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
            'canvas',
            'carousel',
            'carousel-slide',
            'bites-wrap',
            'ai-generated'
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
                'tailwindParser' => null,
                'data' => [
                    'postType' => get_post_type(),
                    'id' => get_the_ID(),
                ]
            ]
        );
    }

    public function registerTailwind()
    {
        // Use asset file if it exists
        if (file_exists(BLOCKBITE_PLUGIN_DIR . 'build/blockbite-tailwind.asset.php')) {
            $asset_file_tailwind   = include BLOCKBITE_PLUGIN_DIR . 'build/blockbite-tailwind.asset.php';
            $dependencies_tailwind = $asset_file_tailwind['dependencies'];
            $version_tailwind      = $asset_file_tailwind['version'];
        }
        // register editor script
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

        if ($load_swiper && is_admin()) {
            wp_register_script(
                'swiper-editor',
                'https://cdn.jsdelivr.net/npm/swiper@11.1.4/swiper-element-bundle.min.js',
                [],
                '11.1.4',
            );
            wp_enqueue_script('swiper-editor');
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
            ]
        );
    }


    public function blockbite_editor_css()
    {
        $styles = EditorSettings::get_styles($request = null);

        // Enqueue a dummy stylesheet or one that's already enqueued for the block editor.
        wp_enqueue_style('blockbite-editor', false); // Use a registered style handle if available.

        if (isset($styles['css']) && is_admin()) {
            // Append inline styles to the enqueued style.
            if (isset($styles['css']) && is_admin()) {
                //   echo '<style id="blockbite-editor-css-ssr">' . $styles['css'] . '</style>';
            }
        }
    }

    function add_global_styles($editorSettings) {
        // Fetch CSS string from the database
        $styleRecord = DBController::getRecordByHandle('global-user-styles');
    
        if ($styleRecord && !empty($styleRecord->css)) {
            $editorSettings['styles'][] = array(
                'css' => $styleRecord->css,
                '__unstableType' => 'theme',
                'source' => 'blockbite-global',
            );
        }
    
        return $editorSettings;
    }
}
