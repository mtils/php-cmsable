<?php


namespace Cmsable\Widgets;

use Cmsable\Widgets\Contracts\Widget;
use Cmsable\Widgets\Contracts\WidgetItem as ItemContract;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\ValidationException;
use Cmsable\Widgets\Contracts\AreaRepository;
use App;

abstract class AbstractWidget implements Widget
{

    public static $typeId = 'cmsable.widgets.samples.shout-out-box';

    protected $validationFactory;


    /**
     * return an array of validation rules
     **/
    abstract public function rules();

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function getTypeId()
    {
        return static::$typeId;
    }

    /**
     * {@inheritdoc}
     *
     * @param array
     * @return bool
     * @throws \Illuminate\Contracts\Validation\ValidationException
     **/
    public function validate(array $data)
    {
        $validator = $this->createValidator($data, $this->rules);
        if ($validator->passes()) {
            return true;
        }
        throw new ValidationException($validator);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $item
     **/
    public function configure(ItemContract $item){}

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function category()
    {
        return 'banners';
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     **/
    public function getMaxColumnSpan()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     **/
    public function getMaxRowSpan()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     **/
    public function isEditable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $pageTypeId
     * @param string $areaName
     * @return bool
     **/
    public function isAllowedOn($pageTypeId, $areaName=AreaRepository::CONTENT)
    {
        return true;
    }

    protected function createValidator($data, $rules)
    {
        return $this->validationFactory()->make($data, $this->rules);
    }

    protected function validationFactory()
    {
        if (!$this->validationFactory) {
            $this->validationFactory = App::make('Illuminate\Contracts\Validation\Factory');
        }
        return $this->validationFactory;
    }

    protected function trKey($key, $namespace='')
    {
        return $namespace . 'widgets.' . str_replace('.','/',$this->getTypeId()) . ".$key";
    }

}