<?php


namespace Cmsable\Widgets\Repositories;

use Illuminate\Database\Eloquent\Model;
use Cmsable\Widgets\Contracts\AreaRepository as RepositoryContract;
use Cmsable\Widgets\Contracts\Area;

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
     * @param \Illuminate\Database\Eloquent\Model $areaModel
     **/
    public function __construct(Model $areaModel)
    {
        $this->areaModel = $areaModel;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $pageTypeId
     * @param int $pageId (optional)
     * @param string $name (optional)
     * @return \Cmsable\Widgets\Contracts\Area
     **/
    public function areaFor($pageTypeId, $pageId=null, $name=self::CONTENT)
    {
        if (!$pageId) {
            return $this->configure($this->areaModel->newInstance([$this->areaPageTypeKey=>$pageTypeId]));
        }
        if ($area = $this->getAreaFromDatabase($pageTypeId, $pageId, $name)) {
            return $this->configure($area);
        }
        return $this->configure($this->areaModel->newInstance([$this->areaPageTypeKey=>$pageTypeId]));
    }

    /**
     * {@inheritdoc}
     *
     * @param \Cmsable\Widgets\Contracts\Area $area
     * @param array $attributes
     * @return self
     **/
    public function update(Area $area, array $attributes)
    {
    }

    protected function configure(Area $area)
    {
        return $area;
    }

    protected function getAreaFromDatabase($pageTypeId, $pageId, $name)
    {
        return $this->areaModel
                    ->where($this->areaPageTypeKey, $pageTypeId)
                    ->where($this->pageIdKey, $pageId)
                    ->where($this->nameKey, $name)
                    ->first();
    }

}