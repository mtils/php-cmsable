<?php

namespace Cmsable\Widgets\Contracts;

use Ems\Contracts\Graphics\LayoutItem;

/**
 * A widget renders widget items, validates its data before it gets saved
 * So you have one Widget and many WidgetItems which will be rendered by this widget
 **/
interface Widget extends Renderer
{

    /**
     * Return the type id of this widget
     *
     * Return an identifier for the type of widget like:
     * org.your-domain.image-gallery
     *
     * @return string
     **/
    public function getTypeId();

    /**
     * Validate the data before it gets saved. Throw a validation
     * exception if the data is not valid
     *
     * @param array
     * @return bool
     * @throws \Illuminate\Contracts\Validation\ValidationException
     **/
    public function validate(array $data);

    /**
     * Return an array of default data for new items
     *
     * @return array
     **/
    public function defaultData();

    /**
     * Return a string for a category so it can be grouped in ui
     *
     * @return string
     **/
    public function category();

    /**
     * Return the max col span of this widget
     *
     * @return int
     **/
    public function getMaxColumnSpan();

    /**
     * Return the max row span of this widget
     *
     * @return int
     **/
    public function getMaxRowSpan();

    /**
     * Return if this widget can be edited
     *
     * @return bool
     **/
    public function isEditable();

    /**
     * Return if the widget is allowed on pages with $pageTypeId and an area named $areaName
     *
     * @param string $pageTypeId
     * @param string $areaName
     * @return bool
     **/
    public function isAllowedOn($pageTypeId, $areaName=AreaRepository::CONTENT);

}
