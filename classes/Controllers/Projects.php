<?php

namespace Blockbite\Blockbite\Controllers;

use Blockbite\Blockbite\Controllers\Database as DbController;
use Blockbite\Blockbite\Controllers\Bites as BitesController;

class Projects extends Controller
{

    private static $encryption;

    public function __construct()
    {
        self::$encryption = new \Blockbite\Blockbite\Controllers\DataEncryption();
    }



    public static function export_project()
    {

        // get all bites by handle
        $bites_result = DbController::getAllRecordsByHandle('bites');
        $designtokens = DbController::getRecordByHandle('design-tokens');
        $code_editor_css = DbController::getRecordByHandle('blockbite-editor-css');
        $code_editor_js = DbController::getRecordByHandle('blockbite-editor-js');
        $utils = DbController::getRecordByHandle('utils');


        $bites = [];
        // loop over bites and decode
        if (is_array($bites_result) && count($bites_result) > 0) {
            foreach ($bites_result as $bite) {
                $bites[] = json_decode($bite->data);
            }
        }


        $data = array(
            'bites' => $bites_result,
            'utils' => isset($utils->data) ? json_decode($utils->data) : [],
            'designtokens' => isset($designtokens->data) ? json_decode($designtokens->data) : [],
            'code_editor_css' => isset($code_editor_css->data) ? json_decode($code_editor_css->data) : [],
            'code_editor_js' =>  isset($code_editor_js->data) ? json_decode($code_editor_js->data) : [],
        );

        $json = ['data' => $data];
        // curl -X POST https://blockbite-platform.ddev.site/wp-json/dashbite/v1/projects/data -H "Content-Type: application/json" -H "Authorization: Bearer asdf1234" -H "Origin: https://tianheyang.com" -d '{"data":{"key":"value"}}'

        // use wordpress curl 
        $encrypted_key = get_option('blockbite_project_token', '');
        try {
            $key = $encrypted_key ? self::$encryption->decrypt($encrypted_key) : '';

            if ($key === false) {
                error_log('Decryption failed: Invalid encrypted key or decryption error.');
                return new WP_Error('decryption_failed', 'Failed to decrypt the Project Token key.', array('status' => 500));
            }

            $response = wp_remote_post(
                BLOCKBITE_PLATFORM_URL . '/wp-json/dashbite/v1/projects/data',
                array(
                    'headers' => array(
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $key
                    ),
                    'body' => json_encode(['data' => $json])
                )
            );
            return rest_ensure_response(array('response' => $response));
        } catch (Exception $e) {
            error_log('Exception encountered: ' . $e->getMessage());
            return new WP_Error('exception_occurred', 'An error occurred while retrieving the Project Token key.', array('status' => 500));
        }
    }






    public static function get_project($request)
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
                $unformatted_bites = $data->bites;
                $data->bites = BitesController::parse_bite_blocks($unformatted_bites);
            }
        }

        return rest_ensure_response($data);
    }
}
