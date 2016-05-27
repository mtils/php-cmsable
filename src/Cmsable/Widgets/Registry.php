<?php


namespace Cmsable\Widgets;

use OutOfBoundsException;
use Cmsable\Widgets\Contracts\Registry as RegistryContract;


class Registry implements RegistryContract
{

    /**
     * @var array
     **/
    protected $classes = [];

    /**
     * @var array
     **/
    protected $instances = [];

    /**
     * @var callable
     **/
    protected $creator;

    public function __construct()
    {
        $this->creator = function($class) { return new $class; };
    }

    /**
     * {@inheritdoc}
     *
     * @param string $typeId
     * @return \Cmsable\Widgets\Contracts\Widget
     **/
    public function get($typeId)
    {
        if (isset($this->instances[$typeId])) {
            return $this->instances[$typeId];
        }

        $this->instances[$typeId] = $this->createWidget($this->getClass($typeId));

        return $this->instances[$typeId];
    }

    /**
     * {@inheritdoc}
     *
     * @param strin $typeId
     * @return string
     **/
    public function getClass($typeId)
    {

        if (isset($this->classes[$typeId])) {
            return $this->classes[$typeId];
        }

        throw new OutOfBoundsException("No widget class assigned for '$typeId'");

    }

    /**
     * {@inheritdoc}
     *
     * @param string $typeId
     * @param string $class
     * @return self
     **/
    public function set($typeId, $class)
    {
        $this->classes[$typeId] = $class;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $typeId
     * @retrun self
     **/
    public function remove($typeId)
    {
        $this->getClass($typeId); // trigger OutOfBoundsException
        unset($this->classes[$typeId]);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $pageTypeId (optional)
     * @param string $areaName (optional)
     * @param string $category (optional)
     * @return array
     **/
    public function all()
    {

        $widgets = [];

        foreach ($this->classes as $typeId=>$class) {
            $widgets[] = $this->get($typeId);
        }

        return $widgets;
    }

    /**
     * {@inheritdoc}
     *
     * @param callable $creator
     * @return self
     **/
    public function createWidgetsWith(callable $creator)
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * Create the widget with the assigned creator
     *
     * @param string $class
     * @return \Cmsable\Widgets\Contracts\Widget
     **/
    protected function createWidget($class)
    {
        return call_user_func($this->creator, $class);
    }
}