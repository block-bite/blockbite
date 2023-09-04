<?php

namespace Blockbite\Blockbite;

class Library
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


    public static function  getBlockById($request)
    {
        $id = $request['id'];
        $block = get_post($id);

        if (empty($block)) {
            return new WP_Error('post_not_found', 'Block not found', array('status' => 404));
        }

        // Parse the content into blocks
        $blocks_parsed = parse_blocks($block->post_content);
        $block_preview = '';
        foreach ($blocks_parsed as $parsed_block) {
            $block_preview .= render_block($parsed_block);
        }

        $data = array(
            'ID'            => $block->ID,
            'title'         => get_the_title($block),
            'content'       => $block->post_content,
            'date'          => $block->post_date,
            'preview'       => $block_preview,
            // Add more fields as needed
        );

        return rest_ensure_response($data);
    }

    public static function  getBlocks($request)
    {

        // get all blocks with post_type wp_block
        $blocks = get_posts(array(
            'post_type' => 'wp_block',
            'posts_per_page' => -1,
        ));

        // loop through  blocks
        $data = array();
        foreach ($blocks as $block) {
            // Parse the content into blocks
            $blocks_parsed = parse_blocks($block->post_content);
            $block_preview = '';
            foreach ($blocks_parsed as $parsed_block) {
                $block_preview .= render_block($parsed_block);
            }
            $data[] = array(
                'ID'            => $block->ID,
                'title'         => get_the_title($block),
                'content'       => $block->post_content,
                'date'          => $block->post_date,
                'preview'       => $block_preview,
                // Add more fields as needed
            );
        }


        return rest_ensure_response($data);
    }


    public function registerAssets()
    {


        $dependencies = [];
        $version      = BLOCKBITE_PLUGIN_VERSION;




        // Use asset file if it exists
        if (file_exists(BLOCKBITE_PLUGIN_DIR . 'build/blockbite-library.asset.php')) {
            $asset_file   = include BLOCKBITE_PLUGIN_DIR . 'build/blockbite-library.asset.php';
            $dependencies = $asset_file['dependencies'];
            $version      = $asset_file['version'];
        }

        // register library script
        wp_register_script(
            'blockbite-library',
            plugins_url('build/blockbite-library.js', BLOCKBITE_MAIN_FILE),
            $dependencies,
            $version,
        );

        wp_enqueue_script('blockbite-library');


        // pas data to react plugin
        wp_localize_script(
            'blockbite-library',
            'blockbiteLibrary',
            [
                'libraryUrl'   => 'https://www.block-bite.com/wp-json/blockbite/v1',
                'settings' => [],

            ]
        );
    }
}
