<?php


namespace Cmsable\Widgets\Repositories;

use Illuminate\Database\Eloquent\Model;
use Cmsable\Widgets\Contracts\WidgetItemRepository as RepositoryContract;
use Cmsable\Widgets\Contracts\WidgetItem;
use Cmsable\Widgets\Contracts\Registry as RegistryContract;

class WidgetItemRepository implements RepositoryContract
{

    /**
     * The name of type id column
     *
     * @var string
     **/
    public $typeIdKey = 'type_id';

    /** The name of type id column
     *
     * @var string
     **/
    public $dataKey = 'data';

    /**
     * @var \Illuminate\Database\Eloquent\Model
     **/
    protected $model;

    /**
     * @var \Cmsable\Widgets\Contracts\Registry
     **/
    protected $registry;

    public function __construct(Model $model, RegistryContract $registry)
    {
        $this->model = $model;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     *
     * @param int $id
     * @return \Cmsable\Widgets\Contracts\WidgetItem
     **/
    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $typeId
     * @param array $data (optional)
     * @return \Cmsable\Widgets\Contracts\WidgetItem
     **/
    public function make($typeId, array $data=[])
    {
        $defaults = $this->registry->get($typeId)->defaultData();
        $data = array_merge($defaults, $data);
        $item = $this->model->newInstance();
        $item->setAttribute($this->typeIdKey, $typeId);
        $item->setAttribute($this->dataKey, $data);
        return $item;

    }

    /**
     * {@inheritdoc}
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $widget
     * @param array $data
     * @return self
     **/
    public function update(WidgetItem $widget, array $data)
    {
    }

}