<?php

namespace Cmsable\Widgets\Contracts;

use Ems\Contracts\Graphics\Layout;
use Ems\Contracts\Core\Named;

interface Area extends Layout, Named
{

    /**
     * Return the id of the page this area belongs to
     *
     * @return int
     **/
    public function getPageId();

    /**
     * Set the id of the corresponding page
     *
     * @param int $pageId
     * @return self
     **/
    public function setPageId($pageId);

}
