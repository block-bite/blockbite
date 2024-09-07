<?php

namespace Blockbite\Blockbite\Rest\Routes;

use Blockbite\Blockbite\Plugin;
use Blockbite\Blockbite\Controllers\Bites as BitesController;
use Blockbite\Blockbite\Rest\Api;

class Bites extends Api
{


    protected $namespace  = 'blockbite/v1';

    public function Register()
    {


        $bitesController = new BitesController($this->plugin);




        register_rest_route($this->namespace, '/bites/purge', [

            [
                'methods' => 'POST',
                'callback' => [$bitesController, 'update_bites'],
                'permission_callback' => [$bitesController, 'authorize'],
                'args' => [
                    'utils' => [
                        'required' => true,
                    ],
                    'post_id' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                        'type' => 'string',
                    ],
                    'bites' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                    'blockstyles' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                ]
            ],
        ]);

        register_rest_route($this->namespace, '/bites/blocks/(?P<post_id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [$bitesController, 'get_bite_blocks'],
                'permission_callback' => [$bitesController, 'authorize'],
                'args' => [
                    'post_id' => [
                        'required' => true,
                        'sanitize_callback' => 'absint',
                        'type' => 'integer',
                    ],
                ]
            ],
        ]);


        register_rest_route($this->namespace, '/bites/library', [
            [
                'methods' => 'GET',
                'callback' => [$bitesController, 'get_bite_library'],
                'permission_callback' => [$bitesController, 'authorize'],
                'args' => []
            ],
        ]);


        register_rest_route($this->namespace, '/bites/utils', [
            [
                'methods' => 'GET',
                'callback' => [$bitesController, 'get_bite_utils'],
                'permission_callback' => [$bitesController, 'authorize'],
                'args' => []
            ],
        ]);
    }
}
