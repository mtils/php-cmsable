<?php


namespace Cmsable\Widgets;

use OutOfBoundsException;
use Illuminate\Database\Eloquent\Model;
use Ems\Graphics\LayoutItemTrait;
use Cmsable\Widgets\Contracts\WidgetItem as WidgetItemContract;


class WidgetItem extends Model implements WidgetItemContract
{

    use LayoutItemTrait;

    protected $table = 'widget_items';

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
     * @return int
     **/
    public function getTypeId()
    {
        return $this->getAttribute('type_id');
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     **/
    public function getData()
    {
        return $this->getAttribute('data');
    }

}