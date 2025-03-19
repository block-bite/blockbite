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
                    'bites' => [
                        'required' => false,
                        'type' => 'json',
                    ],
                    'blockstyles' => [
                        'required' => false,
                        'type' => 'json',
                    ],
                    'utils' => [
                        'required' => false,
                        'type' => 'json',
                    ],

                ]
            ],
        ]);

        register_rest_route($this->namespace, '/bites/blocks', [
            [
                'methods' => 'GET',
                'callback' => [$bitesController, 'get_bite_blocks'],
                'permission_callback' => [$bitesController, 'authorize'],
                'args' => []
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

        register_rest_route($this->namespace, '/bites/bites', [
            [
                'methods' => 'GET',
                'callback' => [$bitesController, 'get_bites'],
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


        register_rest_route($this->namespace, '/bites/preview-image', [

            [
                'methods' => 'POST',
                'callback' => [$bitesController, 'update_preview_image'],
                'permission_callback' => [$bitesController, 'authorize'],
                'args' => []
            ],
        ]);
    }
}
