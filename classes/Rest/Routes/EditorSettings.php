<?php

namespace Blockbite\Blockbite\Rest\Routes;

use Blockbite\Blockbite\Plugin;
use Blockbite\Blockbite\Controllers\EditorSettings as EditorSettingsController;
use Blockbite\Blockbite\Rest\Api;

class EditorSettings extends Api
{


    protected $namespace  = 'blockbite/v1';

    public function Register()
    {


        $editorSettingsController = new EditorSettingsController($this->plugin);



        register_rest_route($this->namespace, '/editor-settings', [
            [
                'methods' => 'GET',
                'callback' => [$editorSettingsController, 'get_settings'],
                'permission_callback' => [$editorSettingsController, 'authorize']
            ]
        ]);


        register_rest_route($this->namespace, '/frontend-css', [
            [
                'methods' => 'GET',
                'callback' => [$editorSettingsController, 'get_frontend_css'],
                'permission_callback' => [$editorSettingsController, 'authorize'],
                'args' => []
            ],
            [
                'methods' => 'POST',
                'callback' => [$editorSettingsController, 'update_frontend_css'],
                'permission_callback' => [$editorSettingsController, 'authorize'],
                'args' => [
                    'data' => [
                        'required' => false,
                        'type' => 'json',
                    ],
                    'content' => [
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                        'type' => 'string',
                    ]
                ]
            ],
        ]);


        register_rest_route($this->namespace, '/editor-styles/safelist', [
            [
                'methods' => 'POST',
                'callback' => [$editorSettingsController, 'update_safelist'],
                'permission_callback' => [$editorSettingsController, 'authorize'],
                'args' => [
                    'list' => [
                        'required' => true
                    ]
                ]
            ],
        ]);

        register_rest_route($this->namespace, '/editor-settings/native-global-styles', [
            [
                'methods' => 'GET',
                'callback' => [$editorSettingsController, 'get_native_global_styles'],
                'permission_callback' => [$editorSettingsController, 'authorize']
            ]
        ]);



        register_rest_route($this->namespace, '/editor-styles/references', [
            [
                'methods' => 'POST',
                'callback' => [$editorSettingsController, 'update_references'],
                'permission_callback' => [$editorSettingsController, 'authorize'],
                'args' => [
                    'references' => [
                        'required' => true,
                        // callback to sanitize the input should be here
                        'type' => 'array',
                    ],
                    'post_id' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                        'type' => 'string',
                    ],
                ]
            ],
        ]);
    }
}
