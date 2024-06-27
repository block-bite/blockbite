<?php

namespace Blockbite\Blockbite\Rest\Routes;

use Blockbite\Blockbite\Plugin;
use Blockbite\Blockbite\Controllers\BlockSupport as BlockSupportController;
use Blockbite\Blockbite\Rest\Api;

class BlockSupport extends Api
{


    protected $namespace  = 'blockbite/v1';

    public function Register()
    {


        $blockSupportController = new BlockSupportController($this->plugin);


        register_rest_route($this->namespace, '/block-support', [
            [
                'methods' => 'GET',
                'callback' => [$blockSupportController, 'get_block_support'],
                'permission_callback' => [$blockSupportController, 'authorize']
            ],

        ]);
        register_rest_route($this->namespace, '/block-support/allowed', [
            [
                'methods' => 'POST',
                'callback' => [$blockSupportController, 'update_block_support'],
                'permission_callback' => [$blockSupportController, 'authorize'],
                'args' => [
                    'blocks' => [
                        'required' => true,
                        'type' => 'array',
                    ]
                ]
            ],
        ]);
        register_rest_route($this->namespace, '/block-support/dynamic', [
            [
                'methods' => 'POST',
                'callback' => [$blockSupportController, 'update_dynamic_block_support'],
                'permission_callback' => [$blockSupportController, 'authorize'],
                'args' => [
                    'blockname' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                    'allowed' => [
                        'required' => true,
                        'type' => 'boolean',
                    ]
                ]
            ]
        ]);
    }
}
