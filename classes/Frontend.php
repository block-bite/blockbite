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

       
        $blockbite_meta_keys = self::blockbite_meta_keys();
        $css = '';

         // Get Tailwind CSS from each post and compile
        foreach($blockbite_meta_keys as $id){
            $css .= get_post_meta($id, 'blockbitecss', true);
        }
        // Output the compiled CSS within <style> tags
        if (!empty($css)) {
            echo '<style>' . $css . '</style>';
        }
       
    }

    // get all meta keys to query
    private static function blockbite_meta_keys(){
        $meta_id  = [];
        $post_id = get_the_ID();
        $refs=  get_post_meta($post_id, 'blockbiterefs', true);
        if(is_array($refs)){
            foreach($refs as $ref){
                $meta_id[] = $ref;
            }
        }
        $meta_id[] = $post_id;
        return $meta_id;
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
