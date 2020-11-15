<?php
/**
 *  * Created by mtils on 15.11.20 at 12:09.
 **/

namespace Cmsable\Widgets\FormFields;


use Cmsable\Widgets\Contracts\AreaRenderer;
use Cmsable\Widgets\Contracts\AreaRepository;
use Cmsable\Widgets\Contracts\WidgetItem;
use FormObject\Field;
use Illuminate\Contracts\View\Factory as ViewFactory;

class WidgetSelectField extends AbstractWidgetField
{
    /**
     * @var ViewFactory
     */
    private $view;

    /**
     * @var string
     */
    private $template = 'widgets.select';

    /**
     * WidgetSelectField constructor.
     *
     * @param AreaRepository $areaRepository
     * @param AreaRenderer $areaRenderer
     * @param ViewFactory $view
     */
    public function __construct(AreaRepository $areaRepository, AreaRenderer $areaRenderer, ViewFactory $view)
    {
        parent::__construct($areaRepository, $areaRenderer);
        $this->view = $view;
    }

    public function __toString()
    {
        try {
//            $area = $this->getArea();
//            $area->setMaxColumns(1);
//            $area->setMaxRows(1);

            $vars = [
                'field' => $this,
                'form' => $this->getForm(),
                'name' => $this->getPlainName(),
                'handle' => $this->getForm()->getName() . '--'. $this->getName(),
                'fullName' => $this->getName(),
                'layoutName' => $this->getName() . "__layout",
                'widgetConfigName' => $this->getName() . "__widget_config",
                'editMode'         => self::MODE_SELECT_EXISTING,
                'renderer'         => $this->areaRenderer,
                'widgetItem'       => $this->getWidgetItem(),
                'area'             => $this->getArea()
            ];
            return $this->view->make($this->template, $vars)->render();
        } catch (\Exception $e) {
            return get_class($e) . $e->getFile() . ':' . $e->getLine() . ' ' . $e->getMessage();
        }
    }

    public function getValue()
    {
        if ($this->value) {
            return parent::getValue();
        }
        if (!$this->area) {
            return parent::getValue();
        }
        if (!$this->area->count()) {
            return parent::getValue();
        }

        /** @var WidgetItem $widgetItem */
        foreach ($this->area as $widgetItem) {
            return $widgetItem->getId();
        }
        return parent::getValue();
    }

    /**
     * @return WidgetItem|null
     */
    public function getWidgetItem()
    {
        if (!$value = $this->getValue()) {
            return null;
        }
        if (!$this->area) {
            return null;
        }
        /** @var WidgetItem $item */
        foreach ($this->area as $item) {
            if ($item->getId() == $value) {
                return $item;
            }
        }
        return null;
    }
}