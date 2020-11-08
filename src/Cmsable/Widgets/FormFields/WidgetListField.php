<?php


namespace Cmsable\Widgets\FormFields;

use Cmsable\Widgets\Contracts\Area;
use FormObject\Field;

use Cmsable\Widgets\Contracts\AreaRepository;
use Cmsable\Widgets\Contracts\AreaRenderer;
use Cmsable\Model\SiteTreeNodeInterface as Page;

class WidgetListField extends Field
{

    /**
     * @var \Cmsable\Widgets\Contracts\AreaRepository
     **/
    protected $areaRepository;

    /**
     * @var \Cmsable\Widgets\Contracts\AreaRenderer
     **/
    protected $areaRenderer;

    /**
     * @var Area
     */
    private $area;

    public function __construct(AreaRepository $areaRepository, AreaRenderer $areaRenderer)
    {
        $this->areaRepository = $areaRepository;
        $this->areaRenderer = $areaRenderer;
    }

    public function getWidgetConfig()
    {
        return json_encode([]);
    }

    public function __toString()
    {
        try {
            $area = $this->getArea();
            $area->setMaxColumns(1);
            $area->setMaxRows(100);
            $vars = [
                'field' => $this,
                'form' => $this->getForm(),
                'name' => $this->getPlainName(),
                'handle' => $this->getForm()->getName() . '--'. $this->getName(),
                'fullName' => $this->getName(),
                'layoutName' => $this->getName() . "__layout",
                'widgetConfigName' => $this->getName() . "__widget_config"
            ];
            return $this->areaRenderer->renderEditArea($area, $vars);
        } catch (\Exception $e) {
            return get_class($e) . $e->getFile() . ':' . $e->getLine() . ' ' . $e->getMessage();
        }

    }

    public function getArea()
    {
        if ($this->area) {
            return $this->area;
        }
        if (!$page = $this->getPage()) {
            return;
        }

        return $this->areaRepository->areaFor($page->getPageTypeId(),
                                              $page->getIdentifier(),
                                              $this->getPlainName());
    }

    public function setArea(Area $area=null)
    {
        $this->area = $area;
        return $this;
    }

    protected function getPage()
    {
        if (!$form = $this->getForm()) {
            return;
        }

        if (!$model = $form->getModel()) {
            return;
        }

        if ($model instanceof Page) {
            return $model;
        }

    }
}
