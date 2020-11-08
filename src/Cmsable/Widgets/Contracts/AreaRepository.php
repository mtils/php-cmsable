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
     * Find all areas that match $criteria. Possible keys are:
     * page_type_id, page_id, name.
     * All three should work with equal when string and WHERE IN when array.
     * An existing key means "search for it". If it is null search for IS NULL.
     *
     *
     * @param array $criteria (optional)
     *
     * @return Area[]
     */
    public function find(array $criteria=[]);

    /**
     * Return a layout for pageType $pageTypeId.and $pageId. If no $pageId is
     * passed create a new one for $pageId.
     * Optionally pass a $name to allow multiple areas per page
     *
     * @param string $pageTypeId
     * @param int $pageId (optional)
     * @param string $name (optional)
     * @return Area
     **/
    public function areaFor($pageTypeId, $pageId=null, $name=self::CONTENT);

    /**
     * Save the area
     *
     * @param Area $area
     * @param array $attributes
     * @return self
     **/
    public function update(Area $area, array $attributes);

    /**
     * Configure the area. Add items and its widgets.
     *
     * @param Area $area
     *
     * @return Area
     */
    public function configure(Area $area);

}
