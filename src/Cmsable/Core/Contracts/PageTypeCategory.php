<?php
/**
 *  * Created by mtils on 01.12.18 at 06:49.
 **/

namespace Cmsable\Core\Contracts;


use Ems\Contracts\Core\Named;
use Ems\Core\NamedObject;


class PageTypeCategory extends NamedObject
{

    /**
     * @var string
     */
    protected $cmsIcon = '';

    /**
     * Return a icon name/url to display this category
     *
     * @return string
     */
    public function getCmsIcon()
    {
        return $this->cmsIcon;
    }

    /**
     * @see self::getCmsIcon()
     *
     * @param string $icon
     *
     * @return $this
     */
    public function setCmsIcon($icon)
    {
        $this->cmsIcon = $icon;
        return $this;
    }
}