<?php


namespace Cmsable\Widgets;

use OutOfBoundsException;
use Illuminate\Database\Eloquent\Model;
use Cmsable\Widgets\Contracts\Area as AreaContract;
use Ems\Graphics\LayoutTrait;


class Area extends Model implements AreaContract
{

    use LayoutTrait;

    protected $table = 'widget_areas';

    protected $guarded = ['id'];

    /**
     * {@inheritdoc}
     *
     * @return int
     **/
    public function getId()
    {
        return $this->getKey();
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function getName()
    {
        return $this->getAttribute('name');
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     **/
    public function getPageId()
    {
        return $this->getAttribute('page_id');
    }

    /**
     * {@inheritdoc}
     *
     * @param int $pageId
     * @return self
     **/
    public function setPageId($pageId)
    {
        $this->setAttribute('page_id', $pageId);
        return $this;
    }

}