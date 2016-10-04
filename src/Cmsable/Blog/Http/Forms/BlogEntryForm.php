<?php

namespace Cmsable\Blog\Http\Forms;

use Forms;
use FormObject\Form;
use Cmsable\Resource\Contracts\ResourceForm;
use Ems\App\Services\Casting\TypeIntrospectorCaster as Caster;
use Ems\Contracts\Core\TextProvider;

class BlogEntryForm extends Form implements ResourceForm
{

    public $validationRules = [

        'image_id'             => 'exists:files,id',
        'preview_image_id'     => 'exists:files,id',
        'title'                => 'required|min:3|max:255',
        'menu_title'           => 'min:3|max:255',
        'url_segment'          => 'alpha_dash|min:3|max:255',
        'topic'                => 'max:255',
        'content'              => 'required|min:8|max:64000',
        'preview_content'      => 'min:8|max:2048',
        'priority'             => 'in:1,4,8,12,16',
        'publish_at'           => 'local_date',
        'blog_date'            => 'required|local_date'

    ];

    protected $caster;

    protected $texts;

    public function __construct(Caster $caster, TextProvider $texts)
    {
        $this->caster = $caster;
        $this->texts = $texts->forNamespace('cmsable-blog')
                             ->forDomain('models.blog_entry');
    }

    public function resourceName()
    {
        return 'blog-entries';
    }

    public function setModel($model)
    {
        $this->model = $model;
        $this->fillByArray($this->caster->format(get_class($model), $this->collectAttributes($model)));
        return parent::setModel($model);
    }

    public function createFields()
    {

        $fields = parent::createFields();

        $mainFields = Forms::fieldList('main', trans('cmsable::forms.page-form.main'));
        $mainFields->setSwitchable(true);

        $images = Forms::fieldList('images')->addCssClass('horizontal-split');

        $images->push(
            Forms::imageDbField('image_id')->setTitle($this->texts->get('fields.image')),
            Forms::imageDbField('preview_image_id')->setTitle($this->texts->get('fields.preview_image'))
        );

        $topicAndDate = Forms::fieldList('topic_date')->addCssClass('horizontal-split');

        $topicAndDate->push(
            Forms::text('topic'),
            Forms::date('blog_date')
        );

        $mainFields->push(
            $images,
            Forms::text('title')->addCssClass('input-lg'),
            $topicAndDate,
            Forms::html('content')
        );

        $settingFields = Forms::fieldList('settings', trans('cmsable::forms.page-form.settings'));
        $settingFields->setSwitchable(TRUE);

        $settingFields->push(
            Forms::text('url_segment'),
            Forms::text('menu_title'),
            $this->priorityField()
        );

        $fields->push($mainFields, $settingFields);

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

    protected function priorityField()
    {
        $priorities = [
            1 => 'Höchste Priorität',
            4 => 'Wichtig',
            8 => 'Normal',
            12 => 'Unwichtig',
            16 => 'Überflüssig'
        ];

        return Forms::selectOne('priority')->setSrc($priorities);
    }
    
    protected function collectAttributes($model)
    {
        $casted = [];
        foreach ($model->getAttributes() as $key=>$value) {
            $casted[$key] = $model->getAttribute($key);
        }
        return $casted;
    }

}
