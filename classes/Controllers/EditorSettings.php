<?php

namespace Blockbite\Blockbite\Controllers;
// use WP_Error
use WP_Error;

use Blockbite\Blockbite\Controllers\Database as DbController;
use Blockbite\Blockbite\Controllers\Bites as BitesController;

class EditorSettings extends Controller
{

    public static function get_settings()
    {
        $data = DBController::getRecordsByHandles([
            'design-tokens',
            'design-tokens-optin',
        ]);
        $designTokensOptin = false;
        $designTokens = false;
        foreach ($data as $row) {
            if ($row->handle === 'design-tokens-optin') {
                $designTokensOptin = json_decode($row->content);
            } else if ($row->handle === 'design-tokens') {
                $designTokens = json_decode($row->content);
            }
        }

        // query single post_type wp_global_styles
        return [
            'designtokens' => $designTokens,
            'designtokensOptin' => $designTokensOptin,
            'utils' => BitesController::get_merged_bite_utils(),
            'blockStyles' => BitesController::get_merged_blockstyles(),

        ];
    }


    public static function get_native_global_styles()
    {
        return DbController::getGlobalStyles();
    }



    private static function minify($input)
    {
        // remove comments
        $output = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $input);
        // remove whitespace
        $output = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $output);
        return $output;
    }

    public static function update_styles($request)
    {
        $content_css = $request->get_param('css');
        $content_tailwind = $request->get_param('tailwind');
        $content_handle = $request->get_param('handle');

        // Check if content_handle is either frontend-css or blockbite-css
        if ($content_handle !== 'frontend-css' && $content_handle !== 'bites-css') {
            return new WP_Error('invalid_handle', 'Invalid handle', ['status' => 400]);
        }

        // Minify the CSS
        $css = self::minify($content_css);

        // If frontend-css, write to public/css/style.css
        if ($content_handle === 'frontend-css') {
            $file_path = BLOCKBITE_PLUGIN_DIR . '/public/style.css';

            // Open the file and check for errors
            $file = fopen($file_path, 'w');
            if ($file === false) {
                return new WP_Error('file_error', 'Failed to open CSS file for writing', ['status' => 500]);
            }

            // Write to the file and close it
            fwrite($file, $css);
            fclose($file);
            $css = ''; // Clear the CSS after writing it, no need to store it in the database
        }

        // Update or create the handle in the database
        return DbController::updateOrCreateHandle([
            'css' => $css,
            'tailwind' => $content_tailwind
        ], $content_handle);
    }



    public static function get_styles($request)
    {
        $handle = $request->get_param('handle');
        $styles = self::get_styles_handle($handle);
        return $styles;
    }


    public static function get_styles_handle($handle)
    {
        $tailwind = '';
        $css = '';
        $user_css = '';


        $result = DbController::getRecordByHandle($handle);
        if (isset($result->tailwind) && isset($result->css)) {
            $tailwind = $result->tailwind;
            $css = $result->css;
        }
        $user_styles_result = DbController::getRecordByHandle('global-user-styles');
        if (isset($user_styles_result->css)) {
            // Custom global style
            $user_css = $user_styles_result->css;
        }
        return
            [
                'tailwind' => $tailwind,
                'css' =>  $css,
                'user_css' => $user_css
            ];
    }



    public static function generate_style($request)
    {
        $style_path = $request['stylePath'];
        wp_enqueue_style('custom-editor-style', $style_path, array(), '1.0', 'all');

        return true;
    }

    public static function add_theme_settings($request)
    {
        $optin = self::get_optin_settings();

        if ($optin) {
            $tokensContent = self::get_tokens_content();
            if (!$tokensContent) {
                return;
            }
            if (property_exists($optin, 'colors') && $optin->colors) {
                self::add_support('editor-color-palette', $tokensContent->colors, 'color', 'disable-custom-colors');
            }
            if (property_exists($optin, 'fontSizes') && $optin->fontSizes) {
                self::add_support('editor-font-sizes', $tokensContent->fontSizes,  'size', 'disable-custom-font-sizes');
            }
            if (property_exists($optin, 'fonts') && $optin->fonts) {
                self::add_support('editor-font', $tokensContent->fonts,  'fontFamily', 'disable-custom-fonts');
            }
        }
    }

    private static function get_optin_settings()
    {
        $optinRecord = DBController::getRecordByHandle('design-tokens-optin');
        if (!$optinRecord) {
            return null;
        } else {
            $content = json_decode($optinRecord->content);
            return $content;
        }
    }

    private static function get_tokens_content()
    {
        $tokensRecord = DBController::getRecordByHandle('design-tokens');
        return $tokensRecord ? json_decode($tokensRecord->content) : null;
    }

    private static function add_support($supportType, $items, $map, $disable)
    {
        if (isset($items)) {
            $supportArray = [];
            foreach ($items as $item) {
                if ($item->value && $item->token && $item->name) {
                    $supportArray[] = [
                        'name' => $item->name,
                        'slug' => $item->token,
                        $map => $item->value
                    ];
                }
            }

            if ($supportType === 'editor-font-sizes') {
                $supportArray = array_reverse($supportArray);
            }
            // add_theme_support($disable);
            add_theme_support($supportType, $supportArray);
        }
    }
}
