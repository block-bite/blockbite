<?php

namespace Blockbite\Blockbite\Rest\Routes;

use Blockbite\Blockbite\Plugin;
use Blockbite\Blockbite\Controllers\Settings as SettingsController;
use Blockbite\Blockbite\Rest\Api;

class Settings extends Api
{


    protected $namespace  = 'blockbite/v1';

    public function Register()
    {


        $settingsController = new SettingsController($this->plugin);



        register_rest_route($this->namespace, '/settings', array(
            array(
                'methods' => 'GET',
                'callback' => [$settingsController, 'get_option_settings'],
                'permission_callback' => [$settingsController, 'authorize'],
            ),

        ));


        register_rest_route($this->namespace, '/settings/toggle', array(
            array(
                'methods' => 'POST',
                'callback' => [$settingsController, 'update_option_settings_toggle'],
                'permission_callback' => [$settingsController, 'authorize'],
                'args' => array(
                    'option' => array(
                        'required' => true,
                        'validate_callback' => function ($param) {
                            return is_string($param);
                        }
                    ),
                    'enabled' => array(
                        'required' => true,
                        'validate_callback' => function ($param) {
                            return is_bool($param);
                        }
                    ),
                ),
            )

        ));


        register_rest_route($this->namespace, '/settings/textfield', array(
            array(
                'methods' => 'POST',
                'callback' => [$settingsController, 'update_option_settings_textfield'],
                'permission_callback' => [$settingsController, 'authorize'],
                'args' => array(
                    'option' => array(
                        'required' => true,
                        'validate_callback' => function ($param) {
                            return is_string($param);
                        }
                    ),
                    'textfield' => array(
                        'required' => true,
                        'validate_callback' => function ($param) {
                            return is_string($param);
                        }
                    ),
                ),
            )

        ));



        register_rest_route($this->namespace, '/settings/tokens', array(
            array(
                'methods' => 'GET',
                'callback' => [$settingsController, 'get_tokens'],
                'permission_callback' => [$settingsController, 'authorize'],
            ),
            array(
                'methods' => 'POST',
                'callback' => [$settingsController, 'set_token'],
                'permission_callback' => [$settingsController, 'authorize'],
                'args' => array(
                    'key' => array(
                        'required' => true,
                        'validate_callback' => function ($param) {
                            return is_string($param);
                        }
                    ),
                    'type' => array(
                        'required' => true,
                        'validate_callback' => function ($param) {
                            return is_string($param);
                        }
                    ),
                ),
            )
        ));
    }
}
