<?php

namespace Cmsable\Widgets\SiteTreePlugins;

use Cmsable\Controller\SiteTree\Plugin\Plugin;
use Cmsable\Model\SiteTreeNodeInterface;
use Cmsable\Widgets\Contracts\Area;
use Cmsable\Widgets\Contracts\AreaRepository;
use Cmsable\Widgets\FormFields\WidgetSelectField;
use Cmsable\Widgets\Repositories\WidgetTool;
use Exception;
use FormObject\Field\SelectableProxy;
use FormObject\Field\SelectOneField;
use FormObject\FieldList;
use FormObject\Form;

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

    /**
     * WidgetAnchorPlugin constructor.
     *
     * @param AreaRepository    $areaRepository
     * @param WidgetTool        $widgetTool
     * @param WidgetSelectField $widgetField
     */
    public function __construct(AreaRepository $areaRepository, WidgetTool $widgetTool, WidgetSelectField $widgetField)
    {
        $this->areaRepository = $areaRepository;
        $this->widgetTool = $widgetTool;
        $this->widgetField = $widgetField;
    }

    /**
     * @param FieldList $fields
     * @param SiteTreeNodeInterface $page
     *
     * @throws Exception
     */
    public function modifyFormFields(FieldList $fields, SiteTreeNodeInterface $page)
    {
        /** @var FieldList $mainFields */
        $mainFields = $fields('main');
        $mainFields->push($this->createAreaSelect($page));//->before('content');
        $mainFields->offsetUnset('content');
        $mainFields->push($this->widgetField->setName('widget__select'));
    }

    /**
     * @param Form                  $form
     * @param SiteTreeNodeInterface $page
     */
    public function fillForm(Form $form, SiteTreeNodeInterface $page)
    {
        list($widget, $targetPage) = $this->widgetTool->widgetAndRelatedPage($page);

        /** @var SelectOneField $areaSelect */
        $areaSelect = $form->get('main')->get('redirect__redirect_target_i');
        $area = null;

        /** @var Area $area */
        if (!$area = ($widget ? $widget->getLayout() : null)) {
            $area = $this->guessAreaFromSelect($areaSelect);
        }

        if (!$area) {
            return;
        }

        $this->areaRepository->configure($area);
        $this->widgetField->setArea($area);

        if ($targetPage) {
            $areaSelect->setValue($this->widgetTool->createAreaPointer($targetPage, $area));
        }

        if ($widget) {
            $this->widgetField->setValue($widget->getId());
        }

    }

    /**
     * Store anchor details in right places.
     *
     * @param Form $form
     * @param SiteTreeNodeInterface $page
     */
    public function prepareSave(Form $form, SiteTreeNodeInterface $page)
    {
        $areaPointer = $this->widgetTool->parseAreaPointer(
            $form['redirect__redirect_target_i']
        );
        $page->redirect_type = 'internal';
        $page->redirect_target = $areaPointer['pageId'] . '#' . $this->widgetTool->widgetToAnchor($form['widget__select']);
    }

    /**
     * Try to guess the area from $targetSelect
     *
     * @param SelectOneField $targetSelect
     *
     * @return Area|null
     */
    protected function guessAreaFromSelect(SelectOneField $targetSelect)
    {
        if (!$value = $targetSelect->getValue()) {
            /** @var SelectableProxy $item */
            foreach ($targetSelect as $item) {
                $value = $item->getKey();
                break;
            }
        }

        if (!$value) {
            return null;
        }

        $areaInfo = $this->widgetTool->parseAreaPointer($value);

        foreach ($this->areaRepository->find(['page_id' => $areaInfo['pageId']]) as $area) {
            if ($area->getId() == $areaInfo['areaId'] || $area->getName() == $areaInfo['areaName']) {
                return $area;
            }
        }

        return null;
    }

    /**
     * Create the area select field.
     *
     * @param SiteTreeNodeInterface $page
     *
     * @return SelectOneField
     * @throws Exception
     */
    protected function createAreaSelect(SiteTreeNodeInterface $page)
    {
        $field = new SelectOneField(
            'redirect__redirect_target_i',
            trans(
                'ems::sitetree-plugins.widget-anchor-plugin.area-containing-widgets'
            )
        );

        $field->addCssClass('widget-area-loader');
        $field->setAttribute('data-replace-url', url(''));

        return $field->setSrc($this->getAreaSelectOptions($page));
    }

    /**
     * Return readable options to select an area from a page.
     *
     * @param SiteTreeNodeInterface $page
     *
     * @return array
     */
    protected function getAreaSelectOptions($page)
    {
        $scopeModelId = $page->{$page->rootIdColumn};

        if (!$pages = $this->widgetTool->pagesWithAreas($scopeModelId)) {
            return [
                '0' => trans(
                    'ems::sitetree-plugins.widget-anchor-plugin.no-pages-with-areas'
                )
            ];
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
                $id = $this->widgetTool->createAreaPointer($pageId, $area);
                $options[$id] = $page->getMenuTitle() . ' (' . $area->getName(
                    ) . ')';
            }
        }

        return $options;
    }

}