<?php

namespace Blockbite\Blockbite\Controllers;
// use WP_Error
use WP_Error;

use Blockbite\Blockbite\Controllers\Database as DatabaseController;

class BlockHelperLinks extends Controller
{


    // create function for rest route pick_link
    public function pick_link($request)
    {
        $search = $request['keyword'];

        $post_types = get_post_types();

        $unset_post_types = [
            'revision',
            'nav_menu_item',
            'custom_css',
            'customize_changeset',
            'oembed_cache',
            'user_request',
            'wp_block',
            'acf-field-group',
            'acf-field',
            'wpforms',
            'wpforms_log',
            'revision',
            'wp_template_part',
            'lazyblocks',
            'jet-form-builder',
            'wp_global_styles',
            'wp_navigation',
            'wp_navigation_menu',
            'wp_navigation_menu_item',
            'wp_template',
            'wp_block'
        ];

        foreach ($unset_post_types as $key => $post_type) {
            if (($key = array_search($post_type, $post_types)) !== false) {
                unset($post_types[$key]);
            }
        }

        // query by post_types
        $query = new \WP_Query([
            's' => $search,
            'post_type' => $post_types,
            'posts_per_page' => 10,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $links = [];
        while ($query->have_posts()) {
            $query->the_post();
            $links[] = [
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'url' => get_the_permalink(),
                'post_type' => get_post_type(),
            ];
        }
        wp_reset_postdata();

        return $links;
    }

    // search posts by title keyword
    public function search_keyword($where, $query)
    {
        global $wpdb;
        $starts_with = esc_sql($query->get('starts_with'));
        if ($starts_with) {
            $where .= " AND $wpdb->posts.post_title LIKE '$starts_with%'";
        }
        return $where;
    }
}
