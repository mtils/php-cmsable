<?php

namespace Cmsable\Widgets\Contracts;

interface Renderer
{

    /**
     * Render the widgetItem $item on the page
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $item
     * @return string
     **/
    public function render(WidgetItem $item);

    /**
     * Render a preview of the widgetItem $item on the admin summary page
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $item
     * @return string
     **/
    public function renderPreview(WidgetItem $item);

    /**
     * Render a form to edit widgetItem $item on the edit page
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $item
     * @return string
     **/
    public function renderForm(WidgetItem $item, $params=[]);

}
