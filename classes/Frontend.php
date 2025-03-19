<?php

namespace Blockbite\Blockbite;

use Blockbite\Blockbite\Controllers\EditorSettings;
use Blockbite\Blockbite\Controllers\Database as DbController;

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

    function biteClassDynamicBlocks($parsed_block, $source)
    {
        static $dynamic_blocks = null;

        // Fetch dynamic blocks once
        if ($dynamic_blocks === null) {
            $dynamic_block_result = DbController::getRecordByHandle('dynamic_block_support');

            if (isset($dynamic_block_result->data)) {
                $dynamic_blocks = json_decode($dynamic_block_result->data, true);
            } else {
                $dynamic_blocks = [];
            }
        }


        // Apply only if the block is in our dynamic list
        if (is_array($dynamic_blocks) && in_array($parsed_block['blockName'], $dynamic_blocks, true)) {
            if (isset($parsed_block['attrs']['metadata']['biteClass'])) {
                $parsed_block['attrs']['className'] =
                    isset($parsed_block['attrs']['className'])
                    ? $parsed_block['attrs']['className'] . ' ' . esc_attr($parsed_block['attrs']['metadata']['biteClass'])
                    : esc_attr($parsed_block['attrs']['metadata']['biteClass']);
            }
        }

        return $parsed_block;
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

    /*
     if (is_singular('blockbites')) {
            $handle = 'bites-css';
        }
    */


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
            'blockbite-frontend-asset',
            plugins_url('build/blockbite-frontend.js', BLOCKBITE_MAIN_FILE),
            $dependencies,
            $version,
        );

        wp_enqueue_script('blockbite-frontend-asset');


        // register frontend style
        wp_register_style(
            'blockbite-frontend-asset',
            $this->css_url,
            [],
            $version
        );

        // add to frontend
        wp_enqueue_style('blockbite-frontend-asset');
    }

    public static function frontendCodeEditorStyles()
    {
        $frontendAssets = DbController::getRecordsByHandles([
            'blockbite-editor-css',
            'blockbite-editor-js',
        ]);

        foreach ($frontendAssets as $asset) {
            if (isset($asset->handle)) {
                if ($asset->handle === 'blockbite-editor-css') {
                    wp_register_style('blockbite-editor-css', false);
                    wp_enqueue_style('blockbite-editor-css');
                    wp_add_inline_style('blockbite-editor-css', $asset->content);
                } elseif ($asset->handle === 'blockbite-editor-js') {
                    wp_register_script('blockbite-editor-js', '', [], false, true);
                    wp_enqueue_script('blockbite-editor-js');
                    wp_add_inline_script('blockbite-editor-js', 'document.addEventListener("DOMContentLoaded", function () {' . $asset->content . '});');
                }
            }
        }
    }


    public static function registerBodyClass($classes)
    {
        $strategy = get_option('blockbite_tw_strategy', 'b_');
        $classes[] = $strategy;
        return $classes;
    }


    public static function getFrontendCss()
    {
        // Define paths for the CSS file
        $file_name = get_option('blockbite_css_name', 'style') . '.css';
        $style_url = BLOCKBITE_PLUGIN_URL . 'public/' . $file_name;
        $style_path = BLOCKBITE_PLUGIN_DIR . 'public/' . $file_name;

        // Validate the file exists and is not empty
        if (file_exists($style_path) && filesize($style_path) > 0) {
            $cache_version = filemtime($style_path); // Cache-busting with file's last modified time
        } else {
            $cache_version = time(); // Fallback cache version
            error_log('Warning: Blockbite style.css is missing or empty.');
        }
        return [
            'url' => $style_url,
            'version' => $cache_version,
        ];
    }


    public function registerParsedCssFrontend()
    {
        $frontendCss = $this->getFrontendCss();
        // Register the frontend CSS
        wp_register_style(
            'blockbite-parsed-frontend',
            $frontendCss['url'],
            [],
            $frontendCss['version']
        );
        // Enqueue the frontend CSS
        wp_enqueue_style('blockbite-parsed-frontend');
    }




    public function registerLibraries()
    {

        $load_gsap = get_option('blockbite_load_gsap', false);
        $load_lottie = get_option('blockbite_load_lottie', false);
        $load_swiper = get_option('blockbite_load_swiper', true);
        $load_plyr = get_option('blockbite_load_plyr', false);


        if ($load_gsap) {
            wp_register_script(
                'blockbite-gsap',
                'https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/gsap.min.js',
                [],
                '3.12.7',
            );
            wp_enqueue_script('gsap');
        }
        if ($load_lottie) {
            wp_register_script(
                'blockbite-lottie',
                'https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js',
                [],
                '3.12.7',
            );
            wp_enqueue_script('blockbite-lottie');
        }
        if ($load_swiper) {

            wp_register_script(
                'blockbite-swiper',
                'https://cdn.jsdelivr.net/npm/swiper@11.1.4/swiper-element-bundle.min.js',
                [],
                '11.1.4',
            );

            wp_enqueue_script('blockbite-swiper');
        }
        if ($load_plyr) {
            wp_register_script(
                'blockbite-plyr',
                'https://cdnjs.cloudflare.com/ajax/libs/plyr/3.7.8/plyr.min.js',
                [],
                '3.7.8',
            );
            wp_enqueue_script('blockbite-plyr');
        }
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
