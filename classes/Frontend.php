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

    public function biteClassDynamicBlocks($block_content, $block)
    {
        // Check for valid block content and biteClass attribute
        if (!$block_content || !isset($block['attrs']['biteClass'])) {
            return $block_content;
        }

        // Load block content into DOMDocument safely
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);  // Suppress warnings from malformed HTML
        $dom->loadHTML(mb_convert_encoding($block_content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors(); // Clear any libxml errors after loading

        // Get the first element in the block
        $xpath = new \DOMXPath($dom);
        $element = $xpath->query('//*')->item(0);  // The first element

        if ($element) {
            // Handle classes - append injected classes
            $this->appendClasses($element, $block['attrs']);

            // Inject data attributes for interaction
            $this->injectDataAttributes($element, $block['attrs']['biteMeta'] ?? []);
        }

        // Return the updated HTML content
        return $dom->saveHTML($element);
    }

    // Helper function to append classes
    private function appendClasses(&$element, $attrs)
    {
        $injected_class = esc_attr($attrs['biteClass']);

        // Append motion class if available
        if (isset($attrs['biteMotionClass'])) {
            $injected_class .= ' ' . esc_attr($attrs['biteMotionClass']);
        }

        // Append classes to the existing class attribute
        $existing_classes = $element->getAttribute('class');
        $element->setAttribute('class', trim($existing_classes . ' ' . $injected_class));
    }

    // Helper function to inject data attributes
    private function injectDataAttributes(&$element, $biteMeta)
    {
        // Inject 'data-b_action_type' if actionType is set
        if (isset($biteMeta['interaction']['actionType'])) {
            $element->setAttribute('data-b_action_type', esc_attr($biteMeta['interaction']['actionType']));
        }

        // Inject 'data-b_action_ref' if actionRef is set
        if (isset($biteMeta['interaction']['actionRef'])) {
            $element->setAttribute('data-b_action_ref', esc_attr($biteMeta['interaction']['actionRef']));
        }
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

        // public/css/style.css with [], filemtime(get_stylesheet_directory()
        // wp_enqueue_style('blockbite-style', BLOCKBITE_PLUGIN_URL . 'public/style.css', [], filemtime(get_stylesheet_directory() . '/public/style.css'));
        wp_enqueue_style('blockbite-style', BLOCKBITE_PLUGIN_URL . 'public/style.css', [], '1.0.0');


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
        $load_swiper = get_option('blockbite_load_swiper', true);

        if ($load_swiper) {
            wp_register_script(
                'swiper-frontend',
                'https://cdn.jsdelivr.net/npm/swiper@11.1.4/swiper-element-bundle.min.js',
                [],
                '11.1.4',
            );
            wp_enqueue_script('swiper-frontend');
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
