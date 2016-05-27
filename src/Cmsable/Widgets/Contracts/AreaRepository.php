<?php

namespace Cmsable\Widgets\Contracts;

interface AreaRepository
{

    /**
     * The default area name
     *
     * @var string
     **/
    const CONTENT = 'content';

    /**
     * The area name for a sidebar
     *
     * @var string
     **/
    const SIDEBAR = 'sidebar';

    /**
     * Return a layout for pageType $pageTypeId.and $pageId. If no $pageId is
     * passed create a new one for $pageId.
     * Optionally pass a $name to allow multiple areas per page
     *
     * @param string $pageTypeId
     * @param int $pageId (optional)
     * @param string $name (optional)
     * @return \Cmsable\Widgets\Contracts\Area
     **/
    public function areaFor($pageTypeId, $pageId=null, $name=self::CONTENT);

    /**
     * Save the area
     *
     * @param \Cmsable\Widgets\Contracts\Area $area
     * @param array $attributes
     * @return self
     **/
    public function update(Area $area, array $attributes);

}
