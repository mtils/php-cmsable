<?php

namespace Cmsable\Widgets\Contracts;

/**
 * The AreaRenderer renders the complete widget area. It is the central place to register
 * the renderer for your own widgets
 * It offers the same interface as Renderer to allow rendering of individual widgets
 **/
interface AreaRenderer extends Renderer
{

    /**
     * Render the area $area
     *
     * @param \Cmsable\Widgets\Contracts\Area
     * @param array $vars (optional) view variabled
     * @return string
     **/
    public function renderArea(Area $area, array $vars=[]);

    /**
     * Render the area $area to edit it
     *
     * @param \Cmsable\Widgets\Contracts\Area $area
     * @param array $vars (optional) view variabled
     * @return string
     **/
    public function renderEditArea(Area $area, array $vars=[]);

}