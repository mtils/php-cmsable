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
     * Instanciate WidgetItem with typeId and $data. Give it a artificial
     * id unless it gets some real from the database to allow multipage/js
     * handling of the items.
     * You have to recognize your id in exists() and remove it when you save
     * it
     *
     * @param string $typeId
     * @param array $data (optional)
     * @return \Cmsable\Widgets\Contracts\WidgetItem
     **/
    public function make($typeId, array $data=[]);

    /**
     * This method finds a widget item or creates a new one. You have to encode the widget type id
     * in your generated id to allow this.
     *
     * @param mixed $itemId
     * @return \Cmsable\Widgets\Contracts\WidgetItem
     **/
    public function findOrMake($itemId);

    /**
     * Save the WidgetItem
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $widget
     * @param array $data
     * @return self
     **/
    public function update(WidgetItem $widget, array $data);

    /**
     * Delete the passed item
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $item
     * @return self
     **/
    public function delete(WidgetItem $item);

    /**
     * Return true if an item exists (in database)
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $widget
     * @return bool
     **/
    public function exists(WidgetItem $widget);

}
