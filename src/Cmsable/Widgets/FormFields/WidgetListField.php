<?php


namespace Cmsable\Widgets\FormFields;

class WidgetListField extends AbstractWidgetField
{

    private $editMode = self::MODE_MANAGE;

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
                'widgetConfigName' => $this->getName() . "__widget_config",
                'editMode'         => $this->editMode
            ];
            return $this->areaRenderer->renderEditArea($area, $vars);
        } catch (\Exception $e) {
            return get_class($e) . $e->getFile() . ':' . $e->getLine() . ' ' . $e->getMessage();
        }

    }

    /**
     * @return string
     */
    public function getEditMode(): string
    {
        return $this->editMode;
    }

    /**
     * @param string $editMode
     */
    public function setEditMode(string $editMode)
    {
        $this->editMode = $editMode;
    }

}
