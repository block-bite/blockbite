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
        return [
            'designtokens' => $designTokens,
            'designtokensOptin' => $designTokensOptin,
            'utils' => BitesController::get_merged_bite_utils(),
            'blockStyles' => BitesController::get_merged_blockstyles(),
        ];
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
