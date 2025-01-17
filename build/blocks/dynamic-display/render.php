<?php

use Blockbite\Blockbite\Controllers\DynamicContent as DynamicContentController;


// Access the 'contentId' attribute
$contentId = isset($attributes['contentId']) ? $attributes['contentId'] : '';
$renderTag = isset($attributes['renderTag']) ? $attributes['renderTag'] : 'element';
$design = isset($attributes['designId']) ? $attributes['designId'] : null;


DynamicContentController::render_dynamic_content($contentId, $design, $renderTag);
