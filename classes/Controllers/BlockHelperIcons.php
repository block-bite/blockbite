<?php

namespace Blockbite\Blockbite\Controllers;
// use WP_Error
use WP_Error;


class BlockHelperIcons extends Controller
{

    // icon directory
    private $icon_dir;
    // icon uri
    private $icon_uri;

    public function __construct()
    {
        $this->icon_dir = get_template_directory() . '/' . BLOCKBITE_ICON_DIR;
        $this->icon_uri = get_template_directory_uri() . '/' . BLOCKBITE_ICON_URI;
    }

    public function get_icons()
    {
        // check if BLOCKBITE_ICON_DIR is directory
        if (!is_dir($this->icon_dir)) {
            return [
                'icon_url' => '',
                'icons' => [],
                'dir' =>  ''
            ];
        } else {
            $icons = scandir($this->icon_dir);
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
                'icon_url' => $this->icon_uri,
                'icons' => $safe_icons,
                'dir' => $this->icon_dir
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
            return "Icon not found";
        } else {
            $content = file_get_contents($this->icon_dir . '/' . $icon);
            return $content;
        }
    }
}
