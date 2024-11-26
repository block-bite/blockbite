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
        // Check for valid block content and 'biteClass' attribute
        if (!$block_content || !isset($block['attrs']['biteClass'])) {
            return $block_content;
        }

        // Check if DOMDocument is available
        if (class_exists('DOMDocument')) {
            try {
                // Load block content into DOMDocument safely
                $dom = new \DOMDocument();
                libxml_use_internal_errors(true); // Suppress warnings from malformed HTML
                $dom->loadHTML(mb_convert_encoding($block_content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                libxml_clear_errors(); // Clear any libxml errors after loading

                // Get the first element in the block
                $xpath = new \DOMXPath($dom);
                $element = $xpath->query('//*')->item(0); // The first element

                if ($element) {
                    // Handle classes - append injected classes
                    $this->appendClasses($element, $block['attrs']);

                    // Inject data attributes for interaction
                    $this->injectDataAttributes($element, $block['attrs']['biteMeta'] ?? []);
                }

                // Return the updated HTML content
                return $dom->saveHTML($element);
            } catch (\Exception $e) {
                // Log the exception for debugging and fall back to manual processing
                error_log('DOMDocument processing failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Fallback: Manually process HTML string to append classes and attributes.
     */
    private function fallbackProcessHtml($html, $attrs)
    {
        // Parse the HTML string and locate the first tag
        if (preg_match('/^<(\w+)([^>]*)>/', $html, $matches)) {
            $tag = $matches[1];
            $attributes = $matches[2];

            // Add the biteClass attribute to the class list
            $class = $attrs['biteClass'] ?? '';
            if ($class) {
                if (preg_match('/class="([^"]*)"/', $attributes, $classMatch)) {
                    $attributes = str_replace(
                        $classMatch[0],
                        'class="' . $classMatch[1] . ' ' . htmlspecialchars($class) . '"',
                        $attributes
                    );
                } else {
                    $attributes .= ' class="' . htmlspecialchars($class) . '"';
                }
            }

            // Inject data attributes
            if (!empty($attrs['biteMeta'])) {
                foreach ($attrs['biteMeta'] as $key => $value) {
                    $attributes .= ' data-' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
                }
            }

            // Rebuild the opening tag
            $newOpeningTag = "<$tag$attributes>";

            // Replace the first opening tag in the HTML
            $html = preg_replace('/^<\w+[^>]*>/', $newOpeningTag, $html, 1);
        }

        return $html;
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

        $style_path = BLOCKBITE_PLUGIN_URL . 'public/style.css';

        if (file_exists($style_path) && filesize($style_path) > 0) {
            $cache_version = filemtime($style_path); // Use file's last modified time
        } else {
            $cache_version = time(); // Fallback to current time
            error_log('Warning: blockbite style.css is missing or empty.');
        }

        if (!is_singular('blockbites')) {
            wp_enqueue_style('blockbite-style', $style_path, [], $cache_version);
        }

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

    public static function global_css()
    {
        $styles = EditorSettings::get_styles_handle('blockbite-global-css');
        if (isset($styles['css'])) {
            echo '<style id="blockbite">' . $styles['css'] . '</style>';
        }
    }
}
