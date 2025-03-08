<?php

namespace Blockbite\Blockbite\Rest\Routes;

use Blockbite\Blockbite\Plugin;
use Blockbite\Blockbite\Controllers\DynamicContent as DynamicContentController;
use Blockbite\Blockbite\Rest\Api;

class DynamicContent extends Api
{


    protected $namespace  = 'blockbite/v1';

    public function Register()
    {


        $dynamicContentController = new DynamicContentController($this->plugin);




        register_rest_route($this->namespace, '/dynamic-content', [

            [
                'methods' => 'POST',
                'callback' => [$dynamicContentController, 'update_dynamic_content'],
                'permission_callback' => [$dynamicContentController, 'authorize'],
                'args' => [
                    'id' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                        'type' => 'string',
                    ],
                    'data' => [
                        'required' => false,
                        'type' => 'json',
                    ],
                    'title' => [
                        'required' => false,
                        'type' => 'string',
                    ],
                    'summary' => [
                        'required' => false,
                        'type' => 'string',
                    ],
                    'css' => [
                        'required' => false,
                        'type' => 'string',
                    ],
                ]
            ],
        ]);

        register_rest_route($this->namespace, '/dynamic-content/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [$dynamicContentController, 'get_dynamic_content'],
                'permission_callback' => [$dynamicContentController, 'authorize'],
                'args' => [
                    'id' => [
                        'required' => true,
                        'sanitize_callback' => 'absint',
                        'type' => 'integer',
                    ],
                ]
            ],
        ]);


        // get all dynamic content items
        register_rest_route($this->namespace, '/dynamic-content', [
            [
                'methods' => 'GET',
                'callback' => [$dynamicContentController, 'get_dynamic_content_items'],
                'permission_callback' => [$dynamicContentController, 'authorize'],
            ],
        ]);


        //render_dynamic_content 
        register_rest_route(
            $this->namespace,
            '/dynamic-content/render/',
            [
                [
                    'methods' => 'GET',
                    'callback' => [$dynamicContentController, 'render_dynamic_content_rest'],
                    'permission_callback' => [$dynamicContentController, 'authorize'],
                    'args' => [
                        'designId' => [
                            'required' => false,
                            'type' => 'string',
                        ],
                        'renderTag' => [
                            'required' => false,
                            'type' => 'string',
                        ],
                        'contentId' => [
                            'required' => false,
                            'type' => 'string',
                        ],
                    ]
                ],
            ]
        );


        // save design dynamic content
        register_rest_route($this->namespace, '/dynamic-content/design', [
            [
                'methods' => 'POST',
                'callback' => [$dynamicContentController, 'update_design_dynamic_blocks'],
                'permission_callback' => [$dynamicContentController, 'authorize'],
                'args' => [
                    'blocks' => [
                        'required' => false,
                        'type' => 'string',
                    ],
                ]
            ],
        ]);

        register_rest_route($this->namespace, '/dynamic-design/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [$dynamicContentController, 'get_dynamic_designs_by_parent'],
                'permission_callback' => [$dynamicContentController, 'authorize'],
                'args' => [
                    'id' => [
                        'required' => true,
                        'sanitize_callback' => 'absint',
                        'type' => 'integer',
                    ],
                ]
            ],
        ]);
    }
}
