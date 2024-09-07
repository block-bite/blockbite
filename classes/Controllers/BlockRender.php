<?php

namespace Blockbite\Blockbite\Controllers;

class BlockRender extends Controller
{
    public static function carousel_dynamic($block_content, $block)
    {

        if (isset($block['blockName']) && 'core/post-template' === $block['blockName'] && isset($block['attrs']['biteParentName'])) {
            if ($block['attrs']['biteParentName'] === 'blockbite/dynamic-content-carousel') {
                $block_content = str_replace('<li class="', '<swiper-slide class="', $block_content);
                $block_content = str_replace('</li>', '</swiper-slide>', $block_content);
                $pattern = '/<ul\s+class="[^"]*">/';
                $block_content = preg_replace($pattern, '', $block_content);
                $block_content = str_replace('</ul>', '', $block_content);
            }
        }
        return $block_content;
    }
}
