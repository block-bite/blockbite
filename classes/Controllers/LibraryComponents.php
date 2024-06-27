<?php

namespace Blockbite\Blockbite\Controllers;
// use WP_Error
use WP_Error;

use Blockbite\Blockbite\Controllers\Database as DbController;

class LibraryComponents extends Controller
{


    public static function create_component($request)
    {
        $data = $request->get_params();
        $where = ['handle' => 'component', 'slug' => $data['slug']];

        return DbController::updateOrCreateRecord($data, $where);
    }

    public static function get_components()
    {
        $result = DbController::getAllRecordsByHandle('component');
        return $result;
    }

    public static function get_components_css()
    {
        $result = DbController::getAllRecordsByHandle('component');
        $css = '';
        foreach ((array) $result as $component) {
            $css .= $component->css;
        }
        return $css;
    }
}
