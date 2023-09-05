<?php

namespace Blockbite\Blockbite;

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


    public function __construct()
    {
        $this->blocks = [
            'main',
            'section',
            'group',
            'visual',
            'advanced-button',
            'counter',
            'grid',
            'heading',
            'icon'
        ];
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



        // pas data to react plugin
        wp_localize_script(
            'blockbite-editor',
            'blockbiteEditor',
            [
                'apiUrl'   => rest_url('blockbite/v1'),
                'blocks' => $this->blocks,

            ]
        );
    }
}
