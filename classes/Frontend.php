<?php

namespace Blockbite\Blockbite;

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

        // post id based css
        $css = '';
        $post_id = get_the_ID();
        // Check if the cached CSS exists
        /*
        $cached_css = get_transient('blockbite_css_cache_' . $post_id);
        if ($cached_css !== false) {
            echo '<style>' . $cached_css . '</style>';
            return;
        }
        */

        // get metakeys
        $blockbite_css_metakeys = $this->blockbite_css_metakeys($post_id);
        // add blockbite_template_css
        $blockbite_template_css = $this->blockbite_template_css();
        // prioritize wp_template_part css first (header and footer)
        foreach ((array) $blockbite_template_css as $key => $value) {
            $css .= $value->blockbitecss_value;
        }
        // then add reusable blocks and page css
        foreach ($blockbite_css_metakeys as $id) {
            $css .= get_post_meta($id, 'blockbitecss', true);
        }
        // Output the compiled CSS within <style> tags

        if (!empty($css)) {
            // $cleanCsss = self::uniqueCss($css);
            // Cache the CSS for future use
            // set_transient('blockbite_css_cache_' . $post_id, $css, 0);
            $sortedCss = self::sortCss($css);
            $cleanCss = self::uniqueCss($sortedCss);
            echo '<style>' . $cleanCss . '</style>';
        }
    }

    // create unique css
    static function uniqueCss($input)
    {

        // $regex = '/(\.[a-zA-Z0-9_-]+\s*{[^}]*})(?=.*\1)/s';
        // $cleaned_css = preg_replace($regex, '', $input);
        $pattern = '/(\.[a-zA-Z0-9-]+\s*\{[^}]+\})(?=.*\1)/';
        $uniqueString = preg_replace($pattern, '', $input);

        return $uniqueString;
    }

    static function sortCss($input)
    {
        // Extract media queries using regular expressions
        preg_match_all('/@media\s+\(.*?\)\s*{.*?}\s*}/s', $input, $matches);
        $mediaQueries = implode("\n", $matches[0]);
        // Remove media queries from the original CSS
        $cssWithoutMediaQueries = preg_replace('/@media\s+\(.*?\)\s*{.*?}\s*}/s', '', $input);
        // Add media queries back at the end of the new string
        $newCss = $cssWithoutMediaQueries . "\n\n" . $mediaQueries;

        return $newCss;
    }

    // template css for o.a header and footer
    static function blockbite_template_css()
    {
        global $wpdb; // WordPress database access object

        // Define the table names and prefixes
        $postmeta_table = $wpdb->prefix . 'postmeta';

        // Define the query
        $query = "
            SELECT a.post_id, a.meta_key AS origin_key, a.meta_value AS origin_value, b.meta_key AS blockbitecss_key, b.meta_value AS blockbitecss_value
            FROM $postmeta_table AS a
            JOIN $postmeta_table AS b ON a.post_id = b.post_id
            WHERE a.meta_key = 'origin' AND a.meta_value = 'theme'
            AND b.meta_key = 'blockbitecss';
        ";


        // Run the query
        $results = $wpdb->get_results($query);
        return $results;
    }

    // get refs / reusable block css / recursive (if a reusable block has a reusable block inside it)
    private static function blockbite_css_metakeys($post_id, &$processed_ids = [])
    {
        // Check if the post ID has already been processed
        if (in_array($post_id, $processed_ids)) {
            return [];
        }

        // Add the current post ID to the list of processed IDs
        $processed_ids[] = $post_id;
        $meta_ids = [$post_id];
        $refs = get_post_meta($post_id, 'blockbiterefs', true);

        if (is_array($refs) && !empty($refs)) {
            foreach ($refs as $ref) {
                // Check if the reference post ID is valid
                if (is_numeric($ref) && $ref > 0) {
                    // Recursive call
                    $meta_ids = array_merge($meta_ids, self::blockbite_css_metakeys($ref, $processed_ids));
                }
            }
        }

        return $meta_ids;
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
    }

    public function registerAssetsBackend()
    {
        add_editor_style($this->css_url);
    }
}
