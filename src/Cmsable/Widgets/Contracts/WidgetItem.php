<?php

namespace Cmsable\Widgets\Contracts;

use Ems\Contracts\Graphics\LayoutItem;
use Ems\Contracts\Core\Identifiable;

/**
 * The WidgetItem is the actual widget instance which
 * will be loaded (from db).
 * So you have your Cmsable\Widgets\Widget which renders
 * and manages the WidgetItem instances provided by
 * the layout
 **/
interface WidgetItem extends LayoutItem, Identifiable
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
     * Return the Data associated with this instance
     *
     * @return array
     **/
    public function getData();

    /**
     * Return some css classes identifying this widget
     *
     * @return \Collection\StringList
     **/
    public function cssClasses();

}
