<?php

namespace Blockbite\Blockbite\Rest\Controllers;

use Blockbite\Blockbite\Rest\Controllers\Editor;

class Library extends Controller
{



    /*
      // get all blocks
        register_rest_route($this->namespace, '/library/block', [
            [
                'methods' => 'GET',
                'callback' => [$libraryController, 'get_blocks'],
                'permission_callback' => []
            ]
        ]);
        // get single blocks
        register_rest_route($this->namespace, '/library/block/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [$libraryController, 'get_block'],
                'permission_callback' => [$libraryController, 'authorize']
            ]
        ]);
    */

    public static function  get_block($request)
    {

        $id = $request->get_param('id');
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
        );

        return rest_ensure_response($data);
    }

    public static function  get_blocks($request)
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
                'category'      => get_the_terms($block->ID, 'wp_pattern_category'),
            );
        }


        return rest_ensure_response($data);
    }
}
