<?php

namespace Blockbite\Blockbite\Controllers;

use Blockbite\Blockbite\Controllers\Database as DbController;


class Bites extends Controller
{


    public static function update_bites($request)
    {
        $bites = json_decode(json_encode($request->get_param('bites')));


        foreach ($bites as $bite) {
            // Save bite
            DbController::updateOrCreateRecord([
                'data' => json_encode($bite),
                'handle' => 'bites',
                'slug' => $bite->id,
                'post_id' => $bite->post_id,
            ], ['post_id' => $bite->post_id, 'handle' => 'bites', 'slug' => $bite->id]);
        }

        // return updated utils 
        return ['status' => 200, 'utils' => self::get_utils()];
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
        $bites_result = DbController::getAllRecordsByHandle('bites');

        if (empty($bites_result)) {
            return [
                'status' => 404,
                'message' => 'No bites found'
            ];
        } else {
            $bites_result = self::parse_bite_blocks($bites_result);
            return [
                'status' => 200,
                'bites' => $bites_result,
            ];
        }
    }


    public static function parse_bite_blocks($bites)
    {
        // loop over bites and json decode the data
        foreach ($bites as $bites_row) {
            // parse the data
            $bites_row->data = json_decode($bites_row->data);

            // Directly process the serialized property
            $stripped_block = self::strip_bite($bites_row->data->serialized);
            $parsed_block = parse_blocks($stripped_block);
            $bites_row->data->rendered = $stripped_block;
            $bites_row->data->preview = render_block($parsed_block[0]);
        }
        return $bites;
    }


    public static function get_utils()
    {
        return DbController::getUtils();
    }



    public static function get_merged_blockstyles()
    {
        $blockstylesResult = DbController::getAllRecordsByHandle('blockstyles');
        $blockstyles = [];
        foreach ($blockstylesResult as $blockstyle) {
            $blockstyles = array_merge($blockstyles, json_decode($blockstyle->data, true));
        }
        return  $blockstyles;
    }


    /*
        Get all bites
    */
    public static function get_bite_bites()
    {
        $bitesResult = DbController::getAllRecordsByHandle('bites');
        $bites = [];
        foreach ($bitesResult as $bite) {
            $bites = json_decode($bite->data, true);
            foreach ((array) $bites as $bite) {
                if (isset($bite->blocks)) {
                    $bites = array_merge($bites, $bite);
                }
            }
        }
        return  $bites;
    }


    public static function get_bite_utils($request)
    {
        return [
            'status' => 200,
            'utils' => self::get_utils(),
        ];
    }


    public static function get_project()
    {
        // load incluhire_projects.json from public folder of plugin
        $file = file_get_contents(BLOCKBITE_PLUGIN_DIR . 'public/incluhire_projects.json');
        $data = [];
        // if file json decode
        if ($file === false) {
            return new WP_Error('file_not_found', 'Failed to load project file.', array('status' => 500));
        } else {
            // json_decode
            $data = json_decode($file);
            if (isset($data->bites)) {
                $parsed_bites = self::parse_bite_blocks($data->bites);
                foreach ($data->bites as $bite) {
                    $bite->data = json_decode($bite->data);
                }
            }
        }

        return rest_ensure_response($data);
    }

    public static function update_preview_image($request)
    {
        // Ensure the directory exists
        $upload_dir = BLOCKBITE_PLUGIN_DIR . '/public/bite-previews/';
        if (!file_exists($upload_dir)) {
            wp_mkdir_p($upload_dir);
        }

        // Check if a file was uploaded
        if (empty($_FILES['image'])) {
            return new WP_REST_Response(['status' => 'error', 'message' => 'No file uploaded'], 400);
        }

        $file = $_FILES['image'];
        $file_name = $file['name'];
        $file_path = $upload_dir . $file_name;

        // Move the uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $public_url = plugins_url('/public/bite-previews/' . $file_name, BLOCKBITE_PLUGIN_DIR);
            return new WP_REST_Response([
                'status' => 'success',
                'url'    => $public_url, // Return public URL for frontend usage
            ], 200);
        } else {
            return new WP_REST_Response(['status' => 'error', 'message' => 'Upload failed'], 500);
        }
    }
}
