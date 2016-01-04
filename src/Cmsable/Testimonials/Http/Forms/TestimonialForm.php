<?php

namespace Cmsable\Testimonials\Http\Forms;

use Forms;
use FormObject\Form;
use Cmsable\Resource\Contracts\ResourceForm;


class TestimonialForm extends Form implements ResourceForm
{

    public $validationRules = [

        'preview_image_id'     => 'exists:files,id',
        'origin'  => 'required|min:5|max:255',
        'cite'    => 'required|min:8|max:64000'

    ];

    public function resourceName()
    {
        return 'testimonials';
    }

    public function setModel($model)
    {
        $this->model = $model;
        $this->fillByArray($model->toArray());
        return parent::setModel($model);
    }

    public function createFields()
    {

        $fields = parent::createFields();
        $fields->push(
            Forms::imageDbField('preview_image_id', 'Bild'),
            Form::text('origin'),
            Form::text('cite')->setMultiLine(true)
        );

        return $fields;
    }

    public function createActions()
    {
        if (!$this->model || !$this->model->exists) {
            return parent::createActionList('create');
        }

        $actions = parent::createActionList('save');

        return $actions;
    }

}