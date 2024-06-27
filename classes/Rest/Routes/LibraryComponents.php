<?php

namespace Blockbite\Blockbite\Rest\Routes;

use Blockbite\Blockbite\Plugin;
use Blockbite\Blockbite\Controllers\LibraryComponents as LibraryComponentsController;
use Blockbite\Blockbite\Rest\Api;


class LibraryComponents extends Api
{


    protected $namespace  = 'blockbite/v1';

    public function Register()
    {


        $componentController = new LibraryComponentsController($this->plugin);


        register_rest_route($this->namespace, '/library-components', [
            [
                'methods' => 'POST',
                'callback' => [$componentController, 'create_component'],
                'permission_callback' => [$componentController, 'authorize'],
                'args' => [
                    'css' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                    'slug' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                    'summary' => [
                        'required' => false,
                        'type' => 'string',
                    ],
                    'tailwind' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                    'title' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                ]
            ],
            [
                'methods' => 'GET',
                'callback' => [$componentController, 'get_components'],
                'permission_callback' => [$componentController, 'authorize']
            ]
        ]);
    }
}
