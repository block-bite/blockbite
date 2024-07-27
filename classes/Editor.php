<?php

namespace Blockbite\Blockbite;

use phpDocumentor\Reflection\DocBlock\Tags\Var_;

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
            'slider',
            'icon',
            'heading',
            'repeater',
            'repeater-nav',
            'repeater-content',
            'canvas',
            'carousel',
            'carousel-slide'
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

    public function registerAssets()
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
            'bite',
            [
                'tailwind' => null,
                'apiUrl'   => rest_url('blockbite/v1'),
                'api' => 'blockbite/v1',
                'blocks' => $this->blocks,
                'blocknamespaces' => $this->blocknamespaces
            ]
        );
    }

    public function registerTailwindConfig()
    {
        // Use asset file if it exists
        if (file_exists(BLOCKBITE_PLUGIN_DIR . 'build/tailwind-config.asset.php')) {
            $asset_file   = include BLOCKBITE_PLUGIN_DIR . 'build/tailwind-config.asset.php';
            $dependencies = $asset_file['dependencies'];
            $version      = $asset_file['version'];
        }
        // register config script
        wp_register_script(
            'blockbite-tailwind-config',
            plugins_url('build/tailwind-config.js', BLOCKBITE_MAIN_FILE),
            $dependencies,
            $version,
        );
        if (is_admin()) {
            wp_enqueue_script('blockbite-tailwind-config');
        }
    }

    public function registerTailwindCdn()
    {
        // https://cdn.tailwindcss.com cdn script
        wp_register_script(
            'blockbite-tailwind-cdn',
            'https://cdn.tailwindcss.com',
            [],
        );
        if (is_admin()) {
            wp_enqueue_script('blockbite-tailwind-cdn');
        }
    }

    public function registerSwiperCdn()
    {
        wp_register_script(
            'swiper-editor',
            'https://cdn.jsdelivr.net/npm/swiper@11.1.4/swiper-element-bundle.min.js',
            [],
            '11.1.4',
        );
        if (is_admin()) {
            wp_enqueue_script('swiper-editor');
        }
    }
}
