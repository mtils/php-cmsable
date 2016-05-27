<?php


namespace Cmsable\Widgets\Contracts;

use Ems\Contracts\Graphics\LayoutItem;

/**
 * The Widgets\Registry is the central place to register your
 * widgets.
 * Register it with the class name so instances will be created
 * if they are needed
 **/
interface Registry
{

    /**
     * Get the widget instance for $typeId
     *
     * @param string $typeId
     * @return \Cmsable\Widgets\Contracts\Widget
     **/
    public function get($typeId);

    /**
     * Get the assigned widget class for $typeId
     *
     * @param strin $typeId
     * @return string
     **/
    public function getClass($typeId);

    /**
     * Set a widget $class for $typeId
     *
     * @param string $typeId
     * @param string $class
     * @return self
     **/
    public function set($typeId, $class);

    /**
     * Remove the widget for $typeId
     *
     * @param string $typeId
     * @retrun self
     **/
    public function remove($typeId);

    /**
     * Return all widgets.
     *
     * @return array
     **/
    public function all();

    /**
     * Assign a creator to create the widget instances
     * typically: function($class) {
     *     return $this->app->make($class);
     * }
     * inside a service provider
     *
     * This reduces the external dependencies of this class
     *
     * @param callable $creator
     * @return self
     **/
    public function createWidgetsWith(callable $creator);

}