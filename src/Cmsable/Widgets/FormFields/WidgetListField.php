<?php


namespace Cmsable\Widgets\FormFields;

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

    public function __construct(AreaRepository $areaRepository, AreaRenderer $areaRenderer)
    {
        $this->areaRepository = $areaRepository;
        $this->areaRenderer = $areaRenderer;
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
                'handle' => $this->getForm()->getName() . '--'. $this->getName()
            ];
            return $this->areaRenderer->renderEditArea($area, $vars);
        } catch (\Exception $e) {
            return get_class($e) . ': ' . $e->getMessage();
        }

    }

    protected function getArea()
    {
        if (!$page = $this->getPage()) {
            return;
        }

        return $this->areaRepository->areaFor($page->getPageTypeId(),
                                              $page->getIdentifier(),
                                              $this->getPlainName());
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
