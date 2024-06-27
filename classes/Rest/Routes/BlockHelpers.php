<?php

namespace Blockbite\Blockbite\Rest\Routes;

use Blockbite\Blockbite\Plugin;
use Blockbite\Blockbite\Controllers\BlockHelperIcons as BlockIconsController;
use Blockbite\Blockbite\Controllers\BlockHelperLinks as BlockLinksController;
use Blockbite\Blockbite\Rest\Api;

class BlockHelpers extends Api
{


    protected $namespace  = 'blockbite/v1';

    public function Register()
    {


        $blockIcons = new BlockIconsController($this->plugin);
        $blockLinks = new BlockLinksController($this->plugin);


        // get icons
        register_rest_route($this->namespace, 'block-helpers/get-icons', array(
            'methods' => 'GET',
            'callback' => [$blockIcons, 'get_icons'],
            'permission_callback' => [$blockIcons, 'authorize'],
        ));
        // regster rest route for icon filename like icon.svg
        register_rest_route($this->namespace, 'block-helpers/pick-icon/(?P<icon>\S+)', array(
            'methods' => 'GET',
            'callback' => [$blockIcons, 'pick_icon'],
            'permission_callback' => [$blockIcons, 'authorize']
        ));
        // pick a link
        register_rest_route($this->namespace, 'block-helpers/get-links/(?P<keyword>\S+)', array(
            'methods' => 'GET',
            'callback' => [$blockLinks, 'pick_link'],
            'permission_callback' => [$blockLinks, 'authorize'],
        ));
    }
}
