<?php

namespace Blockbite\Blockbite\Controllers;

use Blockbite\Blockbite\Controllers\Database as DbController;


class Bites extends Controller
{


    public static function update_bites($request)
    {
        $bites = $request->get_param('bites');
        $post_id = intval($request->get_param('post_id'));
        $utils = $request->get_param('utils');
        $blockstyles = $request->get_param('blockstyles');


        $bites_saved = DbController::updateOrCreateRecord(
            ['content' => $bites, 'handle' => 'bites', 'post_id' => $post_id],
            ['post_id' => $post_id, 'handle' => 'bites'],
        );

        $utils_saved = DbController::updateOrCreateRecord(
            ['content' => $utils, 'handle' => 'utils', 'post_id' => $post_id],
            ['post_id' => $post_id, 'handle' => 'utils'],
        );

        $blockstyles_saved = DbController::updateOrCreateRecord(
            ['content' => $blockstyles, 'handle' => 'blockstyles', 'post_id' => $post_id],
            ['post_id' => $post_id, 'handle' => 'blockstyles'],
        );

        return [
            'status' => 200,
            'bites' => 'Bites saved',
            'utils' => $utils_saved,
            'blockstyles' => $blockstyles_saved,
            'post_id' => $post_id,
        ];
    }



    public static function get_bite_library($request)
    {
        $bites = DbController::getAllRecordsByHandle('bites');
        $bite_nav = [];
        foreach ($bites as $bite) {
            $bite_post_id = $bite->post_id;
            $name = get_the_title($bite_post_id);
            // check if is published
            if (get_post_status($bite_post_id) !== 'publish') {
                continue;
            }
            $bite_nav[] = [
                'name' => $name,
                'post_id' => $bite_post_id,
            ];
        }
        return [
            'status' => 200,
            'navigation' => $bite_nav,
        ];
    }



    public static function strip_bite($content)
    {
        // $decoded_content = stripslashes($content);
        $decoded_content = str_replace(array("\n", "\r"), array('', ''), $content);
        return $decoded_content;
    }

    public static function get_bite_blocks($request)
    {

        $post_id = intval($request->get_param('post_id'));

        $bites = DbController::getRecordByQuery([
            'handle' => 'bites',
            'post_id' => $post_id
        ]);

        if (empty($bites)) {
            return [
                'status' => 404,
                'message' => 'No bites found'
            ];
        } else {

            $block_content = json_decode($bites->content);

            $blocks = [];
            $index = 0;
            foreach ($block_content as $block) {

                $strip_component =   self::strip_bite($block->component);
                $strip_raw =   self::strip_bite($block->raw);

                $parse_block = parse_blocks(
                    $strip_raw
                );

                if (empty($parse_block)) {
                    continue;
                }
                array_push($blocks, [
                    'id' => $block->id,
                    'preview' => render_block($parse_block[0]),
                    'component' => $strip_component,
                    'raw' => $strip_raw,
                    'width' => $block->width,
                    'height' => $block->height,
                ]);
            }

            return [
                'status' => 200,
                'blocks' => $blocks,

            ];
        }
    }

    public static function get_merged_bite_utils()
    {
        $utilsResult = DbController::getAllRecordsByHandle('utils');
        $utils = [];
        foreach ($utilsResult as $util) {
            $utils = array_merge($utils, json_decode($util->content, true));
        }
        return  $utils;
    }

    public static function get_merged_blockstyles()
    {
        $blockstylesResult = DbController::getAllRecordsByHandle('blockstyles');
        $blockstyles = [];
        foreach ($blockstylesResult as $blockstyle) {
            $blockstyles = array_merge($blockstyles, json_decode($blockstyle->content, true));
        }
        return  $blockstyles;
    }



    public static function get_bite_utils($request)
    {
        return [
            'status' => 200,
            'utils' => self::get_merged_bite_utils(),
        ];
    }
}
