<?php

namespace Cmsable\Widgets\Contracts;

interface WidgetItemRepository
{

    /**
     * Find WidgetItem with id $id
     *
     * @param int $id
     * @return \Cmsable\Widgets\Contracts\WidgetItem
     **/
    public function find($id);

    /**
     * Instanciate WidgetItem with typeId and $data
     *
     * @param string $typeId
     * @param array $data (optional)
     * @return \Cmsable\Widgets\Contracts\WidgetItem
     **/
    public function make($typeId, array $data=[]);

    /**
     * Save the WidgetItem
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $widget
     * @param array $data
     * @return self
     **/
    public function update(WidgetItem $widget, array $data);

}
