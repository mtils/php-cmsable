<?php
/**
 *  * Created by mtils on 15.11.20 at 12:14.
 **/

namespace Cmsable\Widgets\FormFields;


use Cmsable\Widgets\Contracts\Area;
use Cmsable\Widgets\Contracts\AreaRenderer;
use Cmsable\Widgets\Contracts\AreaRepository;
use FormObject\Field;
use Cmsable\Model\SiteTreeNodeInterface as Page;

abstract class AbstractWidgetField extends Field
{
    const MODE_MANAGE           = 'MANAGE';
    const MODE_SELECT_EXISTING  = 'SELECT_EXISTING';
    const MODE_SELECT_NEW       = 'SELECT_NEW';

    /**
     * @var AreaRepository
     **/
    protected $areaRepository;

    /**
     * @var AreaRenderer
     **/
    protected $areaRenderer;

    /**
     * @var Area
     */
    protected $area;

    public function __construct(AreaRepository $areaRepository, AreaRenderer $areaRenderer)
    {
        $this->areaRepository = $areaRepository;
        $this->areaRenderer = $areaRenderer;
    }

    /**
     * @return Area|null
     */
    public function getArea()
    {
        if ($this->area) {
            return $this->area;
        }

        if (!$page = $this->getPage()) {
            return null;
        }

        return $this->areaRepository->areaFor($page->getPageTypeId(),
                                              $page->getIdentifier(),
                                              $this->getPlainName());
    }

    /**
     * @param Area|null $area
     *
     * @return $this
     */
    public function setArea(Area $area=null)
    {
        $this->area = $area;
        return $this;
    }

    /**
     * @return Page|null
     */
    protected function getPage()
    {
        if (!$form = $this->getForm()) {
            return null;
        }

        if (!$model = $form->getModel()) {
            return null;
        }

        if ($model instanceof Page) {
            return $model;
        }

    }
}