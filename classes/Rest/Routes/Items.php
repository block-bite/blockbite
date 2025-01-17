<?php

namespace Blockbite\Blockbite\Rest\Routes;

use Blockbite\Blockbite\Plugin;
use Blockbite\Blockbite\Controllers\Items as ItemsController;
use Blockbite\Blockbite\Rest\Api;


class Items extends Api
{


    protected $namespace  = 'blockbite/v1';

    public function Register()
    {


        $itemsController = new ItemsController($this->plugin);


        register_rest_route($this->namespace, '/items/get', [
            [
                'methods' => 'GET',
                'callback' => [$itemsController, 'get_items'],
                'permission_callback' => [$itemsController, 'authorize'],
                'args' => [
                    'handle' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                ]
            ]
        ]);



        register_rest_route($this->namespace, '/items/get-item', [
            [
                'methods' => 'GET',
                'callback' => [$itemsController, 'get_item'],
                'permission_callback' => [$itemsController, 'authorize'],
                'args' => [
                    'handle' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                ]
            ]
        ]);


        register_rest_route($this->namespace, '/items/upsert', [
            [
                'methods' => 'POST',
                'callback' => [$itemsController, 'upsert_item'],
                'permission_callback' => [$itemsController, 'authorize'],
                'args' => [
                    'id' => [
                        'required' => false,
                        'type' => 'number',
                    ],
                    'is_default' => [
                        'required' => true,
                        'type' => 'boolean',
                    ],
                    'platform' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                    'handle' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                    'blockname' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                    'slug' => [
                        'required' => false,
                        'type' => 'string',
                    ],
                    'content' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                    "data" => [
                        'required' => false,
                        'type' => 'json',
                    ],
                ]
            ],
        ]);

        register_rest_route($this->namespace, '/items/upsert-handle', [
            [
                'methods' => 'POST',
                'callback' => [$itemsController, 'upsert_item_handle'],
                'permission_callback' => [$itemsController, 'authorize'],
                'args' => [
                    'id' => [
                        'required' => false,
                        'type' => 'number',
                    ],
                    'is_default' => [
                        'required' => false,
                        'type' => 'boolean',
                    ],
                    'platform' => [
                        'required' => false,
                        'type' => 'string',
                    ],
                    'handle' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                    'blockname' => [
                        'required' => false,
                        'type' => 'string',
                    ],
                    'slug' => [
                        'required' => false,
                        'type' => 'string',
                    ],
                    'content' => [
                        'required' => false,
                        'type' => 'string',
                    ],
                    "data" => [
                        'required' => false,
                        'type' => 'json',
                    ],
                ]
            ],
        ]);





        register_rest_route($this->namespace, '/items/delete', [
            [
                'methods' => 'POST',
                'callback' => [$itemsController, 'delete_item'],
                'permission_callback' => [$itemsController, 'authorize'],
                'args' => [
                    'handle' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                    'id' => [
                        'required' => true,
                        'type' => 'number',
                    ],
                ]
            ],

        ]);


        register_rest_route($this->namespace, '/items/toggle-default', [
            [
                'methods' => 'POST',
                'callback' => [$itemsController, 'toggle_default_item'],
                'permission_callback' => [$itemsController, 'authorize'],
                'args' => [
                    'handle' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                    'is_default' => [
                        'required' => true,
                        'type' => 'boolean',
                    ],
                    'blockname' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                    'id' => [
                        'required' => true,
                        'type' => 'number',
                    ],
                ]
            ],

        ]);
    }
}
