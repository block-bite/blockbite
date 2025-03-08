<?php

namespace Blockbite\Blockbite\Controllers;

use Blockbite\Blockbite\Controllers\Database as DbController;
use Blockbite\Blockbite\Controllers\Bites as BitesController;

use WP_REST_Response;
use WP_Error;

class DynamicContent extends Controller
{


    public static function update_dynamic_content($request)
    {

        $id = $request->get_param('id');
        $title = $request->get_param('title');
        // slug title lowercase
        $slug = strtolower(str_replace(' ', '_', $title));


        // if no id create new record
        if (!$id) {
            $data = [
                'title' => $title,
                'slug' => $slug,
                'handle' => 'dynamic_content',
                'data' => json_encode([]),
            ];
        } else {
            $data = [
                'data' =>  json_encode($request->get_param('data')),
            ];

            error_log(json_encode($data));
        }

        $dynamic_content_saved = DbController::updateOrCreateRecord(
            $data,
            ['id' => $id, 'handle' => 'dynamic_content'],
        );

        return [
            'status' => 200,
            'msg' => 'dynamic_content saved',
            'id' => $dynamic_content_saved['id'],
        ];
    }

    /*
    public static function update_design_dynamic_content($request)
    {

        $id = $request->get_param('id');
        $raw = $request->get_param('content');

        $strip_raw = BitesController::strip_bite($raw);

        $parsed_block = parse_blocks($strip_raw);
        $dynamic_content_saved = false;


        $rendered_block = render_block($parsed_block[0]);

        // save the parsed block to content
        $dynamic_content_saved = DbController::updateOrCreateRecord(
            ['content' => $rendered_block],
            ['id' => $id, 'handle' => 'dynamic_content'],
        );

        return [
            'status' => 200,
            'msg' => 'dynamic_content saved',
            'rendered_block' => $rendered_block,
            'parse_block' => $parsed_block,


        ];
    }
    */

    public static function get_dynamic_designs_by_parent($request)
    {
        $parent = $request->get_param('id');
        $dynamic_design = DbController::getAllRecordsByHandleQuery('dynamic_design', ['parent' => $parent]);

        if (empty($dynamic_design)) {
            return [
                'status' => 200,
                'result' => 'No dynamic design found',
                'parent' => $parent,
            ];
        }

        return [
            'status' => 200,
            'result' => $dynamic_design,
            'parent' => $parent,
        ];
    }


    /*
        Save multiple records at once
    */
    public static function update_design_dynamic_blocks($request)
    {

        // get all dynamic design blocks within page
        $blocks = $request->get_param('blocks');
        // json decode the blocks
        $blocks = json_decode($blocks, true);

        // loop blocks
        foreach ($blocks as $block) {
            $parent = $block['parent'];
            $content = $block['content'];
            $slug = $block['slug'];
            $title = $block['title'];

            $strip_raw = BitesController::strip_bite($content);
            $parsed_block = parse_blocks($strip_raw);
            // if parsed block has length
            if (count($parsed_block) === 0) {
                continue;
            }
            $rendered_block = render_block($parsed_block[0]);

            $dynamic_content_saved = DbController::updateOrCreateRecord(
                [
                    'title' => $title,
                    'content' => $rendered_block,
                    'slug' => $slug,
                    'handle' => 'dynamic_design',
                    'parent' => $parent,
                ],
                ['parent' => $parent, 'handle' => 'dynamic_design', 'slug' => $slug],
            );
        }
        return [
            'status' => 200,
            'result' => 'blocks saved',
        ];
    }



    public static function get_dynamic_content_items($request)
    {
        $dynamic_content = DbController::getAllRecordsByHandle(['handle' => 'dynamic_content'], ['id', 'title', 'summary']);

        if (empty($dynamic_content)) {
            return [
                'status' => 200,
                'data' => 'No dynamic content found',
            ];
        }

        return [
            'status' => 200,
            'result' => $dynamic_content,
        ];
    }

    public static function get_dynamic_content($request = null)
    {
        $id = is_null($request) ? null : $request->get_param('id');

        // Retrieve the ID dynamically or use it directly if passed to the function
        if (is_null($id)) {
            return [
                'status' => 400,
                'message' => 'ID is required',
            ];
        }

        $dynamic_content = DbController::getRecord(['id' => $id, 'handle' => 'dynamic_content']);

        if (empty($dynamic_content)) {
            return [
                'status' => 200,
                'data' => [],
                'id' => $id,
            ];
        }

        return [
            'status' => 200,
            'data' => json_decode($dynamic_content->data),
            'id' => $dynamic_content->id,
        ];
    }



    public static function render_dynamic_content_rest($request = null)
    {
        $id = is_null($request) ? null : $request->get_param('contentId');
        $designId = is_null($request) ? null : $request->get_param('designId');
        $renderTag = is_null($request) ? null : $request->get_param('renderTag');


        return self::get_dynamic_content_records($id, $designId, $renderTag, true);
    }

    // fromout template
    public static function render_dynamic_content($contentId, $designId, $renderTag)
    {
        self::get_dynamic_content_records($contentId, $designId, $renderTag, false);
    }


    public static function get_dynamic_content_records($contentId, $designId, $renderTag, $rest)
    {
        $dynamicContent = DbController::getRecord(['id' => $contentId, 'handle' => 'dynamic_content']);
        $dynamicDesign = DbController::getRecord(['parent' => $contentId, 'handle' => 'dynamic_design', 'slug' => $designId]);

        if (empty($dynamicContent) && empty($dynamicDesign)) {
            return self::handle_dynamic_content_error($dynamicContent, $rest);
        } else {
            return self::process_dynamic_content($dynamicContent, $dynamicDesign, $renderTag, $rest);
        }
    }


    public static function handle_dynamic_content_error($dynamicContent, $rest)
    {
        if (empty($dynamicContent) && $rest) {
            return [
                'status' => 404,
                'message' => 'Dynamic content not found',
            ];
        } else {
            error_log('Dynamic content not found');
        }
    }


    private static function process_dynamic_content($dynamicContent, $dynamicDesign, $renderTag, $rest)
    {
        $rest_data = []; // To hold all rendered outputs if $rest is true
        $replaced = ''; // To hold the rendered output if $rest is false


        // array of both schema and content
        $content_array = json_decode($dynamicContent->data, true);

        // if  $dynamicDesign->content;
        if (empty($dynamicDesign)) {
            return [
                'status' => 404,
                'message' => 'Dynamic design not found',
            ];
        }

        $snippet = $dynamicDesign->content;



        foreach ((array) $content_array['content'] as $row) {
            // Ensure $snippet is a valid string
            if (!is_string($snippet)) {
                $snippet = ''; // Provide a default value if it's not a string
            }

            // Replace the snippet with the dynamic content
            $output = preg_replace_callback('/#\{(\w+)\}#/', function ($matches) use ($row) {
                return htmlspecialchars($row[$matches[1]] ?? '', ENT_QUOTES, 'UTF-8');
            }, $snippet);


            ob_start();
            // rest, rest for editor use handles display in block with html injection
            if ($rest) {
                $rest_data[] = $output;
            } else {
                // else render the output in the frontend
                if ($renderTag === 'slide') {
                    echo '<swiper-slide>' . $output . '</swiper-slide>';
                } else {
                    echo $output;
                }
            }
            $buffered_output = ob_get_clean();
            if (!$rest) {
                echo $buffered_output;
            }
        }


        if ($rest) {
            return [
                'status' => 200,
                'data' => $rest_data,
            ];
        }
    }
}
