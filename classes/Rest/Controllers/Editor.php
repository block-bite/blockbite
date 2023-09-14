<?php

namespace Blockbite\Blockbite\Rest\Controllers;


class Editor extends Controller
{


    // function to minify CSS file
    public static function minify($input)
    {
        // remove comments
        $output = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $input);
        // remove whitespace
        $output = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $output);
        return $output;
    }


    public function update_styles($request)
    {
        $content = $request->get_param('css');
        $post_id = $request->get_param('post_id');

        // minify css
        $css = self::minify($content);
        // make sure one meta key exists
        delete_post_meta($post_id, 'blockbitecss');
        //update
        update_post_meta($post_id, 'blockbitecss', addslashes($css));
    }

    public function update_references($request)
    {
        $references = $request->get_param('references');
        $post_id = $request->get_param('post_id');
        delete_post_meta($post_id, 'blockbiterefs');
        update_post_meta($post_id, 'blockbiterefs', $references);
    }

    
    
    // get icons
    public function get_icons()
    {

        // check if BLOCKBITE_ICON_DIR is directory
        if (!is_dir(BLOCKBITE_ICON_DIR)) {
            return new WP_Error('icon_dir_not_found', 'Icon directory not found', array('status' => 404));
        } else {
            $icons = scandir(BLOCKBITE_ICON_DIR);

            $safe_icons = [];
    
            if (is_array($icons)) {
                // check if $icons have a safe svg extension and push to $safe_icon
                foreach ($icons as $key => $icon) {
                    if (strpos($icon, '.svg') !== false) {
                        // strip extension
                        $icon = str_replace('.svg', '', $icon);
                        // push only filename without /svg
                        $safe_icons[] = $icon;
                    }
                }
            }
            return [
                'icon_url' => BLOCKBITE_ICON_URI,
                'icons' => $safe_icons
            ];
        }

    }


    // create function for rest route pick_icon
    public function pick_icon($request)
    {
        // add extension here
        $icon = $request['icon'] . '.svg';
        //
        if (!file_exists($this->icon_dir . '/' . $icon)) {
            return new WP_Error('icon_not_found', 'Icon not found', array('status' => 404));
        } else {
            $content = file_get_contents($this->icon_dir . '/' . $icon);
            // remove fills and class
            $icon_inline_format1 = str_replace('fill', 'data-fill', $content);
            $icon_inline_format2 = str_replace('class', 'data-class', $icon_inline_format1);
            $icon_inline = str_replace('<svg', '<svg fill="currentColor" stroke="currentColor"', $icon_inline_format2);

            return $icon_inline;
        }
    }

    // create function for rest route pick_link
    public function pick_link($request)
    {
        $search = $request['keyword'];

        $post_types = get_post_types();

        $unset_post_types = [
            'attachment',
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
