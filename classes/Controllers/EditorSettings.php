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
                $designTokensOptin = json_decode($row->data);
            } else if ($row->handle === 'design-tokens') {
                $designTokens = json_decode($row->data);
            }
        }

        // query single post_type wp_global_styles
        return [
            'designtokens' => $designTokens,
            'designtokensOptin' => $designTokensOptin,
            'utils' => self::frontend_matching_utils(),
            'utilsOrigin' => BitesController::get_utils(),
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

    public static function update_frontend_css($request)
    {
        $data = $request->get_param('data');
        $content = $request->get_param('content');

        // get option blockbite_css_name (default is style)

        $file_name = get_option('blockbite_css_name', 'style') . '.css';

        // Minify the CSS
        $css = self::minify($content);
        $file_path = BLOCKBITE_PLUGIN_DIR . '/public/' . $file_name;

        // Open the file and check for errors
        $file = fopen($file_path, 'w');


        // Write to the file and close it
        fwrite($file, $css);
        fclose($file);
        $css = ''; // Clear the CSS after writing it, no need to store it in the database


        // Update or create the handle in the database
        return DbController::updateOrCreateHandle([
            'data' => json_encode($data),
            // for now we can leave this to an empty string, we can add an inline option later
            'content' => '',
        ], 'frontend-css');
    }




    public static function get_frontend_css()
    {
        $tailwind = '';
        $bites = '';

        $result = DbController::getRecordByHandle('frontend-css');
        if (isset($result->data)) {
            $data = json_decode($result->data);

            if (isset($data->tailwind)) {
                $tailwind = $data->tailwind;
            }
            if (isset($data->bites)) {
                $bites = $data->bites;
            }
        }
        return
            [
                'tailwind' => $tailwind,
                'bites' =>  $bites,
            ];
    }

    public static function get_scripts_handle($handle)
    {

        $js = '';
        $result = DbController::getRecordByHandle($handle);

        if (isset($result->content)) {
            $js = $result->content;
        }
        return
            [
                'content' =>  $js,
            ];
    }



    // Optimized for performance
    public static function frontend_matching_utils()
    {
        $frontendCss = self::get_frontend_css();
        $all_utils = BitesController::get_utils();

        if (!$all_utils) {
            return [];
        }

        $bites = isset($frontendCss['bites']) ? explode(' ', $frontendCss['bites']) : [];

        $matches = array_filter($all_utils, function ($util) use ($bites) {
            return in_array($util['id'], $bites, true);
        });

        return array_values($matches);
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
        if (!isset($optinRecord->data)) {
            return null;
        } else {
            return $optinRecord->data;
        }
    }

    private static function get_tokens_content()
    {
        $tokensRecord = DBController::getRecordByHandle('design-tokens');
        return $tokensRecord ? json_decode($tokensRecord->data) : null;
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
