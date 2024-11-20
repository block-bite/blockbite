<?php

namespace Blockbite\Blockbite\Controllers;

use WP_REST_Response;
use WP_Error;

use Blockbite\Blockbite\Controllers\Database as DbController;
use Blockbite\Blockbite\Controllers\MigrateTemplates as MigrateTemplates;

class Settings extends Controller
{

    protected $handles = [];
    private static $encryption;

    public function __construct()
    {
        $this->handles = [
            'preset',
            'bites',
            'design-tokens',
        ];
        self::$encryption = new \Blockbite\Blockbite\Controllers\DataEncryption();
    }

    public  function export_items($request = null)
    {

        $handle = $request->get_param('handle');


        error_log($handle);

        if (!in_array($handle, $this->handles)) {
            return new WP_Error('invalid_handle', 'Invalid handle', array('status' => 400));
        }

        if (!current_user_can('manage_options')) {
            return new WP_Error('auth_failed', "You do not have sufficient permissions to access this page.", ['status' => 400]);
        }

        $db = new DbController();
        $items = $db->getAllRecordsByHandle($handle);

        if ($items === false) {
            return new WP_Error('no_items', "No items found", ['status' => 400]);
        }

        $items = array_filter($items, function ($items) {
            return $items->platform === 'site';
        });

        if (empty($items)) {
            return new WP_Error('no_items', "No items available for export", ['status' => 400]);
        }

        // Convert items array to JSON
        $json_data = json_encode($items);
        if ($json_data === false) {
            return new WP_Error('json_encoding_failed', "JSON encoding failed", ['status' => 400]);
        }

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="data.json"');

        // Output the JSON data
        echo $json_data;
    }


    public function import_items($request = null)
    {
        $inserted = false;
        $handle = null;

        $data = $request->get_params();


        if (!isset($data['handle']) && !in_array($handle, $this->handles)) {
            return new WP_Error('invalid_handle', 'Invalid handle', array('status' => 400));
        } else {
            $handle = $data['handle'];
        }

        if (empty($_FILES['file'])) {
            return new WP_Error('no_file', 'No file provided', array('status' => 400));
        }

        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', 'Upload error: ' . $file['error'], array('status' => 400));
        }

        $json_data = file_get_contents($file['tmp_name']);
        $data = json_decode($json_data, true);

        if ($data === null) {
            return new WP_Error('json_decoding_failed', 'JSON decoding failed', array('status' => 400));
        }
        if (count($data) === 0) {
            return new WP_Error('no_data', 'No data found in the file', array('status' => 400));
        }
        if (!array_key_exists("handle", $data[0]) && $data[0]['handle'] !== $handle) {
            return new WP_Error('invalid_data', 'Invalid blockbite file', array('status' => 400));
        } else {
            $db = new DbController();
            $db->insertRecords($data);
        }

        return new WP_REST_Response([
            'status' => 200,
            'message' => 'succes',
            'data' => json_decode($json_data)
        ], 200);
    }

    public static function sync_blockbite_items($request = null)
    {
        if (is_array($request)) {
            $data = $request;
        } else {
            $data = $request->get_params();
        }
        error_log(json_encode($data));

        if (!isset($data['handle']) || !isset($data['version'])) {
            return new WP_Error('invalid_data', 'Invalid data', array('status' => 400));
        }
        $version = $data['version'];
        $handle = $data['handle'];
        $platform = $data['platform'];

        $plugin_root = plugin_dir_path(__FILE__);
        $plugin_root = trailingslashit(dirname(dirname($plugin_root)));
        $file = $plugin_root . 'assets/json/' . $handle . '-' . $version . '.json';

        if (!file_exists($file) || !is_readable($file)) {
            return new WP_Error('file_not_found', 'File not found', array('status' => 400));
        } else {
            $json_data = file_get_contents($file);
            $data = json_decode($json_data, true);
            if ($data === null) {
                return new WP_Error('json_decoding_failed', 'JSON decoding failed', array('status' => 400));
            }
            if (count($data) === 0) {
                return new WP_Error('no_data', 'No data found in the file', array('status' => 400));
            }
            if (!array_key_exists("handle", $data[0]) && $data[0]['handle'] !== $handle) {
                return new WP_Error('invalid_data', 'Invalid blockbite file', array('status' => 400));
            } else {
                // query all records first
                $db = new DbController();
                $db->deleteAllRecordsByQuery([
                    'handle' => $handle,
                    'platform' => $platform,
                    'version' => $version
                ]);

                $db->insertRecords($data);
            }
        }
    }

    // Callback to get the current Swiper setting
    public static function get_swiper_setting()
    {
        $isSwiperEnabled = get_option('blockbite_load_swiper', true);
        return rest_ensure_response(array('isSwiperEnabled' => (bool) $isSwiperEnabled));
    }

    // Callback to update the Swiper setting
    public static function update_swiper_setting($request)
    {
        $isSwiperEnabled = $request->get_param('isSwiperEnabled');

        if (get_option('blockbite_load_swiper') === false) {
            add_option('blockbite_load_swiper', $isSwiperEnabled);
        } else {
            update_option('blockbite_load_swiper', $isSwiperEnabled);
        }

        return rest_ensure_response(array('success' => true, 'isSwiperEnabled' => (bool) $isSwiperEnabled));
    }

    // Functions for getting/setting openai key
    public static function get_openai_key()
    {
        $encrypted_key = get_option('openai_api_key', '');

        try {
            $key = $encrypted_key ? self::$encryption->decrypt($encrypted_key) : '';

            if ($key === false) {
                error_log('Decryption failed: Invalid encrypted key or decryption error.');
                return new WP_Error('decryption_failed', 'Failed to decrypt the OpenAI API key.', array('status' => 500));
            }

            return rest_ensure_response(array('key' => $key));
        } catch (Exception $e) {
            error_log('Exception encountered: ' . $e->getMessage());
            return new WP_Error('exception_occurred', 'An error occurred while retrieving the OpenAI API key.', array('status' => 500));
        }
    }

    public static function set_openai_key($request)
    {
        try {
            $key = $request->get_param('key');
            $encrypted_key = self::$encryption->encrypt($key);

            if ($encrypted_key === false) {
                error_log('Encryption failed: Invalid key or encryption error.');
                return new WP_Error('encryption_failed', 'Failed to encrypt the OpenAI API key. Ensure LOGGED_IN_KEY and LOGGED_IN_SALT variables are set.', array('status' => 500));
            }

            if (get_option('openai_api_key') === false) {
                add_option('openai_api_key', $encrypted_key);
            } else {
                update_option('openai_api_key', $encrypted_key);
            }

            return rest_ensure_response(array('success' => true));
        } catch (Exception $e) {
            error_log('Exception encountered: ' . $e->getMessage());
            return new WP_Error('exception_occurred', 'An error occurred while setting the OpenAI API key.', array('status' => 500));
        }
    }
}
