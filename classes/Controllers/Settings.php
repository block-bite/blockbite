<?php

namespace Blockbite\Blockbite\Controllers;

use WP_REST_Response;
use WP_Error;

use Blockbite\Blockbite\Controllers\Database as DbController;

class Settings extends Controller
{

    protected $handles = [];
    private static $encryption;

    public function __construct()
    {
        self::$encryption = new \Blockbite\Blockbite\Controllers\DataEncryption();
    }


    /*
       Handle blockbite load Options
    */
    public static function get_blockbite_settings($rest = true)
    {
        $isSwiperEnabled = get_option('blockbite_load_swiper', true);
        $isGsapEnabled = get_option('blockbite_load_gsap', false);
        $isLottieEnabled = get_option('blockbite_load_lottie', false);
        $isBaseStyleEnabled = get_option('blockbite_tw_base', false);
        $isStrategy = get_option('blockbite_tw_strategy', 'b_');
        $cssName = get_option('blockbite_css_name', 'style');


        return rest_ensure_response(array(
            "toggle_options" => [
                'blockbite_load_swiper' => $isSwiperEnabled,
                'blockbite_load_gsap' => $isGsapEnabled,
                'blockbite_load_lottie' => $isLottieEnabled,
                'blockbite_tw_base' => $isBaseStyleEnabled,

            ],
            "string_options" => [
                'blockbite_tw_strategy' => $isStrategy,
                'blockbite_css_name' => $cssName
            ],
            "tokens" => self::get_tokens()
        ));
    }

    /*
       Handle blockbite load Options
    */

    public static function update_option_settings_toggle($request)
    {
        $isOptionEnabled = $request->get_param('enabled');
        $option = $request->get_param('option');

        if (get_option($option) === false) {
            add_option($option, $isOptionEnabled);
        } else {
            update_option($option, $isOptionEnabled);
        }

        return rest_ensure_response(array('success' => true));
    }




    public static function update_option_settings_textfield($request)
    {

        $data = $request->get_param('data');
        // loop through the data and update the options
        foreach ($data as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            } else {
                update_option($key, $value);
            }
        }

        return rest_ensure_response(array('success' => true));
    }




    public static function get_tokens()
    {
        $encrypted_keys = [
            'blockbite_project_token' => get_option('blockbite_project_token', ''),
            'blockbite_account_token' => get_option('blockbite_account_token', ''),
        ];

        $decrypted_keys = [];

        try {
            foreach ($encrypted_keys as $key_name => $encrypted_key) {
                $decrypted_key = $encrypted_key ? self::$encryption->decrypt($encrypted_key) : '';

                if ($decrypted_key === false) {
                    error_log("Decryption failed for $key_name: Invalid encrypted key or decryption error.");
                    return new WP_Error('decryption_failed', "Failed to decrypt $key_name.", ['status' => 500]);
                }

                $decrypted_keys[$key_name] = $decrypted_key;
            }

            return $decrypted_keys;
        } catch (Exception $e) {
            error_log('Exception encountered: ' . $e->getMessage());
            return new WP_Error('exception_occurred', 'An error occurred while retrieving the tokens.', ['status' => 500]);
        }
    }


    public static function set_token($request)
    {
        try {
            $key = $request->get_param('key');
            $type = $request->get_param('type');
            $encrypted_key = self::$encryption->encrypt($key);


            // Todo check against the platform BLOCKBITE_PLATFORM_URL

            if ($encrypted_key === false) {
                error_log('Encryption failed: Invalid key or encryption error.');
                return new WP_Error('encryption_failed', 'Failed to encrypt the Token key' . $type . ' Ensure LOGGED_IN_KEY and LOGGED_IN_SALT variables are set.', array('status' => 500));
            }

            if (get_option($type) === false) {
                add_option($type, $encrypted_key);
            } else {
                update_option($type, $encrypted_key);
            }

            return rest_ensure_response(array('success' => true));
        } catch (Exception $e) {
            error_log('Exception encountered: ' . $e->getMessage());
            return new WP_Error('exception_occurred', 'An error occurred while setting the key:' . $type, array('status' => 500));
        }
    }
}
