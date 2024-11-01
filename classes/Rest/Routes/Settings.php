<?php

namespace Blockbite\Blockbite\Rest\Routes;

use Blockbite\Blockbite\Plugin;
use Blockbite\Blockbite\Controllers\Settings as SettingsController;
use Blockbite\Blockbite\Controllers\MigrateTemplates as MigrateTemplatesController;
use Blockbite\Blockbite\Rest\Api;

class Settings extends Api
{


    protected $namespace  = 'blockbite/v1';

    public function Register()
    {


        $settingsController = new SettingsController($this->plugin);
        $migrateTemplatesController = new MigrateTemplatesController($this->plugin);


        register_rest_route($this->namespace, '/settings/site/sync-items', [
            [
                'methods' => 'GET',
                'callback' => [$settingsController, 'export_items'],
                'permission_callback' => [$settingsController, 'authorize'],
                'args' => [
                    'handle' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                ]
            ],
            [
                'methods' => 'POST',
                'callback' => [$settingsController, 'import_items'],
                'permission_callback' => [$settingsController, 'authorize'],
                'args' => [
                    'handle' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                ]
            ],

        ]);
        register_rest_route($this->namespace, '/settings/blockbite/sync-items', [
            [
                'methods' => 'POST',
                'callback' => [$settingsController, 'sync_blockbite_items'],
                'permission_callback' => [$settingsController, 'authorize'],
                'args' => [
                    'handle' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                    'version' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                    'platform' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                ]
            ],
        ]);

        register_rest_route($this->namespace, '/settings/migrate-templates', [
            [
                'methods' => 'POST',
                'callback' => [$migrateTemplatesController, 'migrate'],
                'permission_callback' => [$migrateTemplatesController, 'authorize'],
                'args' => [
                    'find' => [
                        'required' => false,
                        'type' => 'string',
                    ],
                    'replace' => [
                        'required' => false,
                        'type' => 'string',
                    ],
                ]
            ],
        ]);

        register_rest_route($this->namespace, '/settings/swiper', array(
            array(
                'methods' => 'GET',
                'callback' => [$settingsController, 'get_swiper_setting'],
                'permission_callback' => [$settingsController, 'authorize'],
            ),
            array(
                'methods' => 'POST',
                'callback' => [$settingsController, 'update_swiper_setting'],
                'permission_callback' => [$settingsController, 'authorize'],
                'args' => array(
                    'isSwiperEnabled' => array(
                        'required' => true,
                        'validate_callback' => function($param) {
                            return is_bool($param);
                        }
                    ),
                ),
            )
        ));

        register_rest_route($this->namespace, '/settings/openai_key', array(
            array(
                'methods' => 'GET',
                'callback' => [$settingsController, 'get_openai_key'],
                'permission_callback' => [$settingsController, 'authorize'],
            ),
            array(
                'methods' => 'POST',
                'callback' => [$settingsController, 'set_openai_key'],
                'permission_callback' => [$settingsController, 'authorize'],
                'args' => array(
                    'key' => array(
                        'required' => true,
                        'validate_callback' => function($param) {
                            return is_string($param);
                        }
                    ),
                ),
            )
        ));
    }
}
