<?php

namespace Blockbite\Blockbite;

use Blockbite\Blockbite\Controllers\EditorSettings as EditorSettingsController;



use WP_Error;

class PostTypes
{
    public static function register_bites()
    {
        $labels = array(
            'name'               => 'Bites',
            'singular_name'      => 'Bites',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Bite',
            'edit_item'          => 'Edit Bite',
            'new_item'           => 'New Bite',
            'all_items'          => 'All Bite',
            'view_item'          => 'View Bite',
            'search_items'       => 'Search Bite',
            'not_found'          => 'No Bite found',
            'not_found_in_trash' => 'No Bite found in the Trash',
            'parent_item_colon'  => '',
            'menu_name'          => 'Bite',
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'exclude_from_search' => true,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'blockbites'),
            'capability_type'    => 'post',
            'show_in_rest'       => true,
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
            'template'           => array(
                array('blockbite/bites-wrap', array(), array())
            ),

        );

        register_post_type('blockbites', $args);
    }

    /*
        Pulls in the styles for the blockbite
        Hide the blockbite from search engines
    */

    public static function bites_view()
    {
        if (is_singular('blockbites')) {
            echo '<meta name="robots" content="noindex, nofollow" />';
            $styles = EditorSettingsController::get_styles_handle('bites-css');
            if (isset($styles['css'])) {
                echo '<style id="blockbite">' . $styles['css'] . '</style>';
            }
        }
    }

    /*
    public static function restrict_block_to_post_type($allowed_block_types, $block_editor_context)
    {

        if (isset($block_editor_context->post) && 'blockbites' === $block_editor_context->post->post_type) {
            return array(
                'blockbite/bite', // This ensures only this block is allowed at the top level.
                // Other blocks will be handled via the innerBlocks in JavaScript.
            );
        }
        return $allowed_block_types;
    }
        */
}
