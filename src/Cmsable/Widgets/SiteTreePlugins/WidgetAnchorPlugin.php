<?php

namespace Cmsable\Widgets\SiteTreePlugins;

use BeeTree\Helper;
use Cmsable\Controller\SiteTree\Plugin\Plugin;
use Cmsable\Model\AdjacencyListSiteTreeModel;
use Cmsable\Model\SiteTreeNodeInterface;
use Cmsable\Widgets\Contracts\Area;
use Cmsable\Widgets\Contracts\WidgetItem;
use Cmsable\Widgets\FormFields\WidgetListField;
use Cmsable\Widgets\FormFields\WidgetSelectField;
use Cmsable\Widgets\Repositories\WidgetTool;
use ErrorException;
use FormObject\Field\Selectable;
use FormObject\Field\SelectableProxy;
use FormObject\Field\SelectOneField;
use FormObject\FieldList;
use FormObject\Form;
use Cmsable\Widgets\Contracts\AreaRepository;

use function array_unique;
use function explode;
use function get_class;
use function is_numeric;
use function strpos;

/**
 *  * Created by mtils on 08.11.20 at 10:16.
 **/

class WidgetAnchorPlugin extends Plugin
{

    /**
     * @var AreaRepository
     */
    private $areaRepository;

    /**
     * @var WidgetTool
     */
    private $widgetTool;

    /**
     * @var WidgetSelectField
     */
    private $widgetField;

    public function __construct(AreaRepository $areaRepository, WidgetTool $widgetTool, WidgetSelectField $widgetField)
    {
        $this->areaRepository = $areaRepository;
        $this->widgetTool = $widgetTool;
        $this->widgetField = $widgetField;
    }

    /**
     * @param FieldList             $fields
     * @param SiteTreeNodeInterface $page
     */
    public function modifyFormFields(FieldList $fields, SiteTreeNodeInterface $page)
    {
        /** @var FieldList $mainFields */
        $mainFields = $fields('main');
        $mainFields->push($this->createPageSelect($page));//->before('content');
        $mainFields->offsetUnset('content');

        $this->configureListField($this->widgetField, $page);
        $mainFields->push($this->widgetField);

    }

    public function fillForm(Form $form, SiteTreeNodeInterface $page)
    {
        list($widget, $target) = $this->widgetAndRelatedPage($page);
        if ($target) {
            $this->configureListField($this->widgetField, $target);
        } else {
            /** @var SelectOneField $targetSelect */
            $targetSelect = $form->get('main')->get('redirect__redirect_target_i');
            $value = $targetSelect->getValue();
            if (!$value) {
                /** @var SelectableProxy $item */
                foreach ($targetSelect as $item) {
                    $value = $item->getKey();
                    break;
                }
            }
            if ($value) {
                list($pageId, $areaId, $areaName) = explode('|', $value);
                foreach ($this->areaRepository->find(['page_id' => $pageId]) as $area) {
                    if ($area->getName() == $areaName) {
                        $this->widgetField->setArea($this->areaRepository->configure($area));
                        break;
                    }
                }
                $this->widgetField->setName("widgets__$areaName");
            }
        }

    }

    protected function widgetAndRelatedPage(SiteTreeNodeInterface $redirectHolder)
    {
        $target = $redirectHolder->getRedirectTarget();
        $pagePointer = $target;
        $anchor = '';
        if (strpos($target, '#')) {
            list($pagePointer, $anchor) = explode('#', $target);
        }
        $widget = null;
        $page = null;
        if ($anchor) {
            $widget = $this->widgetTool->widgetOfAnchor($anchor);
        }
        if (is_numeric($pagePointer)) {
            $page = $this->createTreeModel($redirectHolder)->get($pagePointer);
        }
        if ($pagePointer == 'firstchild') {
            $childNodes = $redirectHolder->childNodes();
            $page = isset($childNodes[0]) ? $childNodes[0] : null;
        }
        return [$widget, $page];
    }

    protected function configureListField(WidgetSelectField $field, SiteTreeNodeInterface $page)
    {
        /** @var WidgetItem $widget */
        /** @var SiteTreeNodeInterface $targetPage */
        list($widget, $targetPage) = $this->widgetAndRelatedPage($page);
        $areaName = 'unknown';
        if ($widget && $widget->getLayout()) {
            /** @var Area $area */
            $area = $widget->getLayout();
            $areaName = $area->getName();
            $field->setArea($area);
        }
        $name = "widgets__$areaName";
        $field->setName($name);
    }

    protected function createPageSelect(SiteTreeNodeInterface $page)
    {
        $field = new SelectOneField('redirect__redirect_target_i', trans('ems::sitetree-plugins.widget-anchor-plugin.area-containing-widgets'));

        $field->addCssClass('widget-area-loader');
        $field->setAttribute('data-replace-url', url(''));

        return $field->setSrc($this->getPageOptions($page));

    }

    protected function getPageOptions($page)
    {
        $scopeModelId = $page->{$page->rootIdColumn};

        if (!$pages = $this->widgetTool->pagesWithAreas($scopeModelId)) {
            return ['0' => trans('ems::sitetree-plugins.widget-anchor-plugin.no-pages-with-areas')];
        }

        $areas = $this->widgetTool->areasInUse($scopeModelId);
        $areasByPageId = [];
        foreach ($areas as $area) {
            $pageId = $area->getPageId();
            if (!isset($areasByPageId[$pageId])) {
                $areasByPageId[$pageId] = [];
            }
            $areasByPageId[$pageId][] = $area;
        }
        $options = [];
        foreach ($pages as $page) {
            $pageId = $page->getIdentifier();
            if (!isset($areasByPageId[$pageId])) {
                continue;
            }
            /** @var Area $area */
            foreach ($areasByPageId[$pageId] as $area) {
                $id = $pageId . '|' . $area->getId() . '|' . $area->getName();
                $options[$id] = $page->getMenuTitle() . ' (' . $area->getName() . ')';
            }

        }

        return $options;

    }

    protected function createTreeModel(SiteTreeNodeInterface $page)
    {
        return new AdjacencyListSiteTreeModel(get_class($page), $page->{$page->rootIdColumn});
    }

}