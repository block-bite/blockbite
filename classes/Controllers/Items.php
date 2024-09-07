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

    private static function validate($data, $fields)
    {
        $validated_data = [];

        foreach ($fields as $field) {
            switch ($field) {
                case 'handle':
                    if (empty($data['handle']) || !is_string($data['handle'])) {
                        return new WP_Error('invalid_handle', self::$error_messages['invalid_handle'], ['status' => 400]);
                    }
                    $validated_data['handle'] = sanitize_text_field($data['handle']);
                    break;

                case 'id':
                    if (isset($data['id']) && (!is_numeric($data['id']))) {
                        return new WP_Error('invalid_id', self::$error_messages['invalid_id'], ['status' => 400]);
                    }
                    $validated_data['id'] = isset($data['id']) ? intval($data['id']) : null;
                    break;

                case 'is_default':
                    if (isset($data['is_default']) === "") {
                        $data['is_default'] = 0;
                    }
                    $validated_data['is_default'] = intval($data['is_default']);
                    break;
                case 'blockname':
                    if (empty($data['blockname']) || !is_string($data['blockname'])) {
                        return new WP_Error('invalid_blockname', self::$error_messages['invalid_blockname'], ['status' => 400]);
                    }
                    $validated_data['blockname'] = sanitize_text_field($data['blockname']);
                    break;

                default:
                    if (isset($data[$field])) {
                        $validated_data[$field] = sanitize_text_field($data[$field]);
                    }
                    break;
            }
        }
        return $validated_data;
    }


    public static function upsert_item($request = null)
    {
        $data = $request->get_params();
        $validated_data = self::validate($data, ['handle', 'id']);

        if (is_wp_error($validated_data)) {
            return $validated_data;
        }
        $handle = $validated_data['handle'];
        $id = $validated_data['id'];

        $upsert = DbController::updateOrCreateRecord($data, ['handle' => $handle, 'id' => $id]);

        return new WP_REST_Response([
            'status' => 200,
            'message' => $handle . ' saved',
            'data' => $upsert
        ], 200);
    }



    public static function upsert_item_handle($request = null)
    {
        $data = $request->get_params();
        $validated_data = self::validate($data, ['handle']);

        if (is_wp_error($validated_data)) {
            return $validated_data;
        }
        $handle = $validated_data['handle'];
        $upsert = DbController::updateOrCreateHandle($data, $handle);

        return new WP_REST_Response([
            'status' => 200,
            'message' => $handle . ' saved',
            'data' => $upsert
        ], 200);
    }




    public static function delete_item($request = null)
    {
        $data = $request->get_params();
        $validated_data = self::validate($data, ['id']);

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
        $validated_data = self::validate($data, ['handle']);

        if (is_wp_error($validated_data)) {
            return $validated_data;
        }

        $handle = $validated_data['handle'];
        $result = DbController::getAllRecordsByHandle($handle);

        $result = array_map(function ($item) {
            $item->is_default = intval($item->is_default);
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
        $id = $validated_data['id'];
        $result = DbController::getRecordByHandle($handle);

        return new WP_REST_Response([
            'status' => 200,
            'message' => $handle . ' fetched',
            'data' => $result
        ], 200);
    }



    public static function toggle_default_item($request = null)
    {
        $data = $request->get_params();
        $validated_data = self::validate($data, ['handle', 'id', 'is_default', 'blockname']);

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
