<?php

namespace Blockbite\Blockbite\Rest\Routes;

use Blockbite\Blockbite\Plugin;
use Blockbite\Blockbite\Controllers\EditorStyles as EditorStylesController;
use Blockbite\Blockbite\Rest\Api;

class EditorStyles extends Api
{


    protected $namespace  = 'blockbite/v1';

    public function Register()
    {


        $editorStylesController = new EditorStylesController($this->plugin);



        // save the styles
        register_rest_route($this->namespace, '/editor-styles', [
            [
                'methods' => 'GET',
                'callback' => [$editorStylesController, 'get_styles'],
                'permission_callback' => [$editorStylesController, 'authorize']
            ],
            [
                'methods' => 'POST',
                'callback' => [$editorStylesController, 'update_styles'],
                'permission_callback' => [$editorStylesController, 'authorize'],
                'args' => [
                    'blockbite_css' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                        'type' => 'string',
                    ],
                    'blockbite_tailwind' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                        'type' => 'string',
                    ],
                ]
            ],

        ]);


        // save the styles
        register_rest_route($this->namespace, '/editor-styles/safelist', [
            [
                'methods' => 'POST',
                'callback' => [$editorStylesController, 'update_safelist'],
                'permission_callback' => [$editorStylesController, 'authorize'],
                'args' => [
                    'list' => [
                        'required' => true
                    ]
                ]
            ],
        ]);


        // save the references
        register_rest_route($this->namespace, '/editor-styles/references', [
            [
                'methods' => 'POST',
                'callback' => [$editorStylesController, 'update_references'],
                'permission_callback' => [$editorStylesController, 'authorize'],
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
