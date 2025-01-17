<?php

namespace Blockbite\Blockbite\Controllers;

use WP_REST_Response;
use WP_Error;

use Blockbite\Blockbite\Controllers\Database as DbController;

class Items extends Controller
{


    protected $public_url = '';



    private static $error_messages = [
        'invalid_handle' => 'Handle is required and must be a string',
        'invalid_id' => 'ID is required and must be numeric',
        'invalid_is_default' => 'is_default is required',
        'invalid_blockname' => 'blockname is required'
    ];




    private static function validate($data)
    {
        $fields_schema = [
            'id' => 'int',
            'handle' => 'string',
            'category' => 'string',
            'blockname' => 'string',
            'is_default' => 'boolean',
            'platform' => 'string',
            'title' => 'string',
            'slug' => 'string',
            'version' => 'string',
            'summary' => 'string',
            'css' => 'text',
            'tailwind' => 'text',
            'content' => 'text',
            'post_id' => 'int',
            'parent' => 'int',
            'updated_at' => 'timestamp',
            'data' => 'json',
        ];

        $validated_data = [];

        foreach ($fields_schema as $field => $type) {
            switch ($type) {
                case 'int':
                    $validated_data[$field] = isset($data[$field]) && is_numeric($data[$field]) && $data[$field] != 0
                        ? intval($data[$field])
                        : null;
                    break;

                case 'string':
                    $validated_data[$field] = isset($data[$field]) && is_string($data[$field])
                        ? sanitize_text_field($data[$field])
                        : '';
                    break;

                case 'boolean':
                    $validated_data[$field] = isset($data[$field])
                        ? (bool) $data[$field]
                        : false;
                    break;

                case 'text':
                    $validated_data[$field] = isset($data[$field])
                        ? sanitize_textarea_field($data[$field])
                        : '';
                    break;

                case 'json':
                    $validated_data[$field] = isset($data[$field]) && is_array($data[$field])
                        ? json_encode($data[$field])
                        : '{}';
                    break;

                case 'timestamp':
                    $validated_data[$field] = isset($data[$field]) && strtotime($data[$field])
                        ? date('Y-m-d H:i:s', strtotime($data[$field]))
                        : date('Y-m-d H:i:s');
                    break;

                default:
                    // Handle unexpected types if needed.
                    $validated_data[$field] = isset($data[$field])
                        ? sanitize_text_field($data[$field])
                        : '';
                    break;
            }
        }

        // Specific validation for required fields
        if (empty($validated_data['handle'])) {
            return new WP_Error('invalid_handle', self::$error_messages['invalid_handle'], ['status' => 400]);
        }

        return $validated_data;
    }



    public static function upsert_item($request = null)
    {
        $data = $request->get_params();
        $validated_data = self::validate($data);

        if (is_wp_error($validated_data)) {
            return $validated_data;
        }

        $upsert = DbController::updateOrCreateRecord($validated_data, ['handle' => $validated_data['handle'], 'id' => $validated_data['id']]);

        return new WP_REST_Response([
            'status' => 200,
            'message' => $validated_data['id'] . ' saved',
            'data' => $upsert
        ], 200);
    }



    public static function upsert_item_handle($request = null)
    {
        $data = $request->get_params();
        $validated_data = self::validate($data);


        if (is_wp_error($validated_data)) {
            return $validated_data;
        }


        $upsert = DbController::updateOrCreateHandle($validated_data, $validated_data['handle']);

        // json decode data for return
        $upsert['data'] = json_decode($upsert['data']);

        return new WP_REST_Response([
            'status' => 200,
            'message' => $validated_data['id'] . ' saved',
            'data' => $upsert
        ], 200);
    }




    public static function delete_item($request = null)
    {
        $data = $request->get_params();
        $validated_data = self::validate($data);

        if (is_wp_error($validated_data)) {
            return $validated_data;
        }

        $id = $validated_data['id'];

        $deleted = DbController::deleteRecordById($id);

        return new WP_REST_Response([
            'status' => 200,
            'message' => $id . ' deleted',
            'data' => $deleted
        ], 200);
    }

    public static function get_items($request)
    {
        $data = $request->get_params();
        $validated_data = self::validate($data);

        if (is_wp_error($validated_data)) {
            return $validated_data;
        }

        $handle = $validated_data['handle'];
        $result = DbController::getAllRecordsByHandle($handle);

        $result = array_map(function ($item) {
            $item->is_default = intval($item->is_default);
            // decode data
            $item->data = json_decode($item->data);
            return $item;
        }, $result);




        return new WP_REST_Response([
            'status' => 200,
            'message' => $handle . ' fetched',
            'data' => $result
        ], 200);
    }

    public static function get_item($request)
    {
        $data = $request->get_params();



        $validated_data = self::validate($data, ['handle']);

        if (is_wp_error($validated_data)) {
            return $validated_data;
        }

        $handle = $validated_data['handle'];
        $result = DbController::getRecordByHandle($handle);

        // decode data
        if ($result) {
            $result->data = json_decode($result->data);
        }


        return new WP_REST_Response([
            'status' => 200,
            'message' => $handle . ' fetched',
            'data' => $result
        ], 200);
    }



    public static function toggle_default_item($request = null)
    {
        $data = $request->get_params();
        $validated_data = self::validate($data);

        if (is_wp_error($validated_data)) {
            return $validated_data;
        }

        $handle = $validated_data['handle'];
        $id = $validated_data['id'];
        $is_default = $validated_data['is_default'];
        $blockname = $validated_data['blockname'];

        $default = DbController::toggleDefaultHandle($id, $handle, $is_default, $blockname);

        return new WP_REST_Response([
            'status' => 200,
            'message' => $is_default ? 'Toggled ' . $id . ' true' : 'Toggled ' . $id . ' false',
            'data' => $is_default
        ], 200);
    }
}
