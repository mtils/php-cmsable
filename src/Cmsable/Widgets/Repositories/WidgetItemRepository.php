<?php


namespace Cmsable\Widgets\Repositories;

use OutOfBoundsException;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    /**
     * The name of type id column
     *
     * @var string
     **/
    public $dataKey = 'data';

    /**
     * The name of id column
     *
     * @var string
     **/
    public $idKey = 'id';

    /**
     * @var \Illuminate\Database\Eloquent\Model
     **/
    protected $model;

    /**
     * @var \Cmsable\Widgets\Contracts\Registry
     **/
    protected $registry;

    protected $cleanFromData = [
        'id', 'typeId', 'frame'
    ];

    /**
     * _C_msable_W_idgets_R_epositories_W_idget_I_tem_R_epository = cwrwir
     * 
     * @var string
     **/
    protected $idPrefix = 'cwrwir';

    /**
     * @var string
     **/
    protected $idSeparator = '--';

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
        if ($this->isGeneratedId($id)) {
            throw new ModelNotFoundException;
        }

        return $this->model->findOrFail($id);
    }

     /**
     * {@inheritdoc}
     *
     * @param mixed $itemId
     * @return \Cmsable\Widgets\Contracts\WidgetItem
     **/
    public function findOrMake($itemId)
    {
        if (!$this->isGeneratedId($itemId)) {
            return $this->find($itemId);
        }
        return $this->make($this->extractTypeId($itemId));
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

        if (isset($data[$this->idKey])) {
            $item->setAttribute($this->idKey, $data[$this->idKey]);
            return $item;
        }

        $item->setAttribute($this->idKey, $this->generateId($typeId));

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

        $data = $this->removeAndCleanData($data);

        if (!$this->exists($widget)) {
            unset($widget->id);
        }

        $widget->data = $data;
        $widget->save();

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $item
     * @return self
     **/
    public function delete(WidgetItem $item)
    {
        $item->delete();
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $item
     * @return bool
     **/
    public function exists(WidgetItem $item)
    {
        $id = $item->getId();
        if (!$id || $this->isGeneratedId($id)) {
            return false;
        }
        return true;
    }

    protected function generateId($typeId)
    {
        return implode($this->idSeparator, [$this->idPrefix, $typeId, uniqid()]);
    }

    protected function extractTypeId($generatedId)
    {
        $parts = explode($this->idSeparator, $generatedId);
        if (count($parts) != 3) {
            throw new InvalidArgumentException("The id '$generatedId' is not valid");
        }

        $typeId = $parts[1];

        try {
            return $this->registry->get($typeId)->getTypeId();
        } catch (OutOfBoundsException $e) {
            throw new OutOfBoundsException("Invalid typeId $typeId in generated id. Widget not found");
        }
    }

    protected function isGeneratedId($id)
    {
        return starts_with($id, $this->idPrefix.$this->idSeparator);
    }

    protected function removeAndCleanData($data)
    {
        $filtered = [];
        foreach ($data as $key=>$value) {
            if (in_array($key, $this->cleanFromData)) {
                continue;
            }
            $filtered[$key] = $value;
        }
        return $filtered;
    }

}