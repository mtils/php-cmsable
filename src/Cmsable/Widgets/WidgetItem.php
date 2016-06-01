<?php


namespace Cmsable\Widgets;

use OutOfBoundsException;
use Illuminate\Database\Eloquent\Model;
use Ems\Graphics\LayoutItemTrait;
use Ems\Contracts\Graphics\Layout;
use Cmsable\Widgets\Contracts\WidgetItem as WidgetItemContract;
use Collection\StringList;


class WidgetItem extends Model implements WidgetItemContract
{

    use LayoutItemTrait;

    protected $table = 'widget_items';

    protected $guarded = ['id'];

    /**
     * @var \Collection\StringList
     **/
    protected $_cssClasses;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'area_id' => 'integer',
        'type_id' => 'string',
        'data' => 'array'
    ];

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

    public function setLayout(Layout $layout)
    {
        $this->_layout = $layout;
        $this->area()->associate($layout);
        return $this;
    }

    public function area()
    {
        return $this->belongsTo('Cmsable\Widgets\Area');
    }

    /**
     * {@inheritdoc}
     *
     * @return \Collection\StringList
     **/
    public function cssClasses()
    {
        if (!$this->_cssClasses) {
            $typeParts = explode('.',$this->getTypeId());
            $partCount = count($typeParts);
            $lastPart = $partCount > 1 ? $typeParts[$partCount-1] : implode('_',$typeParts);
            $this->_cssClasses = new StringList([
                $lastPart
            ]);
        }
        return $this->_cssClasses;
    }

}