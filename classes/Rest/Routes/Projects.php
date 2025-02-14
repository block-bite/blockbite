<?php

namespace Blockbite\Blockbite\Rest\Routes;

use Blockbite\Blockbite\Plugin;
use Blockbite\Blockbite\Controllers\Projects as ProjectsController;
use Blockbite\Blockbite\Rest\Api;

class Projects extends Api
{


    protected $namespace  = 'blockbite/v1';

    public function Register()
    {


        $projectsController = new ProjectsController($this->plugin);


        register_rest_route($this->namespace, '/projects/export', [
            [
                'methods' => 'POST',
                'callback' => [$projectsController, 'export_project'],
                'permission_callback' => [$projectsController, 'authorize'],
                'args' => []
            ],
            [
                'methods' => 'GET',
                'callback' => [$projectsController, 'get_project'],
                'permission_callback' => [$projectsController, 'authorize'],
                'args' => []
            ]
        ]);

        register_rest_route($this->namespace, '/projects/library/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [$projectsController, 'get_project'],
                'permission_callback' => [$projectsController, 'authorize'],
                'args' => [
                    'id' => [
                        'required' => true,
                        'sanitize_callback' => 'absint',
                        'type' => 'integer',
                    ],
                ]
            ]
        ]);
    }
}
