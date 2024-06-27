<?php

namespace Blockbite\Blockbite\Controllers;
// use WP_Error
use WP_Error;

use Blockbite\Blockbite\Controllers\Database as DbController;

class EditorStyles extends Controller
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


    public static function update_styles($request)
    {
        $content_css = $request->get_param('blockbite_css');
        $content_tailwind = $request->get_param('blockbite_tailwind');


        return DbController::updateOrCreateRecordById([
            'id' => 1,
            'handle' => 'global',
            'css' => self::minify($content_css),
            'tailwind' => $content_tailwind
        ], 1);
    }


    public static function get_styles()
    {
        $tailwind = '';
        $css = '';

        $result = DbController::getRecordByHandle('global');
        if (isset($result->tailwind) && isset($result->css)) {
            $tailwind = $result->tailwind;
            $css = $result->css;
        }
        return
            [
                'tailwind' => $tailwind,
                'css' =>  $css
            ];
    }



    public static function generate_style($request)
    {
        $style_path = $request['stylePath'];
        wp_enqueue_style('custom-editor-style', $style_path, array(), '1.0', 'all');

        return true;
    }
}
