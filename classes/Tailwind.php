<?php

namespace Blockbite\Blockbite;

class tailwind
{

    /**
     * Name of the option settings are saved in
     *
     * @since 0.0.1
     *
     * @var string
     */
    protected $name = '';



    public function __construct()
    {
       
    }



    public function registerAssets()
    {


        $dependencies = [];
        $version      = BLOCKBITE_PLUGIN_VERSION;

        // Use asset file if it exists
        if (file_exists(BLOCKBITE_PLUGIN_DIR . 'build/blockbite-tailwind.asset.php')) {
            $asset_file   = include BLOCKBITE_PLUGIN_DIR . 'build/blockbite-tailwind.asset.php';
            $dependencies = $asset_file['dependencies'];
            $version      = $asset_file['version'];
        }

      
        // register tailwind script
        wp_register_script(
            'blockbite-tailwind',
            plugins_url('build/blockbite-tailwind.js', BLOCKBITE_MAIN_FILE),
            $dependencies,
            $version,
        );

        // add tailwind to editor
        wp_enqueue_script('blockbite-tailwind');
        $tailwind = plugins_url('build/blockbite-tailwind.css?v='.$version, BLOCKBITE_MAIN_FILE);
        add_editor_style($tailwind);

       
        // pas data to react plugin
        wp_localize_script(
            'blockbite-tailwind',
            'blockbiteTailwind',
            [
                
            ]
        );
    }
}
