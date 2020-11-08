<?php


namespace Cmsable\Widgets\Repositories;

use Illuminate\Database\Eloquent\Builder;
use OutOfBoundsException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Cmsable\Widgets\Contracts\AreaRepository as RepositoryContract;
use Cmsable\Widgets\Contracts\WidgetItemRepository as WidgetItems;
use Cmsable\Widgets\Contracts\Registry as RegistryContract;
use Cmsable\Widgets\Contracts\Area;
use Cmsable\Widgets\Contracts\WidgetItem;
use Cmsable\Widgets\Area as AreaModel;

use function is_array;

class AreaRepository implements RepositoryContract
{

    public $areaPageTypeKey = 'page_type';

    public $pageIdKey = 'page_id';

    public $nameKey = 'name';

    /**
     * @var \Illuminate\Database\Eloquent\Model
     **/
    protected $areaModel;

    /**
     * @var \Cmsable\Widgets\Contracts\WidgetItemRepository
     **/
    protected $widgetItems;

    /**
     * @var \Cmsable\Widgets\Contracts\Registry
     **/
    protected $registry;

    public $cleanFromData = ['typeId', 'id', 'typeId', 'framed'];

    /**
     * @param \Illuminate\Database\Eloquent\Model $areaModel
     * @param \Cmsable\Widgets\Contracts\WidgetItemRepository $widgetItems
     **/
    public function __construct(Model $areaModel, WidgetItems $widgetItems, RegistryContract $registry)
    {
        $this->areaModel = $areaModel;
        $this->widgetItems = $widgetItems;
        $this->registry = $registry;
    }

    /**
     * @param array $criteria
     *
     * @return AreaModel[]
     */
    public function find(array $criteria = [])
    {
        /** @var AreaModel[] $areas */
        $areas = $this->makeAreaQuery($criteria)->get()->all();
        return $areas;
    }


    /**
     * {@inheritdoc}
     *
     * @param string $pageTypeId
     * @param int $pageId (optional)
     * @param string $name (optional)
     * @return Area
     **/
    public function areaFor($pageTypeId, $pageId=null, $name=self::CONTENT)
    {
        if (!$pageId) {
            return $this->configure($this->newArea($pageTypeId, $pageId, $name));
        }
        if ($area = $this->getAreaFromDatabase($pageTypeId, $pageId, $name)) {
            return $this->configure($area);
        }
        return $this->configure($this->newArea($pageTypeId, $pageId, $name));
    }

    /**
     * {@inheritdoc}
     *
     * @param Area $area
     * @param array $attributes
     * @return self
     **/
    public function update(Area $area, array $attributes)
    {

        if (!$area->exists) {
            $area->save();
        }

        $previousAreaItems = $this->areaItems($area);

        if (!isset($attributes['widget_config'])) {
            $area->delete();
            return $this;
        }

        $sendedItems = [];
        $idMap = [];

        foreach ($attributes['widget_config'] as $itemId=>$config) {
            $config = is_array($config) ? $config : json_decode($config, true);
            $item = $this->widgetItems->findOrMake($itemId);
            $sendedItems[] = $item;
            $config = $this->cleanData($config);
            $item->setLayout($area);
            $this->widgetItems->update($item, $config);
            // Update the ids
            $idMap[$itemId] = $item->getId();
        }

        foreach ($previousAreaItems as $previousItem) {
            if (!$this->isInItems($previousItem, $sendedItems)) {
                $this->widgetItems->delete($previousItem);
            }
        }

        if (!isset($attributes['layout'])) {
            return;
        }

        if (!$sendedLayout = json_decode($attributes['layout'], true)) {
            return $this;
        }
        $layout = [];
        foreach ($sendedLayout as $itemId) {
            $layout[] = $idMap[$itemId];
        }

        $area->layout = $layout;
        $area->save();
        return $this;

    }

    /**
     * @param Area $area
     *
     * @return Area
     */
    public function configure(Area $area)
    {
        if (!$area->exists) {
            return $area;
        }

        $items = $area->items;

        if (!$items || !count($items)) {
            return $area;
        }

        foreach ($items as $item) {
            $this->registry->get($item->getTypeId())->configure($item);
        }

        if ($area->layout) {
            return $this->addItemsByLayout($area, $area->layout, $items);
        }


        $row = 0;
        foreach ($items as $item) {
            $area->setItem($row, 0, $item);
            $row++;
        }

        return $area;
    }

    protected function areaItems(Area $area)
    {
        $items = [];
        foreach ($area as $item) {
            $items[] = $item;
        }
        return $items;
    }

    protected function isInItems(WidgetItem $item, array $items)
    {
        foreach ($items as $checkItem) {
            if ($item->getId() == $checkItem->getId()) {
                return true;
            }
        }
        return false;
    }

    protected function deleteItems($items)
    {
        foreach ($items as $item) {
            $this->widgetItems->delete($item);
        }
    }

    protected function addItemsByLayout(Area $area, $layout, $items)
    {

        $itemsById = $this->byId($items);

        foreach ($layout as $row=>$id) {
            if (isset($itemsById[$id])) {
                $area->setItem($row, 0, $itemsById[$id]);
            }
        }

        return $area;
    }

    protected function byId($items)
    {
        $byId = [];
        foreach ($items as $item) {
            $byId[$item->getId()] = $item;
        }
        return $byId;
    }

    protected function getAreaFromDatabase($pageTypeId, $pageId, $name)
    {
        $criteria = [
            'page_type_id' => $pageTypeId,
            'page_id'      => $pageId,
            'name'         => $name
        ];

        return $this->makeAreaQuery($criteria)->first();
    }

    /**
     * @param array $criteria (optional)
     *
     * @return Builder
     */
    protected function makeAreaQuery(array $criteria=[])
    {
        $query = $this->areaModel->newQuery();
        foreach ($criteria as $key=>$criterion) {
            $key = $key == 'page_type_id' ? 'page_type' : $key;
            $this->addCriteria($query, $key, $criterion);
        }
        return $query;
    }

    /**
     * @param Builder $builder
     * @param string  $key
     * @param mixed   $value
     */
    protected function addCriteria(Builder $builder, $key, $value)
    {
        if ($value === null) {
            $builder->whereNull($key);
            return;
        }
        if (is_array($value)) {
            $builder->whereIn($key, $value);
            return;
        }
        $builder->where($key, $value);
    }

    protected function newArea($pageTypeId, $pageId, $name)
    {
        return $this->areaModel->newInstance([
            $this->areaPageTypeKey=>$pageTypeId,
            $this->nameKey => $name,
            $this->pageIdKey => $pageId
        ]);
    }

    protected function getOrCreateItem($itemId, array $data)
    {
        try{
            return $this->widgetItems->find($itemId);
        } catch (ModelNotFoundException $e) {
        }

        if (!isset($data['typeId'])) {
            throw new OutOfBoundsException("The passed data of new items have to contain typeId with the widgets type key");
        }

        return $this->widgetItems->make($data['typeId']);
    }

    protected function cleanData($data)
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